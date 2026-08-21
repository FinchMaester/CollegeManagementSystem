<?php
$exam_name = isset($exam->exam) ? htmlspecialchars($exam->exam) : '';
$exam_id   = isset($exam_id) ? (int) $exam_id : 0;
?>
<form method="post" action="<?php echo site_url('admin/examgroup/addsubjectgroupsforexam'); ?>" id="assignSubjectGroupExamForm">
    <?php echo $this->customlib->getCSRF(); ?>
    <input type="hidden" name="exam_id" value="<?php echo $exam_id; ?>">
    <p class="text-muted"><strong><?php echo $this->lang->line('exam'); ?>:</strong> <?php echo $exam_name; ?></p>
    <div class="table-responsive">
        <table class="table table-striped table-condensed">
            <thead>
                <tr>
                    <th><input type="checkbox" id="assign_sg_select_all"/> <?php echo $this->lang->line('all'); ?></th>
                    <th><?php echo $this->lang->line('subject_group'); ?></th>
                    <th><?php echo $this->lang->line('status'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php
                if (empty($subject_groups)) {
                    ?>
                    <tr>
                        <td colspan="3" class="text-center text-danger"><?php echo $this->lang->line('no_subject_groups_found') ? $this->lang->line('no_subject_groups_found') : 'No subject groups found.'; ?></td>
                    </tr>
                    <?php
                } else {
                    foreach ($subject_groups as $sg) {
                        $assigned = !empty($sg['exam_subject_group_id']) && $sg['exam_subject_group_id'] != 0;
                        ?>
                        <tr>
                            <td>
                                <input type="hidden" name="all_subject_groups[]" value="<?php echo (int) $sg['subject_group_id']; ?>">
                                <input class="assign-sg-cb checkbox" type="checkbox" name="subject_groups_id[]" value="<?php echo (int) $sg['subject_group_id']; ?>" <?php echo $assigned ? 'checked="checked"' : ''; ?>>
                            </td>
                            <td><?php echo htmlspecialchars($sg['subject_group_name']); ?></td>
                            <td>
                                <?php if ($assigned) { ?>
                                    <span class="label label-success"><?php echo $this->lang->line('assigned'); ?></span>
                                <?php } else { ?>
                                    <span class="label label-default"><?php echo $this->lang->line('not_assigned'); ?></span>
                                <?php } ?>
                            </td>
                        </tr>
                        <?php
                    }
                }
                ?>
            </tbody>
        </table>
    </div>
    <div class="text-right">
        <button type="submit" class="btn btn-primary btn-sm" id="assign_sg_submit_btn" data-loading-text="<i class='fa fa-spinner fa-spin'></i> <?php echo $this->lang->line('saving'); ?>"><?php echo $this->lang->line('save'); ?></button>
    </div>
</form>
