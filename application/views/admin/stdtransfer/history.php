<?php
$sch_setting = isset($sch_setting) ? $sch_setting : $this->setting_model->getSetting();
$action_type = isset($action_type) ? $action_type : 'promote';
$filters = isset($filters) ? $filters : array();
$resultlist = isset($resultlist) ? $resultlist : array();
$searched = !empty($searched);
?>
<div class="content-wrapper">
    <section class="content-header">
        <h1>
            <i class="fa fa-history"></i> Promote / Graduate History
            <small>Preview and revert</small>
        </h1>
    </section>

    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-search"></i> <?php echo $this->lang->line('select_criteria'); ?></h3>
                        <div class="box-tools pull-right">
                            <a href="<?php echo site_url('admin/stdtransfer/index'); ?>" class="btn btn-default btn-sm">
                                <i class="fa fa-arrow-left"></i> Back to Promote / Graduate
                            </a>
                        </div>
                    </div>

                    <form method="post" action="<?php echo site_url('admin/stdtransfer/history'); ?>" id="historyFilterForm">
                        <?php echo $this->customlib->getCSRF(); ?>
                        <div class="box-body">
                            <div class="alert alert-info">
                                This page shows inferred promote moves and graduation records from <code>student_session</code>.
                                Revert is guarded: type <strong>REVERT</strong> to confirm. Fee deposits are never deleted.
                                If HEMIS StudentUpgrade already synced for a promote, correct HEMIS manually after local revert.
                            </div>

                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Action type</label>
                                        <select name="action_type" id="action_type" class="form-control">
                                            <option value="promote" <?php echo $action_type === 'promote' ? 'selected' : ''; ?>>Promotion</option>
                                            <option value="graduate" <?php echo $action_type === 'graduate' ? 'selected' : ''; ?>>Graduation</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label><?php echo $this->lang->line('session'); ?></label>
                                        <select name="session_id" id="session_id" class="form-control">
                                            <option value=""><?php echo $this->lang->line('select'); ?></option>
                                            <?php if (!empty($sessionlist)) {
                                                foreach ($sessionlist as $session) { ?>
                                                    <option value="<?php echo (int) $session['id']; ?>" <?php echo (!empty($filters['session_id']) && (int) $filters['session_id'] === (int) $session['id']) ? 'selected' : ''; ?>>
                                                        <?php echo htmlspecialchars($session['session']); ?>
                                                    </option>
                                            <?php }
                                            } ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label><?php echo $this->lang->line('section'); ?></label>
                                        <select name="section_id" id="section_id" class="form-control">
                                            <option value=""><?php echo $this->lang->line('select'); ?></option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label><?php echo $this->lang->line('program'); ?></label>
                                        <select name="program_id" id="program_id" class="form-control">
                                            <option value=""><?php echo $this->lang->line('select'); ?></option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label><?php echo $this->lang->line('class'); ?> (destination / graduate class)</label>
                                        <select name="class_id" id="class_id" class="form-control">
                                            <option value=""><?php echo $this->lang->line('select'); ?></option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-5">
                                    <div class="form-group">
                                        <label>Search by admission no / name</label>
                                        <input type="text" name="search_text" class="form-control" value="<?php echo htmlspecialchars(isset($filters['search_text']) ? $filters['search_text'] : ''); ?>" placeholder="Optional keyword">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group" style="padding-top: 24px;">
                                        <button type="submit" class="btn btn-primary"><i class="fa fa-search"></i> Search</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                <?php if ($searched) { ?>
                <div class="box box-info">
                    <div class="box-header with-border">
                        <h3 class="box-title">
                            <?php echo $action_type === 'graduate' ? 'Graduation history' : 'Promotion history'; ?>
                            <small>(<?php echo count($resultlist); ?> record<?php echo count($resultlist) === 1 ? '' : 's'; ?>)</small>
                        </h3>
                        <?php if (!empty($resultlist)) { ?>
                        <div class="box-tools pull-right">
                            <span class="text-muted" id="bulkSelectedCount" style="margin-right:8px;">0 selected</span>
                            <button type="button" class="btn btn-danger btn-sm" id="bulkRevertBtn" disabled>
                                <i class="fa fa-undo"></i> Revert selected
                            </button>
                        </div>
                        <?php } ?>
                    </div>
                    <div class="box-body table-responsive">
                        <?php if (empty($resultlist)) { ?>
                            <div class="alert alert-warning">No matching records found for the selected filters.</div>
                        <?php } else { ?>
                        <table class="table table-striped table-bordered table-hover" id="historyTable">
                            <thead>
                                <tr>
                                    <th style="width:36px;">
                                        <input type="checkbox" id="select_all_history" title="Select all">
                                    </th>
                                    <th><?php echo $this->lang->line('admission_no'); ?></th>
                                    <th><?php echo $this->lang->line('student_name'); ?></th>
                                    <th>From</th>
                                    <th>To</th>
                                    <th>Updated</th>
                                    <th>Fee deposits</th>
                                    <th class="text-right"><?php echo $this->lang->line('action'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($resultlist as $row) {
                                    $full_name = $this->customlib->getFullName(
                                        $row['firstname'],
                                        $row['middlename'],
                                        $row['lastname'],
                                        $sch_setting->middlename,
                                        $sch_setting->lastname
                                    );
                                    $from_label = trim(
                                        (isset($row['from_class']) ? $row['from_class'] : '') . ' / ' .
                                        (isset($row['from_section']) ? $row['from_section'] : '') . ' / ' .
                                        (isset($row['from_program']) ? $row['from_program'] : '') . ' / ' .
                                        (isset($row['from_session']) ? $row['from_session'] : '')
                                    );
                                    $to_label = trim(
                                        (isset($row['to_class']) ? $row['to_class'] : '') . ' / ' .
                                        (isset($row['to_section']) ? $row['to_section'] : '') . ' / ' .
                                        (isset($row['to_program']) ? $row['to_program'] : '') . ' / ' .
                                        (isset($row['to_session']) ? $row['to_session'] : '')
                                    );
                                    if (empty($row['from_session_row_id'])) {
                                        $from_label = '—';
                                    }
                                    $fee_count = isset($row['fee_deposit_count']) ? (int) $row['fee_deposit_count'] : 0;
                                    $student_label = $row['admission_no'] . ' - ' . $full_name;
                                    ?>
                                    <tr>
                                        <td>
                                            <input type="checkbox"
                                                class="history-row-check"
                                                data-action-type="<?php echo htmlspecialchars($action_type); ?>"
                                                data-student-id="<?php echo (int) $row['student_id']; ?>"
                                                data-from-id="<?php echo (int) (isset($row['from_session_row_id']) ? $row['from_session_row_id'] : 0); ?>"
                                                data-to-id="<?php echo (int) $row['to_session_row_id']; ?>"
                                                data-fee-count="<?php echo $fee_count; ?>"
                                                data-label="<?php echo htmlspecialchars($student_label, ENT_QUOTES, 'UTF-8'); ?>">
                                        </td>
                                        <td>
                                            <a href="<?php echo site_url('student/view/' . (int) $row['student_id']); ?>">
                                                <?php echo htmlspecialchars($row['admission_no']); ?>
                                            </a>
                                        </td>
                                        <td><?php echo htmlspecialchars($full_name); ?></td>
                                        <td><?php echo htmlspecialchars($from_label); ?></td>
                                        <td>
                                            <?php echo htmlspecialchars($to_label); ?>
                                            <?php if ($action_type === 'graduate') { ?>
                                                <br><span class="label label-success">Graduated</span>
                                                <?php if (!empty($row['is_alumni'])) { ?>
                                                    <span class="label label-info">Alumni</span>
                                                <?php } ?>
                                            <?php } ?>
                                        </td>
                                        <td><?php echo !empty($row['to_updated_at']) ? htmlspecialchars($row['to_updated_at']) : '—'; ?></td>
                                        <td>
                                            <?php if ($fee_count > 0) { ?>
                                                <span class="label label-warning"><?php echo $fee_count; ?></span>
                                            <?php } else { ?>
                                                <span class="text-muted">0</span>
                                            <?php } ?>
                                        </td>
                                        <td class="text-right">
                                            <button type="button"
                                                class="btn btn-danger btn-xs js-open-revert"
                                                data-action-type="<?php echo htmlspecialchars($action_type); ?>"
                                                data-student-id="<?php echo (int) $row['student_id']; ?>"
                                                data-from-id="<?php echo (int) (isset($row['from_session_row_id']) ? $row['from_session_row_id'] : 0); ?>"
                                                data-to-id="<?php echo (int) $row['to_session_row_id']; ?>"
                                                data-fee-count="<?php echo $fee_count; ?>"
                                                data-student-label="<?php echo htmlspecialchars($student_label, ENT_QUOTES, 'UTF-8'); ?>"
                                                data-from-label="<?php echo htmlspecialchars($from_label, ENT_QUOTES, 'UTF-8'); ?>"
                                                data-to-label="<?php echo htmlspecialchars($to_label, ENT_QUOTES, 'UTF-8'); ?>">
                                                <i class="fa fa-undo"></i> Revert
                                            </button>
                                        </td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                        <?php } ?>
                    </div>
                </div>
                <?php } ?>
            </div>
        </div>
    </section>
</div>

<div class="modal fade" id="revertModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-danger">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title"><i class="fa fa-undo"></i> <span id="revertModalTitle">Confirm revert</span></h4>
            </div>
            <div class="modal-body">
                <div id="singleRevertSummary">
                    <p><strong id="revertStudentLabel"></strong></p>
                    <p>
                        <span class="text-muted">From:</span> <span id="revertFromLabel"></span><br>
                        <span class="text-muted">To:</span> <span id="revertToLabel"></span>
                    </p>
                </div>
                <div id="bulkRevertSummary" style="display:none;">
                    <p>You are about to revert <strong id="bulkCountLabel">0</strong> selected record(s).</p>
                    <ul id="bulkPreviewList" style="max-height:140px; overflow:auto; padding-left:18px;"></ul>
                </div>
                <div class="alert alert-warning" id="revertFeeWarning" style="display:none;">
                    One or more selected destination sessions have fee deposits. Revert will <strong>deactivate</strong> those rows and keep fee history.
                    Check the box below to allow that.
                </div>
                <div class="form-group" id="allowFeesWrap" style="display:none;">
                    <label>
                        <input type="checkbox" id="allow_with_fees" value="1">
                        Allow revert even with fee deposits (do not delete fees)
                    </label>
                </div>
                <div class="form-group">
                    <label>Type <code>REVERT</code> to confirm</label>
                    <input type="text" class="form-control" id="confirm_text" autocomplete="off" placeholder="REVERT">
                </div>
                <input type="hidden" id="revert_mode" value="single">
                <input type="hidden" id="revert_action_type" value="">
                <input type="hidden" id="revert_student_id" value="">
                <input type="hidden" id="revert_from_id" value="">
                <input type="hidden" id="revert_to_id" value="">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmRevertBtn">
                    <i class="fa fa-undo"></i> Revert now
                </button>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
(function () {
    var presetSection = '<?php echo (int) (isset($filters["section_id"]) ? $filters["section_id"] : 0); ?>';
    var presetProgram = '<?php echo (int) (isset($filters["program_id"]) ? $filters["program_id"] : 0); ?>';
    var presetClass = '<?php echo (int) (isset($filters["class_id"]) ? $filters["class_id"] : 0); ?>';

    function loadSections() {
        $.ajax({
            url: '<?php echo site_url("sections/getAll"); ?>',
            method: 'GET',
            dataType: 'json',
            success: function (response) {
                var html = '<option value=""><?php echo $this->lang->line("select"); ?></option>';
                $.each(response || [], function (i, section) {
                    var selected = (String(presetSection) === String(section.id)) ? 'selected' : '';
                    html += '<option value="' + section.id + '" ' + selected + '>' + section.section + '</option>';
                });
                $('#section_id').html(html);
                if (presetSection) {
                    $('#section_id').trigger('change');
                }
            }
        });
    }

    $(document).on('change', '#section_id', function () {
        var sectionId = $(this).val();
        $('#program_id').html('<option value=""><?php echo $this->lang->line("select"); ?></option>');
        $('#class_id').html('<option value=""><?php echo $this->lang->line("select"); ?></option>');
        if (!sectionId) return;
        $.ajax({
            url: '<?php echo site_url("programs/getBySection"); ?>',
            method: 'POST',
            data: { section_id: sectionId },
            dataType: 'json',
            success: function (response) {
                var html = '<option value=""><?php echo $this->lang->line("select"); ?></option>';
                $.each(response || [], function (i, program) {
                    var selected = (String(presetProgram) === String(program.id)) ? 'selected' : '';
                    html += '<option value="' + program.id + '" ' + selected + '>' + program.title + '</option>';
                });
                $('#program_id').html(html);
                if (presetProgram) {
                    $('#program_id').trigger('change');
                }
            }
        });
    });

    $(document).on('change', '#program_id', function () {
        var sectionId = $('#section_id').val();
        var programId = $(this).val();
        $('#class_id').html('<option value=""><?php echo $this->lang->line("select"); ?></option>');
        if (!sectionId || !programId) return;
        $.ajax({
            url: '<?php echo site_url("classes/getBySectionAndProgram"); ?>',
            method: 'POST',
            data: { section_id: sectionId, program_id: programId },
            dataType: 'json',
            success: function (response) {
                var html = '<option value=""><?php echo $this->lang->line("select"); ?></option>';
                var list = Array.isArray(response) ? response : (response && response.classlist ? response.classlist : []);
                $.each(list || [], function (i, classItem) {
                    var id = classItem.id || classItem.class_id;
                    var name = classItem.class || classItem.class_name || '';
                    var selected = (String(presetClass) === String(id)) ? 'selected' : '';
                    html += '<option value="' + id + '" ' + selected + '>' + name + '</option>';
                });
                $('#class_id').html(html);
            }
        });
    });

    function updateBulkSelectionUi() {
        var count = $('.history-row-check:checked').length;
        $('#bulkSelectedCount').text(count + ' selected');
        $('#bulkRevertBtn').prop('disabled', count < 1);
        var total = $('.history-row-check').length;
        $('#select_all_history').prop('checked', total > 0 && count === total);
    }

    function getSelectedBulkItems() {
        var items = [];
        var feeAny = 0;
        $('.history-row-check:checked').each(function () {
            var $c = $(this);
            var feeCount = parseInt($c.data('fee-count'), 10) || 0;
            if (feeCount > 0) feeAny += 1;
            items.push({
                student_id: parseInt($c.data('student-id'), 10) || 0,
                from_session_row_id: parseInt($c.data('from-id'), 10) || 0,
                to_session_row_id: parseInt($c.data('to-id'), 10) || 0,
                label: $c.data('label') || ''
            });
        });
        return { items: items, feeAny: feeAny };
    }

    $(document).on('change', '#select_all_history', function () {
        $('.history-row-check').prop('checked', $(this).prop('checked'));
        updateBulkSelectionUi();
    });

    $(document).on('change', '.history-row-check', function () {
        updateBulkSelectionUi();
    });

    $(document).on('click', '.js-open-revert', function () {
        var $btn = $(this);
        var feeCount = parseInt($btn.data('fee-count'), 10) || 0;
        $('#revert_mode').val('single');
        $('#revertModalTitle').text('Confirm revert');
        $('#singleRevertSummary').show();
        $('#bulkRevertSummary').hide();
        $('#revert_action_type').val($btn.data('action-type'));
        $('#revert_student_id').val($btn.data('student-id'));
        $('#revert_from_id').val($btn.data('from-id'));
        $('#revert_to_id').val($btn.data('to-id'));
        $('#revertStudentLabel').text($btn.data('student-label'));
        $('#revertFromLabel').text($btn.data('from-label'));
        $('#revertToLabel').text($btn.data('to-label'));
        $('#confirm_text').val('');
        $('#allow_with_fees').prop('checked', false);
        if (feeCount > 0) {
            $('#revertFeeWarning').show();
            $('#allowFeesWrap').show();
        } else {
            $('#revertFeeWarning').hide();
            $('#allowFeesWrap').hide();
        }
        $('#revertModal').modal('show');
    });

    $('#bulkRevertBtn').on('click', function () {
        var selected = getSelectedBulkItems();
        if (!selected.items.length) {
            errorMsg('Please select at least one record.');
            return;
        }
        $('#revert_mode').val('bulk');
        $('#revertModalTitle').text('Confirm bulk revert');
        $('#singleRevertSummary').hide();
        $('#bulkRevertSummary').show();
        $('#bulkCountLabel').text(selected.items.length);
        $('#revert_action_type').val($('.history-row-check:checked').first().data('action-type') || '<?php echo $action_type; ?>');
        var previewHtml = '';
        $.each(selected.items.slice(0, 12), function (i, item) {
            previewHtml += '<li>' + $('<div>').text(item.label).html() + '</li>';
        });
        if (selected.items.length > 12) {
            previewHtml += '<li>… and ' + (selected.items.length - 12) + ' more</li>';
        }
        $('#bulkPreviewList').html(previewHtml);
        $('#confirm_text').val('');
        $('#allow_with_fees').prop('checked', false);
        if (selected.feeAny > 0) {
            $('#revertFeeWarning').show();
            $('#allowFeesWrap').show();
        } else {
            $('#revertFeeWarning').hide();
            $('#allowFeesWrap').hide();
        }
        $('#revertModal').modal('show');
    });

    $('#confirmRevertBtn').on('click', function () {
        var $btn = $(this);
        var mode = $('#revert_mode').val();
        var allowFees = $('#allow_with_fees').is(':checked') ? '1' : '0';
        var confirmText = $('#confirm_text').val();

        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Reverting...');

        if (mode === 'bulk') {
            var selected = getSelectedBulkItems();
            $.ajax({
                url: '<?php echo site_url("admin/stdtransfer/revert_bulk"); ?>',
                method: 'POST',
                dataType: 'json',
                data: {
                    action_type: $('#revert_action_type').val(),
                    confirm_text: confirmText,
                    allow_with_fees: allowFees,
                    items: JSON.stringify(selected.items)
                },
                success: function (res) {
                    if (res && (res.status === 'success' || res.status === 'partial')) {
                        if (res.status === 'success') {
                            successMsg(res.msg || 'Bulk revert completed');
                        } else {
                            errorMsg(res.msg || 'Partial bulk revert');
                            if (res.errors && res.errors.length) {
                                console.warn('Bulk revert errors', res.errors);
                            }
                        }
                        $('#revertModal').modal('hide');
                        setTimeout(function () { window.location.reload(); }, 900);
                        return;
                    }
                    if (res && res.needs_fee_confirm) {
                        $('#revertFeeWarning').show();
                        $('#allowFeesWrap').show();
                    }
                    var msg = (res && res.msg) ? res.msg : 'Bulk revert failed';
                    if (res && res.errors && res.errors.length) {
                        msg += ' — ' + res.errors.slice(0, 3).join(' | ');
                    }
                    errorMsg(msg);
                },
                error: function () {
                    errorMsg('Bulk revert request failed');
                },
                complete: function () {
                    $btn.prop('disabled', false).html('<i class="fa fa-undo"></i> Revert now');
                }
            });
            return;
        }

        $.ajax({
            url: '<?php echo site_url("admin/stdtransfer/revert"); ?>',
            method: 'POST',
            dataType: 'json',
            data: {
                action_type: $('#revert_action_type').val(),
                student_id: $('#revert_student_id').val(),
                from_session_row_id: $('#revert_from_id').val(),
                to_session_row_id: $('#revert_to_id').val(),
                confirm_text: confirmText,
                allow_with_fees: allowFees
            },
            success: function (res) {
                if (res && res.status === 'success') {
                    successMsg(res.msg || 'Reverted');
                    $('#revertModal').modal('hide');
                    setTimeout(function () { window.location.reload(); }, 700);
                    return;
                }
                if (res && res.needs_fee_confirm) {
                    $('#revertFeeWarning').show();
                    $('#allowFeesWrap').show();
                }
                errorMsg((res && res.msg) ? res.msg : 'Revert failed');
            },
            error: function () {
                errorMsg('Revert request failed');
            },
            complete: function () {
                $btn.prop('disabled', false).html('<i class="fa fa-undo"></i> Revert now');
            }
        });
    });

    updateBulkSelectionUi();
    loadSections();
})();
</script>
