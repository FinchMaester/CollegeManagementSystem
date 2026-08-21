<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$ms = isset($marksheet) ? $marksheet : null;
$mode = ($ms && isset($ms->marksheet_display_mode)) ? $ms->marksheet_display_mode : 'legacy';
$grading = ($ms && isset($ms->grading_system_mode)) ? $ms->grading_system_mode : 'inherit';
$ms_show_grading = !$ms || !isset($ms->marksheet_show_grading_details) || (int) $ms->marksheet_show_grading_details === 1;
$ms_show_att = !$ms || !isset($ms->marksheet_show_attendance_block) || (int) $ms->marksheet_show_attendance_block === 1;
$ms_show_nona = !$ms || !isset($ms->marksheet_show_nonacademic_block) || (int) $ms->marksheet_show_nonacademic_block === 1;
$col_init_json = 'null';
if ($ms && !empty($ms->column_layout_json)) {
    $decoded = json_decode($ms->column_layout_json, true);
    if (is_array($decoded) && count($decoded) > 0) {
        $col_init_json = json_encode($decoded);
    }
}
?>
<div class="form-group" style="border-top:1px solid #eee;padding-top:12px;margin-top:12px;">
    <label><?php echo $this->lang->line('marksheet_display_mode'); ?></label>
    <select name="marksheet_display_mode" id="marksheet_display_mode" class="form-control">
        <option value="legacy" <?php echo $mode === 'legacy' ? 'selected' : ''; ?>><?php echo $this->lang->line('marksheet_mode_legacy'); ?></option>
        <option value="dynamic" <?php echo $mode === 'dynamic' ? 'selected' : ''; ?>><?php echo $this->lang->line('marksheet_mode_dynamic'); ?></option>
    </select>
    <p class="help-block text-muted small"><?php echo $this->lang->line('marksheet_mode_help'); ?></p>
</div>
<div class="form-group">
    <label><?php echo $this->lang->line('grading_system_mode'); ?></label>
    <select name="grading_system_mode" id="grading_system_mode" class="form-control">
        <option value="inherit" <?php echo $grading === 'inherit' ? 'selected' : ''; ?>><?php echo $this->lang->line('grading_inherit_exam'); ?></option>
        <option value="basic_system" <?php echo $grading === 'basic_system' ? 'selected' : ''; ?>><?php echo $this->lang->line('grading_pass_fail'); ?></option>
        <option value="gpa" <?php echo $grading === 'gpa' ? 'selected' : ''; ?>><?php echo $this->lang->line('grading_gpa_columns'); ?></option>
        <option value="coll_grade_system" <?php echo $grading === 'coll_grade_system' ? 'selected' : ''; ?>><?php echo $this->lang->line('grading_college_points'); ?></option>
    </select>
</div>
<div class="form-group">
    <label><?php echo $this->lang->line('marksheet_extra_sections_label'); ?></label>
    <p class="help-block text-muted small"><?php echo $this->lang->line('marksheet_extra_sections_help'); ?></p>
    <div class="checkbox"><label><input type="checkbox" name="marksheet_show_grading_details" value="1" <?php echo $ms_show_grading ? 'checked' : ''; ?>> <?php echo $this->lang->line('marksheet_show_grading_details'); ?></label></div>
    <div class="checkbox"><label><input type="checkbox" name="marksheet_show_attendance_block" value="1" <?php echo $ms_show_att ? 'checked' : ''; ?>> <?php echo $this->lang->line('marksheet_show_attendance_block'); ?></label></div>
    <div class="checkbox"><label><input type="checkbox" name="marksheet_show_nonacademic_block" value="1" <?php echo $ms_show_nona ? 'checked' : ''; ?>> <?php echo $this->lang->line('marksheet_show_nonacademic_block'); ?></label></div>
</div>
<div id="marksheet_designer_wrap" style="<?php echo $mode === 'dynamic' ? '' : 'display:none;'; ?>">
    <label><?php echo $this->lang->line('marksheet_column_layout'); ?></label>
    <p class="text-muted small"><?php echo $this->lang->line('marksheet_column_layout_help'); ?></p>
    <div class="btn-group btn-group-xs" style="margin-bottom:8px;">
        <button type="button" class="btn btn-default" id="ms_preset_theory"><?php echo $this->lang->line('marksheet_preset_theory'); ?></button>
        <button type="button" class="btn btn-default" id="ms_preset_practical"><?php echo $this->lang->line('marksheet_preset_practical'); ?></button>
        <button type="button" class="btn btn-default" id="ms_preset_gpa"><?php echo $this->lang->line('marksheet_preset_gpa'); ?></button>
        <button type="button" class="btn btn-default" id="ms_preset_bbs"><?php echo $this->lang->line('marksheet_preset_bbs'); ?></button>
    </div>
    <ul id="marksheet_column_sortable" class="list-group" style="margin-bottom:8px;"></ul>
    <button type="button" class="btn btn-default btn-xs" id="ms_add_column"><i class="fa fa-plus"></i> <?php echo $this->lang->line('marksheet_add_custom_column'); ?></button>
    <input type="hidden" name="column_layout_json" id="column_layout_json" value="">
</div>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
<script>
(function () {
    var defaultCols = <?php echo isset($default_column_json) ? $default_column_json : '[]'; ?>;
    var initial = <?php echo $col_init_json; ?>;
    var cols = (initial && initial.length) ? initial : (defaultCols && defaultCols.length ? defaultCols.slice() : []);
    var $list = $('#marksheet_column_sortable');
    var $hidden = $('#column_layout_json');
    var $mode = $('#marksheet_display_mode');
    var $wrap = $('#marksheet_designer_wrap');

    function preset(name) {
        if (name === 'practical') {
            return [
                {id:'subject_name',label:'Subject',visible:true},
                {id:'full_marks',label:'Full Marks',visible:true},
                {id:'pass_marks',label:'Pass Marks',visible:true},
                {id:'viva_marks',label:'Viva',visible:true},
                {id:'project_marks',label:'Project',visible:true},
                {id:'obtained_marks',label:'Total Obtained',visible:true},
                {id:'grade_letter',label:'Grade',visible:true},
                {id:'remarks',label:'Remarks',visible:true}
            ];
        }
        if (name === 'gpa') {
            return [
                {id:'sn',label:'SN',visible:true},
                {id:'subject_name',label:'Subject',visible:true},
                {id:'credit_hours',label:'Credit Hrs',visible:true},
                {id:'grade_point',label:'Grade Point',visible:true},
                {id:'letter_grade',label:'Grade',visible:true}
            ];
        }
        if (name === 'bbs') {
            return [
                {id:'subject_name',label:'Subject',visible:true},
                {id:'full_marks',label:'Full Marks',visible:true},
                {id:'pass_marks',label:'Pass Marks',visible:true},
                {id:'obtained_marks',label:'Mark Obtained',visible:true}
            ];
        }
        return defaultCols.slice();
    }
    function syncHidden() {
        var out = [];
        $list.find('.ms-col-item').each(function () {
            var $li = $(this);
            out.push({
                id: String($li.data('col-id')),
                label: $li.find('.ms-label').val() || String($li.data('col-id')),
                visible: $li.find('.ms-vis').is(':checked')
            });
        });
        cols = out;
        $hidden.val(JSON.stringify(cols));
    }
    function render() {
        $list.empty();
        cols.forEach(function (c) {
            var vis = c.visible !== false;
            var safeId = $('<div/>').text(c.id).html();
            var safeLabel = $('<div/>').text(c.label || c.id).html();
            var row = $('<li class="list-group-item ms-col-item" style="cursor:move;padding:8px 10px;"></li>');
            row.attr('data-col-id', c.id);
            row.append('<span class="handle text-muted" style="margin-right:8px;"><i class="fa fa-arrows"></i></span>');
            row.append('<label class="checkbox-inline" style="margin-right:10px;"><input type="checkbox" class="ms-vis" '+(vis?'checked':'')+'> '+ safeLabel +'</label>');
            row.append('<input type="text" class="form-control input-sm ms-label" style="display:inline-block;width:160px;" />');
            row.find('.ms-label').val(c.label || c.id);
            row.append('<span class="text-muted small" style="margin-left:8px;">('+ safeId +')</span>');
            row.append('<button type="button" class="btn btn-link btn-xs pull-right ms-remove">&times;</button>');
            $list.append(row);
        });
        syncHidden();
    }
    $list.on('change', '.ms-vis, .ms-label', syncHidden);
    $list.on('click', '.ms-remove', function () {
        var id = $(this).closest('.ms-col-item').data('col-id');
        cols = cols.filter(function (x) { return x.id !== id; });
        render();
    });
    $('#ms_preset_theory').on('click', function () { cols = preset('theory'); render(); });
    $('#ms_preset_practical').on('click', function () { cols = preset('practical'); render(); });
    $('#ms_preset_gpa').on('click', function () { cols = preset('gpa'); render(); });
    $('#ms_preset_bbs').on('click', function () { cols = preset('bbs'); render(); });
    $('#ms_add_column').on('click', function () {
        cols.push({id: 'custom_' + Date.now(), label: 'Column', visible: true});
        render();
    });
    $mode.on('change', function () {
        $wrap.toggle($mode.val() === 'dynamic');
    });
    var el = document.getElementById('marksheet_column_sortable');
    if (el && typeof Sortable !== 'undefined') {
        Sortable.create(el, {
            handle: '.handle',
            animation: 150,
            onEnd: function () {
                syncHidden();
            }
        });
    }
    if (!cols.length) cols = defaultCols.slice();
    render();
    $('form#form1, form#editform').on('submit', function () { syncHidden(); });
})();
</script>
