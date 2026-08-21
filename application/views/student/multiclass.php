<?php
$currency_symbol = $this->customlib->getSchoolCurrencyFormat();
?>
<div class="content-wrapper">
    <!-- Main content -->
    <section class="content">
        <div class="row">
            <!-- left column -->
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-search"></i> <?php echo $this->lang->line('select_criteria'); ?></h3>
                    </div>
                    <form action="<?php echo site_url('student/multiclass') ?>"  method="post" accept-charset="utf-8">
                        <div class="box-body">
                            <?php echo $this->customlib->getCSRF(); ?>
                            <div class="row">
                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <label><?php echo $this->lang->line('section'); ?></label><small class="req"> *</small>
                                        <select id="section_id" name="section_id" class="form-control">
                                            <option value=""><?php echo $this->lang->line('select'); ?></option>
                                        </select>
                                        <span class="text-danger"><?php echo form_error('section_id'); ?></span>
                                    </div>
                                </div>
                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <label for="exampleInputEmail1"><?php echo $this->lang->line('program'); ?></label><small class="req"> *</small>
                                        <select id="program_id" name="program_id" class="form-control">
                                            <option value=""><?php echo $this->lang->line('select'); ?></option>
                                        </select>
                                        <span class="text-danger"><?php echo form_error('program_id'); ?></span>
                                    </div>
                                </div>
                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <label><?php echo $this->lang->line('class'); ?></label> <small class="req"> *</small>
                                        <select id="class_id" name="class_id" class="form-control">
                                            <option value=""><?php echo $this->lang->line('select'); ?></option>
                                        </select>
                                        <span class="text-danger" id="error_class_id"></span>
                                    </div>
                                </div>
                                <div class="col-sm-12">
                                    <button type="submit" class="btn btn-primary btn-sm pull-right"><i class="fa fa-search"></i> <?php echo $this->lang->line('search'); ?></button>
                                </div>
                            </div>
                        </div>
                    </form>

                    <div class="ptt10">
                        <div class="bordertop">
                            <?php  
                            if (!empty($students)) {
                            ?>
                                <div class="box-header with-border">
                                    <h3 class="box-title"><i class="fa fa-users"></i> <?php echo $this->lang->line('student_list'); ?></h3>
                                </div>
                                <div class="box-body">
                                    <div class="row">
                                        <?php
                                        foreach ($students as $student_key => $student_value) {
                                        ?>
                                            <form action="<?php echo site_url('student/savemulticlass'); ?>" method="POST" class="update">
                                                <?php echo $this->customlib->getCSRF(); ?>
                                                <div class="col-md-6">
                                                    <div class="panel panel-info">
                                                        <div class="panel-body panelheight">
                                                            <?php
                                                            echo $this->customlib->getFullName($student_value['firstname'], $student_value['middlename'], $student_value['lastname'], $sch_setting->middlename, $sch_setting->lastname) . " (" . $student_value['admission_no'] . ")";
                                                            ?>
                                                            <input type="hidden" value="<?php echo $student_value['id'] ?>" name="student_id">
                                                            <input type="hidden" value="<?php echo count($student_value['student_sessions']) + 1; ?>" name="nxt_row" class="nxt_row">
                                                            <div class="row">
                                                                <div class="text-center">
                                                                    <div class="col-xs-12 col-xs-offset-0 col-sm-3 col-sm-offset-9">
                                                                        <?php if ($this->rbac->hasPrivilege('multi_class_student', 'can_add')) {?>
                                                                        <button type="button" class="btn btn-default btn-sm pull-right addrow addrow-mb2010">
                                                                            <i class="fa fa-plus"></i>
                                                                        </button>
                                                                        <?php }?>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="append_row pluscolmn">
                                                                <?php
                                                                // Convert object to array if needed
                                                                $student_sessions = is_array($student_value['student_sessions']) ? $student_value['student_sessions'] : (array)$student_value['student_sessions'];

                                                                if (!empty($student_sessions)) {
                                                                    $count = 1;
                                                                    foreach ($student_sessions as $student_session_key => $student_session_value) {
                                                                        // Convert session value to array if it's an object
                                                                        $session_data = is_array($student_session_value) ? $student_session_value : (array)$student_session_value;
                                                                        ?>
                                                                        <div class="row">
                                                                            <input type="hidden" name="row_count[]" value="<?php echo $count; ?>">
                                                                            <input type="hidden" name="student_session_id_<?php echo $count; ?>" value="<?php echo isset($session_data['id']) ? $session_data['id'] : ''; ?>">
                                                                           
                                                                            <div class="col-sm-5 col-lg-5 col-md-4">
                                                                                <div class="form-group">
                                                                                    <label><?php echo $this->lang->line('section'); ?></label>
                                                                                    <p class="form-control-static"><?php echo isset($session_data['section']) ? $session_data['section'] : 'N/A'; ?></p>
                                                                                    <input type="hidden" name="section_id_<?php echo $count; ?>" value="<?php echo isset($session_data['section_id']) ? $session_data['section_id'] : ''; ?>">
                                                                                </div>
                                                                            </div>
                                                                           
                                                                            <div class="col-sm-5 col-lg-5 col-md-4">
                                                                                <div class="form-group">
                                                                                    <label><?php echo $this->lang->line('program'); ?></label>
                                                                                    <p class="form-control-static"><?php echo isset($session_data['program_name']) ? $session_data['program_name'] : 'N/A'; ?></p>
                                                                                    <input type="hidden" name="program_id_<?php echo $count; ?>" value="<?php echo isset($session_data['program_id']) ? $session_data['program_id'] : ''; ?>">
                                                                                </div>
                                                                            </div>

                                                                            <div class="col-sm-5 col-lg-5 col-md-4">
                                                                                <div class="form-group">
                                                                                    <label><?php echo $this->lang->line('class'); ?></label><small class="req"> *</small>
                                                                                    <select name="class_id_<?php echo $count; ?>" class="form-control class-dropdown" data-row="<?php echo $count; ?>" data-selected="<?php echo isset($session_data['class_id']) ? $session_data['class_id'] : ''; ?>">
                                                                                        <option value=""><?php echo $this->lang->line('select'); ?></option>
                                                                                    </select>
                                                                                    <span class="text-danger"></span>
                                                                                </div>
                                                                            </div>
                                                                           
                                                                            <div class="col-sm-2 col-lg-2 col-md-4">
                                                                                <div class="form-group">
                                                                                    <label for="email" style="opacity: 0;"><?php echo $this->lang->line('action') ?></label>
                                                                                    <?php if ($this->rbac->hasPrivilege('multi_class_student', 'can_delete')) {?>
                                                                                        <button class="btn btn-sm btn-danger rmv_row" type="button"><?php echo $this->lang->line('remove') ?></button>
                                                                                    <?php }?>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        <?php
                                                                        $count++;
                                                                    }
                                                                }
                                                                ?>
                                                            </div>
                                                        </div>
                                                        <div class="panel-footer panel-fo">
                                                            <div class="row text-center">
                                                                <div class="col-xs-12 col-xs-offset-0 col-sm-3 col-sm-offset-9">
                                                                    <?php if ($this->rbac->hasPrivilege('multi_class_student', 'can_edit')) {?>
                                                                    <button type="submit" class="btn btn-default btn-sm pull-right" data-loading-text="<i class='fa fa-spinner fa-spin '></i> <?php echo $this->lang->line('update'); ?>">
                                                                        <?php echo $this->lang->line('update'); ?>
                                                                    </button>
                                                                    <?php }?>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </form>
                                            <?php
                                        }
                                        ?>
                                    </div>
                                </div>
                            <?php
                            } else { ?>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="col-md-12">
                                            <div class="alert alert-danger"><?php echo $this->lang->line('no_record_found'); ?></div>
                                        </div>
                                    </div>
                                </div>
                            <?php
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- /.row -->
    </section>
    <!-- /.content -->
    <div class="clearfix"></div>
</div>

<script type="text/javascript">
    // Store the selected values from POST data
    var selectedValues = {
        section_id: '<?php echo $this->input->post("section_id") ? $this->input->post("section_id") : ""; ?>',
        program_id: '<?php echo $this->input->post("program_id") ? $this->input->post("program_id") : ""; ?>',
        class_id: '<?php echo $this->input->post("class_id") ? $this->input->post("class_id") : ""; ?>'
    };

    // Form submission handler
    $(document).on('submit', '.update', function (e) {
        var submit_btn = $(this).find("button[type=submit]");
        e.preventDefault();

        var form = $(this);
        var url = form.attr('action');

        $.ajax({
            type: "POST",
            url: url,
            data: form.serialize(),
            dataType: "json",
            beforeSend: function () {
                submit_btn.button('loading');
            },
            success: function (data) {
                if (data.status == 1) {
                    successMsg(data.message);
                } else {
                    errorMsg(data.message);
                }
                submit_btn.button('reset');
            },
            error: function (xhr) {
                alert("<?php echo $this->lang->line('error_occurred_please_try_again'); ?>");
            },
            complete: function () {
                submit_btn.button('reset');
            }
        });
    });

    // Function to load classes for dynamic rows
    function loadClassesForDynamicRow(rowContainer, selected_class_id = null) {
        var section_id = rowContainer.find('input[name*="section_id"]').val();
        var program_id = rowContainer.find('input[name*="program_id"]').val();
        var classSelect = rowContainer.find('select[name*="class_id"]');

        if (section_id && program_id) {
            classSelect.html('<option value="">Loading Academic Levels...</option>');
            $.ajax({
                url: '<?php echo base_url(); ?>classes/getBySectionAndProgram',
                method: 'POST',
                data: {
                    section_id: section_id,
                    program_id: program_id,
                    '<?php echo $this->security->get_csrf_token_name(); ?>': '<?php echo $this->security->get_csrf_hash(); ?>'
                },
                dataType: 'json',
                success: function(response) {
                    var html = '<option value=""><?php echo $this->lang->line("select"); ?></option>';
                    if (response && response.length > 0) {
                        $.each(response, function(index, classItem) {
                            var sel = (selected_class_id == classItem.id) ? 'selected' : '';
                            html += '<option value="' + classItem.id + '" ' + sel + '>' +
                                   (classItem.degree ? classItem.degree + ' - ' : '') +
                                   classItem.class + '</option>';
                        });
                    } else {
                        html += '<option value="" disabled>No academic levels found for this combination</option>';
                    }
                    classSelect.html(html);
                },
                error: function() {
                    classSelect.html('<option value="">Error loading academic levels</option>');
                }
            });
        } else {
            classSelect.html('<option value=""><?php echo $this->lang->line("select"); ?></option>');
        }
    }

    // Function to load sections
    function loadSections() {
        $.ajax({
            url: '<?php echo base_url(); ?>sections/getAll',
            method: 'GET',
            dataType: 'json',
            success: function (response) {
                var html = '<option value=""><?php echo $this->lang->line("select"); ?></option>';
                
                $.each(response, function (index, section) {
                    var selected = (selectedValues.section_id == section.id) ? 'selected' : '';
                    html += '<option value="' + section.id + '" ' + selected + '>' + section.section + '</option>';
                });
                
                $('#section_id').html(html);
                
                // If section was selected, trigger program loading
                if (selectedValues.section_id) {
                    loadPrograms(selectedValues.section_id);
                }
            },
            error: function() {
                console.log('Error loading sections');
            }
        });
    }

    // Function to load programs
    function loadPrograms(section_id) {
        if (section_id) {
            $('#program_id').html('<option value="">Loading...</option>');
            
            $.ajax({
                url: '<?php echo base_url(); ?>programs/getBySection',
                method: 'POST',
                data: {
                    section_id: section_id,
                    '<?php echo $this->security->get_csrf_token_name(); ?>': '<?php echo $this->security->get_csrf_hash(); ?>'
                },
                dataType: 'json',
                success: function (response) {
                    var html = '<option value=""><?php echo $this->lang->line("select"); ?></option>';
                    
                    if(response && response.length > 0) {
                        $.each(response, function (index, program) {
                            var selected = (selectedValues.program_id == program.id) ? 'selected' : '';
                            html += '<option value="' + program.id + '" ' + selected + '>' + program.title + '</option>';
                        });
                    } else {
                        html += '<option value="" disabled>No programs found</option>';
                    }
                    
                    $('#program_id').html(html);
                    
                    // If program was selected, trigger class loading
                    if (selectedValues.program_id) {
                        loadClasses(section_id, selectedValues.program_id);
                    }
                },
                error: function() {
                    $('#program_id').html('<option value="">Error loading programs</option>');
                }
            });
        } else {
            $('#program_id').html('<option value=""><?php echo $this->lang->line("select"); ?></option>');
            $('#class_id').html('<option value=""><?php echo $this->lang->line("select"); ?></option>');
        }
    }

    // Function to load classes
    function loadClasses(section_id, program_id) {
        if (section_id && program_id) {
            $('#class_id').html('<option value="">Loading...</option>');
            
            $.ajax({
                url: '<?php echo base_url(); ?>classes/getBySectionAndProgram',
                method: 'POST',
                data: {
                    section_id: section_id,
                    program_id: program_id,
                    '<?php echo $this->security->get_csrf_token_name(); ?>': '<?php echo $this->security->get_csrf_hash(); ?>'
                },
                dataType: 'json',
                success: function(response) {
                    var html = '<option value=""><?php echo $this->lang->line("select"); ?></option>';
                    
                    if (response && response.length > 0) {
                        $.each(response, function(index, classItem) {
                            var selected = (selectedValues.class_id == classItem.id) ? 'selected' : '';
                            html += '<option value="' + classItem.id + '" ' + selected + '>' +
                                   (classItem.degree ? classItem.degree + ' - ' : '') +
                                   classItem.class + '</option>';
                        });
                    } else {
                        html += '<option value="" disabled>No classes found for this combination</option>';
                    }
                    
                    $('#class_id').html(html);
                },
                error: function() {
                    $('#class_id').html('<option value="">Error loading classes</option>');
                }
            });
        } else {
            $('#class_id').html('<option value=""><?php echo $this->lang->line("select"); ?></option>');
        }
    }

    // Document ready
    $(document).ready(function () {
        // Load sections on page load
        loadSections();
        
        // Initialize all existing dynamic rows' class dropdowns
        $('.append_row .row').each(function() {
            var selected_class_id = $(this).find('select[name*="class_id"]').data('selected');
            loadClassesForDynamicRow($(this), selected_class_id);
        });
    });

    // Section change handler
    $(document).on('change', '#section_id', function (e) {
        var section_id = $(this).val();
        selectedValues.program_id = '';
        selectedValues.class_id = '';
        loadPrograms(section_id);
    });

    // Program change handler
    $(document).on('change', '#program_id', function (e) {
        var section_id = $('#section_id').val();
        var program_id = $(this).val();
        selectedValues.class_id = '';
        loadClasses(section_id, program_id);
    });

    // Remove row handler
    $(document).on('click', '.rmv_row', function (e) {
        $(this).closest("div.row").remove();
    });

    // Add row handler
    $(document).on('click', '.addrow', function () {
        var container = $(this).closest(".panel-body").find('.append_row');
        var nxt_row = $(this).closest(".panel-body").find('.nxt_row').val();
        
        // Get values from the main search criteria dropdowns
        var main_section_id = $('#section_id').val();
        var main_section_text = $('#section_id option:selected').text();
        var main_program_id = $('#program_id').val();
        var main_program_text = $('#program_id option:selected').text();

        if (!main_section_id || !main_program_id) {
            alert('Please select Section and Program first');
            return;
        }

        var $newDiv = $('<div>').addClass('row').append(
            $('<input>', {type: 'hidden', name: 'row_count[]', val: parseInt(nxt_row)})
        ).append(
            $('<div>').addClass('col-sm-5 col-lg-5 col-md-4').append(
                $('<div>').addClass('form-group').append(
                    $('<label>').html('<?php echo $this->lang->line("section"); ?>')
                ).append(
                    $('<p>').addClass('form-control-static').text(main_section_text)
                ).append(
                    $('<input>', {type: 'hidden', name: 'section_id_' + nxt_row, val: main_section_id})
                )
            )
        ).append(
            $('<div>').addClass('col-sm-5 col-lg-5 col-md-4').append(
                $('<div>').addClass('form-group').append(
                    $('<label>').html('<?php echo $this->lang->line("program"); ?>')
                ).append(
                    $('<p>').addClass('form-control-static').text(main_program_text)
                ).append(
                    $('<input>', {type: 'hidden', name: 'program_id_' + nxt_row, val: main_program_id})
                )
            )
        ).append(
            $('<div>').addClass('col-sm-5 col-lg-5 col-md-4').append(
                $('<div>').addClass('form-group').append(
                    $('<label>').html('<?php echo $this->lang->line("class"); ?><small class="req"> *</small>')
                ).append(
                    $('<select>').attr('name', 'class_id_' + nxt_row).addClass('form-control').html('<option value=""><?php echo $this->lang->line("select"); ?></option>')
                ).append(
                    $('<span>').addClass('text-danger')
                )
            )
        ).append(
            $('<div>').addClass('col-sm-2 col-lg-2 col-md-4').append(
                $('<div>').addClass('form-group').append(
                    $('<label>', {css: {'opacity': 0}}).html('<?php echo $this->lang->line("action"); ?>')
                ).append(
                    <?php if ($this->rbac->hasPrivilege('multi_class_student', 'can_delete')) {?>
                    $('<button>').html('<?php echo $this->lang->line("remove"); ?>').addClass('btn btn-sm btn-danger rmv_row').attr('type', 'button')
                    <?php } ?>
                )
            )
        );

        $(this).closest(".panel-body").find('.nxt_row').val(parseInt(nxt_row) + 1);
        $newDiv.appendTo(container);
        
        // Initialize the new row's class dropdown
        loadClassesForDynamicRow($newDiv);
    });
</script>