<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Dynamic subject table for marksheet (used when template marksheet_display_mode = dynamic).
 * Expects functions from parent: displaySubjectName, findGradePoints, capSubjectPointMax, getGradeFromPoints, findGrade.
 *
 * @var object $template
 * @var object $exam
 * @var array  $exam_grades
 * @var array  $student_value
 * @var array  $marksheet_layout_columns
 */

$cols = marksheet_visible_columns(isset($marksheet_layout_columns) ? $marksheet_layout_columns : array());
if (empty($cols)) {
    $cols = marksheet_visible_columns(marksheet_layout_default_columns());
}

$grading_mode = 'inherit';
if (!empty($template->grading_system_mode) && $template->grading_system_mode !== 'inherit') {
    $grading_mode = $template->grading_system_mode;
} else {
    $grading_mode = isset($exam->exam_group_type) ? $exam->exam_group_type : 'coll_grade_system';
}

$is_basic = ($grading_mode === 'basic_system' || $grading_mode === 'pass_fail');
$results = isset($student_value['exam_result']) ? $student_value['exam_result'] : array();

$academic_rows = array();
foreach ($results as $r) {
    if (isset($r->is_academic) && $r->is_academic != 1) {
        continue;
    }
    $academic_rows[] = $r;
}

$sn = 0;
$total_hours = 0;
$total_quality_point = 0;
$has_absent_overall = false;
$has_ng_overall = false;
$ms_failed = false;

if ($is_basic) {
    foreach ($academic_rows as $r) {
        if (strtolower($r->attendence ?? '') === 'absent') {
            $ms_failed = true;
            break;
        }
        if ((float) $r->get_marks < (float) $r->min_marks) {
            $ms_failed = true;
            break;
        }
    }
}

$basic_obtained_idx = -1;
foreach ($cols as $___i => $___c) {
    if (isset($___c['id']) && $___c['id'] === 'obtained_marks') {
        $basic_obtained_idx = (int) $___i;
        break;
    }
}
if ($basic_obtained_idx < 0) {
    $basic_obtained_idx = max(0, count($cols) - 1);
}
$basic_total_obtained = 0;
foreach ($academic_rows as $___r) {
    if (strtolower($___r->attendence ?? '') === 'absent') {
        continue;
    }
    $basic_total_obtained += (float) $___r->get_marks;
}

?>
<table cellpadding="0" cellspacing="0" width="98%" class="denifittable marks" style="margin-top:10px;text-transform:uppercase;">
    <thead>
    <tr>
        <?php foreach ($cols as $c) { ?>
            <th><?php echo htmlspecialchars($c['label'], ENT_QUOTES, 'UTF-8'); ?></th>
        <?php } ?>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($academic_rows as $row) {
        $row_absent = (strtolower($row->attendence ?? '') === 'absent');
        if ($row_absent) {
            $has_absent_overall = true;
        }
        if (!$is_basic && !$row_absent) {
            $pct = ($row->max_marks > 0) ? (($row->get_marks * 100) / $row->max_marks) : 0;
            $pt  = (float) number_format(findGradePoints($exam_grades, $pct), 2, '.', '');
            $total_hours += (float) $row->credit_hours;
            $total_quality_point += ((float) $row->credit_hours * $pt);
        }
        if (!$is_basic && !$row_absent) {
            $pctg = ($row->max_marks > 0) ? (($row->get_marks * 100) / $row->max_marks) : 0;
            $ptc = capSubjectPointMax((float) number_format(findGradePoints($exam_grades, $pctg), 2, '.', ''));
            if (getGradeFromPoints($ptc) === 'NG') {
                $has_ng_overall = true;
            }
        }
        $sn++;
        ?>
        <tr>
        <?php foreach ($cols as $c) {
            $id = $c['id'];
            ob_start();
            switch ($id) {
                case 'sn':
                    echo $sn;
                    break;
                case 'subject_name':
                    echo htmlspecialchars(displaySubjectName($row->name), ENT_QUOTES, 'UTF-8');
                    break;
                case 'full_marks':
                    echo (float) $row->max_marks;
                    break;
                case 'pass_marks':
                    echo (float) $row->min_marks;
                    break;
                case 'credit_hours':
                    echo (float) $row->credit_hours;
                    break;
                case 'obtained_marks':
                    echo $row_absent ? ($this->lang->line('absent') ?: 'Absent') : (float) $row->get_marks;
                    break;
                case 'result_pf':
                    if ($row_absent) {
                        echo $this->lang->line('absent') ?: 'Absent';
                    } else {
                        $subj_pass = (float) $row->get_marks >= (float) $row->min_marks;
                        echo $subj_pass ? ($this->lang->line('pass') ?: 'Pass') : ($this->lang->line('fail') ?: 'Fail');
                    }
                    break;
                case 'grade_point':
                    if ($row_absent) {
                        echo '—';
                    } else {
                        $pct = ($row->max_marks > 0) ? (($row->get_marks * 100) / $row->max_marks) : 0;
                        $pt  = (float) number_format(findGradePoints($exam_grades, $pct), 2, '.', '');
                        echo number_format(capSubjectPointMax($pt), 2, '.', '');
                    }
                    break;
                case 'letter_grade':
                case 'grade_letter':
                    if ($row_absent) {
                        echo $this->lang->line('exam_absent') ?: 'Absent';
                    } else {
                        $pct = ($row->max_marks > 0) ? (($row->get_marks * 100) / $row->max_marks) : 0;
                        $pt  = (float) number_format(findGradePoints($exam_grades, $pct), 2, '.', '');
                        $pt_c = capSubjectPointMax($pt);
                        echo htmlspecialchars(getGradeFromPoints($pt_c), ENT_QUOTES, 'UTF-8');
                    }
                    break;
                case 'final_grade':
                    echo '—';
                    break;
                case 'viva_marks':
                case 'project_marks':
                    echo '—';
                    break;
                case 'remarks':
                    echo htmlspecialchars((string) (isset($row->note) ? $row->note : ''), ENT_QUOTES, 'UTF-8');
                    break;
                default:
                    echo (strpos((string) $id, 'custom_') === 0) ? '—' : '';
            }
            $cell = ob_get_clean();
            ?>
            <td style="text-align:center;"><?php echo $cell; ?></td>
        <?php } ?>
        </tr>
    <?php } ?>

    <?php
    $__cc = count($cols);
    if ($is_basic) {
        $__io = (int) $basic_obtained_idx;
        $__sum_pass = $ms_failed
            ? ($this->lang->line('marksheet_failed') ?: ($this->lang->line('fail') ?: 'Failed'))
            : ($this->lang->line('marksheet_passed') ?: ($this->lang->line('pass') ?: 'Passed'));
        if ($__cc <= 1) { ?>
        <tr class="ms-summary-row">
            <td style="text-align:center;font-weight:700;"><?php echo $this->lang->line('total_marks'); ?>: <?php echo (float) $basic_total_obtained; ?> — <?php echo $this->lang->line('result'); ?>: <?php echo htmlspecialchars($__sum_pass, ENT_QUOTES, 'UTF-8'); ?></td>
        </tr>
        <?php } else { ?>
        <tr class="ms-summary-row">
            <?php if ($__io > 0) { ?>
            <td colspan="<?php echo $__io; ?>" style="text-align:right;font-weight:600;"><?php echo $this->lang->line('total_marks'); ?></td>
            <?php } else { ?>
            <td style="text-align:right;font-weight:600;"><?php echo $this->lang->line('total_marks'); ?></td>
            <?php } ?>
            <td style="font-weight:700;"><?php echo (float) $basic_total_obtained; ?></td>
            <?php for ($___j = $__io + 1; $___j < $__cc; $___j++) { ?>
            <td></td>
            <?php } ?>
        </tr>
        <tr class="ms-summary-row">
            <?php if ($__io > 0) { ?>
            <td colspan="<?php echo $__io; ?>" style="text-align:right;font-weight:600;"><?php echo $this->lang->line('result'); ?></td>
            <?php } else { ?>
            <td style="text-align:right;font-weight:600;"><?php echo $this->lang->line('result'); ?></td>
            <?php } ?>
            <td style="font-weight:700;"><?php echo htmlspecialchars($__sum_pass, ENT_QUOTES, 'UTF-8'); ?></td>
            <?php for ($___j = $__io + 1; $___j < $__cc; $___j++) { ?>
            <td></td>
            <?php } ?>
        </tr>
        <?php }
    } else { ?>
        <tr class="ms-summary-row">
            <?php if ($__cc <= 1) { ?>
            <td style="text-align:center;">GPA: <?php
                if ($total_hours <= 0 || $has_absent_overall || $has_ng_overall) {
                    echo '-';
                } else {
                    $__gpa_val = $total_quality_point / $total_hours;
                    echo number_format($__gpa_val, 2, '.', '');
                    if (isset($exam->exam_group_type) && $exam->exam_group_type === 'gpa') {
                        echo ' [' . htmlspecialchars(getGradeFromPoints($__gpa_val), ENT_QUOTES, 'UTF-8') . ']';
                    }
                }
            ?></td>
            <?php } else { ?>
            <td colspan="<?php echo (int) ($__cc - 1); ?>" style="text-align:right;font-weight:600;">Grade Point Average (GPA)</td>
            <td>
                <?php
                if ($total_hours <= 0 || $has_absent_overall || $has_ng_overall) {
                    echo '-';
                } else {
                    $__gpa_val = $total_quality_point / $total_hours;
                    echo number_format($__gpa_val, 2, '.', '');
                    if (isset($exam->exam_group_type) && $exam->exam_group_type === 'gpa') {
                        echo ' [' . htmlspecialchars(getGradeFromPoints($__gpa_val), ENT_QUOTES, 'UTF-8') . ']';
                    }
                }
                ?>
            </td>
            <?php } ?>
        </tr>
    <?php } ?>
    </tbody>
</table>
