<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$this->load->helper('marksheet_layout');
$ms = isset($marksheet) ? $marksheet : null;
$sd_state = marksheet_student_detail_default_layout();
if ($ms && !empty($ms->student_detail_layout_json)) {
    $sd_state = marksheet_parse_student_detail_json($ms->student_detail_layout_json);
}
$sd_state_json = json_encode($sd_state);
$sd_default_json = json_encode(marksheet_student_detail_default_layout());
?>
<div class="form-group" style="border-top:1px solid #eee;padding-top:12px;margin-top:12px;">
    <label><?php echo $this->lang->line('marksheet_student_detail_title'); ?></label>
    <p class="help-block text-muted small"><?php echo $this->lang->line('marksheet_student_detail_help'); ?></p>
    <div class="row">
        <div class="col-sm-6">
            <div class="text-muted small" style="margin-bottom:6px;"><strong><?php echo $this->lang->line('marksheet_student_detail_left'); ?></strong></div>
            <ul id="ms_sd_sort_left" class="list-group" style="min-height:48px;margin-bottom:8px;"></ul>
        </div>
        <div class="col-sm-6">
            <div class="text-muted small" style="margin-bottom:6px;"><strong><?php echo $this->lang->line('marksheet_student_detail_right'); ?></strong></div>
            <ul id="ms_sd_sort_right" class="list-group" style="min-height:48px;margin-bottom:8px;"></ul>
        </div>
    </div>
    <div class="btn-group btn-group-xs" style="margin-bottom:8px;">
        <button type="button" class="btn btn-default" id="ms_sd_reset"><?php echo $this->lang->line('marksheet_student_detail_reset'); ?></button>
    </div>
    <div class="form-inline" style="margin-bottom:8px;">
        <select id="ms_sd_add_select" class="form-control input-sm" style="width:200px;display:inline-block;">
            <option value=""><?php echo $this->lang->line('marksheet_student_detail_add_field'); ?></option>
        </select>
        <button type="button" class="btn btn-default btn-sm" id="ms_sd_add_btn"><?php echo $this->lang->line('add'); ?></button>
        <span class="text-muted small" style="margin-left:8px;"><?php echo $this->lang->line('marksheet_student_detail_add_hint'); ?></span>
    </div>
    <input type="hidden" name="student_detail_layout_json" id="student_detail_layout_json" value="">
</div>
<script>
(function () {
    var SD_POOL = [
        { id: 'admission_no', label: 'Admission No' },
        { id: 'faculty', label: 'Faculty' },
        { id: 'roll_no', label: 'Roll No.' },
        { id: 'father_name', label: 'Father / Husband Name' },
        { id: 'dob', label: 'Date Of Birth' },
        { id: 'symbol_no', label: 'Symbol Number' },
        { id: 'student_name', label: 'Name' },
        { id: 'mother_name', label: 'Mother Name' },
        { id: 'class_section', label: 'Academic Level' },
        { id: 'program', label: 'Program' },
        { id: 'address', label: 'Address' },
        { id: 'gender', label: 'Gender' }
    ];

    var sdDefault = <?php echo $sd_default_json; ?>;
    var sdState = <?php echo $sd_state_json; ?>;
    var $hid = $('#student_detail_layout_json');
    var $left = $('#ms_sd_sort_left');
    var $right = $('#ms_sd_sort_right');
    var $addSel = $('#ms_sd_add_select');

    function esc(s) {
        return $('<div/>').text(s == null ? '' : String(s)).html();
    }
    function poolLabel(id) {
        for (var i = 0; i < SD_POOL.length; i++) {
            if (SD_POOL[i].id === id) return SD_POOL[i].label;
        }
        return id;
    }
    function rowHtml(it) {
        var vis = it.visible !== false;
        return '<li class="list-group-item ms-sd-item" style="cursor:default;padding:8px 10px;" data-field-id="' + esc(it.id) + '">' +
            '<span class="handle text-muted" style="cursor:move;margin-right:8px;"><i class="fa fa-arrows"></i></span>' +
            '<label class="checkbox-inline" style="margin-right:8px;"><input type="checkbox" class="ms-sd-vis" ' + (vis ? 'checked' : '') + '> ' + esc(it.label) + '</label>' +
            '<input type="text" class="form-control input-sm ms-sd-label" style="display:inline-block;width:160px;" />' +
            '<span class="text-muted small" style="margin-left:6px;">(' + esc(it.id) + ')</span>' +
            '<button type="button" class="btn btn-link btn-xs pull-right ms-sd-remove" title="Remove from layout">&times;</button>' +
            '</li>';
    }
    function fillRow($li, it) {
        $li.find('.ms-sd-label').val(it.label || it.id);
    }
    function collectSide($ul) {
        var out = [];
        $ul.find('.ms-sd-item').each(function () {
            var $li = $(this);
            var id = String($li.data('field-id'));
            out.push({
                id: id,
                label: $li.find('.ms-sd-label').val() || id,
                visible: $li.find('.ms-sd-vis').is(':checked')
            });
        });
        return out;
    }
    function syncHidden() {
        var payload = { left: collectSide($left), right: collectSide($right) };
        $hid.val(JSON.stringify(payload));
    }
    function refreshAddSelect() {
        var used = {};
        $left.add($right).find('.ms-sd-item').each(function () {
            used[String($(this).data('field-id'))] = true;
        });
        $addSel.empty().append($('<option/>').val('').text('<?php echo addslashes($this->lang->line('marksheet_student_detail_add_field')); ?>'));
        SD_POOL.forEach(function (p) {
            if (!used[p.id]) {
                $addSel.append($('<option/>').val(p.id).text(p.label + ' (' + p.id + ')'));
            }
        });
    }
    function renderFromState() {
        $left.empty();
        $right.empty();
        (sdState.left || []).forEach(function (it) {
            var $li = $(rowHtml(it));
            fillRow($li, it);
            $left.append($li);
        });
        (sdState.right || []).forEach(function (it) {
            var $li = $(rowHtml(it));
            fillRow($li, it);
            $right.append($li);
        });
        refreshAddSelect();
        syncHidden();
    }
    $left.add($right).on('change', '.ms-sd-vis, .ms-sd-label', syncHidden);
    $left.add($right).on('click', '.ms-sd-remove', function () {
        $(this).closest('.ms-sd-item').remove();
        refreshAddSelect();
        syncHidden();
    });
    $('#ms_sd_reset').on('click', function () {
        sdState = JSON.parse(JSON.stringify(sdDefault));
        renderFromState();
    });
    $('#ms_sd_add_btn').on('click', function () {
        var id = $addSel.val();
        if (!id) return;
        var it = { id: id, label: poolLabel(id), visible: true };
        var $li = $(rowHtml(it));
        fillRow($li, it);
        $right.append($li);
        refreshAddSelect();
        syncHidden();
    });
    renderFromState();
    var g = 'ms_student_detail';
    if (typeof Sortable !== 'undefined') {
        Sortable.create(document.getElementById('ms_sd_sort_left'), {
            group: g, animation: 150, handle: '.handle',
            onEnd: function () { refreshAddSelect(); syncHidden(); }
        });
        Sortable.create(document.getElementById('ms_sd_sort_right'), {
            group: g, animation: 150, handle: '.handle',
            onEnd: function () { refreshAddSelect(); syncHidden(); }
        });
    }
    $('form#form1, form#editform').on('submit', function () { syncHidden(); });
})();
</script>
