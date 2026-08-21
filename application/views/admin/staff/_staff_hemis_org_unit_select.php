<?php
/**
 * UGC Department (teaching) or Section (non-teaching) — shown in HEMIS employment block.
 */
$staff_row = isset($staff) && is_array($staff) ? $staff : array();
$is_edit = !empty($staff_row);
$sel_dept = set_value('department', '');
if ($sel_dept === '' && $is_edit) {
    if (!empty($staff_row['ugc_department_id'])) {
        $sel_dept = (int) $staff_row['ugc_department_id'];
    } elseif (!empty($staff_row['ugc_section_id'])) {
        $sel_dept = (int) $staff_row['ugc_section_id'];
    } elseif (!empty($staff_row['department'])) {
        $sel_dept = (int) $staff_row['department'];
    }
}
$ugc_departments = isset($ugc_departments) && is_array($ugc_departments) ? $ugc_departments : array();
$ugc_sections = isset($ugc_sections) && is_array($ugc_sections) ? $ugc_sections : array();
?>
<div class="col-md-4" id="hemis_org_unit_wrap">
    <div class="form-group">
        <label for="department" id="hemis_org_unit_label"><?php echo $this->lang->line('department'); ?> <span class="label label-info">HEMIS</span></label>
        <select id="department" name="department" class="form-control" data-selected="<?php echo html_escape((string) $sel_dept); ?>">
            <option value=""><?php echo $this->lang->line('select'); ?></option>
        </select>
        <span class="help-block small" id="hemis_org_unit_hint">Filled from Employee Position when available; otherwise pick UGC department (teaching) or section (non-teaching).</span>
        <span class="text-danger"><?php echo form_error('department'); ?></span>
    </div>
</div>
<script type="text/javascript">
window.staffUgcOrgUnits = {
    departments: <?php echo json_encode(array_values($ugc_departments)); ?>,
    sections: <?php echo json_encode(array_values($ugc_sections)); ?>
};
</script>
