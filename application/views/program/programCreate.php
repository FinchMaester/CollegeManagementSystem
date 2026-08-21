<div class="content-wrapper">
    <section class="content-header">
        <h1>
            <i class="fa fa-book"></i> <?php echo $this->lang->line('academics'); ?> <small><?php echo $this->lang->line('program_management'); ?></small>
        </h1>
    </section>
    <!-- Main content -->
    <section class="content">
        <div class="row">
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
                                <label for="title"><?php echo $this->lang->line('title'); ?></label></label><small class="req"> *</small>
                                <input id="title" name="title" placeholder="" type="text" class="form-control" value="<?php echo set_value('title'); ?>" />
                                <span class="text-danger"><?php echo form_error('title'); ?></span>
                            </div>


                            <div class="form-group">
                                <label for="section_id"><?php echo $this->lang->line('section'); ?></label><small class="req"> *</small>
                                <select class="form-control" id="section_id" name="section_id">
                                    <option value=""><?php echo $this->lang->line('select'); ?></option>
                                    <?php
                                    if (!empty($sections)) {
                                        foreach ($sections as $section) {
                                            $selected = '';

                                            if (isset($program_section) && $program_section == $section['id']) {
                                                $selected = 'selected="selected"';
                                            } 
                                            elseif (set_value('section_id') == $section['id']) {
                                                $selected = 'selected="selected"';
                                            }
                                            ?>
                                            <option value="<?php echo $section['id'] ?>" <?php echo $selected; ?>>
                                                <?php echo htmlspecialchars($section['section']) ?>
                                            </option>
                                            <?php
                                        }
                                    }
                                    ?>
                                </select>
                                <span class="text-danger"><?php echo form_error('section_id'); ?></span>
                            </div>
                            
                            <div class="form-group">
                                <label for="code"><?php echo $this->lang->line('code'); ?></label></label><small class="req"> *</small>
                                <input id="code" name="code" placeholder="" type="text" class="form-control" value="<?php echo set_value('code'); ?>" />
                                <span class="text-danger"><?php echo form_error('code'); ?></span>
                            </div>
                           
                            <div class="form-group">
                                <label for="duration"><?php echo $this->lang->line('duration'); ?> (<?php echo $this->lang->line('years'); ?>)</label></label><small class="req"> *</small>
                                <input id="duration" name="duration" placeholder="" type="number" class="form-control" value="<?php echo set_value('duration'); ?>" min="1" />
                                <span class="text-danger"><?php echo form_error('duration'); ?></span>
                            </div>
                           
                            <div class="form-group">
                                <label for="session_type"><?php echo $this->lang->line('session_type'); ?></label>
                                <select class="form-control" id="session_type" name="session_type">
                                    <option value="yearly"><?php echo $this->lang->line('yearly'); ?></option>
                                    <option value="semester"><?php echo $this->lang->line('semester'); ?></option>
                                    <option value="trimester"><?php echo $this->lang->line('trimester'); ?></option>
                                </select>
                            </div>
                           
                            <div class="form-group">
                                <label for="total_session"><?php echo $this->lang->line('total_sessions'); ?></label>
                                <input id="total_session" name="total_session" placeholder="" type="number" class="form-control" value="<?php echo set_value('total_session'); ?>" min="1" />
                                <span class="text-danger"><?php echo form_error('total_session'); ?></span>
                            </div>
                           
                            <div class="form-group">
                                <label for="description"><?php echo $this->lang->line('description'); ?></label>
                                <textarea id="description" name="description" class="form-control" rows="3"><?php echo set_value('description'); ?></textarea>
                            </div>
                           
                            <div class="form-group">
                                <label for="is_active"><?php echo $this->lang->line('status'); ?></label></label><small class="req"> *</small>
                                <select class="form-control" id="is_active" name="is_active">
                                    <option value="1"><?php echo $this->lang->line('active'); ?></option>
                                    <option value="0"><?php echo $this->lang->line('inactive'); ?></option>
                                </select>
                            </div>
                        </div>
                        <div class="box-footer">
                            <button type="button" class="btn btn-default" onclick="window.history.back()"><?php echo $this->lang->line('cancel'); ?></button>
                            <button type="submit" class="btn btn-info pull-right"><?php echo $this->lang->line('save'); ?></button>
                        </div>
                    </form>
                </div>            
            </div>
            <div class="col-md-8">              
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><?php echo $this->lang->line('program_list'); ?></h3>
                    </div>
                    <div class="box-body ">
                        <div class="table-responsive mailbox-messages">
                            <table class="table table-hover table-striped">
                                <thead>
                                    <tr>
                                        <th><?php echo $this->lang->line('title'); ?></th>
                                        <th><?php echo $this->lang->line('code'); ?></th>
                                        <th><?php echo $this->lang->line('section'); ?></th>
                                        <th><?php echo $this->lang->line('duration'); ?></th>
                                        <th><?php echo $this->lang->line('status'); ?></th>
                                        <th class="pull-right"><?php echo $this->lang->line('action'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $count = 1;
                                    foreach ($programlist as $program) {
                                        ?>
                                        <tr>
                                            <td class="mailbox-name"><a href="#"><?php echo $program['title'] ?></a></td>
                                            <td><?php echo $program['code'] ?></td>
                                            <td><?php echo $program['class_name'] ?></td>
                                            <td><?php echo $program['duration'] ?> <?php echo $this->lang->line('years'); ?></td>
                                            <td><?php echo ($program['is_active'] == 1) ? $this->lang->line('active') : $this->lang->line('inactive'); ?></td>
                                            <td class="mailbox-date pull-right">
                                                <a href="<?php echo base_url(); ?>programs/edit/<?php echo $program['id'] ?>" class="btn btn-default btn-xs"  data-toggle="tooltip" title="<?php echo $this->lang->line('edit'); ?>">
                                                    <i class="fa fa-pencil"></i>
                                                </a>
                                                <a href="<?php echo base_url(); ?>programs/delete/<?php echo $program['id'] ?>"class="btn btn-default btn-xs"  data-toggle="tooltip" title="<?php echo $this->lang->line('delete'); ?>" onclick="return confirm('<?php echo $this->lang->line('delete_confirm') ?>');">
                                                    <i class="fa fa-remove"></i>
                                                </a>
                                            </td>
                                        </tr>
                                        <?php
                                        $count++;
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="box-footer">
                        <div class="mailbox-controls">
                        </div>
                    </div>
                </div>
            </div>
        </div>  
    </section>
</div>

