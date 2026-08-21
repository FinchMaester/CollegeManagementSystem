<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Library Cards</title>
<?php
$card_width_mm = isset($id_card[0]->card_width_mm) ? (float) $id_card[0]->card_width_mm : 86.00;
$card_height_mm = isset($id_card[0]->card_height_mm) ? (float) $id_card[0]->card_height_mm : 54.00;
if ($card_width_mm <= 0) { $card_width_mm = 86.00; }
if ($card_height_mm <= 0) { $card_height_mm = 54.00; }
$card_width_mm = max(40, min(150, $card_width_mm));
$card_height_mm = max(25, min(120, $card_height_mm));
$is_full_print = (isset($print_mode) && $print_mode === 'full');
$enabled_detail_count = 0;
$enabled_detail_count += !empty($id_card[0]->enable_library_card_no) ? 1 : 0;
$enabled_detail_count += !empty($id_card[0]->enable_member_type) ? 1 : 0;
$enabled_detail_count += !empty($id_card[0]->enable_name) ? 1 : 0;
$enabled_detail_count += (!empty($id_card[0]->enable_admission_no) || !empty($id_card[0]->enable_employee_id)) ? 1 : 0;
$enabled_detail_count += !empty($id_card[0]->enable_class) ? 1 : 0;
$enabled_detail_count += !empty($id_card[0]->enable_section) ? 1 : 0;
$enabled_detail_count += !empty($id_card[0]->enable_program) ? 1 : 0;
$enabled_detail_count += !empty($id_card[0]->enable_phone) ? 1 : 0;
$enabled_detail_count += !empty($id_card[0]->enable_dob) ? 1 : 0;
$has_signature = !empty($id_card[0]->sign_image);
$has_barcode = !empty($id_card[0]->enable_library_barcode);
$min_required_height_mm = 22 + ($enabled_detail_count * 4.0) + ($has_signature ? 8 : 0) + ($has_barcode ? 7 : 0);
$effective_card_height_mm = $is_full_print ? $card_height_mm : max($card_height_mm, min(120, $min_required_height_mm));
$header_offset_x_mm = isset($id_card[0]->header_offset_x_mm) ? (float) $id_card[0]->header_offset_x_mm : 0;
$header_offset_y_mm = isset($id_card[0]->header_offset_y_mm) ? (float) $id_card[0]->header_offset_y_mm : 0;
$photo_offset_x_mm = isset($id_card[0]->photo_offset_x_mm) ? (float) $id_card[0]->photo_offset_x_mm : 0;
$photo_offset_y_mm = isset($id_card[0]->photo_offset_y_mm) ? (float) $id_card[0]->photo_offset_y_mm : 0;
$details_offset_x_mm = isset($id_card[0]->details_offset_x_mm) ? (float) $id_card[0]->details_offset_x_mm : 0;
$details_offset_y_mm = isset($id_card[0]->details_offset_y_mm) ? (float) $id_card[0]->details_offset_y_mm : 0;
$signature_offset_x_mm = isset($id_card[0]->signature_offset_x_mm) ? (float) $id_card[0]->signature_offset_x_mm : 0;
$signature_offset_y_mm = isset($id_card[0]->signature_offset_y_mm) ? (float) $id_card[0]->signature_offset_y_mm : 0;
$barcode_offset_x_mm = isset($id_card[0]->barcode_offset_x_mm) ? (float) $id_card[0]->barcode_offset_x_mm : 0;
$barcode_offset_y_mm = isset($id_card[0]->barcode_offset_y_mm) ? (float) $id_card[0]->barcode_offset_y_mm : 0;
$header_align = isset($id_card[0]->header_align) ? $id_card[0]->header_align : 'left';
$content_layout = isset($id_card[0]->content_layout) ? $id_card[0]->content_layout : 'same_row';
$photo_align = isset($id_card[0]->photo_align) ? $id_card[0]->photo_align : 'left';
$details_align = isset($id_card[0]->details_align) ? $id_card[0]->details_align : 'left';
$signature_align = isset($id_card[0]->signature_align) ? $id_card[0]->signature_align : 'right';
$barcode_align = isset($id_card[0]->barcode_align) ? $id_card[0]->barcode_align : 'right';
$allowed_align = array('left', 'center', 'right');
if (!in_array($header_align, $allowed_align, true)) { $header_align = 'left'; }
if (!in_array($photo_align, $allowed_align, true)) { $photo_align = 'left'; }
if (!in_array($details_align, $allowed_align, true)) { $details_align = 'left'; }
if (!in_array($signature_align, $allowed_align, true)) { $signature_align = 'right'; }
if (!in_array($barcode_align, $allowed_align, true)) { $barcode_align = 'right'; }
if (!in_array($content_layout, array('same_row', 'stacked'), true)) { $content_layout = 'same_row'; }
$is_stacked_layout = ($content_layout === 'stacked');
?>
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{
    font-family:Arial,Helvetica,sans-serif;
    color:#222;
    padding:10px;
    background:#efefef;
    text-align:center;
}
@media print {
    @page{
<?php if ($is_full_print) { ?>
        size: auto;
        margin: 8mm;
<?php } else { ?>
        size: <?php echo $card_width_mm; ?>mm <?php echo $effective_card_height_mm; ?>mm;
        margin: 0;
<?php } ?>
    }
    html, body{background:#fff !important}
    .page-break{display:block;page-break-before:always}
    .idcard-wrapper{page-break-inside:avoid}
    body{
        padding:0;
        margin:0;
        background:#fff;
        text-align:<?php echo $is_full_print ? 'left' : 'center'; ?>;
    }
    .idcard-wrapper{padding:0}
}
.idcard-wrapper{display:inline-block;padding:2mm;text-align:left;vertical-align:top}
.idcard-h{
    width: <?php echo $is_full_print ? '680px' : ($card_width_mm . 'mm'); ?>;
    min-height: <?php echo $is_full_print ? 'auto' : ($effective_card_height_mm . 'mm'); ?>;
    height: <?php echo $is_full_print ? 'auto' : ($effective_card_height_mm . 'mm'); ?>;
    margin: 0;
    background:#fff;
    border:0.3mm solid #dcdcdc;
    border-radius:2mm;
    padding:2.2mm;
    position:relative;
    text-align:left;
    overflow:hidden;
}
.hdr{display:flex;align-items:flex-start;gap:<?php echo $is_full_print ? '8px' : '1.2mm'; ?>;margin-bottom:<?php echo $is_full_print ? '10px' : '1.2mm'; ?>;transform: translate(<?php echo $header_offset_x_mm; ?>mm, <?php echo $header_offset_y_mm; ?>mm);}
.hdr-logo{width:<?php echo $is_full_print ? '26px' : '5mm'; ?>;height:<?php echo $is_full_print ? '26px' : '5mm'; ?>;object-fit:contain}
.sch-name{font-size:<?php echo $is_full_print ? '15px' : '2.7mm'; ?>;font-weight:700;color:#6f7b88;line-height:1.15}
.sch-addr{font-size:<?php echo $is_full_print ? '11px' : '2mm'; ?>;color:#8893a0;margin-top:<?php echo $is_full_print ? '2px' : '0.5mm'; ?>;line-height:1.1}
.card-title{text-transform:uppercase;letter-spacing:.08em;font-size:<?php echo $is_full_print ? '12px' : '2.2mm'; ?>;color:#9aa5b1;margin:<?php echo $is_full_print ? '14px 0 12px 0' : '1.6mm 0 1.6mm 0'; ?>;line-height:1.1}
.h-body{display:grid;grid-template-columns:<?php echo $is_full_print ? '130px' : '20mm'; ?> 1fr;gap:<?php echo $is_full_print ? '18px' : '2mm'; ?>;position:relative}
.h-body-stacked{display:block}
.h-body-photo-right .photo-box-wrap{order:2}
.photo-box-wrap{display:flex}
.photo-box{width:<?php echo $is_full_print ? '110px' : '18mm'; ?>;height:<?php echo $is_full_print ? '110px' : '20mm'; ?>;border:0.25mm solid #e3e3e3;border-radius:1mm;background:#fafafa;overflow:hidden;transform: translate(<?php echo $photo_offset_x_mm; ?>mm, <?php echo $photo_offset_y_mm; ?>mm);}
.photo-box img{width:100%;height:100%;object-fit:cover}
.wm{position:absolute;inset:0;display:flex;align-items:center;justify-content:center;pointer-events:none}
.wm img{width:70%;height:70%;object-fit:contain;opacity:.07}
.detail-list{width:100%;border-collapse:collapse;transform: translate(<?php echo $details_offset_x_mm; ?>mm, <?php echo $details_offset_y_mm; ?>mm);}
.detail-list td{padding:<?php echo $is_full_print ? '6px' : '0.5mm'; ?>;font-size:<?php echo $is_full_print ? '12px' : '2.05mm'; ?>;vertical-align:top;line-height:1.1}
.detail-label{width:<?php echo $is_full_print ? '145px' : '22mm'; ?>;font-weight:700}
.sign-wrap{width:<?php echo $is_full_print ? '170px' : '20mm'; ?>;height:<?php echo $is_full_print ? '44px' : '6mm'; ?>;border:0.25mm solid #ddd;border-radius:0.8mm;display:flex;align-items:center;justify-content:center;background:#fff}
.sign-wrap img{max-width:<?php echo $is_full_print ? '160px' : '18mm'; ?>;max-height:<?php echo $is_full_print ? '34px' : '4.8mm'; ?>;object-fit:contain}
.barcode-wrap{margin-top:<?php echo $is_full_print ? '12px' : '1.2mm'; ?>;display:flex;justify-content:flex-end;transform: translate(<?php echo $barcode_offset_x_mm; ?>mm, <?php echo $barcode_offset_y_mm; ?>mm);}
.barcode-wrap img{height:<?php echo $is_full_print ? '24px' : '4.6mm'; ?>}
.idcard-v{
    width: <?php echo $is_full_print ? '300px' : ($card_width_mm . 'mm'); ?>;
    min-height: <?php echo $is_full_print ? 'auto' : ($effective_card_height_mm . 'mm'); ?>;
    height: <?php echo $is_full_print ? 'auto' : ($effective_card_height_mm . 'mm'); ?>;
    margin: 0;
    background:#fff;
    border:0.3mm solid #dcdcdc;
    border-radius:2mm;
    overflow:hidden;
    text-align:center;
    display:inline-block;
}
.v-top{color:#fff;padding:<?php echo $is_full_print ? '10px 8px 8px 8px' : '1.8mm 1.4mm 1.4mm 1.4mm'; ?>;transform: translate(<?php echo $header_offset_x_mm; ?>mm, <?php echo $header_offset_y_mm; ?>mm);}
.v-top .row1{display:flex;align-items:center;justify-content:center;gap:6px}
.v-top .row1 img{width:<?php echo $is_full_print ? '24px' : '4.5mm'; ?>;height:<?php echo $is_full_print ? '24px' : '4.5mm'; ?>;object-fit:contain}
.v-sch-name{font-size:<?php echo $is_full_print ? '14px' : '2.6mm'; ?>;font-weight:700;line-height:1.1}
.v-addr{font-size:<?php echo $is_full_print ? '11px' : '1.9mm'; ?>;opacity:.9;margin-top:<?php echo $is_full_print ? '2px' : '0.4mm'; ?>;line-height:1.1}
.v-photo{margin-top:<?php echo $is_full_print ? '8px' : '1.2mm'; ?>;display:flex;justify-content:center;transform: translate(<?php echo $photo_offset_x_mm; ?>mm, <?php echo $photo_offset_y_mm; ?>mm);}
.v-photo .img{width:<?php echo $is_full_print ? '88px' : '13mm'; ?>;height:<?php echo $is_full_print ? '88px' : '13mm'; ?>;border-radius:1.5mm;border:0.7mm solid #fff;overflow:hidden;background:#f5f5f5}
.v-photo .img img{width:100%;height:100%;object-fit:cover}
.v-name{margin-top:<?php echo $is_full_print ? '8px' : '1.2mm'; ?>;font-weight:800;text-transform:uppercase;font-size:<?php echo $is_full_print ? '13px' : '2.25mm'; ?>;line-height:1.1}
.vertlist{padding:<?php echo $is_full_print ? '10px 12px' : '1.4mm 1.8mm'; ?>;list-style:none;text-align:left;transform: translate(<?php echo $details_offset_x_mm; ?>mm, <?php echo $details_offset_y_mm; ?>mm);}
.vertlist li{font-size:<?php echo $is_full_print ? '12px' : '1.95mm'; ?>;padding:<?php echo $is_full_print ? '3px 0' : '0.35mm 0'; ?>;border-bottom:0.2mm dashed #eee;line-height:1.1}
.vertlist li:last-child{border-bottom:0}
.vertlist li b{display:inline-block;width:48%}
.vertlist li span{display:inline-block;width:50%;text-align:right}
.v-sign{margin:0;border:0.25mm solid #ddd;width:<?php echo $is_full_print ? '160px' : '20mm'; ?>;height:<?php echo $is_full_print ? '30px' : '5.5mm'; ?>;display:flex;align-items:center;justify-content:center;transform: translate(<?php echo $signature_offset_x_mm; ?>mm, <?php echo $signature_offset_y_mm; ?>mm);}
.v-sign img{max-width:<?php echo $is_full_print ? '150px' : '18.5mm'; ?>;max-height:<?php echo $is_full_print ? '24px' : '4.4mm'; ?>}
.v-bar{margin:0;border:0.25mm solid #e5e5e5;width:<?php echo $is_full_print ? '180px' : '24mm'; ?>;height:<?php echo $is_full_print ? '26px' : '5.2mm'; ?>;display:flex;align-items:center;justify-content:center;transform: translate(<?php echo $barcode_offset_x_mm; ?>mm, <?php echo $barcode_offset_y_mm; ?>mm);}
.v-bar img{max-height:<?php echo $is_full_print ? '20px' : '4.2mm'; ?>}
</style>
</head>
<body>
<?php
$bgRel   = isset($id_card[0]->background) ? 'uploads/library_card/background/'.$id_card[0]->background : '';
$logoRel = isset($id_card[0]->logo) ? 'uploads/library_card/logo/'.$id_card[0]->logo : '';
$signRel = isset($id_card[0]->sign_image) ? 'uploads/library_card/signature/'.$id_card[0]->sign_image : '';
$bgUrl   = $bgRel ? base_url($bgRel) : '';
$logo    = $logoRel ? base_url($logoRel) : '';
$sign    = $signRel ? base_url($signRel) : '';
$is_vertical = (isset($id_card[0]->enable_vertical_card) && (int) $id_card[0]->enable_vertical_card === 1);
$card_count = 0;
foreach ($members as $member):
    $card_count++;
    if ($card_count > 1 && ($card_count - 1) % 2 == 0) { echo '<div class="page-break"></div>'; }
    $name = trim((isset($member->firstname) ? $member->firstname : '') . ' ' . (isset($member->middlename) ? $member->middlename : '') . ' ' . (isset($member->lastname) ? $member->lastname : ''));
    $img = '';
    if (!empty($member->image)) {
        $img = (strpos($member->image, 'uploads/') === 0) ? base_url($member->image) : base_url('uploads/student_images/' . $member->image);
    }
    if (empty($img)) {
        $img = base_url('uploads/student_images/default_male.jpg');
    }
    $id_no = ($member->member_type === 'student') ? $member->admission_no : $member->employee_id;
?>

<div class="idcard-wrapper">
<?php if ($is_vertical) { ?>
    <div class="idcard-v">
        <div class="v-top" style="background: <?php echo isset($id_card[0]->header_color) ? $id_card[0]->header_color : '#4a90e2'; ?>; text-align: <?php echo $header_align; ?>;">
            <div class="row1" style="justify-content: <?php echo $header_align === 'left' ? 'flex-start' : ($header_align === 'right' ? 'flex-end' : 'center'); ?>;">
                <?php if ($logo) { ?><img src="<?php echo $logo; ?>" alt=""><?php } ?>
                <div class="v-sch-name"><?php echo html_escape($id_card[0]->school_name); ?></div>
            </div>
            <div class="v-addr"><?php echo html_escape($id_card[0]->school_address); ?></div>
        </div>
        <div class="v-photo" style="justify-content: <?php echo $photo_align === 'left' ? 'flex-start' : ($photo_align === 'right' ? 'flex-end' : 'center'); ?>;"><div class="img"><img src="<?php echo $img; ?>" alt=""></div></div>
        <div class="v-name"><?php echo strtoupper(html_escape($name)); ?></div>
        <ul class="vertlist" style="text-align: <?php echo $details_align; ?>;">
            <?php if ($id_card[0]->enable_library_card_no) { ?><li><b>Library Card No</b><span><?php echo html_escape($member->library_card_no); ?></span></li><?php } ?>
            <?php if ($id_card[0]->enable_member_type) { ?><li><b><?php echo $this->lang->line('member_type'); ?></b><span><?php echo $this->lang->line($member->member_type); ?></span></li><?php } ?>
            <?php if ($id_card[0]->enable_name) { ?><li><b><?php echo $this->lang->line('name'); ?></b><span><?php echo html_escape($name); ?></span></li><?php } ?>
            <?php if ($id_card[0]->enable_admission_no && $member->member_type === 'student') { ?><li><b><?php echo $this->lang->line('admission_no'); ?></b><span><?php echo html_escape($member->admission_no); ?></span></li><?php } ?>
            <?php if ($id_card[0]->enable_employee_id && $member->member_type === 'teacher') { ?><li><b><?php echo $this->lang->line('staff_id'); ?></b><span><?php echo html_escape($member->employee_id); ?></span></li><?php } ?>
            <?php if ($id_card[0]->enable_class) { ?><li><b><?php echo $this->lang->line('class'); ?></b><span><?php echo html_escape($member->class_name); ?></span></li><?php } ?>
            <?php if ($id_card[0]->enable_section) { ?><li><b><?php echo $this->lang->line('section'); ?></b><span><?php echo html_escape($member->section_name); ?></span></li><?php } ?>
            <?php if ($id_card[0]->enable_program) { ?><li><b><?php echo $this->lang->line('program'); ?></b><span><?php echo html_escape($member->program_name); ?></span></li><?php } ?>
            <?php if ($id_card[0]->enable_phone) { ?><li><b><?php echo $this->lang->line('phone'); ?></b><span><?php echo html_escape($member->phone); ?></span></li><?php } ?>
            <?php if ($id_card[0]->enable_dob) { ?><li><b><?php echo $this->lang->line('date_of_birth'); ?></b><span><?php echo html_escape($member->dob); ?></span></li><?php } ?>
        </ul>
        <?php if ($sign) { ?><div style="display:flex;justify-content: <?php echo $signature_align === 'left' ? 'flex-start' : ($signature_align === 'right' ? 'flex-end' : 'center'); ?>;padding:<?php echo $is_full_print ? '8px 12px 10px 12px' : '1.2mm 1.8mm 1.2mm 1.8mm'; ?>;"><div class="v-sign"><img src="<?php echo $sign; ?>" alt=""></div></div><?php } ?>
        <?php if ($id_card[0]->enable_library_barcode && !empty($member->barcode)) { ?><div style="display:flex;justify-content: <?php echo $barcode_align === 'left' ? 'flex-start' : ($barcode_align === 'right' ? 'flex-end' : 'center'); ?>;padding:<?php echo $is_full_print ? '6px 12px 12px 12px' : '1mm 1.8mm 1.6mm 1.8mm'; ?>;"><div class="v-bar"><img src="<?php echo base_url($member->barcode); ?>" alt=""></div></div><?php } ?>
    </div>
<?php } else { ?>
    <div class="idcard-h">
        <div class="hdr" style="justify-content: <?php echo $header_align === 'left' ? 'flex-start' : ($header_align === 'right' ? 'flex-end' : 'center'); ?>; text-align: <?php echo $header_align; ?>;">
            <?php if ($logo) { ?><img class="hdr-logo" src="<?php echo $logo; ?>" alt=""><?php } ?>
            <div><div class="sch-name"><?php echo html_escape($id_card[0]->school_name); ?></div><div class="sch-addr"><?php echo html_escape($id_card[0]->school_address); ?></div></div>
        </div>
        <div class="card-title"><?php echo strtoupper(html_escape($id_card[0]->title)); ?></div>
        <div class="h-body <?php echo $is_stacked_layout ? 'h-body-stacked' : ''; ?> <?php echo (!$is_stacked_layout && $photo_align === 'right') ? 'h-body-photo-right' : ''; ?>">
            <?php if ($bgUrl) { ?><div class="wm"><img src="<?php echo $bgUrl; ?>" alt=""></div><?php } ?>
            <div class="photo-box-wrap" style="justify-content: <?php echo $photo_align === 'left' ? 'flex-start' : ($photo_align === 'right' ? 'flex-end' : 'center'); ?>;margin-bottom:<?php echo $is_stacked_layout ? ($is_full_print ? '12px' : '1.4mm') : '0'; ?>;">
                <div class="photo-box"><img src="<?php echo $img; ?>" alt=""></div>
            </div>
            <table class="detail-list" style="text-align: <?php echo $details_align; ?>;margin-top:<?php echo $is_stacked_layout ? ($is_full_print ? '8px' : '1mm') : '0'; ?>;">
                <tbody>
                    <?php if ($id_card[0]->enable_library_card_no) { ?><tr><td class="detail-label">Library Card No</td><td><?php echo html_escape($member->library_card_no); ?></td></tr><?php } ?>
                    <?php if ($id_card[0]->enable_member_type) { ?><tr><td class="detail-label"><?php echo $this->lang->line('member_type'); ?></td><td><?php echo $this->lang->line($member->member_type); ?></td></tr><?php } ?>
                    <?php if ($id_card[0]->enable_name) { ?><tr><td class="detail-label"><?php echo $this->lang->line('name'); ?></td><td><?php echo html_escape($name); ?></td></tr><?php } ?>
                    <?php if (($id_card[0]->enable_admission_no && $member->member_type === 'student') || ($id_card[0]->enable_employee_id && $member->member_type === 'teacher')) { ?><tr><td class="detail-label"><?php echo $member->member_type === 'student' ? $this->lang->line('admission_no') : $this->lang->line('staff_id'); ?></td><td><?php echo html_escape($id_no); ?></td></tr><?php } ?>
                    <?php if ($id_card[0]->enable_class) { ?><tr><td class="detail-label"><?php echo $this->lang->line('class'); ?></td><td><?php echo html_escape($member->class_name); ?></td></tr><?php } ?>
                    <?php if ($id_card[0]->enable_section) { ?><tr><td class="detail-label"><?php echo $this->lang->line('section'); ?></td><td><?php echo html_escape($member->section_name); ?></td></tr><?php } ?>
                    <?php if ($id_card[0]->enable_program) { ?><tr><td class="detail-label"><?php echo $this->lang->line('program'); ?></td><td><?php echo html_escape($member->program_name); ?></td></tr><?php } ?>
                    <?php if ($id_card[0]->enable_phone) { ?><tr><td class="detail-label"><?php echo $this->lang->line('phone'); ?></td><td><?php echo html_escape($member->phone); ?></td></tr><?php } ?>
                    <?php if ($id_card[0]->enable_dob) { ?><tr><td class="detail-label"><?php echo $this->lang->line('date_of_birth'); ?></td><td><?php echo html_escape($member->dob); ?></td></tr><?php } ?>
                </tbody>
            </table>
        </div>
        <?php if ($sign) { ?><div style="display:flex;justify-content: <?php echo $signature_align === 'left' ? 'flex-start' : ($signature_align === 'right' ? 'flex-end' : 'center'); ?>;<?php echo $is_full_print ? 'margin-top:10px;' : ('position:absolute;left:1.2mm;right:1.2mm;bottom:' . (($id_card[0]->enable_library_barcode && !empty($member->barcode)) ? '8.2mm' : '1.2mm') . ';margin-top:0;'); ?>transform: translate(<?php echo $signature_offset_x_mm; ?>mm, <?php echo $signature_offset_y_mm; ?>mm);"><div class="sign-wrap"><img src="<?php echo $sign; ?>" alt=""></div></div><?php } ?>
        <?php if ($id_card[0]->enable_library_barcode && !empty($member->barcode)) { ?><div class="barcode-wrap" style="justify-content: <?php echo $barcode_align === 'left' ? 'flex-start' : ($barcode_align === 'right' ? 'flex-end' : 'center'); ?>;<?php echo $is_full_print ? '' : 'position:absolute;left:1.2mm;right:1.2mm;bottom:1.2mm;margin-top:0;'; ?>"><img src="<?php echo base_url($member->barcode); ?>" alt=""></div><?php } ?>
    </div>
<?php } ?>
</div>
<?php endforeach; ?>
</body>
</html>
