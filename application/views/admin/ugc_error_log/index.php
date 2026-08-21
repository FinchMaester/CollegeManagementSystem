<div class="content-wrapper">
    <section class="content-header">
        <h1><i class="fa fa-exclamation-triangle"></i> <?php echo html_escape($title); ?></h1>
    </section>

    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Latest 200 UGC sync errors (traceId + message)</h3>
                    </div>

                    <div class="box-body">
                        <?php if (empty($rows)) { ?>
                            <div class="alert alert-info">No UGC errors logged yet.</div>
                        <?php } else { ?>
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>When</th>
                                            <th>Module</th>
                                            <th>HTTP</th>
                                            <th>traceId</th>
                                            <th>Message</th>
                                            <th>Endpoint</th>
                                            <th>Response snippet</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($rows as $r) { ?>
                                            <tr>
                                                <td><?php echo (int) $r['id']; ?></td>
                                                <td><?php echo html_escape($r['created_at']); ?></td>
                                                <td><?php echo html_escape((string) $r['module']); ?></td>
                                                <td><?php echo html_escape((string) $r['http_code']); ?></td>
                                                <td style="max-width:240px; word-break:break-all;"><?php echo html_escape((string) $r['trace_id']); ?></td>
                                                <td style="max-width:320px; word-break:break-word;"><?php echo html_escape((string) $r['message']); ?></td>
                                                <td style="max-width:420px; word-break:break-all;"><?php echo html_escape((string) $r['endpoint']); ?></td>
                                                <td style="max-width:520px; white-space:pre-wrap; word-break:break-word;"><?php echo html_escape((string) $r['response_snippet']); ?></td>
                                            </tr>
                                        <?php } ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

