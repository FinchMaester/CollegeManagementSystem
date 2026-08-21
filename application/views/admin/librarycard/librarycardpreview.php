<?php if (!empty($idcard)) { ?>
<?php
$card_width_mm = isset($idcard->card_width_mm) ? (float) $idcard->card_width_mm : 86.00;
$card_height_mm = isset($idcard->card_height_mm) ? (float) $idcard->card_height_mm : 54.00;
if ($card_width_mm <= 0) { $card_width_mm = 86.00; }
if ($card_height_mm <= 0) { $card_height_mm = 54.00; }
$card_width_mm = max(40, min(150, $card_width_mm));
$card_height_mm = max(25, min(120, $card_height_mm));
$header_offset_x_mm = isset($idcard->header_offset_x_mm) ? (float) $idcard->header_offset_x_mm : 0;
$header_offset_y_mm = isset($idcard->header_offset_y_mm) ? (float) $idcard->header_offset_y_mm : 0;
$photo_offset_x_mm = isset($idcard->photo_offset_x_mm) ? (float) $idcard->photo_offset_x_mm : 0;
$photo_offset_y_mm = isset($idcard->photo_offset_y_mm) ? (float) $idcard->photo_offset_y_mm : 0;
$details_offset_x_mm = isset($idcard->details_offset_x_mm) ? (float) $idcard->details_offset_x_mm : 0;
$details_offset_y_mm = isset($idcard->details_offset_y_mm) ? (float) $idcard->details_offset_y_mm : 0;
$signature_offset_x_mm = isset($idcard->signature_offset_x_mm) ? (float) $idcard->signature_offset_x_mm : 0;
$signature_offset_y_mm = isset($idcard->signature_offset_y_mm) ? (float) $idcard->signature_offset_y_mm : 0;
$barcode_offset_x_mm = isset($idcard->barcode_offset_x_mm) ? (float) $idcard->barcode_offset_x_mm : 0;
$barcode_offset_y_mm = isset($idcard->barcode_offset_y_mm) ? (float) $idcard->barcode_offset_y_mm : 0;
$header_align = isset($idcard->header_align) ? $idcard->header_align : 'left';
$content_layout = isset($idcard->content_layout) ? $idcard->content_layout : 'same_row';
$photo_align = isset($idcard->photo_align) ? $idcard->photo_align : 'left';
$details_align = isset($idcard->details_align) ? $idcard->details_align : 'left';
$signature_align = isset($idcard->signature_align) ? $idcard->signature_align : 'right';
$barcode_align = isset($idcard->barcode_align) ? $idcard->barcode_align : 'right';
$allowed_align = array('left', 'center', 'right');
if (!in_array($header_align, $allowed_align, true)) { $header_align = 'left'; }
if (!in_array($photo_align, $allowed_align, true)) { $photo_align = 'left'; }
if (!in_array($details_align, $allowed_align, true)) { $details_align = 'left'; }
if (!in_array($signature_align, $allowed_align, true)) { $signature_align = 'right'; }
if (!in_array($barcode_align, $allowed_align, true)) { $barcode_align = 'right'; }
if (!in_array($content_layout, array('same_row', 'stacked'), true)) { $content_layout = 'same_row'; }
$is_stacked_layout = ($content_layout === 'stacked');

$sample_name = 'Sample Member';
$sample_member_type = 'student';
$sample_admission = 'ADM-1001';
$sample_employee = 'EMP-1001';
$sample_class = '12';
$sample_section = 'A';
$sample_program = 'BBA';
$sample_phone = '98XXXXXXXX';
$sample_dob = '2000-01-01';
?>
<style>
.library-preview-shell{
    width:100%;
    background:#efefef;
    border-radius:4px;
    padding:12px;
    display:flex;
    justify-content:center;
    align-items:flex-start;
}
.idcard-wrapper{display:block;padding:2mm;text-align:left;vertical-align:top;margin:0 auto}
.idcard-h{
    width: <?php echo $card_width_mm; ?>mm;
    min-height: <?php echo $card_height_mm; ?>mm;
    margin: 0;
    background:#fff;
    border:0.3mm solid #dcdcdc;
    border-radius:2mm;
    padding:2.2mm;
    position:relative;
    text-align:left;
    overflow:hidden;
    box-shadow:0 1px 3px rgba(0,0,0,.18);
}
.hdr{display:flex;align-items:flex-start;gap:1.2mm;margin-bottom:1.2mm;transform: translate(<?php echo $header_offset_x_mm; ?>mm, <?php echo $header_offset_y_mm; ?>mm);}
.hdr-logo{width:5mm;height:5mm;object-fit:contain}
.sch-name{font-size:2.7mm;font-weight:700;color:#6f7b88;line-height:1.15}
.sch-addr{font-size:2mm;color:#8893a0;margin-top:0.5mm;line-height:1.1}
.card-title{text-transform:uppercase;letter-spacing:.08em;font-size:2.2mm;color:#9aa5b1;margin:1.6mm 0 1.6mm 0;line-height:1.1}
.h-body{display:grid;grid-template-columns:20mm 1fr;gap:2mm;position:relative}
.h-body-stacked{display:block}
.h-body-photo-right .photo-box-wrap{order:2}
.photo-box-wrap{display:flex}
.photo-box{width:18mm;height:20mm;border:0.25mm solid #e3e3e3;border-radius:1mm;background:#fafafa;overflow:hidden;transform: translate(<?php echo $photo_offset_x_mm; ?>mm, <?php echo $photo_offset_y_mm; ?>mm);}
.photo-box img{width:100%;height:100%;object-fit:cover}
.wm{position:absolute;inset:0;display:flex;align-items:center;justify-content:center;pointer-events:none}
.wm img{width:70%;height:70%;object-fit:contain;opacity:.07}
.detail-list{width:100%;border-collapse:collapse;transform: translate(<?php echo $details_offset_x_mm; ?>mm, <?php echo $details_offset_y_mm; ?>mm);}
.detail-list td{padding:0.5mm;font-size:2.05mm;vertical-align:top;line-height:1.1}
.detail-label{width:22mm;font-weight:700}
.sign-wrap{width:20mm;height:6mm;border:0.25mm solid #ddd;border-radius:0.8mm;display:flex;align-items:center;justify-content:center;background:#fff}
.sign-wrap img{max-width:18mm;max-height:4.8mm;object-fit:contain}
.barcode-wrap{margin-top:1.2mm;display:flex;justify-content:flex-end;transform: translate(<?php echo $barcode_offset_x_mm; ?>mm, <?php echo $barcode_offset_y_mm; ?>mm);}
.barcode-wrap img{height:4.6mm}
.idcard-v{
    width: <?php echo $card_width_mm; ?>mm;
    min-height: <?php echo $card_height_mm; ?>mm;
    margin: 0;
    background:#fff;
    border:0.3mm solid #dcdcdc;
    border-radius:2mm;
    overflow:hidden;
    text-align:center;
    display:inline-block;
    box-shadow:0 1px 3px rgba(0,0,0,.18);
}
.v-top{color:#fff;padding:1.8mm 1.4mm 1.4mm 1.4mm;transform: translate(<?php echo $header_offset_x_mm; ?>mm, <?php echo $header_offset_y_mm; ?>mm);}
.v-top .row1{display:flex;align-items:center;justify-content:center;gap:6px}
.v-top .row1 img{width:4.5mm;height:4.5mm;object-fit:contain}
.v-sch-name{font-size:2.6mm;font-weight:700;line-height:1.1}
.v-addr{font-size:1.9mm;opacity:.9;margin-top:0.4mm;line-height:1.1}
.v-photo{margin-top:1.2mm;display:flex;justify-content:center;transform: translate(<?php echo $photo_offset_x_mm; ?>mm, <?php echo $photo_offset_y_mm; ?>mm);}
.v-photo .img{width:13mm;height:13mm;border-radius:1.5mm;border:0.7mm solid #fff;overflow:hidden;background:#f5f5f5}
.v-photo .img img{width:100%;height:100%;object-fit:cover}
.v-name{margin-top:1.2mm;font-weight:800;text-transform:uppercase;font-size:2.25mm;line-height:1.1}
.vertlist{padding:1.4mm 1.8mm;list-style:none;text-align:left;transform: translate(<?php echo $details_offset_x_mm; ?>mm, <?php echo $details_offset_y_mm; ?>mm);}
.vertlist li{font-size:1.95mm;padding:0.35mm 0;border-bottom:0.2mm dashed #eee;line-height:1.1}
.vertlist li:last-child{border-bottom:0}
.vertlist li b{display:inline-block;width:48%}
.vertlist li span{display:inline-block;width:50%;text-align:right}
.v-sign{margin:0;border:0.25mm solid #ddd;width:20mm;height:5.5mm;display:flex;align-items:center;justify-content:center;transform: translate(<?php echo $signature_offset_x_mm; ?>mm, <?php echo $signature_offset_y_mm; ?>mm);}
.v-sign img{max-width:18.5mm;max-height:4.4mm}
.v-bar{margin:0;border:0.25mm solid #e5e5e5;width:24mm;height:5.2mm;display:flex;align-items:center;justify-content:center;transform: translate(<?php echo $barcode_offset_x_mm; ?>mm, <?php echo $barcode_offset_y_mm; ?>mm);}
.v-bar img{max-height:4.2mm}
</style>

<?php
$bgUrl = !empty($idcard->background) ? $this->media_storage->getImageURL('uploads/library_card/background/' . $idcard->background) : '';
$logoUrl = !empty($idcard->logo) ? $this->media_storage->getImageURL('uploads/library_card/logo/' . $idcard->logo) : '';
$signUrl = !empty($idcard->sign_image) ? $this->media_storage->getImageURL('uploads/library_card/signature/' . $idcard->sign_image) : '';
$photoUrl = $this->media_storage->getImageURL('uploads/student_images/default_male.jpg');
?>

<div class="library-preview-shell">
<div class="idcard-wrapper">
<?php if ((int) $idcard->enable_vertical_card === 1) { ?>
    <div class="idcard-v">
        <div class="v-top" style="background: <?php echo !empty($idcard->header_color) ? $idcard->header_color : '#4a90e2'; ?>; text-align: <?php echo $header_align; ?>;">
            <div class="row1" style="justify-content: <?php echo $header_align === 'left' ? 'flex-start' : ($header_align === 'right' ? 'flex-end' : 'center'); ?>;">
                <?php if (!empty($logoUrl)) { ?><img src="<?php echo $logoUrl; ?>" alt=""><?php } ?>
                <div class="v-sch-name"><?php echo html_escape($idcard->school_name); ?></div>
            </div>
            <div class="v-addr"><?php echo html_escape($idcard->school_address); ?></div>
        </div>
        <div class="v-photo" style="justify-content: <?php echo $photo_align === 'left' ? 'flex-start' : ($photo_align === 'right' ? 'flex-end' : 'center'); ?>;"><div class="img"><img src="<?php echo $photoUrl; ?>" alt=""></div></div>
        <div class="v-name"><?php echo strtoupper(html_escape($sample_name)); ?></div>
        <ul class="vertlist" style="text-align: <?php echo $details_align; ?>;">
            <?php if ($idcard->enable_library_card_no) { ?><li><b>Library Card No</b><span>LC-001</span></li><?php } ?>
            <?php if ($idcard->enable_member_type) { ?><li><b><?php echo $this->lang->line('member_type'); ?></b><span><?php echo $this->lang->line($sample_member_type); ?></span></li><?php } ?>
            <?php if ($idcard->enable_name) { ?><li><b><?php echo $this->lang->line('name'); ?></b><span><?php echo $sample_name; ?></span></li><?php } ?>
            <?php if ($idcard->enable_admission_no) { ?><li><b><?php echo $this->lang->line('admission_no'); ?></b><span><?php echo $sample_admission; ?></span></li><?php } ?>
            <?php if ($idcard->enable_employee_id) { ?><li><b><?php echo $this->lang->line('staff_id'); ?></b><span><?php echo $sample_employee; ?></span></li><?php } ?>
            <?php if ($idcard->enable_class) { ?><li><b><?php echo $this->lang->line('class'); ?></b><span><?php echo $sample_class; ?></span></li><?php } ?>
            <?php if ($idcard->enable_section) { ?><li><b><?php echo $this->lang->line('section'); ?></b><span><?php echo $sample_section; ?></span></li><?php } ?>
            <?php if ($idcard->enable_program) { ?><li><b><?php echo $this->lang->line('program'); ?></b><span><?php echo $sample_program; ?></span></li><?php } ?>
            <?php if ($idcard->enable_phone) { ?><li><b><?php echo $this->lang->line('phone'); ?></b><span><?php echo $sample_phone; ?></span></li><?php } ?>
            <?php if ($idcard->enable_dob) { ?><li><b><?php echo $this->lang->line('date_of_birth'); ?></b><span><?php echo $sample_dob; ?></span></li><?php } ?>
        </ul>
        <?php if (!empty($signUrl)) { ?><div style="display:flex;justify-content: <?php echo $signature_align === 'left' ? 'flex-start' : ($signature_align === 'right' ? 'flex-end' : 'center'); ?>;padding:1.2mm 1.8mm 1.2mm 1.8mm;"><div class="v-sign"><img src="<?php echo $signUrl; ?>" alt=""></div></div><?php } ?>
        <?php if ($idcard->enable_library_barcode) { ?><div style="display:flex;justify-content: <?php echo $barcode_align === 'left' ? 'flex-start' : ($barcode_align === 'right' ? 'flex-end' : 'center'); ?>;padding:1mm 1.8mm 1.6mm 1.8mm;"><div class="v-bar"><img src="<?php echo $this->media_storage->getImageURL('uploads/staff_id_card/barcodes/default.png'); ?>" alt=""></div></div><?php } ?>
    </div>
<?php } else { ?>
    <div class="idcard-h">
        <div class="hdr" style="justify-content: <?php echo $header_align === 'left' ? 'flex-start' : ($header_align === 'right' ? 'flex-end' : 'center'); ?>; text-align: <?php echo $header_align; ?>;">
            <?php if (!empty($logoUrl)) { ?><img class="hdr-logo" src="<?php echo $logoUrl; ?>" alt=""><?php } ?>
            <div>
                <div class="sch-name"><?php echo html_escape($idcard->school_name); ?></div>
                <div class="sch-addr"><?php echo html_escape($idcard->school_address); ?></div>
            </div>
        </div>
        <div class="card-title"><?php echo strtoupper(html_escape($idcard->title)); ?></div>
        <div class="h-body <?php echo $is_stacked_layout ? 'h-body-stacked' : ''; ?> <?php echo (!$is_stacked_layout && $photo_align === 'right') ? 'h-body-photo-right' : ''; ?>">
            <?php if (!empty($bgUrl)) { ?><div class="wm"><img src="<?php echo $bgUrl; ?>" alt=""></div><?php } ?>
            <div class="photo-box-wrap" style="justify-content: <?php echo $photo_align === 'left' ? 'flex-start' : ($photo_align === 'right' ? 'flex-end' : 'center'); ?>;margin-bottom:<?php echo $is_stacked_layout ? '1.4mm' : '0'; ?>;">
                <div class="photo-box"><img src="<?php echo $photoUrl; ?>" alt=""></div>
            </div>
            <table class="detail-list" style="text-align: <?php echo $details_align; ?>;margin-top:<?php echo $is_stacked_layout ? '1mm' : '0'; ?>;">
                <tbody>
                    <?php if ($idcard->enable_library_card_no) { ?><tr><td class="detail-label">Library Card No</td><td>LC-001</td></tr><?php } ?>
                    <?php if ($idcard->enable_member_type) { ?><tr><td class="detail-label"><?php echo $this->lang->line('member_type'); ?></td><td><?php echo $this->lang->line($sample_member_type); ?></td></tr><?php } ?>
                    <?php if ($idcard->enable_name) { ?><tr><td class="detail-label"><?php echo $this->lang->line('name'); ?></td><td><?php echo $sample_name; ?></td></tr><?php } ?>
                    <?php if ($idcard->enable_admission_no) { ?><tr><td class="detail-label"><?php echo $this->lang->line('admission_no'); ?></td><td><?php echo $sample_admission; ?></td></tr><?php } ?>
                    <?php if ($idcard->enable_employee_id) { ?><tr><td class="detail-label"><?php echo $this->lang->line('staff_id'); ?></td><td><?php echo $sample_employee; ?></td></tr><?php } ?>
                    <?php if ($idcard->enable_class) { ?><tr><td class="detail-label"><?php echo $this->lang->line('class'); ?></td><td><?php echo $sample_class; ?></td></tr><?php } ?>
                    <?php if ($idcard->enable_section) { ?><tr><td class="detail-label"><?php echo $this->lang->line('section'); ?></td><td><?php echo $sample_section; ?></td></tr><?php } ?>
                    <?php if ($idcard->enable_program) { ?><tr><td class="detail-label"><?php echo $this->lang->line('program'); ?></td><td><?php echo $sample_program; ?></td></tr><?php } ?>
                    <?php if ($idcard->enable_phone) { ?><tr><td class="detail-label"><?php echo $this->lang->line('phone'); ?></td><td><?php echo $sample_phone; ?></td></tr><?php } ?>
                    <?php if ($idcard->enable_dob) { ?><tr><td class="detail-label"><?php echo $this->lang->line('date_of_birth'); ?></td><td><?php echo $sample_dob; ?></td></tr><?php } ?>
                </tbody>
            </table>
        </div>
        <?php if (!empty($signUrl)) { ?><div style="display:flex;justify-content: <?php echo $signature_align === 'left' ? 'flex-start' : ($signature_align === 'right' ? 'flex-end' : 'center'); ?>;margin-top:1.2mm;transform: translate(<?php echo $signature_offset_x_mm; ?>mm, <?php echo $signature_offset_y_mm; ?>mm);"><div class="sign-wrap"><img src="<?php echo $signUrl; ?>" alt=""></div></div><?php } ?>
        <?php if ($idcard->enable_library_barcode) { ?><div class="barcode-wrap" style="justify-content: <?php echo $barcode_align === 'left' ? 'flex-start' : ($barcode_align === 'right' ? 'flex-end' : 'center'); ?>;"><img src="<?php echo $this->media_storage->getImageURL('uploads/staff_id_card/barcodes/default.png'); ?>" alt=""></div><?php } ?>
    </div>
<?php } ?>
</div>
</div>
<?php } ?>
