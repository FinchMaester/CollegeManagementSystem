<div class="content-wrapper">
    <section class="content-header">
        <h1><?php echo html_escape($title); ?></h1>
        <p class="text-muted">Temporary maintenance tool. Restrict to Super Admin. Remove this feature when data is fully captured at admission.</p>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-md-10">
                <div class="box box-warning">
                    <div class="box-header with-border">
                        <h3 class="box-title">Current gaps (students table)</h3>
                    </div>
                    <div class="box-body">
                        <table class="table table-bordered table-condensed">
                            <tbody>
                                <tr><th>Total students</th><td><?php echo (int) $stats['total_students']; ?></td></tr>
                                <tr><th>Missing / blank ethnicity</th><td><?php echo (int) $stats['missing_ethnicity']; ?></td></tr>
                                <tr><th>Missing / blank nationality</th><td><?php echo (int) $stats['missing_nationality']; ?></td></tr>
                                <tr><th>Missing / blank citizenship</th><td><?php echo (int) $stats['missing_citizenship']; ?></td></tr>
                                <tr><th>Missing / blank disability_status</th><td><?php echo (int) $stats['missing_disability_status']; ?></td></tr>
                                <tr><th>Gender rows needing normalization</th><td><?php echo (int) $stats['gender_needs_review']; ?></td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="box box-default">
                    <div class="box-header with-border">
                        <h3 class="box-title">HEMIS gender codes (reference)</h3>
                    </div>
                    <div class="box-body">
                        <p>Stored values stay compatible with existing screens (<code>Male</code> / <code>Female</code> from forms). Numeric codes in imports map as:</p>
                        <ul>
                            <?php foreach ($gender_legend as $code => $label) { ?>
                                <li><code><?php echo html_escape((string) $code); ?></code> → <strong><?php echo html_escape($label); ?></strong></li>
                            <?php } ?>
                        </ul>
                        <p class="text-muted">Blank or unrecognized gender values are set to <code>Other</code> when normalization runs (review these students afterward).</p>
                    </div>
                </div>

                <?php if (!empty($flash)) { ?>
                    <div class="alert alert-<?php echo html_escape(isset($flash['type']) ? $flash['type'] : 'info'); ?>">
                        <?php echo html_escape(isset($flash['text']) ? $flash['text'] : ''); ?>
                        <?php if (!empty($flash['detail']) && is_array($flash['detail'])) { ?>
                            <pre style="margin-top:10px; white-space:pre-wrap;"><?php echo html_escape(print_r($flash['detail'], true)); ?></pre>
                        <?php } ?>
                    </div>
                <?php } ?>

                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Batch update</h3>
                    </div>
                    <form method="post" action="<?php echo site_url('admin/data_sanitization/apply'); ?>" class="box-body">
                        <?php echo $this->customlib->getCSRF(); ?>
                        <div class="checkbox">
                            <label>
                                <input type="checkbox" name="fill_unknown" value="1" checked>
                                Fill empty <strong>ethnicity</strong>, <strong>nationality</strong>, <strong>citizenship</strong> with <code><?php echo html_escape($default_unknown); ?></code>;
                                set empty <strong>disability_status</strong> to the same and clear <strong>disability_type</strong>.
                            </label>
                        </div>
                        <div class="checkbox">
                            <label>
                                <input type="checkbox" name="fill_general_ethnicity" value="1">
                                Set ethnicity <code><?php echo html_escape($default_unknown); ?></code> → <code><?php echo html_escape($default_general); ?></code> (broad HEMIS-style bucket). Use after the step above if you prefer “General” over “Unknown”.
                            </label>
                        </div>
                        <div class="checkbox">
                            <label>
                                <input type="checkbox" name="normalize_gender" value="1">
                                Normalize <strong>gender</strong> to <code>Male</code> / <code>Female</code> / <code>Other</code>; map codes 1/2/3; blank → <code>Other</code>.
                            </label>
                        </div>
                        <div class="checkbox">
                            <label>
                                <input type="checkbox" name="confirm" value="yes">
                                I understand this updates live data and I have a database backup.
                            </label>
                        </div>
                        <button type="submit" class="btn btn-danger" <?php echo empty($can_apply) ? 'disabled' : ''; ?>>Apply selected fixes</button>
                    </form>
                </div>
            </div>
        </div>
    </section>
</div>
