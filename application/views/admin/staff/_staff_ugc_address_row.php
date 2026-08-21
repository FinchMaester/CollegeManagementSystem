<?php
/**
 * UGC/HEMIS address cascade row (province, district, local level).
 * $prefix: ugc_p or ugc_t
 * $label_prefix: e.g. permanent or temporary for section title context
 */
$prefix = isset($prefix) ? (string) $prefix : 'ugc_p';
$staff_row = isset($staff) && is_array($staff) ? $staff : array();
$key_prov = ($prefix === 'ugc_p') ? 'ugc_p_province' : 'ugc_t_province';
$key_dist = ($prefix === 'ugc_p') ? 'ugc_p_district' : 'ugc_t_district';
$key_loc  = ($prefix === 'ugc_p') ? 'ugc_p_local_level' : 'ugc_t_local_level';
$key_code = ($prefix === 'ugc_p') ? 'ugc_p_combined_code' : 'ugc_t_combined_code';
?>
<div class="col-md-3">
    <div class="form-group">
        <label for="<?php echo $prefix; ?>_province"><?php echo $this->lang->line('province'); ?> <span class="label label-info">HEMIS</span></label>
        <select id="<?php echo $prefix; ?>_province" class="form-control" title="Official UGC province"></select>
    </div>
</div>
<div class="col-md-3">
    <div class="form-group">
        <label for="<?php echo $prefix; ?>_district"><?php echo $this->lang->line('district'); ?> <span class="label label-info">HEMIS</span></label>
        <select id="<?php echo $prefix; ?>_district" class="form-control"></select>
    </div>
</div>
<div class="col-md-3">
    <div class="form-group">
        <label for="<?php echo $prefix; ?>_local_level"><?php echo $this->lang->line('localLevel'); ?> <span class="label label-info">HEMIS</span></label>
        <select id="<?php echo $prefix; ?>_local_level" class="form-control"></select>
    </div>
</div>
<script type="text/javascript">
window.__staffUgcAddressInitial = window.__staffUgcAddressInitial || {};
window.__staffUgcAddressInitial['<?php echo $prefix; ?>'] = {
    province: <?php echo json_encode((string) set_value($key_prov, isset($staff_row[$key_prov]) ? $staff_row[$key_prov] : '')); ?>,
    district: <?php echo json_encode((string) set_value($key_dist, isset($staff_row[$key_dist]) ? $staff_row[$key_dist] : '')); ?>,
    localLevel: <?php echo json_encode((string) set_value($key_loc, isset($staff_row[$key_loc]) ? $staff_row[$key_loc] : '')); ?>,
    combinedCode: <?php echo json_encode((string) set_value($key_code, isset($staff_row[$key_code]) ? $staff_row[$key_code] : '')); ?>
};
</script>
