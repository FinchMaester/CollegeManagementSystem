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

/* name + designation */
.v-name{margin-top:8px;font-weight:800;text-transform:uppercase;font-size:13px;}
.v-desig{font-size:12px;color:#9b1818;margin-top:2px;}

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
</style>

<?php if ($id_card[0]->enable_vertical_card) { ?>

<!-- ========= VERTICAL CARDS ========= -->
<?php 
$card_count = 0;
foreach ($staffs as $staff_key => $staff_value) { 
  $card_count++;
  
  // Add page break before every 3rd card (2 cards per page)
  if ($card_count > 1 && ($card_count - 1) % 2 == 0) {
    echo '<div class="page-break"></div>';
  }
?>
<div class="idcard-wrapper">
  <div class="idcard-v">
    <!-- Top colored bar -->
    <div class="v-top" style="background: <?php echo $id_card[0]->header_color; ?>">
      <div class="row1">
        <img src="<?php echo base_url('uploads/staff_id_card/logo/' . $id_card[0]->logo); ?>" alt="">
        <div class="v-sch-name"><?php echo $id_card[0]->school_name; ?></div>
      </div>
      <div class="v-addr"><?php echo $id_card[0]->school_address; ?></div>
    </div>

    <!-- Photo -->
    <div class="v-photo">
      <div class="img">
        <img src="<?php
          if (!empty($staff_value->image)) {
              echo base_url()."uploads/staff_images/".$staff_value->image;
          } else {
              if ($staff_value->gender == 'Female') {
                  echo base_url()."uploads/staff_images/default_female.jpg";
              } elseif ($staff_value->gender == 'Male') {
                  echo base_url()."uploads/staff_images/default_male.jpg";
              }
          }
        ?>" alt="">
      </div>
    </div>

    <!-- Name / Designation -->
    <div class="v-name"><?php echo strtoupper($staff_value->name . " " . $staff_value->surname); ?></div>
    <?php if ($id_card[0]->enable_designation == 1) { ?>
      <div class="v-desig"><?php echo $staff_value->designation; ?></div>
    <?php } ?>

    <!-- Details -->
    <ul class="vertlist">
      <?php if ($id_card[0]->enable_staff_id == 1) { ?>
        <li><b><?php echo $this->lang->line('staff_id'); ?></b><span><?php echo $staff_value->employee_id; ?></span></li>
      <?php } ?>
      <?php if ($id_card[0]->enable_staff_department == 1) { ?>
        <li><b><?php echo $this->lang->line('department'); ?></b><span><?php echo $staff_value->department; ?></span></li>
      <?php } ?>
      <?php if ($id_card[0]->enable_fathers_name == 1) { ?>
        <li><b><?php echo $this->lang->line('father_name'); ?></b><span><?php echo $staff_value->father_name; ?></span></li>
      <?php } ?>
      <?php if ($id_card[0]->enable_mothers_name == 1) { ?>
        <li><b><?php echo $this->lang->line('mother_name'); ?></b><span><?php echo $staff_value->mother_name; ?></span></li>
      <?php } ?>
      <?php if ($id_card[0]->enable_date_of_joining == 1) { ?>
        <li><b><?php echo $this->lang->line('date_of_joining'); ?></b><span><?php
          if (!empty($staff_value->date_of_joining) && $staff_value->date_of_joining != '0000-00-00') {
            echo date($this->customlib->getSchoolDateFormat(), $this->customlib->dateYYYYMMDDtoStrtotime($staff_value->date_of_joining));
          } ?></span></li>
      <?php } ?>
      <?php if ($id_card[0]->enable_permanent_address == 1) { ?>
        <li><b><?php echo $this->lang->line('address'); ?></b><span><?php echo $staff_value->local_address; ?></span></li>
      <?php } ?>
      <?php if ($id_card[0]->enable_staff_phone == 1) { ?>
        <li><b><?php echo $this->lang->line('phone'); ?></b><span><?php echo $staff_value->contact_no; ?></span></li>
      <?php } ?>
      <?php if ($id_card[0]->enable_staff_dob == 1) { ?>
        <li><b><?php echo $this->lang->line('date_of_birth'); ?></b><span><?php
          $dob="";
          if ($staff_value->dob != "0000-00-00") {
              $dob = date($this->customlib->getSchoolDateFormat(), $this->customlib->dateYYYYMMDDtoStrtotime($staff_value->dob));
          }
          echo $dob; ?></span></li>
      <?php } ?>
    </ul>

    <!-- Signature -->
    <div class="v-sign">
      <img src="<?php echo base_url('uploads/staff_id_card/signature/' . $id_card[0]->sign_image); ?>" alt="">
    </div>

    <!-- Barcode (optional) -->
    <?php if ($id_card[0]->enable_staff_barcode == 1 && file_exists("./uploads/staff_id_card/barcodes/" . $staff_value->employee_id . ".png")) { ?>
      <div class="v-bar">
        <img src="<?php echo base_url('uploads/staff_id_card/barcodes/' . $staff_value->employee_id . '.png'); ?>" alt="">
      </div>
    <?php } ?>
  </div>
</div>
<script>console.log('Card <?php echo $card_count; ?> rendered for employee: <?php echo $staff_value->employee_id; ?>');</script>
<?php } ?>

<?php } else { ?>

<!-- ========= HORIZONTAL CARDS ========= -->
<?php 
$card_count = 0;
foreach ($staffs as $staff_key => $staff_value) { 
  $card_count++;
  
  // Add page break before every 3rd card (2 cards per page)
  if ($card_count > 1 && ($card_count - 1) % 2 == 0) {
    echo '<div class="page-break"></div>';
  }
?>
<div class="idcard-wrapper">
  <div class="idcard-h">
    <!-- Header -->
    <div class="hdr">
      <img class="hdr-logo" src="<?php echo base_url('uploads/staff_id_card/logo/' . $id_card[0]->logo); ?>" alt="">
      <div class="hdr-right">
        <div class="sch-name"><?php echo $id_card[0]->school_name; ?></div>
        <div class="sch-addr"><?php echo $id_card[0]->school_address; ?></div>
      </div>
    </div>

    <!-- Title -->
    <div class="card-title">
      <?php echo strtoupper($id_card[0]->title); ?>
    </div>

    <!-- Body -->
    <div class="h-body">
      <!-- watermark -->
      <div class="wm">
        <img src="<?php echo base_url('uploads/staff_id_card/background/' . $id_card[0]->background); ?>" alt="">
      </div>

      <!-- photo -->
      <div class="photo-box">
        <img src="<?php
          if (!empty($staff_value->image)) {
              echo base_url()."uploads/staff_images/".$staff_value->image;
          } else {
              if ($staff_value->gender == 'Female') {
                  echo base_url()."uploads/staff_images/default_female.jpg";
              } elseif ($staff_value->gender == 'Male') {
                  echo base_url()."uploads/staff_images/default_male.jpg";
              } else {
                  echo base_url()."uploads/staff_images/default_male.jpg";
              }
          }
        ?>" alt="">
      </div>

      <!-- details table -->
      <table class="detail-list">
        <tbody>
          <?php if ($id_card[0]->enable_name == 1) { ?>
          <tr><td class="detail-label"><?php echo $this->lang->line('staff'); ?> <?php echo $this->lang->line('name'); ?></td><td class="detail-value"><?php echo $staff_value->name; ?> <?php echo $staff_value->surname; ?></td></tr>
          <?php } ?>
          <?php if ($id_card[0]->enable_staff_id == 1) { ?>
          <tr><td class="detail-label"><?php echo $this->lang->line('staff_id'); ?></td><td class="detail-value"><?php echo $staff_value->employee_id; ?></td></tr>
          <?php } ?>
          <?php if ($id_card[0]->enable_fathers_name == 1) { ?>
          <tr><td class="detail-label"><?php echo $this->lang->line('father_name'); ?></td><td class="detail-value"><?php echo $staff_value->father_name; ?></td></tr>
          <?php } ?>
          <?php if ($id_card[0]->enable_mothers_name == 1) { ?>
          <tr><td class="detail-label"><?php echo $this->lang->line('mother_name'); ?></td><td class="detail-value"><?php echo $staff_value->mother_name; ?></td></tr>
          <?php } ?>
          <?php if ($id_card[0]->enable_date_of_joining == 1) { ?>
          <tr><td class="detail-label"><?php echo $this->lang->line('date_of_joining'); ?></td><td class="detail-value"><?php
            if (!empty($staff_value->date_of_joining) && $staff_value->date_of_joining != '0000-00-00') {
                echo date($this->customlib->getSchoolDateFormat(), $this->customlib->dateYYYYMMDDtoStrtotime($staff_value->date_of_joining));
            } ?></td></tr>
          <?php } ?>
          <?php if ($id_card[0]->enable_permanent_address == 1) { ?>
          <tr><td class="detail-label"><?php echo $this->lang->line('address'); ?></td><td class="detail-value"><?php echo $staff_value->local_address; ?></td></tr>
          <?php } ?>
          <?php if ($id_card[0]->enable_staff_phone == 1) { ?>
          <tr><td class="detail-label"><?php echo $this->lang->line('phone'); ?></td><td class="detail-value"><?php echo $staff_value->contact_no; ?></td></tr>
          <?php } ?>
          <?php if ($id_card[0]->enable_staff_dob == 1) { ?>
          <tr><td class="detail-label"><?php echo $this->lang->line('date_of_birth'); ?></td><td class="detail-value"><?php
            $dob="";
            if ($staff_value->dob != "0000-00-00") {
                $dob = date($this->customlib->getSchoolDateFormat(), $this->customlib->dateYYYYMMDDtoStrtotime($staff_value->dob));
            }
            echo $dob; ?></td></tr>
          <?php } ?>
        </tbody>
      </table>

      <!-- signature -->
      <div class="sign-wrap">
        <img src="<?php echo base_url('uploads/staff_id_card/signature/' . $id_card[0]->sign_image); ?>" alt="">
      </div>
    </div>

    <!-- Optional barcode -->
    <?php if ($id_card[0]->enable_staff_barcode == 1 && file_exists("./uploads/staff_id_card/barcodes/" . $staff_value->employee_id . ".png")) { ?>
      <div style="margin-top:14px; display:flex; justify-content:flex-end;">
        <div class="barcode-wrap">
          <img src="<?php echo base_url('uploads/staff_id_card/barcodes/' . $staff_value->employee_id . '.png'); ?>" alt="">
        </div>
      </div>
    <?php } ?>
  </div>
</div>
<script>console.log('Card <?php echo $card_count; ?> rendered for employee: <?php echo $staff_value->employee_id; ?>');</script>
<?php } ?>

<?php } ?>

<script>console.log('Total cards rendered: <?php echo $card_count; ?>');</script>