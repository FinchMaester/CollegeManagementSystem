<?php
$currency_symbol = $this->customlib->getSchoolCurrencyFormat();
$normalizeStaffReportGender = function ($gender) {
    $gender = strtolower(trim((string) $gender));
    return in_array($gender, array('male', 'female', 'other'), true) ? $gender : 'other';
};
$school_date_format = $this->customlib->getSchoolDateFormat();
$formatStaffReportDate = function ($value) use ($school_date_format) {
    if ($value === null || $value === '' || $value === '0000-00-00') {
        return '';
    }
    $timestamp = strtotime((string) $value);
    return $timestamp ? date($school_date_format, $timestamp) : '';
};
$staffReportCell = function ($staff, $key) {
    return isset($staff[$key]) && $staff[$key] !== null && $staff[$key] !== '' ? html_escape($staff[$key]) : '';
};
$formatStaffReportUgcAddress = function ($staff, $prefix) {
    $parts = array();
    foreach (array('province', 'district', 'local_level') as $part) {
        $key = $prefix . '_' . $part;
        if (!empty($staff[$key])) {
            $parts[] = trim((string) $staff[$key]);
        }
    }
    $ward_key = ($prefix === 'ugc_p') ? 'permanent_ward_no' : 'temporary_ward_no';
    if (!empty($staff[$ward_key])) {
        $parts[] = 'Ward ' . $staff[$ward_key];
    }
    $house_key = ($prefix === 'ugc_p') ? 'permanent_house_no' : '';
    if ($house_key !== '' && !empty($staff[$house_key])) {
        $parts[] = trim((string) $staff[$house_key]);
    }
    $text_key = ($prefix === 'ugc_p') ? 'permanent_address' : 'local_address';
    if (!empty($staff[$text_key])) {
        $parts[] = trim((string) $staff[$text_key]);
    }
    return html_escape(implode(', ', array_filter($parts)));
};
?>
<style type="text/css">
    .text-left{text-align: left !important;}
</style>
<div class="content-wrapper">
    <section class="content-header">
        <h1>
            <i class="fa fa-bus"></i> <?php //echo $this->lang->line('transport'); ?></h1>
    </section>
    <!-- Main content -->
    <section class="content">
        <?php $this->load->view('reports/_human_resource'); ?>
        <div class="row">
            <!-- Staff by Ethnicity and Gender -->
            <div class="col-md-6">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-users"></i> <?php echo $this->lang->line('total_staff_by_ethnicity_gender'); ?></h3>
                        <div class="box-tools pull-right">
                            <button class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                        </div>
                    </div>
                    <div class="box-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th><?php echo $this->lang->line('ethnicity'); ?></th>
                                        <th><?php echo $this->lang->line('male'); ?></th>
                                        <th><?php echo $this->lang->line('female'); ?></th>
                                        <th><?php echo $this->lang->line('other'); ?></th>
                                        <th><?php echo $this->lang->line('total'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $ethnicities = array();
                                    $ethnicity_totals = array(
                                        'male' => 0,
                                        'female' => 0,
                                        'other' => 0,
                                        'total' => 0
                                    );

                                    // Group data by ethnicity
                                    foreach ($ethnicity_gender_stats as $stat) {
                                        $ethnicity = !empty($stat['ethnicity']) ? $stat['ethnicity'] : $this->lang->line('not_specified');
                                        $gender = $normalizeStaffReportGender(isset($stat['gender']) ? $stat['gender'] : '');
                                        
                                        if (!isset($ethnicities[$ethnicity])) {
                                            $ethnicities[$ethnicity] = array(
                                                'male' => 0,
                                                'female' => 0,
                                                'other' => 0,
                                                'total' => 0
                                            );
                                        }
                                        
                                        $ethnicities[$ethnicity][$gender] += (int) $stat['total'];
                                        $ethnicities[$ethnicity]['total'] += (int) $stat['total'];
                                        
                                        $ethnicity_totals[$gender] += (int) $stat['total'];
                                        $ethnicity_totals['total'] += (int) $stat['total'];
                                    }
                                    
                                    // Display data
                                    foreach ($ethnicities as $ethnicity => $counts) {
                                        ?>
                                        <tr>
                                            <td><?php echo $ethnicity; ?></td>
                                            <td><?php echo $counts['male']; ?></td>
                                            <td><?php echo $counts['female']; ?></td>
                                            <td><?php echo $counts['other']; ?></td>
                                            <td><strong><?php echo $counts['total']; ?></strong></td>
                                        </tr>
                                        <?php
                                    }
                                    ?>
                                    <tr class="bg-gray">
                                        <td><strong><?php echo $this->lang->line('total'); ?></strong></td>
                                        <td><strong><?php echo $ethnicity_totals['male']; ?></strong></td>
                                        <td><strong><?php echo $ethnicity_totals['female']; ?></strong></td>
                                        <td><strong><?php echo $ethnicity_totals['other']; ?></strong></td>
                                        <td><strong><?php echo $ethnicity_totals['total']; ?></strong></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Staff by Position, Job Type and Gender -->
            <div class="col-md-6">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-briefcase"></i> <?php echo $this->lang->line('total_staff_by_position_jobtype_gender'); ?></h3>
                        <div class="box-tools pull-right">
                            <button class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                        </div>
                    </div>
                    <div class="box-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th><?php echo $this->lang->line('position'); ?></th>
                                        <th><?php echo $this->lang->line('job_type'); ?></th>
                                        <th><?php echo $this->lang->line('male'); ?></th>
                                        <th><?php echo $this->lang->line('female'); ?></th>
                                        <th><?php echo $this->lang->line('other'); ?></th>
                                        <th><?php echo $this->lang->line('total'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $positions = array();
                                    $position_totals = array(
                                        'male' => 0,
                                        'female' => 0,
                                        'other' => 0,
                                        'total' => 0
                                    );
                                    
                                    // Group data by position and job type
                                    foreach ($position_jobtype_gender_stats as $stat) {
                                        $position = !empty($stat['position']) ? $stat['position'] : $this->lang->line('not_specified');
                                        $job_type = !empty($stat['job_type']) ? $stat['job_type'] : $this->lang->line('not_specified');
                                        $gender = $normalizeStaffReportGender(isset($stat['gender']) ? $stat['gender'] : '');
                                        
                                        $key = $position . '|' . $job_type;
                                        
                                        if (!isset($positions[$key])) {
                                            $positions[$key] = array(
                                                'position' => $position,
                                                'job_type' => $job_type,
                                                'male' => 0,
                                                'female' => 0,
                                                'other' => 0,
                                                'total' => 0
                                            );
                                        }
                                        
                                        $positions[$key][$gender] += (int) $stat['total'];
                                        $positions[$key]['total'] += (int) $stat['total'];
                                        
                                        $position_totals[$gender] += (int) $stat['total'];
                                        $position_totals['total'] += (int) $stat['total'];
                                    }
                                    
                                    // Display data
                                    foreach ($positions as $key => $counts) {
                                        ?>
                                        <tr>
                                            <td><?php echo $counts['position']; ?></td>
                                            <td><?php echo $counts['job_type']; ?></td>
                                            <td><?php echo $counts['male']; ?></td>
                                            <td><?php echo $counts['female']; ?></td>
                                            <td><?php echo $counts['other']; ?></td>
                                            <td><strong><?php echo $counts['total']; ?></strong></td>
                                        </tr>
                                        <?php
                                    }
                                    ?>
                                    <tr class="bg-gray">
                                        <td colspan="2"><strong><?php echo $this->lang->line('total'); ?></strong></td>
                                        <td><strong><?php echo $position_totals['male']; ?></strong></td>
                                        <td><strong><?php echo $position_totals['female']; ?></strong></td>
                                        <td><strong><?php echo $position_totals['other']; ?></strong></td>
                                        <td><strong><?php echo $position_totals['total']; ?></strong></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="box removeboxmius">
                    <div class="box-header ptbnull"></div>
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-search"></i> <?php echo $this->lang->line('select_criteria'); ?></h3>
                    </div>
                    <form role="form" action="<?php echo site_url('report/staff_report') ?>" method="post" class="">
                        <div class="box-body row">
                            <?php echo $this->customlib->getCSRF(); ?>
                            <div class="col-sm-3 col-md-3">
                                <div class="form-group">
                                    <label><?php echo $this->lang->line('search_type_by_date_of_joining'); ?></label>
                                    <select class="form-control" name="search_type" onchange="showdate(this.value)">
                                        <?php foreach ($searchlist as $key => $search) {
                                            ?>
                                            <option value="<?php echo $key ?>" <?php
                                            if ((isset($search_type)) && ($search_type == $key)) {
                                                echo "selected";
                                            }
                                            ?>><?php echo $search ?></option>
                                                <?php } ?>
                                    </select>
                                    <span class="text-danger"><?php echo form_error('search_type'); ?></span>
                                </div>
                            </div>
                            <div class="col-sm-3 col-md-3">
                                <div class="form-group">
                                    <label><?php echo $this->lang->line('status') ?></label>
                                    <select class="form-control" name="staff_status">
                                        <?php
                                        foreach ($status as $status_key => $status_value) {
                                            ?>
                                            <option value="<?php echo $status_key ?>" <?php
                                            if ((isset($status_val)) && ($status_val == $status_key)) {
                                                echo "selected";
                                            }
                                            ?>><?php echo $status_value ?></option>
                                                <?php } ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-sm-3 col-md-3">
                                <div class="form-group">
                                    <label><?php echo $this->lang->line('role'); ?></label>
                                    <select class="form-control" name="role" >
                                        <option value=""><?php echo $this->lang->line('select') ?></option>
                                        <?php foreach ($roles as $value) {
                                            ?>
                                            <option value="<?php echo $value['id'] ?>" <?php
                                            if ((isset($role_val)) && ($role_val == $value['id'])) {
                                                echo "selected";
                                            }
                                            ?>><?php echo $value['name'] ?></option>
                                                <?php } ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-sm-3 col-md-3">
                                <div class="form-group">
                                    <label><?php echo $this->lang->line('designation'); ?></label>
                                    <select class="form-control" name="designation">
                                        <option value=""><?php echo $this->lang->line('select'); ?></option>
                                        <?php foreach ($designation as $value) {
                                            ?>
                                            <option value="<?php echo $value['id'] ?>" <?php
                                            if ((isset($designation_val)) && ($designation_val == $value['id'])) {
                                                echo "selected";
                                            }
                                            ?>><?php echo $value['designation'] ?></option>
                                                <?php } ?>
                                    </select>
                                    <span class="text-danger"><?php echo form_error('search_type'); ?></span>
                                </div>
                            </div>
                            <div id='date_result'>
                            </div>
                            <div class="form-group">
                                <div class="col-sm-12">
                                    <button type="submit" name="search" value="search_filter" class="btn btn-primary btn-sm checkbox-toggle pull-right"><i class="fa fa-search"></i> <?php echo $this->lang->line('search'); ?></button>
                                </div>
                            </div>
                        </div>
                    </form>
                    <div class="">
                        <div class="box-header ptbnull"></div>
                        <div class="box-header ptbnull">
                            <h3 class="box-title titlefix"><i class="fa fa-money"></i> <?php echo $this->lang->line('staff_report'); ?></h3>
                        </div>
                        <div class="box-body table-responsive">
                            <div class="download_label"><?php echo $this->lang->line('staff_report').' '.$this->customlib->get_postmessage();  ?></div>
                            <table class="table table-striped table-bordered table-hover example ">
                                <thead>
                                    <tr>
                                        <th><?php echo $this->lang->line('staff_id'); ?></th>
                                        <th>HEMIS Employee ID</th>
                                        <th><?php echo $this->lang->line('role'); ?></th>
                                        <th><?php echo $this->lang->line('designation'); ?></th>
                                        <th><?php echo $this->lang->line('department'); ?></th>
                                        <th><?php echo $this->lang->line('name'); ?></th>
                                        <th><?php echo $this->lang->line('salutation'); ?></th>
                                        <th><?php echo $this->lang->line('email'); ?></th>
                                        <th><?php echo $this->lang->line('gender'); ?></th>
                                        <th><?php echo $this->lang->line('ethnicity'); ?></th>
                                        <th><?php echo $this->lang->line('date_of_birth'); ?></th>
                                        <th><?php echo $this->lang->line('date_of_joining'); ?></th>
                                        <th><?php echo $this->lang->line('phone'); ?></th>
                                        <th><?php echo $this->lang->line('marital_status'); ?></th>
                                        <th><?php echo $this->lang->line('contract_type'); ?></th>
                                        <th>Employee Type (HEMIS)</th>
                                        <th>Post Name (HEMIS)</th>
                                        <th>Position (HEMIS)</th>
                                        <th>Position Type (HEMIS)</th>
                                        <th>Citizenship No (HEMIS)</th>
                                        <th><?php echo $this->lang->line('pan_no'); ?></th>
                                        <th><?php echo $this->lang->line('permanent_address'); ?> (HEMIS)</th>
                                        <th><?php echo $this->lang->line('current_address'); ?> (HEMIS)</th>
                                        <th>Graduated From (HEMIS)</th>
                                        <th>Program Name (HEMIS)</th>
                                        <th>Level Name (HEMIS)</th>
                                        <th>Graduated Faculty (HEMIS)</th>
                                        <th>Teaching Faculty (HEMIS)</th>
                                        <th>Enrolled Year (HEMIS)</th>
                                        <th>Passed Year (HEMIS)</th>
                                        <th><?php echo $this->lang->line('qualification'); ?></th>
                                            <?php
                                            if (!empty($fields)) {
                                                foreach ($fields as $fields_key => $fields_value) {
                                                    ?>
                                                <th><?php echo $fields_value->name; ?></th>
                                                <?php
                                                }
                                            }
                                            ?>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    if (empty($resultlist)) {
                                        ?>
                                        <?php
                                    } else {
                                        foreach ($resultlist as $staff) {
                                            ?>
                                            <tr>
                                                <td><?php echo $staffReportCell($staff, 'employee_id'); ?></td>
                                                <td><?php echo $staffReportCell($staff, 'hemis_employee_id'); ?></td>
                                                <td><?php echo $staffReportCell($staff, 'user_type'); ?></td>
                                                <td><?php echo $staffReportCell($staff, 'designation'); ?></td>
                                                <td><?php echo $staffReportCell($staff, 'department'); ?></td>
                                                <td><?php echo html_escape(trim($staff['name'] . ' ' . $staff['surname'])); ?></td>
                                                <td><?php echo $staffReportCell($staff, 'salutation'); ?></td>
                                                <td><?php echo $staffReportCell($staff, 'email'); ?></td>
                                                <td><?php echo $this->lang->line($normalizeStaffReportGender(isset($staff['gender']) ? $staff['gender'] : '')); ?></td>
                                                <td><?php echo $staffReportCell($staff, 'caste'); ?></td>
                                                <td><?php echo $formatStaffReportDate(isset($staff['dob']) ? $staff['dob'] : ''); ?></td>
                                                <td><?php echo $formatStaffReportDate(isset($staff['date_of_joining']) ? $staff['date_of_joining'] : ''); ?></td>
                                                <td><?php echo $staffReportCell($staff, 'contact_no'); ?></td>
                                                <td><?php echo $staffReportCell($staff, 'marital_status'); ?></td>
                                                <td><?php echo $staffReportCell($staff, 'contract_type'); ?></td>
                                                <td><?php echo $staffReportCell($staff, 'employee_type'); ?></td>
                                                <td><?php echo $staffReportCell($staff, 'post_name'); ?></td>
                                                <td><?php echo $staffReportCell($staff, 'position'); ?></td>
                                                <td><?php echo $staffReportCell($staff, 'position_type'); ?></td>
                                                <td><?php echo $staffReportCell($staff, 'citizenship_no'); ?></td>
                                                <td><?php echo $staffReportCell($staff, 'pan_no'); ?></td>
                                                <td><?php echo $formatStaffReportUgcAddress($staff, 'ugc_p'); ?></td>
                                                <td><?php echo $formatStaffReportUgcAddress($staff, 'ugc_t'); ?></td>
                                                <td><?php echo $staffReportCell($staff, 'graduated_from'); ?></td>
                                                <td><?php echo $staffReportCell($staff, 'program_name'); ?></td>
                                                <td><?php echo $staffReportCell($staff, 'level_name'); ?></td>
                                                <td><?php echo $staffReportCell($staff, 'faculty_name'); ?></td>
                                                <td><?php echo $staffReportCell($staff, 'teaching_faculty_name'); ?></td>
                                                <td><?php echo $staffReportCell($staff, 'enrolled_year'); ?></td>
                                                <td><?php echo $staffReportCell($staff, 'passed_year'); ?></td>
                                                <td><?php echo $staffReportCell($staff, 'qualification'); ?></td>
                                                <?php
                                                if (!empty($fields)) {
                                                    foreach ($fields as $fields_key => $fields_value) {
                                                        $display_field = isset($staff[$fields_value->name]) ? $staff[$fields_value->name] : '';
                                                        if ($fields_value->type == "link" && $display_field !== '') {
                                                            $display_field = "<a href=" . html_escape($display_field) . " target='_blank'>" . html_escape($display_field) . "</a>";
                                                        } else {
                                                            $display_field = html_escape($display_field);
                                                        }
                                                        ?>
                                                        <td><?php echo $display_field; ?></td>
                                                        <?php
                                                    }
                                                }
                                                ?>
                                            </tr>
                                            <?php
                                        }
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>   
    </div>  
</section>
</div>

<script>
<?php
if ($search_type == 'period') {
    ?>
        $(document).ready(function () {
            showdate('period');
        });
    <?php
}
?>
</script>