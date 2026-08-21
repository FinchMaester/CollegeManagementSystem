<?php
$staff_row = isset($staff) && is_array($staff) ? $staff : array();
$is_edit = !empty($staff_row);
$ugc_departments = isset($ugc_departments) && is_array($ugc_departments) ? $ugc_departments : array();
$ugc_sections = isset($ugc_sections) && is_array($ugc_sections) ? $ugc_sections : array();
$bank_branch_label = $this->lang->line('bank_branch_name');
if ($bank_branch_label === false || trim($bank_branch_label) === '') {
    $bank_branch_label = 'Bank Branch';
}
?>
<div class="row">
    <div class="col-md-12">
        <hr style="margin:15px 0 10px;" />
        <h4 class="pagetitleh2" style="margin:0 0 6px;">HEMIS / UGC (Employment)</h4>
        <p class="text-muted small" style="margin-bottom:10px;">Select <strong>Employee Position (HEMIS)</strong> — type, post, and category appear beside it. Pick <strong>Department</strong> or <strong>Section</strong> on the next row when needed.</p>
    </div>
</div>

<!-- Row 1: Position + live summary -->
<div class="row">
    <div class="col-md-5">
        <div class="form-group">
            <label for="employee_position_id">Employee Position (HEMIS)</label><small class="req"> *</small>
            <?php
            $sel_pos = set_value('employee_position_id', $is_edit && isset($staff_row['employee_position_id']) ? (int) $staff_row['employee_position_id'] : '');
            $position_category = set_value('position', $is_edit && isset($staff_row['position']) ? $staff_row['position'] : '');
            $this->load->view('admin/staff/_hemis_employee_position_select', array(
                'ugc_employee_positions' => isset($ugc_employee_positions) ? $ugc_employee_positions : array(),
                'sel_pos'              => $sel_pos,
                'position_category'    => $position_category,
            ));
            ?>
            <span class="text-danger"><?php echo form_error('employee_position_id'); ?></span>
            <input type="hidden" id="employee_type" name="employee_type" value="<?php echo html_escape(set_value('employee_type', $is_edit && isset($staff_row['employee_type']) ? $staff_row['employee_type'] : '')); ?>" />
            <input type="hidden" id="post_name" name="post_name" value="<?php echo html_escape(set_value('post_name', $is_edit && isset($staff_row['post_name']) ? $staff_row['post_name'] : '')); ?>" />
            <input type="hidden" name="position" id="hemis_position_category" value="<?php echo html_escape($position_category); ?>" />
        </div>
    </div>
    <div class="col-md-7">
        <div class="form-group" style="margin-bottom:0;">
            <label>From selected position</label>
            <div id="hemis_position_summary" class="well well-sm" style="margin-bottom:0;min-height:52px;padding:10px 12px;background:#f9f9f9;border-color:#e3e3e3;">
                <span class="text-muted">Select Employee Position to see type, post, and category.</span>
            </div>
        </div>
    </div>
</div>

<!-- Row 2: Org unit + joining + position type -->
<div class="row" style="margin-top:8px;">
    <?php $this->load->view('admin/staff/_staff_hemis_org_unit_select', array(
        'staff' => $staff_row,
        'ugc_departments' => $ugc_departments,
        'ugc_sections' => $ugc_sections,
    )); ?>
    <div class="col-md-4">
        <div class="form-group">
            <label for="contract_type"><?php echo $this->lang->line('contract_type'); ?> (HEMIS Joining Type)</label><small class="req"> *</small>
            <select class="form-control" name="contract_type" id="contract_type">
                <option value=""><?php echo $this->lang->line('select'); ?></option>
                <?php foreach ($contract_type as $key => $value) { ?>
                    <option value="<?php echo $key ?>" <?php echo set_select('contract_type', $key, $is_edit && isset($staff_row['contract_type']) && $staff_row['contract_type'] == $key); ?>><?php echo $value ?></option>
                <?php } ?>
            </select>
            <span class="text-danger"><?php echo form_error('contract_type'); ?></span>
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label for="position_type">Position Type (HEMIS)</label>
            <input id="position_type" name="position_type" type="text" class="form-control" value="<?php echo set_value('position_type', $is_edit && !empty($staff_row['position_type']) ? $staff_row['position_type'] : 'Other'); ?>" />
            <span class="help-block small">Usually <code>Other</code> — distinct from Joining Type above.</span>
        </div>
    </div>
</div>

<!-- Row 3: Identity for HEMIS -->
<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label for="citizenship_no">Citizenship No (HEMIS)</label><small class="req"> *</small>
            <input id="citizenship_no" name="citizenship_no" placeholder="e.g. 01-01-00000" type="text" class="form-control" value="<?php echo set_value('citizenship_no', $is_edit && isset($staff_row['citizenship_no']) ? $staff_row['citizenship_no'] : ''); ?>" />
            <span class="text-danger"><?php echo form_error('citizenship_no'); ?></span>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="pan_no"><?php echo $this->lang->line('pan_no'); ?></label><small class="req"> *</small>
            <input id="pan_no" name="pan_no" type="text" class="form-control" value="<?php echo set_value('pan_no', $is_edit && isset($staff_row['pan_no']) ? $staff_row['pan_no'] : ''); ?>" />
            <span class="text-danger"><?php echo form_error('pan_no'); ?></span>
        </div>
    </div>
</div>

<!-- Row 4: Banking -->
<div class="row">
    <div class="col-md-3">
        <div class="form-group">
            <label for="account_title"><?php echo $this->lang->line('account_title'); ?></label>
            <input id="account_title" name="account_title" type="text" class="form-control" value="<?php echo set_value('account_title', $is_edit && isset($staff_row['account_title']) ? $staff_row['account_title'] : ''); ?>" />
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group">
            <label for="bank_account_no"><?php echo $this->lang->line('bank_account_number'); ?></label><small class="req"> *</small>
            <input id="bank_account_no" name="bank_account_no" type="text" class="form-control" value="<?php echo set_value('bank_account_no', $is_edit && isset($staff_row['bank_account_no']) ? $staff_row['bank_account_no'] : ''); ?>" />
            <span class="text-danger"><?php echo form_error('bank_account_no'); ?></span>
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group">
            <label for="bank_name"><?php echo $this->lang->line('bank_name'); ?></label><small class="req"> *</small>
            <input id="bank_name" name="bank_name" type="text" class="form-control" value="<?php echo set_value('bank_name', $is_edit && isset($staff_row['bank_name']) ? $staff_row['bank_name'] : ''); ?>" />
            <span class="text-danger"><?php echo form_error('bank_name'); ?></span>
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group">
            <label for="bank_branch"><?php echo html_escape($bank_branch_label); ?></label>
            <input id="bank_branch" name="bank_branch" type="text" class="form-control" placeholder="Branch name" value="<?php echo set_value('bank_branch', $is_edit && isset($staff_row['bank_branch']) ? $staff_row['bank_branch'] : ''); ?>" />
        </div>
    </div>
</div>
