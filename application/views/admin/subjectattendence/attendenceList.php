<style type="text/css">
    .radio {
        padding-left: 20px;}
    .radio label {
        display: inline-block;
        vertical-align: middle;
        position: relative;
        padding-left: 5px; }
    .radio label::before {
        content: "";
        display: inline-block;
        position: absolute;
        width: 17px;
        height: 17px;
        left: 0;
        margin-left: -20px;
        border: 1px solid #cccccc;
        border-radius: 50%;
        background-color: #fff;
        -webkit-transition: border 0.15s ease-in-out;
        -o-transition: border 0.15s ease-in-out;
        transition: border 0.15s ease-in-out; }
    .radio label::after {
        display: inline-block;
        position: absolute;
        content: " ";
        width: 11px;
        height: 11px;
        left: 3px;
        top: 3px;
        margin-left: -20px;
        border-radius: 50%;
        background-color: #555555;
        -webkit-transform: scale(0, 0);
        -ms-transform: scale(0, 0);
        -o-transform: scale(0, 0);
        transform: scale(0, 0);
        -webkit-transition: -webkit-transform 0.1s cubic-bezier(0.8, -0.33, 0.2, 1.33);
        -moz-transition: -moz-transform 0.1s cubic-bezier(0.8, -0.33, 0.2, 1.33);
        -o-transition: -o-transform 0.1s cubic-bezier(0.8, -0.33, 0.2, 1.33);
        transition: transform 0.1s cubic-bezier(0.8, -0.33, 0.2, 1.33); }
    .radio input[type="radio"] {
        opacity: 0;
        z-index: 1; }
    .radio input[type="radio"]:focus + label::before {
        outline: thin dotted;
        outline: 5px auto -webkit-focus-ring-color;
        outline-offset: -2px; }
    .radio input[type="radio"]:checked + label::after {
        -webkit-transform: scale(1, 1);
        -ms-transform: scale(1, 1);
        -o-transform: scale(1, 1);
        transform: scale(1, 1); }
    .radio input[type="radio"]:disabled + label {
        opacity: 0.65; }
    .radio input[type="radio"]:disabled + label::before {
        cursor: not-allowed; }
    .radio.radio-inline {
        margin-top: 0; }
    .radio-primary input[type="radio"] + label::after {
        background-color: #337ab7; }
    .radio-primary input[type="radio"]:checked + label::before {
        border-color: #337ab7; }
    .radio-primary input[type="radio"]:checked + label::after {
        background-color: #337ab7; }
    .radio-danger input[type="radio"] + label::after {
        background-color: #d9534f; }
    .radio-danger input[type="radio"]:checked + label::before {
        border-color: #d9534f; }
    .radio-danger input[type="radio"]:checked + label::after {
        background-color: #d9534f; }
    .radio-info input[type="radio"] + label::after {
        background-color: #5bc0de; }
    .radio-info input[type="radio"]:checked + label::before {
        border-color: #5bc0de; }
    .radio-info input[type="radio"]:checked + label::after {
        background-color: #5bc0de; }
    @media (max-width:767px){
        .radio.radio-inline {display: inherit;}
    }
</style>

<?php
$language1      = $this->customlib->getLanguage();
$language_name1 = $language1["short_code"];
?>
<div class="content-wrapper" style="min-height: 946px;">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1><i class="fa fa-calendar-check-o"></i> <?php echo $this->lang->line('attendance'); ?> <small><?php echo $this->lang->line('by_date1'); ?></small></h1>
    </section>
    <!-- Main content -->
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-search"></i> <?php echo $this->lang->line('select_criteria'); ?></h3>
                    </div>
                    <form id='form1' action="<?php echo site_url('admin/subjectattendence') ?>"  method="post" accept-charset="utf-8">
                        <div class="box-body">
                            <?php
if ($this->session->flashdata('msg')) {
    echo $this->session->flashdata('msg');
    $this->session->unset_userdata('msg');
}
?>
                            <?php echo $this->customlib->getCSRF(); ?>
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="exampleInputEmail1"><?php echo $this->lang->line('faculty'); ?></label><small class="req"> *</small>
                                        <select autofocus="" id="section_id" name="section_id" class="form-control" >
                                            <option value=""><?php echo $this->lang->line('select'); ?></option>
                                            <?php
if (isset($sectionlist) && !empty($sectionlist)) {
    foreach ($sectionlist as $section) {
        ?>
                                                <option value="<?php echo $section['id'] ?>" <?php echo set_select('section_id', $section['id'], set_value('section_id')); ?>><?php echo $section['section'] ?></option>
                                                <?php
    }
}
?>
                                        </select>
                                        <span class="text-danger"><?php echo form_error('section_id'); ?></span>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="exampleInputEmail1"><?php echo $this->lang->line('program'); ?></label><small class="req"> *</small>
                                        <select  id="program_id" name="program_id" class="form-control" >
                                            <option value=""><?php echo $this->lang->line('select'); ?></option>
                                        </select>
                                        <span class="text-danger"><?php echo form_error('program_id'); ?></span>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="exampleInputEmail1"><?php echo $this->lang->line('academic_level'); ?></label><small class="req"> *</small>
                                        <select  id="class_id" name="class_id" class="form-control" >
                                            <option value=""><?php echo $this->lang->line('select'); ?></option>
                                        </select>
                                        <span class="text-danger"><?php echo form_error('class_id'); ?></span>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="exampleInputEmail1">
                                            <?php echo $this->lang->line('date'); ?>
                                        </label><small class="req"> *</small>
                                        <input id="date" name="date" placeholder="" type="text" class="form-control date-bs nepali-datepicker"  value="<?php echo set_value('date'); ?>" data-nepali-date="true" autocomplete="off"/>
                                        <span class="text-danger"><?php echo form_error('date'); ?></span>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="">
                                            <?php echo $this->lang->line('subject'); ?>
                                        </label><small class="req"> *</small>
                                        <select  id="subject_timetable_id" name="subject_timetable_id" class="form-control" >
                                            <option value=""><?php echo $this->lang->line('select'); ?></option>
                                        </select>
                                        <p id="subject_timetable_period_hint" class="help-block text-info small" style="display: none; margin-top: 8px;"
                                           data-msg-day="<?php echo htmlspecialchars($this->lang->line('subject_attendance_no_periods_this_day'), ENT_QUOTES, 'UTF-8'); ?>"
                                           data-msg-general="<?php echo htmlspecialchars($this->lang->line('subject_attendance_no_periods_general'), ENT_QUOTES, 'UTF-8'); ?>"
                                           data-msg-error="<?php echo htmlspecialchars($this->lang->line('subject_attendance_load_subjects_failed'), ENT_QUOTES, 'UTF-8'); ?>"></p>
                                        <span class="text-danger"><?php echo form_error('subject_timetable_id'); ?></span>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <button type="submit" name="search" value="search" class="btn btn-primary btn-sm pull-right checkbox-toggle"><i class="fa fa-search"></i> <?php echo $this->lang->line('search'); ?></button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                    <?php
if (isset($resultlist)) {
    ?>
                        <div class="">
                            <div class="box-header ptbnull"></div>
                            <div class="box-header with-border">
                                <h3 class="box-title"><i class="fa fa-users"></i> <?php echo $this->lang->line('student_list'); ?></h3>
                                <div class="box-tools pull-right">
                                </div>
                            </div>
                            <div class="box-body">
                                <?php
// Show flashdata message if available (after redirect)
if ($this->session->flashdata('msg')) {
    echo $this->session->flashdata('msg');
    $this->session->unset_userdata('msg');
}

if (!empty($resultlist)) {
        $checked  = "";
        $can_edit = 1;
        if (!isset($msg)) {
            if ($resultlist[0]['attendence_type_id'] != "") {
                if ($resultlist[0]['attendence_type_id'] != 5) {
                    if ($this->rbac->hasPrivilege('student_attendance', 'can_edit')) {
                        $can_edit = 1;
                    } else {
                        $can_edit = 0;
                    }
                    ?>
                                                <div class="alert alert-success"><?php echo $this->lang->line('attendance_already_submitted_you_can_edit_record'); ?></div>
                                                <?php
} else {
                    $checked = "checked='checked'";
                    ?>
                                                <div class="alert alert-warning"><?php echo $this->lang->line('attendance_already_submitted_as_holiday'); ?>. <?php echo $this->lang->line('you_can_edit_record'); ?></div>
                                                <?php
}
            }
        }
        ?>
                                    <form action="<?php echo site_url('admin/subjectattendence/index') ?>" method="post" class="form_attendence">
                                        <?php echo $this->customlib->getCSRF(); ?>
                                        <div class="mailbox-controls">
                                            <span class="button-checkbox">
                                                <?php if ($this->rbac->hasPrivilege('student_attendance', 'can_add')) {?>
                                                    <button type="button" class="btn btn-sm btn-primary" data-color="primary"><?php echo $this->lang->line('mark_as_holiday'); ?></button>
                                                    <input type="checkbox" id="checkbox1" class="hidden" name="holiday" value="checked" <?php echo $checked; ?>/>
                                                </span>
                                                <div class="pull-right">
                                                    <?php
}
        if ($can_edit == 1) {
            if ($this->rbac->hasPrivilege('student_attendance', 'can_add')) {
                ?>
                                                        <button type="submit" name="search" value="saveattendence" class="btn btn-primary btn-sm pull-right checkbox-toggle"><i class="fa fa-save"></i> <?php echo $this->lang->line('save_attendance'); ?> </button>
                                                    <?php }
        }
        ?>
                                            </div>
                                        </div>
                                        <input type="hidden" name="class_id" value="<?php echo $class_id; ?>">
                                        <input type="hidden" name="section_id" value="<?php echo $section_id; ?>">
                                        <input type="hidden" name="program_id" value="<?php echo isset($program_id) ? $program_id : ''; ?>">
                                        <input type="hidden" name="subject_timetable_id" value="<?php echo $subject_timetable_id; ?>">
                                        <input type="hidden" name="date" value="<?php echo $date; ?>">
                                        <div class="table-responsive ptt10">
                                            <div class="download_label">
                                          <?php echo $this->lang->line('student_list'); ?>
                                            </div>
                                            <table class="table table-hover table-striped example">
                                                <thead>
                                                    <tr>
                                                        <th>#</th>
                                                        <th><?php echo $this->lang->line('admission_no'); ?></th>
                                                        <th><?php echo $this->lang->line('roll_number'); ?></th>
                                                        <th><?php echo $this->lang->line('name'); ?></th>
                                                        <th class=""><?php echo $this->lang->line('attendance'); ?></th>
                                                        <th><?php echo $this->lang->line('note'); ?></th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php
$row_count = 1;
        foreach ($resultlist as $key => $value) {
            ?>
                                                        <tr>
                                                            <td>
                                                                <input type="hidden" name="student_session[]" value="<?php echo $value['student_session_id']; ?>">
                                                                <input  type="hidden" value="<?php echo $value['student_subject_attendance_id']; ?>"  name="attendance_id<?php echo $value['student_session_id']; ?>">
            <?php echo $row_count; ?>
                                                            </td>
                                                            <td>
            <?php echo $value['admission_no']; ?>
                                                            </td>
                                                            <td>
            <?php echo $value['roll_no']; ?>
                                                            </td>
                                                            <td>
           <?php
echo $this->customlib->getFullName($value['firstname'], $value['middlename'], $value['lastname'], $sch_setting->middlename, $sch_setting->lastname);
            ?>
                                                            </td>
                                                            <td>
                                                                <?php
$c     = 1;
            $count = 0;
            foreach ($attendencetypeslist as $key => $type) {
                if ($type['key_value'] != "H") {
                    $att_type = str_replace(" ", "_", strtolower($type['type']));
                    if ($value['date'] != "xxx") {
                        ?>
                                                                            <div class="radio radio-info radio-inline">
                                                                                <input <?php if ($value['attendence_type_id'] == $type['id']) {
                            echo "checked";
                        }
                        ?> type="radio" id="attendencetype<?php echo $value['student_session_id'] . "-" . $count; ?>" value="<?php echo $type['id'] ?>" name="attendencetype<?php echo $value['student_session_id']; ?>" >
                                                                                <label for="attendencetype<?php echo $value['student_session_id'] . "-" . $count; ?>">
                        <?php echo $this->lang->line($att_type); ?>
                                                                                </label>
                                                                            </div>
                                                                            <?php
} else {
                        ?>
                                                                            <div class="radio radio-info radio-inline">
                                                                                <input <?php if ($c == 1) {
                            echo "checked";
                        }
                        ?> type="radio" id="attendencetype<?php echo $value['student_session_id'] . "-" . $count; ?>" value="<?php echo $type['id'] ?>" name="attendencetype<?php echo $value['student_session_id']; ?>" >
                                                                                <label for="attendencetype<?php echo $value['student_session_id'] . "-" . $count; ?>">
                        <?php echo $this->lang->line($att_type); ?>
                                                                                </label>
                                                                            </div>
                                                                            <?php
}
                    $c++;
                    $count++;
                }
            }
            ?>
                                                            </td>
                                                            <?php if ($date == 'xxx') {?>
                                                                <td><input type="text" name="remark<?php echo $value["student_session_id"] ?>" ></td>
                                                            <?php } else {?>
                                                                <td><input type="text" name="remark<?php echo $value["student_session_id"] ?>" value="<?php echo $value["remark"]; ?>" ></td>
                                                        <?php }?>
                                                        </tr>
                                                        <?php
$row_count++;
        }
        ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </form>
                                    <?php
} else {
        ?>
                                    <div class="alert alert-info"><?php echo $this->lang->line('admited_alert'); ?></div>
                                    <?php
}
    ?>
                            </div>
                        </div>
                    </div>
                    <?php
}
?>
                </section>
            </div>
            
            <script type="text/javascript">
                $(document).ready(function () {
                    var section_id_post = "<?php echo isset($section_id) ? $section_id : set_value('section_id'); ?>";
                    var program_id_post = "<?php echo isset($program_id) ? $program_id : set_value('program_id'); ?>";
                    var class_id_post = "<?php echo isset($class_id) ? $class_id : set_value('class_id'); ?>";
                    var date_post = "<?php echo isset($date) ? $date : set_value('date'); ?>";
                    var subject_timetable_id = "<?php echo isset($subject_timetable_id) ? $subject_timetable_id : set_value('subject_timetable_id', ''); ?>";
                    
                    // Prevent English datepicker from attaching to Nepali date fields
                    var $dateField = $('#date');
                    if ($dateField.length > 0) {
                        // Remove any existing datepicker classes
                        $dateField.removeClass('date');
                        
                        // Use capture phase to intercept focusin before footer.php delegate
                        $dateField[0].addEventListener('focusin', function(e) {
                            e.stopPropagation();
                            e.stopImmediatePropagation();
                        }, true);
                        
                        // Also prevent on the form itself
                        $('#form1').on('focusin', '#date', function(e) {
                            e.stopPropagation();
                        });
                    }
                    
                    // Helper function to get date value reliably
                    function getDateValue() {
                        var $dateField = $('#date');
                        var dateValue = $dateField.val();
                        
                        // Try multiple methods to get the date
                        if (!dateValue || dateValue === '') {
                            dateValue = $dateField[0] ? $dateField[0].value : '';
                        }
                        if (!dateValue || dateValue === '') {
                            dateValue = $dateField.attr('value');
                        }
                        
                        // Try to get from Nepali datepicker instance
                        try {
                            var datepickerInstance = $dateField.data('nepaliDatePicker');
                            if (datepickerInstance && datepickerInstance.selectedDate && datepickerInstance.selectedDate.bs) {
                                dateValue = datepickerInstance.selectedDate.bs;
                            }
                        } catch(err) {
                            console.log('Error reading Nepali datepicker:', err);
                        }
                        
                        return dateValue || '';
                    }
                    
                    // If there's a pre-selected section, load programs
                    if(section_id_post) {
                        getProgramsBySection(section_id_post, program_id_post);
                    }
                    
                    // Faculty/Section change event
                    $(document).on('change', '#section_id', function (e) {
                        if (typeof subjectPeriodHint === 'function') {
                            subjectPeriodHint(false);
                        }
                        $('#program_id').html('<option value=""><?php echo $this->lang->line('select'); ?></option>');
                        $('#class_id').html('<option value=""><?php echo $this->lang->line('select'); ?></option>');
                        $('#subject_timetable_id').html('<option value=""><?php echo $this->lang->line('select'); ?></option>');
                        var section_id = $(this).val();
                        if (section_id) {
                            getProgramsBySection(section_id);
                        }
                    });
                    
                    // Program change event
                    $(document).on('change', '#program_id', function (e) {
                        if (typeof subjectPeriodHint === 'function') {
                            subjectPeriodHint(false);
                        }
                        $('#class_id').html('<option value=""><?php echo $this->lang->line('select'); ?></option>');
                        $('#subject_timetable_id').html('<option value=""><?php echo $this->lang->line('select'); ?></option>');
                        var program_id = $(this).val();
                        var section_id = $('#section_id').val();
                        if (program_id && section_id) {
                            getClassesByProgram(section_id, program_id);
                        }
                    });
                    
                    // Academic Level change event - fetch subjects immediately (date not required)
                    $(document).on('change', '#class_id', function (e) {
                        if (typeof subjectPeriodHint === 'function') {
                            subjectPeriodHint(false);
                        }
                        $('#subject_timetable_id').html('<option value=""><?php echo $this->lang->line('select'); ?></option>');
                        var class_id = $(this).val();
                        var section_id = $('#section_id').val();
                        var date = getDateValue(); // Optional - only used for timetable filtering
                        if (class_id && section_id) {
                            getSubjects(class_id, section_id, date, 0);
                        }
                    });
                    
                    function getProgramsBySection(section_id, selected_program_id = null) {
                        $('#program_id').html('<option value="">Loading...</option>');
                        $.ajax({
                            url: baseurl + 'programs/getBySection',
                            method: 'POST',
                            data: {
                                section_id: section_id,
                                '<?php echo $this->security->get_csrf_token_name(); ?>': '<?php echo $this->security->get_csrf_hash(); ?>'
                            },
                            dataType: 'json',
                            success: function (response) {
                                var html = '<option value=""><?php echo $this->lang->line('select'); ?></option>';
                                if(response && response.length > 0) {
                                    $.each(response, function (index, program) {
                                        var selected = (selected_program_id == program.id) ? 'selected' : '';
                                        html += '<option value="' + program.id + '" ' + selected + '>' + program.title + '</option>';
                                    });
                                }
                                $('#program_id').html(html);
                                
                                // If there's a pre-selected program, load classes
                                if(selected_program_id) {
                                    getClassesByProgram(section_id, selected_program_id, class_id_post);
                                }
                            },
                            error: function(xhr, status, error) {
                                console.error('Error loading programs:', error);
                                $('#program_id').html('<option value="">Error loading programs</option>');
                            }
                        });
                    }
                    
                    function getClassesByProgram(section_id, program_id, selected_class_id = null) {
                        $('#class_id').html('<option value="">Loading...</option>');
                        $.ajax({
                            url: baseurl + 'classes/getBySectionAndProgram',
                            method: 'POST',
                            data: {
                                section_id: section_id,
                                program_id: program_id,
                                '<?php echo $this->security->get_csrf_token_name(); ?>': '<?php echo $this->security->get_csrf_hash(); ?>'
                            },
                            dataType: 'json',
                            success: function (response) {
                                var html = '<option value=""><?php echo $this->lang->line('select'); ?></option>';
                                if(response && response.length > 0) {
                                    $.each(response, function (index, classItem) {
                                        var display_text = classItem.class;
                                        if (classItem.degree) {
                                            display_text = classItem.degree + ' - ' + classItem.class;
                                        }
                                        var selected = (selected_class_id == classItem.id) ? 'selected' : '';
                                        html += '<option value="' + classItem.id + '" ' + selected + '>' + display_text + '</option>';
                                    });
                                }
                                $('#class_id').html(html);
                                
                                // If there's a pre-selected class and date, load subjects
                                if(selected_class_id && date_post) {
                                    getSubjects(selected_class_id, section_id, date_post, subject_timetable_id);
                                }
                            },
                            error: function(xhr, status, error) {
                                console.error('Error loading academic levels:', error);
                                $('#class_id').html('<option value="">Error loading academic levels</option>');
                            }
                        });
                    }

                    function subjectPeriodHint(show, mode) {
                        var $h = $('#subject_timetable_period_hint');
                        if (!$h.length) {
                            return;
                        }
                        if (!show) {
                            $h.hide().text('');
                            return;
                        }
                        var text = '';
                        if (mode === 'day') {
                            text = $h.attr('data-msg-day') || '';
                        } else if (mode === 'error') {
                            text = $h.attr('data-msg-error') || '';
                        } else {
                            text = $h.attr('data-msg-general') || '';
                        }
                        $h.text(text).show();
                    }

                    function getSubjects(class_id, section_id, date, subject_timetable_id) {
                        // Date is optional - subjects are fetched based on Faculty, Program, and Level
                        if (section_id != "" && class_id != "") {
                            subjectPeriodHint(false);
                            $('#subject_timetable_id').html('<option value="">Loading...</option>');
                            var dateTrimmed = date ? String(date).trim() : '';
                            $.ajax({
                                type: "POST",
                                url: baseurl + "admin/subjectgroup/getSubjectByClassandSectionDate",
                                data: {
                                    'class_id': class_id, 
                                    'section_id': section_id, 
                                    'date': date || '', // Optional - only used for timetable filtering
                                    '<?php echo $this->security->get_csrf_token_name(); ?>': '<?php echo $this->security->get_csrf_hash(); ?>'
                                },
                                dataType: "json",
                                success: function (data) {
                                    var div_data = '<option value=""><?php echo $this->lang->line('select'); ?></option>';
                                    var added = 0;
                                    if (data && data.error) {
                                        console.error('getSubjectByClassandSectionDate:', data.error);
                                        $('#subject_timetable_id').html('<option value=""><?php echo $this->lang->line('select'); ?></option>');
                                        subjectPeriodHint(true, 'error');
                                        return;
                                    }
                                    if (data && data.length > 0) {
                                        $.each(data, function (i, obj) {
                                            var tid = parseInt(obj.id, 10);
                                            if (!tid || tid <= 0) {
                                                return;
                                            }
                                            var select = "";
                                            if (String(subject_timetable_id) === String(tid)) {
                                                select = "selected=selected";
                                            }
                                            div_data += "<option value=\"" + tid + "\" " + select + ">" + obj.subject_name + "</option>";
                                            added++;
                                        });
                                    }
                                    $('#subject_timetable_id').html(div_data);
                                    if (added === 0) {
                                        subjectPeriodHint(true, dateTrimmed !== '' ? 'day' : 'general');
                                    } else {
                                        subjectPeriodHint(false);
                                    }
                                },
                                error: function(xhr, status, error) {
                                    console.error('Error loading subjects:', error);
                                    console.error('Response:', xhr.responseText);
                                    $('#subject_timetable_id').html('<option value=""><?php echo $this->lang->line('select'); ?></option>');
                                    subjectPeriodHint(true, 'error');
                                }
                            });
                        }
                    }
                    
                    // Initialize Nepali datepicker
                    if (typeof $.fn.nepaliDatePicker !== 'undefined') {
                        var $dateField = $('#date');
                        
                        // Set initial value if provided
                        if (date_post && date_post.trim() !== '') {
                            $dateField.val(date_post);
                        }
                        
                        $dateField.nepaliDatePicker({
                            ndpTriggerButton: false,
                            dateFormat: 'YYYY-MM-DD',
                            ndpYear: true,
                            ndpMonth: true,
                            ndpYearCount: 10,
                            readOnlyInput: false,
                            ndpTrigger: 'click',
                            onChange: function(selectedDate) {
                                if (selectedDate && selectedDate.bs) {
                                    $dateField.val(selectedDate.bs);
                                    
                                    // Refresh subjects when date changes (to filter by timetable day)
                                    var class_id = $('#class_id').val();
                                    var section_id = $('#section_id').val();
                                    var date = selectedDate.bs;
                                    if (class_id && section_id) {
                                        getSubjects(class_id, section_id, date, 0);
                                    }
                                }
                            }
                        });
                    } else {
                        console.error('Nepali datepicker not available');
                    }
                });

                $(function () {
                    $('.button-checkbox').each(function () {
                        var $widget = $(this),
                                $button = $widget.find('button'),
                                $checkbox = $widget.find('input:checkbox'),
                                color = $button.data('color'),
                                settings = {
                                    on: {
                                        icon: 'glyphicon glyphicon-check'
                                    },
                                    off: {
                                        icon: 'glyphicon glyphicon-unchecked'
                                    }
                                };
                        $button.on('click', function () {
                            $checkbox.prop('checked', !$checkbox.is(':checked'));
                            $checkbox.triggerHandler('change');
                            updateDisplay();
                        });
                        $checkbox.on('change', function () {
                            updateDisplay();
                        });

                        function updateDisplay() {
                            var isChecked = $checkbox.is(':checked');
                            $button.data('state', (isChecked) ? "on" : "off");
                            $button.find('.state-icon')
                                    .removeClass()
                                    .addClass('state-icon ' + settings[$button.data('state')].icon);
                            if (isChecked) {
                                $button
                                        .removeClass('btn-success')
                                        .addClass('btn-' + color + ' active');
                            } else {
                                $button
                                        .removeClass('btn-' + color + ' active')
                                        .addClass('btn-primary');
                            }
                        }

                        function init() {
                            updateDisplay();
                            if ($button.find('.state-icon').length == 0) {
                                $button.prepend('<i class="state-icon ' + settings[$button.data('state')].icon + '"></i> ');
                            }
                        }
                        init();
                    });
                });

                $('#checkbox1').change(function () {
                    if (this.checked) {
                        var returnVal = confirm("<?php echo $this->lang->line('are_you_sure'); ?>");
                        $(this).prop("checked", returnVal);
                        $("input[type=radio]").attr('disabled', true);

                    } else {
                        $("input[type=radio]").attr('disabled', false);
                        $("input[type=radio][value='1']").attr("checked", "checked");

                    }
                });

                $('form.form_attendence').on('submit', function (e) {
                    $(this).submit(function () {
                        return false;
                    });
                    return true;
                });
            </script>