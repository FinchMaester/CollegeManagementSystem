<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admit Card</title>
    <style>
        @media print {
            @page { size: A4; margin: 4mm; }
            html, body { width: 210mm; height: 297mm; }
            body { margin: 0; padding: 0; background: #fff; zoom: 0.9; }
            .admit-card { box-shadow: none; width: 100%; min-height: auto; padding: 4mm; }
            .content-section, .schedule-section, .instructions, .signatures { page-break-inside: avoid; }
            .header { padding-bottom: 4px; margin-bottom: 6px; border-bottom-width: 2px; }
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f5f5f5; padding: 20px; }

        :root { --primary:#1e3a8a; --primary2:#3b82f6; --border:#e5e7eb; --text:#111827; }

        .admit-card {
            width: 242mm;
            min-height: 297mm;
            background: #ffffff;
            margin: 0 auto;
            padding: 20mm;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
            border: 1px solid var(--border);
            border-radius: 14px;
            position: relative;
        }

        .header {
            text-align: center;
            padding-bottom: 12px;
            margin-bottom: 18px;
            border-bottom: 3px solid rgba(30,58,138,0.25);
        }
        .header-inner { display: flex; align-items: center; justify-content: space-between; gap: 12px; }
        .logo-box { width: 70px; height: 70px; border: 1px solid var(--border); border-radius: 10px; display: flex; align-items: center; justify-content: center; background: #ffffff; overflow: hidden; }
        .logo-box img { max-width: 100%; max-height: 100%; object-fit: contain; }

        .admit-title { font-size: 14px; font-weight: 700; color: var(--primary); margin-bottom: 6px; }
        .school-name { font-size: 22px; font-weight: bold; margin-bottom: 5px; text-transform: uppercase; color: #111827; letter-spacing: 0.5px; }
        .school-address { font-size: 13px; color: #6b7280; }

        .exam-info {
            text-align: center;
            margin-bottom: 16px;
            padding: 10px;
            background: #f8fafc;
            border: 1px solid var(--border);
            border-radius: 10px;
        }
        .exam-name { font-size: 15px; font-weight: 700; margin-bottom: 4px; color: var(--text); }

        .content-section { display: flex; gap: 24px; margin-bottom: 16px; }
        .student-details { flex: 2; }
        .photo-section { flex: 1; text-align: center; }

        .details-table { width: 100%; border-collapse: collapse; overflow: hidden; border-radius: 8px; border: 1px solid var(--border); }
        .details-table tr:nth-child(even) td { background: #fbfdff; }
        .details-table td { padding: 6px 10px; border: 1px solid var(--border); font-size: 12px; }
        .details-table .label { background: #f1f5f9; font-weight: 700; width: 40%; color: #374151; }

        .photo-frame {
            width: 120px; height: 150px; border: 2px solid rgba(30,58,138,0.35); margin: 0 auto 10px; background: #ffffff; display: flex; align-items: center; justify-content: center; font-size: 12px; color: #6b7280; border-radius: 10px; overflow: hidden;
        }
        .photo-frame img { width: 100%; height: 100%; object-fit: cover; }
        .roll-display { background: linear-gradient(90deg, var(--primary), var(--primary2)); color: #ffffff; padding: 6px 10px; font-weight: 700; margin-top: 6px; border-radius: 999px; display: inline-block; box-shadow: 0 6px 16px rgba(30,58,138,0.25); font-size: 12px; }

        .schedule-section { margin-bottom: 14px; }
        .section-title { font-size: 14px; font-weight: 800; background: #f1f5f9; color: var(--text); padding: 8px 12px; margin-bottom: 8px; border-radius: 8px; border: 1px solid var(--border); }

        .schedule-table { width: 100%; border-collapse: collapse; border-radius: 10px; overflow: hidden; border: 1px solid var(--border); }
        .schedule-table th { background: linear-gradient(90deg, var(--primary), var(--primary2)); color: #ffffff; padding: 10px 8px; font-weight: 700; text-align: center; font-size: 12px; }
        .schedule-table td { padding: 10px 8px; border-top: 1px solid var(--border); text-align: center; font-size: 12px; }
        .schedule-table tr:nth-child(even) td { background: #fafcff; }

        .instructions { margin-top: 10px; font-size: 12px; }
        .instructions ul { margin-left: 18px; line-height: 1.5; }
        .instructions li { margin-bottom: 4px; }

        .signatures { display: flex; justify-content: flex-end; margin-top: 16px; }
        .signature-box { text-align: center; width: 200px; }
        .signature-line { border-bottom: 2px solid rgba(17,24,39,0.6); height: 42px; margin-bottom: 6px; }
        .signature-label { font-weight: 700; font-size: 12px; color: #374151; }

        /* Image loading error handling */
        img { 
            image-rendering: -webkit-optimize-contrast;
            image-rendering: crisp-edges;
        }
        img[src=""], img:not([src]) {
            display: none;
        }
    </style>
</head>
<body>

<?php
// --------- image URL resolver with local fallback ----------
if (!function_exists('asset_img')) {
    /**
     * Resolve image path/URL to a same-origin URL when possible.
     *
     * @param object $media_storage
     * @param string $pathOrUrl  'uploads/.../file.jpg' or full http(s) URL
     * @return array             ['url'=>string]  (keeping array shape for minimal changes)
     */
    function asset_img($media_storage, $pathOrUrl) {
        $raw = (string)$pathOrUrl;
        if ($raw === '') return ['url'=>''];

        $force_local  = isset($_GET['force_local']) ? (int)$_GET['force_local'] : 0;
        $current_host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '';

        $base = function($rel){
            $rel = ltrim($rel, '/');
            if (function_exists('base_url')) {
                return rtrim(base_url(), '/').'/'.$rel;
            }
            $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
            return $scheme.'://'.$_SERVER['HTTP_HOST'].'/'.$rel;
        };

        // Absolute URL
        if (preg_match('#^https?://#i', $raw)) {
            $p = @parse_url($raw);
            $host = isset($p['host']) ? $p['host'] : '';
            $path = isset($p['path']) ? ltrim($p['path'],'/') : '';
            $fs   = defined('FCPATH') ? rtrim(FCPATH,'/\\').'/'.$path : null;
            $exists_local = ($fs && file_exists($fs));

            if (($force_local || ($host && $current_host && strcasecmp($host, $current_host) !== 0)) && $exists_local) {
                $url = $base($path);
                $url = preg_replace('#(?<!:)//+#', '/', $url);
                return ['url'=>$url];
            }
            $url = preg_replace('#(?<!:)//+#', '/', $raw);
            return ['url'=>$url];
        }

        // Relative path via media_storage; fallback to base_url when host mismatches and local file exists
        $rel = ltrim($raw, '/');
        $url_from_media = '';
        if (is_object($media_storage) && method_exists($media_storage, 'getImageURL')) {
            $url_from_media = (string)$media_storage->getImageURL($rel);
        }

        if ($url_from_media !== '' && preg_match('#^https?://([^/]+)#i', $url_from_media, $m)) {
            $host_from_media = $m[1];
            $fs = defined('FCPATH') ? rtrim(FCPATH,'/\\').'/'.$rel : null;
            if (($force_local || ($current_host && strcasecmp($host_from_media, $current_host) !== 0)) && $fs && file_exists($fs)) {
                $url = $base($rel);
                $url = preg_replace('#(?<!:)//+#', '/', $url);
                return ['url'=>$url];
            }
            $url = preg_replace('#^(https?://[^/]+)(?!/)#', '$1/', $url_from_media);
            $url = preg_replace('#(?<!:)//+#', '/', $url);
            return ['url'=>$url];
        }

        // Media storage empty → local
        $url = $base($rel);
        $url = preg_replace('#(?<!:)//+#', '/', $url);
        return ['url'=>$url];
    }
}

/**
 * Safe image output with error handling
 */
function safe_img_tag($url, $alt, $style = '') {
    if (empty($url)) {
        return '';
    }
    $url = htmlspecialchars($url, ENT_QUOTES, 'UTF-8');
    $alt = htmlspecialchars($alt, ENT_QUOTES, 'UTF-8');
    $style_attr = !empty($style) ? ' style="'.htmlspecialchars($style, ENT_QUOTES, 'UTF-8').'"' : '';
    return '<img src="'.$url.'" alt="'.$alt.'"'.$style_attr.' onerror="this.style.display=\'none\'">';
}
?>

<?php if (!empty($student_details)) { foreach ($student_details as $student_key => $student_value) { ?>
    <div class="admit-card">
        <!-- Header -->
        <div class="header">
            <div class="header-inner">
                <div class="logo-box">
                    <?php
                        $left_src_val = !empty($admitcard->left_logo) ? ('uploads/admit_card/'.$admitcard->left_logo) : '';
                        $left_res = $left_src_val ? asset_img($this->media_storage, $left_src_val) : ['url'=>''];
                        $left_url = $left_res['url'];
                        if (!empty($left_url)) {
                            echo safe_img_tag($left_url, 'left-logo');
                        }
                    ?>
                </div>
                <div style="flex:1;">
                    <div class="admit-title"><?php echo !empty($admitcard->title) ? htmlspecialchars($admitcard->title) : $this->lang->line('admit_card'); ?></div>
                    <div class="school-name"><?php echo !empty($admitcard->school_name) ? htmlspecialchars($admitcard->school_name) : htmlspecialchars($this->customlib->getAppName()); ?></div>
                    <div class="school-address"><?php echo !empty($admitcard->exam_center) ? htmlspecialchars($admitcard->exam_center) : '-'; ?></div>
                </div>
                <div class="logo-box">
                    <?php
                        $right_src_val = !empty($admitcard->right_logo) ? ('uploads/admit_card/'.$admitcard->right_logo) : '';
                        $right_res = $right_src_val ? asset_img($this->media_storage, $right_src_val) : ['url'=>''];
                        $right_url = $right_res['url'];
                        if (!empty($right_url)) {
                            echo safe_img_tag($right_url, 'right-logo');
                        }
                    ?>
                </div>
            </div>
        </div>

        <!-- Exam info -->
        <div class="exam-info">
            <div class="exam-name"><?php echo !empty($admitcard->exam_name) ? htmlspecialchars($admitcard->exam_name) : '-'; ?></div>
        </div>

        <!-- Student details -->
        <div class="content-section">
            <div class="student-details">
            <?php
                $display_roll   = (isset($student_value->students_roll_no)   && $student_value->students_roll_no   !== '') ? htmlspecialchars($student_value->students_roll_no)   : '-';
                $display_symbol = (isset($student_value->students_symbol_no) && $student_value->students_symbol_no !== '') ? htmlspecialchars($student_value->students_symbol_no) : '-';
            ?>
                <table class="details-table">
                    <?php if (!empty($admitcard->is_roll_no)) { ?>
                    <tr>
                        <td class="label"><?php echo $this->lang->line('roll_number'); ?></td>
                        <td><?php echo $display_roll; ?></td>
                    </tr>
                    <?php } ?>
                    <?php if (!empty($admitcard->is_admission_no)) { ?>
                    <tr>
                        <td class="label"><?php echo $this->lang->line('admission_no'); ?></td>
                        <td><?php echo !empty($student_value->admission_no) ? htmlspecialchars($student_value->admission_no) : '-'; ?></td>
                    </tr>
                    <?php } ?>

                    <!-- Symbol No directly from students.symbol_no -->
                    <tr>
                        <td class="label"><?php echo $this->lang->line('symbol_no') ? $this->lang->line('symbol_no') : 'Symbol No'; ?></td>
                        <td><?php echo $display_symbol; ?></td>
                    </tr>

                    <?php if (!empty($admitcard->is_name)) { ?>
                    <tr>
                        <td class="label"><?php echo $this->lang->line('student_name'); ?></td>
                        <td><?php echo htmlspecialchars($this->customlib->getFullName($student_value->firstname, $student_value->middlename, $student_value->lastname, isset($sch_setting->middlename)?$sch_setting->middlename:0, isset($sch_setting->lastname)?$sch_setting->lastname:0)); ?></td>
                    </tr>
                    <?php } ?>

                    <?php if (!empty($admitcard->is_class) || !empty($admitcard->is_section)) { ?>
                    <tr>
                        <td class="label"><?php echo $this->lang->line('class'); ?></td>
                        <td>
                            <?php
                                $cls = !empty($student_value->class) ? htmlspecialchars($student_value->class) : '';
                                $sec = !empty($student_value->section) ? ' - ' . htmlspecialchars($student_value->section) : '';
                                echo ($cls.$sec) !== '' ? ($cls.$sec) : '-';
                            ?>
                        </td>
                    </tr>
                    <?php } ?>

                    <?php if (!empty($admitcard->is_dob)) { ?>
                    <tr>
                        <td class="label"><?php echo $this->lang->line('d_o_b'); ?></td>
                        <td><?php echo (!empty($student_value->dob) && $student_value->dob!='0000-00-00') ? date($this->customlib->getSchoolDateFormat(), strtotime($student_value->dob)) : '-'; ?></td>
                    </tr>
                    <?php } ?>

                    <?php if (!empty($admitcard->is_gender)) { ?>
                    <tr>
                        <td class="label"><?php echo $this->lang->line('gender'); ?></td>
                        <td><?php echo !empty($student_value->gender) ? $this->lang->line(strtolower($student_value->gender)) : '-'; ?></td>
                    </tr>
                    <?php } ?>

                    <?php if (!empty($admitcard->is_father_name)) { ?>
                    <tr>
                        <td class="label"><?php echo $this->lang->line('father_name'); ?></td>
                        <td><?php echo !empty($student_value->father_name) ? htmlspecialchars($student_value->father_name) : '-'; ?></td>
                    </tr>
                    <?php } ?>

                    <?php if (!empty($admitcard->is_mother_name)) { ?>
                    <tr>
                        <td class="label"><?php echo $this->lang->line('mother_name'); ?></td>
                        <td><?php echo !empty($student_value->mother_name) ? htmlspecialchars($student_value->mother_name) : '-'; ?></td>
                    </tr>
                    <?php } ?>

                    <?php if (!empty($admitcard->is_address)) { ?>
                    <tr>
                        <td class="label"><?php echo $this->lang->line('address'); ?></td>
                        <td><?php echo !empty($student_value->current_address) ? htmlspecialchars($student_value->current_address) : '-'; ?></td>
                    </tr>
                    <?php } ?>

                    <?php if (!empty($admitcard->exam_center)) { ?>
                    <tr>
                        <td class="label"><?php echo $this->lang->line('exam_center'); ?></td>
                        <td><?php echo htmlspecialchars($admitcard->exam_center); ?></td>
                    </tr>
                    <?php } ?>
                </table>
            </div>

            <div class="photo-section">
                <div class="photo-frame">
                    <?php
                        $photo_rel = !empty($student_value->image)
                            ? $student_value->image
                            : ('uploads/student_images/' . (strtolower((string)$student_value->gender) === 'female' ? 'default_female.jpg' : 'default_male.jpg'));
                        $photo_res = asset_img($this->media_storage, $photo_rel);
                        $photo_url = $photo_res['url'];
                    ?>
                    <?php if (!empty($photo_url)) { 
                        echo safe_img_tag($photo_url, 'student-photo', 'width:100%;height:100%;object-fit:cover;');
                    } else { ?>
                        <div style="text-align:center;">
                            <?php echo $this->lang->line('student'); ?><br><?php echo $this->lang->line('photograph'); ?>
                        </div>
                    <?php } ?>
                </div>
                <?php if (!empty($admitcard->is_roll_no)) { ?>
                <div class="roll-display">
                    <?php echo $this->lang->line('roll_number'); ?>: <?php echo $display_roll; ?>
                </div>
                <?php } ?>
            </div>
        </div>

        <!-- Schedule -->
        <div class="schedule-section">
            <div class="section-title"><?php echo $this->lang->line('examination'); ?> <?php echo $this->lang->line('schedule'); ?></div>
            <table class="schedule-table">
                <thead>
                    <tr>
                        <th><?php echo $this->lang->line('date'); ?></th>
                        <th><?php echo $this->lang->line('time'); ?></th>
                        <th><?php echo $this->lang->line('paper_code'); ?></th>
                        <th><?php echo $this->lang->line('subject_name'); ?></th>
                        <th><?php echo $this->lang->line('duration'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($exam_subjects)) { foreach ($exam_subjects as $subject_value) { ?>
                        <tr>
                            <td><?php echo !empty($subject_value->date_from) ? date($this->customlib->getSchoolDateFormat(), strtotime($subject_value->date_from)) : '-'; ?></td>
                            <td><?php echo !empty($subject_value->time_from) ? htmlspecialchars($subject_value->time_from) : '-'; ?></td>
                            <td><?php echo !empty($subject_value->subject_code) ? htmlspecialchars($subject_value->subject_code) : '-'; ?></td>
                            <td><?php echo !empty($subject_value->subject_name) ? htmlspecialchars($subject_value->subject_name) : '-'; ?></td>
                            <td><?php echo isset($subject_value->duration) && $subject_value->duration !== '' ? htmlspecialchars($subject_value->duration) : '3 Hours'; ?></td>
                        </tr>
                    <?php } } else { ?>
                        <tr><td colspan="5">-</td></tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>

        <!-- Signature -->
        <div class="signatures">
            <div class="signature-box">
                <div class="signature-line" style="display:flex;align-items:flex-end;justify-content:center;height:56px;border-bottom:2px solid rgba(17,24,39,0.6);">
                    <?php
                        $sign_src_val = !empty($admitcard->sign) ? ('uploads/admit_card/'.$admitcard->sign) : '';
                        $sign_res = $sign_src_val ? asset_img($this->media_storage, $sign_src_val) : ['url'=>''];
                        $sign_url = $sign_res['url'];
                        if (!empty($sign_url)) {
                            echo safe_img_tag($sign_url, 'principal-signature', 'height:60px;width:auto;display:block;');
                        }
                    ?>
                </div>
                <div class="signature-label"><?php echo $this->lang->line('principal'); ?> <?php echo $this->lang->line('signature'); ?></div>
            </div>
        </div>
    </div>

    <?php if ($student_key < count($student_details) - 1) { ?>
        <div style="page-break-after: always;"></div>
    <?php } ?>

<?php } } else { ?>
    <div class="admit-card"><div style="text-align:center; font-weight:bold;">No student data available</div></div>
<?php } ?>

<script>
// Add error handling for dynamically loaded content
document.addEventListener('DOMContentLoaded', function() {
    // Handle image loading errors gracefully
    var images = document.querySelectorAll('img');
    images.forEach(function(img) {
        img.addEventListener('error', function() {
            this.style.display = 'none';
            console.warn('Failed to load image:', this.src);
        });
    });
    
    // If this page was loaded via AJAX, notify parent
    if (window.parent !== window) {
        window.parent.postMessage({type: 'admitcard_loaded', success: true}, '*');
    }
});

// Global error handler
window.addEventListener('error', function(e) {
    console.error('Page error:', e.message, e.filename, e.lineno);
}, true);
</script>

</body>
</html>