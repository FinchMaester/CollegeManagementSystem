<?php
$currency_symbol = $this->customlib->getSchoolCurrencyFormat();
?>
<div class="content-wrapper">
    <section class="content-header">
        <h1>
            <i class="fa fa-bus"></i> <?php //echo $this->lang->line('transport'); ?></h1>
    </section> 
    <!-- Main content -->
    <section class="content">
        <?php $this->load->view('reports/_studentinformation') ?>
        <div class="row">
            <div class="col-md-12">
                <div class="box removeboxmius">
                    <div class="box-header ptbnull"></div>
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-search"></i> <?php echo $this->lang->line('select_criteria'); ?></h3>
                    </div>
                    <form role="form" action="<?php echo site_url('report/sibling_report') ?>" method="post" class="" id="class_search_form"  >
                        <div class="box-body row">
                            <?php echo $this->customlib->getCSRF(); ?>
                            <div class="col-sm-6 col-md-4">
                                    <div class="form-group">
                                        <label><?php echo $this->lang->line('section'); ?></label><small class="req"> *</small>
                                        <select id="section_id" name="section_id" class="form-control">
                                            <option value=""><?php echo $this->lang->line('select'); ?></option>
                                        </select>
                                        <span class="text-danger"><?php echo form_error('section_id'); ?></span>
                                    </div>
                                </div>
                                <div class="col-sm-6 col-md-4">
                                    <div class="form-group">
                                        <label for="exampleInputEmail1"><?php echo $this->lang->line('program'); ?></label><small class="req"> *</small>
                                        <select id="program_id" name="program_id" class="form-control">
                                            <option value=""><?php echo $this->lang->line('select'); ?></option>
                                        </select>
                                        <span class="text-danger"><?php echo form_error('program_id'); ?></span>
                                    </div>
                                </div>
                                <div class="col-sm-6 col-md-4">
                                    <div class="form-group">
                                        <label><?php echo $this->lang->line('class'); ?></label> <small class="req"> *</small>
                                        <select id="class_id" name="class_id" class="form-control">
                                            <option value=""><?php echo $this->lang->line('select'); ?></option>
                                        </select>
                                        <span class="text-danger" id="error_class_id"></span>
                                    </div>
                                </div>
                            <div class="form-group">
                                <div class="col-sm-12">
                                    <button type="submit" name="search" value="search_filter" class="btn btn-primary btn-sm checkbox-toggle pull-right"><i class="fa fa-search"></i> <?php echo $this->lang->line('search'); ?></button>
                                </div>
                            </div>
                        </div>    
                    </form>
                    <div class="">
                        <div class="box-header ptbnull"></div>
                        <div class="box-header ptbnull">
                            <h3 class="box-title titlefix"><i class="fa fa-money"> </i> <?php echo $this->lang->line('sibling_report'); ?></h3>
                        </div>
                        <div class="box-body table-responsive">                            
                            <div class="download_label"><?php echo $this->lang->line('sibling_report')." ".$this->customlib->get_postmessage();
                                            ?></div>
                            <table class="table table-striped table-bordered table-hover example">
                                <thead>
                                    <tr>
                                        <?php if ($sch_setting->father_name) { ?>
                                            <th><?php echo $this->lang->line('father_name'); ?></th>
<?php } if ($sch_setting->mother_name) { ?>
                                            <th><?php echo $this->lang->line('mother_name'); ?></th>
<?php } if($sch_setting->guardian_name){?>
                                        <th><?php echo $this->lang->line('guardian_name') ?></th>
                                        <?php } if($sch_setting->guardian_phone){?>
                                        <th><?php echo $this->lang->line('guardian_phone') ?></th>
                                    <?php } ?>
                                        <th><?php echo $this->lang->line('student_name_sibling'); ?></th>
                                        <th><?php echo $this->lang->line('class'); ?></th>
<?php if ($sch_setting->admission_date) { ?>
                                            <th><?php echo $this->lang->line('admission_date'); ?></th>
<?php } ?>
                                        <th><?php echo $this->lang->line('gender'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($resultlist)) {    ?>
                                        <?php
                                    } else {
                                        $count = 1;
                                        foreach ($resultlist as $student) {
                                            if (count($student) > 1) {
                                                ?>
                                                <tr>
                                                <?php if ($sch_setting->father_name) { ?>
                                                    <td><?php echo $student[0]['father_name']; ?></td>
                                                <?php } if ($sch_setting->mother_name) { ?>
                                                    <td><?php echo $student[0]['mother_name']; ?></td>
                                                <?php } if($sch_setting->guardian_name){?>
                                                    <td><?php echo $student[0]['guardian_name']; ?></td>
                                                      <?php } if($sch_setting->guardian_phone){?>
                                                    <td><?php echo $student[0]['guardian_phone']; ?></td>
                                                <?php } ?>
                                                    <td>
                                                        <table>
                                                    <?php foreach ($student as $value) { ?>
                                                            <tr>
                                                                <td>
                                                                    <a href="<?php echo base_url(); ?>student/view/<?php echo $value['id']; ?>"><?php echo $this->customlib->getFullName($value['firstname'],$value['middlename'],$value['lastname'],$sch_setting->middlename,$sch_setting->lastname).' ('.$value['admission_no'].')'; ?></a> 
                                                                </td>
                                                            </tr>
                                                    <?php } ?>
                                                        </table>
                                                    </td>
                                                    <td>
                                                        <table>
                                                            <?php foreach ($student as $value) { ?>
                                                                <tr>
                                                                    <td>
                                                                <?php echo $value['class'] . " (" . $value['section'] . ")"; ?>
                                                                    </td>
                                                                </tr>                                                
                                                            <?php } ?>
                                                        </table>
                                                    </td>
                                                <?php if ($sch_setting->admission_date) { ?>
                                                    <td>
                                                        <table>
                                                            <?php foreach ($student as $value) { ?>  
                                                                <tr>
                                                                    <td>
                                                                        <?php
                                                                        if (!empty($value['admission_date'])) {
                                                                            echo date($this->customlib->getSchoolDateFormat(), $this->customlib->dateyyyymmddTodateformat($value['admission_date']));
                                                                        }
                                                                        ?>     
                                                                    </td>
                                                                </tr>
                                                            <?php } ?>
                                                        </table>
                                                    </td>
                                                <?php } ?>
                                        <td class="pull-right">
                                            <table width="100%">
                                                        <?php foreach ($student as $value) { ?>
                                                    <tr><td >
                                                            <?php
                                                            if (!empty($value['gender'])) {
                                                                echo $this->lang->line(strtolower($value['gender']));
                                                            }
                                                            ?>
                                                        </td></tr>
                                        <?php } ?>
                                            </table>
                                        </td>
                                        </tr>
                                        <?php
                                        $count++;
                                    }
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