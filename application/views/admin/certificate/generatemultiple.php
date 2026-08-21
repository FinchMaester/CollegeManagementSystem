<?php
/**
 * Improved Multiple ID Cards Template
 * $id_card[0], $students, $sch_setting, $sch_settingdata expected.
 * Better error handling and fallback images.
 */
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Student ID Cards</title>

<style type="text/css">
/* ===== Print-safe base ===== */
@media print {
  .page-break{display:block;page-break-before:always;}
  .idcard-wrapper{page-break-inside:avoid;}
}

/* Reset + base */
*{margin:0;padding:0;box-sizing:border-box}
table{border-collapse:collapse}
html,body{font-family:Arial,Helvetica,sans-serif;color:#222}
img{border:0;}

/* Layout helpers */
.tc-container{width:100%;position:relative;text-align:center;margin-bottom:60px;padding-bottom:10px;}
.idcard-wrapper{width:100%;padding:20px;display:block;text-align:center;}

/* ===== HORIZONTAL CARD (default branch) ===== */
.idcard-h{
  width: 680px;
  margin: 0 auto 20px auto;
  background:#fff;
  border:1px solid #dcdcdc;
  border-radius:4px;
  padding:18px 22px 22px 22px;
  position:relative;
  text-align:left;
}

/* header */
.hdr{
  display:flex;
  align-items:flex-start;
  gap:8px;
  margin-bottom:10px;
}
.hdr-logo{
  width:26px;height:26px;object-fit:contain;display:inline-block;margin-top:2px;
}

.hdr-right{}
.sch-name{
  font-size:15px;
  font-weight:700;
  color:#6f7b88;
  line-height:1.2;
}
.sch-addr{
  font-size:11px;
  color:#8893a0;
  margin-top:2px;
}

/* title */
.card-title{
  text-transform:uppercase;
  letter-spacing:.22em;
  font-size:12px;
  color:#9aa5b1;
  margin:14px 0 12px 0;
}

/* body grid */
.h-body{
  display:grid;
  grid-template-columns: 130px 1fr;
  gap:18px;
  align-items:start;
  position:relative;
}

/* photo box */
.photo-box{
  width:110px;height:110px;border:1px solid #e3e3e3;border-radius:4px;display:flex;align-items:center;justify-content:center;background:#fafafa;
  overflow:hidden;
}

.photo-box img{width:100%;height:100%;object-fit:cover}

/* watermark centered */
.wm{
  position:absolute;inset:0;display:flex;align-items:center;justify-content:center;pointer-events:none;
}
.wm img{
  width:220px;height:220px;object-fit:contain;opacity:.085;filter:grayscale(100%);
}

/* details */
.detail-list{width:100%;}
.detail-list tr td{padding:6px 6px;font-size:12px;vertical-align:top;}
.detail-label{width:140px;color:#333;font-weight:700;}
.detail-value{color:#000;}

/* signature box on right side */
.sign-wrap{
  position:absolute;
  right:6px;
  bottom:6px;
  width:170px;height:44px;
  border:1px solid #dddddd;border-radius:3px;
  display:flex;align-items:center;justify-content:center;
  background:#fff;
} 
.sign-wrap img{max-width:160px;max-height:34px;object-fit:contain}

/* barcode box under signature if present */
.barcode-wrap{
  position:relative;
  margin-top:8px;
  width:180px;height:26px;
  border:1px solid #e5e5e5;border-radius:3px;
  display:flex;align-items:center;justify-content:center;
}

/* ===== VERTICAL CARD (enable_vertical_card == true) ===== */
.idcard-v{
  width: 300px;
  margin: 0 auto 20px auto;
  background:#fff;
  border:1px solid #dcdcdc;
  border-radius:4px;
  overflow:hidden;
  position:relative;
  text-align:center;
  display:inline-block;
}

/* colored top bar uses header_color */
.v-top{
  color:#fff;
  padding:10px 8px 8px 8px;
}
.v-top .row1{display:flex;align-items:center;justify-content:center;gap:6px;}
.v-top .row1 img{width:24px;height:24px;object-fit:contain}
.v-sch-name{font-size:14px;font-weight:700;line-height:1.2}
.v-addr{font-size:11px;opacity:.9;margin-top:2px}

/* avatar */
.v-photo{
  margin-top:-24px;
  display:flex;align-items:center;justify-content:center;
}
.v-photo .img{
  width:88px;height:88px;border-radius:10px;border:3px solid #fff;overflow:hidden;background:#f5f5f5;
}
.v-photo .img img{width:100%;height:100%;object-fit:cover}

/* name */
.v-name{margin-top:8px;font-weight:800;text-transform:uppercase;font-size:13px;}

/* vertical details list */
.vertlist{padding:10px 12px;list-style:none;text-align:left}
.vertlist li{font-size:12px;color:#000;padding:3px 0;border-bottom:1px dashed #eee}
.vertlist li:last-child{border-bottom:0}
.vertlist li b{display:inline-block;width:48%;font-weight:700}
.vertlist li span{display:inline-block;width:50%;text-align:right}

/* signature + barcode */
.v-sign{margin:8px auto 10px auto;border:1px solid #ddd;width:160px;height:30px;display:flex;align-items:center;justify-content:center;border-radius:3px;}
.v-sign img{max-width:150px;max-height:24px;object-fit:contain}
.v-bar{margin:6px auto 12px auto;border:1px solid #e5e5e5;width:180px;height:26px;display:flex;align-items:center;justify-content:center;border-radius:3px;}
.v-bar img{max-height:20px;object-fit:contain}

@media screen {
    body { padding: 20px; background: #f5f5f5; }
    .print-instructions { 
        background: #e3f2fd; 
        padding: 15px; 
        margin-bottom: 20px; 
        border-radius: 5px;
        border-left: 4px solid #2196F3;
    }
}
.error-message{color:#d32f2f;font-size:11px;font-style:italic}
</style>
</head>
<body>

<div class="sheet">
    <div class="cards">

        <?php
        // Template asset paths/URLs with better error handling
        $bgRel   = isset($id_card[0]->background) ? 'uploads/student_id_card/background/'.$id_card[0]->background : '';
        $logoRel = isset($id_card[0]->logo) ? 'uploads/student_id_card/logo/'.$id_card[0]->logo : '';
        $signRel = isset($id_card[0]->sign_image) ? 'uploads/student_id_card/signature/'.$id_card[0]->sign_image : '';

        $bgUrl   = $bgRel ? base_url($bgRel) : '';
        $logo    = $logoRel ? base_url($logoRel) : '';
        $sign    = $signRel ? base_url($signRel) : '';

        // Barcode enabled check with better fallbacks
        $barcode_enabled = 0;
        if (isset($sch_setting) && is_array($sch_setting) && isset($sch_setting[0]['student_barcode'])) {
            $barcode_enabled = (int)$sch_setting[0]['student_barcode'];
        } elseif (isset($sch_settingdata) && is_object($sch_settingdata) && isset($sch_settingdata->student_barcode)) {
            $barcode_enabled = (int)$sch_settingdata->student_barcode;
        }

        // Safety check for students array
        if (!isset($students) || !is_array($students)) {
            echo '<div class="error-message">No student data available.</div>';
        } else {
            // Check if vertical card is enabled
            // Explicitly cast to int to handle database values (1/0, "1"/"0", null)
            // This prevents string "0" from being truthy in PHP
            $enable_vertical_val = isset($id_card[0]->enable_vertical_card) ? (int)$id_card[0]->enable_vertical_card : 0;
            $is_vertical = ($enable_vertical_val === 1);
            
            if ($is_vertical) {
                // ========= VERTICAL CARDS ========= //
                $card_count = 0;
                foreach ($students as $student):
                    // Safety checks for student object
                    if (!is_object($student)) {
                        continue;
                    }
                    
                    $card_count++;
                    
                    // Add page break before every 3rd card (2 cards per page)
                    if ($card_count > 1 && ($card_count - 1) % 2 == 0) {
                        echo '<div class="page-break"></div>';
                    }
                    
                    // Student photo with gender fallback
                    $stuImgUrl = '';
                    if (!empty($student->image)) {
                        $stuImgUrl = base_url($student->image);
                    } else {
                        $gender = isset($student->gender) ? strtolower($student->gender) : '';
                        if ($gender === 'female') {
                            $stuImgUrl = base_url('uploads/student_images/default_female.jpg');
                        } else {
                            $stuImgUrl = base_url('uploads/student_images/default_male.jpg');
                        }
                    }
                    
                    // Barcode URL
                    $barcodeUrl = '';
                    if (!empty($student->barcode)) {
                        $barcodeUrl = base_url($student->barcode);
                    }
                    
                    // Class and section handling
                    $cls = isset($student->class) ? $student->class : '';
                    $sec = isset($student->section) ? $student->section : '';
                    $classSec = trim($cls.(($cls && $sec)?' - ':'').$sec);
                    
                    // Safe property access
                    $firstName = isset($student->firstname) ? $student->firstname : '';
                    $middleName = isset($student->middlename) ? $student->middlename : '';
                    $lastName = isset($student->lastname) ? $student->lastname : '';
                    $fullName = trim($firstName.' '.$middleName.' '.$lastName);
                    
                    $admissionNo = isset($student->admission_no) ? $student->admission_no : '';
                    $programName = isset($student->program_name) ? $student->program_name : '';
                    $fatherName = isset($student->father_name) ? $student->father_name : '';
                    $motherName = isset($student->mother_name) ? $student->mother_name : '';
                    $currentAddress = isset($student->current_address) ? $student->current_address : '';
                    $mobileNo = isset($student->mobileno) ? $student->mobileno : '';
                    $dob = isset($student->dob) ? $student->dob : '';
                    $bloodGroup = isset($student->blood_group) ? $student->blood_group : '';
        ?>
        
        <div class="idcard-wrapper">
            <div class="idcard-v">
                <!-- Top colored bar -->
                <div class="v-top" style="background: <?php echo isset($id_card[0]->header_color) ? $id_card[0]->header_color : '#4a90e2'; ?>">
                    <div class="row1">
                        <?php if ($logo): ?>
                        <img src="<?php echo $logo; ?>" alt="">
                        <?php endif; ?>
                        <div class="v-sch-name"><?php echo html_escape(isset($id_card[0]->school_name) ? $id_card[0]->school_name : 'School Name'); ?></div>
                    </div>
                    <div class="v-addr"><?php echo html_escape(isset($id_card[0]->school_address) ? $id_card[0]->school_address : ''); ?></div>
                </div>
                
                <!-- Photo -->
                <div class="v-photo">
                    <div class="img">
                        <img src="<?php echo $stuImgUrl; ?>" alt="">
                    </div>
                </div>
                
                <!-- Name -->
                <div class="v-name"><?php echo strtoupper(html_escape($fullName)); ?></div>
                
                <!-- Details -->
                <ul class="vertlist">
                    <?php if (isset($id_card[0]->enable_admission_no) && $id_card[0]->enable_admission_no == 1) { ?>
                        <li><b><?php echo $this->lang->line('admission_no'); ?></b><span><?php echo html_escape($admissionNo); ?></span></li>
                    <?php } ?>
                    <?php if (isset($id_card[0]->enable_program) && $id_card[0]->enable_program == 1 && $programName) { ?>
                        <li><b><?php echo $this->lang->line('program'); ?></b><span><?php echo html_escape($programName); ?></span></li>
                    <?php } ?>
                    <?php if (isset($id_card[0]->enable_class) && $id_card[0]->enable_class == 1) { ?>
                        <li><b><?php echo $this->lang->line('class'); ?></b><span><?php echo html_escape($classSec); ?></span></li>
                    <?php } ?>
                    <?php if (isset($id_card[0]->enable_fathers_name) && $id_card[0]->enable_fathers_name == 1) { ?>
                        <li><b><?php echo $this->lang->line('father_name'); ?></b><span><?php echo html_escape($fatherName); ?></span></li>
                    <?php } ?>
                    <?php if (isset($id_card[0]->enable_mothers_name) && $id_card[0]->enable_mothers_name == 1) { ?>
                        <li><b><?php echo $this->lang->line('mother_name'); ?></b><span><?php echo html_escape($motherName); ?></span></li>
                    <?php } ?>
                    <?php if (isset($id_card[0]->enable_address) && $id_card[0]->enable_address == 1) { ?>
                        <li><b><?php echo $this->lang->line('address'); ?></b><span><?php echo html_escape($currentAddress); ?></span></li>
                    <?php } ?>
                    <?php if (isset($id_card[0]->enable_phone) && $id_card[0]->enable_phone == 1) { ?>
                        <li><b><?php echo $this->lang->line('phone'); ?></b><span><?php echo html_escape($mobileNo); ?></span></li>
                    <?php } ?>
                    <?php if (isset($id_card[0]->enable_dob) && $id_card[0]->enable_dob == 1) { ?>
                        <li><b><?php echo $this->lang->line('d_o_b'); ?></b><span><?php echo html_escape(($dob && $dob != '0000-00-00') ? $dob : ''); ?></span></li>
                    <?php } ?>
                    <?php if (isset($id_card[0]->enable_blood_group) && $id_card[0]->enable_blood_group == 1) { ?>
                        <li><b><?php echo $this->lang->line('blood_group'); ?></b><span><?php echo html_escape($bloodGroup); ?></span></li>
                    <?php } ?>
                </ul>
                
                <!-- Signature -->
                <?php if ($sign): ?>
                <div class="v-sign">
                    <img src="<?php echo $sign; ?>" alt="">
                </div>
                <?php endif; ?>
                
                <!-- Barcode (optional) -->
                <?php if (isset($id_card[0]->enable_student_barcode) && $id_card[0]->enable_student_barcode == 1 && $barcode_enabled == 1 && $barcodeUrl) { ?>
                    <div class="v-bar">
                        <img src="<?php echo $barcodeUrl; ?>" alt="">
                    </div>
                <?php } ?>
            </div>
        </div>
        
        <?php 
                endforeach;
            } else {
                // ========= HORIZONTAL CARDS =========
                $card_count = 0;
                foreach ($students as $student):
                    // Safety checks for student object
                    if (!is_object($student)) {
                        continue;
                    }
                    
                    $card_count++;
                    
                    // Add page break before every 3rd card (2 cards per page)
                    if ($card_count > 1 && ($card_count - 1) % 2 == 0) {
                        echo '<div class="page-break"></div>';
                    }
                    
                    // Student photo with gender fallback
                    $stuImgUrl = '';
                    if (!empty($student->image)) {
                        $stuImgUrl = base_url($student->image);
                    } else {
                        $gender = isset($student->gender) ? strtolower($student->gender) : '';
                        if ($gender === 'female') {
                            $stuImgUrl = base_url('uploads/student_images/default_female.jpg');
                        } else {
                            $stuImgUrl = base_url('uploads/student_images/default_male.jpg');
                        }
                    }
                    
                    // Barcode URL
                    $barcodeUrl = '';
                    if (!empty($student->barcode)) {
                        $barcodeUrl = base_url($student->barcode);
                    }
                    
                    // Class and section handling
                    $cls = isset($student->class) ? $student->class : '';
                    $sec = isset($student->section) ? $student->section : '';
                    $classSec = trim($cls.(($cls && $sec)?' - ':'').$sec);
                    
                    // Safe property access
                    $firstName = isset($student->firstname) ? $student->firstname : '';
                    $middleName = isset($student->middlename) ? $student->middlename : '';
                    $lastName = isset($student->lastname) ? $student->lastname : '';
                    $fullName = trim($firstName.' '.$middleName.' '.$lastName);
                    
                    $admissionNo = isset($student->admission_no) ? $student->admission_no : '';
                    $programName = isset($student->program_name) ? $student->program_name : '';
                    $fatherName = isset($student->father_name) ? $student->father_name : '';
                    $motherName = isset($student->mother_name) ? $student->mother_name : '';
                    $currentAddress = isset($student->current_address) ? $student->current_address : '';
                    $mobileNo = isset($student->mobileno) ? $student->mobileno : '';
                    $dob = isset($student->dob) ? $student->dob : '';
                    $bloodGroup = isset($student->blood_group) ? $student->blood_group : '';
        ?>
        
        <div class="idcard-wrapper">
            <div class="idcard-h">
                <!-- Header -->
                <div class="hdr">
                    <?php if ($logo): ?>
                    <img class="hdr-logo" src="<?php echo $logo; ?>" alt="">
                    <?php endif; ?>
                    <div class="hdr-right">
                        <div class="sch-name"><?php echo html_escape(isset($id_card[0]->school_name) ? $id_card[0]->school_name : 'School Name'); ?></div>
                        <div class="sch-addr"><?php echo html_escape(isset($id_card[0]->school_address) ? $id_card[0]->school_address : ''); ?></div>
                    </div>
                </div>
                
                <!-- Title -->
                <div class="card-title">
                    <?php echo strtoupper(html_escape(isset($id_card[0]->title) ? $id_card[0]->title : 'Student ID Card')); ?>
                </div>
                
                <!-- Body -->
                <div class="h-body">
                    <!-- watermark -->
                    <?php if ($bgUrl): ?>
                    <div class="wm">
                        <img src="<?php echo $bgUrl; ?>" alt="">
                    </div>
                    <?php endif; ?>
                    
                    <!-- photo -->
                    <div class="photo-box">
                        <img src="<?php echo $stuImgUrl; ?>" alt="">
                    </div>
                    
                    <!-- details table -->
                    <table class="detail-list">
                        <tbody>
                            <?php if (isset($id_card[0]->enable_student_name) && $id_card[0]->enable_student_name == 1) { ?>
                            <tr><td class="detail-label"><?php echo $this->lang->line('student_name'); ?></td><td class="detail-value"><?php echo html_escape($fullName); ?></td></tr>
                            <?php } ?>
                            <?php if (isset($id_card[0]->enable_admission_no) && $id_card[0]->enable_admission_no == 1) { ?>
                            <tr><td class="detail-label"><?php echo $this->lang->line('admission_no'); ?></td><td class="detail-value"><?php echo html_escape($admissionNo); ?></td></tr>
                            <?php } ?>
                            <?php if (isset($id_card[0]->enable_program) && $id_card[0]->enable_program == 1 && $programName) { ?>
                            <tr><td class="detail-label"><?php echo $this->lang->line('program'); ?></td><td class="detail-value"><?php echo html_escape($programName); ?></td></tr>
                            <?php } ?>
                            <?php if (isset($id_card[0]->enable_class) && $id_card[0]->enable_class == 1) { ?>
                            <tr><td class="detail-label"><?php echo $this->lang->line('class'); ?></td><td class="detail-value"><?php echo html_escape($classSec); ?></td></tr>
                            <?php } ?>
                            <?php if (isset($id_card[0]->enable_fathers_name) && $id_card[0]->enable_fathers_name == 1) { ?>
                            <tr><td class="detail-label"><?php echo $this->lang->line('father_name'); ?></td><td class="detail-value"><?php echo html_escape($fatherName); ?></td></tr>
                            <?php } ?>
                            <?php if (isset($id_card[0]->enable_mothers_name) && $id_card[0]->enable_mothers_name == 1) { ?>
                            <tr><td class="detail-label"><?php echo $this->lang->line('mother_name'); ?></td><td class="detail-value"><?php echo html_escape($motherName); ?></td></tr>
                            <?php } ?>
                            <?php if (isset($id_card[0]->enable_address) && $id_card[0]->enable_address == 1) { ?>
                            <tr><td class="detail-label"><?php echo $this->lang->line('address'); ?></td><td class="detail-value"><?php echo html_escape($currentAddress); ?></td></tr>
                            <?php } ?>
                            <?php if (isset($id_card[0]->enable_phone) && $id_card[0]->enable_phone == 1) { ?>
                            <tr><td class="detail-label"><?php echo $this->lang->line('phone'); ?></td><td class="detail-value"><?php echo html_escape($mobileNo); ?></td></tr>
                            <?php } ?>
                            <?php if (isset($id_card[0]->enable_dob) && $id_card[0]->enable_dob == 1) { ?>
                            <tr><td class="detail-label"><?php echo $this->lang->line('d_o_b'); ?></td><td class="detail-value"><?php echo html_escape(($dob && $dob != '0000-00-00') ? $dob : ''); ?></td></tr>
                            <?php } ?>
                            <?php if (isset($id_card[0]->enable_blood_group) && $id_card[0]->enable_blood_group == 1) { ?>
                            <tr><td class="detail-label"><?php echo $this->lang->line('blood_group'); ?></td><td class="detail-value"><?php echo html_escape($bloodGroup); ?></td></tr>
                            <?php } ?>
                        </tbody>
                    </table>
                    
                    <!-- signature -->
                    <?php if ($sign): ?>
                    <div class="sign-wrap">
                        <img src="<?php echo $sign; ?>" alt="">
                    </div>
                    <?php endif; ?>
                </div>
                
                <!-- Optional barcode -->
                <?php if (isset($id_card[0]->enable_student_barcode) && $id_card[0]->enable_student_barcode == 1 && $barcode_enabled == 1 && $barcodeUrl) { ?>
                    <div style="margin-top:14px; display:flex; justify-content:flex-end;">
                        <div class="barcode-wrap">
                            <img src="<?php echo $barcodeUrl; ?>" alt="">
                        </div>
                    </div>
                <?php } ?>
            </div>
        </div>
        
        <?php 
                endforeach;
            }
        } // End of students check
        ?>

    </div>
</div>

<script>
// Auto-print functionality (optional)
window.addEventListener('load', function() {
    // Uncomment the next line if you want auto-print
    // setTimeout(function() { window.print(); }, 1000);
});

// Handle image load errors gracefully
document.addEventListener('DOMContentLoaded', function() {
    var images = document.querySelectorAll('img');
    images.forEach(function(img) {
        img.addEventListener('error', function() {
            console.warn('Failed to load image:', this.src);
        });
    });
});
</script>

</body>
</html>