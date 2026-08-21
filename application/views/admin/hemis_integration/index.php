<div class="content-wrapper">
    <section class="content">
        <div class="row">
            <?php $this->load->view('setting/_settingmenu'); ?>
            <div class="col-md-10">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-plug"></i> <?php echo $this->lang->line('hemis_integration'); ?></h3>
                    </div>
                    <form action="<?php echo site_url('hemisintegration'); ?>" method="post" class="form-horizontal">
                        <div class="box-body">
                            <?php if ($this->session->flashdata('msg')) { ?>
                                <?php echo $this->session->flashdata('msg'); ?>
                            <?php } ?>
                            <?php echo validation_errors('<div class="alert alert-danger">', '</div>'); ?>
                            <?php echo $this->customlib->getCSRF(); ?>

                            <p class="text-muted"><?php echo $this->lang->line('hemis_integration_help_mock'); ?>
                                <?php echo $this->lang->line('hemis_integration_help_remote'); ?></p>
                            <p class="text-muted"><strong>Inbound:</strong> External systems call your server’s REST routes under
                                <code><?php echo rtrim(base_url(), '/'); ?>/api/...</code> using Bearer tokens from your <code>tokens</code> table (API login).</p>

                            <div class="form-group">
                                <label class="col-sm-3 control-label"><?php echo $this->lang->line('hemis_integration'); ?> — master</label>
                                <div class="col-sm-9">
                                    <label class="checkbox-inline">
                                        <input type="checkbox" name="sync_enabled" value="1" <?php echo !empty($cfg['sync_enabled']) ? 'checked' : ''; ?>>
                                        Enable outbound sync to central API
                                    </label>
                                </div>
                            </div>

                            <h4 class="box-title text-green" style="margin-top:1.5em;">Connection</h4>
                            <hr>
                            <?php
                            $ts = isset($ugc_token_status) && is_array($ugc_token_status) ? $ugc_token_status : array();
                            $tsLevel = isset($ts['status_level']) ? $ts['status_level'] : 'warn';
                            $tsAlert = ($tsLevel === 'ok') ? 'alert-success' : (($tsLevel === 'danger') ? 'alert-danger' : 'alert-warning');
                            ?>
                            <div id="hemis-ugc-token-status" class="alert <?php echo $tsAlert; ?>" style="margin-bottom:18px;">
                                <strong><i class="fa fa-shield"></i> UGC bearer token status</strong>
                                <ul class="list-unstyled" style="margin:8px 0 0 0;">
                                    <li>
                                        <strong>Token saved:</strong>
                                        <span id="hemis-token-saved-label">
                                            <?php if (!empty($ts['has_token'])) { ?>
                                                Yes (<?php echo (int) $ts['token_length']; ?> chars<?php
                                                if (!empty($ts['token_source']) && $ts['token_source'] !== 'none') {
                                                    echo ', source: ' . html_escape($ts['token_source']);
                                                }
                                                ?>)
                                            <?php } else { ?>
                                                No — use <em>Test UGC login</em> or paste <code>tokenString</code> from Postman
                                            <?php } ?>
                                        </span>
                                    </li>
                                    <li>
                                        <strong>Expires:</strong>
                                        <span id="hemis-token-expires">
                                            <?php
                                            if (!empty($ts['expires_at'])) {
                                                echo html_escape($ts['expires_at']);
                                                if (!empty($ts['is_expired'])) {
                                                    echo ' <span class="text-danger">(expired)</span>';
                                                } elseif (!empty($ts['expiring_soon'])) {
                                                    echo ' <span class="text-warning">(within 24 hours)</span>';
                                                }
                                            } elseif (!empty($ts['has_token'])) {
                                                echo 'Unknown (not a JWT or no exp claim)';
                                            } else {
                                                echo '—';
                                            }
                                            ?>
                                        </span>
                                    </li>
                                    <li>
                                        <strong>Last saved:</strong>
                                        <span id="hemis-token-fetched-at"><?php
                                            echo !empty($ts['fetched_at']) ? html_escape($ts['fetched_at']) : '—';
                                        ?></span>
                                    </li>
                                    <li>
                                        <strong>Auto-refresh on sync:</strong>
                                        <span id="hemis-token-auto-label">
                                            <?php if (!empty($ts['auto_refresh_ready'])) { ?>
                                                <span class="text-success">Ready</span> — missing/expired token or HTTP 401 will call <code>/api/User/Login</code> and save a new JWT automatically.
                                            <?php } else { ?>
                                                <span class="text-danger">Not ready</span>
                                                <ul style="margin:4px 0 0 18px;">
                                                    <?php
                                                    $blockers = isset($ts['auto_refresh_blockers']) ? $ts['auto_refresh_blockers'] : array();
                                                    foreach ($blockers as $note) {
                                                        echo '<li>' . html_escape($note) . '</li>';
                                                    }
                                                    ?>
                                                </ul>
                                            <?php } ?>
                                        </span>
                                    </li>
                                    <?php if (!empty($ts['env_token_overrides_db'])) { ?>
                                    <li class="text-warning"><strong>Note:</strong> <code>HEMIS_ENV_TOKEN_OVERRIDES_DB=true</code> — token in <code>.env</code> overrides the database (manual .env updates required).</li>
                                    <?php } elseif (!empty($ts['has_token']) && isset($ts['token_source']) && $ts['token_source'] === '.env') { ?>
                                    <li class="text-muted"><strong>Tip:</strong> Token is coming from <code>.env</code>. Save credentials below and use Test login so the DB token can auto-refresh.</li>
                                    <?php } ?>
                                </ul>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-3 control-label">Central API base URL</label>
                                <div class="col-sm-9">
                                    <input type="url" name="remote_base_url" class="form-control" placeholder="https://hemisapi.ugcnepal.edu.np"
                                           value="<?php echo html_escape(isset($cfg['remote_base_url']) ? $cfg['remote_base_url'] : ''); ?>">
                                    <span class="help-block"><?php echo $this->lang->line('hemis_integration_help_remote'); ?></span>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-3 control-label">Bearer token</label>
                                <div class="col-sm-9">
                                    <input type="password" name="remote_bearer_token" id="remote_bearer_token" class="form-control" autocomplete="new-password" placeholder="<?php echo $this->lang->line('hemis_integration_help_token'); ?>">
                                    <span class="help-block" id="hemis-bearer-token-help">
                                    <?php if (!empty($cfg['remote_bearer_token'])) { ?>
                                        <span class="text-success">A token is already saved. Submit the form with this field empty to keep it unchanged.</span>
                                    <?php } else { ?>
                                        Paste a JWT here, or use <strong>Get token manually</strong> below to fill this field from UGC login.
                                    <?php } ?>
                                    </span>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-3 control-label">HTTP timeout (seconds)</label>
                                <div class="col-sm-9">
                                    <input type="number" name="remote_timeout" class="form-control" min="5" max="300"
                                           value="<?php echo (int) (isset($cfg['remote_timeout']) ? $cfg['remote_timeout'] : 30); ?>">
                                </div>
                            </div>
                            <?php if ($this->db->field_exists('remote_login_email', 'hemis_integration_settings')) { ?>
                            <h4 class="box-title text-green" style="margin-top:1.5em;">UGC auto-login (optional)</h4>
                            <hr>
                            <p class="text-muted">When a bearer token is missing or expired, the system can POST to <code>/api/User/Login</code> using the fields below and save the new token automatically. Put login email/password here or in <code>.env</code> (<code>HEMIS_REMOTE_LOGIN_EMAIL</code> / <code>HEMIS_REMOTE_LOGIN_PASSWORD</code>). Prefer credentials over a static token in <code>.env</code> — a stale <code>HEMIS_REMOTE_BEARER_TOKEN</code> there used to block auto-refresh (fixed: DB token wins unless <code>HEMIS_ENV_TOKEN_OVERRIDES_DB=true</code>).</p>
                            <div class="form-group">
                                <label class="col-sm-3 control-label">UGC login email</label>
                                <div class="col-sm-9">
                                    <input type="email" name="remote_login_email" class="form-control" autocomplete="off"
                                           value="<?php echo html_escape(isset($cfg['remote_login_email']) ? $cfg['remote_login_email'] : ''); ?>">
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-3 control-label">UGC login password</label>
                                <div class="col-sm-9">
                                    <input type="password" name="remote_login_password" class="form-control" autocomplete="new-password" placeholder="Leave blank to keep current password">
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-3 control-label">College / campus ID (UGC)</label>
                                <div class="col-sm-9">
                                    <input type="number" name="remote_college_id" class="form-control" min="0"
                                           value="<?php echo (int) (isset($cfg['remote_college_id']) ? $cfg['remote_college_id'] : 0); ?>">
                                    <span class="help-block">Sent as <code>campusId</code> / <code>institutionId</code> on employee sync. Overrides <code>HEMIS_COLLEGE_ID</code> in <code>.env</code> when saved here.</span>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-3 control-label">University ID (UGC)</label>
                                <div class="col-sm-9">
                                    <input type="number" name="remote_uni_id" class="form-control" min="0"
                                           value="<?php echo (int) (isset($cfg['remote_uni_id']) ? $cfg['remote_uni_id'] : 0); ?>">
                                    <span class="help-block">Used for <code>GET /api/EmployeePositions/Get/{universityId}</code> and for <code>POST /api/Employee/{universityId}</code> when creating employees (unless <code>HEMIS_EMPLOYEE_POST_LEGACY_PATH=true</code>). Typical value <strong>1</strong>. Same as <code>HEMIS_UNIVERSITY_ID</code> in <code>.env</code> when set.</span>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-3 control-label">isUgc</label>
                                <div class="col-sm-9">
                                    <label class="checkbox-inline">
                                        <input type="checkbox" name="remote_is_ugc" value="1" <?php echo !empty($cfg['remote_is_ugc']) ? 'checked' : ''; ?>>
                                        Send <code>isUgc: true</code> in login JSON
                                    </label>
                                </div>
                            </div>
                            <?php if ($this->rbac->hasPrivilege('superadmin', 'can_edit')) { ?>
                            <div class="form-group">
                                <label class="col-sm-3 control-label">UGC token</label>
                                <div class="col-sm-9">
                                    <button type="button" id="hemis-test-ugc-login" class="btn btn-info">
                                        <i class="fa fa-key"></i> Test UGC login
                                    </button>
                                    <button type="button" id="hemis-get-token-manual" class="btn btn-warning" style="margin-left:6px;">
                                        <i class="fa fa-download"></i> Get token manually
                                    </button>
                                    <span class="help-block">
                                        Both call <code>POST /api/User/Login</code> (blank password = use saved password).<br>
                                        <strong>Test UGC login</strong> saves the JWT to the database immediately.<br>
                                        <strong>Get token manually</strong> fills the Bearer token field above — then click <strong>Save</strong> to store it (useful when auto-refresh fails on the live server).<br>
                                        If UGC returns only a <code>verificationToken</code> and no JWT, paste a token from Postman into Bearer token and Save.
                                    </span>
                                    <div id="hemis-test-ugc-login-result" style="margin-top:10px;display:none;"></div>
                                </div>
                            </div>
                            <?php } ?>
                            <?php } ?>
                            <div class="form-group">
                                <label class="col-sm-3 control-label">Local mock base URL</label>
                                <div class="col-sm-9">
                                    <input type="url" name="local_mock_base_url" class="form-control"
                                           value="<?php echo html_escape(isset($cfg['local_mock_base_url']) ? $cfg['local_mock_base_url'] : ''); ?>"
                                           placeholder="http://localhost/your-app/mockhemis">
                                    <span class="help-block">When mock mode is off and this is set, HTTP sync targets this base instead of the central URL (for local testing).</span>
                                </div>
                            </div>

                            <h4 class="box-title text-green" style="margin-top:1.5em;">Mode</h4>
                            <hr>
                            <div class="form-group">
                                <label class="col-sm-3 control-label">Testing</label>
                                <div class="col-sm-9">
                                    <label class="checkbox-inline">
                                        <input type="checkbox" name="mock_mode" value="1" <?php echo !empty($cfg['mock_mode']) ? 'checked' : ''; ?>>
                                        Mock mode (no real HTTP to central; synthetic IDs)
                                    </label><br>
                                    <label class="checkbox-inline" style="margin-top:8px;">
                                        <input type="checkbox" name="mock_http_enabled" value="1" <?php echo !empty($cfg['mock_http_enabled']) ? 'checked' : ''; ?>>
                                        Enable /mockhemis endpoints on this server
                                    </label>
                                </div>
                            </div>

                            <h4 class="box-title text-green" style="margin-top:1.5em;">Outbound sync (what to push)</h4>
                            <hr>
                            <div class="row">
                                <div class="col-sm-6">
                                    <?php
                                    $syncOpts = array(
                                        'sync_student_update' => 'Student update (PATCH)',
                                        'sync_student_upgrade' => 'Student upgrade',
                                        'sync_graduation' => 'Graduation applications',
                                        'sync_dropout' => 'Drop-out',
                                        'sync_library' => 'Library',
                                        'sync_employee' => 'Employee',
                                        'sync_labs' => 'Labs',
                                    );
                                    foreach ($syncOpts as $k => $label) {
                                        $c = !empty($cfg[$k]);
                                        echo '<label class="checkbox" style="display:block;font-weight:normal;"><input type="checkbox" name="' . $k . '" value="1" ' . ($c ? 'checked' : '') . '> ' . $label . '</label>';
                                    }
                                    ?>
                                </div>
                                <div class="col-sm-6">
                                    <?php
                                    $syncOpts2 = array(
                                        'sync_buildings' => 'Buildings',
                                        'sync_fev' => 'Furniture / equipment / vehicles',
                                        'sync_hostels' => 'Hostels',
                                        'sync_lands' => 'Lands',
                                        'sync_management' => 'Department / section (management)',
                                    );
                                    foreach ($syncOpts2 as $k => $label) {
                                        $c = !empty($cfg[$k]);
                                        echo '<label class="checkbox" style="display:block;font-weight:normal;"><input type="checkbox" name="' . $k . '" value="1" ' . ($c ? 'checked' : '') . '> ' . $label . '</label>';
                                    }
                                    ?>
                                </div>
                            </div>

                            <h4 class="box-title text-green" style="margin-top:1.5em;">Inbound reference pull (from central)</h4>
                            <hr>
                            <div class="row">
                                <div class="col-sm-6">
                                    <?php
                                    $pullOpts = array(
                                        'pull_batch' => 'Batch',
                                        'pull_fiscal_year' => 'Fiscal year',
                                        'pull_address' => 'Address',
                                        'pull_employee_positions' => 'Employee positions',
                                        'pull_programmgmt' => 'College programs (ProgramMgmt)',
                                    );
                                    foreach ($pullOpts as $k => $label) {
                                        $c = !empty($cfg[$k]);
                                        echo '<label class="checkbox" style="display:block;font-weight:normal;"><input type="checkbox" name="' . $k . '" value="1" ' . ($c ? 'checked' : '') . '> ' . $label . '</label>';
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>
                        <div class="box-footer">
                            <?php if ($this->rbac->hasPrivilege('superadmin', 'can_edit')) { ?>
                                <button type="submit" name="submit" value="1" class="btn btn-primary pull-right"><?php echo $this->lang->line('save'); ?></button>
                            <?php } ?>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
</div>
<?php if ($this->db->field_exists('remote_login_email', 'hemis_integration_settings') && $this->rbac->hasPrivilege('superadmin', 'can_edit')) { ?>
<script type="text/javascript">
$(function () {
    var $testBtn = $('#hemis-test-ugc-login');
    var $getBtn = $('#hemis-get-token-manual');
    var $result = $('#hemis-test-ugc-login-result');
    if (!$testBtn.length && !$getBtn.length) {
        return;
    }

    function buildLoginPayload($form, opts) {
        var payload = {
            remote_base_url: $form.find('[name="remote_base_url"]').val(),
            remote_login_email: $form.find('[name="remote_login_email"]').val(),
            remote_login_password: $form.find('[name="remote_login_password"]').val(),
            remote_college_id: $form.find('[name="remote_college_id"]').val(),
            remote_uni_id: $form.find('[name="remote_uni_id"]').val(),
            remote_timeout: $form.find('[name="remote_timeout"]').val(),
            save_token: opts.saveToken ? '1' : '0',
            include_token: opts.includeToken ? '1' : '0'
        };
        if ($form.find('[name="remote_is_ugc"]').is(':checked')) {
            payload.remote_is_ugc = '1';
        }
        payload['<?php echo $this->security->get_csrf_token_name(); ?>'] = '<?php echo $this->security->get_csrf_hash(); ?>';
        return payload;
    }

    function runUgcLogin($btn, opts) {
        var $form = $btn.closest('form');
        var payload = buildLoginPayload($form, opts);

        $testBtn.prop('disabled', true);
        $getBtn.prop('disabled', true);
        $result.hide().removeClass('alert-success alert-danger').empty();

        $.ajax({
            url: '<?php echo site_url('hemisintegration/test_ugc_login'); ?>',
            type: 'POST',
            dataType: 'json',
            data: payload
        }).done(function (resp) {
            var ok = resp && parseInt(resp.status, 10) === 1;
            var html = '<strong>' + (ok ? 'Success' : 'Failed') + ':</strong> ' + (resp.message || '');
            if (resp.login_url) {
                html += '<br><small>URL: <code>' + $('<div>').text(resp.login_url).html() + '</code></small>';
            }
            if (resp.login_payload) {
                html += '<br><small>Posted login JSON (password omitted): <code>'
                    + $('<div>').text(JSON.stringify(resp.login_payload)).html()
                    + '</code></small>';
            }
            if (resp.http_code) {
                html += '<br><small>HTTP ' + resp.http_code + '</small>';
            }
            if (resp.token_length) {
                html += '<br><small>Token length: ' + resp.token_length + (resp.token_saved ? ' (saved to DB)' : '') + '</small>';
            }
            if (resp.token_expires_at) {
                html += '<br><small>Expires: ' + resp.token_expires_at + '</small>';
                $('#hemis-token-expires').html(resp.token_expires_at);
            }
            if (resp.token_fetched_at) {
                html += '<br><small>Saved at: ' + resp.token_fetched_at + '</small>';
                $('#hemis-token-fetched-at').text(resp.token_fetched_at);
            }

            if (ok && opts.includeToken && resp.token) {
                var $tokenInput = $('#remote_bearer_token');
                $tokenInput.attr('type', 'text').val(resp.token);
                $('#hemis-bearer-token-help').html(
                    '<span class="text-warning"><strong>Token filled from UGC login.</strong> Click <em>Save</em> at the bottom to store it, then start syncing.</span>'
                );
                $('#hemis-token-saved-label').text('Pending save (' + resp.token_length + ' chars in form)');
                $('#hemis-ugc-token-status').removeClass('alert-danger alert-success').addClass('alert-warning');
                html += '<br><small class="text-warning">Bearer token field was filled — click Save to persist.</small>';
            } else if (ok && resp.token_length && opts.saveToken) {
                $('#hemis-token-saved-label').text('Yes (' + resp.token_length + ' chars, source: database)');
                $('#hemis-ugc-token-status').removeClass('alert-danger alert-warning').addClass('alert-success');
                $('#hemis-token-auto-label').html('<span class="text-success">Ready</span> — token was just saved. Auto-refresh will run on expiry or HTTP 401.');
            }

            if (resp.mock_mode_note) {
                html += '<br><small class="text-warning">' + resp.mock_mode_note + '</small>';
            }
            if (!ok && resp.response_snippet) {
                html += '<br><small>Response: ' + $('<div>').text(resp.response_snippet).html() + '</small>';
            }
            if (resp.verify_url) {
                html += '<br><small>Verify URL tried: <code>' + $('<div>').text(resp.verify_url).html() + '</code></small>';
            }
            $result.addClass(ok ? 'alert alert-success' : 'alert alert-danger').html(html).show();
        }).fail(function (xhr) {
            var msg = 'Request failed';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                msg = xhr.responseJSON.message;
            }
            $result.addClass('alert alert-danger').html('<strong>Error:</strong> ' + msg).show();
        }).always(function () {
            $testBtn.prop('disabled', false);
            $getBtn.prop('disabled', false);
        });
    }

    $testBtn.on('click', function () {
        runUgcLogin($(this), { saveToken: true, includeToken: false });
    });

    $getBtn.on('click', function () {
        runUgcLogin($(this), { saveToken: false, includeToken: true });
    });
});
</script>
<?php } ?>
