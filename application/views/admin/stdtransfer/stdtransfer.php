<div class="content-wrapper" style="min-height: 946px;">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1><i class="fa fa-user-plus"></i> <?php echo $this->lang->line('student_information'); ?> <small><?php echo $this->lang->line('student_fees1'); ?></small> </h1>
    </section>

    <div class="box-tools pull-right">
    <a href="<?php echo site_url('admin/stdtransfer/history'); ?>" class="btn btn-warning btn-sm" style="margin-top:10px; margin-right:8px;">
        <i class="fa fa-history"></i> History / Revert
    </a>
    <a href="#" id="openResultsModalBtn" class="btn btn-info btn-sm" style="margin-top:10px; margin-right:15px;" data-toggle="modal" data-target="#studentResultsModal">
    <i class="fa fa-check-circle"></i> <?php echo $this->lang->line('mark_student_results'); ?>
</a>

    </div>

    <!-- Main content -->
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-search"></i> <?php echo $this->lang->line('select_criteria'); ?></h3>
                        <div class="box-tools pull-right">
                        </div>
                    </div>

                    <form id='form1' class="stdtransfer" action="<?php echo site_url('admin/stdtransfer/index') ?>"  method="post" accept-charset="utf-8">
                        <div class="box-body">
                            <?php echo $this->customlib->getCSRF(); ?>
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label><?php echo $this->lang->line('section'); ?></label><small class="req"> *</small>
                                        <select autofocus="" id="section_id" name="section_id" class="form-control">
                                            <option value=""><?php echo $this->lang->line('select'); ?></option>
                                        </select>
                                        <span class="text-danger"><?php echo form_error('section_id'); ?></span>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="exampleInputEmail1"><?php echo $this->lang->line('program'); ?></label><small class="req"> *</small>
                                        <select id="program_id" name="program_id" class="form-control">
                                            <option value=""><?php echo $this->lang->line('select'); ?></option>
                                        </select>
                                        <span class="text-danger"><?php echo form_error('program_id'); ?></span>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="exampleInputEmail1"><?php echo $this->lang->line('class'); ?></label><small class="req"> *</small>
                                        <select id="class_id" name="class_id" class="form-control">
                                            <option value=""><?php echo $this->lang->line('select'); ?></option>
                                        </select>
                                        <span class="text-danger" id="error_class_id"></span>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="exampleInputEmail1"><?php echo $this->lang->line('promote_in_session'); ?> </label><small class="req"> *</small>
                                        <select id="session_id" name="session_id" class="form-control" >
                                            <option value=""><?php echo $this->lang->line('select'); ?></option>
                                            <?php
                                            foreach ($sessionlist as $session) {
                                                ?>
                                                <option value="<?php echo $session['id'] ?>" <?php if (set_value('session_id') == $session['id']) echo "selected=selected"; ?>><?php echo $session['session'] ?></option>
                                                <?php
                                            }
                                            ?>
                                        </select>
                                        <span class="text-danger"><?php echo form_error('session_id'); ?></span>
                                    </div>
                                </div>
                            </div>

<!-- Promotion options - will be hidden for final class -->
<div id="promotion_options" style="<?php echo isset($is_graduation) && $is_graduation ? 'display:none;' : ''; ?>">
    <h4><?php echo $this->lang->line('promote_students_in_next_session'); ?></h4>
    <div class="row">
        <div class="col-md-4">
            <div class="form-group">
                <label for="exampleInputEmail1"><?php echo $this->lang->line('section'); ?></label><small class="req"> *</small>
                <!-- Made section dropdown readonly/disabled with locked styling -->
                <select id="section_promote_id" name="section_promote_id" class="form-control" readonly disabled style="background-color: #f5f5f5; border: 1px solid #ddd; color: #666;">
                    <option value=""><?php echo $this->lang->line('select'); ?></option>
                </select>
                <!-- Hidden field to store the actual value for form submission -->
                <input type="hidden" id="section_promote_id_hidden" name="section_promote_id" value="">
                <span class="text-danger"><?php echo form_error('section_promote_id'); ?></span>
                <small class="text-muted"><i class="fa fa-lock"></i> <?php echo $this->lang->line('locked_to_source_section'); ?></small>
            </div>
        </div>

        <div class="col-md-4">
            <div class="form-group">
                <label for="exampleInputEmail1"><?php echo $this->lang->line('program'); ?></label><small class="req"> *</small>
                <!-- Made program dropdown readonly/disabled with locked styling -->
                <select id="program_promote_id" name="program_promote_id" class="form-control" readonly disabled style="background-color: #f5f5f5; border: 1px solid #ddd; color: #666;">
                    <option value=""><?php echo $this->lang->line('select'); ?></option>
                </select>
                <!-- Hidden field to store the actual value for form submission -->
                <input type="hidden" id="program_promote_id_hidden" name="program_promote_id" value="">
                <span class="text-danger"><?php echo form_error('program_promote_id'); ?></span>
                <small class="text-muted"><i class="fa fa-lock"></i> <?php echo $this->lang->line('locked_to_source_program'); ?></small>
            </div>
        </div>

        <div class="col-md-4">
            <div class="form-group">
                <label for="exampleInputEmail1"><?php echo $this->lang->line('class'); ?></label><small class="req"> *</small>
                <select id="class_promote_id" name="class_promote_id" class="form-control" >
                    <option value=""><?php echo $this->lang->line('select'); ?></option>
                </select>
                <span class="text-danger"><?php echo form_error('class_promote_id'); ?></span>
            </div>
        </div>
    </div>
</div>

<!-- Graduation message - will be shown for final class -->
<div id="graduation_message" style="<?php echo isset($is_graduation) && $is_graduation ? '' : 'display:none;'; ?>">
    <div class="alert alert-info">
        <h4><i class="fa fa-graduation-cap"></i> <?php echo $this->lang->line('graduation_mode'); ?></h4>
        <p><?php echo $this->lang->line('graduation_message'); ?></p>
        <div id="graduation_details" class="text-muted small">
            <!-- Dynamic graduation criteria will be shown here -->
        </div>
    </div>
</div>

                        </div>
                        <div class="box-footer">
                            <button type="submit" id="search_btn" class="btn btn-primary btn-sm pull-right"><?php echo $this->lang->line('search'); ?></button>
                        </div>
                    </form>

                    <?php
                    if (isset($resultlist)) {
                        ?>
                        <div class="box-header ptbnull">
                            <h3 class="box-title"><i class="fa fa-list"></i><?php echo $this->lang->line('student_list'); ?>  </h3>
                            <div class="box-tools pull-right">
                            </div>
                        </div>

                        <div class="box-body">
                            <form action="#" method="post" accept-charset="utf-8" class="<?php echo isset($is_graduation) && $is_graduation ? 'graduate_form' : 'promote_form'; ?>">
                                <input type="hidden" class="class_post" name="class_post" value="<?php echo $class_post; ?>" >
                                <input type="hidden" class="section_post" name="section_post" value="<?php echo $section_post; ?>" >
                                <input type="hidden" class="program_post" name="program_post" value="<?php echo $program_post; ?>" >

                                <?php if (!isset($is_graduation) || !$is_graduation): ?>
                                    <input type="hidden" class="class_promoted_post" name="class_promote_id" value="<?php echo isset($class_promoted_post) ? $class_promoted_post : ''; ?>" >
                                    <input type="hidden" class="section_promoted_post" name="section_promote_id" value="<?php echo isset($section_promoted_post) ? $section_promoted_post : ''; ?>" >
                                    <input type="hidden" class="program_promoted_post" name="program_promote_id" value="<?php echo isset($program_promoted_post) ? $program_promoted_post : ''; ?>" >
                                    <input type="hidden" class="session_promoted_post" name="session_id" value="<?php echo isset($session_promoted_post) ? $session_promoted_post : ''; ?>" >
                                <?php endif; ?>

                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <tbody>
                                            <tr>
                                                <th><input type="checkbox" class="select-all-students"></th>
                                                <th><?php echo $this->lang->line('admission_no'); ?></th>
                                                <th><?php echo $this->lang->line('student_name'); ?></th>
                                                <th><?php echo $this->lang->line('father_name'); ?></th>
                                                <!-- <th><?php echo $this->lang->line('date_of_birth'); ?></th> -->
                                                <?php if (!isset($is_graduation) || !$is_graduation): ?>
                                                    <!-- <th class=""><?php echo $this->lang->line('current_result'); ?></th> -->
                                                    <th class=""><?php echo $this->lang->line('next_session_status'); ?></th>
                                                <?php else: ?>
                                                    <th class=""><?php echo $this->lang->line('graduation_status'); ?></th>
                                                <?php endif; ?>
                                            </tr>

                                            <?php
                                            if (empty($resultlist)) {
                                                ?>
                                                <tr>
                                                    <td colspan="13" class="text-danger text-center"><?php echo $this->lang->line('no_record_found'); ?></td>
                                                </tr>
                                                <?php
                                            } else {
                                                $count = 1;
                                                foreach ($resultlist as $student) {
                                                    // Check if student is inactive (already promoted)
                                                    $is_inactive = (isset($student['session_is_active']) && $student['session_is_active'] == 'no');
                                                    $row_class = $is_inactive ? 'inactive-student' : '';
                                                    $disabled = $is_inactive ? 'disabled' : '';
                                                    ?>
                                                    <input type="hidden" value="<?php echo $student['id']; ?>">
                                                    <tr class="<?php echo $row_class; ?>" <?php echo $is_inactive ? 'title="This student has been promoted/transferred and is inactive in current session"' : ''; ?>>
                                                        <td><input class="checkbox" name="student_list[]" type="checkbox" autocomplete="off" value="<?php echo $student['id']; ?>" <?php echo $disabled; ?>></td>
                                                        <td><?php echo $student['admission_no']; ?></td>
                                                        <td><?php echo $this->customlib->getFullName($student['firstname'],$student['middlename'],$student['lastname'],$sch_setting->middlename,$sch_setting->lastname); ?></td>
                                                        <td><?php echo $student['father_name']; ?></td>
                                                        <!-- <td><?php
                                                        if($student['dob'] != '0000-00-00' && $student['dob'] != ''){
                                                            echo date($this->customlib->getSchoolDateFormat(), $this->customlib->dateyyyymmddTodateformat($student['dob']));
                                                        }
                                                        ?></td> -->

                                                        <?php if (!isset($is_graduation) || !$is_graduation): ?>
                                                            <!-- Hidden field to maintain pass status by default -->
                                                            <input type="hidden" name="result_<?php echo $student['id']; ?>" value="pass">

                                                            <td>
                                                                <?php if ($is_inactive): ?>
                                                                    <span class="label label-info">Already Processed</span>
                                                                <?php else: ?>
                                                                    <div class="radio-inline">
                                                                        <label>
                                                                            <input type="radio" name="next_working_<?php echo $student['id']; ?>" checked="checked" value="countinue" <?php echo $disabled; ?>>
                                                                            <?php echo $this->lang->line('continue'); ?>
                                                                        </label>
                                                                    </div>
                                                                    <div class="radio-inline">
                                                                        <label>
                                                                            <input type="radio" name="next_working_<?php echo $student['id']; ?>" value="dropout" class="promote-action-radio" <?php echo $disabled; ?>>
                                                                            <?php echo $this->lang->line('dropout'); ?>
                                                                        </label>
                                                                    </div>
                                                                <?php endif; ?>
                                                            </td>
                                                        <?php else: ?>
                                                            <!-- Graduation option -->
                                                            <td>
                                                                <?php if ($is_inactive): ?>
                                                                    <span class="label label-info">Already Processed</span>
                                                                <?php else: ?>
                                                                    <div class="radio-inline">
                                                                        <label>
                                                                            <input type="radio" name="graduate_<?php echo $student['id']; ?>" checked="checked" value="1" <?php echo $disabled; ?>>
                                                                            <?php echo $this->lang->line('graduate'); ?>
                                                                        </label>
                                                                    </div>
                                                                <?php endif; ?>
                                                            </td>
                                                        <?php endif; ?>
                                                    </tr>
                                                    <?php
                                                }
                                                $count++;
                                            }?>
                                        </tbody>
                                    </table>
                                </div>
                            </form>
                        </div>

                        <div class="box-footer clearfix" >
                            <?php
                            if (!empty($resultlist)) {
                                if (isset($is_graduation) && $is_graduation) {
                                    ?>
                                    <a class="btn btn-sm btn-success pull-right" data-toggle="modal" data-target="#graduateStudentModal">
                                        <i class="fa fa-graduation-cap"></i> <?php echo $this->lang->line('graduate'); ?>
                                    </a>
                                    <?php
                                } else {
                                    ?>
                                    <!-- FIX: give href + id to bind a safe click handler -->
                                    <a href="#" id="openPromoteModalBtn" class="btn btn-sm btn-primary pull-right" data-toggle="modal" data-target="#pramoteStudentModal">
                                        <?php echo $this->lang->line('promote'); ?>
                                    </a>
                                    <?php
                                }
                            }
                            ?>
                        </div>
                    </div>
                </div>
                <?php } else { } ?>
    </section>
</div>

<!-- Promote confirmation modal -->
<div class="modal" id="pramoteStudentModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h4 class="modal-title"><?php echo $this->lang->line('promote_confirmation'); ?></h4>
            </div>
            <div class="modal-body">
                <p><?php echo $this->lang->line('are_you_sure_you_want_to_promote_confirm'); ?></p>
                <div id="promoteDropoutFields" style="display:none; margin-top:12px;">
                    <div class="alert alert-warning" style="margin-bottom:10px;">
                        One or more students are marked as <strong><?php echo $this->lang->line('dropout'); ?></strong>.
                        This disables the student locally and syncs HEMIS DropOut when linked.
                    </div>
                    <div class="form-group">
                        <label for="dropout_reason"><?php echo $this->lang->line('dropout_reason'); ?> <span class="req">*</span></label>
                        <input type="text" class="form-control" id="dropout_reason" name="dropout_reason" maxlength="255" placeholder="e.g. Left college / financial reasons">
                    </div>
                    <div class="form-group">
                        <label for="dropout_remarks"><?php echo $this->lang->line('remarks'); ?></label>
                        <textarea class="form-control" id="dropout_remarks" name="dropout_remarks" rows="2"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="dropout_date"><?php echo $this->lang->line('dropout_date'); ?></label>
                        <input type="date" class="form-control" id="dropout_date" name="dropout_date" value="<?php echo date('Y-m-d'); ?>">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo $this->lang->line('cancel'); ?></button>
                <button type="button" class="btn btn-primary pramote_student"><?php echo $this->lang->line('save'); ?></button>
            </div>
        </div>
    </div>
</div>

<!-- Graduate confirmation modal -->
<div class="modal" id="graduateStudentModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h4 class="modal-title"><i class="fa fa-graduation-cap"></i> <?php echo $this->lang->line('graduate_confirmation'); ?></h4>
            </div>
            <div class="modal-body">
                <p><?php echo $this->lang->line('are_you_sure_you_want_to_graduate_these_students'); ?></p>
                <p><small><?php echo $this->lang->line('this_action_marks_students_as_graduated_and_alumni'); ?></small></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">
                    <?php echo $this->lang->line('cancel'); ?>
                </button>
                <button type="button" class="btn btn-success graduate_students"><i class="fa fa-graduation-cap"></i> <?php echo $this->lang->line('graduate'); ?></button>
            </div>
        </div>
    </div>
</div>

<!-- Student Results Modal -->
<div class="modal fade" id="studentResultsModal" tabindex="-1" role="dialog" aria-labelledby="studentResultsModalLabel">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="studentResultsModalLabel">
                    <i class="fa fa-check-circle"></i> <?php echo $this->lang->line('mark_student_results'); ?>
                </h4>
            </div>
            <div class="modal-body">
                <!-- Search Form -->
                <form id="studentResultsForm" method="post">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label><?php echo $this->lang->line('section'); ?></label><small class="req"> *</small>
                                <select id="results_section_id" name="section_id" class="form-control" required>
                                    <option value=""><?php echo $this->lang->line('select'); ?></option>
                                </select>
                                <span class="text-danger" id="error_results_section_id"></span>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label><?php echo $this->lang->line('program'); ?></label><small class="req"> *</small>
                                <select id="results_program_id" name="program_id" class="form-control" required>
                                    <option value=""><?php echo $this->lang->line('select'); ?></option>
                                </select>
                                <span class="text-danger" id="error_results_program_id"></span>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label><?php echo $this->lang->line('class'); ?></label><small class="req"> *</small>
                                <select id="results_class_id" name="class_id" class="form-control" required>
                                    <option value=""><?php echo $this->lang->line('select'); ?></option>
                                </select>
                                <span class="text-danger" id="error_results_class_id"></span>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label><?php echo $this->lang->line('session'); ?></label><small class="req"> *</small>
                                <select id="results_session_id" name="session_id" class="form-control" required>
                                    <option value=""><?php echo $this->lang->line('select'); ?></option>
                                    <?php foreach ($sessionlist as $session) { ?>
                                        <option value="<?php echo $session['id']; ?>"><?php echo $session['session']; ?></option>
                                    <?php } ?>
                                </select>
                                <span class="text-danger" id="error_results_session_id"></span>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <button type="button" id="searchStudentResults" class="btn btn-primary">
                                <i class="fa fa-search"></i> <?php echo $this->lang->line('search'); ?>
                            </button>
                        </div>
                    </div>
                </form>

                <hr>

                <!-- Results Container -->
                <div id="studentResultsContainer" style="display: none;">
                    <h4><?php echo $this->lang->line('promoted_students_list'); ?></h4>
                    <div class="alert alert-info">
                        <i class="fa fa-info-circle"></i>
                        <?php echo $this->lang->line('mark_results_info'); ?>
                    </div>

                    <form id="saveResultsForm">
                        <input type="hidden" id="results_section_post" name="section_post" value="">
                        <input type="hidden" id="results_program_post" name="program_post" value="">
                        <input type="hidden" id="results_class_post" name="class_post" value="">
                        <input type="hidden" id="results_session_post" name="session_post" value="">

                        <div class="table-responsive">
                            <table class="table table-striped table-bordered" id="resultsTable">
                                <thead>
                                    <tr>
                                        <th><input type="checkbox" class="select-all-results"></th>
                                        <th><?php echo $this->lang->line('admission_no'); ?></th>
                                        <th><?php echo $this->lang->line('student_name'); ?></th>
                                        <th><?php echo $this->lang->line('father_name'); ?></th>
                                        <th><?php echo $this->lang->line('current_status'); ?></th>
                                        <th><?php echo $this->lang->line('mark_result'); ?></th>
                                    </tr>
                                </thead>
                                <tbody id="resultsTableBody">
                                    <!-- Dynamic content will be loaded here -->
                                </tbody>
                            </table>
                        </div>
                    </form>
                </div>

                <!-- Loading indicator -->
                <div id="resultsLoading" style="display: none; text-align: center; padding: 20px;">
                    <i class="fa fa-spinner fa-spin fa-2x"></i>
                    <p><?php echo $this->lang->line('loading_students'); ?></p>
                </div>

                <!-- No results message -->
                <div id="noResultsMessage" style="display: none;" class="alert alert-warning">
                    <i class="fa fa-exclamation-triangle"></i>
                    <?php echo $this->lang->line('no_promoted_students_found'); ?>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">
                    <?php echo $this->lang->line('close'); ?>
                </button>
                <button type="button" id="saveStudentResults" class="btn btn-success" style="display: none;">
                    <i class="fa fa-save"></i> <?php echo $this->lang->line('save_results'); ?>
                </button>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
$(document).on('submit', '.stdtransfer', function(e) {
    e.preventDefault();
    console.log('Form submitted with values:', $(this).serialize());
    document.getElementById('search_btn').disabled = true;
    this.submit();
});

// Store original values globally
var originalSectionId = '';
var originalProgramId = '';
var originalSectionText = '';
var originalProgramText = '';

// Initialize all sections on page load
$(document).ready(function () {

    // ===== FIX: robust modal recovery + explicit opener =====
    // if Bootstrap put modal into noConflict, keep a handle so we can call it safely.
    var hasBsModal = !!($.fn.modal);
    if ($.fn.modal && typeof $.fn.modal.noConflict === 'function') {
        // leave $.fn.modal as-is; also expose a backup as $.fn.bsModal
        try {
            $.fn.bsModal = $.fn.modal; // keep alias
        } catch(e){}
    }

    function openModalSafe(sel){
        try {
            if ($.fn.modal) { $(sel).modal('show'); return; }
            if ($.fn.bsModal) { $(sel).bsModal('show'); return; }
        } catch(e){ /* fall through */ }
        // last-resort fallback to show modal UI even if plugin unavailable
        $(sel).addClass('in').css('display','block');
        if (!$('.modal-backdrop').length) {
            $('<div class="modal-backdrop fade in"></div>').appendTo(document.body);
        }
        $('body').addClass('modal-open');
    }

    // Open Promote modal explicitly (bypasses data-toggle if another script interfered)
    $(document).on('click', '#openPromoteModalBtn', function(ev){
        ev.preventDefault();
        // Optional pre-check: at least one student selected
        if ($('.checkbox:checked').length === 0) {
            // still open the modal (your flow confirms inside), or show a tip:
            // errorMsg("Please select at least one student.");
            // return false;
        }
        openModalSafe('#pramoteStudentModal');
    });
    // ===== END FIX =====

    // Get pre-selected values from form data
    var preselected_section = '<?php echo set_value("section_id") ?>';
    var preselected_program = '<?php echo set_value("program_id") ?>';
    var preselected_class = '<?php echo set_value("class_id") ?>';

    console.log('Preselected values:', {
        section: preselected_section,
        program: preselected_program,
        class: preselected_class
    });

    // Fetch all sections (faculties)
    $.ajax({
        url: '<?php echo base_url(); ?>sections/getAll',
        method: 'GET',
        dataType: 'json',
        success: function (response) {
            var html = '<option value=""><?php echo $this->lang->line("select"); ?></option>';

            $.each(response, function (index, section) {
                var selected = (preselected_section == section.id) ? 'selected' : '';
                html += '<option value="' + section.id + '" ' + selected + '>' + section.section + '</option>';
            });

            $('#section_id').html(html);

            // If there's a pre-selected section, load programs immediately
            if(preselected_section) {
                originalSectionId = preselected_section;
                originalSectionText = $('#section_id option:selected').text();

                // Load programs for the preselected section
                loadProgramsForSection(preselected_section, preselected_program, preselected_class);
            }
        }
    });

    // Initialize promotion session dropdown - fetch current and future sessions only
    initializeSessionDropdown();
});

// Add new function to initialize session dropdown
function initializeSessionDropdown() {
    // Use the PHP-provided list
    var sessionlist = <?php echo isset($sessionlist) ? json_encode($sessionlist) : '[]'; ?>;

    var session_id = '<?php echo set_value("session_id") ?>';
    var html = '<option value=""><?php echo $this->lang->line("select"); ?></option>';

    // No year parsing, no filtering — render everything from DB
    if (sessionlist && sessionlist.length > 0) {
        $.each(sessionlist, function(index, session) {
            var selected = (session_id == session.id) ? 'selected' : '';
            html += '<option value="' + session.id + '" ' + selected + '>' + session.session + '</option>';
        });
        $('#session_id').html(html);
    } else {
        // Fallback stays intact
        loadSessionsFallback();
    }
}

// New function to load programs for a specific section (used for initialization)
function loadProgramsForSection(section_id, preselected_program, preselected_class) {
    console.log('Loading programs for section:', section_id, 'with preselected program:', preselected_program);

    $('#program_id').html('<option value="">Loading...</option>');

    $.ajax({
        url: '<?php echo base_url(); ?>programs/getBySection',
        method: 'POST',
        data: {section_id: section_id},
        dataType: 'json',
        success: function (response) {
            var html = '<option value=""><?php echo $this->lang->line("select"); ?></option>';

            if(response.length > 0) {
                $.each(response, function (index, program) {
                    var selected = (preselected_program == program.id) ? 'selected' : '';
                    html += '<option value="' + program.id + '" ' + selected + '>' + program.title + '</option>';
                });
            } else {
                html += '<option value="" disabled>No programs found</option>';
            }

            $('#program_id').html(html);

            // If there's a preselected program, load classes for it
            if(preselected_program) {
                originalProgramId = preselected_program;
                originalProgramText = $('#program_id option:selected').text();
                updateLockedPromotionFields();

                // Load classes for the preselected program
                loadClassesForProgram(section_id, preselected_program, preselected_class);
            }
        },
        error: function(xhr, status, error) {
            console.error("Error fetching programs:", error);
            $('#program_id').html('<option value="">Error loading programs</option>');
        }
    });
}

// New function to load classes for a specific section and program (used for initialization)
function loadClassesForProgram(section_id, program_id, preselected_class) {
    console.log('Loading classes for section:', section_id, 'program:', program_id, 'with preselected class:', preselected_class);

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
            console.log("Classes response:", response);

            var html = '<option value=""><?php echo $this->lang->line("select"); ?></option>';

            if (response && response.length > 0) {
                $.each(response, function(index, classItem) {
                    var selected = (preselected_class == classItem.id) ? 'selected' : '';
                    var is_final = checkIfFinalClassLogic(classItem) ? 'data-is-final="true"' : '';
                    html += '<option value="' + classItem.id + '" ' + selected + ' ' + is_final +
                           ' data-degree="' + (classItem.degree || '') +
                           '" data-class="' + (classItem.class || '') +
                           '" data-duration="' + (classItem.duration || '') + '">' +
                           (classItem.degree ? classItem.degree + ' - ' : '') +
                           classItem.class + '</option>';
                });
            } else {
                html += '<option value="" disabled>No classes found for this combination</option>';
            }

            $('#class_id').html(html);

            // Load promotion classes and check graduation mode
            loadPromotionClasses();

            // Check if selected class is final class
            if (preselected_class) {
                toggleGraduationMode(checkIfFinalClass(preselected_class));
            }
        },
        error: function(xhr, status, error) {
            console.error("AJAX Error:", status, error);
            $('#class_id').html('<option value="">Error loading classes</option>');
        }
    });
}

function loadSessionsFallback() {
    $.ajax({
        url: '<?php echo base_url(); ?>sessions',
        method: 'GET',
        dataType: 'html',
        success: function(response) {
            var html = '<option value=""><?php echo $this->lang->line("select"); ?></option>';
            html += '<option value="" disabled>Please contact administrator</option>';
            $('#session_id').html(html);
            console.log("Fallback loaded - sessions need manual configuration");
        },
        error: function() {
            var html = '<option value=""><?php echo $this->lang->line("select"); ?></option>';
            html += '<option value="" disabled>Error loading sessions - contact admin</option>';
            $('#session_id').html(html);
        }
    });
}

// When section changes
$(document).on('change', '#section_id', function (e) {
    var section_id = $(this).val();

    $('#program_id').html('<option value=""><?php echo $this->lang->line("select"); ?></option>');
    $('#class_id').html('<option value=""><?php echo $this->lang->line("select"); ?></option>');

    if (section_id != '') {
        $('#program_id').html('<option value="">Loading...</option>');

        originalSectionId = section_id;
        originalSectionText = $('#section_id option:selected').text();
        updateLockedPromotionFields();

        $.ajax({
            url: '<?php echo base_url(); ?>programs/getBySection',
            method: 'POST',
            data: {section_id: section_id},
            dataType: 'json',
            success: function (response) {
                var html = '<option value=""><?php echo $this->lang->line("select"); ?></option>';

                if(response.length > 0) {
                    $.each(response, function (index, program) {
                        html += '<option value="' + program.id + '">' + program.title + '</option>';
                    });
                } else {
                    html += '<option value="" disabled>No programs found</option>';
                }

                $('#program_id').html(html);
            },
            error: function(xhr, status, error) {
                console.error("Error fetching programs:", error);
                console.error("Response:", xhr.responseText);
                $('#program_id').html('<option value="">Error loading programs</option>');
            }
        });
    }
});

// When program changes
$(document).on('change', '#program_id', function (e) {
    var section_id = $('#section_id').val();
    var program_id = $(this).val();

    console.log("User changed program - Fetching classes for Section:", section_id, "Program:", program_id);

    if (program_id) {
        originalProgramId = program_id;
        originalProgramText = $('#program_id option:selected').text();
        updateLockedPromotionFields();
    }

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

            var html = '<option value=""><?php echo $this->lang->line("select"); ?></option>';

            if (response && response.length > 0) {
                $.each(response, function(index, classItem) {
                    var is_final = checkIfFinalClassLogic(classItem) ? 'data-is-final="true"' : '';
                    html += '<option value="' + classItem.id + '" ' + is_final +
                           ' data-degree="' + (classItem.degree || '') +
                           '" data-class="' + (classItem.class || '') +
                           '" data-duration="' + (classItem.duration || '') + '">' +
                           (classItem.degree ? classItem.degree + ' - ' : '') +
                           classItem.class + '</option>';
                });
            } else {
                html += '<option value="" disabled>No classes found for this combination</option>';
                console.warn("No classes found for Section:", section_id, "Program:", program_id);
            }

            $('#class_id').html(html);
            loadPromotionClasses();
        },
        error: function(xhr, status, error) {
            console.error("AJAX Error:", status, error);
            console.error("Response:", xhr.responseText);
            $('#class_id').html('<option value="">Error loading classes</option>');
        }
    });
});

// When class changes
$(document).on('change', '#class_id', function (e) {
    var class_id = $(this).val();

    if (class_id) {
        toggleGraduationMode(checkIfFinalClass(class_id));

        if (!originalProgramId) {
            storeOriginalValues();
        }

        loadPromotionClasses();
    }
});

function storeOriginalValues() {
    originalSectionId = $('#section_id').val();
    originalProgramId = $('#program_id').val();
    originalSectionText = $('#section_id option:selected').text();
    originalProgramText = $('#program_id option:selected').text();

    console.log('Original values stored:', {
        sectionId: originalSectionId,
        programId: originalProgramId,
        sectionText: originalSectionText,
        programText: originalProgramText
    });

    updateLockedPromotionFields();
}

function updateLockedPromotionFields() {
    if (originalSectionId && originalProgramId) {
        $('#section_promote_id').html('<option value="' + originalSectionId + '" selected>' + originalSectionText + '</option>');
        $('#section_promote_id_hidden').val(originalSectionId);

        $('#program_promote_id').html('<option value="' + originalProgramId + '" selected>' + originalProgramText + '</option>');
        $('#program_promote_id_hidden').val(originalProgramId);

        console.log('Locked fields updated with original values - Section:', originalSectionId, 'Program:', originalProgramId);
    }
}

// Load promotion classes
function loadPromotionClasses() {
    if (!originalSectionId || !originalProgramId) {
        console.log('Original values not set, cannot load promotion classes');
        return;
    }

    $('#class_promote_id').html('<option value="">Loading classes...</option>');

    $.ajax({
        url: '<?php echo base_url(); ?>classes/getBySectionAndProgram',
        method: 'POST',
        data: {
            section_id: originalSectionId,
            program_id: originalProgramId,
            <?php echo $this->security->get_csrf_token_name(); ?>: '<?php echo $this->security->get_csrf_hash(); ?>'
        },
        dataType: 'json',
        success: function(response) {
            console.log("Promotion classes response:", response);

            var html = '<option value=""><?php echo $this->lang->line("select"); ?></option>';

            if (response && response.length > 0) {
                $.each(response, function(index, classItem) {
                    var selected = ('<?php echo set_value("class_promote_id") ?>' == classItem.id) ? 'selected' : '';
                    var is_final = checkIfFinalClassLogic(classItem) ? 'data-is-final="true"' : '';
                    html += '<option value="' + classItem.id + '" ' + selected + ' ' + is_final +
                           ' data-degree="' + (classItem.degree || '') +
                           '" data-class="' + (classItem.class || '') +
                           '" data-duration="' + (classItem.duration || '') + '">' +
                           (classItem.degree ? classItem.degree + ' - ' : '') +
                           classItem.class + '</option>';
                });
            } else {
                html += '<option value="" disabled>No classes found for promotion</option>';
                console.warn("No promotion classes found for Section:", originalSectionId, "Program:", originalProgramId);
            }

            $('#class_promote_id').html(html);
        },
        error: function(xhr, status, error) {
            console.error("Error fetching promotion classes:", status, error);
            console.error("Response:", xhr.responseText);
            $('#class_promote_id').html('<option value="">Error loading classes</option>');
        }
    });
}

// Helper to detect final class
function checkIfFinalClassLogic(classItem) {
    if (!classItem) return false;

    var degree = (classItem.degree || '').toLowerCase();
    var className = (classItem.class || '').toLowerCase();

    if (className.indexOf('final year') !== -1) {
        return true;
    }

    function hasAny(needles) {
        for (var i = 0; i < needles.length; i++) {
            if (className.indexOf(needles[i]) !== -1) {
                return true;
            }
        }
        return false;
    }

    if (degree === 'bachelors' || degree === 'bachelor') {
        return hasAny([
            '4th year', 'fourth year', '4 year',
            '8th sem', 'eighth sem', '8th semester', 'eighth semester',
            '8 sem', 'sem 8', 'semester 8'
        ]);
    }

    if (degree === 'masters' || degree === 'master') {
        return hasAny([
            '2nd year', 'second year',
            '4th sem', 'fourth sem', '4th semester', 'fourth semester',
            '4 sem', 'sem 4', 'semester 4'
        ]);
    }

    return hasAny(['4th year', 'fourth year', 'final year', '8th semester', 'eighth semester']);
}

function checkIfFinalClass(class_id) {
    var $selectedOption = $("#class_id option[value='" + class_id + "']");

    if ($selectedOption.attr('data-is-final') === 'true') {
        return true;
    }

    return checkIfFinalClassLogic({
        degree: $selectedOption.attr('data-degree') || '',
        class: $selectedOption.attr('data-class') || $selectedOption.text() || '',
        duration: $selectedOption.attr('data-duration') || '0'
    });
}

function toggleGraduationMode(isFinalClass) {
    if (isFinalClass) {
        $("#promotion_options").hide();
        $("#graduation_message").show();
        $("#class_promote_id, #section_promote_id, #program_promote_id").removeAttr('required');
        $("#session_id").attr('required', 'required');
    } else {
        $("#promotion_options").show();
        $("#graduation_message").hide();
        $("#session_id, #class_promote_id, #section_promote_id, #program_promote_id").attr('required', 'required');
    }
}

// Promotion modal setup
$(document).ready(function() {
    $('#pramoteStudentModal').modal({
        backdrop: 'static',
        keyboard: false,
        show: false
    });

    function countPromoteDropouts() {
        return $('input[name^="next_working_"][value="dropout"]:checked, input[name^="next_working_"][value="leave"]:checked').length;
    }

    function refreshPromoteDropoutFields() {
        var n = countPromoteDropouts();
        if (n > 0) {
            $('#promoteDropoutFields').show();
        } else {
            $('#promoteDropoutFields').hide();
        }
    }

    $(document).on('change', 'input[name^="next_working_"]', refreshPromoteDropoutFields);
    $(document).on('click', '#openPromoteModalBtn, [data-target="#pramoteStudentModal"]', function () {
        setTimeout(refreshPromoteDropoutFields, 50);
    });

    // Promotion handler
    $('#pramoteStudentModal').on('click', '.pramote_student', function (e) {
        var $modalDiv = $(e.delegateTarget);
        var dropoutCount = countPromoteDropouts();
        if (dropoutCount > 0) {
            var reason = $.trim($('#dropout_reason').val() || '');
            if (reason === '') {
                errorMsg('<?php echo $this->lang->line('dropout_reason'); ?> is required for dropout students.');
                return false;
            }
        }

        var datastring = $(".promote_form").serialize();

        if (originalProgramId && originalSectionId) {
            datastring += '&program_promote_id=' + originalProgramId;
            datastring += '&section_promote_id=' + originalSectionId;
        }
        datastring += '&dropout_reason=' + encodeURIComponent($.trim($('#dropout_reason').val() || ''));
        datastring += '&dropout_remarks=' + encodeURIComponent($.trim($('#dropout_remarks').val() || ''));
        datastring += '&dropout_date=' + encodeURIComponent($.trim($('#dropout_date').val() || ''));

        $.ajax({
            type: "POST",
            url: '<?php echo site_url("admin/stdtransfer/promote") ?>',
            data: datastring,
            dataType: "JSON",
            beforeSend: function () {
                $modalDiv.addClass('modal_loading');
            },
            success: function (data) {
                if (data.status == "fail") {
                    var error_html = "";
                    $.each(data.msg, function (index, value) {
                        error_html += value;
                    });
                    errorMsg(error_html);
                } else {
                    var msg = "<?php echo $this->lang->line('students_are_successfully_promoted'); ?>";
                    var hemisErr = (data.hemis_sync && data.hemis_sync.error)
                        ? String(data.hemis_sync.error).trim()
                        : '';
                    var drop = (data.hemis_sync && data.hemis_sync.dropout) ? data.hemis_sync.dropout : null;
                    var dropErr = (drop && drop.error) ? String(drop.error).trim() : '';
                    if (hemisErr !== '') {
                        successMsg(msg + ' Local promote saved. HEMIS upgrade failed: ' + hemisErr);
                    } else if (dropErr !== '') {
                        successMsg(msg + ' Local dropout saved. HEMIS DropOut failed: ' + dropErr);
                    } else if (drop && drop.synced > 0) {
                        successMsg(msg + ' HEMIS DropOut synced for ' + drop.synced + ' student(s).');
                    } else if (data.hemis_sync && data.hemis_sync.synced) {
                        successMsg(msg + ' HEMIS StudentUpgrade synced.');
                    } else if (data.hemis_sync && data.hemis_sync.queued > 0 && data.hemis_sync.mode === 'disabled') {
                        successMsg(msg + ' (HEMIS upgrade sync disabled in settings.)');
                    } else if (data.hemis_sync && data.hemis_sync.skipped && data.hemis_sync.skipped.length
                        && !(data.hemis_sync.queued > 0)) {
                        successMsg(msg + ' (No students eligible for HEMIS upgrade — missing UGC id/program/batch.)');
                    } else {
                        successMsg(msg);
                    }
                    setTimeout(function () {
                        location.reload(true);
                    }, (hemisErr !== '' || dropErr !== '') ? 2500 : 400);
                }
                $('#pramoteStudentModal').modal('hide');
            },
            error: function (xhr) {
                console.error('Error during promotion:', xhr.responseText);
                errorMsg("<?php echo $this->lang->line('something_went_wrong'); ?>");
                $modalDiv.removeClass('modal_loading');
            },
            complete: function () {
                $modalDiv.removeClass('modal_loading');
            }
        });
    });

    // Graduation modal setup
    $('#graduateStudentModal').modal({
        backdrop: 'static',
        keyboard: false,
        show: false
    });

    // Graduation handler
    $('#graduateStudentModal').on('click', '.graduate_students', function (e) {
        var $modalDiv = $(e.delegateTarget);

        var session_id = $('#session_id').val();
        if (!session_id) {
            errorMsg("Please select a graduation session.");
            return false;
        }

        var selectedStudents = $('.checkbox:checked').length;
        if (selectedStudents === 0) {
            errorMsg("Please select at least one student to graduate.");
            return false;
        }

        var datastring = $(".graduate_form").serialize();

        if (session_id) {
            datastring += '&session_id=' + session_id;
        }

        var csrf_name = '<?php echo $this->security->get_csrf_token_name(); ?>';
        var csrf_hash = '<?php echo $this->security->get_csrf_hash(); ?>';
        if (csrf_name && csrf_hash) {
            datastring += '&' + csrf_name + '=' + csrf_hash;
        }

        console.log('Graduation data:', datastring);

        $.ajax({
            type: "POST",
            url: '<?php echo site_url("admin/stdtransfer/graduate") ?>',
            data: datastring,
            dataType: "JSON",
            beforeSend: function () {
                $modalDiv.addClass('modal_loading');
                $('.graduate_students').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Processing...');
            },
            success: function (data) {
                console.log('Graduation response:', data);
                if (data.status == "fail") {
                    var error_html = "";
                    $.each(data.msg, function (index, value) {
                        error_html += value + "<br>";
                    });
                    errorMsg(error_html);
                } else {
                    var msg = data.msg || "Students have been successfully graduated!";
                    var hs = data.hemis_sync || {};
                    if (hs.error) {
                        successMsg(msg + ' Local graduation saved. HEMIS failed: ' + String(hs.error));
                    } else if (hs.synced > 0) {
                        successMsg(msg + ' HEMIS Graduation synced for ' + hs.synced + ' student(s).');
                    } else if (hs.skipped && hs.skipped.length && !(hs.synced > 0)) {
                        successMsg(msg + ' (HEMIS skipped — missing hemis_student_id or sync disabled.)');
                    } else {
                        successMsg(msg);
                    }
                    setTimeout(function() {
                        location.reload(true);
                    }, hs.error ? 2500 : 1500);
                }
                $('#graduateStudentModal').modal('hide');
            },
            error: function (xhr, status, error) {
                console.error('Error during graduation:', xhr.responseText);
                console.error('Status:', status, 'Error:', error);
                errorMsg("Something went wrong during graduation. Please try again.");
            },
            complete: function () {
                $modalDiv.removeClass('modal_loading');
                $('.graduate_students').prop('disabled', false).html('<i class="fa fa-graduation-cap"></i> Graduate');
            }
        });
    });

    // Add validation before showing graduation modal
    $(document).on('click', '[data-target="#graduateStudentModal"]', function(e) {
        var session_id = $('#session_id').val();
        if (!session_id) {
            e.preventDefault();
            errorMsg("Please select a graduation session before proceeding.");
            return false;
        }

        var selectedStudents = $('.checkbox:checked').length;
        if (selectedStudents === 0) {
            e.preventDefault();
            errorMsg("Please select at least one student to graduate.");
            return false;
        }
        var sessionText = $('#session_id option:selected').text();
        var modalBody = $('#graduateStudentModal .modal-body');
        var sessionInfo = '<div class="alert alert-info"><strong>Graduation Session:</strong> ' + sessionText + '</div>';

        modalBody.find('.alert-info').remove();
        modalBody.prepend(sessionInfo);
    });

    // Select all students
    $(document).on('click', '.select-all-students', function () {
        $('.checkbox').prop('checked', this.checked);
    });

    // Ensure hidden fields have the original values
    $('form.promote_form').on('submit', function(e) {
        $('#section_promote_id_hidden').val(originalSectionId);
        $('#program_promote_id_hidden').val(originalProgramId);

        console.log('Form submission with original values - Section:', originalSectionId, 'Program:', originalProgramId);
    });

    <?php if(isset($is_graduation) && $is_graduation): ?>
    toggleGraduationMode(true);
    <?php endif; ?>
});
</script>

<script type="text/javascript">
$(document).ready(function() {
    // Initialize sections dropdown for results modal
    $('#studentResultsModal').on('shown.bs.modal', function() {
        loadResultsSections();
    });

    function loadResultsSections() {
        $.ajax({
            url: '<?php echo base_url(); ?>sections/getAll',
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                var html = '<option value=""><?php echo $this->lang->line("select"); ?></option>';
                $.each(response, function(index, section) {
                    html += '<option value="' + section.id + '">' + section.section + '</option>';
                });
                $('#results_section_id').html(html);
            },
            error: function() {
                $('#results_section_id').html('<option value="">Error loading sections</option>');
            }
        });
    }

    $(document).on('change', '#results_section_id', function() {
        var section_id = $(this).val();
        $('#results_program_id').html('<option value=""><?php echo $this->lang->line("select"); ?></option>');
        $('#results_class_id').html('<option value=""><?php echo $this->lang->line("select"); ?></option>');

        if (section_id != '') {
            $('#results_program_id').html('<option value="">Loading...</option>');

            $.ajax({
                url: '<?php echo base_url(); ?>programs/getBySection',
                method: 'POST',
                data: {section_id: section_id},
                dataType: 'json',
                success: function(response) {
                    var html = '<option value=""><?php echo $this->lang->line("select"); ?></option>';
                    if(response.length > 0) {
                        $.each(response, function(index, program) {
                            html += '<option value="' + program.id + '">' + program.title + '</option>';
                        });
                    } else {
                        html += '<option value="" disabled>No programs found</option>';
                    }
                    $('#results_program_id').html(html);
                },
                error: function() {
                    $('#results_program_id').html('<option value="">Error loading programs</option>');
                }
            });
        }
    });

    $(document).on('change', '#results_program_id', function() {
        var section_id = $('#results_section_id').val();
        var program_id = $(this).val();

        $('#results_class_id').html('<option value="">Loading...</option>');

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
                } else {
                    html += '<option value="" disabled>No classes found</option>';
                }
                $('#results_class_id').html(html);
            },
            error: function() {
                $('#results_class_id').html('<option value="">Error loading classes</option>');
            }
        });
    });

    $('#searchStudentResults').on('click', function() {
        var section_id = $('#results_section_id').val();
        var program_id = $('#results_program_id').val();
        var class_id = $('#results_class_id').val();
        var session_id = $('#results_session_id').val();

        if (!section_id || !program_id || !class_id || !session_id) {
            alert('<?php echo $this->lang->line("please_select_all_required_fields"); ?>');
            return;
        }

        $('#studentResultsContainer').hide();
        $('#noResultsMessage').hide();
        $('#saveStudentResults').hide();
        $('#resultsLoading').show();

        $.ajax({
            url: '<?php echo base_url(); ?>admin/stdtransfer/getPromotedStudentsForResults',
            method: 'POST',
            data: {
                section_id: section_id,
                program_id: program_id,
                class_id: class_id,
                session_id: session_id,
                <?php echo $this->security->get_csrf_token_name(); ?>: '<?php echo $this->security->get_csrf_hash(); ?>'
            },
            dataType: 'json',
            success: function(response) {
                $('#resultsLoading').hide();

                if (response.status === 'success' && response.data.length > 0) {
                    populateResultsTable(response.data);
                    $('#studentResultsContainer').show();
                    $('#saveStudentResults').show();

                    $('#results_section_post').val(section_id);
                    $('#results_program_post').val(program_id);
                    $('#results_class_post').val(class_id);
                    $('#results_session_post').val(session_id);
                } else {
                    $('#noResultsMessage').show();
                }
            },
            error: function() {
                $('#resultsLoading').hide();
                alert('<?php echo $this->lang->line("error_loading_students"); ?>');
            }
        });
    });

    function populateResultsTable(students) {
        var html = '';
        $.each(students, function(index, student) {
            var currentStatus = '';
            if (student.is_pass == '1') {
                currentStatus = '<span class="label label-success">Pass</span>';
            } else if (student.is_fail == '1') {
                currentStatus = '<span class="label label-danger">Fail</span>';
            } else {
                currentStatus = '<span class="label label-warning">Pending</span>';
            }

            var passChecked = (student.is_pass == '1') ? 'checked' : '';
            var failChecked = (student.is_fail == '1') ? 'checked' : '';

            html += '<tr>';
            html += '<td><input class="checkbox-result" name="student_list[]" type="checkbox" value="' + student.id + '"></td>';
            html += '<td>' + student.admission_no + '</td>';
            html += '<td>' + student.firstname + ' ' + (student.middlename || '') + ' ' + (student.lastname || '') + '</td>';
            html += '<td>' + (student.father_name || '') + '</td>';
            html += '<td>' + currentStatus + '</td>';
            html += '<td>';
            html += '<div class="radio-inline">';
            html += '<label><input type="radio" name="result_' + student.id + '" value="pass" ' + passChecked + '> <?php echo $this->lang->line("pass"); ?></label>';
            html += '</div>';
            html += '<div class="radio-inline">';
            html += '<label><input type="radio" name="result_' + student.id + '" value="fail" ' + failChecked + '> <?php echo $this->lang->line("fail"); ?></label>';
            html += '</div>';
            html += '</td>';
            html += '</tr>';
        });

        $('#resultsTableBody').html(html);
    }

    $(document).on('click', '.select-all-results', function() {
        $('.checkbox-result').prop('checked', this.checked);
    });

    $('#saveStudentResults').on('click', function() {
        var selectedStudents = $('.checkbox-result:checked').length;
        if (selectedStudents === 0) {
            alert('<?php echo $this->lang->line("please_select_at_least_one_student"); ?>');
            return;
        }

        var allMarked = true;
        $('.checkbox-result:checked').each(function() {
            var studentId = $(this).val();
            if (!$('input[name="result_' + studentId + '"]:checked').length) {
                allMarked = false;
                return false;
            }
        });

        if (!allMarked) {
            alert('<?php echo $this->lang->line("please_mark_results_for_all_selected_students"); ?>');
            return;
        }

        if (!confirm('<?php echo $this->lang->line("are_you_sure_save_results"); ?>')) {
            return;
        }

        var formData = $('#saveResultsForm').serialize();

        $.ajax({
            url: '<?php echo base_url(); ?>admin/stdtransfer/save_student_results',
            method: 'POST',
            data: formData,
            dataType: 'json',
            beforeSend: function() {
                $('#saveStudentResults').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Saving...');
            },
            success: function(response) {
                console.log('Save results response:', response);
                if (response.status == "fail") {
                    var error_html = "";
                    if (Array.isArray(response.msg) || typeof response.msg === 'object') {
                        $.each(response.msg, function (index, value) {
                            error_html += value + "<br>";
                        });
                    } else {
                        error_html = response.msg || "<?php echo $this->lang->line('something_went_wrong'); ?>";
                    }
                    errorMsg(error_html);
                } else {
                    successMsg(response.msg || "<?php echo $this->lang->line('student_results_saved_successfully'); ?>");
                    setTimeout(function() {
                        $('#searchStudentResults').click();
                        $('#studentResultsModal').modal('hide');
                    }, 1500);
                }
            },
            error: function() {
                alert('<?php echo $this->lang->line("error_saving_results"); ?>');
            },
            complete: function() {
                $('#saveStudentResults').prop('disabled', false).html('<i class="fa fa-save"></i> <?php echo $this->lang->line("save_results"); ?>');
            }
        });
    });
});
</script>

<style>
/* CSS for inactive/promoted students */
.inactive-student {
    opacity: 0.5;
    background-color: #f9f9f9 !important;
    color: #888 !important;
}
.inactive-student td { color: #888 !important; }
.inactive-student input[type="checkbox"], .inactive-student input[type="radio"] {
    cursor: not-allowed !important;
    opacity: 0.5 !important;
}
.inactive-student:hover { background-color: #f5f5f5 !important; }
.label-info {
    background-color: #5bc0de;
    color: white;
    padding: 2px 6px;
    border-radius: 3px;
    font-size: 11px;
    font-weight: normal;
}

/* FIX: keep any Nepali datepicker panel under Bootstrap modal/backdrop */
.nepali-date-picker, .nepali-date-picker * { z-index: 1020 !important; }
.ndp-nepali-calendar, .ndp-nepali-calendar * { z-index: 1020 !important; }
.ui-datepicker, .ui-datepicker * { z-index: 1020 !important; } /* safety for other pickers */
.modal { z-index: 1050; }
.modal-backdrop { z-index: 1040; }
</style>

<script>
$(document).ready(function() {
    // Prevent interaction with inactive students
    $('.inactive-student input[type="checkbox"]').on('click', function(e) {
        e.preventDefault();
        return false;
    });

    $('.inactive-student input[type="radio"]').on('click', function(e) {
        e.preventDefault();
        return false;
    });
});

$(document).on('click', '#openResultsModalBtn', function (e) {
    e.preventDefault();
    openModalSafe('#studentResultsModal');   // same helper used for Promote
});
</script>
