<?php
if (!$this->rbac->hasPrivilege('superadmin', 'can_edit')) {
    return;
}
$this->load->model('ugc_sync_state_model');
$sid = (int) $staff['id'];
$st = isset($sync_map[$sid]) ? $sync_map[$sid] : null;
$disp = $this->ugc_sync_state_model->employee_display_for_staff($staff, $st);
$title = isset($disp['last_error']) ? (string) $disp['last_error'] : '';
$hemisId = !empty($disp['hemis_employee_id']) ? (int) $disp['hemis_employee_id'] : 0;
?>
<td>
    <span id="ugc-emp-badge-<?php echo $sid; ?>"
          class="label <?php echo html_escape($disp['badge']); ?>"
          <?php echo $title !== '' ? 'title="' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . '"' : ''; ?>>
        <?php echo html_escape($disp['text']); ?>
    </span>
    <a class="small" style="margin-left:6px;"
       href="<?php echo site_url('admin/ugc_sync/debug_latest_log?module=Employee&limit=1&source=remote'); ?>"
       target="_blank"
       title="Open latest remote Employee sync log">
        log
    </a>
</td>
<td id="hemis-emp-id-<?php echo $sid; ?>">
    <?php echo $hemisId > 0 ? (int) $hemisId : '—'; ?>
</td>
