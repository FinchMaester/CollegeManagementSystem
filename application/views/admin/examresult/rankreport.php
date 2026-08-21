<?php error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT & ~E_NOTICE); ?>
<div class="content-wrapper" style="min-height: 946px;">
    <section class="content-header">
        <h1>
            <i class="fa fa-map-o"></i> <?php echo $this->lang->line('examinations'); ?>
            <small><?php echo $this->lang->line('student_fee1'); ?></small>
        </h1>
    </section>

    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">

                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-search"></i> <?php echo $this->lang->line('select_criteria'); ?></h3>
                    </div>

                    <div class="box-body">
                        <form role="form" action="<?php echo site_url('admin/examresult') ?>" method="post">
                            <?php echo $this->customlib->getCSRF(); ?>
                            <div class="row">
                                <div class="col-sm-6 col-lg-3 col-md-3 col20">
                                    <div class="form-group">
                                        <label><?php echo $this->lang->line('exam_group'); ?></label><small class="req"> *</small>
                                        <select autofocus id="exam_group_id" name="exam_group_id" class="form-control">
                                            <option value=""><?php echo $this->lang->line('select'); ?></option>
                                            <?php foreach ($examgrouplist as $ex_group_value): ?>
                                                <option value="<?php echo $ex_group_value->id ?>" <?php echo set_value('exam_group_id') == $ex_group_value->id ? 'selected' : '' ?>>
                                                    <?php echo $ex_group_value->name; ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <span class="text-danger"><?php echo form_error('exam_group_id'); ?></span>
                                    </div>
                                </div>

                                <div class="col-sm-6 col-lg-3 col-md-3 col20">
                                    <div class="form-group">
                                        <label><?php echo $this->lang->line('exam') ?></label><small class="req"> *</small>
                                        <select id="exam_id" name="exam_id" class="form-control">
                                            <option value=""><?php echo $this->lang->line('select'); ?></option>
                                        </select>
                                        <span class="text-danger"><?php echo form_error('exam_id'); ?></span>
                                    </div>
                                </div>

                                <div class="col-sm-6 col-lg-3 col-md-3 col20">
                                    <div class="form-group">
                                        <label><?php echo $this->lang->line('session'); ?></label><small class="req"> *</small>
                                        <select id="session_id" name="session_id" class="form-control">
                                            <option value=""><?php echo $this->lang->line('select'); ?></option>
                                            <?php foreach ($sessionlist as $session): ?>
                                                <option value="<?php echo $session['id'] ?>" <?php echo set_value('session_id') == $session['id'] ? 'selected' : '' ?>>
                                                    <?php echo $session['session'] ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <span class="text-danger"><?php echo form_error('session_id'); ?></span>
                                    </div>
                                </div>

                                <div class="col-sm-6 col-lg-3 col-md-12 col20">
                                    <div class="form-group">
                                        <label><?php echo $this->lang->line('section'); ?></label><small class="req"> *</small>
                                        <select id="section_id" name="section_id" class="form-control">
                                            <option value=""><?php echo $this->lang->line('select'); ?></option>
                                        </select>
                                        <span class="text-danger"><?php echo form_error('section_id'); ?></span>
                                    </div>
                                </div>

                                <div class="col-sm-6 col-lg-3 col-md-12 col20">
                                    <div class="form-group">
                                        <label><?php echo $this->lang->line('program'); ?></label><small class="req"> *</small>
                                        <select id="program_id" name="program_id" class="form-control">
                                            <option value=""><?php echo $this->lang->line('select'); ?></option>
                                        </select>
                                        <span class="text-danger"><?php echo form_error('program_id'); ?></span>
                                    </div>
                                </div>

                                <div class="col-sm-6 col-lg-3 col-md-12 col20">
                                    <div class="form-group">
                                        <label><?php echo $this->lang->line('class'); ?></label><small class="req"> *</small>
                                        <select id="class_id" name="class_id" class="form-control">
                                            <option value=""><?php echo $this->lang->line('select'); ?></option>
                                        </select>
                                        <span class="text-danger" id="error_class_id"></span>
                                    </div>
                                </div>

                                <div class="col-sm-12">
                                    <div class="form-group">
                                        <button type="submit" name="search" value="search_filter" class="btn btn-primary pull-right btn-sm checkbox-toggle">
                                            <i class="fa fa-search"></i> <?php echo $this->lang->line('search'); ?>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>

<?php if (isset($studentList)) : ?>

<?php
/* ===================== helpers (robust ranges + safe pct) ===================== */
function _in_range($p, $a, $b) {
    $low  = min((float)$a, (float)$b);
    $high = max((float)$a, (float)$b);
    // include both ends and allow tiny float wiggle room
    return ($p + 1e-9) >= $low && ($p - 1e-9) <= $high;
}
function _pct($got, $max) {
    $max = (float)$max;
    if ($max <= 0) return null;                 // cannot grade
    return ((float)$got * 100.0) / $max;
}
function getSubjectMarks($subject_results, $subject_id) {
    if (!empty($subject_results)) foreach ($subject_results as $r) if ($subject_id == $r->subject_id) return $r;
    return false;
}
function get_ExamGrade($exam_grades, $percentage) {
    if ($percentage === null) return '-';
    if (!empty($exam_grades)) {
        foreach ($exam_grades as $g) {
            if (_in_range($percentage, $g->mark_from, $g->mark_upto)) return $g->name;
        }
    }
    return '-';
}
function findGradePoints($exam_grades, $percentage) {
    if ($percentage === null) return 0;
    if (!empty($exam_grades)) {
        foreach ($exam_grades as $g) {
            if (_in_range($percentage, $g->mark_from, $g->mark_upto)) return isset($g->point) ? (float)$g->point : 0.0;
        }
    }
    return 0;
}
function grade_from_points($p) {
    if ($p >= 3.61) return 'A+';
    if ($p >= 3.21) return 'A';
    if ($p >= 2.81) return 'B+';
    if ($p >= 2.41) return 'B';
    if ($p >= 2.01) return 'C+';
    if ($p >= 1.61) return 'C';
    if ($p >= 1.21) return 'D';
    return 'NG';
}

/* ===================== precompute flags + ranks ===================== */
$__flags  = [];     // student_id => ['absent'=>bool, 'failed'=>bool]
$__totals_ok = [];  // totals included in rank
$__exam_type = isset($exam_details->exam_group_type) ? (string)$exam_details->exam_group_type : '';
$__passing_percentage = isset($exam_details->passing_percentage) ? (float)$exam_details->passing_percentage : 0.0;

if (!empty($studentList) && !empty($subjectList)) {
    foreach ($studentList as $__sv) {
        $sid = $__sv->student_id;
        $sum = 0.0; $max_total = 0.0;
        $any_absent = false; $any_ng = false; $any_below_min = false;

        foreach ($subjectList as $__subj) {
            $res = getSubjectMarks($__sv->subject_results, $__subj->subject_id);
            if (!$res) continue;
            $max_total += (float)$__subj->max_marks;

            if (strtolower((string)$res->attendence) === 'absent') { $any_absent = true; continue; }

            $sum += (float)$res->get_marks;

            $perc  = _pct($res->get_marks, $__subj->max_marks);
            $grade = get_ExamGrade($exam_grades, $perc);
            if (strtoupper($grade) === 'NG') $any_ng = true;
            if ($__exam_type === 'basic_system' && (float)$res->get_marks < (float)$__subj->min_marks) $any_below_min = true;
        }

        $overall_pct = ($max_total > 0) ? (($sum * 100.0) / $max_total) : 0.0;
        if ($__exam_type === 'gpa') {
            $failed = ($any_absent || $any_ng);
        } elseif ($__exam_type === 'average_passing') {
            $failed = ($any_absent || $overall_pct < $__passing_percentage);
        } elseif ($__exam_type === 'basic_system') {
            $failed = ($any_absent || $any_below_min);
        } else {
            $failed = ($any_absent || $any_ng);
        }

        $__flags[$sid] = ['absent' => $any_absent, 'failed' => $failed];
        if (!$failed) $__totals_ok[$sid] = $sum;
    }
    arsort($__totals_ok, SORT_NUMERIC);
    $__ranks=[]; $pos=0; $rank=0; $prev=null;
    foreach ($__totals_ok as $sid=>$tot) { $pos++; if ($prev===null || $tot < $prev) $rank=$pos; $__ranks[$sid]=$rank; $prev=$tot; }
}
?>

                        <form method="post" action="<?php echo base_url('admin/examresult/printmarksheet') ?>" id="printMarksheet">
                            <input type="hidden" name="marksheet_template" value="<?php echo $marksheet_template; ?>">
                            <div class="">
                                <div class="box-header ptbnull"></div>
                                <div class="box-header ptbnull">
                                    <h3 class="box-title titlefix"><i class="fa fa-users"></i> <?php echo $this->lang->line('exam_result'); ?></h3>
                                </div>
                                <div class="box-body">
                                    <input type="hidden" name="post_exam_id" value="<?php echo $exam_id; ?>">
                                    <input type="hidden" name="post_exam_group_id" value="<?php echo $exam_group_id; ?>">

                                    <div class="tab-pane active table-responsive no-padding" id="tab_1">
                                        <div class="download_label"><?php echo $this->lang->line('exam_result'); ?></div>

                                        <table class="table table-striped table-bordered table-hover example" cellspacing="0" width="100%">
                                            <thead>
                                                <tr>
                                                    <th><?php echo $this->lang->line('admission_no'); ?></th>
                                                    <th><?php echo $this->lang->line('symbol_number') ?: 'Symbol Number'; ?></th>
                                                    <th><?php echo $this->lang->line('roll_number'); ?></th>
                                                    <th><?php echo $this->lang->line('student_name'); ?></th>

<?php if (!empty($subjectList)): ?>
    <?php foreach ($subjectList as $subject_value): ?>
        <th>
            <?php
                echo htmlspecialchars(preg_replace('/\s*\(\d+\)\s*$/', '', (string)$subject_value->subject_name));
                echo "<br/>(".$this->lang->line('marks')." / ".$this->lang->line('grade').")";
            ?>
        </th>
    <?php endforeach; ?>
<?php endif; ?>

                                                    <th><?php echo $this->lang->line('rank') ?></th>
                                                    <th><?php echo $this->lang->line('result') ?></th>
                                                </tr>
                                            </thead>

                                            <tbody>
<?php if (!empty($studentList)): ?>
    <?php foreach ($studentList as $student_value): ?>
        <?php
            $sid = $student_value->student_id;
            $student_absent = !empty($__flags[$sid]['absent']);
            $student_failed = !empty($__flags[$sid]['failed']);
        ?>
        <tr>
            <td><?php echo $student_value->admission_no; ?></td>
            <td><?php echo !empty($student_value->symbol_no) ? htmlspecialchars($student_value->symbol_no) : ""; ?></td>
            <td><?php echo ($student_value->roll_no != 0) ? htmlspecialchars($student_value->roll_no) : ""; ?></td>
            <td>
                <a href="<?php echo base_url(); ?>student/view/<?php echo $student_value->student_id; ?>">
                    <?php echo $this->customlib->getFullName($student_value->firstname, $student_value->middlename, $student_value->lastname, $sch_setting->middlename, $sch_setting->lastname); ?>
                </a>
            </td>

            <?php
                $total_marks = 0; $get_marks = 0;
                $total_credit_hour = 0; $total_quality_point = 0;
            ?>

            <?php foreach ($subjectList as $subject_value): ?>
                <?php
                    $total_marks += (float)$subject_value->max_marks;
                    $result = getSubjectMarks($student_value->subject_results, $subject_value->subject_id);
                ?>
                <td>
                    <?php if ($result): ?>
                        <?php if (strtolower((string)$result->attendence) === 'absent'): ?>
                            <span class="text-danger"><?php echo $this->lang->line('absent') ?: 'Absent'; ?></span>
                        <?php else: ?>
                            <?php
                                $get_marks += (float)$result->get_marks;

                                $perc  = _pct($result->get_marks, $subject_value->max_marks);   // null if max=0
                                $grade = get_ExamGrade($exam_grades, $perc);

                                if ($exam_details->exam_group_type == "gpa") {
                                    $gp = findGradePoints($exam_grades, $perc);
                                    $total_credit_hour   += (float)$subject_value->credit_hours;
                                    $total_quality_point += ($gp * (float)$subject_value->credit_hours);
                                }

                                echo number_format((float)$result->get_marks, 2, '.', '') . " (" . htmlspecialchars($grade) . ")";
                            ?>
                        <?php endif; ?>
                    <?php else: ?>
                        --
                    <?php endif; ?>
                </td>
            <?php endforeach; ?>

            <td><?php echo (!$student_failed && isset($__ranks[$sid])) ? $__ranks[$sid] : ""; ?></td>

            <td>
                <?php
                    if ($student_absent) {
                        echo $this->lang->line('absent') ?: 'Absent';
                    } elseif ($student_failed) {
                        echo $this->lang->line('fail') ?: 'Fail';
                    } elseif ($exam_details->exam_group_type === 'basic_system') {
                        echo $this->lang->line('pass') ?: 'Pass';
                    } else {
                        if ($exam_details->exam_group_type === 'gpa') {
                            if ($total_credit_hour > 0) {
                                $final_gpa   = $total_quality_point / $total_credit_hour;
                                $final_grade = grade_from_points($final_gpa);
                                echo number_format($final_gpa, 2, '.', '') . " [" . $final_grade . "]";
                            } else {
                                echo "";
                            }
                        } else {
                            $overall_pct = ($total_marks > 0) ? (($get_marks * 100) / $total_marks) : null;
                            $gp          = findGradePoints($exam_grades, $overall_pct);
                            $grade       = get_ExamGrade($exam_grades, $overall_pct);
                            echo number_format((float)$gp, 2, '.', '') . " [" . htmlspecialchars($grade) . "]";
                        }
                    }
                ?>
            </td>
        </tr>
    <?php endforeach; ?>
<?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </form>

<?php endif; // isset($studentList) ?>

                </div>
            </div>
        </div>
    </section>
</div>

<script type="text/javascript">
$(document).ready(function () {
    $('.select2').select2();
    $.extend($.fn.dataTable.defaults, { searching:true, ordering:true, paging:false, retrieve:true, destroy:true, info:false });

    var selectedSection = '<?php echo set_value("section_id") ?>';
    var selectedProgram = '<?php echo set_value("program_id") ?>';
    var selectedClass   = '<?php echo set_value("class_id") ?>';

    loadSections();

    function loadSections(){
        $.ajax({
            url: '<?php echo base_url(); ?>sections/getAll',
            method:'GET', dataType:'json',
            success: function(res){
                var html = '<option value=""><?php echo $this->lang->line("select"); ?></option>';
                $.each(res || [], function(_, s){
                    var sel = (selectedSection == s.id) ? 'selected' : '';
                    html += '<option value="'+s.id+'" '+sel+'>'+s.section+'</option>';
                });
                $('#section_id').html(html);
                if (selectedSection) loadPrograms(selectedSection);
            }
        });
    }

    function loadPrograms(sectionId){
        if(!sectionId) return;
        $('#program_id').html('<option value="">Loading...</option>');
        $('#class_id').html('<option value=""><?php echo $this->lang->line("select"); ?></option>');

        $.ajax({
            url:'<?php echo base_url(); ?>programs/getBySection',
            method:'POST',
            data:{ section_id: sectionId, <?php echo $this->security->get_csrf_token_name(); ?>:'<?php echo $this->security->get_csrf_hash(); ?>' },
            dataType:'json',
            success:function(res){
                var html = '<option value=""><?php echo $this->lang->line("select"); ?></option>';
                if (res && res.length){
                    $.each(res, function(_, p){
                        var sel = (selectedProgram == p.id) ? 'selected' : '';
                        html += '<option value="'+p.id+'" '+sel+'>'+p.title+'</option>';
                    });
                } else {
                    html += '<option value="" disabled>No programs found</option>';
                }
                $('#program_id').html(html);
                if (selectedProgram) loadClasses(sectionId, selectedProgram);
            }
        });
    }

    function loadClasses(sectionId, programId){
        if(!sectionId || !programId) return;
        $('#class_id').html('<option value="">Loading...</option>');
        $.ajax({
            url:'<?php echo base_url(); ?>classes/getBySectionAndProgram',
            method:'POST',
            data:{ section_id: sectionId, program_id: programId, <?php echo $this->security->get_csrf_token_name(); ?>:'<?php echo $this->security->get_csrf_hash(); ?>' },
            dataType:'json',
            success:function(res){
                var html = '<option value=""><?php echo $this->lang->line("select"); ?></option>';
                if(res && res.length){
                    $.each(res, function(_, c){
                        var sel = (selectedClass == c.id) ? 'selected' : '';
                        html += '<option value="'+c.id+'" '+sel+'>'+(c.degree? c.degree+' - ':'')+c.class+'</option>';
                    });
                } else {
                    html += '<option value="" disabled>No classes found for this combination</option>';
                }
                $('#class_id').html(html);
            }
        });
    }

    $('#section_id').on('change', function(){
        selectedSection = $(this).val(); selectedProgram=''; selectedClass='';
        if (selectedSection) { loadPrograms(selectedSection); }
        else {
            $('#program_id').html('<option value=""><?php echo $this->lang->line("select"); ?></option>');
            $('#class_id').html('<option value=""><?php echo $this->lang->line("select"); ?></option>');
        }
    });

    $('#program_id').on('change', function(){
        selectedProgram = $(this).val();
        selectedClass='';
        var sectionId = $('#section_id').val();
        if (sectionId && selectedProgram) loadClasses(sectionId, selectedProgram);
        else $('#class_id').html('<option value=""><?php echo $this->lang->line("select"); ?></option>');
    });

    var exam_group_id = '<?php echo set_value('exam_group_id') ?>';
    var exam_id = '<?php echo set_value('exam_id') ?>';
    getExamByExamgroup(exam_group_id, exam_id);

    $(document).on('change', '#exam_group_id', function(){
        $('#exam_id').html("");
        getExamByExamgroup($(this).val(), 0);
    });

    function getExamByExamgroup(exam_group_id, exam_id){
        if (!exam_group_id) return;
        $('#exam_id').html("");
        var base_url = '<?php echo base_url() ?>';
        var opts = '<option value=""><?php echo $this->lang->line('select'); ?></option>';
        $.ajax({
            type:"POST", url: base_url+"admin/examgroup/getExamByExamgroup",
            data:{ exam_group_id: exam_group_id }, dataType:"json",
            beforeSend: function(){ $('#exam_id').addClass('dropdownloading'); },
            success: function(data){
                $.each(data || [], function(i, obj){
                    var sel = (exam_id == obj.id) ? 'selected' : '';
                    opts += "<option value='"+obj.id+"' "+sel+">"+obj.exam+"</option>";
                });
                $('#exam_id').append(opts);
            },
            complete: function(){ $('#exam_id').removeClass('dropdownloading'); }
        });
    }
});
</script>
