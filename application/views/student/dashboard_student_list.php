<div class="content-wrapper">
    <section class="content-header">
        <h1>
            <i class="fa fa-user-plus"></i> <?php echo html_escape($title); ?>
        </h1>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">
                            <i class="fa fa-list"></i> <?php echo html_escape($title); ?>
                            <span class="badge bg-blue"><?php echo isset($resultlist) ? count($resultlist) : 0; ?></span>
                        </h3>
                        <div class="box-tools pull-right">
                            <a href="<?php echo site_url('admin/admin/dashboard'); ?>" class="btn btn-default btn-sm">
                                <i class="fa fa-arrow-left"></i> Dashboard
                            </a>
                        </div>
                    </div>
                    <div class="box-body table-responsive">
                        <?php if (empty($resultlist)) { ?>
                            <div class="alert alert-info"><?php echo $this->lang->line('no_record_found'); ?></div>
                        <?php } else { ?>
                            <table class="table table-striped table-bordered table-hover example">
                                <thead>
                                    <tr>
                                        <th><?php echo $this->lang->line('admission_no'); ?></th>
                                        <th><?php echo $this->lang->line('student_name'); ?></th>
                                        <th><?php echo $this->lang->line('gender'); ?></th>
                                        <th><?php echo $this->lang->line('class'); ?></th>
                                        <th><?php echo $this->lang->line('section'); ?></th>
                                        <th><?php echo $this->lang->line('program'); ?></th>
                                        <?php if (!empty($show_session)) { ?>
                                            <th><?php echo $this->lang->line('session'); ?></th>
                                        <?php } ?>
                                        <th><?php echo $this->lang->line('mobile_number'); ?></th>
                                        <th class="text-right noExport"><?php echo $this->lang->line('action'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($resultlist as $student) {
                                        $full_name = $this->customlib->getFullName(
                                            isset($student['firstname']) ? $student['firstname'] : '',
                                            isset($student['middlename']) ? $student['middlename'] : '',
                                            isset($student['lastname']) ? $student['lastname'] : '',
                                            $sch_setting->middlename,
                                            $sch_setting->lastname
                                        );
                                        ?>
                                        <tr>
                                            <td><?php echo html_escape(isset($student['admission_no']) ? $student['admission_no'] : ''); ?></td>
                                            <td><?php echo html_escape($full_name); ?></td>
                                            <td><?php echo html_escape(isset($student['gender']) ? $student['gender'] : ''); ?></td>
                                            <td><?php echo html_escape(isset($student['class']) ? $student['class'] : ''); ?></td>
                                            <td><?php echo html_escape(isset($student['section']) ? $student['section'] : ''); ?></td>
                                            <td><?php echo html_escape(isset($student['program_name']) ? $student['program_name'] : ''); ?></td>
                                            <?php if (!empty($show_session)) { ?>
                                                <td><?php echo html_escape(isset($student['session_name']) ? $student['session_name'] : ''); ?></td>
                                            <?php } ?>
                                            <td><?php echo html_escape(isset($student['mobileno']) ? $student['mobileno'] : ''); ?></td>
                                            <td class="text-right">
                                                <a href="<?php echo site_url('student/view/' . (int) $student['id']); ?>" class="btn btn-default btn-xs" data-toggle="tooltip" title="<?php echo $this->lang->line('view'); ?>">
                                                    <i class="fa fa-reorder"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
