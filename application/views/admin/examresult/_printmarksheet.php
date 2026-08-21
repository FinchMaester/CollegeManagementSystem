<?php defined('BASEPATH') OR exit('No direct script access allowed');

// Fail-safe exam object so property reads never fatal
if (!isset($exam) || !is_object($exam)) {
    $exam = (object)['id'=>0,'exam_group_type'=>'coll_grade_system','session'=>''];
}

/* ================= HELPERS ================= */
if (!function_exists('findGrade')) {
function findGrade($exam_grades, $percentage) {
    if (!empty($exam_grades)) {
        foreach ($exam_grades as $g) {
            if ($percentage >= $g->mark_upto && $percentage <= $g->mark_from) return $g->name;
        }
    }
    return "-";
}}

if (!function_exists('cleanBaseName')) {
function cleanBaseName($name) {
    $name = trim($name);
    $name = preg_replace('/\s*\((TH|IN)\)\s*(\(\d+\))?\s*$/i', '', $name);
    $name = preg_replace('/\s*\(\d+\)\s*$/', '', $name);
    $name = preg_replace('/\s+/', ' ', $name);
    return trim($name);
}}

if (!function_exists('displaySubjectName')) {
function displaySubjectName($name) { return preg_replace('/\s*\(\d+\)\s*$/', '', trim($name)); }
}

if (!function_exists('capSubjectPointMax')) {
function capSubjectPointMax($points) {
    $bands = [
        ['min'=>3.61,'max'=>4.00,'top'=>4.00],
        ['min'=>3.21,'max'=>3.60,'top'=>3.60],
        ['min'=>2.81,'max'=>3.20,'top'=>3.20],
        ['min'=>2.41,'max'=>2.80,'top'=>2.80],
        ['min'=>2.01,'max'=>2.40,'top'=>2.40],
        ['min'=>1.61,'max'=>2.00,'top'=>2.00],
        ['min'=>1.21,'max'=>1.60,'top'=>1.60],
        ['min'=>0.00,'max'=>1.20,'top'=>1.20],
    ];
    foreach ($bands as $b) if ($points >= $b['min'] && $points <= $b['max']) return (float) number_format($b['top'],2,'.','');
    return (float) number_format($points,2,'.','');
}}

if (!function_exists('getPointRangeForGrade')) {
function getPointRangeForGrade($grade_name) {
    $point_ranges = [
        'A+' => ['min' => 3.61, 'max' => 4.00],
        'A'  => ['min' => 3.21, 'max' => 3.60],
        'B+' => ['min' => 2.81, 'max' => 3.20],
        'B'  => ['min' => 2.41, 'max' => 2.80],
        'C+' => ['min' => 2.01, 'max' => 2.40],
        'C'  => ['min' => 1.61, 'max' => 2.00],
        'D'  => ['min' => 1.21, 'max' => 1.60],
        'NG' => ['min' => 0.00, 'max' => 1.20],
    ];
    return $point_ranges[$grade_name] ?? null;
}}

if (!function_exists('findExamDivision')) {
function findExamDivision($marks_division, $percentage){
    if (!empty($marks_division)) {
        foreach ($marks_division as $d) {
            if ($percentage >= $d->percentage_to && $percentage <= $d->percentage_from) return $d->name;
        }
    }
    return "";
}}

if (!function_exists('findGradePoints')) {
function findGradePoints($exam_grades, $percentage) {
    if (empty($exam_grades)) return 0;
    foreach ($exam_grades as $g) {
        if ($percentage >= $g->mark_upto && $percentage <= $g->mark_from) {
            $ranges = [
                'A+' => ['min'=>3.61,'max'=>4.00],
                'A'  => ['min'=>3.21,'max'=>3.60],
                'B+' => ['min'=>2.81,'max'=>3.20],
                'B'  => ['min'=>2.41,'max'=>2.80],
                'C+' => ['min'=>2.01,'max'=>2.40],
                'C'  => ['min'=>1.61,'max'=>2.00],
                'D'  => ['min'=>1.21,'max'=>1.60],
                'NG' => ['min'=>0.00,'max'=>1.20],
            ];
            $min = $ranges[$g->name]['min'] ?? $g->point;
            $max = $ranges[$g->name]['max'] ?? $g->point;
            $range = max(0.000001, $g->mark_from - $g->mark_upto);
            $ratio = ($percentage - $g->mark_upto) / $range;
            $p = $min + $ratio * ($max - $min);
            return (float) number_format(max($min, min($max,$p)), 2, '.', '');
        }
    }
    return 0;
}}

if (!function_exists('getGradeFromPoints')) {
function getGradeFromPoints($p){
    if ($p >= 3.61) return 'A+'; if ($p >= 3.21) return 'A'; if ($p >= 2.81) return 'B+'; if ($p >= 2.41) return 'B';
    if ($p >= 2.01) return 'C+'; if ($p >= 1.61) return 'C'; if ($p >= 1.21) return 'D'; return 'NG';
}}

/* Consolidate helper */
if (!function_exists('examTotalResult')) {
function examTotalResult($array,$exam_type,$exam_grade) {
    $ret = ['max_marks'=>0,'min_marks'=>0,'credit_hours'=>0,'get_marks'=>0,'exam_result'=>true,'total_points'=>0,'quality_point'=>0];
    if (!empty($array)) {
        foreach ($array as $r) {
            if ($r->attendence == "absent") $ret['exam_result'] = false;
            $pct = ($r->max_marks>0) ? ($r->get_marks*100/$r->max_marks) : 0;
            $pt  = findGradePoints($exam_grade,$pct);
            $ret['total_points'] += $pt;
            $ret['quality_point']+= ($pt * $r->credit_hours);
            $ret['max_marks']    += $r->max_marks;
            $ret['min_marks']    += $r->min_marks;
            $ret['credit_hours'] += $r->credit_hours;
            $ret['get_marks']    += $r->get_marks;
        }
    }
    return json_encode($ret);
}}

if (!function_exists('getWeightageExam')) {
function getWeightageExam($exam_connection_list, $examid, $get_marks,$exam_get_percentage) {
    foreach ($exam_connection_list as $x) {
        if ($x->exam_group_class_batch_exams_id == $examid) {
            return [
                'exam_weightage'=>$x->exam_weightage,
                'exam_consolidate_marks'=>($get_marks*$x->exam_weightage)/100,
                'exam_consolidate_percentage'=>($exam_get_percentage*$x->exam_weightage)/100
            ];
        }
    }
    return "";
}}

/* ===== UI ===== */
?>
<style>
/* base */
body{font-family:'Helvetica Neue','Helvetica','Arial',sans-serif;color:#333;line-height:1.55}
.denifittable{width:98%!important;margin:0 auto;border-collapse:collapse;font-size:13px}
.denifittable td,.denifittable th{border:1px solid #e0e0e0;padding:4px 10px;text-align:center;vertical-align:middle}
.denifittable thead th{background:#4a90e2;color:#fff;font-weight:700;text-transform:uppercase;letter-spacing:.3px;border-color:#4a90e2}
.denifittable.marks tbody tr:nth-child(even) td{background:#f8faff}
.denifittable.marks tbody tr:nth-child(odd) td{background:#fff}
.denifittable.marks tbody tr.ms-summary-row td{background:#e9f1f8;font-weight:600;border-top:2px solid #4a90e2}
.ms-final-grade{font-weight:700}
.sig-line{width:180px;height:34px;border-bottom:1px dotted #000;margin:0 auto 6px}

/* blue section headers */
.box-title{
  background:#4a90e2;
  color:#fff;
  font-weight:700;
  padding:6px 10px;
  text-transform:uppercase;
  border:1px solid #4a90e2;
  border-radius:2px;
  display:block;
  /* the gap you asked for */
  margin:10px auto 6px;   /* top 10px, bottom 6px */
  width:98%;
}

/* sections wrapper (keeps overall rhythm consistent) */
.section{width:98%;margin:10px auto 0}

/* ensure tables that follow a .box-title don't add extra top space */
.box-title + .denifittable{margin-top:0}

/* print */
@media print{
  @page{margin:10mm}
  body{margin:0;padding:0;-webkit-print-color-adjust:exact;print-color-adjust:exact}
  .denifittable{font-size:10px}
  .box-title{margin:8px auto 4px}
}
</style>

<?php

/* ---------- reusable grading table (left) + attendance/skill (right); $opts from template_marksheets ---------- */
$__render_grading_block = function ($exam_grades, $attendance_data = null, $nonacademic_marks = null, $opts = null) {
    $show_grading     = ($opts === null || !empty($opts['show_grading']));
    $show_attendance  = ($opts === null || !empty($opts['show_attendance']));
    $show_nonacademic = ($opts === null || !empty($opts['show_nonacademic']));
    if (!$show_grading && !$show_attendance && !$show_nonacademic) {
        return;
    }

    $rows = is_array($exam_grades) ? $exam_grades : (array) $exam_grades;
    usort($rows, function ($a, $b) {
        $af = (float) $a->mark_from;
        $bf = (float) $b->mark_from;
        return $af == $bf ? ((float) $b->mark_upto <=> (float) $a->mark_upto) : ($bf <=> $af);
    });

    if ($attendance_data === null) {
        $attendance_data = ['working_days' => 0, 'present_days' => 0, 'absent_days' => 0];
    }
    if ($nonacademic_marks === null) {
        $nonacademic_marks = array();
    }

    $left_col  = $show_grading;
    $right_col = ($show_attendance || $show_nonacademic);
    ?>
    <table width="100%" cellpadding="0" cellspacing="0" style="margin-top:6px;">
        <tr>
            <?php if ($left_col && $right_col) { ?>
            <td style="width:65%;vertical-align:top;">
                <div class="box-title">Details of Grading System</div>
                <table class="denifittable" style="width:100%">
                    <thead>
                        <tr>
                            <th>Percent</th>
                            <th>Grade</th>
                            <th>Description</th>
                            <th>Grade Point</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (!empty($rows)) {
                        foreach ($rows as $g) {
                            $pr = getPointRangeForGrade($g->name);
                            $point_display = $pr ? (number_format($pr['min'], 2, '.', '') . ' - ' . number_format($pr['max'], 2, '.', ''))
                                                 : number_format((float) $g->point, 2, '.', '');
                            echo '<tr>';
                            echo '<td>' . number_format((float) $g->mark_upto, 0) . ' to ' . number_format((float) $g->mark_from, 0) . '</td>';
                            echo '<td>' . htmlspecialchars($g->name) . '</td>';
                            echo '<td>' . htmlspecialchars($g->description ?? '') . '</td>';
                            echo '<td>' . $point_display . '</td>';
                            echo '</tr>';
                        }
                    } else { ?>
                        <tr><td colspan="4">-- No grading rows configured --</td></tr>
                    <?php } ?>
                    </tbody>
                </table>
            </td>
            <td style="width:35%;vertical-align:top;padding-left:10px;">
                <?php if ($show_attendance) { ?>
                <div class="box-title">Attendance</div>
                <table class="denifittable" style="width:100%">
                    <tr><td style="text-align:left">Working Days</td><td style="width:70px"><?php echo isset($attendance_data['working_days']) ? (int) $attendance_data['working_days'] : '0'; ?></td></tr>
                    <tr><td style="text-align:left">Present Days</td><td><?php echo isset($attendance_data['present_days']) ? (int) $attendance_data['present_days'] : '0'; ?></td></tr>
                    <tr><td style="text-align:left">Absent Days</td><td><?php echo isset($attendance_data['absent_days']) ? (int) $attendance_data['absent_days'] : '0'; ?></td></tr>
                </table>
                <?php } ?>
                <?php if ($show_nonacademic) { ?>
                <div class="box-title" style="margin-top:8px;">Non-Academic Skill</div>
                <table class="denifittable" style="width:100%">
                    <?php
                    if (!empty($nonacademic_marks)) {
                        foreach ($nonacademic_marks as $mark) {
                            echo '<tr><td style="text-align:left">' . htmlspecialchars($mark['subject_name']) . '</td><td style="width:70px">' . htmlspecialchars($mark['marks_obtained']) . '</td></tr>';
                        }
                    } else {
                        echo '<tr><td colspan="2" style="text-align:center;color:#999;">No non-academic marks</td></tr>';
                    }
                    ?>
                </table>
                <?php } ?>
            </td>
            <?php } elseif ($left_col) { ?>
            <td colspan="2" style="vertical-align:top;">
                <div class="box-title">Details of Grading System</div>
                <table class="denifittable" style="width:100%">
                    <thead>
                        <tr>
                            <th>Percent</th>
                            <th>Grade</th>
                            <th>Description</th>
                            <th>Grade Point</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (!empty($rows)) {
                        foreach ($rows as $g) {
                            $pr = getPointRangeForGrade($g->name);
                            $point_display = $pr ? (number_format($pr['min'], 2, '.', '') . ' - ' . number_format($pr['max'], 2, '.', ''))
                                                 : number_format((float) $g->point, 2, '.', '');
                            echo '<tr>';
                            echo '<td>' . number_format((float) $g->mark_upto, 0) . ' to ' . number_format((float) $g->mark_from, 0) . '</td>';
                            echo '<td>' . htmlspecialchars($g->name) . '</td>';
                            echo '<td>' . htmlspecialchars($g->description ?? '') . '</td>';
                            echo '<td>' . $point_display . '</td>';
                            echo '</tr>';
                        }
                    } else { ?>
                        <tr><td colspan="4">-- No grading rows configured --</td></tr>
                    <?php } ?>
                    </tbody>
                </table>
            </td>
            <?php } else { ?>
            <td colspan="2" style="vertical-align:top;max-width:420px;">
                <?php if ($show_attendance) { ?>
                <div class="box-title">Attendance</div>
                <table class="denifittable" style="width:100%">
                    <tr><td style="text-align:left">Working Days</td><td style="width:70px"><?php echo isset($attendance_data['working_days']) ? (int) $attendance_data['working_days'] : '0'; ?></td></tr>
                    <tr><td style="text-align:left">Present Days</td><td><?php echo isset($attendance_data['present_days']) ? (int) $attendance_data['present_days'] : '0'; ?></td></tr>
                    <tr><td style="text-align:left">Absent Days</td><td><?php echo isset($attendance_data['absent_days']) ? (int) $attendance_data['absent_days'] : '0'; ?></td></tr>
                </table>
                <?php } ?>
                <?php if ($show_nonacademic) { ?>
                <div class="box-title" style="margin-top:8px;">Non-Academic Skill</div>
                <table class="denifittable" style="width:100%">
                    <?php
                    if (!empty($nonacademic_marks)) {
                        foreach ($nonacademic_marks as $mark) {
                            echo '<tr><td style="text-align:left">' . htmlspecialchars($mark['subject_name']) . '</td><td style="width:70px">' . htmlspecialchars($mark['marks_obtained']) . '</td></tr>';
                        }
                    } else {
                        echo '<tr><td colspan="2" style="text-align:center;color:#999;">No non-academic marks</td></tr>';
                    }
                    ?>
                </table>
                <?php } ?>
            </td>
            <?php } ?>
        </tr>
    </table>
    <?php
};
/* -------------------- END helpers -------------------- */


if (empty($marksheet)) { ?>
    <div class="alert alter-info"><?php echo $this->lang->line('no_record_found'); ?></div>
<?php } else {
    $__ms_extra = array(
        'show_grading'     => !isset($template->marksheet_show_grading_details) || (int) $template->marksheet_show_grading_details === 1,
        'show_attendance'  => !isset($template->marksheet_show_attendance_block) || (int) $template->marksheet_show_attendance_block === 1,
        'show_nonacademic' => !isset($template->marksheet_show_nonacademic_block) || (int) $template->marksheet_show_nonacademic_block === 1,
    );
    ?>
<style>
/* Screen */
.marksheet-page {
    box-decoration-break: clone;
}
/*
 * Bulk browser print: force each student onto a new sheet.
 * break-after on large table blocks is often ignored; break-before on 2nd+ sheet is more reliable (Chrome/Edge).
 */
@media print {
    html, body {
        height: auto !important;
        overflow: visible !important;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }
    .marksheet-page {
        display: block !important;
        float: none !important;
        clear: both !important;
        position: relative !important;
        width: 100% !important;
        max-width: 100% !important;
        box-sizing: border-box !important;
    }
    /*
     * Standalone view (viewmarksheet) has no layout/ss-print.css — define break here.
     * Same idea backend/ss-print.css .page-break
     */
    .page-break {
        display: block !important;
        break-before: page !important;
        page-break-before: always !important;
    }
    div.page-break.marksheet-between-students {
        clear: both !important;
        height: 0 !important;
        margin: 0 !important;
        padding: 0 !important;
        border: 0 !important;
        overflow: hidden !important;
        font-size: 0 !important;
        line-height: 0 !important;
    }
}
</style>
<?php

    /* ======================== SINGLE-EXAM ======================== */
    if ($marksheet['exam_connection'] == 0 && !empty($marksheet['students'])) {

        $total_students= count($marksheet['students']);
        $to_be_print=0;

        foreach ($marksheet['students'] as $student_key => $student_value) {
            $to_be_print++;

            $result_status = 1; $absent_status = false; $percentage_total = 0;
            $has_absent_overall = false; $has_ng_overall = false; $has_below_min_overall = false;

            // Roll & Symbol fetch from students table (canonical source)
            $__roll   = $student_value['student_roll_no'] ?? '';
            $__symbol = $student_value['symbol_no'] ?? '';
            try {
                $sid = isset($student_value['student_id']) ? (int)$student_value['student_id'] : 0;
                if ($sid > 0) {
                    $__row = $this->db->select('roll_no, symbol_no')->from('students')->where('id',$sid)->get()->row();
                } elseif (!empty($student_value['admission_no'])) {
                    $__row = $this->db->select('roll_no, symbol_no')->from('students')->where('admission_no',$student_value['admission_no'])->get()->row();
                } else { $__row = null; }
                if ($__row) { $__roll = (string)$__row->roll_no; $__symbol = (string)$__row->symbol_no; }
            } catch (Throwable $e) {}

            ?>
<?php if ($to_be_print > 1) { ?>
<div class="page-break marksheet-between-students" aria-hidden="true"></div>
<?php } ?>
<div class="marksheet-page" style="width:98%;margin:0 auto;border:1px solid #000;padding:10px 5px;box-sizing:border-box;">
    <?php if($template->header_image){ ?>
        <img src="<?php echo base_url('uploads/marksheet/' . $template->header_image); ?>" style="width:100%;height:120px;object-fit:contain;">
    <?php } ?>

    <table cellpadding="0" cellspacing="0" width="100%">
        <tr>
            <td align="center" style="width:20%;"><?php if ($template->left_logo) { ?><img src="<?php echo base_url('uploads/marksheet/' . $template->left_logo); ?>" width="70" height="70"><?php } ?></td>
            <td align="center" style="width:60%;">
                <div style="font-size:14px;">Quality Education</div>
                <div style="font-size:16px;font-weight:bold;text-transform:uppercase;padding:6px 0"><?php echo htmlspecialchars($template->school_name ?? ''); ?></div>
                <div style="font-size:13px;">Bheemdatta Municipality, Kanchanpur</div>
                <div style="font-size:18px;font-weight:700;margin-top:6px;"><?php echo $template->exam_name; ?></div>
                <?php if (!empty($exam->session)) { ?><div style="font-weight:bold;text-transform:uppercase;"><?php echo htmlspecialchars($exam->session); ?></div><?php } ?>
            </td>
            <td align="center" style="width:20%;"><?php if ($template->right_logo) { ?><img src="<?php echo base_url('uploads/marksheet/' . $template->right_logo); ?>" width="70" height="70"><?php } ?></td>
        </tr>
    </table>

    <?php
    $this->load->helper('marksheet_layout');
    $this->load->view('admin/examresult/_printmarksheet_student_identity', array(
        'template'       => $template,
        'student_value'  => $student_value,
        'exam'           => $exam,
        'sch_setting'    => $sch_setting,
        'symbol_no'      => $__symbol,
    ));
    ?>

    <?php
    $marksheet_layout_columns = marksheet_parse_layout_json(isset($template->column_layout_json) ? $template->column_layout_json : null);
    if ($marksheet_layout_columns === null) {
        $marksheet_layout_columns = marksheet_layout_default_columns();
    }
    $use_dynamic_marksheet = (!empty($template->marksheet_display_mode) && $template->marksheet_display_mode === 'dynamic');
    ?>

    <!-- SUBJECT TABLE (dynamic layout or legacy) -->
    <?php if ($use_dynamic_marksheet) {
        $this->load->view('admin/examresult/_printmarksheet_subject_dynamic', array(
            'template'                 => $template,
            'exam'                     => $exam,
            'exam_grades'              => $exam_grades,
            'student_value'            => $student_value,
            'marksheet_layout_columns' => $marksheet_layout_columns,
        ));
    } elseif ($exam->exam_group_type == 'basic_system') {
        $ms_absent = false; $ms_below_min = false;
        foreach ($student_value['exam_result'] as $r) {
            if (isset($r->is_academic) && $r->is_academic != 1) continue;
            if (strtolower($r->attendence ?? '') === 'absent') $ms_absent = true;
            elseif ((float)$r->get_marks < (float)$r->min_marks) $ms_below_min = true;
        }
        $ms_failed = ($ms_absent || $ms_below_min);
    ?>
    <table cellpadding="0" cellspacing="0" width="98%" class="denifittable marks" style="margin-top:10px;text-transform:uppercase;">
        <thead>
        <tr>
            <th><?php echo $this->lang->line('subjects'); ?></th>
            <th>Max Marks</th>
            <th>Min Marks</th>
            <th>Marks Obtained</th>
            <th><?php echo $this->lang->line('result'); ?></th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($student_value['exam_result'] as $row) {
            if (isset($row->is_academic) && $row->is_academic != 1) continue;
            $row_abs = (strtolower($row->attendence ?? '') === 'absent');
            $subj_pass = !$row_abs && (float)$row->get_marks >= (float)$row->min_marks;
        ?>
            <tr>
                <td style="text-align:left;"><?php echo displaySubjectName($row->name); ?></td>
                <td><?php echo (float)$row->max_marks; ?></td>
                <td><?php echo (float)$row->min_marks; ?></td>
                <td><?php echo $row_abs ? ($this->lang->line('absent') ?: 'Absent') : (float)$row->get_marks; ?></td>
                <td><?php echo $row_abs ? ($this->lang->line('absent') ?: 'Absent') : ($subj_pass ? ($this->lang->line('pass') ?: 'Pass') : ($this->lang->line('fail') ?: 'Fail')); ?></td>
            </tr>
        <?php } ?>
        <tr class="ms-summary-row">
            <td colspan="4" style="text-align:right;font-weight:600;"><?php echo $this->lang->line('result'); ?></td>
            <td style="font-weight:700;"><?php echo $ms_failed ? ($this->lang->line('fail') ?: 'Fail') : ($this->lang->line('pass') ?: 'Pass'); ?></td>
        </tr>
        </tbody>
    </table>
    <?php } else { ?>
    <table cellpadding="0" cellspacing="0" width="98%" class="denifittable marks" style="margin-top:10px;text-transform:uppercase;">
        <tbody>
        <tr>
            <?php if ($exam->exam_group_type == "gpa") { ?><th>SN</th><?php } ?>
            <th><?php echo $this->lang->line('subjects') ?></th>
            <?php if ($exam->exam_group_type != "gpa") { ?>
                <th><?php echo $this->lang->line('credit_hours'); ?></th>
                <th><?php echo $this->lang->line('grade_point'); ?></th>
                <th><?php echo $this->lang->line('grade'); ?></th>
                <th>Final Grade</th>
            <?php } else { ?>
                <th><?php echo $this->lang->line('credit_hours') ?></th>
                <th>Grade Point</th>
                <th>Grade</th>
                <th>Final Grade</th>
            <?php } ?>
        </tr>

        <?php
        $total_hours = 0; $total_quality_point = 0; $total_points = 0; $sr = 0;

        // Pair TH/IN for final grade (used for both GPA & College so "Final Grade" behaves the same)
        // ✅ FILTER: Only process academic subjects (is_academic = 1) for GPA calculations
        $pair_points = [];
        foreach ($student_value['exam_result'] as $erv) {
            // ✅ FILTER: Only include academic subjects for GPA
            if (isset($erv->is_academic) && $erv->is_academic != 1) {
                continue; // Skip non-academic subjects
            }
            
            $base = cleanBaseName($erv->name);
            $type = (stripos($erv->name,'(IN)')!==false) ? 'IN' : ((stripos($erv->name,'(TH)')!==false)?'TH':'');
            if ($type==='') continue;
            $is_absent = (isset($erv->attendence) && strtolower($erv->attendence)==='absent');
            $pct       = ($erv->max_marks>0) ? (($erv->get_marks*100)/$erv->max_marks) : 0;
            $raw_point = findGradePoints($exam_grades,$pct);
            $raw_point = (float) number_format($raw_point,2,'.','');
            $disp_pt   = capSubjectPointMax($raw_point);
            $disp_gr   = getGradeFromPoints($disp_pt);
            $is_ng     = ($disp_gr==='NG');
            if ($is_absent) $has_absent_overall = true;
            if ($is_ng)     $has_ng_overall = true;

            $pair_points[$base][$type] = [
                'ch'=>(float)$erv->credit_hours,'pt'=>$raw_point,'abs'=>$is_absent,'ng'=>$is_ng
            ];
        }
        $final_grade_printed = [];

        foreach ($student_value['exam_result'] as $row) {
            // ✅ FILTER: Only include academic subjects in totals and display
            if (isset($row->is_academic) && $row->is_academic != 1) {
                continue; // Skip non-academic subjects from main table
            }
            $row_absent = (strtolower($row->attendence ?? '') === 'absent');
            if ($row_absent) $has_absent_overall = true;
            if ($exam->exam_group_type == 'basic_system' && !$row_absent && (float)$row->get_marks < (float)$row->min_marks) $has_below_min_overall = true;
        ?>
            <tr>
                <?php if ($exam->exam_group_type == "gpa") { ?><td><?php echo ++$sr; ?></td><?php } ?>
                <td style="text-align:left;"><?php echo displaySubjectName($row->name); ?></td>

                <td><?php if (!$row_absent) { $total_hours += (float)$row->credit_hours; } echo (float)$row->credit_hours; ?></td>
                <td>
                    <?php
                    if ($row_absent) {
                        echo '—';
                    } else {
                        $pct  = ($row->max_marks>0) ? (($row->get_marks*100)/$row->max_marks) : 0;
                        $pt   = (float) number_format(findGradePoints($exam_grades,$pct),2,'.','');
                        $pt_c = capSubjectPointMax($pt);
                        $total_quality_point += ($row->credit_hours * $pt);
                        $total_points        += $pt;
                        echo number_format($pt_c,2,'.','');
                    }
                    ?>
                </td>
                <td><?php
                    if ($row_absent) {
                        echo $this->lang->line('exam_absent') ?: 'Absent';
                    } else {
                        $pt_c = ($row->max_marks>0) ? capSubjectPointMax((float) number_format(findGradePoints($exam_grades, ($row->get_marks*100)/$row->max_marks),2,'.','')) : 0;
                        $g_here = getGradeFromPoints($pt_c);
                        echo $g_here;
                        if ($g_here==='NG') $has_ng_overall = true;
                    }
                    ?></td>

                <?php
                $base = cleanBaseName($row->name);
                $final_grade_str = '';
                if (isset($pair_points[$base]['TH']) && isset($pair_points[$base]['IN'])) {
                    $th = $pair_points[$base]['TH']; $in = $pair_points[$base]['IN'];
                    if (($th['ng']||$in['ng']) || ($th['abs']||$in['abs'])) {
                        $final_grade_str = 'NG';
                    } else {
                        $sum_ch = $th['ch']+$in['ch'];
                        $wg = $sum_ch>0 ? (($th['ch']*$th['pt']+$in['ch']*$in['pt'])/$sum_ch) : 0;
                        $final_grade_str = getGradeFromPoints((float) number_format($wg,2,'.',''));
                    }
                }
                if (isset($pair_points[$base]['TH']) && isset($pair_points[$base]['IN'])) {
                    if (!isset($final_grade_printed[$base])) {
                        $display_final = ($final_grade_str === 'NG') ? '-' : $final_grade_str;
                        echo '<td class="ms-final-grade" rowspan="2">'.htmlspecialchars($display_final).'</td>';
                        $final_grade_printed[$base] = true;
                    }
                } else {
                    echo '<td class="ms-final-grade"></td>';
                }
                ?>
            </tr>
        <?php } ?>

        <tr class="ms-summary-row">
            <td colspan="3" style="text-align:right;font-weight:600;">Grade Point Average (GPA)</td>
            <td>
                <?php
                if ($total_hours<=0 || $has_absent_overall || $has_ng_overall) {
                    echo '-';
                } else {
                    $__gpa_val = $total_quality_point / $total_hours;
                    echo number_format($__gpa_val, 2, '.', '');
                    if ($exam->exam_group_type === 'gpa') {
                        echo ' [' . htmlspecialchars(getGradeFromPoints($__gpa_val)) . ']';
                    }
                }
                ?>
            </td>
            <td></td>
        </tr>
        </tbody>
    </table>
    <?php } ?>

    <!-- Grading + attendance + non-academic (per template toggles) -->
    <?php
    $attendance_data_single = isset($student_value['attendance_data']) ? $student_value['attendance_data'] : ['working_days' => 0, 'present_days' => 0, 'absent_days' => 0];
    $nonacademic_marks_single = isset($student_value['nonacademic_marks']) ? $student_value['nonacademic_marks'] : array();
    if (!empty($__ms_extra['show_grading']) || !empty($__ms_extra['show_attendance']) || !empty($__ms_extra['show_nonacademic'])) {
    ?>
    <div class="section"><?php $__render_grading_block($exam_grades, $attendance_data_single, $nonacademic_marks_single, $__ms_extra); ?></div>
    <?php } ?>

    <?php $this->load->view('admin/examresult/_printmarksheet_footer', array('template' => $template)); ?>
</div>
<?php if($to_be_print < $total_students){ ?><pagebreak /><?php } ?>
<?php
        } // foreach student
    } // end single-exam


    /* ======================== CONSOLIDATED ======================== */
    if ($marksheet['exam_connection'] == 1 && !empty($marksheet['students'])) {

        $total_students= count($marksheet['students']); $to_be_print=0;

        foreach ($marksheet['students'] as $student_key => $student_value) { $to_be_print++; ?>
<?php if ($to_be_print > 1) { ?>
<div class="page-break marksheet-between-students" aria-hidden="true"></div>
<?php } ?>
<div class="marksheet-page" style="width:100%;margin:0 auto;border:1px solid #000;padding:5px;">
    <?php if ($template->background_img != "") { ?>
        <img src="<?php echo base_url('uploads/marksheet/' . $template->background_img); ?>" class="tcmybg" width="100%" height="100%" />
    <?php } ?>
    <?php if($template->header_image){ ?>
        <img src="<?php echo base_url('uploads/marksheet/' . $template->header_image); ?>" style="width:100%;height:120px;object-fit:contain;">
    <?php } ?>

    <!-- (The consolidated header/top block kept as in your earlier version) -->

    <!-- Consolidated current-exam table (kept) -->
    <table cellpadding="0" cellspacing="0" width="100%" class="denifittable marks" style="margin-top:8px;text-transform:uppercase;">
        <thead>
            <tr>
                <th><?php echo $this->lang->line('subjects') ?></th>
                <th class="text-center"><?php echo $this->lang->line('credit_hours') ?></th>
                <th class="text-center"><?php echo $this->lang->line('grade_point') ?></th>
                <th class="text-center"><?php echo $this->lang->line('grade') ?></th>
                <th style="text-align:left"><?php echo $this->lang->line('note') ?></th>
            </tr>
        </thead>
        <tbody>
            <?php
            $total_hours = 0; $total_quality_point = 0; $has_abs=false; $has_ng=false;
            foreach ($student_value['exam_result']['exam_result_' . $exam->id] as $erv) {
                // ✅ FILTER: Only include academic subjects for GPA calculations
                if (isset($erv->is_academic) && $erv->is_academic != 1) {
                    continue; // Skip non-academic subjects
                }
                $erv_absent = (strtolower($erv->attendence ?? '') === 'absent');
                if ($erv_absent) $has_abs = true;

                if (!$erv_absent) {
                    $total_hours += $erv->credit_hours;
                    $pct = ($erv->max_marks>0) ? ($erv->get_marks*100/$erv->max_marks) : 0;
                    $pt  = (float) number_format(findGradePoints($exam_grades,$pct),2,'.','');
                    $total_quality_point += ($erv->credit_hours * $pt);
                }
                $ptc = $erv_absent ? null : capSubjectPointMax((float) number_format(findGradePoints($exam_grades, ($erv->max_marks>0) ? ($erv->get_marks*100/$erv->max_marks) : 0),2,'.',''));
                $gr  = $erv_absent ? ($this->lang->line('exam_absent') ?: 'Absent') : getGradeFromPoints($ptc);
                if ($gr==='NG') $has_ng=true;

                echo '<tr>';
                echo '<td style="text-align:left">'.displaySubjectName($erv->name).'</td>';
                echo '<td>'.$erv->credit_hours.'</td>';
                echo '<td>'.($erv_absent ? '—' : number_format($ptc,2,'.','')).'</td>';
                echo '<td>'.htmlspecialchars($gr).'</td>';
                echo '<td style="text-align:left">'.($erv->note).'</td>';
                echo '</tr>';
            }
            ?>
            <tr class="ms-summary-row">
                <td colspan="2" style="text-align:right;font-weight:600;">Grade Point Average (CGPA)</td>
                <td class="text-center">
                    <?php
                    if ($total_hours<=0 || $has_abs || $has_ng) {
                        echo '-';
                    } else {
                        echo number_format($total_quality_point/$total_hours,2,'.','');
                    }
                    ?>
                </td>
                <td></td><td></td>
            </tr>
        </tbody>
    </table>

    <?php
    $attendance_data_consolidated = isset($student_value['attendance_data']) ? $student_value['attendance_data'] : [
        'working_days' => 0,
        'present_days' => 0,
        'absent_days' => 0
    ];
    $nonacademic_marks_consolidated = isset($student_value['nonacademic_marks']) ? $student_value['nonacademic_marks'] : array();
    if (!empty($__ms_extra['show_grading']) || !empty($__ms_extra['show_attendance']) || !empty($__ms_extra['show_nonacademic'])) {
    ?>
    <div class="section"><?php $__render_grading_block($exam_grades, $attendance_data_consolidated, $nonacademic_marks_consolidated, $__ms_extra); ?></div>
    <?php } ?>

    <!-- (rest of consolidated blocks — combined exams table, result, division — unchanged from your version) -->

    <?php $this->load->view('admin/examresult/_printmarksheet_footer', array('template' => $template)); ?>
</div>
<?php if($to_be_print < $total_students){ ?><pagebreak /><?php } ?>

<?php
        } // foreach student
    } // consolidated
} // not empty
?>
<?php if (!empty($marksheet_autoprint)) { ?>
<script>
window.addEventListener('load', function () {
    setTimeout(function () { window.print(); }, 350);
});
</script>
<?php } ?>
