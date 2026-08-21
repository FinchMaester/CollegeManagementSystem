<link rel="stylesheet" href="<?php echo base_url(); ?>backend/plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.min.css">
<script src="<?php echo base_url(); ?>backend/plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.all.min.js"></script>
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            <i class="fa fa-flask"></i> <?php //echo $this->lang->line('approve_leave'); ?>
        </h1>
    </section>
    <section class="content">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-search"></i> <?php echo $this->lang->line('select_criteria'); ?></h3>

            </div>
            <form  class="assign_teacher_form" action="<?php echo base_url(); ?>admin/approve_leave" method="post" enctype="multipart/form-data">
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-12">
                            <?php if ($this->session->flashdata('msg')) { ?>
                                <?php 
                                    echo $this->session->flashdata('msg');
                                    $this->session->unset_userdata('msg');
                                ?>
                            <?php } ?>
                            <?php echo $this->customlib->getCSRF(); ?>
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
                    <button type="submit" id="search_filter" name="search" value="search_filter" class="btn btn-primary btn-sm checkbox-toggle pull-right"><i class="fa fa-search"></i> <?php echo $this->lang->line('search'); ?></button>
                </div>
            </form>
            <div class="row">
                <div class="col-md-12">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-users"></i> <?php echo $this->lang->line('approve_leave_list'); ?></h3>
                        <div class="box-tools pull-right">
                            <?php if ($this->rbac->hasPrivilege('approve_leave', 'can_add')) { ?>
                                <button type="button" onclick="add_leave()" class="btn btn-sm btn-primary " data-toggle="tooltip"><i class="fa fa-plus"></i> <?php echo $this->lang->line('add'); ?></button>
                            <?php } ?>
                        </div>
                    </div> 
                    <div class="box-body table-responsive overflow-visible">
                        <div class="download_label"> <?php echo $this->lang->line('approve_leave_list'); ?> </div>
                        <div >
                            <table class="table table-hover table-striped table-bordered example">
                                <thead>
                                    <tr>
                                        <th><?php echo $this->lang->line('student_name') ?></th>
                                        <th><?php echo $this->lang->line('class'); ?></th>
                                        <th><?php echo $this->lang->line('section'); ?></th>
                                        <th><?php echo $this->lang->line('program'); ?></th>
                                        <th><?php echo $this->lang->line('apply_date'); ?></th>
                                        <th><?php echo $this->lang->line('from_date'); ?></th>
                                        <th><?php echo $this->lang->line('to_date'); ?></th>
                                        <th><?php echo $this->lang->line('status'); ?></th>
                                        <th><?php echo $this->lang->line('approve_disapprove_by'); ?></th>
                                        <th class="text-right noExport"><?php echo $this->lang->line('action'); ?></th>
                                    </tr> 
                                </thead>
                                <tbody>
                                    <?php
                                    foreach ($results as $value) {

                                        ?>
                                        <tr>
                                            <td><?php 

                                            echo $this->customlib->getFullName($value['firstname'],$value['middlename'],$value['lastname'],$sch_setting->middlename,$sch_setting->lastname)." (".$value['admission_no'].")"; ?></td>
                                            <td><?php echo $value['class']; ?></td>
                                            <td><?php echo $value['section']; ?></td>
                                            <td><?php 
                    
                                                echo isset($value['program_title']) ? $value['program_title'] : ''; 
                                            ?></td>
                                            <td><?php echo $value['apply_date']; ?></td>
                                            <td><?php echo $value['from_date']; ?></td>
                                            <td><?php echo $value['to_date']; ?></td>
                                            <td><?php 
                                            
                                            if($value['approve_date']){
                                                $approve_date =  '('.date($this->customlib->getSchoolDateFormat(), strtotime($value['approve_date'])).')';
                                            }else{
                                                $approve_date = '';
                                            }
                                            
                                            if($value['status']==1){
                                                echo $this->lang->line('approved').' '.$approve_date ;
                                            }elseif($value['status']==2){
                                                echo $this->lang->line('disapproved');
                                            }else{
                                                echo $this->lang->line('pending');
                                            }?></td>                                           
                                            <td><?php   echo $value['staff_name'] . " " . $value['surname'] ;
                                             if($value['staff_id']!=""){ echo " (".$value['staff_id'].")" ; }  ?></td>
                                            <td class="text-right white-space-nowrap">
                                               
                                                <?php
                                                if ($value['docs'] != '') {
                                                    ?>
                                                    <a href="<?php echo base_url(); ?>admin/approve_leave/download/<?php echo $value['id'] ?>" class="btn btn-default btn-xs" data-toggle="tooltip" title="" data-original-title="<?php echo $this->lang->line('download'); ?>">
                                                        <i class="fa fa-download"></i>
                                                    </a>
                                                    <?php
                                                }
                                                ?>
                                                
                                                <?php if ($this->rbac->hasPrivilege('approve_leave', 'can_edit')) { ?>
                                                
                                                <a onclick="get('<?php echo $value['id']; ?>', '<?php echo $value['class_id']; ?>', '<?php echo $value['section_id']; ?>', '<?php echo $value['status']; ?>')" class="btn btn-default btn-xs" data-toggle="tooltip" data-original-title="<?php echo $this->lang->line('edit') ?>"><i class="fa fa-pencil"></i> </a>
                                                
                                                <?php } if ($this->rbac->hasPrivilege('approve_leave', 'can_delete')) { ?>
                                                
                                                <a onclick="delete_leave('<?php echo $value['id']; ?>', '<?php echo $value['class_id']; ?>', '<?php echo $value['section_id']; ?>');"  data-toggle="tooltip" data-original-title="<?php echo $this->lang->line('delete') ?>" class="btn btn-default btn-xs"><i class="fa fa-trash" ></i> </a>
                                                
                                                <?php } ?>
                                            </td>
                                        </tr>
                                        <?php
                                    }
                                    ?>

                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<div class="modal fade" id="homework_docs" tabindex="-1" role="dialog" aria-labelledby="evaluation" style="padding-left: 0 !important">
    <div class="modal-dialog " role="document">
        <div class="modal-content modal-media-content">
            <div class="modal-header modal-media-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="box-title" id="title"></h4>
            </div>
            <form role="form" id="addleave_form" method="post" enctype="multipart/form-data" action="">
                <div class="modal-body pb0">
                    <div class="row">
                        <div class="col-lg-12 col-md-12 col-sm-12">
                            <div class="row">                            
                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label><?php echo $this->lang->line('section'); ?></label><small class="req"> *</small>
                                        <select id="modal_section_id" name="section_id" class="form-control">
                                            <option value=""><?php echo $this->lang->line('select'); ?></option>
                                        </select>
                                        <span class="text-danger"><?php echo form_error('section_id'); ?></span>
                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label for="exampleInputEmail1"><?php echo $this->lang->line('program'); ?></label><small class="req"> *</small>
                                        <select id="modal_program_id" name="program_id" class="form-control">
                                            <option value=""><?php echo $this->lang->line('select'); ?></option>
                                        </select>
                                        <span class="text-danger"><?php echo form_error('program_id'); ?></span>
                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label><?php echo $this->lang->line('class'); ?></label> <small class="req"> *</small>
                                        <select id="modal_class_id" name="class_id" class="form-control">
                                            <option value=""><?php echo $this->lang->line('select'); ?></option>
                                        </select>
                                        <span class="text-danger" id="error_class_id"></span>
                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label for="pwd"><?php echo $this->lang->line('student'); ?></label><small class="req"> *</small>
                                        <select type="text" name="student" id="modal_student" class="form-control ">
                                            <option value=""><?php echo $this->lang->line('select'); ?></option>
                                            <?php
                                            if (!empty($resultlist)) {
                                                foreach ($resultlist as $student) {
                                                    $fullname = $this->customlib->getFullName($student['firstname'], $student['middlename'], $student['lastname'], $sch_setting->middlename, $sch_setting->lastname);
                                                    ?>
                                                    <option value="<?php echo $student['id'] ?>"><?php echo $fullname . " (" . $student['admission_no'] . ")"; ?></option>
                                                    <?php
                                                }
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                    
                                <div class="col-sm-4">
                                    <label><?php echo $this->lang->line('apply_date'); ?></label><small class="req"> *</small>
                                    <input type="text" id="apply_date" name="apply_date" value="" class="form-control nepali-datepicker" readonly="readonly">
                                </div>
                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label for="pwd"><?php echo $this->lang->line('from_date'); ?></label><small class="req"> *</small>
                                        <input type="text" name="from_date" id="from_date" class="form-control nepali-datepicker" readonly="readonly">
                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label for="pwd"><?php echo $this->lang->line('to_date'); ?></label><small class="req"> *</small>
                                        <input type="text" name="to_date" id="to_date" class="form-control nepali-datepicker" readonly="readonly">
                                    </div>
                                </div>
                                <div class="col-sm-12">
                                    <div class="form-group">
                                        <label for="pwd"><?php echo $this->lang->line('reason'); ?></label>
                                        <input type="hidden" name="leave_id" id="leave_id">
                                        <textarea type="text" id="message" name="message" class="form-control "></textarea>
                                    </div>
                                </div> 
                                <div class="col-sm-12">
                                    <div class="form-group">
                                        <label for="pwd"><?php echo $this->lang->line('leave_status'); ?></label><small class="req"> *</small>
                                    <br>
                                        <label class="radio-inline">
                                            <input type="radio" name="leave_status" value="0" id="leave_pending"  ><?php echo $this->lang->line('pending'); ?></label>
                                        <label class="radio-inline">
                                            <input value="2" type="radio" id="leave_disapprove" name="leave_status" ><?php echo $this->lang->line('disapprove'); ?>                                               </label> 
                                             <label class="radio-inline">
                                            <input value="1" type="radio" id="leave_approve" name="leave_status" ><?php echo $this->lang->line('approve'); ?></label>
                                        </div>
                                </div>
                                <div class="col-sm-12">
                                    <div class="form-group">
                                        <label for="pwd"><?php echo $this->lang->line('attach_document'); ?></label>
                                        <input type="file" id="file" name="userfile" class="filestyle form-control" autocomplete="off">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="box-footer">
                    <div class="pull-right paddA10">
                        <button type="submit" class="btn btn-info pull-right" data-loading-text="<i class='fa fa-spinner fa-spin '></i> Please wait" value=""><?php echo $this->lang->line('save'); ?></button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script type="text/javascript">
    // Initialize modal on close
    $('#homework_docs').on('hidden.bs.modal', function() {
        $(this).find("input,textarea,select")
            .val('')
            .end()
            .find("input[type=checkbox], input[type=radio]")
            .prop("checked", "")
            .end();
        $('#modal_section_id').find('option').not(':first').remove();
        $('#modal_program_id').find('option').not(':first').remove();
        $('#modal_class_id').find('option').not(':first').remove();
        $('#modal_student').find('option').not(':first').remove();
        
    });

    $(document).ready(function() {
        // Function to load sections for main search
        function loadSections() {
            $.ajax({
                url: '<?php echo base_url(); ?>sections/getAll',
                method: 'GET',
                dataType: 'json',
                success: function(response) {
                    var html = '<option value=""><?php echo $this->lang->line("select"); ?></option>';
                    if (response && response.length > 0) {
                        $.each(response, function(index, section) {
                            html += '<option value="' + section.id + '"' + 
                                   (section.id == '<?php echo $section_id; ?>' ? ' selected' : '') + 
                                   '>' + section.section + '</option>';
                        });
                    }
                    $('#section_id').html(html);
                    
                    // If section is pre-selected, trigger change to load programs
                    if ('<?php echo $section_id; ?>' != '') {
                        $('#section_id').trigger('change');
                    }
                },
                error: function(xhr, status, error) {
                    console.log('Error loading sections: ' + error);
                }
            });
        }

        // Function to load sections for modal
        function loadModalSections() {
            $.ajax({
                url: '<?php echo base_url(); ?>sections/getAll',
                method: 'GET',
                dataType: 'json',
                success: function(response) {
                    var html = '<option value=""><?php echo $this->lang->line("select"); ?></option>';
                    if (response && response.length > 0) {
                        $.each(response, function(index, section) {
                            html += '<option value="' + section.id + '">' + section.section + '</option>';
                        });
                    }
                    $('#modal_section_id').html(html);
                },
                error: function(xhr, status, error) {
                    console.log('Error loading modal sections: ' + error);
                }
            });
        }

        // Initial load of sections for main form
        loadSections();

        // Load sections when modal opens
        $('#homework_docs').on('shown.bs.modal', function() {
            if ($('#leave_id').val() == '') {
                loadModalSections();
            }
        });
    });

    // MAIN SEARCH FORM DROPDOWNS
    // When main section changes, fetch programs
    $(document).on('change', '#section_id', function(e) {
        var section_id = $(this).val();
        
        // Clear dependent dropdowns
        $('#program_id').html('<option value=""><?php echo $this->lang->line("select"); ?></option>');
        $('#class_id').html('<option value=""><?php echo $this->lang->line("select"); ?></option>');
        
        if (section_id != '') {
            $.ajax({
                url: '<?php echo base_url(); ?>programs/getBySection', 
                method: 'POST',
                data: {
                    section_id: section_id,
                    <?php echo $this->security->get_csrf_token_name(); ?>: '<?php echo $this->security->get_csrf_hash(); ?>'
                },
                dataType: 'json',
                success: function(response) {
                    var html = '<option value=""><?php echo $this->lang->line("select"); ?></option>';
                    if(response && response.length > 0) {
                        $.each(response, function(index, program) {
                            html += '<option value="' + program.id + '"' + 
                                   (program.id == '<?php echo $program_id; ?>' ? ' selected' : '') + 
                                   '>' + program.title + '</option>';
                        });
                    }
                    $('#program_id').html(html);
                    
                    // If program is pre-selected, trigger change to load classes
                    if ('<?php echo $program_id; ?>' != '') {
                        $('#program_id').trigger('change');
                    }
                },
                error: function(xhr, status, error) {
                    console.log('Error loading programs: ' + error);
                }
            });
        }
    });

    // When main program changes, fetch classes
    $(document).on('change', '#program_id', function(e) {
        var section_id = $('#section_id').val();
        var program_id = $(this).val();
        
        // Clear dependent dropdown
        $('#class_id').html('<option value=""><?php echo $this->lang->line("select"); ?></option>');
        
        if (section_id && program_id) {
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
                    var html = '<option value=""><?php echo $this->lang->line("select"); ?></option>';
                    if (response && response.length > 0) {
                        $.each(response, function(index, classItem) {
                            html += '<option value="' + classItem.id + '"' + 
                                   (classItem.id == '<?php echo $class_id; ?>' ? ' selected' : '') + 
                                   '>' + (classItem.degree ? classItem.degree + ' - ' : '') + 
                                   classItem.class + '</option>';
                        });
                    }
                    $('#class_id').html(html);
                },
                error: function(xhr, status, error) {
                    console.log('Error loading classes: ' + error);
                }
            });
        }
    });

    // MODAL DROPDOWNS - Using your existing URLs but correct modal IDs
    // When modal section changes, fetch programs
    $(document).on('change', '#modal_section_id', function(e) {
        var section_id = $(this).val();
        
        // Clear dependent dropdowns
        $('#modal_program_id').html('<option value=""><?php echo $this->lang->line("select"); ?></option>');
        $('#modal_class_id').html('<option value=""><?php echo $this->lang->line("select"); ?></option>');
        $('#modal_student').html('<option value=""><?php echo $this->lang->line("select"); ?></option>');
        
        if (section_id != '') {
            $.ajax({
                url: '<?php echo base_url(); ?>programs/getBySection', 
                method: 'POST',
                data: {
                    section_id: section_id,
                    <?php echo $this->security->get_csrf_token_name(); ?>: '<?php echo $this->security->get_csrf_hash(); ?>'
                },
                dataType: 'json',
                success: function(response) {
                    var html = '<option value=""><?php echo $this->lang->line("select"); ?></option>';
                    if(response && response.length > 0) {
                        $.each(response, function(index, program) {
                            html += '<option value="' + program.id + '">' + program.title + '</option>';
                        });
                    }
                    $('#modal_program_id').html(html);
                },
                error: function(xhr, status, error) {
                    console.log('Error loading modal programs: ' + error);
                }
            });
        }
    });

    // When modal program changes, fetch classes
    $(document).on('change', '#modal_program_id', function(e) {
        var section_id = $('#modal_section_id').val();
        var program_id = $(this).val();
        
        // Clear dependent dropdown
        $('#modal_class_id').html('<option value=""><?php echo $this->lang->line("select"); ?></option>');
        $('#modal_student').html('<option value=""><?php echo $this->lang->line("select"); ?></option>');
        
        if (section_id && program_id) {
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
                    var html = '<option value=""><?php echo $this->lang->line("select"); ?></option>';
                    if (response && response.length > 0) {
                        $.each(response, function(index, classItem) {
                            html += '<option value="' + classItem.id + '">' + 
                                   (classItem.degree ? classItem.degree + ' - ' : '') + 
                                   classItem.class + '</option>';
                        });
                    }
                    $('#modal_class_id').html(html);
                },
                error: function(xhr, status, error) {
                    console.log('Error loading modal classes: ' + error);
                }
            });
        }
    });

    // When modal class changes, fetch students
    $(document).on('change', '#modal_class_id', function() {
        var section_id = $('#modal_section_id').val();
        var program_id = $('#modal_program_id').val();
        var class_id = $(this).val();
        
        if (section_id && program_id && class_id) {
            $.ajax({
                url: "<?php echo site_url('admin/approve_leave/searchByClassSectionProgram'); ?>",
                type: "POST",
                data: {
                    section_id: section_id,
                    program_id: program_id,
                    class_id: class_id,
                    <?php echo $this->security->get_csrf_token_name(); ?>: '<?php echo $this->security->get_csrf_hash(); ?>'
                },
                success: function(res) {
                    $('#modal_student').html(res);
                },
                error: function(xhr, status, error) {
                    console.log('Error loading modal students: ' + error);
                }
            });
        } else {
            $('#modal_student').html('<option value=""><?php echo $this->lang->line('select'); ?></option>');
        }
    });

    // Form submission
    $("#addleave_form").submit(function(e) {
        e.preventDefault();
        var $form = $(this);
        var $submitBtn = $form.find("button[type=submit]");
        
        // Validate required fields - using correct modal IDs
        if (!$('#modal_section_id').val()) {
            errorMsg("Please select Section");
            return false;
        }
        if (!$('#modal_program_id').val()) {
            errorMsg("Please select Program");
            return false;
        }
        if (!$('#modal_class_id').val()) {
            errorMsg("Please select Class");
            return false;
        }
        if (!$('#modal_student').val()) {
            errorMsg("Please select Student");
            return false;
        }
        if (!$('input[name="leave_status"]:checked').val()) {
            errorMsg("Please select Leave Status");
            return false;
        }
        if (!$('#from_date').val()) {
            errorMsg("Please select From Date");
            return false;
        }
        if (!$('#to_date').val()) {
            errorMsg("Please select To Date");
            return false;
        }

        var formData = new FormData(this);
        
        $submitBtn.button('loading');
        
        $.ajax({
            url: "<?php echo site_url('admin/approve_leave/add'); ?>",
            type: "POST",
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(res) {
                if (res.status == "fail") {
                    var message = "";
                    $.each(res.error, function(index, value) {
                        message += value + "\n";
                    });
                    errorMsg(message);
                } else {
                    successMsg(res.message);
                    $('#homework_docs').modal('hide');
                    window.location.reload();
                }
            },
            error: function(xhr) {
                errorMsg("Error occurred. Please try again.");
            },
            complete: function() {
                $submitBtn.button('reset');
            }
        });
    });

    function add_leave() {
        // Clear the form for new entry
        $('#addleave_form')[0].reset();
        $('#leave_id').val('');
        
        $('#title').html('<?php echo $this->lang->line('add_leave'); ?>');
        $('#homework_docs').modal('show');
        
    }

    function approve_leave(id, status, class_id, section_id) {
        $.ajax({
            url: "<?php echo site_url('admin/approve_leave/status'); ?>",
            type: "POST",
            data: {
                'class_id': class_id, 
                'section_id': section_id, 
                'id': id, 
                'status': status,
                <?php echo $this->security->get_csrf_token_name(); ?>: '<?php echo $this->security->get_csrf_hash(); ?>'
            },
            dataType: "json",
            success: function(res) {
                if (res.status == 0) {
                    errorMsg(res.error);
                } else {
                    successMsg(res.success);
                    window.location.reload(true);
                }
            }
        });
    }

    function delete_leave(leave_id, class_id, section_id) {
        var confirmation = confirm('<?php echo $this->lang->line('delete_confirm'); ?>');
        if (confirmation == true) {
            $.ajax({
                url: "<?php echo site_url('admin/approve_leave/remove_leave'); ?>",
                type: "POST",
                data: {
                    'class_id': class_id, 
                    'section_id': section_id, 
                    'id': leave_id,
                    <?php echo $this->security->get_csrf_token_name(); ?>: '<?php echo $this->security->get_csrf_hash(); ?>'
                },
                dataType: "json",
                success: function(res) {
                    if (res.status == 0) {
                        errorMsg(res.error);
                    } else {
                        successMsg(res.success);
                        window.location.reload(true);
                    }
                }
            });
        }
    }

    function get(id, class_id, section_id, status) {
        $.ajax({
            url: "<?php echo site_url('admin/approve_leave/get_details'); ?>",
            type: "POST",
            data: {
                'class_id': class_id, 
                'section_id': section_id, 
                'id': id,
                <?php echo $this->security->get_csrf_token_name(); ?>: '<?php echo $this->security->get_csrf_hash(); ?>'
            },
            dataType: 'json',
            success: function(res) {
                if (res.status == 'fail') {
                    errorMsg(res.error);
                } else {
                    // Set the values first
                    $('#apply_date').val(res.apply_date);
                    $('#from_date').val(res.from_date);
                    $('#to_date').val(res.to_date);
                    $('#message').val(res.reason); // Changed from html() to val()
                    $('#leave_id').val(res.id);
                    
                    // Set radio button based on status
                    if(status == 1) {
                        $("#leave_approve").prop('checked', true);                        
                    } else if(status == 2) {
                        $("#leave_disapprove").prop('checked', true);                         
                    } else {
                        $("#leave_pending").prop('checked', true);                         
                    }

                    $('#title').html('<?php echo $this->lang->line('edit_leave'); ?>');
                    
                    // Populate dropdowns for editing - Load sections first
                    $.ajax({
                        url: '<?php echo base_url(); ?>sections/getAll',
                        method: 'GET',
                        dataType: 'json',
                        success: function(sections) {
                            var sectionHtml = '<option value=""><?php echo $this->lang->line("select"); ?></option>';
                            if (sections && sections.length > 0) {
                                $.each(sections, function(index, section) {
                                    var selected = (section.id == res.section_id) ? ' selected' : '';
                                    sectionHtml += '<option value="' + section.id + '"' + selected + '>' + section.section + '</option>';
                                });
                            }
                            $('#modal_section_id').html(sectionHtml);
                            
                            // Load programs after section is set
                            if (res.section_id) {
                                $.ajax({
                                    url: '<?php echo base_url(); ?>programs/getBySection',
                                    method: 'POST',
                                    data: {
                                        section_id: res.section_id,
                                        <?php echo $this->security->get_csrf_token_name(); ?>: '<?php echo $this->security->get_csrf_hash(); ?>'
                                    },
                                    dataType: 'json',
                                    success: function(programs) {
                                        var programHtml = '<option value=""><?php echo $this->lang->line("select"); ?></option>';
                                        if (programs && programs.length > 0) {
                                            $.each(programs, function(index, program) {
                                                var selected = (program.id == res.program_id) ? ' selected' : '';
                                                programHtml += '<option value="' + program.id + '"' + selected + '>' + program.title + '</option>';
                                            });
                                        }
                                        $('#modal_program_id').html(programHtml);
                                        
                                        // Load classes after program is set
                                        if (res.program_id) {
                                            $.ajax({
                                                url: '<?php echo base_url(); ?>classes/getBySectionAndProgram',
                                                method: 'POST',
                                                data: {
                                                    section_id: res.section_id,
                                                    program_id: res.program_id,
                                                    <?php echo $this->security->get_csrf_token_name(); ?>: '<?php echo $this->security->get_csrf_hash(); ?>'
                                                },
                                                dataType: 'json',
                                                success: function(classes) {
                                                    var classHtml = '<option value=""><?php echo $this->lang->line("select"); ?></option>';
                                                    if (classes && classes.length > 0) {
                                                        $.each(classes, function(index, classItem) {
                                                            var selected = (classItem.id == res.class_id) ? ' selected' : '';
                                                            classHtml += '<option value="' + classItem.id + '"' + selected + '>' + 
                                                                       (classItem.degree ? classItem.degree + ' - ' : '') + 
                                                                       classItem.class + '</option>';
                                                        });
                                                    }
                                                    $('#modal_class_id').html(classHtml);
                                                    
                                                    // Load students after class is set
                                                    if (res.class_id) {
                                                        $.ajax({
                                                            url: "<?php echo site_url('admin/approve_leave/searchByClassSectionProgram'); ?>",
                                                            type: "POST",
                                                            data: {
                                                                section_id: res.section_id,
                                                                program_id: res.program_id,
                                                                class_id: res.class_id,
                                                                <?php echo $this->security->get_csrf_token_name(); ?>: '<?php echo $this->security->get_csrf_hash(); ?>'
                                                            },
                                                            success: function(studentRes) {
                                                                $('#modal_student').html(studentRes);
                                                                $('#modal_student').val(res.stud_id);
                                                                
                                                                // Show modal after all data is loaded
                                                                $('#homework_docs').modal('show');
                                                                
                                                                // Initialize date pickers after modal is shown and data is loaded
                                                                setTimeout(function() {
                                                                    initializeNepaliDatePickers();
                                                                }, 400);
                                                            }
                                                        });
                                                    } else {
                                                        // Show modal if no students to load
                                                        $('#homework_docs').modal('show');
                                                        setTimeout(function() {
                                                            initializeNepaliDatePickers();
                                                        }, 400);
                                                    }
                                                }
                                            });
                                        } else {
                                            // Show modal if no classes to load
                                            $('#homework_docs').modal('show');
                                        }
                                    }
                                });
                            } else {
                                // Show modal if no programs to load
                                $('#homework_docs').modal('show');
                            }
                        }
                    });
                }
            }
        });
    }
</script>


<script type="text/javascript">
$(document).ready(function() {
    // Initialize Nepali date pickers for homework_docs modal
    $('#apply_date').nepaliDatePicker({
        dateFormat: '%Y-%m-%d',
        ndpMonth: true,
        ndpYear: true,
        readOnlyInput: true
    });
    
    $('#from_date').nepaliDatePicker({
        dateFormat: '%Y-%m-%d',
        ndpMonth: true,
        ndpYear: true,
        readOnlyInput: true
    });
    
    $('#to_date').nepaliDatePicker({
        dateFormat: '%Y-%m-%d',
        ndpMonth: true,
        ndpYear: true,
        readOnlyInput: true
    });
    
    // Clear date fields when modal is hidden
    $('#homework_docs').on('hidden.bs.modal', function () {
        $('#from_date').val('');
        $('#to_date').val('');
        // Reset apply_date to current date
        $('#apply_date').val(formattedDate);
    });
});
</script>