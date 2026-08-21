<div class="content-wrapper">
    <section class="content-header">
        <h1><i class="fa fa-shield"></i> <?php echo html_escape($title); ?></h1>
    </section>

    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Current session role union</h3>
                    </div>

                    <div class="box-body">
                        <p class="text-muted">
                            This page reflects the merged permission cache used by <code>$this->rbac->hasPrivilege()</code>.
                        </p>

                        <h4>Roles in session</h4>
                        <?php if (empty($roles)) { ?>
                            <div class="alert alert-warning">No roles found in session.</div>
                        <?php } else { ?>
                            <ul>
                                <?php foreach ($roles as $r) { ?>
                                    <li><?php echo html_escape($r['name']); ?> (ID: <?php echo (int) $r['id']; ?>)</li>
                                <?php } ?>
                            </ul>
                        <?php } ?>

                        <h4>Cache summary</h4>
                        <ul>
                            <li><strong>cache_key</strong>: <?php echo html_escape(isset($cache['cache_key']) ? (string) $cache['cache_key'] : ''); ?></li>
                            <li><strong>built_at</strong>: <?php echo html_escape(isset($cache['built_at']) ? (string) $cache['built_at'] : ''); ?></li>
                            <li><strong>merged_categories</strong>: <?php echo (int) (isset($cache['merged_categories']) ? $cache['merged_categories'] : 0); ?></li>
                            <li><strong>merged_rows</strong>: <?php echo (int) (isset($cache['merged_rows']) ? $cache['merged_rows'] : 0); ?></li>
                        </ul>

                        <h4>Merged permissions (categories)</h4>
                        <?php if (empty($merged)) { ?>
                            <div class="alert alert-info">Merged permissions are empty.</div>
                        <?php } else { ?>
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>Category</th>
                                            <th>can_view</th>
                                            <th>can_add</th>
                                            <th>can_edit</th>
                                            <th>can_delete</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        ksort($merged);
                                        foreach ($merged as $cat => $p) {
                                            $cv = !empty($p['can_view']) ? 1 : 0;
                                            $ca = !empty($p['can_add']) ? 1 : 0;
                                            $ce = !empty($p['can_edit']) ? 1 : 0;
                                            $cd = !empty($p['can_delete']) ? 1 : 0;
                                            ?>
                                            <tr>
                                                <td><?php echo html_escape((string) $cat); ?></td>
                                                <td><?php echo $cv; ?></td>
                                                <td><?php echo $ca; ?></td>
                                                <td><?php echo $ce; ?></td>
                                                <td><?php echo $cd; ?></td>
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

