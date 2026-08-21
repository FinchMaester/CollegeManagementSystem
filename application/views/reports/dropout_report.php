<div class="content-wrapper">
    <section class="content-header">
        <h1>
            <i class="fa fa-user-times"></i> <?php echo $this->lang->line('dropout_report'); ?>
        </h1>
    </section>
    <section class="content">
        <?php $this->load->view('reports/_studentinformation'); ?>
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-search"></i> <?php echo $this->lang->line('select_criteria'); ?></h3>
                    </div>
                    <form role="form" action="<?php echo site_url('report/dropout_report') ?>" method="post" class="">
                        <div class="box-body row">
                            <?php echo $this->customlib->getCSRF(); ?>
                            <div class="col-sm-3 col-md-3">
                                    <div class="form-group">
                                        <label><?php echo $this->lang->line('section'); ?></label><small class="req"> *</small>
                                        <select id="section_id" name="section_id" class="form-control">
                                            <option value=""><?php echo $this->lang->line('select'); ?></option>
                                        </select>
                                        <span class="text-danger"><?php echo form_error('section_id'); ?></span>
                                    </div>
                                </div>
                                <div class="col-sm-3 col-md-3">
                                    <div class="form-group">
                                        <label for="exampleInputEmail1"><?php echo $this->lang->line('program'); ?></label><small class="req"> *</small>
                                        <select id="program_id" name="program_id" class="form-control">
                                            <option value=""><?php echo $this->lang->line('select'); ?></option>
                                        </select>
                                        <span class="text-danger"><?php echo form_error('program_id'); ?></span>
                                    </div>
                                </div>
                                <div class="col-sm-3 col-md-3">
                                    <div class="form-group">
                                        <label><?php echo $this->lang->line('class'); ?></label> <small class="req"> *</small>
                                        <select id="class_id" name="class_id" class="form-control">
                                            <option value=""><?php echo $this->lang->line('select'); ?></option>
                                        </select>
                                        <span class="text-danger" id="error_class_id"></span>
                                    </div>
                                </div>
                            <div class="col-sm-3 col-md-3">
                                <div class="form-group">
                                    <label><?php echo $this->lang->line('session'); ?></label>
                                    <select id="session_id" name="session_id" class="form-control" >
                                        <option value=""><?php echo $this->lang->line('select'); ?></option>
                                        <?php foreach ($sessions as $session) { ?>
                                            <option value="<?php echo $session['id'] ?>" <?php if (set_value('session_id') == $session['id'] || $session_id == $session['id']) echo "selected=selected" ?>>
                                                <?php echo $session['session'] ?>
                                            </option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-sm-3 col-md-3">
                                <div class="form-group">
                                    <label><?php echo $this->lang->line('gender'); ?></label>
                                    <select id="gender" name="gender" class="form-control">
                                        <option value=""><?php echo $this->lang->line('all'); ?></option>
                                        <option value="Male" <?php echo set_select('gender', 'Male', ($gender == 'Male')); ?>><?php echo $this->lang->line('male'); ?></option>
                                        <option value="Female" <?php echo set_select('gender', 'Female', ($gender == 'Female')); ?>><?php echo $this->lang->line('female'); ?></option>
                                        <option value="Other" <?php echo set_select('gender', 'Other', ($gender == 'Other')); ?>><?php echo $this->lang->line('other'); ?></option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-sm-12">
                                <div class="form-group">
                                    <button type="submit" name="search" value="search" class="btn btn-primary btn-sm pull-right"><i class="fa fa-search"></i> <?php echo $this->lang->line('search'); ?></button>
                                </div>
                            </div>
                        </div>
                    </form>
                    
                
                    <?php if (isset($studentlist)) { ?>
                    <div class="box-header ptbnull">
                        <h3 class="box-title titlefix"><i class="fa fa-user-times"></i> <?php echo $this->lang->line('dropout_students_list'); ?></h3>
                        <div class="box-tools pull-right">
                            <button class="btn btn-default btn-xs" id="print" onclick="printDiv()"><i class="fa fa-print"></i> <?php echo $this->lang->line('print'); ?></button>
                            <a href="<?php echo base_url(); ?>report/dropout_report_export" class="btn btn-default btn-xs"><i class="fa fa-file-excel-o"></i> <?php echo $this->lang->line('export_to_excel'); ?></a>
                        </div>
                    </div>
                    <div class="box-body" id="printarea">
                        <?php if (!empty($search_note)) { ?>
                        <div class="alert alert-info">
                            <i class="fa fa-info-circle"></i> <?php echo $search_note; ?>
                        </div>
                        <?php } ?>
                        
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered table-hover example">
                                <thead>
                                    <tr>
                                        <th><?php echo $this->lang->line('admission_no'); ?></th>
                                        <th><?php echo $this->lang->line('student_name'); ?></th>
                                        <th><?php echo $this->lang->line('academic_level'); ?></th>
                                        <th><?php echo $this->lang->line('faculty'); ?></th>
                                        <th><?php echo $this->lang->line('program'); ?></th>
                                        <th><?php echo $this->lang->line('gender'); ?></th>
                                        <th><?php echo $this->lang->line('date_of_birth'); ?></th>
                                        <th><?php echo $this->lang->line('guardian_name'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($studentlist)) { ?>
                                    <tr>
                                        <td colspan="10" class="text-center"><?php echo $this->lang->line('no_record_found'); ?></td>
                                    </tr>
                                    <?php } else {
                                        foreach ($studentlist as $student) { ?>
                                    <tr>
                                        <td><?php echo $student['admission_no']; ?></td>
                                        <td>
                                        <?php 
                                          // Safely get name components with null checks
                                          $firstname = isset($student['firstname']) ? $student['firstname'] : '';
                                          $middlename = isset($student['middlename']) ? $student['middlename'] : '';
                                          $lastname = isset($student['lastname']) ? $student['lastname'] : '';

                                          // Safely get settings with null checks
                                          $use_middlename = isset($sch_setting->middlename) ? $sch_setting->middlename : false;
                                          $use_lastname = isset($sch_setting->lastname) ? $sch_setting->lastname : false;

                                          echo $this->customlib->getFullName($firstname, $middlename, $lastname, $use_middlename, $use_lastname); 
                                        ?>
                                        </td>
                                        <td><?php echo $student['class']; ?></td>
                                        <td><?php echo $student['section']; ?></td>
                                        <td><?php echo $student['program_name']; ?></td>
                                        <td><?php echo $student['gender']; ?></td>
                                        <td>
                                            <?php 
                                            if($student['dob'] != '' && $student['dob'] != '0000-00-00') {
                                                echo date($this->customlib->getSchoolDateFormat(), $this->customlib->dateyyyymmddTodateformat($student['dob']));
                                            }
                                            ?>
                                        </td>
                                        <td><?php echo $student['guardian_name']; ?></td>
                                        <td>
                                            <?php 
                                            if($student['updated_at'] != '' && $student['updated_at'] != '0000-00-00') {
                                                echo date($this->customlib->getSchoolDateFormat(), strtotime($student['updated_at']));
                                            }
                                            ?>
                                        </td>
                                        <td><?php echo $student['dis_note']; ?></td>
                                    </tr>
                                    <?php } 
                                    } ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <?php if (!empty($studentlist)) { ?>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="alert alert-success">
                                    <strong>Total Dropout Students: <?php echo count($studentlist); ?></strong>
                                </div>
                            </div>
                        </div>
                        <?php } ?>
                    </div>
                    <?php } ?>
                </div>
            </div>
        </div>
    </section>
</div>

<script type="text/javascript">
$(document).ready(function () {
    // Fetch all sections
    $.ajax({
        url: '<?php echo base_url(); ?>sections/getAll',
        method: 'GET',
        dataType: 'json',
        success: function (response) {
            var section_id = '<?php echo set_value("section_id") ?>';
            var html = '<option value=""><?php echo $this->lang->line("select"); ?></option>';
            
            $.each(response, function (index, section) {
                var selected = (section_id == section.id) ? 'selected' : '';
                html += '<option value="' + section.id + '" ' + selected + '>' + section.section + '</option>';
            });
            
            $('#section_id').html(html);
            
            // If there's a pre-selected section, trigger program load
            if(section_id) {
                $('#section_id').trigger('change');
            }
        }
    });
});

// When section changes, fetch programs
$(document).on('change', '#section_id', function (e) {
    var section_id = $(this).val();
    
    // Clear program and class dropdowns
    $('#program_id').html('<option value=""><?php echo $this->lang->line("select"); ?></option>');
    $('#class_id').html('<option value=""><?php echo $this->lang->line("select"); ?></option>');
    
    if (section_id != '') {
        // Show loading indicator
        $('#program_id').html('<option value="">Loading...</option>');
        
        // Fetch programs for this section
        $.ajax({
            url: '<?php echo base_url(); ?>programs/getBySection', 
            method: 'POST',
            data: {section_id: section_id},
            dataType: 'json',
            success: function (response) {
                var program_id = '<?php echo set_value("program_id") ?>';
                var html = '<option value=""><?php echo $this->lang->line("select"); ?></option>';
                
                if(response.length > 0) {
                    $.each(response, function (index, program) {
                        var selected = (program_id == program.id) ? 'selected' : '';
                        html += '<option value="' + program.id + '" ' + selected + '>' + program.title + '</option>';
                    });
                } else {
                    html += '<option value="" disabled>No programs found</option>';
                }
                
                $('#program_id').html(html);
                
                // If there's a pre-selected program, trigger class load
                if(program_id) {
                    $('#program_id').trigger('change');
                }
            },
            error: function(xhr, status, error) {
                console.error("Error fetching programs:", error);
                console.error("Response:", xhr.responseText);
                $('#program_id').html('<option value="">Error loading programs</option>');
            }
        });
    }
});

// When program changes, fetch classes
$(document).on('change', '#program_id', function (e) {
    var section_id = $('#section_id').val();
    var program_id = $(this).val();
    
    console.log("Fetching classes for Section:", section_id, "Program:", program_id);
    
    $('#class_id').html('<option value="">Loading...</option>');
    
    $.ajax({
        url: '<?php echo base_url(); ?>classes/getBySectionAndProgram',
        method: 'POST',
        data: {
            section_id: section_id,
            program_id: program_id,
            <?php echo $this->security->get_csrf_token_name(); ?>: '<?php echo $this->security->get_csrf_hash(); ?>'
        },
        dataType: 'json',
        success: function(response) {
            console.log("Raw response:", response);
            
            var class_id = '<?php echo set_value("class_id") ?>'; // ADD THIS LINE
            var html = '<option value=""><?php echo $this->lang->line("select"); ?></option>';
            
            if (response && response.length > 0) {
                $.each(response, function(index, classItem) {
                    // ADD SELECTED LOGIC HERE
                    var selected = (class_id == classItem.id) ? 'selected' : '';
                    html += '<option value="' + classItem.id + '" ' + selected + '>' + 
                           (classItem.degree ? classItem.degree + ' - ' : '') + 
                           classItem.class + '</option>';
                });
            } else {
                html += '<option value="" disabled>No classes found for this combination</option>';
                console.warn("No classes found for Section:", section_id, "Program:", program_id);
            }
            
            $('#class_id').html(html);
        },
        error: function(xhr, status, error) {
            console.error("AJAX Error:", status, error);
            console.error("Response:", xhr.responseText);
            $('#class_id').html('<option value="">Error loading classes</option>');
        }
    });
});

function printDiv() {
    var printContents = document.getElementById('printarea').innerHTML;
    var originalContents = document.body.innerHTML;
    document.body.innerHTML = printContents;
    window.print();
    document.body.innerHTML = originalContents;
}
</script>