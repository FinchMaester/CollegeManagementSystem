<div class="content-wrapper">
    <section class="content-header">
        <h1><i class="fa fa-id-card"></i></h1>
    </section>
    <section class="content">
        <?php
        $can_view_library_card = $this->rbac->hasPrivilege('library_card', 'can_view') || $this->rbac->hasPrivilege('issue_return', 'can_view');
        $can_generate_library_card = $this->rbac->hasPrivilege('library_card', 'can_add') || $this->rbac->hasPrivilege('issue_return', 'can_add');
        ?>
        <?php if ($this->session->flashdata('msg')) { echo $this->session->flashdata('msg'); $this->session->unset_userdata('msg'); } ?>
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-search"></i> <?php echo $this->lang->line('select_criteria'); ?></h3>
                    </div>
                    <div class="box-body">
                        <div class="row">
                            <form action="<?php echo site_url('admin/generatelibrarycard/search'); ?>" method="post">
                                <?php echo $this->customlib->getCSRF(); ?>
                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label><?php echo $this->lang->line('member_type'); ?></label><small class="req"> *</small>
                                        <select id="member_type" name="member_type" class="form-control">
                                            <option value="all" <?php echo set_select('member_type', 'all', (set_value('member_type', isset($member_type) ? $member_type : 'all') == 'all')); ?>>All</option>
                                            <option value="student" <?php echo set_select('member_type', 'student', (set_value('member_type', isset($member_type) ? $member_type : '') == 'student')); ?>><?php echo $this->lang->line('student'); ?></option>
                                            <option value="teacher" <?php echo set_select('member_type', 'teacher', (set_value('member_type', isset($member_type) ? $member_type : '') == 'teacher')); ?>><?php echo $this->lang->line('teacher'); ?></option>
                                        </select>
                                        <span class="text-danger"><?php echo form_error('member_type'); ?></span>
                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label><?php echo $this->lang->line('id_card_template'); ?></label><small class="req"> *</small>
                                        <select id="id_card" name="id_card" class="form-control">
                                            <option value=""><?php echo $this->lang->line('select'); ?></option>
                                            <?php foreach ($idcardlist as $list) { ?>
                                                <option value="<?php echo $list->id; ?>" <?php echo set_select('id_card', $list->id, (set_value('id_card') == $list->id)); ?>><?php echo $list->title; ?></option>
                                            <?php } ?>
                                        </select>
                                        <span class="text-danger"><?php echo form_error('id_card'); ?></span>
                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label>Print Mode</label>
                                        <select id="print_mode" name="print_mode" class="form-control">
                                            <option value="card" <?php echo set_select('print_mode', 'card', (set_value('print_mode', isset($print_mode) ? $print_mode : 'card') == 'card')); ?>>Card Size (Template)</option>
                                            <option value="full" <?php echo set_select('print_mode', 'full', (set_value('print_mode', isset($print_mode) ? $print_mode : 'card') == 'full')); ?>>Full Size (A4 Friendly)</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-sm-12">
                                    <div class="form-group" style="margin-top:24px;">
                                        <button type="submit" class="btn btn-primary btn-sm pull-right"><i class="fa fa-search"></i> <?php echo $this->lang->line('search'); ?></button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <?php if (isset($resultlist)) { ?>
                        <div class="box-header ptbnull">
                            <h3 class="box-title titlefix"><i class="fa fa-users"></i> Library Members</h3>
                            <?php if ($can_generate_library_card) { ?>
                            <button class="btn btn-info btn-sm printSelected pull-right" type="button"><?php echo $this->lang->line('generate'); ?></button>
                            <?php } ?>
                        </div>
                        <div class="box-body table-responsive overflow-visible">
                            <table class="table table-striped table-bordered table-hover example">
                                <thead>
                                    <tr>
                                        <th><input type="checkbox" id="select_all" /></th>
                                        <th><?php echo $this->lang->line('member_id'); ?></th>
                                        <th><?php echo $this->lang->line('library_card_no'); ?></th>
                                        <th><?php echo $this->lang->line('name'); ?></th>
                                        <th><?php echo $this->lang->line('member_type'); ?></th>
                                        <th><?php echo $this->lang->line('admission_no'); ?>/<?php echo $this->lang->line('staff_id'); ?></th>
                                        <th><?php echo $this->lang->line('class'); ?></th>
                                        <th><?php echo $this->lang->line('phone'); ?></th>
                                        <th class="text-right noExport"><?php echo $this->lang->line('action'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($resultlist as $member) { ?>
                                        <?php $full_name = trim($member['firstname'] . ' ' . $member['middlename'] . ' ' . $member['lastname']); ?>
                                        <tr>
                                            <td class="text-center"><input type="checkbox" class="checkbox center-block" data-lib_member_id="<?php echo $member['lib_member_id']; ?>"></td>
                                            <td><?php echo $member['lib_member_id']; ?></td>
                                            <td><?php echo $member['library_card_no']; ?></td>
                                            <td><?php echo html_escape($full_name); ?></td>
                                            <td><?php echo $this->lang->line($member['member_type']); ?></td>
                                            <td><?php echo $member['member_type'] == 'student' ? $member['admission_no'] : $member['employee_id']; ?></td>
                                            <td><?php echo trim($member['class_name'] . (($member['section_name']) ? ' (' . $member['section_name'] . ')' : '')); ?></td>
                                            <td><?php echo $member['phone']; ?></td>
                                            <td class="text-right">
                                                <?php if ($can_view_library_card) { ?>
                                                <button type="button" class="btn btn-default btn-xs viewLibraryCard" data-lib_member_id="<?php echo $member['lib_member_id']; ?>" title="View Library Card">
                                                    <i class="fa fa-eye"></i>
                                                </button>
                                                <?php } ?>
                                                <?php if ($can_generate_library_card) { ?>
                                                <button type="button" class="btn btn-primary btn-xs generateSingle" data-lib_member_id="<?php echo $member['lib_member_id']; ?>" title="Generate Card">
                                                    <i class="fa fa-print"></i>
                                                </button>
                                                <?php } ?>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </div>
    </section>
</div>

<div class="modal fade" id="libraryCardPreviewModal" tabindex="-1" role="dialog" aria-labelledby="libraryCardPreviewLabel">
    <div class="modal-dialog modal-lg" role="document" style="width: 90%;">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="libraryCardPreviewLabel">Library Card Preview</h4>
            </div>
            <div class="modal-body" style="padding: 10px; background: #efefef;">
                <iframe id="libraryCardPreviewFrame" style="width: 100%; height: 62vh; border: 1px solid #ddd; background: #efefef;"></iframe>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo $this->lang->line('close'); ?></button>
                <?php if ($can_generate_library_card) { ?>
                <button type="button" class="btn btn-primary" id="printLibraryCardFromModal">
                    <i class="fa fa-print"></i> <?php echo $this->lang->line('print'); ?>
                </button>
                <?php } ?>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function () {
    $('#select_all').on('click', function () { $('.checkbox').prop('checked', this.checked); });
    $(document).on('click', '.checkbox', function () { $('#select_all').prop('checked', $('.checkbox:checked').length === $('.checkbox').length); });

    $(document).on('click', '.printSelected', function () {
        var selected = [];
        $('.checkbox:checked').each(function () {
            selected.push({lib_member_id: $(this).data('lib_member_id')});
        });
        if (selected.length === 0) {
            alert("<?php echo $this->lang->line('no_record_selected'); ?>");
            return false;
        }
        var idCard = $('#id_card').val();
        if (!idCard) {
            alert('Please select ID card template.');
            return false;
        }
        $.ajax({
            url: '<?php echo site_url("admin/generatelibrarycard/generatemultiple"); ?>',
            type: 'POST',
            dataType: 'json',
            data: {
                data: JSON.stringify(selected),
                id_card: idCard,
                member_type: $('#member_type').val(),
                print_mode: $('#print_mode').val(),
                '<?php echo $this->security->get_csrf_token_name(); ?>': '<?php echo $this->security->get_csrf_hash(); ?>'
            },
            success: function (response) {
                if (response.status == 1 && response.page) {
                    Popup(response.page);
                } else {
                    alert(response.message ? response.message : 'Error generating cards');
                }
            },
            error: function () {
                alert('Error generating cards');
            }
        });
    });

    $(document).on('click', '.generateSingle', function () {
        var memberId = $(this).data('lib_member_id');
        var idCard = $('#id_card').val();
        if (!idCard) {
            alert('Please select ID card template.');
            return false;
        }
        $.ajax({
            url: '<?php echo site_url("admin/generatelibrarycard/generatemultiple"); ?>',
            type: 'POST',
            dataType: 'json',
            data: {
                data: JSON.stringify([{lib_member_id: memberId}]),
                id_card: idCard,
                member_type: $('#member_type').val(),
                print_mode: $('#print_mode').val(),
                '<?php echo $this->security->get_csrf_token_name(); ?>': '<?php echo $this->security->get_csrf_hash(); ?>'
            },
            success: function (response) {
                if (response.status == 1 && response.page) {
                    Popup(response.page);
                } else {
                    alert(response.message ? response.message : 'Error generating card');
                }
            },
            error: function () {
                alert('Error generating card');
            }
        });
    });

    $(document).on('click', '.viewLibraryCard', function () {
        var memberId = $(this).data('lib_member_id');
        var idCard = $('#id_card').val();
        if (!idCard) {
            alert('Please select ID card template.');
            return false;
        }
        $.ajax({
            url: '<?php echo site_url("admin/generatelibrarycard/generatemultiple"); ?>',
            type: 'POST',
            dataType: 'json',
            data: {
                data: JSON.stringify([{lib_member_id: memberId}]),
                id_card: idCard,
                member_type: $('#member_type').val(),
                print_mode: $('#print_mode').val(),
                '<?php echo $this->security->get_csrf_token_name(); ?>': '<?php echo $this->security->get_csrf_hash(); ?>'
            },
            success: function (response) {
                if (response.status == 1 && response.page) {
                    var frame = document.getElementById('libraryCardPreviewFrame');
                    var frameDoc = frame.contentWindow ? frame.contentWindow.document : frame.contentDocument;
                    frameDoc.open();
                    frameDoc.write(response.page);
                    frameDoc.close();
                    $('#libraryCardPreviewModal').modal('show');
                } else {
                    alert(response.message ? response.message : 'Error loading library card preview');
                }
            },
            error: function () {
                alert('Error loading library card preview');
            }
        });
    });

    $('#printLibraryCardFromModal').on('click', function () {
        var frame = document.getElementById('libraryCardPreviewFrame');
        if (frame && frame.contentWindow) {
            frame.contentWindow.focus();
            frame.contentWindow.print();
        }
    });

    $('#libraryCardPreviewModal').on('hidden.bs.modal', function () {
        var frame = document.getElementById('libraryCardPreviewFrame');
        if (frame) {
            var frameDoc = frame.contentWindow ? frame.contentWindow.document : frame.contentDocument;
            frameDoc.open();
            frameDoc.write('');
            frameDoc.close();
        }
    });
});

function Popup(data) {
    var frame1 = $('<iframe />');
    frame1[0].name = "frame1";
    $("body").append(frame1);
    var frameDoc = frame1[0].contentWindow ? frame1[0].contentWindow : frame1[0].contentDocument.document ? frame1[0].contentDocument.document : frame1[0].contentDocument;
    frameDoc.document.open();
    frameDoc.document.write('<html><head><title></title></head><body>');
    frameDoc.document.write(data);
    frameDoc.document.write('</body></html>');
    frameDoc.document.close();
    setTimeout(function () {
        window.frames["frame1"].focus();
        window.frames["frame1"].print();
        frame1.remove();
    }, 500);
}
</script>
