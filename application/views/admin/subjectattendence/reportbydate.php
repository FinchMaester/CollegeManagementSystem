<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            <i class="fa fa-calendar-check-o"></i> <?php echo $this->lang->line('attendance'); ?> <small><?php echo $this->lang->line('by_date1'); ?></small></h1>
    </section>
    <!-- Main content -->
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-search"></i> <?php echo $this->lang->line('select_criteria'); ?></h3>
                    </div>
                    <form id='form1' action="<?php echo site_url('admin/subjectattendence/reportbydate') ?>"  method="post" accept-charset="utf-8">
                        <div class="box-body">
                            <?php
if ($this->session->flashdata('msg')) {
    echo $this->session->flashdata('msg');
    $this->session->unset_userdata('msg');
}
?>
                            <?php echo $this->customlib->getCSRF(); ?>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="exampleInputEmail1"><?php echo $this->lang->line('faculty'); ?></label><small class="req"> *</small>
                                        <select autofocus="" id="section_id" name="section_id" class="form-control" >
                                            <option value=""><?php echo $this->lang->line('select'); ?></option>
                                            <?php
if (isset($sectionlist) && !empty($sectionlist)) {
    foreach ($sectionlist as $section) {
        ?>
                                                <option value="<?php echo $section['id'] ?>" <?php
        if (set_value('section_id') == $section['id']) {
            echo "selected =selected";
        }
        ?>><?php echo $section['section'] ?></option>
                                                        <?php
    }
}
?>
                                        </select>
                                        <span class="text-danger"><?php echo form_error('section_id'); ?></span>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="exampleInputEmail1"><?php echo $this->lang->line('program'); ?></label><small class="req"> *</small>
                                        <select  id="program_id" name="program_id" class="form-control" >
                                            <option value=""><?php echo $this->lang->line('select'); ?></option>
                                        </select>
                                        <span class="text-danger"><?php echo form_error('program_id'); ?></span>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="exampleInputEmail1"><?php echo $this->lang->line('academic_level'); ?></label><small class="req"> *</small>
                                        <select  id="class_id" name="class_id" class="form-control" >
                                            <option value=""><?php echo $this->lang->line('select'); ?></option>
                                        </select>
                                        <span class="text-danger"><?php echo form_error('class_id'); ?></span>
                                    </div>
                                </div>
                                <div class="col-md-4">
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
                                        <button type="submit" name="search" value="search" class="btn btn-primary btn-sm pull-right checkbox-toggle"><i class="fa fa-search"></i> <?php echo $this->lang->line('search'); ?></button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>

                    <?php
if (isset($resultlist) || isset($attendance_report)) {
    ?>
                        <div class="">
                            <div class="box-header ptbnull"></div>
                            <div class="box-header with-border">
                                <h3 class="box-title"><i class="fa fa-users"></i> <?php echo $this->lang->line('student'); ?> <?php echo $this->lang->line('list'); ?></h3>
                                <div class="box-tools pull-right">
                                </div>
                            </div>
                            <div class="box-body">
                              <div class="table-responsive">
                                <?php
        $student_result = null;
        if (!empty($attendance_report) && is_array($attendance_report) && !empty($attendance_report['subjects'])) {
            $student_result = new stdClass();
            $student_result->subjects = $attendance_report['subjects'];
            $student_result->student_record = isset($attendance_report['student_record']) && is_array($attendance_report['student_record'])
                ? $attendance_report['student_record'] : array();
        } elseif (!empty($resultlist)) {
            $student_result = json_decode($resultlist);
        }

        if ($student_result && !empty($student_result->subjects)) {

        if (!function_exists('reportbydate_subject_prop')) {
            function reportbydate_subject_prop($row, $key)
            {
                if (is_array($row)) {
                    return array_key_exists($key, $row) ? $row[$key] : null;
                }
                if (is_object($row)) {
                    return property_exists($row, $key) ? $row->$key : null;
                }

                return null;
            }
        }
        ?>
                                    <table class="table table-hover table stripped">
                                        <thead>
                                            <tr>
                                                <th>Student</th>
                                                <?php
foreach ($student_result->subjects as $subject_key => $subject_value) {
            ?>
                                                    <th class="text text-center">
                                                        <?php
// Debug: Check what we have
$code_raw = reportbydate_subject_prop($subject_value, 'code');
$sub_code = ($code_raw !== null && trim((string) $code_raw) !== '') ? ' (' . trim((string) $code_raw) . ')' : '';

            // Get subject name — model may set name, subject_name, or code; tolerate array or object rows from JSON
            $subject_name = '';
            $sn = reportbydate_subject_prop($subject_value, 'subject_name');
            $nm = reportbydate_subject_prop($subject_value, 'name');
            $cd = reportbydate_subject_prop($subject_value, 'code');
            $sid = reportbydate_subject_prop($subject_value, 'subject_id');

            if ($sn !== null && trim((string) $sn) !== '' && trim((string) $sn) !== '-') {
                $subject_name = trim((string) $sn);
            } elseif ($nm !== null && trim((string) $nm) !== '' && trim((string) $nm) !== '-') {
                $subject_name = trim((string) $nm);
            } elseif ($cd !== null && trim((string) $cd) !== '') {
                $subject_name = trim((string) $cd);
            } elseif ($sid !== null && (int) $sid > 0) {
                $subject_name = 'Subject ID: ' . (int) $sid;
            } else {
                $subject_name = 'Unknown Subject';
            }

            echo htmlspecialchars($subject_name) . $sub_code;
            echo "<br/>";
            $tf = reportbydate_subject_prop($subject_value, 'time_from');
            $tt = reportbydate_subject_prop($subject_value, 'time_to');
            if ($tf !== null && trim((string) $tf) !== '' && $tt !== null && trim((string) $tt) !== '') {
                echo htmlspecialchars(trim((string) $tf)) . " - " . htmlspecialchars(trim((string) $tt));
            }
            ?>

                                                    </th>
                                                    <?php
}
        ?>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
foreach ($student_result->student_record as $students_key => $students_value) {
            if (!is_object($students_value)) {
                continue;
            }
            ?>
                                                <tr>
                                                    <td><?php echo $this->customlib->getFullName($students_value->firstname, $students_value->middlename, $students_value->lastname, $sch_setting->middlename, $sch_setting->lastname) . " (" . $students_value->admission_no . ")"; ?></td>
                                                    <?php
for ($i = 1; $i <= count($student_result->subjects); $i++) {
                ?>
                                                        <td class="text text-center"><?php
$attendance_field = "attendence_type_id_" . $i;
                $attendance_value = isset($students_value->{$attendance_field}) ? $students_value->{$attendance_field} : null;
                if ($attendance_value == "" || $attendance_value === null || $attendance_value === false) {
                    // Show "-" instead of "N/A" as requested
                    ?>
                                                                <span>-</span>
                                                                <?php
} else {
                    echo getattendencetype($attendencetypeslist, $attendance_value);
                }
                ?></td>
                                                        <?php
}
            ?>
                                                </tr>
                                                <?php
}
        ?>
                                        </tbody>
                                    </table>
                                    <?php
} else {
        ?>
                                    <div class="alert alert-info"><?php echo $this->lang->line('admited_alert') ?></div>
                                    <?php
}
    ?>
                            </div>
                         </div>
                        </div>
                    </div>
                    <?php
}
?>
                </section>
            </div>
            <?php

function getattendencetype($attendencetype, $find)
{
    foreach ($attendencetype as $attendencetype_key => $attendencetype_value) {
        if ($attendencetype_value['id'] == $find) {
            return $attendencetype_value['key_value'];
        }
    }
    return false;
}
?>

            <script type="text/javascript">
                    console.log('=== REPORTBYDATE DEBUG: Script starting ===');
                    $(document).ready(function () {
                        console.log('=== REPORTBYDATE DEBUG: Document ready ===');
                        var section_id_post = "<?php echo set_value('section_id'); ?>";
                        var program_id_post = "<?php echo set_value('program_id'); ?>";
                        var class_id_post = "<?php echo set_value('class_id'); ?>";
                        var date_post = "<?php echo set_value('date'); ?>";
                        
                        console.log('=== REPORTBYDATE DEBUG: Initial values ===');
                        console.log('section_id_post:', section_id_post);
                        console.log('program_id_post:', program_id_post);
                        console.log('class_id_post:', class_id_post);
                        console.log('date_post:', date_post);
                    
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
                    
                    // Form submit handler with detailed logging
                    $('#form1').on('submit', function(e) {
                        console.log('=== REPORTBYDATE DEBUG: Form submit triggered ===');
                        var formData = {
                            section_id: $('#section_id').val(),
                            program_id: $('#program_id').val(),
                            class_id: $('#class_id').val(),
                            date: getDateValue()
                        };
                        console.log('=== REPORTBYDATE DEBUG: Form data being submitted ===');
                        console.log('Form Data:', formData);
                        
                        // Don't prevent default - let form submit normally
                    });
                    
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
                    
                    // Load all faculties (sections) on page load
                    console.log('=== REPORTBYDATE DEBUG: Loading faculties ===');
                    
                    // If there's a pre-selected section, load programs
                    if(section_id_post) {
                        console.log('=== REPORTBYDATE DEBUG: Pre-selected section found, loading programs ===');
                        getProgramsBySection(section_id_post, program_id_post);
                    }
                    
                    // Faculty/Section change event
                    $(document).on('change', '#section_id', function (e) {
                        $('#program_id').html('<option value=""><?php echo $this->lang->line('select'); ?></option>');
                        $('#class_id').html('<option value=""><?php echo $this->lang->line('select'); ?></option>');
                        var section_id = $(this).val();
                        if (section_id) {
                            getProgramsBySection(section_id);
                        }
                    });
                    
                    // Program change event
                    $(document).on('change', '#program_id', function (e) {
                        $('#class_id').html('<option value=""><?php echo $this->lang->line('select'); ?></option>');
                        var program_id = $(this).val();
                        var section_id = $('#section_id').val();
                        if (program_id && section_id) {
                            getClassesByProgram(section_id, program_id);
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
                            },
                            error: function(xhr, status, error) {
                                console.error('Error loading academic levels:', error);
                                $('#class_id').html('<option value="">Error loading academic levels</option>');
                            }
                        });
                    }
                    
                    // Initialize Nepali datepicker
                    console.log('=== REPORTBYDATE DEBUG: Initializing Nepali datepicker ===');
                    console.log('$.fn.nepaliDatePicker available:', typeof $.fn.nepaliDatePicker !== 'undefined');
                    if (typeof $.fn.nepaliDatePicker !== 'undefined') {
                        var $dateField = $('#date');
                        console.log('Date field found:', $dateField.length > 0);
                        console.log('Date field current value:', $dateField.val());
                        
                        // Set initial value if provided
                        if (date_post && date_post.trim() !== '') {
                            console.log('Setting initial date value:', date_post);
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
                                console.log('=== REPORTBYDATE DEBUG: Nepali datepicker onChange ===');
                                console.log('Selected date:', selectedDate);
                                if (selectedDate && selectedDate.bs) {
                                    console.log('Setting date field value to:', selectedDate.bs);
                                    $dateField.val(selectedDate.bs);
                                }
                            }
                        });
                        
                        console.log('Nepali datepicker initialized');
                        
                        // Log date field value after initialization
                        setTimeout(function() {
                            console.log('=== REPORTBYDATE DEBUG: Date field value after init ===');
                            console.log('jQuery val():', $dateField.val());
                            console.log('Native value:', $dateField[0] ? $dateField[0].value : 'N/A');
                        }, 500);
                    } else {
                        console.error('=== REPORTBYDATE DEBUG: Nepali datepicker not available ===');
                    }
                    
                    // Log PHP resultlist if available
                    <?php if (isset($resultlist)): ?>
                    console.log('=== REPORTBYDATE DEBUG: PHP resultlist available ===');
                    console.log('resultlist type:', typeof <?php echo json_encode($resultlist); ?>);
                    console.log('resultlist empty check:', <?php echo empty($resultlist) ? 'true' : 'false'; ?>);
                    <?php if (!empty($resultlist)): ?>
                    try {
                        var resultlistJson = <?php echo json_encode($resultlist); ?>;
                        var parsed = JSON.parse(resultlistJson);
                        console.log('=== PARSED RESULTLIST ===');
                        console.log('Subjects count:', parsed.subjects ? parsed.subjects.length : 0);
                        console.log('Students count:', parsed.student_record ? parsed.student_record.length : 0);
                        
                        // DETAILED SUBJECT DEBUGGING - EXTREME DETAIL
                        console.log('=== DETAILED SUBJECT DEBUG (FROM JSON) ===');
                        if (parsed.subjects && parsed.subjects.length > 0) {
                            console.log('Total Subjects in JSON:', parsed.subjects.length);
                            parsed.subjects.forEach(function(subject, index) {
                                console.log('--- Subject ' + (index + 1) + ' ---');
                                console.log('  - timetable_id (id):', subject.id, '(type:', typeof subject.id, ')');
                                console.log('  - name:', subject.name, '(type:', typeof subject.name, ')');
                                console.log('  - name is empty?:', !subject.name || subject.name === '' || subject.name.trim() === '');
                                console.log('  - name length:', subject.name ? subject.name.length : 0);
                                console.log('  - name trimmed:', subject.name ? subject.name.trim() : 'N/A');
                                console.log('  - subject_id:', subject.subject_id, '(type:', typeof subject.subject_id, ')');
                                console.log('  - code:', subject.code);
                                console.log('  - type:', subject.type);
                                console.log('  - time_from:', subject.time_from);
                                console.log('  - time_to:', subject.time_to);
                                console.log('  - Full object:', JSON.stringify(subject, null, 2));
                                
                                // Check if this will show as Unknown Subject
                                var willShowUnknown = !subject.name || subject.name === '' || subject.name.trim() === '' || subject.name.trim() === '-';
                                console.log('  - WILL SHOW AS UNKNOWN SUBJECT?:', willShowUnknown);
                            });
                        } else {
                            console.log('NO SUBJECTS IN PARSED RESULTLIST!');
                        }
                        
                        console.log('=== DB DEBUG INFO ===');
                        <?php if (isset($db_debug_info)): ?>
                        var dbDebug = <?php echo json_encode($db_debug_info); ?>;
                        console.log('DB Debug Info:', dbDebug);
                        <?php endif; ?>
                        
                        console.log('First student:', parsed.student_record && parsed.student_record.length > 0 ? parsed.student_record[0] : 'No students');
                        
                        // Log attendance fields for first student
                        if (parsed.student_record && parsed.student_record.length > 0) {
                            var firstStudent = parsed.student_record[0];
                            console.log('=== FIRST STUDENT ATTENDANCE FIELDS ===');
                            for (var i = 1; i <= (parsed.subjects ? parsed.subjects.length : 0); i++) {
                                var fieldName = 'attendence_type_id_' + i;
                                var subjectName = parsed.subjects[i-1] ? parsed.subjects[i-1].name : 'N/A';
                                console.log('Subject ' + i + ' (name: "' + subjectName + '", id: ' + (parsed.subjects[i-1] ? parsed.subjects[i-1].id : 'N/A') + '):', fieldName + ' =', firstStudent[fieldName]);
                            }
                        }
                    } catch(e) {
                        console.error('Error parsing resultlist:', e);
                        console.log('Raw resultlist:', resultlistJson);
                    }
                    <?php else: ?>
                    console.log('resultlist is empty');
                    <?php endif; ?>
                    <?php else: ?>
                    console.log('=== REPORTBYDATE DEBUG: No PHP resultlist ===');
                    <?php endif; ?>
                    
                    // Log controller debug info
                    <?php if (isset($controller_debug)): ?>
                    console.log('=== CONTROLLER DEBUG INFO ===');
                    console.log('Controller Class ID:', <?php echo isset($controller_debug['class_id']) ? json_encode($controller_debug['class_id']) : 'null'; ?>);
                    console.log('Controller Section ID:', <?php echo isset($controller_debug['section_id']) ? json_encode($controller_debug['section_id']) : 'null'; ?>);
                    console.log('Controller Program ID:', <?php echo isset($controller_debug['program_id']) ? json_encode($controller_debug['program_id']) : 'null'; ?>);
                    console.log('Controller Day:', <?php echo isset($controller_debug['day']) ? json_encode($controller_debug['day']) : 'null'; ?>);
                    console.log('Controller Date (Raw):', <?php echo isset($controller_debug['date_raw']) ? json_encode($controller_debug['date_raw']) : 'null'; ?>);
                    console.log('Controller Date (AD):', <?php echo isset($controller_debug['date_ad']) ? json_encode($controller_debug['date_ad']) : 'null'; ?>);
                    console.log('Controller Date Timestamp:', <?php echo isset($controller_debug['date_timestamp']) ? $controller_debug['date_timestamp'] : 'null'; ?>);
                    <?php endif; ?>
                    
                    // EXTREME DETAIL DEBUGGING - Log database debug info
                    <?php if (isset($db_debug_info)): ?>
                    console.log('========================================');
                    console.log('=== EXTREME DETAIL DEBUGGING START ===');
                    console.log('========================================');
                    
                    console.log('=== STEP 1: ATTENDANCE IDs FOUND ===');
                    console.log('Attendance IDs Count:', <?php echo isset($db_debug_info['step1_attendance_ids_found']) ? $db_debug_info['step1_attendance_ids_found'] : 0; ?>);
                    console.log('Attendance IDs List:', <?php echo isset($db_debug_info['step1_attendance_ids_list']) ? json_encode($db_debug_info['step1_attendance_ids_list']) : '[]'; ?>);
                    
                    console.log('=== STEP 2: TIMETABLE IDs PROCESSED ===');
                    console.log('Timetable IDs Count:', <?php echo isset($db_debug_info['step2_timetable_ids_count']) ? $db_debug_info['step2_timetable_ids_count'] : 0; ?>);
                    console.log('Timetable IDs List:', <?php echo isset($db_debug_info['step2_timetable_ids_list']) ? json_encode($db_debug_info['step2_timetable_ids_list']) : '[]'; ?>);
                    
                    console.log('=== STEP 3: BASIC TIMETABLE QUERY ===');
                    console.log('Basic Query Result Count:', <?php echo isset($db_debug_info['step3_basic_timetable_query_count']) ? $db_debug_info['step3_basic_timetable_query_count'] : 0; ?>);
                    console.log('Basic Query Subjects:', <?php echo isset($db_debug_info['step3_basic_timetable_subjects']) ? json_encode($db_debug_info['step3_basic_timetable_subjects']) : '[]'; ?>);
                    
                    console.log('=== STEP 4: NAME LOOKUP QUERY ===');
                    console.log('Name Lookup Query:', <?php echo isset($db_debug_info['step4_name_lookup_query']) ? json_encode($db_debug_info['step4_name_lookup_query']) : 'null'; ?>);
                    console.log('Name Lookup IDs:', <?php echo isset($db_debug_info['step4_name_lookup_ids']) ? json_encode($db_debug_info['step4_name_lookup_ids']) : '[]'; ?>);
                    console.log('Name Lookup Result Count:', <?php echo isset($db_debug_info['step4_name_lookup_result_count']) ? $db_debug_info['step4_name_lookup_result_count'] : 0; ?>);
                    console.log('Name Lookup Results:', <?php echo isset($db_debug_info['step4_name_lookup_results']) ? json_encode($db_debug_info['step4_name_lookup_results']) : '[]'; ?>);
                    
                    console.log('=== STEP 5: NAME POPULATION ===');
                    console.log('Name Population Details:', <?php echo isset($db_debug_info['step5_name_population']) ? json_encode($db_debug_info['step5_name_population']) : '[]'; ?>);
                    
                    console.log('=== STEP 6: FLEXIBLE QUERY (FALLBACK) ===');
                    console.log('Missing Name IDs Count:', <?php echo isset($db_debug_info['step6_missing_name_ids_count']) ? $db_debug_info['step6_missing_name_ids_count'] : 0; ?>);
                    console.log('Missing Name IDs List:', <?php echo isset($db_debug_info['step6_missing_name_ids_list']) ? json_encode($db_debug_info['step6_missing_name_ids_list']) : '[]'; ?>);
                    console.log('Flexible Query:', <?php echo isset($db_debug_info['step6_flexible_query']) ? json_encode($db_debug_info['step6_flexible_query']) : 'null'; ?>);
                    console.log('Flexible Result Count:', <?php echo isset($db_debug_info['step6_flexible_result_count']) ? $db_debug_info['step6_flexible_result_count'] : 0; ?>);
                    console.log('Flexible Results:', <?php echo isset($db_debug_info['step6_flexible_results']) ? json_encode($db_debug_info['step6_flexible_results']) : '[]'; ?>);
                    console.log('Flexible Populated Count:', <?php echo isset($db_debug_info['step6_flexible_populated_count']) ? $db_debug_info['step6_flexible_populated_count'] : 0; ?>);
                    
                    console.log('=== STEP 7: FINAL ATTENDANCE SUBJECTS ===');
                    console.log('Final Attendance Subjects Count:', <?php echo isset($db_debug_info['step7_final_attendance_subjects_count']) ? $db_debug_info['step7_final_attendance_subjects_count'] : 0; ?>);
                    console.log('Final Attendance Subjects:', <?php echo isset($db_debug_info['step7_final_attendance_subjects']) ? json_encode($db_debug_info['step7_final_attendance_subjects']) : '[]'; ?>);
                    
                    console.log('=== STEP 8: FINAL STATE BEFORE RETURN ===');
                    console.log('Final Subjects Count:', <?php echo isset($db_debug_info['step8_final_before_return']['subjects_count']) ? $db_debug_info['step8_final_before_return']['subjects_count'] : 0; ?>);
                    console.log('Final Students Count:', <?php echo isset($db_debug_info['step8_final_before_return']['students_count']) ? $db_debug_info['step8_final_before_return']['students_count'] : 0; ?>);
                    console.log('Final Subjects:', <?php echo isset($db_debug_info['step8_final_before_return']['subjects']) ? json_encode($db_debug_info['step8_final_before_return']['subjects']) : '[]'; ?>);
                    
                    console.log('========================================');
                    console.log('=== EXTREME DETAIL DEBUGGING END ===');
                    console.log('========================================');
                    
                    console.log('=== RECEIVED PARAMETERS IN MODEL ===');
                    console.log('Received Class ID:', <?php echo isset($db_debug_info['received_params']['class_id']) ? json_encode($db_debug_info['received_params']['class_id']) : 'null'; ?>);
                    console.log('Received Section ID:', <?php echo isset($db_debug_info['received_params']['section_id']) ? json_encode($db_debug_info['received_params']['section_id']) : 'null'; ?>);
                    console.log('Received Day:', <?php echo isset($db_debug_info['received_params']['day']) ? json_encode($db_debug_info['received_params']['day']) : 'null'; ?>);
                    console.log('Received Date:', <?php echo isset($db_debug_info['received_params']['date']) ? json_encode($db_debug_info['received_params']['date']) : 'null'; ?>);
                    console.log('Current Session:', <?php echo isset($db_debug_info['received_params']['current_session']) ? json_encode($db_debug_info['received_params']['current_session']) : 'null'; ?>);
                    
                    console.log('=== QUERY RESULTS ===');
                    console.log('Attendance records count (before query):', <?php echo isset($db_debug_info['attendance_records_count']) ? $db_debug_info['attendance_records_count'] : 0; ?>);
                    console.log('Attendance query subjects found:', <?php echo isset($db_debug_info['attendance_query_subjects']) ? $db_debug_info['attendance_query_subjects'] : 'null'; ?>);
                    console.log('Timetable query subjects found:', <?php echo isset($db_debug_info['timetable_query_subjects']) ? $db_debug_info['timetable_query_subjects'] : 'null'; ?>);
                    console.log('Combined query subjects found:', <?php echo isset($db_debug_info['combined_query_subjects']) ? $db_debug_info['combined_query_subjects'] : 'null'; ?>);
                    console.log('Subjects before filter:', <?php echo isset($db_debug_info['subjects_before_filter']) ? $db_debug_info['subjects_before_filter'] : 'N/A'; ?>);
                    console.log('Subjects after filter:', <?php echo isset($db_debug_info['subjects_after_filter']) ? $db_debug_info['subjects_after_filter'] : 'N/A'; ?>);
                    console.log('Subjects after deduplication:', <?php echo isset($db_debug_info['subjects_after_deduplication']) ? $db_debug_info['subjects_after_deduplication'] : 'N/A'; ?>);
                    console.log('Subject details:', <?php echo isset($db_debug_info['subject_details']) ? json_encode($db_debug_info['subject_details']) : '[]'; ?>);
                    console.log('Final subjects found:', <?php echo isset($db_debug_info['subjects_found']) ? $db_debug_info['subjects_found'] : 0; ?>);
                    console.log('Final students found:', <?php echo isset($db_debug_info['students_found']) ? $db_debug_info['students_found'] : 0; ?>);
                    console.log('Subjects from query:', <?php echo isset($db_debug_info['subjects']) ? json_encode($db_debug_info['subjects']) : '[]'; ?>);
                    console.log('Stored dates in database:', <?php echo isset($db_debug_info['stored_dates']) ? json_encode($db_debug_info['stored_dates']) : '[]'; ?>);
                    console.log('Existing attendance records count:', <?php echo isset($db_debug_info['existing_attendance_count']) ? $db_debug_info['existing_attendance_count'] : 0; ?>);
                    console.log('Existing attendance records:', <?php echo isset($db_debug_info['existing_attendance']) ? json_encode($db_debug_info['existing_attendance']) : '[]'; ?>);
                    
                    // Log attendance name query debug info
                    console.log('=== ATTENDANCE NAME QUERY DEBUG ===');
                    console.log('Attendance name query IDs:', <?php echo isset($db_debug_info['attendance_name_query_ids']) ? json_encode($db_debug_info['attendance_name_query_ids']) : '[]'; ?>);
                    console.log('Attendance name query results:', <?php echo isset($db_debug_info['attendance_name_query_results']) ? json_encode($db_debug_info['attendance_name_query_results']) : '[]'; ?>);
                    console.log('Attendance name populated count:', <?php echo isset($db_debug_info['attendance_name_populated_count']) ? $db_debug_info['attendance_name_populated_count'] : 0; ?>);
                    console.log('Immediate name query IDs:', <?php echo isset($db_debug_info['immediate_name_query_ids']) ? json_encode($db_debug_info['immediate_name_query_ids']) : '[]'; ?>);
                    console.log('Immediate name query results:', <?php echo isset($db_debug_info['immediate_name_query_results']) ? json_encode($db_debug_info['immediate_name_query_results']) : '[]'; ?>);
                    console.log('Immediate name populated count:', <?php echo isset($db_debug_info['immediate_name_populated_count']) ? $db_debug_info['immediate_name_populated_count'] : 0; ?>);
                    
                    // Detailed comparison
                    if (<?php echo isset($db_debug_info['stored_dates']) && !empty($db_debug_info['stored_dates']) ? 'true' : 'false'; ?>) {
                        console.log('=== DATE COMPARISON ===');
                        var searchDate = <?php echo isset($db_debug_info['search_date']) ? json_encode($db_debug_info['search_date']) : 'null'; ?>;
                        var storedDates = <?php echo isset($db_debug_info['stored_dates']) ? json_encode($db_debug_info['stored_dates']) : '[]'; ?>;
                        console.log('Searching for date:', searchDate);
                        console.log('Dates in database:', storedDates);
                        storedDates.forEach(function(sd) {
                            console.log('  - Stored date:', sd.date, 'Count:', sd.count, 'Match:', sd.date === searchDate);
                        });
                    }
                    <?php endif; ?>
                });
            </script>
