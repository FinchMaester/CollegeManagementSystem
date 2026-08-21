<div class="content-wrapper">
    <section class="content-header">
        <h1><i class="fa fa-cog"></i> <?php echo $this->lang->line('library_settings'); ?></h1>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><?php echo $this->lang->line('library_settings'); ?></h3>
                        <div class="box-tools pull-right">
                            <a href="<?php echo site_url('admin/member'); ?>" class="btn btn-default btn-sm">
                                <i class="fa fa-arrow-left"></i> <?php echo $this->lang->line('library'); ?>
                            </a>
                        </div>
                    </div>
                    <form action="<?php echo site_url('admin/librarysettings'); ?>" method="post" class="form-horizontal">
                        <div class="box-body">
                            <?php
                            if ($this->session->flashdata('msg')) {
                                echo $this->session->flashdata('msg');
                                $this->session->unset_userdata('msg');
                            }
                            echo validation_errors('<div class="alert alert-danger">', '</div>');
                            echo $this->customlib->getCSRF();
                            $s = isset($settings) ? $settings : array();
                            $renew_on = !empty($s['renewal_enabled']);
                            $fine_on = !empty($s['fine_enabled']);
                            ?>

                            <p class="text-muted"><?php echo $this->lang->line('library_settings_help'); ?></p>

                            <h4 class="text-bold"><i class="fa fa-refresh"></i> <?php echo $this->lang->line('book_renewal_settings'); ?></h4>
                            <hr>

                            <div class="form-group">
                                <label class="col-sm-4 control-label"><?php echo $this->lang->line('enable_book_renewal'); ?></label>
                                <div class="col-sm-8">
                                    <label class="checkbox-inline">
                                        <input type="checkbox" name="renewal_enabled" value="1" <?php echo $renew_on ? 'checked' : ''; ?> <?php echo empty($can_edit) ? 'disabled' : ''; ?>>
                                        <?php echo $this->lang->line('enable'); ?>
                                    </label>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-4 control-label" for="renewal_days"><?php echo $this->lang->line('renewal_extension_days'); ?></label>
                                <div class="col-sm-4">
                                    <input type="number" min="1" max="365" class="form-control" id="renewal_days" name="renewal_days"
                                           value="<?php echo (int) (isset($s['renewal_days']) ? $s['renewal_days'] : 14); ?>"
                                           <?php echo empty($can_edit) ? 'readonly' : ''; ?>>
                                    <span class="help-block"><?php echo $this->lang->line('renewal_extension_days_help'); ?></span>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-4 control-label" for="max_renewals"><?php echo $this->lang->line('max_renewals_per_book'); ?></label>
                                <div class="col-sm-4">
                                    <input type="number" min="0" max="20" class="form-control" id="max_renewals" name="max_renewals"
                                           value="<?php echo (int) (isset($s['max_renewals']) ? $s['max_renewals'] : 2); ?>"
                                           <?php echo empty($can_edit) ? 'readonly' : ''; ?>>
                                </div>
                            </div>

                            <h4 class="text-bold" style="margin-top:24px;"><i class="fa fa-money"></i> <?php echo $this->lang->line('library_fine_settings'); ?></h4>
                            <hr>

                            <div class="form-group">
                                <label class="col-sm-4 control-label"><?php echo $this->lang->line('enable_library_fines'); ?></label>
                                <div class="col-sm-8">
                                    <label class="checkbox-inline">
                                        <input type="checkbox" name="fine_enabled" value="1" <?php echo $fine_on ? 'checked' : ''; ?> <?php echo empty($can_edit) ? 'disabled' : ''; ?>>
                                        <?php echo $this->lang->line('enable'); ?>
                                    </label>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-4 control-label" for="fine_per_day"><?php echo $this->lang->line('fine_per_day'); ?></label>
                                <div class="col-sm-4">
                                    <div class="input-group">
                                        <span class="input-group-addon"><?php echo htmlspecialchars($currency_symbol); ?></span>
                                        <input type="number" step="0.01" min="0" class="form-control" id="fine_per_day" name="fine_per_day"
                                               value="<?php echo htmlspecialchars(number_format((float) (isset($s['fine_per_day']) ? $s['fine_per_day'] : 10), 2, '.', '')); ?>"
                                               <?php echo empty($can_edit) ? 'readonly' : ''; ?>>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-4 control-label" for="fine_grace_days"><?php echo $this->lang->line('fine_grace_days'); ?></label>
                                <div class="col-sm-4">
                                    <input type="number" min="0" max="30" class="form-control" id="fine_grace_days" name="fine_grace_days"
                                           value="<?php echo (int) (isset($s['fine_grace_days']) ? $s['fine_grace_days'] : 0); ?>"
                                           <?php echo empty($can_edit) ? 'readonly' : ''; ?>>
                                    <span class="help-block"><?php echo $this->lang->line('fine_grace_days_help'); ?></span>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-4 control-label" for="fine_max"><?php echo $this->lang->line('maximum_fine_per_book'); ?></label>
                                <div class="col-sm-4">
                                    <div class="input-group">
                                        <span class="input-group-addon"><?php echo htmlspecialchars($currency_symbol); ?></span>
                                        <input type="number" step="0.01" min="0" class="form-control" id="fine_max" name="fine_max"
                                               value="<?php echo htmlspecialchars(number_format((float) (isset($s['fine_max']) ? $s['fine_max'] : 500), 2, '.', '')); ?>"
                                               <?php echo empty($can_edit) ? 'readonly' : ''; ?>>
                                    </div>
                                    <span class="help-block"><?php echo $this->lang->line('maximum_fine_help'); ?></span>
                                </div>
                            </div>
                        </div>
                        <?php if (!empty($can_edit)) { ?>
                        <div class="box-footer">
                            <button type="submit" class="btn btn-info pull-right">
                                <i class="fa fa-check"></i> <?php echo $this->lang->line('save'); ?>
                            </button>
                        </div>
                        <?php } ?>
                    </form>
                </div>
            </div>
        </div>
    </section>
</div>
