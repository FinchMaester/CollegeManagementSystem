<?php
if (!isset($resultlist)) {
    $resultlist = array();
}
if (!isset($disable_reason) || !is_array($disable_reason)) {
    $disable_reason = array();
}
?>
<div class="nav-tabs-custom border0">
    <ul class="nav nav-tabs">
        <li class="active"><a href="#tab_1" data-toggle="tab" aria-expanded="true"><i class="fa fa-list"></i> <?php echo $this->lang->line('list_view'); ?></a></li>
        <li class=""><a href="#tab_2" data-toggle="tab" aria-expanded="false"><i class="fa fa-newspaper-o"></i> <?php echo $this->lang->line('details_view'); ?></a></li>
    </ul>
    <div class="tab-content">
        <div class="download_label"> <?php echo $this->lang->line('disable_student_list'); ?></div>
        <div class="tab-pane active table-responsive no-padding overflow-visible" id="tab_1">
            <table class="table table-striped table-bordered table-hover example disablestudents-table" cellspacing="0" width="100%">
                <thead>
                    <tr>
                        <th><?php echo $this->lang->line('admission_no'); ?></th>
                        <th><?php echo $this->lang->line('student_name'); ?></th>
                        <th><?php echo $this->lang->line('class'); ?></th>
                        <?php if ($sch_setting->father_name) {?>
                            <th><?php echo $this->lang->line('father_name'); ?></th>
                        <?php }?>
                        <th><?php echo $this->lang->line('disable_reason'); ?></th>
                        <th><?php echo $this->lang->line('gender'); ?></th>
                        <?php if ($sch_setting->mobile_no) {?>
                            <th><?php echo $this->lang->line('mobile_number'); ?></th>
                        <?php }?>
                        <th class="pull-right noExport"><?php echo $this->lang->line('action'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if (empty($resultlist)) {
                        ?>
                        <tr><td colspan="9" class="text-center"><?php echo $this->lang->line('no_record_found'); ?></td></tr>
                        <?php
                    } else {
                        foreach ($resultlist as $student) {
                            $reason_id = isset($student['dis_reason']) ? $student['dis_reason'] : null;
                            ?>
                            <tr>
                                <td><?php echo $student['admission_no']; ?></td>
                                <td>
                                    <a href="<?php echo base_url(); ?>student/view/<?php echo $student['id']; ?>"><?php echo $this->customlib->getFullName($student['firstname'], $student['middlename'], $student['lastname'], $sch_setting->middlename, $sch_setting->lastname); ?></a>
                                </td>
                                <td><?php echo (isset($student['class']) ? htmlspecialchars($student['class']) : '') . (isset($student['section']) ? '(' . htmlspecialchars($student['section']) . ')' : ''); ?></td>
                                <?php if ($sch_setting->father_name) {?>
                                    <td><?php echo $student['father_name']; ?></td>
                                <?php }?>
                                <td><span data-toggle="popover" class="detail_popover" data-original-title="" title=""><?php
                                    if (!empty($reason_id) && isset($disable_reason[$reason_id]) && !empty($disable_reason[$reason_id]['reason'])) {
                                        echo htmlspecialchars($disable_reason[$reason_id]['reason']);
                                    } else {
                                        echo !empty($student['dis_note']) ? htmlspecialchars($student['dis_note']) : '&mdash;';
                                    }
                                    ?></span>
                                    <div class="fee_detail_popover" style="display: none"><?php echo isset($student['dis_note']) ? htmlspecialchars($student['dis_note']) : ''; ?></div></td>
                                <td><?php echo $this->lang->line(strtolower($student['gender'])); ?></td>
                                <?php if ($sch_setting->mobile_no) {?>
                                    <td><?php echo $student['mobileno']; ?></td>
                                <?php }?>
                                <td class="pull-right">
                                    <a href="<?php echo base_url(); ?>student/view/<?php echo $student['id'] ?>" class="btn btn-default btn-xs" data-toggle="tooltip" title="<?php echo $this->lang->line('view'); ?>">
                                        <i class="fa fa-reorder"></i> <?php echo $this->lang->line('view'); ?>
                                    </a>
                                </td>
                            </tr>
                            <?php
                        }
                    }
                    ?>
                </tbody>
            </table>
        </div>
        <div class="tab-pane" id="tab_2">
            <?php if (empty($resultlist)) { ?>
                <div class="alert alert-info"><?php echo $this->lang->line('no_record_found'); ?></div>
            <?php } else {
                foreach ($resultlist as $student) {
                    if (empty($student["image"])) {
                        $image = "uploads/student_images/no_image.png";
                    } else {
                        $image = $student['image'];
                    }
                    ?>
                    <div class="carousel-row">
                        <div class="slide-row">
                            <div class="carousel slide slide-carousel" data-ride="carousel">
                                <div class="carousel-inner">
                                    <div class="item active">
                                        <a href="<?php echo base_url(); ?>student/view/<?php echo $student['id'] ?>"> <img class="img-responsive img-thumbnail width150" alt="<?php echo $student["firstname"] . " " . $student["lastname"] ?>" src="<?php echo $this->media_storage->getImageURL($image); ?>"></a>
                                    </div>
                                </div>
                            </div>
                            <div class="slide-content">
                                <h4><a href="<?php echo base_url(); ?>student/view/<?php echo $student['id'] ?>"> <?php echo $this->customlib->getFullName($student['firstname'], $student['middlename'], $student['lastname'], $sch_setting->middlename, $sch_setting->lastname); ?></a></h4>
                                <div class="row">
                                    <div class="col-xs-6 col-md-6">
                                        <address>
                                            <strong><b><?php echo $this->lang->line('class'); ?>: </b><?php echo (isset($student['class']) ? htmlspecialchars($student['class']) : '') . (isset($student['section']) ? ' (' . htmlspecialchars($student['section']) . ')' : ''); ?></strong><br>
                                            <b><?php echo $this->lang->line('admission_no'); ?>: </b><?php echo $student['admission_no'] ?><br/>
                                            <b><?php echo $this->lang->line('date_of_birth'); ?>: <?php
                                            if ((!empty($student['dob'])) && ($student['dob'] != null)) {
                                                echo date($this->customlib->getSchoolDateFormat(), $this->customlib->dateyyyymmddTodateformat($student['dob']));
                                            }
                                            ?></b><br>
                                            <b><?php echo $this->lang->line('gender'); ?>:&nbsp;</b><?php echo $this->lang->line(strtolower($student['gender'])) ?><br>
                                        </address>
                                    </div>
                                    <div class="col-xs-6 col-md-6">
                                        <?php if ($sch_setting->local_identification_no) {?>
                                            <b><?php echo $this->lang->line('local_identification_number'); ?>:&nbsp;</b><?php echo $student['samagra_id'] ?><br>
                                        <?php }if ($sch_setting->guardian_name) {?>
                                            <b><?php echo $this->lang->line('guardian_name'); ?>:&nbsp;</b><?php echo $student['guardian_name'] ?><br>
                                        <?php }if ($sch_setting->guardian_phone) {?>
                                            <b><?php echo $this->lang->line('guardian_phone'); ?>: </b> <abbr title="Phone"><i class="fa fa-phone-square"></i>&nbsp;</abbr> <?php echo $student['guardian_phone'] ?><br>
                                        <?php }if ($sch_setting->current_address) {?>
                                            <b><?php echo $this->lang->line('current_address'); ?>:&nbsp;</b><?php echo $student['current_address'] ?> <?php echo $student['city'] ?><br>
                                        <?php }?>
                                    </div>
                                </div>
                            </div>
                            <div class="slide-footer">
                                <span class="pull-right buttons">
                                    <a href="<?php echo base_url(); ?>student/view/<?php echo $student['id'] ?>" class="btn btn-default btn-xs" data-toggle="tooltip" title="<?php echo $this->lang->line('view'); ?>">
                                        <i class="fa fa-reorder"></i>
                                    </a>
                                </span>
                            </div>
                        </div>
                    </div>
                    <?php
                }
            } ?>
        </div>
    </div>
</div>
