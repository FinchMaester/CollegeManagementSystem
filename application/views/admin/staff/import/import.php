<?php
$currency_symbol = $this->customlib->getSchoolCurrencyFormat();
?>
<div class="content-wrapper" style="min-height: 946px;">
    <section class="content-header">
        <h1>
            <i class="fa fa-book"></i> <?php //echo $this->lang->line('staff'); ?> <small><?php //echo $this->lang->line('student1'); ?></small></h1>
    </section>
    <!-- Main content -->
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-info" style="padding:5px;">
                    <div class="box-header with-border">
                        <h3 class="box-title"><?php echo $this->lang->line('staff_import'); ?></h3>
                        <div class="pull-right box-tools">
                            <a href="<?php echo site_url('admin/staff/exportformat') ?>">
                                <button class="btn btn-primary btn-sm"><i class="fa fa-download"></i> <?php echo $this->lang->line('download_sample_import_file'); ?></button>
                            </a>
                        </div>
                    </div>
                    <div class="box-body">
                        <?php
                        $import_res = $this->session->flashdata('staff_import_result');
                        if ($import_res !== false && is_array($import_res)) {
                            $success_n = isset($import_res['success_count']) ? (int) $import_res['success_count'] : 0;
                            $total_n = isset($import_res['total_rows']) ? (int) $import_res['total_rows'] : 0;
                            $failures = isset($import_res['failures']) && is_array($import_res['failures']) ? $import_res['failures'] : array();
                            $fail_n = count($failures);
                            if ($total_n === 0) {
                                echo '<div class="alert alert-info text-center">' . $this->lang->line('no_record_found') . '</div>';
                            } else {
                                $summary_class = ($fail_n === 0) ? 'alert-success' : (($success_n === 0) ? 'alert-danger' : 'alert-warning');
                                $summary_text = ($success_n === $total_n && $fail_n === 0)
                                    ? $this->lang->line('staff_import_all_success')
                                    : sprintf($this->lang->line('staff_import_summary'), $success_n, $total_n);
                                echo '<div class="alert ' . $summary_class . ' text-center">' . htmlspecialchars($summary_text, ENT_QUOTES, 'UTF-8') . '</div>';
                                if ($fail_n > 0) {
                                    echo '<div class="alert alert-danger"><strong>' . $this->lang->line('staff_import_failed_rows_title') . '</strong> (' . (int) $fail_n . ')</div>';
                                    echo '<div class="table-responsive" style="margin-bottom:15px;"><table class="table table-bordered table-striped table-condensed">';
                                    echo '<thead><tr><th>#</th><th>' . $this->lang->line('employee_id') . '</th><th>' . $this->lang->line('first_name') . '</th><th>' . $this->lang->line('email') . '</th><th>' . $this->lang->line('reason') . '</th></tr></thead><tbody>';
                                    foreach ($failures as $frow) {
                                        echo '<tr>';
                                        echo '<td>' . (int) $frow['row'] . '</td>';
                                        echo '<td>' . htmlspecialchars(isset($frow['employee_id']) ? $frow['employee_id'] : '', ENT_QUOTES, 'UTF-8') . '</td>';
                                        echo '<td>' . htmlspecialchars(isset($frow['name']) ? $frow['name'] : '', ENT_QUOTES, 'UTF-8') . '</td>';
                                        echo '<td>' . htmlspecialchars(isset($frow['email']) ? $frow['email'] : '', ENT_QUOTES, 'UTF-8') . '</td>';
                                        echo '<td>' . htmlspecialchars(isset($frow['reason']) ? $frow['reason'] : '', ENT_QUOTES, 'UTF-8') . '</td>';
                                        echo '</tr>';
                                    }
                                    echo '</tbody></table></div>';
                                }
                            }
                        } elseif ($this->session->flashdata('msg')) {
                            ?>
                        <div><?php echo $this->session->flashdata('msg'); ?></div>
                            <?php
                        }
                        ?>
                        <br/>
                        1. <?php echo $this->lang->line('import_staff_step1'); ?><br/>
                        2. <?php echo $this->lang->line('import_staff_step2'); ?><br/>

                        <hr/></div>
                    <div class="box-body table-responsive" style="overflow-x:auto;">
                        <table class="table table-striped table-bordered table-hover" id="sampledata">
                            <thead>
                                <tr>
                                    <?php
foreach ($field as $key => $value) {
    if ($value == 'staff_id' || $value == 'first_name' || $value == 'email' || $value == 'gender' || $value == 'date_of_birth') {
        $req = "<span class='text-red'>*</span>";
    } else {
        $req = "";
    }
    ?>
                                        <th><?php echo "<span>" . $this->lang->line($value) . "</span>" . $req; ?></th>
                                    <?php }?>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <?php foreach ($field as $key => $value) {
    ?>
                                        <td><?php echo "XYZ" ?></td>
                                    <?php }?>
                                </tr>
                            </tbody>

                        </table>
                    </div>
                    <hr/>
                    <form action="<?php echo site_url('admin/staff/import') ?>"  id="employeeform" name="employeeform" method="post" enctype="multipart/form-data">
                        <div class="box-body">
                            <?php echo $this->customlib->getCSRF(); ?>
                            <div class="row">
                                <div class="col-md-4">

                                    <div class="form-group">
                                        <label for="exampleInputEmail1"><?php echo $this->lang->line('role'); ?></label><small class="req"> *</small>
                                        <select  id="role" name="role" class="form-control" >
                                            <option value=""   ><?php echo $this->lang->line('select'); ?></option>
                                            <?php
foreach ($roles as $key => $role) {
    ?>
                                                <option value="<?php echo $role['id'] ?>" <?php echo set_select('role', $role['id'], set_value('role')); ?>><?php echo $role["name"] ?></option>
                                            <?php }
?>
                                        </select>
                                        <span class="text-danger"><?php echo form_error('role'); ?></span>
                                    </div>
                                </div>
                                <!-- <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="exampleInputEmail1"><?php echo $this->lang->line('designation'); ?></label>

                                        <select id="designation" name="designation" placeholder="" type="text" class="form-control" >
                                            <option value="select"><?php echo $this->lang->line('select') ?></option>
                                            <?php foreach ($designation as $key => $value) {
    ?>
                                                <option value="<?php echo $value["id"] ?>" <?php echo set_select('designation', $value['id'], set_value('designation')); ?> ><?php echo $value["designation"] ?></option>
                                            <?php }
?>
                                        </select>
                                        <span class="text-danger"><?php echo form_error('designation'); ?></span>
                                    </div>
                                </div> -->
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="exampleInputEmail1"><?php echo $this->lang->line('department'); ?></label>
                                        <select id="department" name="department" placeholder="" type="text" class="form-control" >
                                            <option value="select"><?php echo $this->lang->line('select') ?></option>
                                            <?php foreach ($department as $key => $value) {
    ?>
                                                <option value="<?php echo $value["id"] ?>" <?php echo set_select('department', $value['id'], set_value('department')); ?>><?php echo $value["department_name"] ?></option>
                                            <?php }
?>
                                        </select>
                                        <span class="text-danger"><?php echo form_error('department'); ?></span>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="exampleInputFile"><?php echo $this->lang->line('select_csv_file'); ?></label><small class="req"> *</small>
                                        <div><input class="filestyle form-control" type='file' name='file' id="file" size='20' />
                                            <span class="text-danger"><?php echo form_error('file'); ?></span></div>
                                    </div></div>
                                <div class="col-md-6 pt20">
                                    <button type="submit" class="btn btn-info pull-right"> <?php echo $this->lang->line('staff') . " " . $this->lang->line('import'); ?></button>
                                </div>
                            </div>
                        </div>
                    </form>
                    <div>
                    </div>
                </div>
                </section>
            </div>