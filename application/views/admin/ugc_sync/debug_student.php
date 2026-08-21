<?php
$r = isset($report) && is_array($report) ? $report : array();
$sid = isset($student_id) ? (int) $student_id : 0;
$summary = isset($r['student_summary']) ? $r['student_summary'] : array();
?>
<div class="content-wrapper">
    <section class="content-header">
        <h1><i class="fa fa-stethoscope"></i> HEMIS Student POST vs PATCH</h1>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Student #<?php echo (int) $sid; ?></h3>
                        <div class="box-tools pull-right">
                            <a href="<?php echo site_url('student/edit/' . $sid); ?>" class="btn btn-default btn-sm">Edit student</a>
                            <a href="<?php echo site_url('admin/ugc_sync/debug_student/' . $sid . '?format=json'); ?>" class="btn btn-default btn-sm" target="_blank">JSON</a>
                        </div>
                    </div>
                    <div class="box-body">
                        <?php if (empty($r['student_found'])) { ?>
                            <div class="alert alert-danger"><?php echo html_escape(isset($r['error']) ? $r['error'] : 'Student not found'); ?></div>
                        <?php } else { ?>

                        <div class="row">
                            <div class="col-md-6">
                                <table class="table table-bordered table-condensed">
                                    <tr><th>Name</th><td><?php echo html_escape(isset($summary['name']) ? $summary['name'] : ''); ?></td></tr>
                                    <tr><th>Admission no</th><td><?php echo html_escape(isset($summary['admission_no']) ? $summary['admission_no'] : ''); ?></td></tr>
                                    <tr><th>Local roll_no</th><td><?php echo html_escape(isset($summary['roll_no']) ? $summary['roll_no'] : ''); ?></td></tr>
                                    <tr><th>hemis_student_id</th><td><strong><?php echo $r['hemis_student_id'] ? (int) $r['hemis_student_id'] : '— (PATCH will POST Create instead)'; ?></strong></td></tr>
                                    <tr><th>Local ugc_batch_id</th><td><?php echo html_escape(isset($summary['ugc_batch_id']) ? $summary['ugc_batch_id'] : ''); ?></td></tr>
                                    <tr><th>Local admissionYearId</th><td><?php echo html_escape(isset($summary['admissionYearId']) ? $summary['admissionYearId'] : ''); ?></td></tr>
                                    <tr><th>hemis_sync_at</th><td><?php echo html_escape(isset($summary['hemis_sync_at']) ? $summary['hemis_sync_at'] : ''); ?></td></tr>
                                    <tr><th>hemis_sync_error</th><td class="text-danger"><?php echo html_escape(isset($summary['hemis_sync_error']) ? $summary['hemis_sync_error'] : ''); ?></td></tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <table class="table table-bordered table-condensed">
                                    <tr><th>POST URL</th><td><code><?php echo html_escape(isset($r['post_url']) ? $r['post_url'] : ''); ?></code></td></tr>
                                    <tr><th>PATCH URL</th><td><code><?php echo html_escape(isset($r['patch_url']) ? $r['patch_url'] : 'N/A'); ?></code></td></tr>
                                    <tr><th>sync_enabled</th><td><?php echo !empty($r['sync_config']['sync_enabled']) ? 'yes' : 'no'; ?></td></tr>
                                    <tr><th>sync_student_update</th><td><?php echo !empty($r['sync_config']['sync_student_update']) ? 'yes' : 'no'; ?></td></tr>
                                    <tr><th>mock_mode</th><td><?php echo !empty($r['sync_config']['mock_mode']) ? 'yes' : 'no'; ?></td></tr>
                                    <tr><th>POST precheck missing</th><td>
                                        <?php
                                        $miss = isset($r['post_precheck_missing']) ? $r['post_precheck_missing'] : array();
                                        echo empty($miss) ? '<span class="text-green">None</span>' : html_escape(implode(', ', $miss));
                                        ?>
                                    </td></tr>
                                    <tr><th>POST wire warnings</th><td>
                                        <?php
                                        $warn = isset($r['post_wire_warnings']) ? $r['post_wire_warnings'] : array();
                                        if (empty($warn)) {
                                            echo '<span class="text-green">None</span>';
                                        } else {
                                            echo '<span class="text-red">' . html_escape(implode(' ', $warn)) . '</span>';
                                        }
                                        ?>
                                    </td></tr>
                                </table>
                                <?php if (!empty($r['hemis_student_id'])) { ?>
                                <button type="button" class="btn btn-warning btn-sm" id="btn_try_patch">
                                    <i class="fa fa-refresh"></i> Try live PATCH now
                                </button>
                                <pre id="try_patch_result" style="display:none;margin-top:10px;max-height:200px;overflow:auto;"></pre>
                                <?php } ?>
                            </div>
                        </div>

                        <?php if (!empty($r['local_vs_remote'])) { ?>
                        <h4 class="text-yellow"><i class="fa fa-warning"></i> Local DB vs HEMIS (drift)</h4>
                        <table class="table table-striped table-bordered">
                            <thead><tr><th>Field</th><th>Local DB</th><th>HEMIS GET</th></tr></thead>
                            <tbody>
                            <?php foreach ($r['local_vs_remote'] as $fk => $pair) { ?>
                                <tr>
                                    <td><?php echo html_escape($fk); ?></td>
                                    <td><?php echo html_escape(isset($pair['local_db']) ? $pair['local_db'] : ''); ?></td>
                                    <td><?php echo html_escape(isset($pair['hemis']) ? $pair['hemis'] : ''); ?></td>
                                </tr>
                            <?php } ?>
                            </tbody>
                        </table>
                        <?php } ?>

                        <?php if (!empty($r['remote_get'])) { ?>
                        <h4>HEMIS GET snapshot</h4>
                        <pre style="max-height:220px;overflow:auto;"><?php echo html_escape(json_encode($r['remote_get'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)); ?></pre>
                        <?php } elseif (!empty($r['remote_get_error'])) { ?>
                        <div class="alert alert-warning"><?php echo html_escape($r['remote_get_error']); ?></div>
                        <?php } ?>

                        <?php if (!empty($r['field_diff'])) { ?>
                        <h4>Fields that differ: POST wire vs PATCH wire</h4>
                        <p class="text-muted">PATCH runs GET alignment first; these are the intentional or problematic differences.</p>
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
                        <p class="text-muted">POST and PATCH wire previews are identical (or PATCH not applicable without hemis_student_id).</p>
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
                        <h4>Recent ugc_error_logs (Student)</h4>
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
                                <small class="text-danger">UGC response: <?php echo html_escape(substr((string) $log['response_body'], 0, 500)); ?></small>
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
                            Search another student: change the URL to
                            <code><?php echo site_url('admin/ugc_sync/debug_student/'); ?>{students.id}</code>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
<script type="text/javascript">
(function () {
    var btn = document.getElementById('btn_try_patch');
    if (!btn) return;
    btn.addEventListener('click', function () {
        if (!confirm('Send a live PATCH to UGC for this student?')) return;
        btn.disabled = true;
        var pre = document.getElementById('try_patch_result');
        pre.style.display = 'block';
        pre.textContent = 'Sending…';
        var fd = new FormData();
        fd.append('<?php echo $this->security->get_csrf_token_name(); ?>', '<?php echo $this->security->get_csrf_hash(); ?>');
        fetch('<?php echo site_url('admin/ugc_sync/debug_student_try_patch/' . (int) $sid); ?>', {
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
