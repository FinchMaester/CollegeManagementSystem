<?php foreach ($examList as $exam_key => $exam_value) { ?>
<tr>
    <td>
        <?php echo $exam_value->exam; ?>
    </td>
    <td class="white-space-nowrap">
        <?php echo $exam_value->session; ?>
    </td>
    <td>
        <?php echo $exam_value->total_subjects; ?>
    </td>
    <td class="text text-center">
        <?php echo ($exam_value->is_active == 1) ? "<i class='fa fa-check-square-o'></i>" : "<i class='fa fa-exclamation-circle'></i>"; ?>
    </td>
    <td class="text text-center">
        <?php echo ($exam_value->is_publish == 1) ? "<i class='fa fa-check-square-o'></i>" : "<i class='fa fa-exclamation-circle'></i>"; ?>
    </td>
    <td>
        <?php echo $exam_value->description; ?>
    </td>
    <td class="text-right white-space-nowrap">
        <?php if ($this->rbac->hasPrivilege('exam_assign_view_student', 'can_view')) { ?>
            <?php
            $assigned_levels = isset($exam_value->assigned_levels) ? $exam_value->assigned_levels : array();
            $levels_tooltip = '';
            $levels_badge  = '';
            if (!empty($assigned_levels)) {
                $parts = array();
                foreach ($assigned_levels as $lev) {
                    $label = trim(($lev->section ? $lev->section : '') . ' / ' . ($lev->program ? $lev->program : '') . ' / ' . ($lev->class ? $lev->class : ''));
                    $label = trim($label, ' /');
                    if ($label !== '') {
                        $parts[] = $label;
                    }
                }
                $levels_tooltip = implode('; ', $parts);
                $levels_badge  = count($parts);
            }
            ?>
            <?php if ($levels_badge > 0) : ?>
                <span class="label label-info assign-levels-badge" data-toggle="tooltip" title="<?php echo htmlspecialchars($levels_tooltip); ?>"><?php echo (int) $levels_badge; ?> <?php echo $this->lang->line('level') ? $this->lang->line('level') : 'level'; ?><?php echo $levels_badge !== 1 ? 's' : ''; ?></span>
            <?php endif; ?>
            <?php if ($this->rbac->hasPrivilege('exam_group', 'can_edit')) { ?>
                <button type="button" class="btn btn-default btn-xs assignSubjectGroupExamBtn" data-toggle="tooltip"
                        data-examid="<?php echo $exam_value->id; ?>"
                        data-examname="<?php echo htmlspecialchars($exam_value->exam); ?>"
                        title="<?php echo $this->lang->line('assign_subject_groups') ? $this->lang->line('assign_subject_groups') : 'Assign Subject Group'; ?>">
                    <i class="fa fa-th-list" aria-hidden="true"></i>
                </button>
            <?php } ?>
            <button type="button"
                    data-toggle="tooltip"
                    title="<?php echo $this->lang->line('assign_student'); ?>"
                    class="btn btn-default btn-xs assignStudent"
                    id="load"
                    data-examid="<?php echo $exam_value->id; ?>">
                <i class="fa fa-tag"></i>
            </button>

            <?php if (isset($exam_value->total_students) && $exam_value->total_students > 0) { ?>
                <button type="button"
                        data-toggle="tooltip"
                        title="<?php echo $this->lang->line('view_assigned_students'); ?>"
                        class="btn btn-default btn-xs viewAssignedStudents"
                        data-examid="<?php echo $exam_value->id; ?>">
                    <i class="fa fa-eye"></i>
                </button>
            <?php } ?>
        <?php } ?>

        <?php if ($this->rbac->hasPrivilege('exam_subject', 'can_view')) { ?>
            <button class="btn btn-default btn-xs subjectModalButton" data-toggle="tooltip"
                    data-exam_id="<?php echo $exam_value->id; ?>"
                    title="<?php echo $this->lang->line('exam_subject'); ?>">
                <i class="fa fa-book" aria-hidden="true"></i>
            </button>
        <?php } ?>


        <?php if ($this->rbac->hasPrivilege('exam_marks', 'can_view')) { ?>
            <button type="button"
                    class="btn btn-default btn-xs examMarksSubject"
                    id="load"
                    data-toggle="tooltip"
                    data-recordid="<?php echo $exam_value->id; ?>"
                    title="<?php echo $this->lang->line('exam_marks'); ?>"
                    data-loading-text="<i class='fa fa-spinner fa-spin'></i>">
                <i class="fa fa-newspaper-o"></i>
            </button>


            <button type="button"
                    class="btn btn-default btn-xs examTeacherReamark"
                    id="load"
                    data-toggle="tooltip"
                    data-recordid="<?php echo $exam_value->id; ?>"
                    title="<?php echo $this->lang->line('teacher_remark'); ?>"
                    data-loading-text="<i class='fa fa-spinner fa-spin'></i>">
                <i class="fa fa-comment"></i>
            </button>
        <?php } ?>


        <?php if ($this->rbac->hasPrivilege('exam', 'can_edit')) { ?>
            <button class="btn btn-default btn-xs editexamModalButton"
                    data-toggle="tooltip"
                    data-exam_id="<?php echo $exam_value->id; ?>"
                    title="<?php echo $this->lang->line('edit') ?>">
                <i class="fa fa-pencil" aria-hidden="true"></i>
            </button>
        <?php } ?>


        <?php if ($this->rbac->hasPrivilege('generate_rank', 'can_view')) { ?>
            <button class="btn btn-default btn-xs"
                    data-toggle="modal"
                    data-original-title="<?php echo $this->lang->line('generate_rank'); ?>"
                    data-target="#studentRankModal"
                    data-exam_id="<?php echo $exam_value->id; ?>"
                    data-exam-name="<?php echo $exam_value->exam; ?>"
                    title="<?php echo $this->lang->line('generate_rank'); ?>">
                <i class="fa fa-list-alt" aria-hidden="true"></i>
            </button>
        <?php } ?>


        <?php if ($this->rbac->hasPrivilege('exam', 'can_delete')) { ?>
            <span data-toggle="tooltip" title="<?php echo $this->lang->line('delete'); ?>">
                <a href="#" class="btn btn-default btn-xs delete-exam-btn"
                   data-id="<?php echo $exam_value->id; ?>"
                   data-exam="<?php echo $exam_value->exam; ?>"
                   id="deleteItem">
                    <i class="fa fa-remove"></i>
                </a>
            </span>
        <?php } ?>
    </td>
</tr>
<?php } ?>



