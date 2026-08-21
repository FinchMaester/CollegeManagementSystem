<div class="content-wrapper">
    <section class="content-header">
        <h1>
            <i class="fa fa-map-o"></i> <?php echo $this->lang->line('examinations'); ?> <small><?php echo $this->lang->line('student_fee1'); ?></small>  </h1>
    </section>
    <!-- Main content -->
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <?php if (function_exists('validation_errors') && trim((string) validation_errors()) !== '') { ?>
                <div class="alert alert-danger alert-dismissible" role="alert">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <strong><?php echo $this->lang->line('error'); ?>:</strong> <?php echo validation_errors(); ?>
                </div>
                <?php } ?>
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-search"></i> <?php echo $this->lang->line('select_criteria'); ?></h3>
                    </div>
                    <div class="box-body">
                        <form role="form" action="<?php echo site_url('admin/examresult/marksheet') ?>" method="post" class="row">
                            <?php echo $this->customlib->getCSRF(); ?>
                            <div class="col-sm-6 col-lg-4 col-md-4">
                                    <div class="form-group">
                                        <label><?php echo $this->lang->line('exam_group'); ?></label><small class="req"> *</small>
                                        <select autofocus="" id="exam_group_id" name="exam_group_id" class="form-control" >
                                            <option value=""><?php echo $this->lang->line('select'); ?></option>
                                            <?php
foreach ($examgrouplist as $ex_group_key => $ex_group_value) {
    ?>
                                                <option value="<?php echo $ex_group_value->id ?>" <?php
if (set_value('exam_group_id') == $ex_group_value->id) {
        echo "selected=selected";
    }
    ?>><?php echo $ex_group_value->name; ?></option>
                                                        <?php
}
?>
                                        </select>
                                        <span class="text-danger"><?php echo form_error('exam_group_id'); ?></span>
                                    </div>
                                </div><!--./col-md-3-->
                                <div class="col-sm-6 col-lg-4 col-md-4">
                                    <div class="form-group">
                                        <label><?php echo $this->lang->line('exam') ?></label><small class="req"> *</small>
                                        <select  id="exam_id" name="exam_id" class="form-control" >
                                            <option value=""><?php echo $this->lang->line('select'); ?></option>
                                        </select>
                                        <span class="text-danger"><?php echo form_error('exam_id'); ?></span>
                                    </div>
                                </div><!--./col-md-3-->
                            <div class="col-sm-6 col-lg-4 col-md-4">
                                <div class="form-group">  
                                    <label><?php echo $this->lang->line('session'); ?></label><small class="req"> *</small>
                                    <select  id="session_id" name="session_id" class="form-control" >
                                        <option value=""><?php echo $this->lang->line('select'); ?></option>
                                        <?php
                                        foreach ($sessionlist as $session) {
                                            ?>
                                            <option value="<?php echo $session['id'] ?>" <?php
                                            if (set_value('session_id') == $session['id']) {
                                                echo "selected=selected";
                                            }
                                            ?>><?php echo $session['session'] ?></option>
                                                    <?php
                                                }
                                                ?>
                                    </select>
                                    <span class="text-danger"><?php echo form_error('session_id'); ?></span>
                                </div>  
                            </div>
                            <div class="col-sm-6 col-lg-4 col-md-4">
                                <div class="form-group">
                                    <label><?php echo $this->lang->line('section'); ?></label><small class="req"> *</small>
                                    <select id="section_id" name="section_id" class="form-control">
                                        <option value=""><?php echo $this->lang->line('select'); ?></option>
                                    </select>
                                    <span class="text-danger"><?php echo form_error('section_id'); ?></span>
                                </div>
                            </div>
                            <div class="col-sm-6 col-lg-4 col-md-4">
                                <div class="form-group">
                                    <label for="exampleInputEmail1"><?php echo $this->lang->line('program'); ?></label><small class="req"> *</small>
                                    <select id="program_id" name="program_id" class="form-control">
                                        <option value=""><?php echo $this->lang->line('select'); ?></option>
                                    </select>
                                    <span class="text-danger"><?php echo form_error('program_id'); ?></span>
                                </div>
                            </div>
                            <div class="col-sm-6 col-lg-4 col-md-4">
                                <div class="form-group">
                                    <label><?php echo $this->lang->line('class'); ?></label> <small class="req"> *</small>
                                    <select id="class_id" name="class_id" class="form-control">
                                        <option value=""><?php echo $this->lang->line('select'); ?></option>
                                    </select>
                                    <span class="text-danger" id="error_class_id"></span>
                                </div>
                            </div>
                            <div class="col-sm-6 col-lg-4 col-md-4">
                                <div class="form-group">
                                    <label for="exampleInputEmail1"><?php echo $this->lang->line('marksheet_template'); ?></label><small class="req"> *</small>
                                    <select  id="marksheet" name="marksheet" class="form-control" >
                                        <option value=""><?php echo $this->lang->line('select'); ?></option>
                                        <?php
                                        foreach ($marksheetlist as $marksheet) {
                                            ?>
                                            <option value="<?php echo $marksheet->id ?>" <?php
                                            if (set_value('marksheet') == $marksheet->id) {
                                                echo "selected=selected";
                                            }
                                            ?>><?php echo $marksheet->template; ?></option>
                                                    <?php
                                                }
                                                ?>
                                    </select>
                                    <span class="text-danger"><?php echo form_error('marksheet'); ?></span>
                                </div>
                            </div>
                            <div class="col-sm-12">
                                <div class="form-group">
                                    <button type="submit" name="search" value="search_filter" class="btn btn-primary pull-right btn-sm checkbox-toggle"><i class="fa fa-search"></i> <?php echo $this->lang->line('search'); ?></button>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="aa"></div>
                    <?php
                    if (isset($studentList)) {
                        ?>
                        <form method="post" action="<?php echo base_url('admin/examresult/printmarksheet') ?>" id="printMarksheet">
                            <input type="hidden" name="marksheet_template" value="<?php echo $marksheet_template; ?>">
                            <div class="box-header ptbnull"></div>  
                            <div class="box-header ptbnull">
                                <h3 class="box-title titlefix"><i class="fa fa-users"></i> <?php echo $this->lang->line('student_list'); ?></h3>
                                <div class="pull-right" style="display: inline-block;">
                                    <button class="btn btn-warning btn-sm" id="saveAttendanceBtn" type="button" title="Save Attendance Summary" style="margin-right: 5px;">
                                        <i class="fa fa-calendar-check-o"></i> Save Attendance
                                    </button>
                                    <button class="btn btn-info btn-sm" id="saveNonAcademicMarksBtn" type="button" title="Save Non-Academic Marks" style="margin-right: 5px;">
                                        <i class="fa fa-pencil-square-o"></i> Non-Academic Marks
                                    </button>
                                    <button class="btn btn-info btn-sm printSelected" type="button" name="generate" title="<?php echo htmlspecialchars($this->lang->line('bulk_download') . ' — opens a new tab with all selected marksheets; use Print / Save as PDF', ENT_QUOTES, 'UTF-8'); ?>" data-loading-text="<i class='fa fa-circle-o-notch fa-spin'></i> <?php echo $this->lang->line('please_wait') ; ?>"> <?php echo $this->lang->line('bulk_download'); ?></button>
                                </div>
                            </div>
                            <div class="box-body">
                                <input type="hidden" name="post_exam_id" id="post_exam_id" value="<?php echo $exam_id; ?>">
                                <input type="hidden" name="post_exam_group_id" id="post_exam_group_id" value="<?php echo $exam_group_id; ?>">
                                <div class="tab-pane active table-responsive no-padding" id="tab_1">
                                <?php if (!empty($studentList)) { ?>
                                    <table class="table table-striped table-bordered table-hover" cellspacing="0" width="100%">
                                        <thead>
                                            <tr>
                                                <th><input type="checkbox" id="select_all" /></th>
                                                <th><?php echo $this->lang->line('admission_no'); ?></th>
                                                <th><?php echo $this->lang->line('student_name'); ?></th>
                                                <th><?php echo $this->lang->line('father_name'); ?></th>
                                                <th><?php echo $this->lang->line('date_of_birth'); ?></th>
                                                <th><?php echo $this->lang->line('gender'); ?></th>
                                                <th><?php echo $this->lang->line('category'); ?></th>
                                                <th class=""><?php echo $this->lang->line('mobile_number'); ?></th>
                                                <th class="text-right"><?php echo $this->lang->line('action'); ?></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                                $count = 1;
                                                foreach ($studentList as $student_key => $student_value) {
                                                $student_name = $this->customlib->getFullName($student_value->firstname,$student_value->middlename,$student_value->lastname,$sch_setting->middlename,$sch_setting->lastname);
                                                    ?>


                                                    <tr>
                                                        <td class="text-center">
                                                            <input type="checkbox" class="checkbox center-block student-checkbox"  
                                                                   name="exam_group_class_batch_exam_student_id[]" 
                                                                   data-student-id="<?php echo $student_value->student_id; ?>"
                                                                   data-student-name="<?php echo htmlspecialchars($student_name); ?>"
                                                                   data-admission-no="<?php echo htmlspecialchars($student_value->admission_no); ?>"
                                                                   value="<?php echo $student_value->exam_group_class_batch_exam_student_id; ?>">
                                                        </td>
                                                        <td><?php echo $student_value->admission_no; ?></td>        <td>
            <a href="<?php echo base_url(); ?>student/view/<?php echo $student_value->student_id; ?>">            
            <?php echo $student_name; ?>            
            
         </a>
        </td>
                                                        <td><?php echo $student_value->father_name;?></td>
                                                        <td><?php
                                                            if (!empty($student_value->dob) && $student_value->dob != '0000-00-00') {
                                                            echo date($student_value->dob); }?></td>
                                                        <td><?php echo $this->lang->line(strtolower($student_value->gender)); ?></td>
                                                        <td><?php echo $student_value->category; ?></td>
                                                        <td><?php echo $student_value->mobileno; ?></td>
                                                        <td class="text-right white-space-nowrap">

                        <!-- NEW: VIEW BUTTON -->
                        <button type="button"
                                class="btn btn-default btn-xs view_marksheet"
                                data-toggle="tooltip"
                                data-original-title="<?php echo $this->lang->line('view'); ?>"
                                data-exam_group_class_batch_exam_student_id="<?php echo $student_value->exam_group_class_batch_exam_student_id; ?>"
                                data-student_id="<?php echo $student_value->student_id?>">
                          <i class="fa fa-eye"></i>
                        </button>

                        <button type="button" class="btn btn-default btn-xs download_pdf" data-action="download" data-toggle="tooltip" data-original-title="<?php echo htmlspecialchars($this->lang->line('download') . ' / Print', ENT_QUOTES, 'UTF-8'); ?>" data-exam_group_class_batch_exam_student_id="<?php echo $student_value->exam_group_class_batch_exam_student_id; ?>" data-admission_no="<?php echo $student_value->admission_no;?>" data-student_name="<?php echo $student_name; ?>" data-student_id="<?php echo $student_value->student_id?>" data-loading-text="<i class='fa fa-circle-o-notch fa-spin'></i>" ><i class="fa fa-download"></i></button>
                       <button  type="button"  class="btn btn-default btn-xs email_pdf" data-action="email" data-exam_group_class_batch_exam_student_id="<?php echo $student_value->exam_group_class_batch_exam_student_id; ?>" id="load1" data-toggle="tooltip"  data-original-title="<?php echo $this->lang->line('sent_to_email'); ?>" data-student_id="<?php echo $student_value->student_id?>" data-loading-text="<i class='fa fa-circle-o-notch fa-spin'></i>" ><i class="fa fa-envelope"></i></button>
                                                        </td>
                                                    </tr>
                                                    <?php
                                                    $count++;
                                                }
                                            ?>
                                        </tbody>
                                    <?php }else{ ?>
                                        <div class="alert alert-info"><?php echo $this->lang->line('no_record_found'); ?></div>
                                    <?php } ?>
                                    </table>
                                </div>                                                            
                            </div>
                        </form>
                    </div>
                    <?php
                }
                ?>
            </div>
        </div>
    </section>
</div>

<!-- Attendance Modal -->
<div id="attendanceModal" class="modal fade" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header bg-warning">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Save Attendance Summary</h4>
      </div>
      <div class="modal-body">
        <div class="form-group">
          <label>Working Days <span class="text-danger">*</span></label>
          <input type="number" class="form-control" id="working_days" placeholder="Enter total working days (applies to all students)" min="1">
          <small class="text-info">
            <i class="fa fa-info-circle"></i> Changing working days will update for ALL students in this class/section/session.
          </small>
        </div>
        <div class="alert alert-warning" style="margin-bottom: 15px;">
          <i class="fa fa-exclamation-triangle"></i> <strong>Note:</strong> Present days cannot exceed working days. Values will be automatically adjusted.
        </div>
        <table class="table table-bordered" id="attendanceTable">
          <thead>
            <tr><th>Student</th><th>Admission No</th><th>Present Days</th><th>Absent Days</th></tr>
          </thead>
          <tbody></tbody>
        </table>
      </div>
      <div class="modal-footer">
        <button type="button" id="saveAttendanceConfirmBtn" class="btn btn-success">
          <i class="fa fa-save"></i> Save Attendance
        </button>
        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
      </div>
    </div>
  </div>
</div>

<!-- Non-Academic Marks Modal -->
<div id="nonAcademicMarksModal" class="modal fade" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header bg-info">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Save Non-Academic Marks</h4>
      </div>
      <div class="modal-body">
        <div class="alert alert-info" style="margin-bottom: 15px;">
          <i class="fa fa-info-circle"></i> <strong>Note:</strong> Enter marks for non-academic subjects (Reading, Writing, Speaking, etc.)
        </div>
        <table class="table table-bordered" id="nonAcademicMarksTable">
          <thead>
            <tr>
              <th>Student</th>
              <th>Admission No</th>
              <th id="nonAcademicSubjectsHeader"></th>
            </tr>
          </thead>
          <tbody></tbody>
        </table>
      </div>
      <div class="modal-footer">
        <button type="button" id="saveNonAcademicMarksConfirmBtn" class="btn btn-success">
          <i class="fa fa-save"></i> Save Non-Academic Marks
        </button>
        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
      </div>
    </div>
  </div>
</div>

<script type="text/javascript">
// CRITICAL: Define baseurl variable at the top
var baseurl = '<?php echo base_url(); ?>';

/**
 * Open marksheet HTML in a new tab (same as View), optional browser print dialog for Save-as-PDF.
 * @param {string[]} ecbesIds exam_group_class_batch_exam_student_id values
 * @param {boolean} autoprint if true, page runs window.print() after load
 */
function submitMarksheetPrintView(ecbesIds, autoprint) {
    if (!ecbesIds || !ecbesIds.length) {
        toastr.error('<?php echo addslashes($this->lang->line('please_select_student')); ?>');
        return;
    }
    var marksheet_template = $('input[name=marksheet_template]').val();
    var post_exam_id = $('#post_exam_id').val();
    var post_exam_group_id = $('#post_exam_group_id').val();
    if (!marksheet_template || !post_exam_id || !post_exam_group_id) {
        toastr.error('Missing exam or template. Search again.');
        return;
    }
    var $form = $('<form>', {
        method: 'POST',
        target: '_blank',
        action: baseurl + 'admin/examresult/viewmarksheet'
    });
    $form.append($('<input>', { type: 'hidden', name: '<?php echo $this->security->get_csrf_token_name(); ?>', value: '<?php echo $this->security->get_csrf_hash(); ?>' }));
    $form.append($('<input>', { type: 'hidden', name: 'marksheet_template', value: marksheet_template }));
    $form.append($('<input>', { type: 'hidden', name: 'post_exam_id', value: post_exam_id }));
    $form.append($('<input>', { type: 'hidden', name: 'post_exam_group_id', value: post_exam_group_id }));
    if (autoprint) {
        $form.append($('<input>', { type: 'hidden', name: 'autoprint', value: '1' }));
    }
    var sid = $('#session_id').val(), cid = $('#class_id').val(), secid = $('#section_id').val();
    if (sid) {
        $form.append($('<input>', { type: 'hidden', name: 'session_id', value: sid }));
    }
    if (cid) {
        $form.append($('<input>', { type: 'hidden', name: 'class_id', value: cid }));
    }
    if (secid) {
        $form.append($('<input>', { type: 'hidden', name: 'section_id', value: secid }));
    }
    ecbesIds.forEach(function (id) {
        if (id && id !== 'on') {
            $form.append($('<input>', { type: 'hidden', name: 'exam_group_class_batch_exam_student_id[]', value: id }));
        }
    });
    $('body').append($form);
    $form.trigger('submit');
    $form.remove();
}

// Select/Deselect all functionality
$(document).on('click', '#select_all', function () {
    $('.student-checkbox, .checkbox').prop('checked', this.checked);
});

// Update select all when individual checkboxes change
$(document).on('change', '.student-checkbox, .checkbox', function() {
    var totalCheckboxes = $('.student-checkbox, .checkbox').length;
    var checkedCheckboxes = $('.student-checkbox:checked, .checkbox:checked').length;
    $('#select_all').prop('checked', totalCheckboxes === checkedCheckboxes);
});

// Auto calculate absent days and update max attribute for present days inputs
$(document).on('input', '#working_days, .present-days', function(){
    var working = parseInt($('#working_days').val()) || 0;
    
    // Update max attribute for all present days inputs
    $('.present-days').attr('max', working);
    
    $('#attendanceTable tbody tr').each(function(){
        var presentInput = $(this).find('.present-days');
        var present = parseInt(presentInput.val()) || 0;
        
        // Validate: present days cannot exceed working days
        if(present > working){
            presentInput.val(working);
            present = working;
        }
        
        var absent = Math.max(working - present, 0);
        $(this).find('.absent-days').text(absent);
    });
});

// ============ ATTENDANCE FUNCTIONALITY ============

// Save Attendance Button Click - Open Modal
$(document).on('click', '#saveAttendanceBtn', function(e){
    e.preventDefault();
    e.stopPropagation();
    
    var selected = $('.student-checkbox:checked');
    
    if(selected.length === 0){
        alert('Please select at least one student.');
        return;
    }

    // Validate required fields
    var classId = $('#class_id').val();
    var sectionId = $('#section_id').val();
    var sessionId = $('#session_id').val();

    if(!classId || !sectionId || !sessionId){
        alert('Please select Class, Section, and Session first.');
        return;
    }

    $('#attendanceTable tbody').empty();

    var students = [];
    selected.each(function(){
        var id = $(this).data('student-id');
        var name = $(this).data('student-name');
        var adm = $(this).data('admission-no');
        students.push({student_id:id, name:name, admission_no:adm});
    });

    // Fetch previously saved attendance before showing modal
    $.ajax({
        url: baseurl + 'admin/examresult/get_saved_attendance',
        type: 'POST',
        dataType: 'json',
        data: {
            class_id: classId,
            section_id: sectionId,
            session_id: sessionId,
            students: JSON.stringify(students)
        },
        success: function(res){
            var saved = res.data || {};
            var savedWorkingDays = res.working_days || 0;
            
            $('#attendanceTable tbody').empty();
            
            // Auto-fill working days if previously saved
            if(savedWorkingDays > 0){
                $('#working_days').val(savedWorkingDays);
            }

            students.forEach(function(stu){
                var existing = saved[stu.student_id];
                var present = existing ? existing.present_days : 0;
                var absent = existing ? existing.absent_days : 0;
                var note = existing ? '<small class="text-success">✔ Already saved (last updated ' + existing.updated_at + ')</small>' : '<small class="text-muted">New entry</small>';

                var row = '<tr data-student-id="' + stu.student_id + '">' +
                          '<td>' + stu.name + '<br>' + note + '</td>' +
                          '<td>' + stu.admission_no + '</td>' +
                          '<td><input type="number" class="form-control present-days" min="0" max="' + savedWorkingDays + '" value="' + present + '"></td>' +
                          '<td class="absent-days">' + absent + '</td>' +
                          '</tr>';
                $('#attendanceTable tbody').append(row);
            });

            $('#attendanceModal').modal('show');
        },
        error: function(xhr, status, error){
            console.error("Fetch error:", error, xhr.responseText);
            alert('Failed to fetch saved attendance. Opening modal with empty data.');
            
            // Still open modal with empty data
            $('#attendanceTable tbody').empty();
            students.forEach(function(stu){
                var row = '<tr data-student-id="' + stu.student_id + '">' +
                          '<td>' + stu.name + '<br><small class="text-muted">New entry</small></td>' +
                          '<td>' + stu.admission_no + '</td>' +
                          '<td><input type="number" class="form-control present-days" min="0" value="0"></td>' +
                          '<td class="absent-days">0</td>' +
                          '</tr>';
                $('#attendanceTable tbody').append(row);
            });
            
            $('#attendanceModal').modal('show');
        }
    });
});

// Save Attendance Confirm Button
$(document).on('click', '#saveAttendanceConfirmBtn', function(e) {
    e.preventDefault();
    e.stopPropagation();
    
    var workingDays = parseInt($('#working_days').val()) || 0;
    
    if(workingDays <= 0){
        alert('Please enter valid working days.');
        return;
    }
    
    var students = [];
    var hasError = false;

    $('#attendanceTable tbody tr').each(function(){
        var student_id = $(this).data('student-id');
        var present = parseInt($(this).find('.present-days').val()) || 0;
        
        // Validate: present days cannot exceed working days
        if(present > workingDays){
            alert('Present days cannot be more than working days!');
            hasError = true;
            return false; // break the loop
        }
        
        var absent = Math.max(workingDays - present, 0);
        students.push({
            student_id: student_id,
            present_days: present,
            absent_days: absent
        });
    });

    if(hasError){
        return;
    }

    // Disable button to prevent double submission
    $('#saveAttendanceConfirmBtn').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Saving...');

    $.ajax({
        url: baseurl + 'admin/examresult/save_attendance_summary',
        type: 'POST',
        dataType: 'json',
        data: {
            class_id: $('#class_id').val(),
            section_id: $('#section_id').val(),
            session_id: $('#session_id').val(),
            working_days: workingDays,
            students: JSON.stringify(students)
        },
        success: function(res){
            if(res.status == 1){
                // Close modal immediately
                $('#attendanceModal').modal('hide');
                
                // Reset button state
                $('#saveAttendanceConfirmBtn').prop('disabled', false).html('<i class="fa fa-save"></i> Save Attendance');
                
                // Show success notification
                showSuccessNotification('Attendance saved successfully!');
            } else {
                alert('Failed to save attendance: ' + (res.msg || 'Unknown error'));
                $('#saveAttendanceConfirmBtn').prop('disabled', false).html('<i class="fa fa-save"></i> Save Attendance');
            }
        },
        error: function(xhr, status, error){
            console.error("Save error:", error, xhr.responseText);
            alert('Failed to save attendance. Please try again.');
            $('#saveAttendanceConfirmBtn').prop('disabled', false).html('<i class="fa fa-save"></i> Save Attendance');
        }
    });
});

// ============ NON-ACADEMIC MARKS FUNCTIONALITY ============

// Save Non-Academic Marks Button Click - Open Modal
$(document).on('click', '#saveNonAcademicMarksBtn', function(e){
    e.preventDefault();
    e.stopPropagation();
    
    var selected = $('.student-checkbox:checked');
    
    if(selected.length === 0){
        alert('Please select at least one student.');
        return;
    }

    // Validate required fields
    var examId = $('#exam_id').val();
    var examGroupId = $('#exam_group_id').val();
    var classId = $('#class_id').val();
    var sectionId = $('#section_id').val();
    var sessionId = $('#session_id').val();

    if(!examId || !examGroupId || !classId || !sectionId || !sessionId){
        alert('Please select Exam Group, Exam, Class, Section, and Session first.');
        return;
    }

    $('#nonAcademicMarksTable tbody').empty();

    var students = [];
    selected.each(function(){
        var id = $(this).data('student-id');
        var name = $(this).data('student-name');
        var adm = $(this).data('admission-no');
        students.push({student_id:id, name:name, admission_no:adm});
    });

    // Fetch non-academic subjects and previously saved marks
    $.ajax({
        url: baseurl + 'admin/examresult/get_nonacademic_marks',
        type: 'POST',
        dataType: 'json',
        data: {
            exam_id: examId,
            exam_group_id: examGroupId,
            class_id: classId,
            section_id: sectionId,
            session_id: sessionId,
            students: JSON.stringify(students)
        },
        success: function(res){
            if(res.status == 0){
                alert('Failed to fetch non-academic subjects: ' + (res.msg || 'Unknown error'));
                return;
            }
            
            var subjects = res.subjects || [];
            var savedMarks = res.marks || {};
            
            if(subjects.length === 0){
                alert('No non-academic subjects found for this class and section. Please add non-academic subjects to the subject group for this class/section.');
                return;
            }
            
            // Build table header with subject columns
            var headerHtml = '<th>Student</th><th>Admission No</th>';
            subjects.forEach(function(subj){
                headerHtml += '<th>' + subj.name + '</th>';
            });
            $('#nonAcademicMarksTable thead tr').html(headerHtml);
            
            // Build table rows
            $('#nonAcademicMarksTable tbody').empty();
            students.forEach(function(stu){
                var row = '<tr data-student-id="' + stu.student_id + '">';
                row += '<td>' + stu.name + '</td>';
                row += '<td>' + stu.admission_no + '</td>';
                
                subjects.forEach(function(subj){
                    var key = stu.student_id + '_' + subj.id;
                    var savedMark = savedMarks[key] ? savedMarks[key].marks_obtained : '';
                    var note = savedMarks[key] ? '<br><small class="text-success">✔ Saved</small>' : '';
                    row += '<td><input type="number" class="form-control non-academic-mark" ' +
                           'data-subject-id="' + subj.id + '" ' +
                           'data-subject-name="' + subj.name + '" ' +
                           'min="0" step="0.01" value="' + savedMark + '" ' +
                           'style="width: 100px; display: inline-block;">' + note + '</td>';
                });
                
                row += '</tr>';
                $('#nonAcademicMarksTable tbody').append(row);
            });

            $('#nonAcademicMarksModal').modal('show');
        },
        error: function(xhr, status, error){
            console.error("Error:", error, xhr.responseText);
            alert('Failed to fetch non-academic subjects. Please check console for details.');
        }
    });
});

// Save Non-Academic Marks Confirm Button
$(document).on('click', '#saveNonAcademicMarksConfirmBtn', function(e) {
    e.preventDefault();
    e.stopPropagation();
    
    var examId = $('#exam_id').val();
    var examGroupId = $('#exam_group_id').val();
    
    if(!examId || !examGroupId){
        alert('Please select Exam Group and Exam first.');
        return;
    }
    
    var marks = [];
    var hasError = false;

    $('#nonAcademicMarksTable tbody tr').each(function(){
        var student_id = $(this).data('student-id');
        
        $(this).find('.non-academic-mark').each(function(){
            var subject_id = $(this).data('subject-id');
            var marks_obtained = parseFloat($(this).val()) || 0;
            
            if(marks_obtained < 0){
                alert('Marks cannot be negative!');
                hasError = true;
                return false;
            }
            
            marks.push({
                exam_id: examId,
                exam_group_id: examGroupId,
                student_id: student_id,
                subject_id: subject_id,
                marks_obtained: marks_obtained
            });
        });
    });

    if(hasError){
        return;
    }

    if(marks.length === 0){
        alert('No marks to save.');
        return;
    }

    // Disable button to prevent double submission
    $('#saveNonAcademicMarksConfirmBtn').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Saving...');

    $.ajax({
        url: baseurl + 'admin/examresult/save_nonacademic_marks',
        type: 'POST',
        dataType: 'json',
        data: {
            marks: JSON.stringify(marks)
        },
        success: function(res){
            if(res.status == 1){
                // Close modal immediately
                $('#nonAcademicMarksModal').modal('hide');
                
                // Reset button state
                $('#saveNonAcademicMarksConfirmBtn').prop('disabled', false).html('<i class="fa fa-save"></i> Save Non-Academic Marks');
                
                // Show success notification
                showSuccessNotification('Non-academic marks saved successfully!');
            } else {
                alert('Failed to save non-academic marks: ' + (res.msg || 'Unknown error'));
                $('#saveNonAcademicMarksConfirmBtn').prop('disabled', false).html('<i class="fa fa-save"></i> Save Non-Academic Marks');
            }
        },
        error: function(xhr, status, error){
            console.error("Save error:", error, xhr.responseText);
            alert('Failed to save non-academic marks. Please try again.');
            $('#saveNonAcademicMarksConfirmBtn').prop('disabled', false).html('<i class="fa fa-save"></i> Save Non-Academic Marks');
        }
    });
});

// Helper function to show success notification
function showSuccessNotification(message) {
    // Create a Bootstrap alert that auto-dismisses
    var alertHtml = '<div class="alert alert-success alert-dismissible" style="position: fixed; top: 70px; right: 20px; z-index: 9999; min-width: 300px;" role="alert">' +
                    '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>' +
                    '<strong><i class="fa fa-check-circle"></i> Success!</strong> ' + message +
                    '</div>';
    
    $('body').append(alertHtml);
    
    // Auto remove after 3 seconds
    setTimeout(function() {
        $('.alert-success').fadeOut(400, function() {
            $(this).remove();
        });
    }, 3000);
}
// Single-student: open marksheet in new tab and show print dialog (Save as PDF from the browser)
$(document).on('click', '.download_pdf', function() {
    var $button_ = $(this);
    var action = $button_.data('action');
    var exam_group_class_batch_exam_student_id = String($button_.data('exam_group_class_batch_exam_student_id') || '');
    if (action !== 'download') {
        return;
    }
    submitMarksheetPrintView([exam_group_class_batch_exam_student_id], true);
    toastr.info('A new tab opened — use Print → Save as PDF if you want a file.');
});

// Bulk: open all selected marksheets in one HTML page (new tab) + print dialog — same data path as View
$(document).on('click', '#printMarksheet .printSelected', function(e) {
    e.preventDefault();
    var selected_students = [];
    $('#printMarksheet .student-checkbox:checked').each(function() {
        var v = $(this).val();
        if (v && v !== 'on') {
            selected_students.push(v);
        }
    });
    if (selected_students.length === 0) {
        toastr.error('Please select at least one student');
        return;
    }
    submitMarksheetPrintView(selected_students, true);
    toastr.info('A new tab opened with ' + selected_students.length + ' marksheet(s). Use Print → Save as PDF.');
});

// Alternative: ZIP download function
$(document).on('click', '.downloadZip', function(e) {
    e.preventDefault();
    
    var selected_students = [];
    $('.checkbox:checked').each(function() {
        if ($(this).val() !== 'on') {
            selected_students.push($(this).val());
        }
    });
    
    if (selected_students.length === 0) {
        toastr.error('Please select at least one student');
        return;
    }
    
    let $button = $(this);
    let post_exam_id = $('#post_exam_id').val();
    let post_exam_group_id = $('#post_exam_group_id').val();
    let marksheet_template = $("input[name=marksheet_template]").val();
    
    $button.button('loading');
    
    $.ajax({
        url: baseurl + 'admin/examresult/downloadMarksheetZip',
        type: 'POST',
        data: {
            marksheet_template: marksheet_template,
            post_exam_id: post_exam_id,
            post_exam_group_id: post_exam_group_id,
            'exam_group_class_batch_exam_student_id[]': selected_students
        },
        xhrFields: {
            responseType: 'blob'
        },
        success: function(data, status, xhr) {
            var blob = new Blob([data], { type: 'application/zip' });
            var link = document.createElement('a');
            link.href = window.URL.createObjectURL(blob);
            link.download = 'marksheets_' + new Date().toISOString().slice(0, 19).replace(/:/g, '-') + '.zip';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            window.URL.revokeObjectURL(link.href);
            toastr.success('ZIP file downloaded successfully');
            $button.button('reset');
        },
        error: function(xhr, status, error) {
            console.error('ZIP download error:', error);
            toastr.error('Error downloading ZIP file. Please try again.');
            $button.button('reset');
        }
    });
});

// Select all functionality
$(document).on('change', '#select_all', function() {
    $('.checkbox').prop('checked', $(this).is(':checked'));
});

 $(document).on('click','.email_pdf',function(){
      let $button_ = $(this);
      let post_exam_id=$('#post_exam_id').val();
      let post_exam_group_id=$('#post_exam_group_id').val();
      var student_id=($button_.data('student_id'));
      var action=($button_.data('action'));
      var exam_group_class_batch_exam_student_id=($button_.data('exam_group_class_batch_exam_student_id'));
      let marksheet_template=$("input[name=marksheet_template]").val();
    $.ajax({
    type: 'POST',
    url: baseurl+'admin/examresult/pdftmarksheet',
    data: {
          'marksheet_template':marksheet_template,
        'type':action,
           'student_id':student_id,
           'exam_group_class_batch_exam_student_id':exam_group_class_batch_exam_student_id,
           'post_exam_id':post_exam_id,
           "post_exam_group_id":post_exam_group_id
          },
    dataType: 'JSON',
    beforeSend: function() {      
       $button_.button('loading');      
    },
   success: function (data, jqXHR, response) {
             if (data.status == 1) {
                  successMsg(data.message);
                } else {
                  errorMsg(data.message);
                }
            },
    error: function(xhr) { // if error occured      
        $button_.button('reset');
    },
    complete: function() {    
            $button_.button('reset');      
    }
});
    });


 $(document).ready(function () {
        $('.select2').select2();
    });
    var date_format = '<?php echo $result = strtr($this->customlib->getSchoolDateFormat(), ['d' => 'dd', 'm' => 'mm', 'Y' => 'yyyy']) ?>';
    var class_id = '<?php echo set_value('class_id') ?>';
    var section_id = '<?php echo set_value('section_id') ?>';
    var session_id = '<?php echo set_value('session_id') ?>';
    var exam_group_id = '<?php echo set_value('exam_group_id') ?>';
    var exam_id = '<?php echo set_value('exam_id') ?>';

   getExamByExamgroup(exam_group_id, exam_id);
    $(document).on('change', '#exam_group_id', function (e) {
        $('#exam_id').html("");
        var exam_group_id = $(this).val();
        getExamByExamgroup(exam_group_id, 0);
    });


    function getExamByExamgroup(exam_group_id, exam_id) {

        if (exam_group_id !== "") {
            $('#exam_id').html("");
            var base_url = '<?php echo base_url() ?>';
            var div_data = '<option value=""><?php echo $this->lang->line('select'); ?></option>';

            $.ajax({
                type: "POST",
                url: base_url + "admin/examgroup/getExamByExamgroup",
                data: {'exam_group_id': exam_group_id},
                dataType: "json",
                beforeSend: function () {
                    $('#exam_id').addClass('dropdownloading');
                },
                success: function (data) {
                    $.each(data, function (i, obj)
                    {
                        var sel = "";
                        if (exam_id === obj.id) {
                            sel = "selected";
                        }
                        div_data += "<option value=" + obj.id + " " + sel + ">" + obj.exam + "</option>";
                    });
                    $('#exam_id').append(div_data);
                },
                complete: function () {
                    $('#exam_id').removeClass('dropdownloading');
                }
            });
        }
        }
</script>
<script>


    $(document).on('submit', 'form#printMarksheet', function (e) {
        e.preventDefault();
        var selected = [];
        $('form#printMarksheet input[name="exam_group_class_batch_exam_student_id[]"]:checked').each(function() {
            var v = $(this).val();
            if (v && v !== 'on') {
                selected.push(v);
            }
        });
        if (selected.length > 0) {
            submitMarksheetPrintView(selected, true);
            toastr.info('A new tab opened — use Print → Save as PDF.');
        } else {
            confirm("<?php echo $this->lang->line('please_select_student'); ?>");
        }
    });


    $(document).on('click', '#select_all', function () {
        $(this).closest('table').find('td input:checkbox').prop('checked', this.checked);
    });
</script>


<script type="text/javascript">
    var base_url = '<?php echo base_url() ?>';
    function Popup(data)
    {
        var frame1 = $('<iframe />');
        frame1[0].name = "frame1";
        $("body").append(frame1);
        var frameDoc = frame1[0].contentWindow ? frame1[0].contentWindow : frame1[0].contentDocument.document ? frame1[0].contentDocument.document : frame1[0].contentDocument;
        frameDoc.document.open();
//Create a new HTML document.
        frameDoc.document.write('<html>');
        frameDoc.document.write('<head>');
        frameDoc.document.write('<title></title>');
        frameDoc.document.write('</head>');
        frameDoc.document.write('<body>');
        frameDoc.document.write(data);
        frameDoc.document.write('</body>');
        frameDoc.document.write('</html>');
        frameDoc.document.close();
        setTimeout(function () {
            window.frames["frame1"].focus();
            window.frames["frame1"].print();
            frame1.remove();
        }, 500);
        return true;
    }
</script>

<script>  
$(document).ready(function () {
    // Store selected values
    var selectedSection = '<?php echo set_value("section_id") ?>';
    var selectedProgram = '<?php echo set_value("program_id") ?>';
    var selectedClass = '<?php echo set_value("class_id") ?>';
    
    // Fetch all sections on page load
    loadSections();
    
    function loadSections() {
        $.ajax({
            url: '<?php echo base_url(); ?>sections/getAll',
            method: 'GET',
            dataType: 'json',
            success: function (response) {
                var html = '<option value=""><?php echo $this->lang->line("select"); ?></option>';
                
                $.each(response, function (index, section) {
                    var selected = (selectedSection == section.id) ? 'selected' : '';
                    html += '<option value="' + section.id + '" ' + selected + '>' + section.section + '</option>';
                });
                
                $('#section_id').html(html);
                
                // If there's a pre-selected section, load programs
                if(selectedSection) {
                    loadPrograms(selectedSection);
                }
            },
            error: function(xhr, status, error) {
                console.error("Error loading sections:", error);
            }
        });
    }
    
    function loadPrograms(sectionId) {
        if (!sectionId) return;
        
        $('#program_id').html('<option value="">Loading...</option>');
        $('#class_id').html('<option value=""><?php echo $this->lang->line("select"); ?></option>');
        
        $.ajax({
            url: '<?php echo base_url(); ?>programs/getBySection', 
            method: 'POST',
            data: {
                section_id: sectionId,
                <?php echo $this->security->get_csrf_token_name(); ?>: '<?php echo $this->security->get_csrf_hash(); ?>'
            },
            dataType: 'json',
            success: function (response) {
                var html = '<option value=""><?php echo $this->lang->line("select"); ?></option>';
                
                if(response && response.length > 0) {
                    $.each(response, function (index, program) {
                        var selected = (selectedProgram == program.id) ? 'selected' : '';
                        html += '<option value="' + program.id + '" ' + selected + '>' + program.title + '</option>';
                    });
                } else {
                    html += '<option value="" disabled>No programs found</option>';
                }
                
                $('#program_id').html(html);
                
                // If there's a pre-selected program, load classes
                if(selectedProgram) {
                    loadClasses(sectionId, selectedProgram);
                }
            },
            error: function(xhr, status, error) {
                console.error("Error fetching programs:", error);
                $('#program_id').html('<option value="">Error loading programs</option>');
            }
        });
    }
    
    function loadClasses(sectionId, programId) {
        if (!sectionId || !programId) return;
        
        $('#class_id').html('<option value="">Loading...</option>');
        
        $.ajax({
            url: '<?php echo base_url(); ?>classes/getBySectionAndProgram',
            method: 'POST',
            data: {
                section_id: sectionId,
                program_id: programId,
                <?php echo $this->security->get_csrf_token_name(); ?>: '<?php echo $this->security->get_csrf_hash(); ?>'
            },
            dataType: 'json',
            success: function(response) {
                var html = '<option value=""><?php echo $this->lang->line("select"); ?></option>';
                
                if (response && response.length > 0) {
                    $.each(response, function(index, classItem) {
                        var selected = (selectedClass == classItem.id) ? 'selected' : '';
                        html += '<option value="' + classItem.id + '" ' + selected + '>' + 
                               (classItem.degree ? classItem.degree + ' - ' : '') + 
                               classItem.class + '</option>';
                    });
                } else {
                    html += '<option value="" disabled>No classes found for this combination</option>';
                }
                
                $('#class_id').html(html);
            },
            error: function(xhr, status, error) {
                console.error("AJAX Error:", status, error);
                $('#class_id').html('<option value="">Error loading classes</option>');
            }
        });
    }
    
    // Event handlers
    $('#section_id').on('change', function() {
        var sectionId = $(this).val();
        selectedSection = sectionId;
        selectedProgram = '';
        selectedClass = '';
        
        if (sectionId) {
            loadPrograms(sectionId);
        } else {
            $('#program_id').html('<option value=""><?php echo $this->lang->line("select"); ?></option>');
            $('#class_id').html('<option value=""><?php echo $this->lang->line("select"); ?></option>');
        }
    });
    
    $('#program_id').on('change', function() {
        var programId = $(this).val();
        var sectionId = $('#section_id').val();
        selectedProgram = programId;
        selectedClass = '';
        
        if (sectionId && programId) {
            loadClasses(sectionId, programId);
        } else {
            $('#class_id').html('<option value=""><?php echo $this->lang->line("select"); ?></option>');
        }
    });
});
</script>

<!-- View: HTML preview in new tab (no auto print) -->
<script>
$(document).on('click', '.view_marksheet', function () {
  var ecbes_id = String($(this).data('exam_group_class_batch_exam_student_id') || '');
  submitMarksheetPrintView([ecbes_id], false);
});
</script>
