<div class="content-wrapper" style="min-height: 946px;">
    <section class="content-header">
        <h1>
            <i class="fa fa-mortar-board"></i> <?php echo $this->lang->line('academics'); ?> <small><?php echo $this->lang->line('programs'); ?></small>
        </h1>
    </section>
    <!-- Main content -->
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><?php echo $this->lang->line('program_details'); ?></h3>
                        <div class="box-tools pull-right">

                        </div>
                    </div>
                    <div class="box-body no-padding">
                        <div class="mailbox-controls">                          
                            <a href="<?php echo base_url(); ?>programs/create" class="btn btn-primary btn-sm" data-toggle="tooltip" title="<?php echo $this->lang->line('add_program'); ?>">
                                <i class="fa fa-plus"></i><?php echo $this->lang->line('add_program'); ?>
                            </a>
                            <div class="pull-right">
                            </div>
                        </div>
                        <div class="table-responsive mailbox-messages">
                            <table class="table table-hover table-striped">
                                <tbody>
                                    <tr>
                                        <td><?php echo $this->lang->line('title'); ?></td>
                                        <td class="mailbox-name"><?php echo $program['title']; ?></td>
                                    </tr>
                                    <tr>
                                        <td><?php echo $this->lang->line('code'); ?></td>
                                        <td class="mailbox-name"><?php echo $program['code']; ?></td>
                                    </tr>
                                    <tr>
                                        <td><?php echo $this->lang->line('class'); ?></td>
                                        <td class="mailbox-name"><?php echo $program['class_name']; ?></td>
                                    </tr>
                                    <tr>
                                        <td><?php echo $this->lang->line('section'); ?></td>
                                        <td class="mailbox-name"><?php echo $program['section_name']; ?></td>
                                    </tr>
                                    <tr>
                                        <td><?php echo $this->lang->line('duration'); ?></td>
                                        <td class="mailbox-name"><?php echo $program['duration']; ?></td>
                                    </tr>
                                    <tr>
                                        <td><?php echo $this->lang->line('session_type'); ?></td>
                                        <td class="mailbox-name"><?php echo ucfirst($program['session_type']); ?></td>
                                    </tr>
                                    <tr>
                                        <td><?php echo $this->lang->line('total_session'); ?></td>
                                        <td class="mailbox-name"><?php echo $program['total_session']; ?></td>
                                    </tr>
                                    <tr>
                                        <td><?php echo $this->lang->line('description'); ?></td>
                                        <td class="mailbox-name"><?php echo $program['description']; ?></td>
                                    </tr>
                                    <tr>
                                        <td><?php echo $this->lang->line('status'); ?></td>
                                        <td class="mailbox-name">
                                            <?php if($program['is_active'] == 1) { ?>
                                                <span class="label label-success"><?php echo $this->lang->line('active'); ?></span>
                                            <?php } else { ?>
                                                <span class="label label-danger"><?php echo $this->lang->line('inactive'); ?></span>
                                            <?php } ?>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="box-footer">
                        <div class="mailbox-controls">
                            <div class="pull-right">
                                <a href="<?php echo base_url(); ?>programs/edit/<?php echo $program['id']; ?>" class="btn btn-info btn-sm" data-toggle="tooltip" title="<?php echo $this->lang->line('edit'); ?>">
                                    <i class="fa fa-pencil"></i> <?php echo $this->lang->line('edit'); ?>
                                </a>
                                <a href="<?php echo base_url(); ?>programs" class="btn btn-default btn-sm" data-toggle="tooltip" title="<?php echo $this->lang->line('back'); ?>">
                                    <i class="fa fa-arrow-left"></i> <?php echo $this->lang->line('back'); ?>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div> 
    </section>
</div>