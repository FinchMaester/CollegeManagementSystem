<div class="content-wrapper">
    <section class="content-header">
        <h1>
            <i class="fa fa-graduation-cap"></i> <?php echo $this->lang->line('graduation_report'); ?>
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
                    <form role="form" action="<?php echo site_url('report/graduation_report') ?>" method="post" class="">
                        <div class="box-body row">
                            <?php echo $this->customlib->getCSRF(); ?>
                            <div class="col-sm-2 col-md-2">
                                    <div class="form-group">
                                        <label><?php echo $this->lang->line('section'); ?></label><small class="req"> *</small>
                                        <select id="section_id" name="section_id" class="form-control">
                                            <option value=""><?php echo $this->lang->line('select'); ?></option>
                                        </select>
                                        <span class="text-danger"><?php echo form_error('section_id'); ?></span>
                                    </div>
                                </div>
                                <div class="col-sm-2 col-md-2">
                                    <div class="form-group">
                                        <label for="exampleInputEmail1"><?php echo $this->lang->line('program'); ?></label><small class="req"> *</small>
                                        <select id="program_id" name="program_id" class="form-control">
                                            <option value=""><?php echo $this->lang->line('select'); ?></option>
                                        </select>
                                        <span class="text-danger"><?php echo form_error('program_id'); ?></span>
                                    </div>
                                </div>
                                <div class="col-sm-2 col-md-2">
                                    <div class="form-group">
                                        <label><?php echo $this->lang->line('class'); ?></label> <small class="req"> *</small>
                                        <select id="class_id" name="class_id" class="form-control">
                                            <option value=""><?php echo $this->lang->line('select'); ?></option>
                                        </select>
                                        <span class="text-danger" id="error_class_id"></span>
                                    </div>
                                </div>
                            <div class="col-sm-2 col-md-2">
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
                            <div class="col-sm-2 col-md-2">
                                <div class="form-group">
                                    <label><?php echo $this->lang->line('gender'); ?></label>
                                    <select id="gender" name="gender" class="form-control">
                                        <option value=""><?php echo $this->lang->line('select'); ?></option>
                                        <option value="Male" <?php if (set_value('gender') == 'Male' || $gender == 'Male') echo "selected=selected" ?>>Male</option>
                                        <option value="Female" <?php if (set_value('gender') == 'Female' || $gender == 'Female') echo "selected=selected" ?>>Female</option>
                                        <option value="Other" <?php if (set_value('gender') == 'Other' || $gender == 'Other') echo "selected=selected" ?>>Other</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-sm-2 col-md-2">
                                <div class="form-group">
                                    <label><?php echo $this->lang->line('caste'); ?></label>
                                    <select id="caste" name="caste" class="form-control">
                                        <option value=""><?php echo $this->lang->line('select'); ?></option>
                                        <option value="Brahmin" <?php if (set_value('caste') == 'Brahmin' || $caste == 'Brahmin') echo "selected=selected" ?>>Brahmin</option>
                                        <option value="Chhetri" <?php if (set_value('caste') == 'Chhetri' || $caste == 'Chhetri') echo "selected=selected" ?>>Chhetri</option>
                                        <option value="Dalit" <?php if (set_value('caste') == 'Dalit' || $caste == 'Dalit') echo "selected=selected" ?>>Dalit</option>
                                        <option value="Janajati" <?php if (set_value('caste') == 'Janajati' || $caste == 'Janajati') echo "selected=selected" ?>>Janajati</option>
                                        <option value="Kami" <?php if (set_value('caste') == 'Kami' || $caste == 'Kami') echo "selected=selected" ?>>Kami</option>
                                        <option value="Limbu" <?php if (set_value('caste') == 'Limbu' || $caste == 'Limbu') echo "selected=selected" ?>>Limbu</option>
                                        <option value="Madhesi" <?php if (set_value('caste') == 'Madhesi' || $caste == 'Madhesi') echo "selected=selected" ?>>Madhesi</option>
                                        <option value="Magar" <?php if (set_value('caste') == 'Magar' || $caste == 'Magar') echo "selected=selected" ?>>Magar</option>
                                        <option value="Muslim" <?php if (set_value('caste') == 'Muslim' || $caste == 'Muslim') echo "selected=selected" ?>>Muslim</option>
                                        <option value="Newar" <?php if (set_value('caste') == 'Newar' || $caste == 'Newar') echo "selected=selected" ?>>Newar</option>
                                        <option value="Rai" <?php if (set_value('caste') == 'Rai' || $caste == 'Rai') echo "selected=selected" ?>>Rai</option>
                                        <option value="Sarki" <?php if (set_value('caste') == 'Sarki' || $caste == 'Sarki') echo "selected=selected" ?>>Sarki</option>
                                        <option value="Tamang" <?php if (set_value('caste') == 'Tamang' || $caste == 'Tamang') echo "selected=selected" ?>>Tamang</option>
                                        <option value="Tharu" <?php if (set_value('caste') == 'Tharu' || $caste == 'Tharu') echo "selected=selected" ?>>Tharu</option>
                                        <option value="Others" <?php if (set_value('caste') == 'Others' || $caste == 'Others') echo "selected=selected" ?>>Others</option>
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
                        <h3 class="box-title titlefix"><i class="fa fa-graduation-cap"></i> <?php echo $this->lang->line('graduated_students_list'); ?></h3>
                        <div class="box-tools pull-right">
                            <button class="btn btn-default btn-xs" id="print" onclick="printDiv()"><i class="fa fa-print"></i> <?php echo $this->lang->line('print'); ?></button>
                            <a href="<?php echo base_url(); ?>report/graduation_report_export" class="btn btn-default btn-xs"><i class="fa fa-file-excel-o"></i> <?php echo $this->lang->line('export_to_excel'); ?></a>
                        </div>
                    </div>
                    <div class="box-body" id="printarea">
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
                                        <th><?php echo $this->lang->line('caste'); ?></th>
                                        <th><?php echo $this->lang->line('date_of_birth'); ?></th>
                                        <th><?php echo $this->lang->line('guardian_name'); ?></th>
                                        <th><?php echo $this->lang->line('graduation_date'); ?></th>
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
                                        <td><?php echo isset($student['cast']) ? $student['cast'] : ''; ?></td>
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
                                    </tr>
                                    <?php }
                                    } ?>
                                </tbody>
                            </table>
                        </div>
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

