<?php
$currency_symbol = $this->customlib->getSchoolCurrencyFormat();
?>
<div class="content-wrapper" style="min-height: 946px;">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            <i class="fa fa-money"></i> <?php echo $this->lang->line('fees_collection'); ?> </h1>
    </section>
    <!-- Main content -->
    <section class="content">
        <div class="row">  
            <form id='feesforward' action="<?php echo site_url('admin/feesforward/index') ?>"  method="post" accept-charset="utf-8">
                <div class="col-md-12">
                    <div class="box box-primary">
                        <div class="box-header with-border">
                            <h3 class="box-title"><i class="fa fa-search"></i> <?php echo $this->lang->line('select_criteria'); ?></h3>
                            <div class="box-tools pull-right">
                            </div>
                        </div>
                        <div class="box-body">
                            <?php echo $this->customlib->getCSRF(); ?>
                            <div class="row">
                                <div class="col-md-12">                                   
                                    <?php if ($this->session->flashdata('msg')) { ?>
                                        <?php echo $this->session->flashdata('msg');
                                        $this->session->unset_userdata('msg'); ?>
                                    <?php } ?>
                                </div>
                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label><?php echo $this->lang->line('section'); ?></label><small class="req"> *</small>
                                        <select id="section_id" name="section_id" class="form-control">
                                            <option value=""><?php echo $this->lang->line('select'); ?></option>
                                        </select>
                                        <span class="text-danger"><?php echo form_error('section_id'); ?></span>
                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label for="exampleInputEmail1"><?php echo $this->lang->line('program'); ?></label><small class="req"> *</small>
                                        <select id="program_id" name="program_id" class="form-control">
                                            <option value=""><?php echo $this->lang->line('select'); ?></option>
                                        </select>
                                        <span class="text-danger"><?php echo form_error('program_id'); ?></span>
                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label><?php echo $this->lang->line('class'); ?></label> <small class="req"> *</small>
                                        <select id="class_id" name="class_id" class="form-control">
                                            <option value=""><?php echo $this->lang->line('select'); ?></option>
                                        </select>
                                        <span class="text-danger" id="error_class_id"></span>
                                    </div>
                                </div>

                            </div>
                            <div class="">
                                <button type="submit" name="action" value ="search" class="btn btn-primary pull-right"><?php echo $this->lang->line('search'); ?></button>
                            </div>
                        </div> 

                        <?php
                        if (isset($student_due_fee)) {
                            ?>
                            <div class="box-header ptbnull"></div>   
                            <div class="">
                                <div class="box-header with-border">
                                    <h3 class="box-title titlefix"><?php echo $this->lang->line('previous_session_balance_fees'); ?></h3>
                                    <div class="pull-right">
                                        <span class="text text-danger pt6 bolds"><?php echo $this->lang->line('due_date'); ?>:</span> <?php echo set_value('due_date', $due_date_formated); ?>
                                        <input id="due_date" name="due_date" placeholder="" type="hidden" class="form-control date"  value="<?php echo set_value('due_date', $due_date_formated); ?>" readonly /> 
                                    </div>  
                                </div>
                                <div class="box-body">
                                    <?php
                                    if (!empty($student_due_fee)) {
                                        ?>

                                        <div class="row">
                                            <div class="col-md-12">
                                                <?php
                                                if ($is_update) {
                                                    ?>
                                                    <div class="alert alert-info"><?php echo $this->lang->line('previous_balance_already_forwarded'); ?></div>
                                                    <?php
                                                }
                                                ?>
                                            </div>
                                            <div class="row col-xs-12">
                                                <div class="col-md-4">                          

                                                </div>
                                            </div>
                                            <div class="col-xs-12 table-responsive">
                                                <div class="download_label"><?php echo $this->lang->line('previous_session_balance_fees'); ?></div>
                                                <table class="table table-striped example">
                                                    <thead>
                                                        <tr>
                                                            <th class="text text-left"><?php echo $this->lang->line('student_name'); ?></th> 
                                                            <th class="text text-left"><?php echo $this->lang->line('admission_no'); ?></th>
                                                            <?php if ($sch_setting->admission_date) { ?>
                                                                <th class="text text-left"><?php echo $this->lang->line('admission_date'); ?></th>
                                                            <?php } if ($sch_setting->roll_no) { ?>
                                                                <th class="text text-left"><?php echo $this->lang->line('roll_number'); ?></th>
                                                            <?php } if ($sch_setting->father_name) { ?>
                                                                <th class="text text-left"><?php echo $this->lang->line('father_name'); ?></th><?php } ?>
                                                            <th class="text-right"><?php echo $this->lang->line('balance'); ?> <span><?php echo "(" . $currency_symbol . ")"; ?></span></th>
                                                        </tr>
                                                    </thead> 
                                                    <tbody>
                                                        <?php
                                                        $i = 1;
                                                        foreach ($student_due_fee as $due_fee_key => $due_fee_value) {
                                                           
                                                            ?>
                                                            <tr>
                                                                <td>
                                                                    <input type="hidden" name="student_counter[]" value="<?php echo $i; ?>">
                                                                    <input type="hidden" name="student_sesion[<?php echo $i; ?>]" value="<?php echo $due_fee_value->student_session_id; ?>">
                                                                    <?php echo $due_fee_value->name ?></td>  

                                                                <td><?php echo $due_fee_value->admission_no; ?></td>
                                                                <?php if ($sch_setting->admission_date) { ?>
                                                                    <td><?php echo $due_fee_value->admission_date; ?></td>
                                                                <?php } if ($sch_setting->roll_no) { ?>
                                                                    <td><?php echo $due_fee_value->roll_no; ?></td>
                                                                <?php } if ($sch_setting->father_name) { ?>
                                                                    <td><?php echo $due_fee_value->father_name; ?></td>
                                                                <?php } ?>
                                                                <td class="text text-right">
																	<span class="hidden"><?php echo convertBaseAmountCurrencyFormat($due_fee_value->balance); ?></span>
                                                                    <input type="text" name="amount[<?php echo $i; ?>]" class="form-control tddm200" value="<?php echo convertBaseAmountCurrencyFormat($due_fee_value->balance); ?>">
                                                                </td>
                                                            </tr>
                                                            <?php
                                                            $i++;
                                                        }
                                                        ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="box-footer">
                                                <button type="submit" name="action" value="fee_submit" class="btn btn-primary pull-right"><?php echo $this->lang->line('save'); ?></button>
                                            </div>
                                        </div>
                                        <?php
                                    } else {
                                        ?>
                                        <div class="alert alert-info"><?php echo $this->lang->line('no_record_found'); ?>
                                        </div>
                                        <?php
                                    }
                                    ?>
                                </div>
                            </div>
                        </div> 
                        <?php
                    }
                    ?>
                </div>
            </form>
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
            
            var class_id = '<?php echo set_value("class_id") ?>'; 
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
    $(document).ready(function () {
        $.extend($.fn.dataTable.defaults, {
            searching: true,
            ordering: true,
            paging: false,
            retrieve: true,
            destroy: true,
            info: false
        });
    });
</script>
