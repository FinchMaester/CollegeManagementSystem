<?php
$r = isset($report) && is_array($report) ? $report : array();
$sid = isset($staff_id) ? (int) $staff_id : 0;
$summary = isset($r['staff_summary']) ? $r['staff_summary'] : array();
?>
<div class="content-wrapper">
    <section class="content-header">
        <h1><i class="fa fa-stethoscope"></i> HEMIS Employee POST vs PATCH</h1>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Staff #<?php echo (int) $sid; ?></h3>
                        <div class="box-tools pull-right">
                            <a href="<?php echo site_url('admin/staff/edit/' . $sid); ?>" class="btn btn-default btn-sm">Edit staff</a>
                            <a href="<?php echo site_url('admin/ugc_sync/debug_employee/' . $sid . '?format=json'); ?>" class="btn btn-default btn-sm" target="_blank">JSON</a>
                        </div>
                    </div>
                    <div class="box-body">
                        <?php if (empty($r['staff_found'])) { ?>
                            <div class="alert alert-danger"><?php echo html_escape(isset($r['error']) ? $r['error'] : 'Employee not found'); ?></div>
                        <?php } else { ?>

                        <div class="row">
                            <div class="col-md-6">
                                <table class="table table-bordered table-condensed">
                                    <tr><th>Name</th><td><?php echo html_escape(isset($summary['name']) ? $summary['name'] : ''); ?></td></tr>
                                    <tr><th>Employee ID</th><td><?php echo html_escape(isset($summary['employee_id']) ? $summary['employee_id'] : ''); ?></td></tr>
                                    <tr><th>Email</th><td><?php echo html_escape(isset($summary['email']) ? $summary['email'] : ''); ?></td></tr>
                                    <tr><th>hemis_employee_id</th><td><strong><?php echo $r['hemis_employee_id'] ? (int) $r['hemis_employee_id'] : '— (PATCH will POST Create instead)'; ?></strong></td></tr>
                                    <tr><th>hemis_sync_at</th><td><?php echo html_escape(isset($summary['hemis_sync_at']) ? $summary['hemis_sync_at'] : ''); ?></td></tr>
                                    <tr><th>hemis_sync_error</th><td class="text-danger"><?php echo html_escape(isset($summary['hemis_sync_error']) ? $summary['hemis_sync_error'] : ''); ?></td></tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <table class="table table-bordered table-condensed">
                                    <tr><th>POST URL</th><td><code><?php echo html_escape(isset($r['post_url']) ? $r['post_url'] : ''); ?></code></td></tr>
                                    <tr><th>PATCH URL</th><td><code><?php echo html_escape(isset($r['patch_url']) ? $r['patch_url'] : 'N/A'); ?></code></td></tr>
                                    <tr><th>sync_enabled</th><td><?php echo !empty($r['sync_config']['sync_enabled']) ? 'yes' : 'no'; ?></td></tr>
                                    <tr><th>sync_employee</th><td><?php echo !empty($r['sync_config']['sync_employee']) ? 'yes' : 'no'; ?></td></tr>
                                    <tr><th>mock_mode</th><td><?php echo !empty($r['sync_config']['mock_mode']) ? 'yes' : 'no'; ?></td></tr>
                                    <tr><th>POST precheck missing</th><td>
                                        <?php
                                        $miss = isset($r['post_precheck_missing']) ? $r['post_precheck_missing'] : array();
                                        echo empty($miss) ? '<span class="text-green">None</span>' : html_escape(implode(', ', $miss));
                                        ?>
                                    </td></tr>
                                </table>
                                <button type="button" class="btn btn-warning btn-sm" id="btn_try_sync">
                                    <i class="fa fa-refresh"></i> Try live sync now
                                </button>
                                <pre id="try_sync_result" style="display:none;margin-top:10px;max-height:200px;overflow:auto;"></pre>
                            </div>
                        </div>

                        <?php if (!empty($r['field_diff'])) { ?>
                        <h4>Fields that differ: POST wire vs PATCH wire</h4>
                        <p class="text-muted">For employees the only intended difference is the <code>id</code> field added on PATCH.</p>
                        <table class="table table-striped table-bordered table-condensed">
                            <thead><tr><th>Field</th><th>POST (Create)</th><th>PATCH (Update)</th></tr></thead>
                            <tbody>
                            <?php foreach ($r['field_diff'] as $fk => $pair) { ?>
                                <tr>
                                    <td><code><?php echo html_escape($fk); ?></code></td>
                                    <td><?php echo html_escape((string) (isset($pair['post']) ? $pair['post'] : '')); ?></td>
                                    <td><?php echo html_escape((string) (isset($pair['patch']) ? $pair['patch'] : '')); ?></td>
                                </tr>
                            <?php } ?>
                            </tbody>
                        </table>
                        <?php } else { ?>
                        <p class="text-muted">POST and PATCH wire previews are identical (or PATCH not applicable without hemis_employee_id).</p>
                        <?php } ?>

                        <div class="row">
                            <div class="col-md-6">
                                <h4>POST Create preview</h4>
                                <pre style="max-height:400px;overflow:auto;"><?php echo html_escape(json_encode(isset($r['post_preview']) ? $r['post_preview'] : array(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)); ?></pre>
                            </div>
                            <div class="col-md-6">
                                <h4>PATCH preview<?php echo empty($r['patch_preview']) ? ' (n/a)' : ''; ?></h4>
                                <pre style="max-height:400px;overflow:auto;"><?php echo html_escape(json_encode(isset($r['patch_preview']) ? $r['patch_preview'] : array(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)); ?></pre>
                            </div>
                        </div>

                        <?php if (!empty($r['latest_logs'])) { ?>
                        <h4>Recent ugc_error_logs (Employee)</h4>
                        <?php foreach ($r['latest_logs'] as $log) { ?>
                            <div class="well well-sm">
                                <strong>#<?php echo (int) $log['id']; ?></strong>
                                <?php echo html_escape($log['created_at']); ?> —
                                <?php echo html_escape($log['http_method']); ?>
                                HTTP <?php echo (int) $log['http_code']; ?> —
                                <code><?php echo html_escape($log['endpoint']); ?></code>
                                <?php if (!empty($log['multipart_preview'])) { ?>
                                <pre style="max-height:180px;overflow:auto;margin-top:8px;"><?php echo html_escape(json_encode($log['multipart_preview'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)); ?></pre>
                                <?php } ?>
                                <?php if (!empty($log['response_body'])) { ?>
                                <small class="text-danger"><?php echo html_escape(substr((string) $log['response_body'], 0, 300)); ?></small>
                                <?php } ?>
                            </div>
                        <?php } ?>
                        <?php } ?>

                        <?php } ?>
                    </div>
                </div>

                <div class="box box-default">
                    <div class="box-body">
                        <p class="text-muted" style="margin:0;">
                            Search another employee: change the URL to
                            <code><?php echo site_url('admin/ugc_sync/debug_employee/'); ?>{staff.id}</code>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
<script type="text/javascript">
(function () {
    var btn = document.getElementById('btn_try_sync');
    if (!btn) return;
    btn.addEventListener('click', function () {
        if (!confirm('Send a live sync (POST/PATCH) to UGC for this employee?')) return;
        btn.disabled = true;
        var pre = document.getElementById('try_sync_result');
        pre.style.display = 'block';
        pre.textContent = 'Sending…';
        var fd = new FormData();
        fd.append('<?php echo $this->security->get_csrf_token_name(); ?>', '<?php echo $this->security->get_csrf_hash(); ?>');
        fetch('<?php echo site_url('admin/ugc_sync/debug_employee_try_sync/' . (int) $sid); ?>', {
            method: 'POST',
            body: fd,
            credentials: 'same-origin'
        }).then(function (r) { return r.json(); }).then(function (j) {
            pre.textContent = JSON.stringify(j, null, 2);
            btn.disabled = false;
        }).catch(function (e) {
            pre.textContent = String(e);
            btn.disabled = false;
        });
    });
})();
</script>
