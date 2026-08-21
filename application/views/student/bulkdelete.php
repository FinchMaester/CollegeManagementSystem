<div class="content-wrapper">
    <!-- Main content -->
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-search"></i> <?php echo $this->lang->line('select_criteria'); ?></h3>
                    </div>
                    <div class="box-body pb0">
                        <div class="row">  
                            <?php if ($this->session->flashdata('msg')) { ?> <div class="alert alert-success">  <?php echo $this->session->flashdata('msg'); $this->session->unset_userdata('msg'); ?></div> <?php } ?>
                            <form role="form" action="<?php echo site_url('student/bulkdelete') ?>" method="post" class="">
                                <?php echo $this->customlib->getCSRF(); ?>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label><?php echo $this->lang->line('section'); ?></label><small class="req"> *</small>
                                        <select id="section_id" name="section_id" class="form-control">
                                            <option value=""><?php echo $this->lang->line('select'); ?></option>
                                        </select>
                                        <span class="text-danger"><?php echo form_error('section_id'); ?></span>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="exampleInputEmail1"><?php echo $this->lang->line('program'); ?></label><small class="req"> *</small>
                                        <select id="program_id" name="program_id" class="form-control">
                                            <option value=""><?php echo $this->lang->line('select'); ?></option>
                                        </select>
                                        <span class="text-danger"><?php echo form_error('program_id'); ?></span>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label><?php echo $this->lang->line('class'); ?></label> <small class="req"> *</small>
                                        <select id="class_id" name="class_id" class="form-control">
                                            <option value=""><?php echo $this->lang->line('select'); ?></option>
                                        </select>
                                        <span class="text-danger" id="error_class_id"></span>
                                    </div>
                                </div>
                                <div class="col-sm-12">
                                    <div class="form-group">
                                        <button type="submit" name="search" value="search_filter" class="btn btn-primary btn-sm pull-right checkbox-toggle"><i class="fa fa-search"></i> <?php echo $this->lang->line('search'); ?></button>
                                    </div>
                                </div>
                            </form>
                        </div>  
                    </div>

                    <div class="box-body pt0">
                        <div class="row ">
                            <div class="col-md-12 col-sm-12">
                                <form class="mt10" action="<?php echo site_url('student/ajax_delete') ?>" method="POST" id="deletebulk">
                                    <?php echo $this->customlib->getCSRF(); ?>
                                    <?php
                                    if (isset($resultlist)) {
                                        ?>                   
                                        <div class="checkbox bordertop pt15">
                                            <label><input type="checkbox" name="checkAll"> <b><?php echo $this->lang->line('select_all'); ?></b> </label>
                                            <?php
                                            if (!empty($resultlist)) {
                                                ?>                              
                                                <button type="submit" class="btn btn-primary pull-right btn-sm " id="load" data-loading-text="<i class='fa fa-spinner fa-spin '></i>  <?php echo $this->lang->line('please_wait'); ?>"> <?php echo $this->lang->line('delete') ?></button>
                                                <?php
                                            }
                                            ?>
                                        </div> 
                                        <div class="table-responsive pt15">
                                            <div class="download_label"><?php echo $this->lang->line('bulk_delete'); ?></div>
                                            <table class="table table-striped table-bordered table-hover example" cellspacing="0" width="100%">
                                                <thead>
                                                    <tr>
                                                        <th>#</th>
                                                        <th><?php echo $this->lang->line('admission_no'); ?></th>
                                                        <th><?php echo $this->lang->line('student_name'); ?></th>
                                                        <th><?php echo $this->lang->line('class'); ?></th>
                                                        <th><?php echo $this->lang->line('date_of_birth'); ?></th>
                                                        <th><?php echo $this->lang->line('gender'); ?></th>
                                                        <?php if ($sch_setting->category) { ?>
                                                            <th><?php echo $this->lang->line('category'); ?></th>
                                                        <?php } if ($sch_setting->mobile_no) { ?>
                                                            <th><?php echo $this->lang->line('mobile_number'); ?></th>
                                                            <?php
                                                        }
                                                        if (!empty($fields)) {

                                                            foreach ($fields as $fields_key => $fields_value) {
                                                                ?>
                                                                <th><?php echo $fields_value->name; ?></th>
                                                                <?php
                                                            }
                                                        }
                                                        ?>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php
                                                    if (empty($resultlist)) {
                                                        ?>

                                                        <?php
                                                    } else {
                                                        $count = 1;
                                                        foreach ($resultlist as $student) {
                                                            ?>
                                                            <tr>
                                                                <td>

                                                                    <input type="checkbox" name="student[]" value="<?php echo $student['id']; ?>">
                                                                </td>
                                                                <td><?php echo $student['admission_no']; ?></td>
                                                                <td>
                                                                    <a href="<?php echo base_url(); ?>student/view/<?php echo $student['id']; ?>"><?php echo $this->customlib->getFullName($student['firstname'],$student['middlename'],$student['lastname'],$sch_setting->middlename,$sch_setting->lastname); ?>
                                                                    </a>
                                                                </td>
                                                                <td><?php echo $student['class'] . "(" . $student['section'] . ")" ?></td>
                                                                <td>
    <?php
    if (!empty($student["dob"]) && $student["dob"] != '0000-00-00') {
        // Safely create a DateTime object from MySQL format (YYYY-MM-DD)
        $dob = DateTime::createFromFormat("Y-m-d", $student["dob"]);
        echo $dob ? $dob->format("d-m-Y") : $student["dob"]; 
    }
    ?>
</td>
                                                                <td><?php echo $this->lang->line(strtolower($student['gender'])); ?></td>
                                                                <?php if ($sch_setting->category) { ?>
                                                                    <td><?php echo $student['category']; ?></td>
                                                                <?php }if ($sch_setting->mobile_no) { ?>
                                                                    <td><?php echo $student['mobileno']; ?></td>
                                                                    <?php
                                                                }
                                                                if (!empty($fields)) {
                                                                    foreach ($fields as $fields_key => $fields_value) {
                                                                        $display_field = $student[$fields_value->name];
                                                                        if ($fields_value->type == "link") {
                                                                            $display_field = "<a href=" . $student[$fields_value->name] . " target='_blank'>" . $student[$fields_value->name] . "</a>";
                                                                        }
                                                                        ?>
                                                                        <td>
                                                                            <?php echo $display_field; ?>
                                                                        </td>
                                                                        <?php
                                                                    }
                                                                }
                                                                ?>
                                                            </tr>
                                                            <?php
                                                            $count++;
                                                        }
                                                    }
                                                    ?>
                                                </tbody>
                                            </table>             

                                            <?php
                                        }
                                        ?>                               </div>           
                                </form>
                            </div>
                        </div>
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
</script>
<script type="text/javascript">
    $("#deletebulk").submit(function (e) {
        e.preventDefault(); // avoid to execute the actual submit of the form.
        var checkCount = $("input[name='student[]']:checked").length;

        if (checkCount == 0)
        {
            alert("<?php echo $this->lang->line('atleast_one_student_should_be_select'); ?>");

        } else {
            if (confirm("<?php echo $this->lang->line('are_you_sure_you_want_to_delete'); ?>")) {

                var form = $(this);
                var url = form.attr('action');
                var submit_button = form.find(':submit');

                $.ajax({
                    type: "POST",
                    url: url,
                    data: form.serialize(), // serializes the form's elements.
                    dataType: "JSON", // serializes the form's elements.
                    beforeSend: function () {

                        submit_button.button('loading');
                    },
                    success: function (data)
                    {

                        var message = "";
                        if (!data.status) {

                            $.each(data.error, function (index, value) {

                                message += value;
                            });

                            errorMsg(message);

                        } else {
                            successMsg(data.message);
                            location.reload();
                        }
                    },
                    error: function (xhr) { // if error occured
                        submit_button.button('reset');
                        alert("<?php echo $this->lang->line('error_occurred_please_try_again'); ?>");

                    },
                    complete: function () {
                        submit_button.button('reset');
                    }
                });
            }
        }

    });

    $("input[name='checkAll']").click(function () {
        $("input[name='student[]']").not(this).prop('checked', this.checked);
    });
</script>