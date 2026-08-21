    <script src="https://cdn.jsdelivr.net/jquery.validation/1.16.0/jquery.validate.js"></script>

    <style>
    /* Long modal layout like Business Statistics */
    #subjectModal .modal-dialog {
        max-width: 95%;
        width: 95%;
        margin: 1vh auto;
    }

    #subjectModal .modal-content {
        max-height: 95vh;
        display: flex;
        flex-direction: column;
    }

    #subjectModal .modal-body {
        max-height: 85vh;
        overflow-y: auto;
        padding: 20px;
    }

    #subjectModal .modal-header {
        flex-shrink: 0;
        padding: 15px 20px;
    }

    /* Make the marks entry form take full height */
    #subjectModal .marksEntryForm {
        max-height: none;
        overflow-y: visible;
    }

    /* Ensure table is responsive */
    #subjectModal .table-responsive {
        max-height: 70vh;
        overflow-y: auto;
    }
    </style>

    <?php
    $currency_symbol = $this->customlib->getSchoolCurrencyFormat();
    $examgroup = isset($examgroup) && !empty($examgroup) ? $examgroup : (object) array(
        'id' => 0,
        'name' => '',
        'exam_type' => '',
        'description' => '',
    );
    ?>
    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
        <!-- Main content -->
        <section class="content">
            <div class="row">
                <div class="col-md-12">
                    <!-- general form elements -->
                    <div class="box box-primary">
                        <div class="box-header ptbnull">
                            <h3 class="box-title titlefix"> <?php echo $this->lang->line('exam_list'); ?></h3>
                            <div class="impbtntitle">
                                <?php
    if ($this->rbac->hasPrivilege('exam', 'can_add')) {
        ?>
                                    <a tabindex="-1" class="btn btn-primary btn-sm" href="#" id="examModalButton"> <?php echo $this->lang->line('new_exam'); ?></a>
                                    <?php
    }
    if ($this->rbac->hasPrivilege('link_exam', 'can_view')) {
        ?>
                                    <a tabindex="-1" class="btn btn-primary btn-sm" href="#" id="examconnectModalButton" data-examgroup-id="<?php echo $examgroup->id; ?>"> <?php echo $this->lang->line('link_exams'); ?></a>
                                    <?php
    }
    ?>
                            </div>
                        </div><!-- /.box-header -->
                        <div class="box-body">
                            <input type="hidden" name="current_session" id="current_session" value="<?php echo $current_session; ?>">
                            <div class="row pb10">
                                <div class="col-lg-2 col-md-3 col-sm-12 col-xs-6">
                                    <p class="examinfo"><span> <?php echo $this->lang->line('exam_group'); ?></span> <?php echo $examgroup->name; ?></p>
                                </div><!--./col-lg-4-->
                                <div class="col-lg-2 col-md-3 col-sm-12 col-xs-6">
                                    <p class="examinfo"><span> <?php echo $this->lang->line('exam_type'); ?></span> <?php echo isset($examType[$examgroup->exam_type]) ? $examType[$examgroup->exam_type] : ''; ?></p>
                                </div><!--./col-lg-4-->
                                <div class="col-lg-8 col-md-6 col-sm-12 col-xs-12">
                                    <p class="examinfo"><span> <?php echo $this->lang->line('description'); ?> </span> <?php echo $examgroup->description; ?></p>
                                </div><!--./col-lg-4-->
                            </div><!--./row-->
                            <div class="divider2"></div>
                            <div class="row">
                                <div class="col-md-12">
                                    <input type="hidden" id="examgroup_id" name="examgroup_id" value="<?php echo $examgroup->id; ?>">
                                </div>
                            </div>
                            <div class="table-responsive mailbox-messages" id="exam_tbl" >
                                <div class="download_label"><?php echo $this->lang->line('exam_list'); ?></div>
                                <table class="table table-hover table-striped table-bordered loading1" id="exam_table">
                                    <thead>
                                        <tr class="white-space-nowrap">
                                            <th><?php echo $this->lang->line('name'); ?></th>
                                            <th><?php echo $this->lang->line('session') ?></th>
                                            <th><?php echo $this->lang->line('subjects_included'); ?></th>
                                            <th class="text text-center"><?php echo $this->lang->line('publish_exam'); ?></th>
                                            <th class="text text-center"><?php echo $this->lang->line('publish_result'); ?></th>
                                            <th class=""><?php echo $this->lang->line('description') ?></th>
                                            <th class="text-right"><?php echo $this->lang->line('action'); ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table><!-- /.table -->
                            </div><!-- /.mail-box-messages -->
                        </div><!-- /.box-body -->
                    </div>
                </div><!--/.col (left) -->
            </div>
        </section><!-- /.content -->
    </div><!-- /.content-wrapper -->

    <div class="modal fade" id="examModal">
        <div class="modal-dialog modal-lg">
            <form id="formadd" action="<?php echo site_url('admin/examgroup/ajaxaddexam'); ?>">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title"><?php echo $this->lang->line('exam'); ?></h4>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>">
                        <input type="hidden" name="exam_id" value="0">
                        <input type="hidden" name="exam_type" value="<?php echo $examgroup->exam_type; ?>">
                        <div class="row">
                            <div class="form-group col-xs-12 col-sm-9 col-md-9 col-lg-9">
                                <label for="exam"><?php echo $this->lang->line('exam') ?><small class="req"> *</small></label>
                                <input type="text" class="form-control" id="exam" name="exam">
                                <span class="text text-danger" id="exam_error"></span>
                            </div>
                            <div class="form-group col-xs-12 col-sm-3 col-md-3 col-lg-3">
                                <label for="exam"><?php echo $this->lang->line('session') ?><small class="req"> *</small></label>
                                <select  id="session_id" name="session_id" class="form-control" >
                                    <option value=""><?php echo $this->lang->line('select'); ?></option>
                                    <?php
    foreach ($sessionlist as $session) {
        ?>
                                        <option value="<?php echo $session['id']; ?>" <?php echo set_select('session_id', 'session_id', (($current_session == $session['id']) ? true : false)); ?>><?php echo $session['session']; ?></option>
                                        <?php
    }
    ?>
                                </select>
                                <span class="text text-danger" id="session_id_error"></span>
                            </div>
                            <?php
    if ($examgroup->exam_type == "average_passing") {
        ?>
        <div class="clearfix"></div>
                            <div class="form-group col-xs-12 col-sm-9 col-md-9 col-lg-9">
                                <label for="passing_percentage"><?php echo $this->lang->line('exam_passing_percentage'); ?><small class="req"> *</small></label>
                                <input type="text" class="form-control" id="passing_percentage" name="passing_percentage" autocomplete="off">
                                <span class="text text-danger" id="passing_percentage_error"></span>
                            </div>
        <?php
    }
    ?>
                            <div class="clearfix"></div>
                            <?php if ($this->rbac->hasPrivilege('exam_publish', 'can_view')){ ?>
                            <div class="col-xs-12 col-sm-2 col-md-2 col-lg-2">
                                <div class="checkbox-inline">
                                    <label>
                                        <input type="checkbox" value="1" name="is_active"> <?php echo $this->lang->line('publish'); ?>
                                    </label>
                                </div>
                            </div>
                            <?php } ?>
                            
                                <div class="col-xs-12 col-sm-2 col-md-2 col-lg-2">
                                <div class="checkbox-inline">
                                    <label>
                                        <input type="checkbox" value="1" name="is_publish" autocomplete="off"> <?php echo $this->lang->line('publish_result'); ?>
                                    </label>
                                </div>
                            </div>
                            <div class="col-xs-12 col-sm-8 col-md-8 col-lg-8">
                                <label class="radio-inline"><input type="radio" value="1" name="use_exam_roll_no" checked="checked"><?php echo $this->lang->line('admit_card_roll_no') ?>  </label>
                                <label class="radio-inline"><input type="radio" value="0" name="use_exam_roll_no"><?php echo $this->lang->line('profile_roll_no') ?>  </label>
                            </div>
                            <div class="clearfix"></div>
                            <div class="form-group col-xs-12 col-sm-12 col-md-12 col-lg-12 mt10">
                                <label for="description"><?php echo $this->lang->line('description') ?></label>
                                <textarea class="form-control" name="description" id="description"></textarea>
                            </div>
                            <div class="clearfix"></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo $this->lang->line('cancel'); ?></button>
                        <button type="submit" class="btn btn-primary" id="load" data-loading-text="<i class='fa fa-spinner fa-spin '></i> <?php echo $this->lang->line('saving'); ?>"><?php echo $this->lang->line('save') ?></button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <div id="addSubject" class="modal fade" role="dialog">
        <div class="modal-dialog modal-xl">
            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title"><?php echo $this->lang->line('add_exam_subject'); ?></h4>
                </div>
                <div class="modal-body subject-body">
                </div>
            </div>
        </div>
    </div>

    <div id="examconnectModal" class="modal fade" role="dialog">
        <div class="modal-dialog">
            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                    <h4 class="modal-title" id="myModalLabel"> <?php echo $this->lang->line('link_exam'); ?></h4>
                </div>
                <div class="modal-body examconnectModalBody">

                </div>
            </div>
        </div>
    </div>

    <!-- Custom Delete Confirmation Modal (Non-Bootstrap) - HIGHEST PRIORITY -->
    <div id="custom-delete-modal" style="display: none !important; position: fixed !important; top: 0 !important; left: 0 !important; width: 100% !important; height: 100% !important; z-index: 99999 !important; background: rgba(0,0,0,0.6) !important; overflow: auto !important; pointer-events: auto !important;">
        <div style="position: absolute !important; top: 50% !important; left: 50% !important; transform: translate(-50%, -50%) !important; background: white !important; padding: 25px !important; border-radius: 8px !important; min-width: 450px !important; max-width: 90% !important; box-shadow: 0 10px 40px rgba(0,0,0,0.5) !important; z-index: 100000 !important; pointer-events: auto !important;">
            <div style="margin-bottom: 15px; position: relative;">
                <h4 style="margin: 0 0 15px 0; font-size: 18px; font-weight: bold;"><?php echo $this->lang->line('confirmation'); ?></h4>
                <button type="button" class="custom-delete-close" style="position: absolute !important; top: 0 !important; right: 0 !important; background: none !important; border: none !important; font-size: 28px !important; cursor: pointer !important; color: #999 !important; line-height: 1 !important; padding: 0 !important; width: 30px !important; height: 30px !important;">&times;</button>
            </div>
            <div style="margin-bottom: 25px; min-height: 60px;">
                <p style="margin-bottom: 10px; font-size: 14px;"><?php echo $this->lang->line('are_you_sure_want_to_delete') ?> <b class="custom-invoice-no" style="color: #d9534f;"></b> <?php echo $this->lang->line('record_this_action_is_irreversible'); ?></p>
                <p style="margin-bottom: 0; font-size: 14px; color: #666;"><?php echo $this->lang->line('do_you_want_to_proceed') ?></p>
                <input type="hidden" name="custom_del_itemid" class="custom-del-itemid" value="">
            </div>
            <div style="text-align: right; border-top: 1px solid #eee; padding-top: 15px;">
                <button type="button" class="btn btn-default custom-delete-cancel" style="margin-right: 10px; min-width: 80px; cursor: pointer !important; pointer-events: auto !important;"><?php echo $this->lang->line('cancel'); ?></button>
                <button type="button" class="btn btn-danger custom-delete-ok" style="min-width: 80px; cursor: pointer !important; pointer-events: auto !important; z-index: 100001 !important; position: relative !important;"><?php echo $this->lang->line('delete'); ?></button>
            </div>
        </div>
    </div>

    <!-- Keep Bootstrap modal for backward compatibility but we'll use custom one -->
    <div class="delmodal modal fade" id="confirm-delete" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" style="display: none !important;">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title" id="myModalLabel"><?php echo $this->lang->line('confirmation'); ?></h4>
                </div>
                <div class="modal-body">
                    <p><?php echo $this->lang->line('are_you_sure_want_to_delete') ?> <b class="invoice_no"></b> <?php echo $this->lang->line('record_this_action_is_irreversible'); ?></p>
                    <p><?php echo $this->lang->line('do_you_want_to_proceed') ?></p>
                    <p class="debug-url"></p>
                    <input type="hidden" name="del_itemid" class="del_itemid" value="">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo $this->lang->line('cancel'); ?></button>
                    <a class="btn btn-danger btn-ok"><?php echo $this->lang->line('delete'); ?></a>
                </div>
            </div>
        </div>
    </div>

    <div id="subjectmarkModal" class="modal fade modalmark" role="dialog">
        <div class="modal-dialog modal-xl">
            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title"> <?php echo $this->lang->line('exam_subject'); ?></h4>
                </div>
                <div class="modal-body">

                </div>
            </div>
        </div>
    </div>

    <div id="teacherRemarkModal" class="modal fade" role="dialog">
        <div class="modal-dialog modal-xl">
            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title"><?php echo $this->lang->line('teacher_remark'); ?></h4>
                </div>
                <div class="modal-body">
                </div>
            </div>
        </div>
    </div>
    <!-- Modal -->

    <div id="subjectModal" class="modal fade" role="dialog">
        <div class="modal-dialog modal-xl">
            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="btn btn-default btn-sm pull-right" data-dismiss="modal" aria-label="Close" title="Close">
                        <i class="fa fa-times"></i>
                    </button>
                    <h4 class="modal-title subjectmodal_header"></h4>
                </div>
                <div class="modal-body">
                <form role="form" id="searchStudentForm" action="<?php echo site_url('admin/examgroup/subjectstudent') ?>" method="post" class="mb10">
        <?php echo $this->customlib->getCSRF(); ?>

                        <input type="hidden" name="subject_id" value="0" class="subject_id">
                        <input type="hidden" name="teachersubject_id" value="0" class="teachersubject_id">
                        <div class="row">
                            <div class="col-sm-3">
                                <div class="form-group">
                                    <label><?php echo $this->lang->line('section'); ?></label><small class="req"> *</small>
                                    <select id="section_id" name="section_id" class="form-control">
                                        <option value=""><?php echo $this->lang->line('select'); ?></option>
                                    </select>
                                    <span class="text-danger"><?php echo form_error('section_id'); ?></span>
                                </div>
                            </div>

                            <div class="col-sm-3">
                                <div class="form-group">
                                    <label for="exampleInputEmail1"><?php echo $this->lang->line('program'); ?></label><small class="req"> *</small>
                                    <select id="program_id" name="program_id" class="form-control">
                                        <option value=""><?php echo $this->lang->line('select'); ?></option>
                                    </select>
                                    <span class="text-danger"><?php echo form_error('program_id'); ?></span>
                                </div>
                            </div>
                            <div class="col-sm-3">
                                <div class="form-group">
                                    <label><?php echo $this->lang->line('class'); ?></label> <small class="req"> *</small>
                                    <select id="class_id" name="class_id" class="form-control">
                                        <option value=""><?php echo $this->lang->line('select'); ?></option>
                                    </select>
                                    <span class="text-danger" id="error_class_id"></span>
                                </div>
                            </div>

                            <div class="col-sm-3">
                                <div class="form-group">
                                    <label><?php echo $this->lang->line('session'); ?><small class="req"> *</small></label>
                                    <select  id="session_id" name="session_id" class="form-control" >
                                        <option value=""><?php echo $this->lang->line('select'); ?></option>
                                        <?php
    foreach ($sessionlist as $session) {
        $selected = ($current_session == $session['id']) ? 'selected="selected"' : '';
        ?>
                                            <option value="<?php echo $session['id'] ?>" <?php echo $selected; ?>><?php echo $session['session'] ?></option>
                                            <?php
    }
    ?>
                                    </select>
                                </div><!--./form-group-->
                            </div>
                            
                            <div class="col-sm-12">
                                <div class="form-group">
                                    <button type="submit" name="search" value="search_filter" class="btn btn-primary pull-right btn-sm checkbox-toggle"><i class="fa fa-search"></i> <?php echo $this->lang->line('search'); ?></button>
                                </div>
                            </div>
                        </div>
                    </form>
                    <div class="examheight100 relative">
                        <div id="examfade"></div>
                        <div id="exammodal">
                            <img id="loader" src="<?php echo base_url() ?>/backend/images/loading_blue.gif" />
                        </div>
                        <div class="marksEntryForm">

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div id="allotStudentModal" class="modal fade" role="dialog">
        <div class="modal-dialog modal-xl">
            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title"> <?php echo $this->lang->line('exam_students'); ?></h4>
                </div>
                <div class="modal-body">
                    <form role="form" id="allotStudentForm" action="<?php echo site_url('admin/examgroup/examstudent') ?>" method="post" >
                        <input type="hidden" name="exam_id" value="0" class="exam_group_class_batch_exam_id">
                        <div class="row">
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
                        <div class="row">
                            <div class="col-sm-12">
                                <button type="submit" name="search" value="search_filter" class="btn btn-primary pull-right btn-sm checkbox-toggle"><i class="fa fa-search"></i> <?php echo $this->lang->line('search'); ?></button>
                            </div>
                        </div>
                    </form><br>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="scroll-area-fullheight">
                                <div class="studentAllotForm">
                                </div>
                            </div>
                        </div>
                    </div>                
                </div>
            </div>
        </div>
    </div>


    <div id="studentRankModal" class="modal fade modalmark" role="dialog" aria-hidden="true" style="display: none;">
        <div class="modal-dialog modal-xl">
            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" autocomplete="off">×</button>
                    <h4 class="modal-title"><?php echo $this->lang->line('student_exam_rank'); ?> :  <b class="exam_title"></b></h4>
                </div>

                <div class="modal-body minheight260">  

                <div class="modal_loader_div" style="display: none;"></div>

                    <div class="modal-body-inner">
                        
                    </div>


                </div>
            </div>
        </div>
    </div>

    <div id="assignSubjectGroupModal" class="modal fade" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title"><?php echo $this->lang->line('assign_subject_groups') ? $this->lang->line('assign_subject_groups') : 'Assign Subject Group'; ?>: <b class="assign-sg-exam-name"></b></h4>
                </div>
                <div class="modal-body assign-sg-modal-body">
                    <div class="text-center p20"><i class="fa fa-spinner fa-spin fa-2x"></i></div>
                </div>
            </div>
        </div>
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
                
                // Do not fill #subjectModal's section — it is filled by getLevelsForExam when Enter Marks is opened
                $('select#section_id').not('#subjectModal select#section_id').html(html);
                
                // If there's a pre-selected section, trigger program load (only for non-subjectModal)
                if (section_id) {
                    $('select#section_id').not('#subjectModal select#section_id').trigger('change');
                }
            }
        });
    });
    </script>

    <script type="text/javascript">
    // When section changes, fetch programs (do not run for Enter Marks or Link Students modals — they use getLevelsForExam)
    $(document).on('change', '#section_id', function (e) {
        if ($(this).closest('#subjectModal').length || $(this).closest('#allotStudentModal').length) return;
        var section_id = $(this).val();
        
        // Clear program and class dropdowns (same modal only)
        var $ctx = $(this).closest('.modal').length ? $(this).closest('.modal') : $(document);
        $ctx.find('#program_id').html('<option value=""><?php echo $this->lang->line("select"); ?></option>');
        $ctx.find('#class_id').html('<option value=""><?php echo $this->lang->line("select"); ?></option>');
        
        if (section_id != '') {
            $ctx.find('#program_id').html('<option value="">Loading...</option>');
            
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
                    
                    $ctx.find('#program_id').html(html);
                    if(program_id) {
                        $ctx.find('#program_id').trigger('change');
                    }
                },
                error: function(xhr, status, error) {
                    console.error("Error fetching programs:", error);
                    $ctx.find('#program_id').html('<option value="">Error loading programs</option>');
                }
            });
        }
    });

    // When program changes, fetch classes (do not run for Enter Marks or Link Students modals)
    $(document).on('change', '#program_id', function (e) {
        if ($(this).closest('#subjectModal').length || $(this).closest('#allotStudentModal').length) return;
        var section_id = $(this).closest('.modal').length ? $(this).closest('.modal').find('#section_id').val() : $('#section_id').val();
        var program_id = $(this).val();
        
        console.log("Fetching classes for Section:", section_id, "Program:", program_id);
        
        var $ctx = $(this).closest('.modal').length ? $(this).closest('.modal') : $(document);
        $ctx.find('#class_id').html('<option value="">Loading...</option>');
        
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
                
                $ctx.find('#class_id').html(html);
            },
            error: function(xhr, status, error) {
                console.error("AJAX Error:", status, error);
                console.error("Response:", xhr.responseText);
                $ctx.find('#class_id').html('<option value="">Error loading classes</option>');
            }
        });
    });

    // Levels allowed for this exam (from subject groups). Set when modal opens.
    window.allotLevelsForExam = [];
    window.allotLevelsForExamSections = [];

    // Function to initialize the modal: load only levels linked to exam's subject group(s)
    function initializeAllotStudentModal() {
        var examId = $('#allotStudentModal .exam_group_class_batch_exam_id').val();
        $('#allotStudentModal #program_id').html('<option value=""><?php echo $this->lang->line("select"); ?></option>');
        $('#allotStudentModal #class_id').html('<option value=""><?php echo $this->lang->line("select"); ?></option>');
        $('#allotStudentModal .studentAllotForm').empty();
        if (!examId) {
            $('#allotStudentModal #section_id').html('<option value=""><?php echo $this->lang->line("select"); ?></option>');
            return;
        }
        $('#allotStudentModal #section_id').html('<option value="">Loading...</option>');
        $.ajax({
            url: '<?php echo site_url("admin/examgroup/getLevelsForExam"); ?>',
            method: 'POST',
            data: {
                exam_id: examId,
                <?php echo $this->security->get_csrf_token_name(); ?>: '<?php echo $this->security->get_csrf_hash(); ?>'
            },
            dataType: 'json',
            success: function (res) {
                if (!res || typeof res !== 'object') {
                    $('#allotStudentModal #section_id').html('<option value=""><?php echo addslashes($this->lang->line("error_occurred_please_try_again") ?: "Error loading levels"); ?></option>');
                    return;
                }
                window.allotLevelsForExam = res.levels || [];
                window.allotLevelsForExamSections = res.sections || [];
                var html = '<option value=""><?php echo $this->lang->line("select"); ?></option>';
                if (res.status === 0 || window.allotLevelsForExam.length === 0) {
                    html += '<option value="" disabled>' + (res.message || '<?php echo addslashes($this->lang->line("assign_subject_groups_first") ?: "Assign subject groups to this exam first"); ?>') + '</option>';
                    $('#allotStudentModal .studentAllotForm').html('<div class="alert alert-info">' + (res.message || '') + '</div>');
                } else {
                    $.each(window.allotLevelsForExamSections, function (i, s) {
                        html += '<option value="' + s.id + '">' + (s.section || '') + '</option>';
                    });
                }
                $('#allotStudentModal #section_id').html(html);
            },
            error: function(xhr, status, error) {
                var msg = 'Error loading levels.';
                var detail = '';
                try {
                    if (xhr.responseText) {
                        var r = null;
                        try { r = typeof xhr.responseText === 'string' ? JSON.parse(xhr.responseText) : null; } catch (e2) {}
                        if (r && r.message) {
                            msg = r.message;
                        } else {
                            detail = 'HTTP ' + (xhr.status || '?') + ' – ' + (xhr.statusText || status || error);
                            if (xhr.responseText && xhr.responseText.length > 0) {
                                var preview = xhr.responseText.replace(/\s+/g, ' ').substring(0, 200);
                                if (preview.indexOf('<') !== -1) detail += ' (server returned a page – check URL or login).';
                                else detail += ' – ' + preview;
                            }
                        }
                    } else {
                        detail = 'HTTP ' + (xhr.status || '?') + ' – ' + (xhr.statusText || status || error);
                    }
                } catch (e) {
                    detail = (xhr.status ? 'HTTP ' + xhr.status : '') + ' ' + (error || status);
                }
                if (detail && detail !== msg) msg = msg + ' ' + detail;
                var safeMsg = $('<div/>').text(msg).html();
                $('#allotStudentModal #section_id').html('<option value="">' + safeMsg.substring(0, 80) + (safeMsg.length > 80 ? '…' : '') + '</option>');
                $('#allotStudentModal .studentAllotForm').html('<div class="alert alert-danger"><strong>Link students – error</strong><br>' + safeMsg + '</div>');
            }
        });
    }

    // Event handler for modal show
    $(document).on('shown.bs.modal', '#allotStudentModal', function () {
        initializeAllotStudentModal();
        // SAFE INIT: custom scrollbar guard
        try {
        if ($.fn.mCustomScrollbar) {
            $('#allotStudentModal .scroll-area-fullheight').mCustomScrollbar({ theme: 'minimal-dark' });
        } else {
            console.warn('mCustomScrollbar plugin missing – skipping init');
        }
        } catch (e) {
        console.warn('mCustomScrollbar init failed:', e);
        }
    });

    // When section changes: show only programs from allowed levels (subject group)
    $(document).on('change', '#allotStudentModal #section_id', function (e) {
        var section_id = $(this).val();
        $('#allotStudentModal #program_id').html('<option value=""><?php echo $this->lang->line("select"); ?></option>');
        $('#allotStudentModal #class_id').html('<option value=""><?php echo $this->lang->line("select"); ?></option>');
        if (!section_id || !window.allotLevelsForExam || window.allotLevelsForExam.length === 0) return;
        var sid = parseInt(section_id, 10);
        var seen = {};
        var html = '<option value=""><?php echo $this->lang->line("select"); ?></option>';
        for (var i = 0; i < window.allotLevelsForExam.length; i++) {
            var lev = window.allotLevelsForExam[i];
            if (lev.section_id !== sid) continue;
            var pid = lev.program_id || 0;
            if (seen[pid]) continue;
            seen[pid] = true;
            html += '<option value="' + pid + '">' + (lev.program_name || '—') + '</option>';
        }
        $('#allotStudentModal #program_id').html(html);
    });

    // When program changes: show only classes from allowed levels (subject group)
    $(document).on('change', '#allotStudentModal #program_id', function (e) {
        var section_id = $('#allotStudentModal #section_id').val();
        var program_id = $(this).val();
        $('#allotStudentModal #class_id').html('<option value=""><?php echo $this->lang->line("select"); ?></option>');
        if (!section_id || !program_id || !window.allotLevelsForExam || window.allotLevelsForExam.length === 0) return;
        var sid = parseInt(section_id, 10);
        var pid = parseInt(program_id, 10);
        var html = '<option value=""><?php echo $this->lang->line("select"); ?></option>';
        for (var i = 0; i < window.allotLevelsForExam.length; i++) {
            var lev = window.allotLevelsForExam[i];
            if (lev.section_id !== sid || (lev.program_id || 0) !== pid) continue;
            html += '<option value="' + lev.class_id + '">' + (lev.class || '') + '</option>';
        }
        $('#allotStudentModal #class_id').html(html);
    });

    // Reset modal when closed
    $(document).on('hidden.bs.modal', '#allotStudentModal', function () {
        window.allotLevelsForExam = [];
        window.allotLevelsForExamSections = [];
        $('#allotStudentModal #section_id').html('<option value=""><?php echo $this->lang->line("select"); ?></option>');
        $('#allotStudentModal #program_id').html('<option value=""><?php echo $this->lang->line("select"); ?></option>');
        $('#allotStudentModal #class_id').html('<option value=""><?php echo $this->lang->line("select"); ?></option>');
        $('#allotStudentModal .studentAllotForm').empty();
        $('#allotStudentForm')[0].reset();
    });

    // Modified existing modal reset function to include program dropdown
    $('#allotStudentModal').on('hidden.bs.modal', function () {
        $('form#allotStudentForm').find('select#class_id').prop("selectedIndex", 0);
        $('form#allotStudentForm').find('select#section_id').find('option:not(:first)').remove();
        $('form#allotStudentForm').find('select#program_id').find('option:not(:first)').remove();
        $('#allotStudentModal').find('div.studentAllotForm').html("");
        $("span[id$='_error']").html("");
    });
    </script>

    <script type="text/javascript">
        // Keep all existing functionality intact
        $(document).on('click', '.assignStudent', function () {
            var examid = $(this).data('examid');
            $('.exam_group_class_batch_exam_id').val(examid);
            $('#allotStudentModal').modal('show');
        });

        $("form#allotStudentForm").on('submit', (function (e) {
            e.preventDefault(); // avoid to execute the actual submit of the form.
            var form = $(this);
            var $this = form.find("button[type=submit]:focus");
            var url = form.attr('action');
            $.ajax({
                url: url,
                type: "POST",
                data: new FormData(this),
                dataType: 'json',
                contentType: false,
                cache: false,
                processData: false,
                beforeSend: function () {
                    $this.button('loading');
                },
                success: function (res)
                {
                    if (res.status == 1) {
                        $('.studentAllotForm').html(res.page);
                    } else {
                        var message = "";
                        $.each(res.error, function (index, value) {
                            message += value;
                        });
                        errorMsg(message);
                    }
                },
                error: function (xhr) { // if error occured
                    alert("<?php echo $this->lang->line('error_occurred_please_try_again'); ?>");
                    $this.button('reset');
                },
                complete: function () {
                    $this.button('reset');
                }
            });
        }));

        $(document).on('submit', 'form#allot_exam_student', function (e) {
            e.preventDefault(); // avoid to execute the actual submit of the form.
            var form = $(this);
            var $this = form.find("button[type=submit]:focus");
            var url = form.attr('action');
            $.ajax({
                url: url,
                type: "POST",
                data: new FormData(this),
                dataType: 'json',
                contentType: false,
                cache: false,
                processData: false,
                beforeSend: function () {
                    $this.button('loading');
                },
                success: function (res)
                {
                    if (res.status == 1) {
                        successMsg(res.message);
                        $('#allotStudentModal').modal('hide');
                    } else {
                        errorMsg(res.message);
                    }
                    $this.button('reset');
                },
                error: function (xhr) { // if error occured
                    alert("<?php echo $this->lang->line('error_occurred_please_try_again'); ?>");
                    $this.button('reset');
                },
                complete: function () {
                    $this.button('reset');
                }
            });
        });

        $(document).on('click', '.select_all', function (e) {
            if (this.checked) {
                $(this).closest('div.table-responsive').find('[type=checkbox]').prop('checked', true);
            } else {
                $(this).closest('div.table-responsive').find('[type=checkbox]').prop('checked', false);
            }
        });
    </script>

    <script type="text/javascript">
        $(document).ready(function () {
        all_records();
        });

    </script>
    <script type="text/javascript">
        $(document).ready(function () {

    $('#studentRankModal').modal({
                backdrop: 'static',
                keyboard: false,
                show:false
            });

            // Ensure Rank modal X / close button always works (even when modal was forced visible)
            $(document).on('click', '#studentRankModal .close, #studentRankModal button[data-dismiss="modal"]', function (e) {
                e.preventDefault();
                e.stopPropagation();
                $('#studentRankModal').modal('hide');
                $('#studentRankModal').css('display', '');
                $('body').removeClass('modal-open');
                $('.modal-backdrop').remove();
            });

            // ===== CUSTOM DELETE MODAL (NO BOOTSTRAP) =====
            var deleteInProgress = false;
            var modalKeepVisibleInterval = null;
            var $customDeleteModal = $('#custom-delete-modal');
            var $bootstrapModal = $('#confirm-delete');
            
            // ENSURE MODAL IS HIDDEN ON PAGE LOAD - CRITICAL!
            $customDeleteModal.css({
                'display': 'none',
                'visibility': 'hidden',
                'opacity': '0',
                'pointer-events': 'none'
            }).hide();
            $customDeleteModal.attr('style', 'display: none !important; visibility: hidden !important; opacity: 0 !important; pointer-events: none !important;');
            
            // Completely disable Bootstrap modal - remove all event handlers
            $bootstrapModal.off('show.bs.modal hide.bs.modal shown.bs.modal hidden.bs.modal');
            $bootstrapModal.modal('hide'); // Hide it if it's showing
            $bootstrapModal.css('display', 'none !important'); // Force hide
            $bootstrapModal.attr('style', 'display: none !important; visibility: hidden !important;');
            
            // Remove Bootstrap modal data to prevent it from being used
            if ($bootstrapModal.data('bs.modal')) {
                $bootstrapModal.data('bs.modal', null);
            }
            
            // Prevent Bootstrap modal from opening via data attributes
            $(document).off('click', '[data-target="#confirm-delete"]');
            $(document).off('click', '[data-toggle="modal"][data-target="#confirm-delete"]');
            
            // Disable any Bootstrap modal triggers
            $bootstrapModal.on('show.bs.modal', function(e) {
                e.preventDefault();
                e.stopPropagation();
                return false;
            });
            
            // Verify custom modal exists
            if ($customDeleteModal.length === 0) {
                console.error('Custom delete modal not found in DOM!');
            } else {
                console.log('✓ Custom delete modal found and ready - HIDDEN by default');
            }
            
            // Remove all old handlers
            $(document).off('click', '.delete-exam-btn');
            
            // Handle delete button click - show CUSTOM modal ONLY
            $(document).on('click', '.delete-exam-btn', function(e) {
                e.preventDefault();
                e.stopPropagation();
                e.stopImmediatePropagation();
                
                console.log('=== DELETE BUTTON CLICKED - USING CUSTOM MODAL ===');
                
                if (deleteInProgress) {
                    console.log('Delete already in progress');
                    return false;
                }
                
                // Ensure Bootstrap modal is hidden
                $bootstrapModal.modal('hide');
                $bootstrapModal.css('display', 'none');
                
                var examId = $(this).data('id');
                var examName = $(this).data('exam');
                
                console.log('Delete data:', {id: examId, name: examName});
                
                if (!examId) {
                    alert('Error: No exam ID found');
                    return false;
                }
                
                // Set data in custom modal
                $('.custom-del-itemid').val(examId);
                $('.custom-invoice-no').html(examName);
                
                // Show custom modal - FORCE DISPLAY with !important
                $customDeleteModal.attr('style', 
                    'display: block !important; ' +
                    'position: fixed !important; ' +
                    'top: 0 !important; ' +
                    'left: 0 !important; ' +
                    'width: 100% !important; ' +
                    'height: 100% !important; ' +
                    'z-index: 99999 !important; ' +
                    'background: rgba(0,0,0,0.6) !important; ' +
                    'visibility: visible !important; ' +
                    'opacity: 1 !important;'
                );
                
                // Also set via CSS as backup
                $customDeleteModal.css({
                    'display': 'block',
                    'z-index': '99999',
                    'visibility': 'visible',
                    'opacity': '1'
                }).show();
                
                // Rebind cancel button handlers when modal is shown (in case they were lost)
                $customDeleteModal.find('.custom-delete-cancel, .custom-delete-close').off('click').on('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    e.stopImmediatePropagation();
                    
                    console.log('Cancel/Close clicked (rebound handler)');
                    
                    if (modalKeepVisibleInterval) {
                        clearInterval(modalKeepVisibleInterval);
                        modalKeepVisibleInterval = null;
                    }
                    
                    $customDeleteModal.css({
                        'display': 'none',
                        'visibility': 'hidden',
                        'opacity': '0'
                    }).hide();
                    
                    $('.custom-del-itemid').val('');
                    $('.custom-invoice-no').html('');
                    
                    return false;
                });
                
                console.log('=== CUSTOM MODAL DISPLAYED ===');
                console.log('Modal display:', $customDeleteModal.css('display'));
                console.log('Modal visible:', $customDeleteModal.is(':visible'));
                console.log('Modal z-index:', $customDeleteModal.css('z-index'));
                
                // Keep modal visible - check every 100ms (ONLY when modal should be visible)
                if (modalKeepVisibleInterval) clearInterval(modalKeepVisibleInterval);
                modalKeepVisibleInterval = setInterval(function() {
                    // Only keep visible if delete is NOT in progress and modal should be shown
                    // Check if modal was intentionally shown (has exam ID set)
                    var hasExamId = $('.custom-del-itemid').val() !== '';
                    if (!deleteInProgress && hasExamId && ($customDeleteModal.css('display') === 'none' || !$customDeleteModal.is(':visible'))) {
                        console.log('Modal lost visibility - RESTORING');
                        $customDeleteModal.attr('style', 
                            'display: block !important; ' +
                            'position: fixed !important; ' +
                            'top: 0 !important; ' +
                            'left: 0 !important; ' +
                            'width: 100% !important; ' +
                            'height: 100% !important; ' +
                            'z-index: 99999 !important; ' +
                            'background: rgba(0,0,0,0.6) !important; ' +
                            'pointer-events: auto !important;'
                        ).show();
                    } else if (!hasExamId && $customDeleteModal.is(':visible')) {
                        // If no exam ID, modal should be hidden - clear interval and hide
                        clearInterval(modalKeepVisibleInterval);
                        modalKeepVisibleInterval = null;
                        $customDeleteModal.css({
                            'display': 'none',
                            'visibility': 'hidden',
                            'opacity': '0',
                            'pointer-events': 'none'
                        }).hide();
                    }
                }, 100);
                
                return false;
            });
            
            // Handle Cancel button - Use document delegation for reliability
            $(document).off('click', '.custom-delete-cancel, .custom-delete-close');
            $(document).on('click', '.custom-delete-cancel, .custom-delete-close', function(e) {
                e.preventDefault();
                e.stopPropagation();
                e.stopImmediatePropagation();
                
                console.log('Cancel/Close button clicked');
                
                // Clear interval
                if (modalKeepVisibleInterval) {
                    clearInterval(modalKeepVisibleInterval);
                    modalKeepVisibleInterval = null;
                }
                
                // Hide modal with forced styles
                $customDeleteModal.css({
                    'display': 'none',
                    'visibility': 'hidden',
                    'opacity': '0'
                }).hide();
                
                // Clear form data
                $('.custom-del-itemid').val('');
                $('.custom-invoice-no').html('');
                
                console.log('Modal closed');
                
                return false;
            });
            
            // Also bind directly to buttons as backup
            $customDeleteModal.find('.custom-delete-cancel, .custom-delete-close').off('click').on('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                e.stopImmediatePropagation();
                
                console.log('Cancel/Close button clicked (direct handler)');
                
                // Clear interval
                if (modalKeepVisibleInterval) {
                    clearInterval(modalKeepVisibleInterval);
                    modalKeepVisibleInterval = null;
                }
                
                // Hide modal
                $customDeleteModal.css({
                    'display': 'none',
                    'visibility': 'hidden',
                    'opacity': '0'
                }).hide();
                
                // Clear form data
                $('.custom-del-itemid').val('');
                $('.custom-invoice-no').html('');
                
                return false;
            });
            
            // Centralized delete function
            function handleDeleteAction($button) {
                if (deleteInProgress) {
                    console.log('Delete already in progress, ignoring');
                    return false;
                }
                
                var id = $('.custom-del-itemid').val();
                console.log('Exam ID to delete:', id);
                
                if (!id) {
                    console.error('No exam ID found');
                    if (typeof errorMsg === 'function') {
                        errorMsg('<?php echo $this->lang->line("error_occurred_please_try_again"); ?>');
                    } else {
                        alert('Error: No exam ID found');
                    }
                    return false;
                }
                
                deleteInProgress = true;
                var $okButton = $button || $('.custom-delete-ok');
                var originalText = $okButton.text();
                $okButton.prop('disabled', true).text('Deleting...');
                
                // Get CSRF token
                var csrfName = '<?php echo $this->security->get_csrf_token_name(); ?>';
                var csrfHash = '<?php echo $this->security->get_csrf_hash(); ?>';
                var deleteUrl = '<?php echo site_url("admin/examgroup/deleteExam") ?>';
                
                console.log('Starting delete AJAX call:', {
                    url: deleteUrl,
                    id: id,
                    csrfName: csrfName
                });
                
                $.ajax({
                    type: "POST",
                    url: deleteUrl,
                    dataType: 'JSON',
                    data: {
                        'id': id,
                        [csrfName]: csrfHash
                    },
                    beforeSend: function() {
                        console.log('Delete request sent to server');
                    },
                    success: function(data) {
                        console.log('Delete response received:', data);
                        
                        // Clear interval first
                        if (modalKeepVisibleInterval) {
                            clearInterval(modalKeepVisibleInterval);
                            modalKeepVisibleInterval = null;
                        }
                        
                        // Hide modal completely
                        $customDeleteModal.css({
                            'display': 'none',
                            'visibility': 'hidden',
                            'opacity': '0',
                            'pointer-events': 'none'
                        }).hide();
                        $customDeleteModal.attr('style', 'display: none !important; visibility: hidden !important; opacity: 0 !important; pointer-events: none !important;');
                        $('.custom-del-itemid').val('');
                        $('.custom-invoice-no').html('');
                        
                        // Show the ACTUAL message from server
                        var message = '';
                        if (data && data.message) {
                            message = data.message;
                        } else if (data.status == 1 || data.status == "1") {
                            message = '<?php echo $this->lang->line("record_deleted_successfully"); ?>';
                        } else {
                            message = '<?php echo $this->lang->line("something_went_wrong"); ?>';
                        }
                        
                        if (data.status == 1 || data.status == "1") {
                            if (typeof successMsg === 'function') {
                                successMsg(message);
                            } else {
                                alert(message);
                            }
                            
                            // Refresh the exam list
                            if (typeof all_records === 'function') {
                                all_records();
                            } else {
                                location.reload();
                            }
                        } else {
                            // Show the actual error message from server
                            if (typeof errorMsg === 'function') {
                                errorMsg(message);
                            } else {
                                alert(message);
                            }
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Delete AJAX error:', {
                            status: status,
                            error: error,
                            responseText: xhr.responseText,
                            statusCode: xhr.status
                        });
                        
                        // Clear interval
                        if (modalKeepVisibleInterval) {
                            clearInterval(modalKeepVisibleInterval);
                            modalKeepVisibleInterval = null;
                        }
                        
                        // Hide modal on error
                        $customDeleteModal.css({
                            'display': 'none',
                            'visibility': 'hidden',
                            'opacity': '0',
                            'pointer-events': 'none'
                        }).hide();
                        $customDeleteModal.attr('style', 'display: none !important; visibility: hidden !important; opacity: 0 !important; pointer-events: none !important;');
                        $('.custom-del-itemid').val('');
                        $('.custom-invoice-no').html('');
                        
                        // Try to parse error message from response
                        var errorMessage = '<?php echo $this->lang->line("error_occurred_please_try_again"); ?>';
                        try {
                            if (xhr.responseText) {
                                var errorData = JSON.parse(xhr.responseText);
                                if (errorData && errorData.message) {
                                    errorMessage = errorData.message;
                                }
                            }
                        } catch (e) {
                            // If parsing fails, use default message
                            console.log('Could not parse error response');
                        }
                        
                        // Show the actual error message
                        if (typeof errorMsg === 'function') {
                            errorMsg(errorMessage);
                        } else {
                            alert(errorMessage);
                        }
                    },
                    complete: function() {
                        deleteInProgress = false;
                        $okButton.prop('disabled', false).text(originalText);
                        console.log('Delete process complete');
                    }
                });
            }
            
            // Handle Delete button - Single handler to prevent duplicate calls
            $(document).off('click', '.custom-delete-ok'); // Remove any existing handlers first
            
            // Bind directly to the button for immediate response
            $customDeleteModal.find('.custom-delete-ok').off('click').on('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                e.stopImmediatePropagation();
                
                console.log('=== DELETE BUTTON CLICKED (Direct) ===');
                
                // Prevent duplicate calls
                if (deleteInProgress) {
                    console.log('Delete already in progress, ignoring duplicate click');
                    return false;
                }
                
                // Call the delete function with button reference
                handleDeleteAction($(this));
                
                return false;
            });
            
            // Document-level delegation as backup
            $(document).on('click', '.custom-delete-ok', function(e) {
                // Skip if this is the direct handler's target
                if ($(e.target).closest('#custom-delete-modal').length === 0) {
                    return true;
                }
                
                e.preventDefault();
                e.stopPropagation();
                e.stopImmediatePropagation();
                
                console.log('=== DELETE BUTTON CLICKED (Document) ===');
                
                // Prevent duplicate calls
                if (deleteInProgress) {
                    console.log('Delete already in progress, ignoring duplicate click');
                    return false;
                }
                
                // Call the delete function with button reference
                handleDeleteAction($(this));
                
                return false;
            });
            
            // Close modal when clicking backdrop (but not the content)
            // Only allow closing if delete is NOT in progress
            $(document).on('click', '#custom-delete-modal', function(e) {
                // Only close if clicking directly on the backdrop, not on child elements
                if ($(e.target).is('#custom-delete-modal') && !deleteInProgress) {
                    console.log('Backdrop clicked - closing modal');
                    // Clear interval
                    if (modalKeepVisibleInterval) {
                        clearInterval(modalKeepVisibleInterval);
                        modalKeepVisibleInterval = null;
                    }
                    $customDeleteModal.css({
                        'display': 'none',
                        'visibility': 'hidden',
                        'opacity': '0',
                        'pointer-events': 'none'
                    }).hide();
                    $customDeleteModal.attr('style', 'display: none !important; visibility: hidden !important; opacity: 0 !important; pointer-events: none !important;');
                    $('.custom-del-itemid').val('');
                    $('.custom-invoice-no').html('');
                }
            });
            
            // Prevent modal content clicks from closing the modal
            $(document).on('click', '#custom-delete-modal > div', function(e) {
                e.stopPropagation();
                e.stopImmediatePropagation();
            });
            
            // Ensure delete button is always clickable
            $customDeleteModal.find('.custom-delete-ok').css({
                'pointer-events': 'auto',
                'cursor': 'pointer',
                'z-index': '100001',
                'position': 'relative'
            });
            
            // SAFETY CHECK: Ensure modal doesn't block clicks when it shouldn't be visible
            // Run this check every 500ms to catch any issues
            setInterval(function() {
                var hasExamId = $('.custom-del-itemid').val() !== '';
                var isModalVisible = $customDeleteModal.is(':visible') || $customDeleteModal.css('display') !== 'none';
                
                // If modal is visible but shouldn't be (no exam ID), hide it
                if (!hasExamId && isModalVisible && !deleteInProgress) {
                    console.warn('Modal is visible but should be hidden - forcing hide');
                    if (modalKeepVisibleInterval) {
                        clearInterval(modalKeepVisibleInterval);
                        modalKeepVisibleInterval = null;
                    }
                    $customDeleteModal.css({
                        'display': 'none',
                        'visibility': 'hidden',
                        'opacity': '0',
                        'pointer-events': 'none'
                    }).hide();
                    $customDeleteModal.attr('style', 'display: none !important; visibility: hidden !important; opacity: 0 !important; pointer-events: none !important;');
                }
            }, 500);
            
            // ===== ALL OLD BOOTSTRAP MODAL CODE REMOVED - USING CUSTOM MODAL NOW =====
        });
    </script>

    <script type="text/javascript">

        var batch_subjects = "";
        var x = 1;
        var date_format = '<?php echo $result = strtr($this->customlib->getSchoolDateFormat(), ['d' => 'dd', 'm' => 'mm', 'Y' => 'yyyy', 'M' => 'MM']) ?>';

        var date_format_time = '<?php echo $result = strtr($this->customlib->getSchoolDateFormat(), ['d' => 'DD', 'm' => 'MM', 'Y' => 'YYYY', 'M' => 'MMM']) ?>';
        $(document).ready(function () {
    // Initialize modals
    $('#examconnectModal,#subjectmarkModal,#allotStudentModal,#teacherRemarkModal').modal({
        backdrop: 'static',
        keyboard: false,
        show: false
    });
    
    // Ensure examconnectModal is properly initialized and prevent auto-close
    $('#examconnectModal').on('hidden.bs.modal', function () {
        $('.examconnectModalBody').html("");
    });
    
    // Prevent examconnectModal from being closed unintentionally
    var examconnectModalExplicitClose = false;
    var examconnectModalJustOpened = false;
    
    $('#examconnectModal').on('shown.bs.modal', function() {
        console.log('examconnectModal is now shown');
        examconnectModalJustOpened = true;
        // Reset flag after a short delay
        setTimeout(function() {
            examconnectModalJustOpened = false;
        }, 500);
    });
    
    $('#examconnectModal').on('hide.bs.modal', function (e) {
        console.log('examconnectModal hide event triggered');
        console.log('explicitClose:', examconnectModalExplicitClose);
        console.log('justOpened:', examconnectModalJustOpened);
        
        // If modal just opened, prevent it from closing immediately
        if (examconnectModalJustOpened && !examconnectModalExplicitClose) {
            console.log('Preventing examconnectModal from closing immediately after opening');
            e.preventDefault();
            e.stopPropagation();
            examconnectModalJustOpened = false;
            return false;
        }
        
        // If this is not an explicit close action, check if it's from another modal
        if (!examconnectModalExplicitClose) {
            // Check if this is being triggered by another modal or script
            var stackTrace = new Error().stack || '';
            var isFromOtherModal = stackTrace.indexOf('subjectmarkModal') !== -1 || 
                                  stackTrace.indexOf('subjectModal') !== -1 ||
                                  stackTrace.indexOf('allotStudentModal') !== -1;
            
            if (isFromOtherModal) {
                console.log('Preventing examconnectModal from being closed by another modal');
                e.preventDefault();
                e.stopPropagation();
                return false;
            }
        }
        
        // Reset the flag
        examconnectModalExplicitClose = false;
    });
    
    // Mark explicit close actions
    $(document).on('click', '#examconnectModal .close, #examconnectModal [data-dismiss="modal"]', function() {
        console.log('Explicit close action detected');
        examconnectModalExplicitClose = true;
    });
            
            // Ensure subjectmarkModal doesn't interfere with subjectModal
            $('#subjectmarkModal').on('hidden.bs.modal', function () {
                // Clean up any lingering modal states
                $('body').removeClass('modal-open');
                $('.modal-backdrop').remove();
            });
            
            // Prevent subjectmarkModal from closing unexpectedly
            $('#subjectmarkModal').on('hide.bs.modal', function (e) {
                console.log('subjectmarkModal hide event triggered');
            });

            $('.date').datepicker({
                format: date_format,
                autoclose: true
            });

            $('.datetime').datetimepicker();
            $('#examModalButton').click(function () {
                $('#examModal').modal({
                    backdrop: 'static',
                    keyboard: false
                });
            });

            $('#examModal').on('hidden.bs.modal', function () {
                reset_exm_form();
                $("span[id$='_error']").html("");
            });

            function reset_exm_form() {
                var current_session = $('#current_session').val();
                console.log(current_session);
                $('#formadd')[0].reset();
                $("#class_id").prop("selectedIndex", 0);
                $("#section_id,#batch_id").find('option:not(:first)').remove();
                $("#formadd input[name=exam_id]").val(0);
                $('#session_id').val(current_session);
            }

            $(document).on('click', '.editexamModalButton', function (e) {

                reset_exm_form();
                var exam_id = $(this).data('exam_id');
                $.ajax({
                    type: "POST",
                    url: base_url + "admin/examgroup/getExamByID",
                    data: {'exam_id': exam_id},
                    dataType: "json",
                    beforeSend: function () {
                        $('#formadd')[0].reset();
                    },
                    success: function (data) {
                        $("#formadd select[name=session_id] [value=" + data.exam.session_id + "]").attr('selected', 'true');
                        $("#formadd input[name=exam]").val(data.exam.exam);
                        $("#formadd input[name=date_from]").val(data.exam.date_from);
                        $("#formadd input[name=exam_id]").val(data.exam.id);
                        $("#formadd input[name=date_to]").val(data.exam.date_to);
                        $("#formadd select[name=class_id] [value=" + data.exam.class_id + "]").attr('selected', 'true');
                        $("#formadd textarea[name=description]").val(data.exam.description);

                        if(data.exam.exam_group_type =="average_passing"){
                            $("#formadd input[name=passing_percentage]").val(data.exam.passing_percentage);
                        }
                        if (data.exam.is_active == 1) {

                            $("#formadd input[name=is_active]").prop('checked', true);
                        }

                    $("#formadd input[name=use_exam_roll_no][value='"+data.exam.use_exam_roll_no+"']").prop("checked",true);
                        if (data.exam.is_publish == 1) {
                            $("#formadd input[name=is_publish]").prop('checked', true);
                        }

                        $('#examModal').modal('show');
                    },
                    complete: function () {

                    }
                });
            });

            $(document).on('click', '.subjectModalButton', function (e) {
                e.preventDefault();
                var exam_id = $(this).data('exam_id');
                var exam_group_id = $('#examgroup_id').val();
                batch_subjects = ''; // clear so we never show another exam's subject list
                $('#addSubject').modal({
                    backdrop: 'static',
                    keyboard: false
                });
                // Show loading while fetching
                $('#addSubject .subject-body').html('<div style="text-align:center;padding:30px;"><i class="fa fa-spinner fa-spin fa-2x"></i></div>');
                $.ajax({
                    type: 'POST',
                    url: base_url + 'admin/examgroup/getexamSubjects',
                    data: { 'exam_group_id': exam_group_id, 'class_batch_id': '', 'exam_id': exam_id },
                    dataType: 'json',
                    success: function (data) {
                        var content = (data && data.subject_page) ? data.subject_page : '<div class="alert alert-warning">No content received. Please assign subject groups to this exam and try again.</div>';
                        $('#addSubject .subject-body').html(content);

                        /* NEW: ensure any pre-rendered numeric fields accept decimals */
                        enableDecimalFields('#addSubject .subject-body');

                        var tmp_row = $('#item_table');

                        // SAFE INIT for nepaliDatePicker / datetimepicker
                        if ($.fn.nepaliDatePicker) {
                            $('.datepicker_init input', tmp_row).nepaliDatePicker({
                                dateFormat: '%Y-%m-%d',
                                ndpMonth: true,
                                ndpYear: true,
                                readOnlyInput: true
                            });
                        } else {
                            console.warn('nepaliDatePicker plugin missing – date inputs left plain');
                        }
                        if ($.fn.datetimepicker) {
                            $('.datepicker_init_time', tmp_row).datetimepicker({
                                format: 'HH:mm:ss',
                                showTodayButton: true,
                                ignoreReadonly: true
                            });
                        } else {
                            console.warn('datetimepicker plugin missing');
                        }

                        batch_subjects = data.batch_subject_dropdown;
                        if (data.exam_subjects_count > 0) {
                            x = data.exam_subjects_count + 1;
                        }
                    },
                    error: function (xhr) {
                        var msg = 'Error loading subjects.';
                        var detail = '';
                        if (xhr.status) {
                            if (xhr.status === 404) detail = ' (Page not found)';
                            else if (xhr.status === 500) detail = ' (Server error 500)';
                            else if (xhr.status === 403) detail = ' (Forbidden)';
                            else detail = ' (HTTP ' + xhr.status + ')';
                        }
                        if (xhr.responseText) {
                            try {
                                var r = JSON.parse(xhr.responseText);
                                if (r.message) msg = r.message;
                                else if (r.error) msg = typeof r.error === 'string' ? r.error : (r.error.message || msg);
                            } catch (e) {
                                if (xhr.responseText.length < 500) detail = ' — ' + xhr.responseText.replace(/<[^>]+>/g, ' ').trim().substring(0, 200);
                            }
                        }
                        $('#addSubject .subject-body').html('<div class="alert alert-danger"><strong>Error loading subjects</strong>' + detail + '<br>' + msg + '</div>');
                    }
                });
            });

            function getBatchByClassSection(section_id, batch_id) {
                if (section_id != "") {
                    $('#batch_id').html("");
                    var div_data = '<option value=""><?php echo $this->lang->line('select'); ?></option>';

                    $.ajax({
                        type: "POST",
                        url: base_url + "admin/batchsubject/getBatchByClassSection",
                        data: {'class_section_id': section_id},
                        dataType: "JSON",
                        beforeSend: function () {
                            $('#batch_id').addClass('dropdownloading');
                        },
                        success: function (data) {
                            $.each(data, function (i, obj)
                            {
                                var sel = "";
                                if (batch_id == obj.batch_id) {
                                    sel = "selected";
                                }
                                div_data += "<option value=" + obj.id + " " + sel + ">" + obj.batch_name + "</option>";
                            });
                            $('#batch_id').append(div_data);
                        },
                        complete: function () {
                            $('#batch_id').removeClass('dropdownloading');
                        }
                    });
                }
            }
        });

    // Handle Link Exams button click - prevent default Bootstrap behavior
    $(document).on('click', '#examconnectModalButton', function(e) {
        e.preventDefault();
        e.stopPropagation();
        e.stopImmediatePropagation();
        
        console.log('Link Exams button clicked');
        
        var examgroup_id = $(this).data('examgroup-id') || 
                          $(this).data('examgroupId') ||
                          $(this).attr('data-examgroup-id');
        
        console.log('Link Exams - examgroup_id from button:', examgroup_id);
        
        if (!examgroup_id) {
            alert("<?php echo $this->lang->line('error_occurred_please_try_again'); ?> - Exam Group ID not found");
            return false;
        }
        
        // Store examgroup_id in modal for the show.bs.modal event
        $('#examconnectModal').data('examgroup-id', examgroup_id);
        
        // Prevent any other handlers from closing the modal
        examconnectModalExplicitClose = false;
        
        // Small delay to ensure all event handlers are ready
        setTimeout(function() {
            console.log('Opening examconnectModal with examgroup_id:', examgroup_id);
            $('#examconnectModal').modal('show');
        }, 100);
        
        return false;
    });
    
    $('#examconnectModal').on('show.bs.modal', function (e) {
        console.log('Link Exams Modal - show.bs.modal event triggered');
        
        // Prevent immediate closing - set flags
        examconnectModalJustOpened = true;
        examconnectModalExplicitClose = false;
        
        // Get examgroup_id from multiple sources
        var examgroup_id = $(e.relatedTarget) ? $(e.relatedTarget).data('examgroup-id') || 
                          $(e.relatedTarget).data('examgroupId') : null;
        
        // If not from relatedTarget, get from modal data or button
        if (!examgroup_id) {
            examgroup_id = $('#examconnectModal').data('examgroup-id') ||
                          $('#examconnectModalButton').data('examgroup-id') ||
                          $('#examconnectModalButton').data('examgroupId') ||
                          $('#examconnectModalButton').attr('data-examgroup-id');
        }
        
        console.log('Link Exams - examgroup_id:', examgroup_id);
        
        if (!examgroup_id) {
            console.error('Link Exams - No examgroup_id found!');
            alert("<?php echo $this->lang->line('error_occurred_please_try_again'); ?> - Exam Group ID not found");
            examconnectModalExplicitClose = true; // Allow closing if no ID
            $(this).modal('hide');
            return;
        }
        
        // Clear previous content and show loading
        $('.examconnectModalBody').html('<div class="text-center"><i class="fa fa-spinner fa-spin fa-2x"></i> Loading...</div>');
        
        // Get CSRF token (even though CSRF is disabled, include it for compatibility)
        var csrfName = '<?php echo $this->security->get_csrf_token_name(); ?>';
        var csrfHash = '<?php echo $this->security->get_csrf_hash(); ?>';
        
        console.log('Link Exams - Sending AJAX request with examgroup_id:', examgroup_id);
        
        $.ajax({
            type: "POST",
            url: base_url + "admin/examgroup/connectexams",
            data: {
                'examgroup_id': examgroup_id,
                [csrfName]: csrfHash
            },
            dataType: "JSON",
            beforeSend: function () {
                console.log('Link Exams - AJAX beforeSend');
            },
            success: function (data)
            {
                console.log('Link Exams - AJAX Success Response:', data);
                console.log('Link Exams - Response type:', typeof data);
                console.log('Link Exams - Response keys:', data ? Object.keys(data) : 'null');
                
                // Handle both JSON string and object
                if (typeof data === 'string') {
                    try {
                        data = JSON.parse(data);
                        console.log('Link Exams - Parsed JSON string:', data);
                    } catch(e) {
                        console.error('Link Exams - Failed to parse JSON:', e);
                        $('.examconnectModalBody').html('<div class="alert alert-danger">Invalid response format: ' + e.message + '</div>');
                        return;
                    }
                }
                
                if (data && data.exam_page) {
                    console.log('Link Exams - Setting exam_page content, length:', data.exam_page.length);
                    $('.examconnectModalBody').html(data.exam_page);
                    console.log('Link Exams - Content set successfully');
                } else {
                    console.error('Link Exams - No exam_page in response:', data);
                    var errorHtml = '<div class="alert alert-danger">No data received. ';
                    if (data) {
                        errorHtml += 'Response: ' + JSON.stringify(data);
                    } else {
                        errorHtml += 'Empty response';
                    }
                    errorHtml += '</div>';
                    $('.examconnectModalBody').html(errorHtml);
                }
            },
            error: function (xhr, status, error) {
                console.error('Link Exams AJAX Error:', {
                    status: status,
                    error: error,
                    responseText: xhr.responseText ? xhr.responseText.substring(0, 500) : 'No response',
                    statusCode: xhr.status,
                    readyState: xhr.readyState
                });
                
                var errorMsg = '<?php echo $this->lang->line('error_occurred_please_try_again'); ?>';
                if (xhr.responseText) {
                    try {
                        var errorData = JSON.parse(xhr.responseText);
                        if (errorData.message) {
                            errorMsg = errorData.message;
                        }
                    } catch(e) {
                        errorMsg += ' (Status: ' + xhr.status + ')';
                        if (xhr.responseText.indexOf('<!DOCTYPE') !== -1) {
                            errorMsg += ' - HTML response received (possible redirect or error page)';
                        }
                    }
                }
                $('.examconnectModalBody').html('<div class="alert alert-danger">' + errorMsg + '</div>');
            },
            complete: function () {
                console.log('Link Exams - AJAX request completed');
            }
        });
    });
        $("#formadd").submit(function (e) {
            e.preventDefault(); // avoid to execute the actual submit of the form.
            $("span[id$='_error']").html("");
            var form = $(this);
            var url = form.attr('action');
            var submit_button = $(this).find(':submit');
            
            // Get form data
            var post_params = $(this).serializeArray();
            post_params.push({name: 'exam_group_id', value: $('#examgroup_id').val()});
            
            // Add CSRF token if not already present
            var csrf_token_name = '<?php echo $this->security->get_csrf_token_name(); ?>';
            var csrf_token_value = '<?php echo $this->security->get_csrf_hash(); ?>';
            var csrf_exists = false;
            for (var i = 0; i < post_params.length; i++) {
                if (post_params[i].name === csrf_token_name) {
                    csrf_exists = true;
                    post_params[i].value = csrf_token_value;
                    break;
                }
            }
            if (!csrf_exists) {
                post_params.push({name: csrf_token_name, value: csrf_token_value});
            }
            
            // Convert to object for easier manipulation
            var post_data = {};
            $.each(post_params, function(i, field) {
                post_data[field.name] = field.value;
            });
            
            // Set button to loading state
            submit_button.button('loading');
            submit_button.prop('disabled', true);
            
            // Create a timeout fallback to ensure button resets
            var timeoutFallback = setTimeout(function() {
                console.error('Form submission timeout - forcing button reset');
                submit_button.button('reset');
                submit_button.prop('disabled', false);
                errorMsg("<?php echo $this->lang->line('error_occurred_please_try_again'); ?>");
            }, 35000); // 35 seconds - slightly longer than AJAX timeout

            $.ajax({
                type: "POST",
                url: url,
                data: post_data,
                dataType: "text",
                timeout: 30000, // 30 second timeout
                beforeSend: function () {
                    console.log('Sending exam form data to:', url);
                },
                success: function (response)
                {
                    clearTimeout(timeoutFallback);
                    console.log('Exam form raw response received');
                    console.log('Response length:', response ? response.length : 0);
                    
                    // Always reset button first
                    submit_button.button('reset');
                    submit_button.prop('disabled', false);
                    
                    var data;
                    try {
                        // Try to extract JSON from response
                        var jsonMatch = response.match(/\{[\s\S]*\}$/);
                        if (jsonMatch) {
                            data = JSON.parse(jsonMatch[0]);
                            console.log('Parsed JSON data:', data);
                        } else if (response.trim() === '') {
                            // Empty response - might be success
                            console.warn('Empty response received');
                            $('#formadd')[0].reset();
                            successMsg("<?php echo $this->lang->line('exam'); ?> <?php echo $this->lang->line('updated_successfully'); ?>");
                            $('#examModal').modal('hide');
                            if (typeof all_records === 'function') {
                                all_records();
                            }
                            return;
                        } else {
                            // Try to parse entire response as JSON
                            try {
                                data = JSON.parse(response);
                                console.log('Parsed full response as JSON:', data);
                            } catch (e2) {
                                // Check if response contains PHP errors but exam was saved
                                if (response.indexOf('A PHP Error was encountered') > -1 || response.indexOf('success') > -1) {
                                    console.warn('PHP errors or success message detected');
                                    $('#formadd')[0].reset();
                                    successMsg("<?php echo $this->lang->line('exam'); ?> <?php echo $this->lang->line('updated_successfully'); ?>");
                                    $('#examModal').modal('hide');
                                    if (typeof all_records === 'function') {
                                        all_records();
                                    }
                                    return;
                                }
                                throw new Error('Invalid response format: ' + response.substring(0, 200));
                            }
                        }
                    } catch (e) {
                        console.error('JSON parse error:', e);
                        console.error('Response preview:', response ? response.substring(0, 500) : 'No response');
                        errorMsg("<?php echo $this->lang->line('error_occurred_please_try_again'); ?>");
                        return;
                    }

                    // Handle response data
                    if (!data || !data.status || data.status == 0 || data.status == "0") {
                        if (data && data.error) {
                            $.each(data.error, function (index, value) {
                                var errorDiv = '#' + index + '_error';
                                if ($(errorDiv).length) {
                                    $(errorDiv).empty().append(value);
                                }
                            });
                        } else {
                            errorMsg(data && data.message ? data.message : "<?php echo $this->lang->line('error_occurred_please_try_again'); ?>");
                        }
                    } else if (data.status == 1 || data.status == "1" || data.status === true) {
                        $('#section_id').find('option').not(':first').remove();
                        $('#batch_id').find('option').not(':first').remove();
                        $('#formadd')[0].reset();
                        successMsg(data.message || "<?php echo $this->lang->line('exam'); ?> <?php echo $this->lang->line('updated_successfully'); ?>");
                        $('#examModal').modal('hide');
                        if (typeof all_records === 'function') {
                            all_records();
                        }
                    } else {
                        // Unknown status
                        console.warn('Unknown status in response:', data);
                        errorMsg("<?php echo $this->lang->line('error_occurred_please_try_again'); ?>");
                    }
                },
                error: function (xhr, status, error) {
                    clearTimeout(timeoutFallback);
                    console.error('Exam form AJAX error:', {
                        status: status,
                        error: error,
                        httpStatus: xhr.status,
                        responseText: xhr.responseText ? xhr.responseText.substring(0, 500) : 'No response'
                    });
                    
                    // Always reset button
                    submit_button.button('reset');
                    submit_button.prop('disabled', false);
                    
                    if (status === 'timeout') {
                        errorMsg("<?php echo $this->lang->line('error_occurred_please_try_again'); ?>");
                    } else if (xhr.status === 403) {
                        errorMsg("<?php echo $this->lang->line('error_occurred_please_try_again'); ?>");
                    } else if (xhr.status === 500) {
                        errorMsg("<?php echo $this->lang->line('error_occurred_please_try_again'); ?>");
                    } else {
                        errorMsg("<?php echo $this->lang->line('error_occurred_please_try_again'); ?>");
                    }
                },
                complete: function () {
                    clearTimeout(timeoutFallback);
                    // Final safety net - always reset button
                    setTimeout(function() {
                        submit_button.button('reset');
                        submit_button.prop('disabled', false);
                    }, 100);
                }
            });
        });
    </script>

    <script type="text/javascript">
        $('.datepicker_init').datetimepicker({
            format: date_format_time,
            reReadonly: true
        });

        function all_records() {
            var examgroup_id = $('#examgroup_id').val();
            if (!examgroup_id) {
                $('#exam_tbl').find('tbody').empty();
                return;
            }
            var postData = {
                examgroup_id: examgroup_id,
                '<?php echo $this->security->get_csrf_token_name(); ?>': '<?php echo $this->security->get_csrf_hash(); ?>'
            };
            $.ajax({
                type: "POST",
                url: base_url + "admin/examgroup/getexam",
                data: postData,
                dataType: "JSON",
                beforeSend: function () {

                },
                success: function (data)
                {
                    $('#exam_tbl').find('tbody').empty().append(data.exam_page || '');

                },
                error: function (xhr) { // if error occured
                    alert("<?php echo $this->lang->line('error_occurred_please_try_again'); ?>");
                },
                complete: function () {

                }
            });
        }
    </script>
    <script>
        $(document).on('submit', '.ssaddSubject', function (e) {

            e.preventDefault();
            var form = $(this);
            var subsubmit_button = $(this).find(':submit');
            var formdata = form.serializeArray();
            formdata.push({name: 'examgroup_id', value: $('#examgroup_id').val()});
            $.ajax({
                type: "POST",
                url: form.attr('action'),
                data: formdata, // serializes the form's elements.
                dataType: "JSON", // serializes the form's elements.
                beforeSend: function () {
                    subsubmit_button.button('loading');
                },
                success: function (response)
                {
                    if (response.status == 0) {
                        var message = "";
                        $.each(response.error, function (index, value) {

                            message += value;
                        });
                        errorMsg(message);

                    } else {
                        all_records();
                        successMsg(response.message);
                        $('#addSubject').modal('hide');
                    }
                },
                error: function (xhr) { // if error occured
                    alert("<?php echo $this->lang->line('error_occurred_please_try_again'); ?>");
                    subsubmit_button.button('reset');
                },
                complete: function () {
                    subsubmit_button.button('reset');
                }
            });
        });
    </script>
    <script>
    $(document).on('click', '.add', function() {
        // First check if we already have subjects loaded
        if (!batch_subjects || batch_subjects.trim() === '') {
            // If not, fetch them first
            fetchSubjectsAndAddRow();
        } else {
            // If we have subjects, just add the row
            addSubjectRow();
        }
    });

    /* NEW: helper to make numeric fields decimal-friendly in any context */
    function enableDecimalFields(ctx) {
        var $ctx = ctx ? $(ctx) : $(document);
        $ctx.find('input.credit_hours, input.max_marks, input.min_marks')
            .attr({ type: 'number', step: '0.1', inputmode: 'decimal', min: '0' });
    }

    function fetchSubjectsAndAddRow() {
        var exam_id = $('.subjectModalButton').data('exam_id');
        var exam_group_id = $('#examgroup_id').val();
        
        // Show loading indicator
        $('#addSubject .subject-body').prepend('<div class="loading-subjects">Loading subjects...</div>');
        
        $.ajax({
            type: 'POST',
            url: base_url + 'admin/examgroup/getexamSubjects',
            data: { 
                'exam_group_id': exam_group_id, 
                'class_batch_id': '', 
                'exam_id': exam_id 
            },
            dataType: 'json',
            success: function(data) {
                if (data.batch_subject_dropdown) {
                    batch_subjects = data.batch_subject_dropdown;
                    addSubjectRow();
                } else {
                    errorMsg('No subjects available to add');
                }
                $('.loading-subjects').remove();
            },
            error: function(xhr) {
                $('.loading-subjects').remove();
                var msg = 'Error loading subjects. Please try again.';
                if (xhr.responseText) {
                    try {
                        var r = JSON.parse(xhr.responseText);
                        if (r.message) msg = r.message; else if (r.error) msg = (typeof r.error === 'string') ? r.error : (r.error.message || msg);
                    } catch (e) { }
                }
                if (xhr.status === 500) msg += ' (Server error 500 — check application/logs)';
                errorMsg(msg);
            }
        });
    }

    function addSubjectRow() {
        if (!batch_subjects || batch_subjects.trim() === '') {
            errorMsg('Subject list is not available. Please try again.');
            return;
        }
        
        var html = '';
        html += '<tr>';
        html += '<td width="150"><select name="subject_' + x + '" class="form-control item_unit tddm200">' + batch_subjects + '</select></td>';
        html += '<td><div class="input-group datepicker_init"><input type="text" name="date_from_' + x + '" class="form-control"/><span class="input-group-addon" id="basic-addon2"><i class="fa fa-calendar"></i></span></div></td>';
        html += '<td><div class="input-group datepicker_init_time"><input type="text" name="time_from' + x + '" class="form-control"/><span class="input-group-addon" id="basic-addon2"><i class="fa fa-clock-o"></i></span></div></td>';
        html += '<td><input type="text" name="duration' + x + '" class="form-control duration" value="0"/></td>';

        /* UPDATED: decimal-friendly number fields */
        html += '<td><input type="number" name="credit_hours' + x + '" class="form-control credit_hours" value="0" step="0.1" inputmode="decimal" min="0"/></td>';
        html += '<td class=""><input type="text" name="room_no_' + x + '" class="form-control room_no" /></td>';
        html += '<td class=""><input type="number" name="max_marks_' + x + '" class="form-control max_marks" step="0.1" inputmode="decimal" min="0"/></td>';
        html += '<td class=""><input type="hidden" name="rows[]" value="' + x + '"> <input name="prev_row[' + x + ']" type="hidden" value="0"><input type="number" name="min_marks_' + x + '" class="form-control min_marks" step="0.1" inputmode="decimal" min="0"/></td>';

        html += '<td class="text-center" style="vertical-align: middle; cursor: pointer;"><span class="text text-danger remove fa fa-times mt5"></span></td></tr>';
        
        var tmp_row = $('#item_table').append(html);

        // make sure the newly added inputs accept decimals
        enableDecimalFields(tmp_row);

        // SAFE INIT for nepaliDatePicker / datetimepicker
        if ($.fn.nepaliDatePicker) {
            $('.datepicker_init input', tmp_row).nepaliDatePicker({
                dateFormat: '%Y-%m-%d',
                ndpMonth: true,
                ndpYear: true,
                readOnlyInput: true
            });
        } else {
            console.warn('nepaliDatePicker plugin missing – date inputs left plain');
        }

        if ($.fn.datetimepicker) {
            $('.datepicker_init_time', tmp_row).datetimepicker({
                format: 'HH:mm:ss',
                showTodayButton: true,
                ignoreReadonly: true
            });
        } else {
            console.warn('datetimepicker plugin missing');
        }
        
        x++;
    }

        $(document).on('click', '.remove', function () {
            $(this).closest('tr').remove();
        });

        $('#insert_form').on('submit', function (event) {
            event.preventDefault();
            var error = '';
            $('.item_name').each(function () {
                var count = 1;
                if ($(this).val() == '')
                {
                    error += "<p>Enter Item Name at " + count + " Row</p>";
                    return false;
                }
                count = count + 1;
            });

            $('.item_quantity').each(function () {
                var count = 1;
                if ($(this).val() == '')
                {
                    error += "<p>Enter Item Quantity at " + count + " Row</p>";
                    return false;
                }
                count = count + 1;
            });

            $('.item_unit').each(function () {
                var count = 1;
                if ($(this).val() == '')
                {
                    error += "<p>Select Unit at " + count + " Row</p>";
                    return false;
                }
                count = count + 1;
            });

            var form_data = $(this).serialize();
            if (error == '')
            {
                $.ajax({
                    url: "insert.php",
                    method: "POST",
                    data: form_data,
                    success: function (data)
                    {
                        if (data == 'ok')
                        {
                            $('#item_table').find("tr:gt(0)").remove();
                            $('#error').html('<div class="alert alert-success"><?php echo $this->lang->line('item_details_saved'); ?></div>');
                        }
                    }
                });
            } else
            {
                $('#error').html('<div class="alert alert-danger">' + error + '</div>');
            }
        });
    </script>
    <script type="text/javascript">
        $(document).on('submit', 'form#connectExamForm', function (e) {

            e.preventDefault();
            var form = $(this);
            var sub_connect_exam = $("button[type=submit]:focus");
            var formdata = form.serializeArray();
                formdata.push({ name: "action", value: sub_connect_exam.attr('name')});
            $.ajax({
                type: "POST",
                url: form.attr('action'),
                data: formdata, // serializes the form's elements.
                dataType: "JSON", // serializes the form's elements.
                beforeSend: function () {
                    sub_connect_exam.button('loading');
                    $('.error_connection').html("");
                },
                success: function (response)
                {

                    if (response.status == 0) {
                        $('.error_connection').html($('<div>', {class: 'alert alert-info', text: response.message}));
                    } else {
                        successMsg(response.message);
                        $('#examconnectModal').modal('hide');
                    }
                    sub_connect_exam.button('reset');
                },
                error: function (xhr) { // if error occured

                    alert("<?php echo $this->lang->line('error_occurred_please_try_again'); ?>");
                    sub_connect_exam.button('reset');

                },
                complete: function () {
                    sub_connect_exam.button('reset');
                }
            });
        });
        $(document).on('click', '#ckbCheckAll', function (e) {
            $(".checkBoxExam").prop('checked', $(this).prop('checked'));
        });

    $(document).on('click', '.examTeacherReamark', function () {
            var $this = $(this);
            console.log("sdfsfs");
            var recordid = $this.data('recordid');
            $.ajax({
                type: 'POST',
                url: base_url + "admin/examgroup/getTeacherRemarkByExam",
                data: {'recordid': recordid},
                dataType: 'JSON',
                beforeSend: function () {
                    $this.button('loading');
                },
                success: function (data) {
                    $('#teacherRemarkModal .modal-body').html(data.subject_page);
                    $('#teacherRemarkModal').modal('show');
                    $this.button('reset');
                },
                error: function (xhr) { // if error occured
                    alert("<?php echo $this->lang->line('error_occurred_please_try_again'); ?>");
                    $this.button('reset');
                },
                complete: function () {
                    $this.button('reset');
                }
            });
        });

        $(document).on('click', '.examMarksSubject', function () {
            var $this = $(this);
            var recordid = $this.data('recordid');
            
            console.log('ExamMarksSubject clicked, recordid:', recordid);
            
            $('input[name=recordid]').val(recordid);
            $.ajax({
                type: 'POST',
                url: base_url + "admin/examgroup/getSubjectByExam",
                data: {'recordid': recordid},
                dataType: 'JSON',
                beforeSend: function () {
                    $this.button('loading');
                },
                success: function (data) {
                    console.log('getSubjectByExam success, data:', data);
                    $('#subjectmarkModal .modal-body').html(data.subject_page);
                    
                    // Small delay to ensure modal content is rendered
                    setTimeout(function() {
                        console.log('Showing subjectmarkModal');
                        // Ensure no other modals are open (but not delete modal or examconnectModal)
                        $('.modal').not('#confirm-delete').not('.delmodal').not('#examconnectModal').modal('hide');
                        $('.modal-backdrop').remove();
                        $('body').removeClass('modal-open');
                        
                        // Small delay before showing the modal
                        setTimeout(function() {
                            $('#subjectmarkModal').modal('show');
                        }, 50);
                    }, 100);
                    
                    $this.button('reset');
                },
                error: function (xhr) { // if error occured
                    alert("<?php echo $this->lang->line('error_occurred_please_try_again'); ?>");
                    $this.button('reset');
                },
                complete: function () {
                    $this.button('reset');
                }
            });
        });
    </script>
    <script type="text/javascript">
        // Initialize subjectModal only once
        if (!window.subjectModalInitialized) {
            $('#subjectModal').modal({
                backdrop: 'static',
                keyboard: false,
                show: false
            });
            window.subjectModalInitialized = true;
        }
        
        // Add debounce mechanism to prevent rapid modal triggers
        var subjectModalDebounce = null;
        var subjectModalOpening = false;
        
        window.openSubjectModal = function(subject_id, subject_name, teachersubject_id) {
            if (subjectModalOpening) {
                console.log('SubjectModal already opening, ignoring duplicate request');
                return;
            }
            
            if (subjectModalDebounce) {
                clearTimeout(subjectModalDebounce);
            }
            
            subjectModalOpening = true;
            subjectModalDebounce = setTimeout(function() {
                console.log('Opening SubjectModal with debounce:', {subject_id, subject_name, teachersubject_id});
                
                // Close any other modals first (but not delete modal or examconnectModal)
                $('.modal').not('#subjectModal').not('#confirm-delete').not('.delmodal').not('#examconnectModal').modal('hide');
                
                // Set the data (scope to subjectModal so the form's hidden inputs are set)
                $('.subjectmodal_header').html(subject_name);
                $('.marksEntryForm').html("");
                $('#subjectModal').find('input.subject_id').val(subject_id);
                $('#subjectModal').find('input.teachersubject_id').val(teachersubject_id);
                $('#subjectModal').find('input[name="subject_name"]').val(subject_name);
                var current_session = $('#current_session').val();
                // Ensure session_id is set to current session
                $('#subjectModal #session_id').val(current_session);
                $('#subjectModal #session_id option[value="'+current_session+'"]').prop("selected", true);
                
                // Show the modal
                $('#subjectModal').modal('show');
                
                // Ensure modal is properly sized for long content
                setTimeout(function() {
                    var modalDialog = $('#subjectModal .modal-dialog');
                    var modalContent = $('#subjectModal .modal-content');
                    var modalBody = $('#subjectModal .modal-body');
                    
                    // Set modal to be long and wide
                    modalDialog.css({
                        'max-width': '95%',
                        'width': '95%',
                        'margin': '1vh auto'
                    });
                    
                    modalContent.css({
                        'max-height': '95vh',
                        'display': 'flex',
                        'flex-direction': 'column'
                    });
                    
                    modalBody.css({
                        'max-height': '85vh',
                        'overflow-y': 'auto',
                        'padding': '20px'
                    });
                    
                    console.log('Modal configured for long layout');
                }, 100);
                
                // Reset the flag after a delay
                setTimeout(function() {
                    subjectModalOpening = false;
                }, 500);
            }, 100);
        };

        // Remove the old shown.bs.modal handler that was causing conflicts
        // The new openSubjectModal function handles this properly

        $('#subjectModal').on('hidden.bs.modal', function () {
            console.log('SubjectModal hidden');
            subjectModalOpening = false;
            $('.subjectmodal_header').html("");
            $('.marksEntryForm').html("");
            $('.subject_id').val("");
            $("#searchStudentForm").find('input:text,select,textarea').val('');
            $('#section_id').find('option').not(':first').remove();
            $('#session_id > option[selected="selected"]').removeAttr('selected');
            $(this).removeData('marksExamId');
            window.marksLevelsForExam = [];
        });
        
        // Add debugging for subjectModal show event
        $('#subjectModal').on('show.bs.modal', function (e) {
            console.log('SubjectModal show event triggered');
            
            // Prevent rapid open/close cycles
            if ($(this).hasClass('modal-open')) {
                console.log('SubjectModal already open, preventing duplicate');
                e.preventDefault();
                return false;
            }
        });
        
        // Ensure modal is properly configured for long layout when shown
        $('#subjectModal').on('shown.bs.modal', function (e) {
            console.log('SubjectModal shown event triggered');
            
            // Ensure session_id is set to current session
            var current_session = $('#current_session').val();
            if (current_session) {
                $('#subjectModal #session_id').val(current_session);
                $('#subjectModal #session_id option[value="'+current_session+'"]').prop('selected', true);
            }
            
            // Configure for long layout
            var modalDialog = $(this).find('.modal-dialog');
            var modalContent = $(this).find('.modal-content');
            var modalBody = $(this).find('.modal-body');
            
            modalDialog.css({
                'max-width': '95%',
                'width': '95%',
                'margin': '1vh auto'
            });
            
            modalContent.css({
                'max-height': '95vh',
                'display': 'flex',
                'flex-direction': 'column'
            });
            
            modalBody.css({
                'max-height': '85vh',
                'overflow-y': 'auto',
                'padding': '20px'
            });
            
            console.log('Modal configured for long layout on shown event');
        });
        
        // Handle custom openSubjectModalBtn clicks (Enter Marks)
        $(document).on('click', '.openSubjectModalBtn', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            var subject_id = $(this).data('subject_id');
            var subject_name = $(this).data('subject_name');
            var teachersubject_id = $(this).data('teachersubject_id');
            var exam_id = $(this).data('exam_id') || $('#subjectmarkModal #subjectmark_exam_id').val() || '';
            var examIdNum = (exam_id && exam_id !== '0') ? parseInt(exam_id, 10) : null;
            $('#subjectModal').data('marksExamId', examIdNum);
            
            console.log('openSubjectModalBtn clicked:', {subject_id, subject_name, teachersubject_id, exam_id, examIdNum});
            
            if (window.openSubjectModal) {
                window.openSubjectModal(subject_id, subject_name, teachersubject_id);
            }
        });


        /* FIX: single delegated handler for subject search (prevents full-page JSON) */
        $(document).off('submit', '#subjectModal form#searchStudentForm');
        $(document).on('submit', '#subjectModal form#searchStudentForm', function (e) {
            e.preventDefault();
            e.stopImmediatePropagation();

            var form = $(this);
            var $this = form.find("button[type=submit]");
            var url = form.attr('action');

            // Ensure session_id is set if empty
            var current_session = $('#current_session').val();
            var session_id_val = $('#subjectModal #session_id').val();
            if (!session_id_val || session_id_val === '' || session_id_val === null) {
                $('#subjectModal #session_id').val(current_session);
                session_id_val = current_session;
            }
            
            // Explicitly set the session_id value in the form before creating FormData
            $('#subjectModal #session_id').val(session_id_val);
            
            // DEBUG: log outgoing payload clearly
            var fd = new FormData(this);
            // Ensure session_id is in FormData - always append to be safe
            fd.set('session_id', session_id_val);
            console.group('subjectstudent → request');
            fd.forEach((v,k)=>console.log(k, '→', v));
            console.groupEnd();

            $.ajax({
                url: url,
                type: "POST",
                data: fd,
                dataType: 'json',
                contentType: false,
                cache: false,
                processData: false,
                beforeSend: function () {
                    $this.button('loading');
                    $('#examfade,#exammodal').css({'display': 'block'});
                },
                success: function (res)
                {
                    console.group('subjectstudent ← response');
                    console.log(res);
                    if (res && res.debug) console.log('server.debug:', res.debug);
                    console.groupEnd();

                    $('#examfade,#exammodal').css({'display': 'none'});
                    if (res.status == "0") {
                        $('.marksEntryForm').html('');
                        var message = "";
                        $.each(res.error, function (index, value) {
                            message += value;
                        });
                        errorMsg(message);
                    } else {
                        $('.marksEntryForm').html(res.page);
                        if ($.fn.dropify) {
                        $('.marksEntryForm').find('.dropify').dropify();
                        }
                        /* ensure decimal-friendly numeric fields if any appear in this page */
                        enableDecimalFields('.marksEntryForm');
                        
                        // Ensure the marks entry form works with long layout
                        setTimeout(function() {
                            var marksForm = $('.marksEntryForm');
                            if (marksForm.length > 0) {
                                // Remove height restrictions for long layout
                                marksForm.css('max-height', 'none');
                                marksForm.css('overflow-y', 'visible');
                                console.log('Marks entry form configured for long layout');
                            }
                        }, 100);
                    }
                },
                error: function (xhr) { // if error occured
                    alert("<?php echo $this->lang->line('error_occurred_please_try_again'); ?>");
                    $this.button('reset');
                    $('#examfade,#exammodal').css({'display': 'none'});
                },
                complete: function () {
                    $this.button('reset');
                    $('#examfade,#exammodal').css({'display': 'none'});
                }
            });

            return false;
        });
    </script>
    <script type="text/javascript">
        $.validator.addMethod("uniqueUserName", function (value, element, options)
        {
            var max_mark = $('#max_mark').val();
            //we need the validation error to appear on the correct element
            return parseFloat(value) <= parseFloat(max_mark);
        },
                "Invalid Marks"
                );

        $(document).ready(function () {

            $(document).on('submit', 'form#assign_form1111', function (event) {
                event.preventDefault();
                $('form#assign_form1111').validate({
        debug: true,
        errorClass: 'error text text-danger',
        validClass: 'success',
        errorElement: 'span',
        highlight: function(element, errorClass, validClass) {
        $(element).parent().addClass(errorClass);
        },
        unhighlight: function(element, errorClass, validClass) {
        $(element).parent().removeClass(errorClass);
        }
    });

                $('.marksssss').each(function () {
                    $(this).rules("add",
                            {
                                required: true,
                                uniqueUserName: true,
                                messages: {
                                    required: '<?php echo $this->lang->line("required"); ?>',//"Required",
                                }
                            });
                });

                // test if form is valid
                if ($('form#assign_form1111').validate().form()) {
                    var $this = $('.allot-fees');
                    $.ajax({
                        type: "POST",
                        dataType: 'Json',
                        url: $("#assign_form1111").attr('action'),
                        data: $("#assign_form1111").serialize(), // serializes the form's elements.
                        beforeSend: function () {
                            $this.button('loading');

                        },
                        success: function (data)
                        {
                            $this.button('reset');
                            if (data.status == "fail") {
                                var message = "";
                                $.each(data.error, function (index, value) {

                                    message += value;
                                });
                                errorMsg(message);
                            } else {
                                successMsg(data.message);
                                $('#subjectModal').modal('hide');
                            }
                        },
                        complete: function () {
                            $this.button('reset');
                        }
                    });
                } else {
                    console.log("does not validate");
                }
            })

            // // initialize the validator

        });
    </script>
    <script type="text/javascript">
        $(document).on('click', '.assignStudent', function () {
            var examid = $(this).data('examid');
            $('.exam_group_class_batch_exam_id').val(examid);
            $('#allotStudentModal').modal('show');
        });

        $("form#allotStudentForm").on('submit', (function (e) {
            e.preventDefault(); // avoid to execute the actual submit of the form.
            var form = $(this);
            var $this = form.find("button[type=submit]:focus");
            var url = form.attr('action');
            $.ajax({
                url: url,
                type: "POST",
                data: new FormData(this),
                dataType: 'json',
                contentType: false,
                cache: false,
                processData: false,
                beforeSend: function () {
                    $this.button('loading');
                },
                success: function (res)
                {

                if (res.status == 1) {
                $('.studentAllotForm').html(res.page);

                } else {
                    var message = "";
                    $.each(res.error, function (index, value) {

                    message += value;
                });

                errorMsg(message);

                }

                },
                error: function (xhr) { // if error occured
                    alert("<?php echo $this->lang->line('error_occurred_please_try_again'); ?>");
                    $this.button('reset');
                },
                complete: function () {
                    $this.button('reset');
                }
            });
        }
        ));

        $(document).on('submit', 'form#allot_exam_student', function (e) {

            e.preventDefault(); // avoid to execute the actual submit of the form.
            var form = $(this);
            var $this = form.find("button[type=submit]:focus");
            var url = form.attr('action');
            $.ajax({
                url: url,
                type: "POST",
                data: new FormData(this),
                dataType: 'json',
                contentType: false,
                cache: false,
                processData: false,
                beforeSend: function () {
                    $this.button('loading');
                },
                success: function (res)
                {
                    if (res.status == 1) {
                        successMsg(res.message);
                        $('#allotStudentModal').modal('hide');

                    } else {
                        errorMsg(res.message);
                    }

                    $this.button('reset');
                },
                error: function (xhr) { // if error occured
                    alert("<?php echo $this->lang->line('error_occurred_please_try_again'); ?>");
                    $this.button('reset');
                },
                complete: function () {
                    $this.button('reset');
                }

            });
        }
        );

        // Per-exam Assign Subject Group modal
        $(document).on('click', '.assignSubjectGroupExamBtn', function () {
            var examId = $(this).data('examid');
            var examName = $(this).data('examname') || ('Exam ' + examId);
            $('#assignSubjectGroupModal .assign-sg-exam-name').text(examName);
            $('#assignSubjectGroupModal .assign-sg-modal-body').html('<div class="text-center p20"><i class="fa fa-spinner fa-spin fa-2x"></i></div>');
            $('#assignSubjectGroupModal').modal('show');
            $.ajax({
                url: '<?php echo site_url("admin/examgroup/assignSubjectGroupsExam"); ?>',
                type: 'POST',
                data: {
                    exam_id: examId,
                    '<?php echo $this->security->get_csrf_token_name(); ?>': '<?php echo $this->security->get_csrf_hash(); ?>'
                },
                dataType: 'json',
                success: function (res) {
                    if (res.status == 1 && res.page) {
                        $('#assignSubjectGroupModal .assign-sg-modal-body').html(res.page);
                    } else {
                        $('#assignSubjectGroupModal .assign-sg-modal-body').html(res.page || '<p class="text-danger">Failed to load.</p>');
                    }
                },
                error: function () {
                    $('#assignSubjectGroupModal .assign-sg-modal-body').html('<p class="text-danger"><?php echo $this->lang->line("error_occurred_please_try_again"); ?></p>');
                }
            });
        });
        $(document).on('change', '#assignSubjectGroupModal #assign_sg_select_all', function () {
            $('#assignSubjectGroupModal .assign-sg-cb').prop('checked', $(this).prop('checked'));
        });
        $(document).on('submit', '#assignSubjectGroupModal #assignSubjectGroupExamForm', function (e) {
            e.preventDefault();
            var $form = $(this);
            var $btn = $form.find('#assign_sg_submit_btn');
            $.ajax({
                url: $form.attr('action'),
                type: 'POST',
                data: $form.serialize(),
                dataType: 'json',
                beforeSend: function () { if ($btn.length) $btn.button('loading'); },
                success: function (res) {
                    if (res.status === 'success') {
                        successMsg(res.message);
                        $('#assignSubjectGroupModal').modal('hide');
                        if (typeof all_records === 'function') { all_records(); }
                    } else {
                        var msg = (res.error && typeof res.error === 'object') ? Object.values(res.error).join(' ') : (res.message || '');
                        errorMsg(msg || '<?php echo $this->lang->line("something_went_wrong"); ?>');
                    }
                },
                error: function () {
                    errorMsg('<?php echo $this->lang->line("error_occurred_please_try_again"); ?>');
                },
                complete: function () { if ($btn.length) $btn.button('reset'); }
            });
        });

        $(document).on('submit', 'form#remark_form', function (e) {

            e.preventDefault(); // avoid to execute the actual submit of the form.
            var form = $(this);
            var $this = form.find("button[type=submit]:focus");
            var url = form.attr('action');
            $.ajax({
                url: url,
                type: "POST",
                data: new FormData(this),
                dataType: 'json',
                contentType: false,
                cache: false,
                processData: false,
                beforeSend: function () {
                    $this.button('loading');
                },
                success: function (res)
                {
                    if (res.status == 1) {
                        successMsg(res.message);
                        $('#teacherRemarkModal').modal('hide');

                    } else {
                        errorMsg(res.message);
                    }

                    $this.button('reset');
                },
                error: function (xhr) { // if error occured
                    alert("<?php echo $this->lang->line('error_occurred_please_try_again'); ?>");
                    $this.button('reset');
                },
                complete: function () {
                    $this.button('reset');
                }

            });
        }
        );

        $('#allotStudentModal').on('hidden.bs.modal', function () {
            $('form#allotStudentForm').find('select#class_id').prop("selectedIndex", 0);
            $('form#allotStudentForm').find('select#section_id').find('option:not(:first)').remove();
            $('#allotStudentModal').find('div.studentAllotForm').html("");
            $("span[id$='_error']").html("");
        });

        $(document).on('click', '.select_all', function (e) {

            if (this.checked) {
                $(this).closest('div.table-responsive').find('[type=checkbox]').prop('checked', true);
            } else {
                $(this).closest('div.table-responsive').find('[type=checkbox]').prop('checked', false);
            }
        });

        $(document).ready(function () {
            $('body').tooltip({
                selector: "[data-toggle=tooltip]",
                container: "body"
            });
        });

        $(document).on('click', '.attendance_chk', function () {
            if ($(this).prop("checked") == true) {
                console.log("Checkbox is checked.");
                $(this).closest('tr').find('.marksssss').val("0");
                $(this).closest('tr').find('.marksssss').prop("disabled",true);

            } else if ($(this).prop("checked") == false) {
                $(this).closest('tr').find('.marksssss').val("");
                $(this).closest('tr').find('.marksssss').prop("disabled",false);
            }
        });
    </script>
    <script type="text/javascript">
        $(document).ready(function () {
            $(document).on('click', "#btnSubmit", function (event) {

                //stop submit the form, we will post it manually.
                event.preventDefault();
                var file_data = $('#my-file-selector').prop('files')[0];
                var form_data = new FormData();
                form_data.append('file', file_data);

                $.ajax({
                    url: base_url + "/admin/examgroup/uploadfile",
                    type: 'POST',
                    dataType: 'JSON',
                    data: form_data,
                contentType: false,
    cache: false,
    processData:false,
    beforeSend: function () {

                        $('#examfade,#exammodal').css({'display': 'block'});
                },
                    success: function (data) {
    $('#fileUploadForm')[0].reset();
    if (data.status == "0") {
            var message = "";
            $.each(data.error, function (index, value) {
                message += value;
            });
            errorMsg(message);
        } else {
            var arr = [];
                        $.each(data.student_marks, function (index) {
                            var s = JSON.parse(data.student_marks[index]);
                            arr.push({
                                adm_no: s.adm_no,
                                attendence: s.attendence,
                                marks: s.marks,
                                note: s.note
                            });

                        });
    //===============
                        $.each(arr, function (index, value) {
                            let adm_no_csv =value.adm_no;
                            var row=$('.marksEntryForm').find('table tbody').find('tr[class="std_adm_'+adm_no_csv+'"]');
                        row.find("td input.marksssss").val(value.marks);
                        row.find("td input.note").val(value.note);
                        if(value.attendence == 0){
                            row.find("td input.attendance_chk").prop( "checked", true );
                        }else{
                            row.find("td input.attendance_chk").prop( "checked", false);
                        }
                        });
                        successMsg("<?php echo $this->lang->line('csv_file_uploaded_successfully') ?>");
                        $(".dropify-clear").trigger('click');
    //=================
        }
                    },
                error: function (xhr) { // if error occured
                    alert("<?php echo $this->lang->line('error_occurred_please_try_again'); ?>");
                        $('#examfade,#exammodal').css({'display': 'none'});
                },
                complete: function () {

                    $('#fileUploadForm')[0].reset();
                    $('#examfade,#exammodal').css({'display': 'none'});
                }

                });
            });
        });
    </script>

    <script type="text/javascript">
            // Fill modal with content from link href
            // Remove any existing handlers first
            $("#studentRankModal").off("show.bs.modal");
            
            $("#studentRankModal").on("show.bs.modal", function(e) {
                console.log('=== GENERATE RANK MODAL OPENING ===');
                
                // Try to get data from relatedTarget (if opened via Bootstrap)
                var link = e.relatedTarget ? $(e.relatedTarget) : null;
                var exam_id = '';
                var exam_name = '';
                
                if (link && link.length > 0) {
                    // Get from button that triggered modal (Bootstrap way)
                    exam_id = link.attr('data-exam_id') || link.data('exam_id') || '';
                    exam_name = link.attr('data-exam-name') || link.data('exam-name') || link.data('examName') || '';
                    console.log('Got data from relatedTarget:', { exam_id: exam_id, exam_name: exam_name });
                }
                
                // If not found, check for stored data (manual open)
                if (!exam_id) {
                    exam_id = $('#studentRankModal').data('pending_exam_id') || '';
                    exam_name = $('#studentRankModal').data('pending_exam_name') || '';
                    console.log('Got data from stored modal data:', { exam_id: exam_id, exam_name: exam_name });
                }
                
                // Only set title if we have exam_name and it's not already set
                if (exam_name && !$('#studentRankModal .exam_title').html().trim()) {
                    $('#studentRankModal .exam_title').html(exam_name);
                }
                
                // Don't load data here - it's already handled in the click handler
                // This event is just for logging/debugging
                if (exam_id) {
                    console.log('Modal opening for exam_id:', exam_id);
                } else {
                    console.log('Modal opening but no exam_id found (will use stored data from click handler)');
                }
            });
            
            // Also listen for when modal is fully shown (backup - only if data not already loaded)
            $("#studentRankModal").on("shown.bs.modal", function() {
                console.log('Modal fully shown (shown.bs.modal event)');
                var pending_id = $('#studentRankModal').data('pending_exam_id');
                
                // Only load if there's pending data and modal body is empty (not already loaded)
                if (pending_id && $('#studentRankModal .modal-body .modal-body-inner').html().trim() === '') {
                    console.log('Found pending exam_id, loading data as backup:', pending_id);
                    var pending_name = $('#studentRankModal').data('pending_exam_name');
                    if (pending_name) {
                        $('#studentRankModal .exam_title').html(pending_name);
                    }
                    getRankData(pending_id);
                } else if (pending_id) {
                    console.log('Data already loaded, skipping duplicate load');
                }
                
                // Clean up stored data
                $('#studentRankModal').removeData('pending_exam_id');
                $('#studentRankModal').removeData('pending_exam_name');
            });
            
            // Handle Generate Rank button click - Use class selector for reliability
            $(document).off('click', '.generate-rank-btn'); // Remove any existing handlers
            $(document).on('click', '.generate-rank-btn', function(e) {
                e.preventDefault();
                e.stopPropagation();
                e.stopImmediatePropagation();
                
                console.log('=== GENERATE RANK BUTTON CLICKED ===');
                var $btn = $(this);
                
                // Get exam data from button attributes
                var exam_id = $btn.attr('data-exam_id') || $btn.data('exam_id');
                var exam_name = $btn.attr('data-exam-name') || $btn.data('exam-name') || $btn.data('examName');
                
                console.log('Button clicked - exam_id:', exam_id, 'exam_name:', exam_name);
                console.log('Button element:', $btn);
                
                if (!exam_id) {
                    console.error('ERROR: Could not extract exam_id from button');
                    console.error('Button attributes:', {
                        'data-exam_id': $btn.attr('data-exam_id'),
                        'data-exam_id (data)': $btn.data('exam_id'),
                        'all data': $btn.data()
                    });
                    alert("<?php echo $this->lang->line('error_occurred_please_try_again'); ?>");
                    return false;
                }
                
                // Store exam data in modal before opening (for show.bs.modal event)
                $('#studentRankModal').data('pending_exam_id', exam_id);
                $('#studentRankModal').data('pending_exam_name', exam_name);
                
                // Set exam title
                if (exam_name) {
                    $('#studentRankModal .exam_title').html(exam_name);
                } else {
                    $('#studentRankModal .exam_title').html('<?php echo $this->lang->line("student_exam_rank"); ?>');
                }
                
                // Clear previous content and show loader
                $('#studentRankModal .modal-body .modal-body-inner').html('');
                $('#studentRankModal .modal-body .modal_loader_div').css('display', 'block');
                
                // Manually open the modal
                console.log('Opening studentRankModal...');
                $('#studentRankModal').modal('show');
                
                // Load data after modal starts opening
                setTimeout(function() {
                    console.log('Loading rank data for exam_id:', exam_id);
                    getRankData(exam_id);
                }, 300);
                
                return false;
            });
            
            // Also keep the old selector as backup
            $(document).on('click', '[data-target="#studentRankModal"]', function(e) {
                // Only handle if it's not our new button class
                if (!$(this).hasClass('generate-rank-btn')) {
                    e.preventDefault();
                    e.stopPropagation();
                    console.log('Old selector clicked, redirecting to new handler');
                    $(this).addClass('generate-rank-btn').trigger('click');
                    return false;
                }
            });

    const getRankData = (exam_id)=>{
                if (!exam_id) {
                    console.error('No exam ID provided for rank generation');
                    alert("<?php echo $this->lang->line('error_occurred_please_try_again'); ?>");
                    return;
                }
                
                console.log('Loading rank data for exam ID:', exam_id);
                
                $.ajax({
                    url: '<?php echo site_url("admin/examresult/examrank"); ?>',
                    type: "POST",
                    data: {
                        "exam_id": exam_id,
                        '<?php echo $this->security->get_csrf_token_name(); ?>': '<?php echo $this->security->get_csrf_hash(); ?>'
                    },
                    dataType: 'json',                   
                    beforeSend: function () {
                        console.log('Rank data request sent');
                        $('#studentRankModal .modal-body .modal-body-inner').html(""); 
                        $('#studentRankModal .modal-body .modal_loader_div').css("display", "block"); 
                    },
                    success: function (data) {
                        console.log('Rank data response received:', data);
                        console.log('Response status:', data.status);
                        console.log('Response has page:', !!(data && data.page));
                        console.log('Page content length:', data && data.page ? data.page.length : 0);
                        
                        // Hide loader
                        $('#studentRankModal .modal-body .modal_loader_div').fadeOut(400);
                        
                        if (data && data.page) {
                            console.log('Setting modal content...');
                            $('#studentRankModal .modal-body .modal-body-inner').html(data.page);
                            
                            // Ensure modal is visible
                            if (!$('#studentRankModal').hasClass('in') && !$('#studentRankModal').hasClass('show')) {
                                console.log('Modal not visible, showing it...');
                                $('#studentRankModal').modal('show');
                            }
                            
                            // Force modal to be visible
                            $('#studentRankModal').css('display', 'block');
                            $('body').addClass('modal-open');
                            
                            // Check if content was actually set
                            var contentLength = $('#studentRankModal .modal-body .modal-body-inner').html().length;
                            console.log('Content set, length:', contentLength);
                            
                            if (contentLength === 0) {
                                console.error('Content was not set properly!');
                                $('#studentRankModal .modal-body .modal-body-inner').html('<div class="alert alert-warning">No data available for this exam.</div>');
                            }
                        } else {
                            console.error('Invalid response format:', data);
                            var errorMsg = '<?php echo $this->lang->line("error_occurred_please_try_again"); ?>';
                            if (data && data.message) {
                                errorMsg = data.message;
                            }
                            $('#studentRankModal .modal-body .modal-body-inner').html('<div class="alert alert-danger">' + errorMsg + '</div>');
                        }
                    },
                    error: function (xhr, status, error) {
                        console.error('Rank data AJAX error:', {
                            status: status,
                            error: error,
                            responseText: xhr.responseText,
                            statusCode: xhr.status
                        });
                        
                        $('#studentRankModal .modal-body .modal_loader_div').fadeOut(400);
                        var errorMsg = "<?php echo $this->lang->line('error_occurred_please_try_again'); ?>";
                        
                        try {
                            if (xhr.responseText) {
                                var errorData = JSON.parse(xhr.responseText);
                                if (errorData && errorData.message) {
                                    errorMsg = errorData.message;
                                }
                            }
                        } catch (e) {
                            // Use default error message
                        }
                        
                        $('#studentRankModal .modal-body .modal-body-inner').html('<div class="alert alert-danger">' + errorMsg + '</div>');
                    },
                    complete: function () {
                        console.log('Rank data request complete');
                    }
                });
            }

        $(document).on('submit', '.updaterank', function (e) {
            e.preventDefault();
            let form = $(this);
            let frm_submit_button = $(this).find(':submit');
            let formdata = form.serializeArray();
            // Add CSRF token (partial form has no CSRF field)
            var csrf_token_name = '<?php echo $this->security->get_csrf_token_name(); ?>';
            var csrf_token_value = '<?php echo $this->security->get_csrf_hash(); ?>';
            var csrf_exists = formdata.some(function (item) { return item.name === csrf_token_name; });
            if (!csrf_exists) {
                formdata.push({ name: csrf_token_name, value: csrf_token_value });
            }
            var exam_id = frm_submit_button.data('examId') || form.find('input[name="exam_group_class_batch_exam_id"]').val();
            $.ajax({
                type: "POST",
                url: form.attr('action'),
                data: $.param(formdata),
                dataType: "JSON", // serializes the form's elements.
                beforeSend: function () {
                    frm_submit_button.button('loading');
                },
                success: function (response)
                {
                    if (response.status == 0) {
                        var message = "";
                        $.each(response.error, function (index, value) {

                            message += value;
                        });
                        errorMsg(message);

                    } else {
                        
                        successMsg(response.message);
                        getRankData(exam_id);
                    
                    }
                },
                error: function (xhr) { // if error occured
                    alert("<?php echo $this->lang->line('error_occurred_please_try_again'); ?>");
                    frm_submit_button.button('reset');
                },
                complete: function () {
                    frm_submit_button.button('reset');
                }
            });
        });
    </script>
    <script type="text/javascript">
    /* ===== SUBJECT MARKS MODAL: Scoped Dropdowns with CSRF ===== */
    (function () {
    var base_url = (typeof base_url !== 'undefined') ? base_url : '<?php echo base_url(); ?>';
    function csrfPair() {
        return {
        '<?php echo $this->security->get_csrf_token_name(); ?>':
            '<?php echo $this->security->get_csrf_hash(); ?>'
        };
    }

    // On Enter Marks modal open → set session + load Section (Faculty) / Program / Class from exam's linked levels, or all sections
    $(document).on('shown.bs.modal', '#subjectModal', function () {
        var $m = $('#subjectModal');
        var current_session = $('#current_session').val();
        $m.find('#session_id').val(current_session);
        $m.find('#program_id').html('<option value=""><?php echo $this->lang->line("select"); ?></option>');
        $m.find('#class_id').html('<option value=""><?php echo $this->lang->line("select"); ?></option>');
        $m.find('#section_id').val('');
        $m.find('#program_id').val('');
        $m.find('#class_id').val('');

        var examId = $m.data('marksExamId');
        if (examId) {
            $m.find('#section_id').html('<option value="">Loading...</option>');
            $.ajax({
                url: base_url + 'admin/examgroup/getLevelsForExam',
                method: 'POST',
                data: { '<?php echo $this->security->get_csrf_token_name(); ?>': '<?php echo $this->security->get_csrf_hash(); ?>', exam_id: examId },
                dataType: 'json',
                success: function (res) {
                    if (res.status === 1 && res.sections && res.sections.length) {
                        var html = '<option value=""><?php echo $this->lang->line("select"); ?></option>';
                        $.each(res.sections, function (i, s) {
                            html += '<option value="' + s.id + '">' + (s.section || '') + '</option>';
                        });
                        $m.find('#section_id').html(html).val('');
                        window.marksLevelsForExam = res.levels || [];
                    } else {
                        var msgHtml = '<option value=""><?php echo $this->lang->line("select"); ?></option>';
                        if (res.message) {
                            var msg = (res.message.length > 60) ? res.message.substring(0, 60) + '…' : res.message;
                            msgHtml = '<option value="">' + msg.replace(/</g, '&lt;') + '</option>';
                        }
                        $m.find('#section_id').html(msgHtml).val('');
                        window.marksLevelsForExam = [];
                    }
                },
                error: function () {
                    $m.find('#section_id').html('<option value=""><?php echo $this->lang->line("select"); ?></option>').val('');
                    window.marksLevelsForExam = [];
                }
            });
        } else {
            $.ajax({
                url: '<?php echo base_url(); ?>sections/getAll',
                method: 'GET',
                dataType: 'json',
                success: function (res) {
                    var html = '<option value=""><?php echo $this->lang->line("select"); ?></option>';
                    if (res && res.length) {
                        $.each(res, function (i, s) {
                            html += '<option value="' + s.id + '">' + (s.section || '') + '</option>';
                        });
                    }
                    $m.find('#section_id').html(html).val('');
                    window.marksLevelsForExam = [];
                },
                error: function () {
                    $m.find('#section_id').html('<option value=""><?php echo $this->lang->line("select"); ?></option>').val('');
                    window.marksLevelsForExam = [];
                }
            });
        }
    });

    // Section (Faculty) → Program — use linked levels when available
    $(document).on('change', '#subjectModal #section_id', function () {
        var $m = $('#subjectModal');
        var section_id = $(this).val();
        $m.find('#program_id').html('<option value=""><?php echo $this->lang->line("select"); ?></option>');
        $m.find('#class_id').html('<option value=""><?php echo $this->lang->line("select"); ?></option>');
        if (!section_id) return;

        var levels = window.marksLevelsForExam;
        if (levels && levels.length) {
            var sid = parseInt(section_id, 10);
            var seen = {};
            var html = '<option value=""><?php echo $this->lang->line("select"); ?></option>';
            for (var i = 0; i < levels.length; i++) {
                if (levels[i].section_id !== sid) continue;
                var pid = levels[i].program_id || 0;
                var key = 'p' + pid;
                if (!seen[key]) {
                    seen[key] = true;
                    html += '<option value="' + pid + '">' + (levels[i].program_name || (pid ? '' : '—')) + '</option>';
                }
            }
            $m.find('#program_id').html(html);
            return;
        }

        $m.find('#program_id').html('<option value="">Loading...</option>');
        $.ajax({
            url: '<?php echo base_url(); ?>programs/getBySection',
            method: 'POST',
            data: $.extend({}, csrfPair(), { section_id: section_id }),
            dataType: 'json',
            success: function (resp) {
                var html = '<option value=""><?php echo $this->lang->line("select"); ?></option>';
                if (resp && resp.length) {
                    $.each(resp, function (i, p) {
                        html += '<option value="' + p.id + '">' + (p.title || '') + '</option>';
                    });
                } else {
                    html += '<option value="" disabled>No programs found</option>';
                }
                $m.find('#program_id').html(html);
            },
            error: function () {
                $m.find('#program_id').html('<option value=""><?php echo $this->lang->line("select"); ?></option>');
            }
        });
    });

    // Program → Class (Academic Level) — use linked levels when available
    $(document).on('change', '#subjectModal #program_id', function () {
        var $m = $('#subjectModal');
        var section_id = $m.find('#section_id').val();
        var program_id = $(this).val();
        $m.find('#class_id').html('<option value=""><?php echo $this->lang->line("select"); ?></option>');
        if (!section_id || !program_id) return;

        var levels = window.marksLevelsForExam;
        if (levels && levels.length) {
            var sid = parseInt(section_id, 10);
            var pid = parseInt(program_id, 10);
            var html = '<option value=""><?php echo $this->lang->line("select"); ?></option>';
            for (var j = 0; j < levels.length; j++) {
                if (levels[j].section_id !== sid || (levels[j].program_id || 0) !== pid) continue;
                html += '<option value="' + levels[j].class_id + '">' + (levels[j].class || '') + '</option>';
            }
            $m.find('#class_id').html(html);
            return;
        }

        $m.find('#class_id').html('<option value="">Loading...</option>');
        $.ajax({
            url: '<?php echo base_url(); ?>classes/getBySectionAndProgram',
            method: 'POST',
            data: $.extend({}, csrfPair(), { section_id: section_id, program_id: program_id }),
            dataType: 'json',
            success: function (resp) {
                var html = '<option value=""><?php echo $this->lang->line("select"); ?></option>';
                if (resp && resp.length) {
                    $.each(resp, function (i, c) {
                        html += '<option value="' + c.id + '">' + (c.degree ? (c.degree + ' - ') : '') + (c.class || '') + '</option>';
                    });
                } else {
                    html += '<option value="" disabled>No classes found</option>';
                }
                $m.find('#class_id').html(html);
            },
            error: function () {
                $m.find('#class_id').html('<option value=""><?php echo $this->lang->line("select"); ?></option>');
            }
        });
    });
    })();
    </script>