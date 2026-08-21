<?php
$currency_symbol = $this->customlib->getSchoolCurrencyFormat();
?>
<div class="content-wrapper" style="min-height: 946px;">   
    <section class="content-header">
        <h1>
            <i class="fa fa-money"></i> <?php echo $this->lang->line('fees_collection'); ?>  </h1>
    </section>
    <!-- Main content -->
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-search"></i> <?php echo $this->lang->line('select_criteria'); ?></h3>
                    </div>
                    <div class="box-body">
                        <form role="form" action="<?php echo site_url('admin/feediscount/assign/' . $id) ?>" method="post" class="row">
                            <?php echo $this->customlib->getCSRF(); ?>
                            <!-- Section/Program/Class (Academic Level) cascading dropdowns as in studentCreate.php -->
                            <div class="col-sm-3">
                                <div class="form-group">
                                    <label><?php echo $this->lang->line('section'); ?></label>
                                    <select id="section_id" name="section_id" class="form-control">
                                        <option value=""><?php echo $this->lang->line('select'); ?></option>
                                    </select>
                                    <span class="text-danger" id="error_section_id"><?php echo form_error('section_id'); ?></span>
                                </div>
                            </div>
                            <div class="col-sm-3">
                                <div class="form-group">
                                    <label><?php echo $this->lang->line('program'); ?></label>
                                    <select id="program_id" name="program_id" class="form-control">
                                        <option value=""><?php echo $this->lang->line('select'); ?></option>
                                    </select>
                                    <span class="text-danger" id="error_program_id"><?php echo form_error('program_id'); ?></span>
                                </div>
                            </div>
                            <div class="col-sm-3">
                                <div class="form-group">
                                    <label><?php echo $this->lang->line('class'); ?></label>
                                    <select id="class_id" name="class_id" class="form-control">
                                        <option value=""><?php echo $this->lang->line('select'); ?></option>
                                    </select>
                                    <span class="text-danger" id="error_class_id"><?php echo form_error('class_id'); ?></span>
                                </div>
                            </div>
                            <div class="col-sm-2">
                                <div class="form-group">  
                                    <label><?php echo $this->lang->line('category'); ?></label>
                                    <select  id="category_id" name="category_id" class="form-control" >
                                        <option value=""><?php echo $this->lang->line('select'); ?></option>
                                        <?php
                                        foreach ($categorylist as $category) {
                                            ?>
                                            <option value="<?php echo $category['id'] ?>" <?php if (set_value('category_id') == $category['id']) echo "selected=selected"; ?>><?php echo $category['category'] ?></option>
                                            <?php
                                            $count++;
                                        }
                                        ?>
                                    </select>
                                </div>  
                            </div>
                            <div class="col-sm-2">
                                <div class="form-group">  
                                    <label><?php echo $this->lang->line('gender'); ?></label>
                                    <select class="form-control" name="gender">
                                        <option value=""><?php echo $this->lang->line('select'); ?></option>
                                        <?php
                                        foreach ($genderList as $key => $value) {
                                            ?>
                                            <option value="<?php echo $key; ?>" <?php if (set_value('gender') == $key) echo "selected"; ?>><?php echo $value; ?></option>
                                            <?php
                                        }
                                        ?>
                                    </select>
                                </div>  
                            </div>
                            <div class="col-sm-2">
                                <div class="form-group">  
                                    <label><?php echo $this->lang->line('rte'); ?></label>
                                    <select  id="rte" name="rte" class="form-control" >
                                        <option value=""><?php echo $this->lang->line('select'); ?></option>
                                        <?php
                                        foreach ($RTEstatusList as $k => $rte) {
                                            ?>
                                            <option value="<?php echo $k; ?>" <?php if (set_value('rte') == $k) echo "selected"; ?>><?php echo $rte; ?></option>

                                            <?php
                                            $count++;
                                        }
                                        ?>
                                    </select>
                                </div>  
                            </div>
                            <div class="col-sm-12">
                                <div class="form-group">
                                    <button type="submit" name="search" value="search_filter" class="btn btn-primary pull-right btn-sm checkbox-toggle"><i class="fa fa-search"></i> <?php echo $this->lang->line('search'); ?></button>
                                </div>
                            </div>
                        </form>
                    </div>
                    <form method="post" action="<?php echo site_url('admin/feediscount/studentdiscount') ?>" id="assign_form">

                        <?php
                        if (isset($resultlist)) {
                            ?>
                            <div class="box-header ptbnull"></div>  
                            <div class="">
                                <div class="box-header with-border">
                                    <h3 class="box-title"><i class="fa fa-users"></i> <?php echo $this->lang->line('assign_fees_discount'); ?>
                                        </i> <?php echo form_error('student'); ?></h3>
                                    <div class="box-tools pull-right">
                                    </div>
                                </div>
                                <div class="box-body">
                                    <div class="row">
                                        <div class="">
                                            <div class="col-md-4">
                                                <div class="table-responsive">
                                                    <h4>
                                                        <?php echo $this->lang->line('fees_discount'); ?>
                                                    </h4>
                                                    <table class="table">
                                                        <thead>
                                                            <tr>
                                                    <th><?php echo $this->lang->line('discount_code'); ?></th>
                                        <th class="text-right"><?php   echo  ($feediscountList['type'] == "fix") ? $this->lang->line('amount') : $this->lang->line('percentage'); ?></th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <tr class="mailbox-name">
                                                        <input type="hidden" name="feediscount_id"value="<?php echo $feediscountList['id']; ?>">
                                                        <td>
                                                            <?php echo $feediscountList['code']; ?>
                                                        </td>
                                                        <td class="text-right">
                                                            <?php 
                                                           
                                                           echo  ($feediscountList['type'] == "fix") ? $currency_symbol . amountFormat($feediscountList['amount']) : $feediscountList['percentage']."%";
                                                            
                                                             ?>
                                                        </td>
                                                        </td>
                                                        </tr>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                            <div class="col-md-8">
                                                <div class=" table-responsive">
                                                    <table class="table table-striped">
                                                        <tbody>
                                                            <tr>
                                                                <th><input type="checkbox" id="select_all"/> <?php echo $this->lang->line('all'); ?></th>
                                                                <th><?php echo $this->lang->line('admission_no'); ?></th>
                                                                <th><?php echo $this->lang->line('student_name'); ?></th>
                                                                <th><?php echo $this->lang->line('class'); ?></th>
                                                                <th><?php echo $this->lang->line('father_name'); ?></th>
                                                                <th><?php echo $this->lang->line('category'); ?></th>
                                                                <th><?php echo $this->lang->line('gender'); ?></th>
                                                            </tr>
                                                            <?php
                                                            if (empty($resultlist)) {
                                                                ?>
                                                                <tr>
                                                                    <td colspan="6" class="text-danger text-center"><?php echo $this->lang->line('no_record_found'); ?></td>
                                                                </tr>
                                                                <?php
                                                            } else {
                                                                $count = 1;
                                                                foreach ($resultlist as $student) {
                                                                    ?>
                                                                <input type="hidden" name="student_list[]" value="<?php echo $student['student_session_id'] ?>">
                                                                <tr>
                                                                    <td> 
                                                                        <?php
                                                                        if ($student['student_fees_discount_id'] != 0) {
                                                                            $sel = "checked='checked'";
                                                                        } else {
                                                                            $sel = "";
                                                                        }
                                                                        ?>
                                                                        <input class="checkbox" type="checkbox" name="student_session_id[]"  value="<?php echo $student['student_session_id']; ?>" <?php echo $sel; ?>/>
                                                                    </td>
                                                                    <td><?php echo $student['admission_no']; ?></td>
                                                                    <td><?php echo $this->customlib->getFullName($student['firstname'],$student['middlename'],$student['lastname'],$sch_setting->middlename,$sch_setting->lastname); ?></td>
                                                                    <td><?php echo $student['class'] . "(" . $student['section'] . ")" ?></td>
                                                                    <td><?php echo $student['father_name']; ?></td>
                                                                    <td><?php echo $student['category']; ?></td>
                                                                    <td><?php echo $this->lang->line(strtolower($student['gender'])); ?></td>
                                                                </tr>
                                                                <?php
                                                            }
                                                            $count++;
                                                        }
                                                        ?>
                                                        </tbody>
                                                    </table>
                                                </div>
                                                <?php
                                                if (!empty($resultlist)) {
                                                    ?>
                                                    <button type="submit" class="allot-fees pull-right btn btn-primary btn-sm " id="load" data-loading-text="<i class='fa fa-spinner fa-spin '></i> <?php echo $this->lang->line('please_wait'); ?>"><?php echo $this->lang->line('save'); ?>
                                                    </button>
                                                <?php } ?>

                                                <br/>
                                                <br/>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php
                        }
                        ?>
                    </form>
                </div>  
            </div>
        </div>
    </section>
</div>

<script type="text/javascript">
// Show success message if present in URL after page reload
$(document).ready(function() {
    try {
        var urlParams = new URLSearchParams(window.location.search);
        var successMessage = urlParams.get('success');
        if (successMessage) {
            successMessage = decodeURIComponent(successMessage);
            successMsg(successMessage);
            // Clean URL by removing the success parameter
            var newUrl = window.location.href.split('?')[0];
            window.history.replaceState({}, document.title, newUrl);
        }
    } catch(e) {
        // Fallback for browsers that don't support URLSearchParams
        var url = window.location.href;
        var match = url.match(/[?&]success=([^&]*)/);
        if (match) {
            var successMessage = decodeURIComponent(match[1]);
            successMsg(successMessage);
            // Clean URL
            var newUrl = url.replace(/[?&]success=[^&]*/, '').replace(/[?&]$/, '');
            window.history.replaceState({}, document.title, newUrl);
        }
    }
});

//select all checkboxes
    $("#select_all").change(function () {  //"select all" change 
        $(".checkbox").prop('checked', $(this).prop("checked")); //change all ".checkbox" checked status
    });

//".checkbox" change 
    $('.checkbox').change(function () {
        //uncheck "select all", if one of the listed checkbox item is unchecked
        if (false == $(this).prop("checked")) { //if this item is unchecked
            $("#select_all").prop('checked', false); //change "select all" checked status to false
        }
        //check "select all" if all checkbox items are checked
        if ($('.checkbox:checked').length == $('.checkbox').length) {
            $("#select_all").prop('checked', true);
        }
    });

    $(document).ready(function () {
    // Fetch all sections
    $.ajax({
        url: '<?php echo base_url(); ?>sections/getAll',
        method: 'GET',
        dataType: 'json',
        success: function (response) {
            var section_id = '<?php echo set_value("section_id") ?>';
            var html = '<option value=""><?php echo $this->lang->line("select"); ?></option>';
            if(response && response.length > 0) {
            $.each(response, function (index, section) {
                var selected = (section_id == section.id) ? 'selected' : '';
                html += '<option value="' + section.id + '" ' + selected + '>' + section.section + '</option>';
            });
            }
            $('#section_id').html(html);
            // If there's a pre-selected section, trigger program load
            if(section_id) {
                $('#section_id').trigger('change');
            }
        },
        error: function(xhr, status, error) {
            console.error('Error loading sections:', error);
            $('#section_id').html('<option value="">Error loading sections</option>');
        }
    });

    // When section changes, fetch programs
    $(document).on('change', '#section_id', function (e) {
        var section_id = $(this).val();
        // Clear program and class dropdowns
        $('#program_id').html('<option value=""><?php echo $this->lang->line("select"); ?></option>');
        $('#class_id').html('<option value=""><?php echo $this->lang->line("select"); ?></option>');
        
        // Ensure class dropdown is visible
        $('#class_id').show();
        
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
                    $('#program_id').html('<option value="">Error loading programs</option>');
                }
            });
        }
    });

    // When program changes, fetch classes
    $(document).on('change', '#program_id', function (e) {
        var section_id = $('#section_id').val();
        var program_id = $(this).val();
        
        // Ensure class dropdown is visible and clear it
        $('#class_id').show().html('<option value="">Loading...</option>');
        
        if (program_id != '') {
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
                    var class_id = '<?php echo set_value("class_id") ?>';
                    var html = '<option value=""><?php echo $this->lang->line("select"); ?></option>';
                    if (response && response.length > 0) {
                        $.each(response, function(index, classItem) {
                            var selected = (class_id == classItem.id) ? 'selected' : '';
                            html += '<option value="' + classItem.id + '" ' + selected + '>' + (classItem.degree ? classItem.degree + ' - ' : '') + classItem.class + '</option>';
                        });
                    } else {
                        html += '<option value="" disabled>No classes found for this combination</option>';
                    }
                    $('#class_id').html(html).show(); // Ensure it's visible after populating
                },
                error: function(xhr, status, error) {
                    $('#class_id').html('<option value="">Error loading classes</option>').show();
                }
            });
        } else {
            // If no program selected, reset class dropdown but keep it visible
            $('#class_id').html('<option value=""><?php echo $this->lang->line("select"); ?></option>').show();
        }
    });
});
</script>

<script type="text/javascript">
    $("#assign_form").submit(function (e) {
        if (confirm('<?php echo $this->lang->line('are_you_sure_save_results'); ?>')) {
            var $this = $('.allot-fees');
            $this.button('loading');
            $.ajax({
                type: "POST",
                dataType: 'Json',
                url: $("#assign_form").attr('action'),
                data: $("#assign_form").serialize(),
                success: function (data)
                {
                    if (data.status == "fail") {
                        var message = "";
                        $.each(data.error, function (index, value) {

                            message += value;
                        });
                        errorMsg(message);
                        $this.button('reset');
                    } else {
                        // Reload page immediately, then show success message
                        var successMessage = encodeURIComponent(data.message);
                        window.location.href = window.location.href.split('?')[0] + '?success=' + successMessage;
                    }
                },
                error: function (xhr) {
                    errorMsg('<?php echo $this->lang->line("error_occurred_please_try_again"); ?>');
                    $this.button('reset');
                }
            });
        }
        e.preventDefault();
    });
</script>