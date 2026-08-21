<?php
$staff_row = isset($staff) && is_array($staff) ? $staff : array();
$val = function ($key, $post_key = null) use ($staff_row) {
    $pk = $post_key !== null ? $post_key : $key;
    if (function_exists('set_value')) {
        $sv = set_value($pk, isset($staff_row[$key]) ? $staff_row[$key] : '');
        return is_string($sv) ? $sv : (string) $sv;
    }
    return isset($staff_row[$key]) ? (string) $staff_row[$key] : '';
};
?>
<input type="hidden" name="ugc_p_province_name" value="<?php echo html_escape($val('ugc_p_province', 'ugc_p_province_name')); ?>">
<input type="hidden" name="ugc_p_district_name" value="<?php echo html_escape($val('ugc_p_district', 'ugc_p_district_name')); ?>">
<input type="hidden" name="ugc_p_local_level_name" value="<?php echo html_escape($val('ugc_p_local_level', 'ugc_p_local_level_name')); ?>">
<input type="hidden" name="ugc_p_combined_code" value="<?php echo html_escape($val('ugc_p_combined_code')); ?>">
<input type="hidden" name="ugc_t_province_name" value="<?php echo html_escape($val('ugc_t_province', 'ugc_t_province_name')); ?>">
<input type="hidden" name="ugc_t_district_name" value="<?php echo html_escape($val('ugc_t_district', 'ugc_t_district_name')); ?>">
<input type="hidden" name="ugc_t_local_level_name" value="<?php echo html_escape($val('ugc_t_local_level', 'ugc_t_local_level_name')); ?>">
<input type="hidden" name="ugc_t_combined_code" value="<?php echo html_escape($val('ugc_t_combined_code')); ?>">
