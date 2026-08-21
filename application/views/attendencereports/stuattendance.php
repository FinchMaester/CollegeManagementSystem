<?php
// Null-safe escaper for this view
if (!function_exists('e_safe')) {
    function e_safe($v) {
        return isset($v) ? htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8') : '';
    }
}
?>
<style type="text/css">
    /*REQUIRED*/
    .carousel-row { margin-bottom: 10px; }
    .slide-row { padding: 0; background-color: #ffffff; min-height: 150px; border: 1px solid #e7e7e7; overflow: hidden; height: auto; position: relative; }
    .slide-carousel { width: 20%; float: left; display: inline-block; }
    .slide-carousel .carousel-indicators { margin-bottom: 0; bottom: 0; background: rgba(0, 0, 0, .5); }
    .slide-carousel .carousel-indicators li { border-radius: 0; width: 20px; height: 6px; }
    .slide-carousel .carousel-indicators .active { margin: 1px; }
    .slide-content { position: absolute; top: 0; left: 20%; display: block; float: left; width: 80%; max-height: 76%; padding: 1.5% 2% 2% 2%; overflow-y: auto; }
    .slide-content h4 { margin-bottom: 3px; margin-top: 0; }
    .slide-footer { position: absolute; bottom: 0; left: 20%; width: 78%; height: 20%; margin: 1%; }
    /* Scrollbars */
    .slide-content::-webkit-scrollbar { width: 5px; }
    .slide-content::-webkit-scrollbar-thumb:vertical { margin: 5px; background-color: #999; -webkit-border-radius: 5px; }
    .slide-content::-webkit-scrollbar-button:start:decrement,
    .slide-content::-webkit-scrollbar-button:end:increment { height: 5px; display: block; }
</style>

<div class="content-wrapper" style="min-height: 946px;">
    <section class="content-header">
        <h1><i class="fa fa-bus"></i> <?php //echo $this->lang->line('transport'); ?></h1>
    </section>

    <section class="content">
        <?php $this->load->view('attendencereports/_attendance'); ?>
        <div class="row">
            <div class="col-md-12">
                <div class="box removeboxmius">
                    <div class="box-header ptbnull"></div>
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-search"></i> <?php echo $this->lang->line('select_criteria'); ?></h3>
                    </div>

                    <form role="form" action="<?php echo site_url('attendencereports/attendancereport') ?>" method="post" class="">
                        <div class="box-body row">
                            <?php echo $this->customlib->getCSRF(); ?>

                            <div class="col-sm-3 col-md-3">
                                <div class="form-group">
                                    <label><?php echo $this->lang->line('search_type'); ?></label>
                                    <select class="form-control" name="search_type" onchange="showdate(this.value)">
                                        <?php foreach ($searchlist as $key => $search) { ?>
                                            <option value="<?php echo $key ?>" <?php if ((isset($search_type)) && ($search_type == $key)) { echo "selected"; } ?>>
                                                <?php echo $search ?>
                                            </option>
                                        <?php } ?>
                                    </select>
                                    <span class="text-danger"><?php echo form_error('search_type'); ?></span>
                                </div>
                            </div>

                            <div id='date_result'></div>

                            <div class="col-sm-3 col-md-3">
                                <div class="form-group">
                                    <label><?php echo $this->lang->line('attendance_type'); ?></label><small class="req"> *</small>
                                    <select class="form-control" name="attendance_type">
                                        <option value=""><?php echo $this->lang->line('select') ?></option>
                                        <?php foreach ($attendance_type as $value) { ?>
                                            <option value="<?php echo $value['id'] ?>" <?php if ((isset($attendance_type_id)) && ($attendance_type_id == $value['id'])) { echo "selected"; } ?>>
                                                <?php echo $this->lang->line(strtolower($value['type'])) . " (" . $value['key_value'] . ")"; ?>
                                            </option>
                                        <?php } ?>
                                    </select>
                                    <span class="text-danger"><?php echo form_error('attendance_type'); ?></span>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="section_id"><?php echo $this->lang->line('faculty'); ?></label><small class="req"> *</small>
                                    <select autofocus id="section_id" name="section_id" class="form-control">
                                        <option value=""><?php echo $this->lang->line('select'); ?></option>
                                    </select>
                                    <span class="text-danger"><?php echo form_error('section_id'); ?></span>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="program_id"><?php echo $this->lang->line('program'); ?></label><small class="req"> *</small>
                                    <select id="program_id" name="program_id" class="form-control">
                                        <option value=""><?php echo $this->lang->line('select'); ?></option>
                                    </select>
                                    <span class="text-danger"><?php echo form_error('program_id'); ?></span>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="class_id"><?php echo $this->lang->line('academic_level'); ?></label><small class="req"> *</small>
                                    <select id="class_id" name="class_id" class="form-control">
                                        <option value=""><?php echo $this->lang->line('select'); ?></option>
                                    </select>
                                    <span class="text-danger"><?php echo form_error('class_id'); ?></span>
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="col-sm-12">
                                    <button type="submit" name="search" value="search_filter" class="btn btn-primary btn-sm checkbox-toggle pull-right">
                                        <i class="fa fa-search"></i> <?php echo $this->lang->line('search'); ?>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>

                    <div class="">
                        <div class="box-header ptbnull"></div>
                        <div class="box-header ptbnull">
                            <h3 class="box-title titlefix"><i class="fa fa-money"></i> <?php echo $this->lang->line('student_attendance_type_report'); ?> </h3>
                        </div>

                        <div class="box-body table-responsive">
                            <div class="download_label">
                                <?php
                                    echo $this->lang->line('student_attendance_type_report');
                                    $this->customlib->get_postmessage();
                                ?>
                            </div>

                            <table class="table table-striped table-bordered table-hover example">
                                <thead>
                                    <tr>
                                        <th><?php echo $this->lang->line('admission_no'); ?></th>
                                        <th><?php echo $this->lang->line('student_name'); ?></th>
                                        <th><?php echo $this->lang->line('class'); ?></th>
                                        <?php if ($sch_setting->father_name) { ?>
                                            <th><?php echo $this->lang->line('father_name'); ?></th>
                                        <?php } ?>
                                        <th><?php echo $this->lang->line('date_of_birth'); ?></th>
                                        <?php if ($sch_setting->admission_date) { ?>
                                            <th><?php echo $this->lang->line('admission_date'); ?></th>
                                        <?php } ?>
                                        <th><?php echo $this->lang->line('gender'); ?></th>
                                        <?php if ($sch_setting->mobile_no) { ?>
                                            <th><?php echo $this->lang->line('mobile_number'); ?></th>
                                        <?php } ?>
                                        <th><?php echo $this->lang->line('count'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    if (!empty($student_attendences)) {
                                        foreach ($student_attendences as $student) {
                                            // Dates: show Y-m-d if it’s already Y-m-d; else show raw safely
                                            $dob_raw = isset($student['dob']) ? trim($student['dob']) : '';
                                            $adm_raw = isset($student['admission_date']) ? trim($student['admission_date']) : '';

                                            $dob_out = '';
                                            if ($dob_raw) {
                                                $dob_out = preg_match('/^\d{4}-\d{2}-\d{2}$/', $dob_raw) ? date('Y-m-d', strtotime($dob_raw)) : e_safe($dob_raw);
                                            }

                                            $adm_out = '';
                                            if ($adm_raw) {
                                                $adm_out = preg_match('/^\d{4}-\d{2}-\d{2}$/', $adm_raw) ? date('Y-m-d', strtotime($adm_raw)) : e_safe($adm_raw);
                                            }

                                            // Gender (guard null)
                                            $gender_key = isset($student['gender']) ? strtolower((string)$student['gender']) : '';
                                            $gender_out = $gender_key ? $this->lang->line($gender_key) : '';
                                            ?>
                                            <tr>
                                                <td><?php echo e_safe($student['admission_no'] ?? ''); ?></td>
                                                <td>
                                                    <a href="<?php echo base_url(); ?>student/view/<?php echo (int)($student['id'] ?? 0); ?>">
                                                        <?php
                                                            // If you prefer to avoid Customlib here too, replace with manual concat:
                                                            echo $this->customlib->getFullName($student['firstname'] ?? '', $student['middlename'] ?? '', $student['lastname'] ?? '', $sch_setting->middlename, $sch_setting->lastname);
                                                        ?>
                                                    </a>
                                                </td>
                                                <td><?php echo e_safe(($student['class'] ?? '').' ('.($student['section'] ?? '').')'); ?></td>
                                                <?php if ($sch_setting->father_name) { ?>
                                                    <td><?php echo e_safe($student['father_name'] ?? ''); ?></td>
                                                <?php } ?>
                                                <td><?php echo $dob_out; ?></td>
                                                <?php if ($sch_setting->admission_date) { ?>
                                                    <td><?php echo $adm_out; ?></td>
                                                <?php } ?>
                                                <td><?php echo e_safe($gender_out); ?></td>
                                                <?php if ($sch_setting->mobile_no) { ?>
                                                    <td><?php echo e_safe($student['mobileno'] ?? ''); ?></td>
                                                <?php } ?>
                                                <td><?php echo (int)($student['total_type'] ?? 0); ?></td>
                                            </tr>
                                            <?php
                                        }
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>
            </div>
        </div>
</div>
</section>
</div>

<script>
    $(document).ready(function () {
        var section_id = '<?php echo set_value('section_id', isset($section_id) ? $section_id : ''); ?>';
        var program_id = '<?php echo set_value('program_id', isset($program_id) ? $program_id : ''); ?>';
        var class_id   = '<?php echo set_value('class_id', isset($class_id) ? $class_id : ''); ?>';

        $.ajax({
            url: '<?php echo base_url(); ?>sections/getAll',
            method: 'GET',
            dataType: 'json',
            success: function (response) {
                var html = '<option value=""><?php echo $this->lang->line('select'); ?></option>';
                $.each(response, function (index, section) {
                    var selected = (section_id == section.id) ? 'selected' : '';
                    html += '<option value="' + section.id + '" ' + selected + '>' + section.section + '</option>';
                });
                $('#section_id').html(html);
                if(section_id) { getProgramsBySection(section_id, program_id); }
            },
            error: function() {
                $('#section_id').html('<option value="">Error loading sections</option>');
            }
        });

        if (class_id && section_id && program_id) {
            getClassesByProgram(program_id, class_id);
        }

        $(document).on('change', '#section_id', function () {
            $('#program_id').html('<option value=""><?php echo $this->lang->line('select'); ?></option>');
            $('#class_id').html('<option value=""><?php echo $this->lang->line('select'); ?></option>');
            var section_id = $(this).val();
            if (section_id) { getProgramsBySection(section_id); }
        });

        $(document).on('change', '#program_id', function () {
            $('#class_id').html('<option value=""><?php echo $this->lang->line('select'); ?></option>');
            var program_id = $(this).val();
            if (program_id) { getClassesByProgram(program_id); }
        });

        function getProgramsBySection(section_id, selected_program_id = null) {
            if (section_id != "") {
                var base_url = '<?php echo base_url() ?>';
                var div_data = '<option value=""><?php echo $this->lang->line('select'); ?></option>';
                $.ajax({
                    type: "POST",
                    url: base_url + "admin/timetable/getProgramsBySection",
                    data: {'section_id': section_id},
                    dataType: "json",
                    success: function (data) {
                        $.each(data, function (i, obj) {
                            var sel = (selected_program_id == obj.id) ? "selected" : "";
                            div_data += "<option value=" + obj.id + " " + sel + ">" + obj.title + (obj.code && obj.code !== obj.title ? " (" + obj.code + ")" : "") + "</option>";
                        });
                        $('#program_id').html(div_data);
                        if (selected_program_id) { getClassesByProgram(selected_program_id, class_id); }
                    },
                    error: function() {
                        $('#program_id').html('<option value=""><?php echo $this->lang->line('select'); ?></option>');
                    }
                });
            }
        }

        function getClassesByProgram(program_id, selected_class_id = null) {
            if (program_id != "") {
                var base_url = '<?php echo base_url() ?>';
                var div_data = '<option value=""><?php echo $this->lang->line('select'); ?></option>';
                $.ajax({
                    type: "POST",
                    url: base_url + "admin/timetable/getClassesByProgram",
                    data: {'program_id': program_id},
                    dataType: "json",
                    success: function (data) {
                        $.each(data, function (i, obj) {
                            var sel = (selected_class_id == obj.id) ? "selected" : "";
                            div_data += "<option value=" + obj.id + " " + sel + ">" + obj.class + " - " + obj.degree + "</option>";
                        });
                        $('#class_id').html(div_data);
                    },
                    error: function() {
                        $('#class_id').html('<option value=""><?php echo $this->lang->line('select'); ?></option>');
                    }
                });
            }
        }
    });
</script>
