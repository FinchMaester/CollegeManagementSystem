<div class="content-wrapper" style="min-height: 946px;">
    <section class="content-header">
        <h1>
            <i class="fa fa-mortar-board"></i> <?php echo $this->lang->line('academics'); ?> <small><?php echo $this->lang->line('programs'); ?></small>
        </h1>
    </section>
    <!-- Main content -->
    <style>
    @media print
    {    
        .no-print, .no-print *
        {
            display: none !important;
        }
    }
    </style>
    <section class="content">
        <div class="row">
            <?php
            if ($this->rbac->hasPrivilege('program', 'can_add')) {
                ?>
                <div class="col-md-4">
                    <div class="box box-primary">
                        <div class="box-header with-border">
                            <h3 class="box-title"><?php echo $this->lang->line('add_program'); ?></h3>
                        </div>
                        <form action="<?php echo site_url('programs/index') ?>" id="programform" name="programform" method="post" accept-charset="utf-8">
                            <div class="box-body">
                                <?php if ($this->session->flashdata('msg')) { ?>
                                    <?php 
                                        echo $this->session->flashdata('msg');
                                        $this->session->unset_userdata('msg');
                                    ?>
                                <?php } ?>
                                <?php echo $this->customlib->getCSRF(); ?>
                                
                                <div class="form-group">
                                    <label for="title"><?php echo $this->lang->line('title'); ?> </label><small class="req"> *</small>
                                    <input autofocus="" id="title" name="title" placeholder="" type="text" class="form-control" value="<?php echo set_value('title'); ?>" />
                                    <span class="text-danger"><?php echo form_error('title'); ?></span>
                                </div>
                                
                                <div class="form-group">
                                    <label for="code"><?php echo $this->lang->line('code'); ?> </label><small class="req"> *</small>
                                    <input id="code" name="code" placeholder="" type="text" class="form-control" value="<?php echo set_value('code'); ?>" />
                                    <span class="text-danger"><?php echo form_error('code'); ?></span>
                                </div>
                                
                                <div class="form-group">
                                    <label for="section_id"><?php echo $this->lang->line('section'); ?></label><small class="req"> *</small>
                                    <select id="section_id" name="section_id" class="form-control">
                                        <option value=""><?php echo $this->lang->line('select'); ?></option>
                                        <?php foreach ($sectionlist as $section) { ?>
                                            <option value="<?php echo $section['id']; ?>" <?php echo set_select('section_id', $section['id']); ?>><?php echo $section['section']; ?></option>
                                        <?php } ?>
                                    </select>
                                    <span class="text-danger"><?php echo form_error('section_id'); ?></span>
                                </div>
                                
                                <div class="form-group">
                                    <label for="duration"><?php echo $this->lang->line('duration'); ?> </label><small class="req"> *</small>
                                    <input id="duration" name="duration" placeholder="" type="number" class="form-control" value="<?php echo set_value('duration'); ?>" />
                                    <span class="text-danger"><?php echo form_error('duration'); ?></span>
                                </div>
                                
                                <div class="form-group">
                                    <label for="session_type"><?php echo $this->lang->line('session_type'); ?></label><small class="req"> *</small>
                                    <select id="session_type" name="session_type" class="form-control">
                                        <option value="yearly" <?php echo set_select('session_type', 'yearly', true); ?>><?php echo $this->lang->line('yearly'); ?></option>
                                        <option value="semester" <?php echo set_select('session_type', 'semester'); ?>><?php echo $this->lang->line('semester'); ?></option>
                                        <option value="trimester" <?php echo set_select('session_type', 'trimester'); ?>><?php echo $this->lang->line('trimester'); ?></option>
                                    </select>
                                    <span class="text-danger"><?php echo form_error('session_type'); ?></span>
                                </div>
                                
                                <div class="form-group">
                                    <label for="total_session"><?php echo $this->lang->line('total_session'); ?> </label><small class="req"> *</small>
                                    <input id="total_session" name="total_session" placeholder="" type="number" class="form-control" value="<?php echo set_value('total_session'); ?>" />
                                    <span class="text-danger"><?php echo form_error('total_session'); ?></span>
                                </div>
                                
                                <div class="form-group">
                                    <label for="description"><?php echo $this->lang->line('description'); ?></label>
                                    <textarea id="description" name="description" class="form-control"><?php echo set_value('description'); ?></textarea>
                                    <span class="text-danger"><?php echo form_error('description'); ?></span>
                                </div>
                                
                                <div class="form-group">
                                    <label class="control-label"><?php echo $this->lang->line('status'); ?></label>
                                    <div class="radio">
                                        <label>
                                            <input type="radio" name="is_active" value="1" checked="checked"> <?php echo $this->lang->line('active'); ?>
                                        </label>
                                        <label>
                                            <input type="radio" name="is_active" value="0"> <?php echo $this->lang->line('inactive'); ?>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="box-footer">
                                <button type="submit" class="btn btn-info pull-right"><?php echo $this->lang->line('save'); ?></button>
                            </div>
                        </form>
                    </div>
                </div>
            <?php } ?>
            <div class="col-md-<?php
            if ($this->rbac->hasPrivilege('program', 'can_add')) {
                echo "8";
            } else {
                echo "12";
            }
            ?>">
                <div class="box box-primary">
                    <div class="box-header ptbnull">
                        <h3 class="box-title titlefix"><?php echo $this->lang->line('program_list'); ?></h3>
                    </div>
                    <div class="box-body">
                        <div class="table-responsive mailbox-messages overflow-visible">
                            <div class="download_label"><?php echo $this->lang->line('program_list'); ?></div>
                            <table class="table table-striped table-bordered table-hover example">
                                <thead>
                                    <tr>
                                        <th><?php echo $this->lang->line('title'); ?></th>
                                        <th><?php echo $this->lang->line('code'); ?></th>
                                        <th><?php echo $this->lang->line('class'); ?></th>
                                        <th><?php echo $this->lang->line('section'); ?></th>
                                        <th><?php echo $this->lang->line('duration'); ?></th>
                                        <th><?php echo $this->lang->line('session_type'); ?></th>
                                        <th><?php echo $this->lang->line('status'); ?></th>
                                        <th class="text-right noExport"><?php echo $this->lang->line('action'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $count = 1;
                                    foreach ($programlist as $program) {
                                        ?>
                                        <tr>
                                            <td class="mailbox-name"><?php echo $program['title']; ?></td>
                                            <td class="mailbox-name"><?php echo $program['code']; ?></td>
                                            <td class="mailbox-name"><?php echo $program['class_name']; ?></td>
                                            <td class="mailbox-name"><?php echo $program['section_name']; ?></td>
                                            <td class="mailbox-name"><?php echo $program['duration']; ?></td>
                                            <td class="mailbox-name"><?php echo ucfirst($program['session_type']); ?></td>
                                            <td class="mailbox-name">
                                                <?php if ($program['is_active'] == 1) { ?>
                                                    <span class="label label-success"><?php echo $this->lang->line('active'); ?></span>
                                                <?php } else { ?>
                                                    <span class="label label-danger"><?php echo $this->lang->line('inactive'); ?></span>
                                                <?php } ?>
                                            </td>
                                            <td class="mailbox-date pull-right">
                                                <a href="<?php echo base_url(); ?>programs/view/<?php echo $program['id']; ?>" class="btn btn-default btn-xs" data-toggle="tooltip" title="<?php echo $this->lang->line('view'); ?>">
                                                    <i class="fa fa-eye"></i>
                                                </a>
                                                <?php
                                                if ($this->rbac->hasPrivilege('program', 'can_edit')) {
                                                    ?>
                                                    <a href="<?php echo base_url(); ?>programs/edit/<?php echo $program['id']; ?>" class="btn btn-default btn-xs" data-toggle="tooltip" title="<?php echo $this->lang->line('edit'); ?>">
                                                        <i class="fa fa-pencil"></i>
                                                    </a>
                                                    <?php
                                                }
                                                if ($this->rbac->hasPrivilege('program', 'can_delete')) {
                                                    ?>
                                                    <a href="<?php echo base_url(); ?>programs/delete/<?php echo $program['id']; ?>" class="btn btn-default btn-xs" data-toggle="tooltip" title="<?php echo $this->lang->line('delete'); ?>" onclick="return confirm('<?php echo $this->lang->line('delete_confirm'); ?>');">
                                                        <i class="fa fa-remove"></i>
                                                    </a>
                                                <?php } ?>
                                            </td>
                                        </tr>
                                        <?php
                                    }
                                    $count++;
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>