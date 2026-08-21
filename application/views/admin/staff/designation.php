<style type="text/css">
    @media print {
        .no-print, .no-print * {
            display: none !important;
        }
    }
    .designation-checkboxes {
        display: none;
        margin-top: 15px;
    }
    .designation-checkboxes.show {
        display: block;
    }
    .checkbox-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 10px;
        margin-top: 10px;
    }
    .checkbox-item {
        display: flex;
        align-items: center;
        padding: 8px;
        border: 1px solid #e0e0e0;
        border-radius: 4px;
        background-color: #f9f9f9;
    }
    .checkbox-item input[type="checkbox"] {
        margin-right: 8px;
    }
    .checkbox-item label {
        margin: 0;
        font-weight: normal;
        cursor: pointer;
        flex: 1;
    }
</style>

<div class="content-wrapper" style="min-height: 946px;">
    <section class="content-header">
        <h1><i class="fa fa-sitemap"></i> <?php //echo $this->lang->line('human_resource'); ?></h1>
    </section>
    <!-- Main content -->
    <section class="content">
        <div class="row">
            <?php if (($this->rbac->hasPrivilege('designation', 'can_add')) || ($this->rbac->hasPrivilege('designation', 'can_edit'))) { ?>
                <div class="col-md-4">
                    <div class="box box-primary">
                        <div class="box-header with-border">
                            <h3 class="box-title"><?php echo $title; ?></h3>
                        </div>
                        <form id="form1" action="<?php echo site_url('admin/designation/designation') ?>" method="post" accept-charset="utf-8" enctype="multipart/form-data">
                            <div class="box-body">
                                <?php if ($this->session->flashdata('msg')) { ?>
                                    <?php echo $this->session->flashdata('msg');
                                    $this->session->unset_userdata('msg'); ?>
                                <?php } ?>
                                <?php echo $this->customlib->getCSRF(); ?>
                                
                                <div class="form-group">
                                    <label for="staff_type"><?php echo 'Staff Type'; ?></label><small class="req"> *</small>
                                    <?php echo form_dropdown('staff_type', $staff_types, (isset($result) ? $result['staff_type'] : ''), 
                                        array('class' => 'form-control', 'id' => 'staff_type')); ?>
                                    <span class="text-danger"><?php echo form_error('staff_type'); ?></span>
                                </div>
                                
                                <!-- Dynamic Designation Checkboxes -->
                                <div class="form-group designation-checkboxes" id="designation-checkboxes">
                                    <label><?php echo 'Select Designations'; ?></label><small class="req"> *</small>
                                    <div class="checkbox-grid" id="designation-options">
                                        <!-- Checkboxes will be populated dynamically -->
                                    </div>
                                    <span class="text-danger"><?php echo form_error('selected_designations[]'); ?></span>
                                </div>

                                <!-- Hidden field for edit mode -->
                                <input type="hidden" name="designationid" value="<?php echo isset($result) ? $result["id"] : ''; ?>" />
                            </div>
                            <div class="box-footer">
                                <button type="submit" class="btn btn-info pull-right"><?php echo $this->lang->line('save'); ?></button>
                            </div>
                        </form>
                    </div>
                </div>
            <?php } ?>
            <div class="col-md-<?php
                if (($this->rbac->hasPrivilege('designation', 'can_add')) || ($this->rbac->hasPrivilege('designation', 'can_edit'))) {
                    echo "8";
                } else {
                    echo "12";
                }
            ?>">
                <div class="box box-primary" id="tachelist">
                    <div class="box-header ptbnull">
                        <h3 class="box-title titlefix"><?php echo $this->lang->line('designation_list'); ?></h3>
                    </div>
                    <div class="box-body">
                        <div class="mailbox-controls">
                        </div>
                        <div class="table-responsive mailbox-messages overflow-visible">
                            <div class="download_label"><?php echo $this->lang->line('designation_list'); ?></div>
                            <table class="table table-striped table-bordered table-hover example">
                                <thead>
                                    <tr>
                                        <th><?php echo 'Staff Type'; ?></th>
                                        <th><?php echo $this->lang->line('designation'); ?></th>
                                        <th class="text-right noExport"><?php echo $this->lang->line('action'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $count = 1;
                                    foreach ($designation as $value) {
                                        $status = "";
                                        if ($value["is_active"] == "yes") {
                                            $status = "Active";
                                        } else {
                                            $status = "Inactive";
                                        }
                                    ?>
                                        <tr>
                                            <td class="mailbox-name"><?php echo $value['staff_type'] ?></td>
                                            <td class="mailbox-name"><?php echo $value['designation'] ?></td>
                                            <td class="mailbox-date pull-right no-print">
                                                <?php if ($this->rbac->hasPrivilege('designation', 'can_edit')) { ?>
                                                    <a href="<?php echo base_url(); ?>admin/designation/designationedit/<?php echo $value['id'] ?>" class="btn btn-default btn-xs" data-toggle="tooltip" title="<?php echo $this->lang->line('edit'); ?>">
                                                        <i class="fa fa-pencil"></i>
                                                    </a>
                                                <?php } if ($this->rbac->hasPrivilege('designation', 'can_delete')) { ?>
                                                    <a href="<?php echo base_url(); ?>admin/designation/designationdelete/<?php echo $value['id'] ?>" class="btn btn-default btn-xs" data-toggle="tooltip" title="<?php echo $this->lang->line('delete'); ?>" onclick="return confirm('<?php echo $this->lang->line('delete_confirm') ?>');">
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
                    <div class="">
                        <div class="mailbox-controls">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<script>
// Designation options from PHP
var designationOptions = <?php echo $designation_options; ?>;

<?php if (isset($existing_designations)): ?>
// Existing designations for edit mode
var existingDesignations = <?php echo json_encode($existing_designations); ?>;
<?php else: ?>
var existingDesignations = [];
<?php endif; ?>

// Function to populate designation checkboxes
function populateDesignations(staffType) {
    var container = document.getElementById('designation-options');
    var checkboxContainer = document.getElementById('designation-checkboxes');
    
    // Clear existing checkboxes
    container.innerHTML = '';
    
    if (staffType && designationOptions[staffType]) {
        // Show the checkbox container
        checkboxContainer.classList.add('show');
        
        // Create checkboxes for the selected staff type
        designationOptions[staffType].forEach(function(designation) {
            var checkboxItem = document.createElement('div');
            checkboxItem.className = 'checkbox-item';
            
            var checkbox = document.createElement('input');
            checkbox.type = 'checkbox';
            checkbox.name = 'selected_designations[]';
            checkbox.value = designation;
            checkbox.id = 'designation_' + designation.replace(/\s+/g, '_').toLowerCase();
            
            // Check if this designation exists in the existing designations array
            if (existingDesignations.includes(designation)) {
                checkbox.checked = true;
            }
            
            var label = document.createElement('label');
            label.setAttribute('for', checkbox.id);
            label.textContent = designation;
            
            checkboxItem.appendChild(checkbox);
            checkboxItem.appendChild(label);
            container.appendChild(checkboxItem);
        });
    } else {
        // Hide the checkbox container
        checkboxContainer.classList.remove('show');
    }
}

// Event listener for staff type dropdown
document.getElementById('staff_type').addEventListener('change', function() {
    var selectedStaffType = this.value;
    populateDesignations(selectedStaffType);
});

// For edit mode - populate designations if staff type is already selected
document.addEventListener('DOMContentLoaded', function() {
    var staffTypeSelect = document.getElementById('staff_type');
    if (staffTypeSelect.value) {
        populateDesignations(staffTypeSelect.value);
    }
});

// Form validation
document.getElementById('form1').addEventListener('submit', function(e) {
    var staffType = document.getElementById('staff_type').value;
    var checkedBoxes = document.querySelectorAll('input[name="selected_designations[]"]:checked');
    
    if (!staffType) {
        alert('Please select a staff type.');
        e.preventDefault();
        return false;
    }
    
    if (checkedBoxes.length === 0) {
        alert('Please select at least one designation.');
        e.preventDefault();
        return false;
    }
    
    return true;
});
</script>