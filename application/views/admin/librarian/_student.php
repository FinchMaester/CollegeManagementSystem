<div class="box box-primary">
    <div class="box-body box-profile">
        <img class="profile-user-img img-responsive img-circle" src="<?php
if (!empty($memberList->image)) {
    echo $this->media_storage->getImageURL($memberList->image);
} else {
    if ($memberList->gender == 'Female') {
        echo $this->media_storage->getImageURL("uploads/student_images/default_female.jpg");
    } elseif ($memberList->gender == 'Male') {
        echo $this->media_storage->getImageURL("uploads/student_images/default_male.jpg");
    }
}
?>" alt="User profile picture">

        <h3 class="profile-username text-center">
            <?php echo $this->customlib->getFullName($memberList->firstname, $memberList->middlename, $memberList->lastname, $sch_setting->middlename, $sch_setting->lastname); ?>
            <?php if (!empty($memberList->is_active) && strtolower((string) $memberList->is_active) === 'no') { ?>
                <br><span class="label label-danger" style="font-size:12px;margin-top:6px;display:inline-block;">Disabled</span>
            <?php } ?>
        </h3>
        <ul class="list-group list-group-unbordered">
            <li class="list-group-item">
                <b><?php echo $this->lang->line('member_id'); ?></b> <a class="pull-right text-aqua"><?php echo $memberList->lib_member_id ?></a>
            </li>
            <li class="list-group-item">
                <b><?php echo $this->lang->line('library_card_no'); ?></b> <a class="pull-right text-aqua"><?php echo $memberList->library_card_no ?></a>
            </li>
            <li class="list-group-item">
                <b><?php echo $this->lang->line('admission_no'); ?></b> <a class="pull-right text-aqua"><?php echo $memberList->admission_no ?></a>
            </li>
            <li class="list-group-item">
                <b><?php echo $this->lang->line('class'); ?></b>
                <a class="pull-right text-aqua"><?php echo !empty($memberList->class) ? html_escape($memberList->class) : '—'; ?></a>
            </li>
            <li class="list-group-item">
                <b><?php echo $this->lang->line('section'); ?></b>
                <a class="pull-right text-aqua"><?php echo !empty($memberList->section) ? html_escape($memberList->section) : '—'; ?></a>
            </li>
            <li class="list-group-item">
                <b><?php echo $this->lang->line('gender'); ?></b> <a class="pull-right text-aqua"><?php echo $this->lang->line(strtolower($memberList->gender)) ?></a>
            </li>
            <li class="list-group-item">
                <b><?php echo $this->lang->line('father_name'); ?></b> <a class="pull-right text-aqua"><?php echo !empty($memberList->father_name) ? html_escape($memberList->father_name) : '—'; ?></a>
            </li>
            <li class="list-group-item">
                <b><?php echo $this->lang->line('date_of_birth'); ?></b> <a class="pull-right text-aqua"><?php echo !empty($memberList->dob) ? $this->customlib->dateformat($memberList->dob) : '—'; ?></a>
            </li>
            <li class="list-group-item">
                <b><?php echo $this->lang->line('member_type'); ?></b> <a class="pull-right text-aqua"><?php echo $this->lang->line($memberList->member_type); ?></a>
            </li>
            <li class="list-group-item">
                <b><?php echo $this->lang->line('mobile_number'); ?></b>
                <a class="pull-right text-aqua">
                    <?php
                    $phone = '';
                    if (!empty($memberList->mobileno)) {
                        $phone = $memberList->mobileno;
                    } elseif (!empty($memberList->guardian_phone)) {
                        // Some student records store the primary phone in guardian_phone
                        $phone = $memberList->guardian_phone;
                    }
                    echo $phone !== '' ? html_escape($phone) : '—';
                    ?>
                </a>
            </li>
            <?php
            $lib_perm_addr = isset($memberList->permanent_address) ? trim((string) $memberList->permanent_address) : '';
            $lib_curr_addr = isset($memberList->current_address) ? trim((string) $memberList->current_address) : '';
            $lib_address_parts = array();
            if ($lib_perm_addr !== '') {
                $lib_address_parts[] = array(
                    'label' => $this->lang->line('permanent_address'),
                    'text'  => $lib_perm_addr,
                );
            }
            if ($lib_curr_addr !== '' && $lib_curr_addr !== $lib_perm_addr) {
                $lib_address_parts[] = array(
                    'label' => $this->lang->line('current_address'),
                    'text'  => $lib_curr_addr,
                );
            }
            if (empty($lib_address_parts)) {
                $lib_structured = array();
                if (!empty($memberList->permanent_house_no)) {
                    $lib_structured[] = trim((string) $memberList->permanent_house_no);
                }
                if (!empty($memberList->permanent_ward_no)) {
                    $lib_structured[] = $this->lang->line('ward') . ' ' . trim((string) $memberList->permanent_ward_no);
                }
                foreach (array('permanent_local_level', 'permanent_district', 'permanent_province') as $addr_field) {
                    if (!empty($memberList->$addr_field)) {
                        $lib_structured[] = trim((string) $memberList->$addr_field);
                    }
                }
                $lib_structured = array_values(array_filter($lib_structured, function ($v) {
                    return $v !== '';
                }));
                if (!empty($lib_structured)) {
                    $lib_address_parts[] = array(
                        'label' => $this->lang->line('address'),
                        'text'  => implode(', ', $lib_structured),
                    );
                }
            }
            foreach ($lib_address_parts as $lib_addr_row) {
                ?>
            <li class="list-group-item">
                <b><?php echo html_escape($lib_addr_row['label']); ?></b>
                <span class="pull-right text-aqua" style="max-width:62%;text-align:right;white-space:normal;word-break:break-word;line-height:1.4;">
                    <?php echo nl2br(html_escape($lib_addr_row['text'])); ?>
                </span>
            </li>
                <?php
            }
            ?>
             <li class="list-group-item ">
                <b><?php echo $this->lang->line('session_year'); ?></b> <a class="pull-right text-aqua"><?php echo $memberList->session_year ?></a>
            </li>
            <?php if ($sch_setting->student_barcode == 1) { ?>
                <li class="list-group-item listnoback">
                    <b><?php echo $this->lang->line('barcode'); ?></b>
                    <?php
                    $barcode_url = isset($member_barcode_url) ? trim((string) $member_barcode_url) : '';
                    if ($barcode_url === '' && !empty($memberList->admission_no)) {
                        $barcode_rel = $this->customlib->ensure_student_barcode($memberList->admission_no);
                        if ($barcode_rel !== '') {
                            $barcode_url = $this->media_storage->getImageURL($barcode_rel);
                        }
                    }
                    if ($barcode_url !== '') {
                        ?>
                    <div class="text-center" style="margin-top:8px;clear:both;">
                        <img src="<?php echo htmlspecialchars($barcode_url, ENT_QUOTES, 'UTF-8'); ?>"
                             alt="<?php echo htmlspecialchars($memberList->admission_no, ENT_QUOTES, 'UTF-8'); ?>"
                             style="max-width:100%;height:auto;display:inline-block;"/>
                        <div class="text-muted small"><?php echo htmlspecialchars($memberList->admission_no, ENT_QUOTES, 'UTF-8'); ?></div>
                    </div>
                        <?php
                    } else {
                        ?>
                    <span class="pull-right text-muted">—</span>
                        <?php
                    }
                    ?>
                </li>
            <?php } ?>
        </ul>
    </div>
</div>