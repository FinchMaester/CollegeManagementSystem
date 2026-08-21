<style type="text/css">
    *{padding:0;margin:0;box-sizing:border-box}
    body{font-family:'arial';-webkit-print-color-adjust:exact;print-color-adjust:exact;background:#fff}

    /* Force exact A4 page with no browser headers/footers space */
    @media print {
        @page { size: A4; margin: 0; }
        html, body { width: 210mm; height: 297mm; }
    }

    /* One physical page */
    .page{
        position:relative;
        width:210mm;
        height:297mm;
        margin:0 auto;
        page-break-after:always;
        overflow:hidden;              /* prevent spill onto next page */
        background-repeat:no-repeat;
        background-position:center top;
        background-size:210mm 297mm;  /* stretch to full page */
    }

    /* Work area that sits safely inside the border */
    .content{
        position:absolute;
        inset:0;                      /* fill page */
        padding:18mm 18mm 18mm 18mm;  /* keep inside the certificate border */
    }

    /* Three header cells aligned across */
    .header-row{
        display:flex;
        justify-content:space-between;
        align-items:flex-start;
        width:100%;
    }
    .header-left, .header-center, .header-right{
        font-size:14px;
        line-height:1.4;
        max-width:33%;
    }
    .header-center{ text-align:center }
    .header-right{ text-align:right }

    /* Main body text (kept away from the photo on the right) */
    .body-wrap{
        width:100%;
    }
    .body-inner{
        margin-right:38mm;            /* reserve space so text does not hit the photo */
        padding:0 6mm;
        text-align:center;
    }
    .body-inner p{
        font-size:14px;
        line-height:24px;
    }

    /* Signature/footer row sits near the bottom but inside the border */
    .footer-row{
        display:flex;
        justify-content:space-between;
        width:100%;
        padding:0 6mm;
    }
    .footer-left{ text-align:left }
    .footer-center{ text-align:center }
    .footer-right{ text-align:right }

/* Signature row with dotted lines like the sample */
.signature-row{ display:flex; justify-content:space-between; width:100%; padding:0 6mm; }
.signature-fixed{ position:absolute; left:18mm; right:18mm; bottom:18mm; }
.sign-block{ width:32%; text-align:center }
.sign-line{ border-bottom:2px dotted #c00; height:0; margin:0 6mm 6px 6mm }
.sign-caption{ font-size:12px }

    /* Student photo anchored in content area (top/right) */
    .student-photo{
        position:absolute;
        right:18mm;                  /* same as right padding to stay inside border */
        width:28mm;
        height:auto;
        border:0;
    }

    /* Do not allow accidental page breaks within key blocks */
    .no-break{ page-break-inside:avoid; }
</style>

<?php
// Make certificate text a definite string (avoid null to str_replace)
$ctext = (string)($certificate[0]->certificate_text ?? '');

// Map legacy placeholders to current student keys
$ctext = str_replace('[name]', '[name]', $ctext);
$ctext = str_replace('[present_address]', '[current_address]', $ctext);
$ctext = str_replace('[guardian]', '[guardian_name]', $ctext);
$ctext = str_replace('[phone]', '[mobileno]', $ctext);

// Helpers for positions (use your stored px offsets but keep them inside the safe content box)
$headerTop   = (int)($certificate[0]->header_height ?? 0);
$contentTop  = (int)($certificate[0]->content_height ?? 0);
$footerTop   = (int)($certificate[0]->footer_height ?? 0);
$imgTop      = (int)($certificate[0]->enable_image_height ?? 0);
$showPhoto   = (int)($certificate[0]->enable_student_image ?? 0) === 1;

// Build the background URL once
$bgUrl = !empty($certificate[0]->background_image)
    ? base_url('uploads/certificate/'.$certificate[0]->background_image)
    : '';
?>

<?php foreach ($students as $student): ?>
<?php
    // Start from template each iteration
    $certificate_body = $ctext;

    // Enrich with identifiers fetched directly from students table
    try {
        $CI =& get_instance();
        $sid = is_object($student) ? ($student->id ?? null) : (is_array($student) ? ($student['id'] ?? null) : null);
        if ($sid) {
            $row = $CI->db->select('symbol_no, registration_no, roll_no')->from('students')->where('id', $sid)->get()->row_array();
            if ($row) {
                if (is_object($student)) {
                    $student->symbol_no       = $row['symbol_no'] ?? '';
                    $student->registration_no = $row['registration_no'] ?? '';
                    $student->roll_no         = $row['roll_no'] ?? '';
                } else if (is_array($student)) {
                    $student['symbol_no']       = $row['symbol_no'] ?? '';
                    $student['registration_no'] = $row['registration_no'] ?? '';
                    $student['roll_no']         = $row['roll_no'] ?? '';
                }
            }
        }
    } catch (Throwable $e) {
        // ignore
    }

    // Ensure placeholders remain visible for empty parent names
    if (is_object($student)) {
        $student->father_name = (isset($student->father_name) && trim((string)$student->father_name) !== '') ? $student->father_name : '[father_name]';
        $student->mother_name = (isset($student->mother_name) && trim((string)$student->mother_name) !== '') ? $student->mother_name : '[mother_name]';
    } else if (is_array($student)) {
        $student['father_name'] = (isset($student['father_name']) && trim((string)$student['father_name']) !== '') ? $student['father_name'] : '[father_name]';
        $student['mother_name'] = (isset($student['mother_name']) && trim((string)$student['mother_name']) !== '') ? $student['mother_name'] : '[mother_name]';
    }

    // Replace placeholders with student values (dates kept "as-is" to avoid Customlib warning)
    foreach ($student as $std_key => $std_value) {
        if (in_array($std_key, ["dob","admission_date","created_at"], true)) {
            if (!empty($std_value) && $std_value !== "0000-00-00") {
                try { $std_value = (string)$std_value; } catch (Throwable $e) { $std_value = ''; }
            } else { $std_value = ''; }
        }
        $certificate_body = str_replace('['.$std_key.']', (string)($std_value ?? ''), $certificate_body);
    }

    // Add the three identifiers to certificate text if they exist
    if (isset($student->symbol_no) && !empty($student->symbol_no)) {
        $certificate_body = str_replace('[symbol_no]', $student->symbol_no, $certificate_body);
    }
    if (isset($student->registration_no) && !empty($student->registration_no)) {
        $certificate_body = str_replace('[registration_no]', $student->registration_no, $certificate_body);
    }
    if (isset($student->roll_no) && !empty($student->roll_no)) {
        $certificate_body = str_replace('[roll_no]', $student->roll_no, $certificate_body);
    }

    // Tidy excess whitespace created by empty fields to avoid awkward gaps
    try {
        $certificate_body = preg_replace('/\s{2,}/u', ' ', (string)$certificate_body);
        $certificate_body = preg_replace('/\s+([\.,])/u', '$1', (string)$certificate_body);
    } catch (Throwable $e) {}

    // Photo path
    $imgRel = '';
    if ($showPhoto){
        if (!empty($student->image)) {
            $imgRel = $student->image; // relative path like uploads/student_images/...
        } else {
            $imgRel = (strtolower($student->gender ?? '') === 'female')
                ? 'uploads/student_images/default_female.jpg'
                : 'uploads/student_images/default_male.jpg';
        }
    }
?>
    <div class="page" style="<?php echo $bgUrl ? "background-image:url('".$bgUrl."');" : ''; ?>">
        <div class="content">
            <?php if ($showPhoto): ?>
                <img class="student-photo no-break" style="top:<?php echo max(0,$imgTop); ?>px"
                     src="<?php echo base_url($imgRel); ?>" alt="student photo">
            <?php endif; ?>

            <!-- Header row (uses your three header fields) -->
            <div class="header-row no-break" style="margin-top:<?php echo max(0,$headerTop); ?>px">
                <div class="header-left"><?php echo $certificate[0]->left_header; ?></div>
                <div class="header-center"><?php echo $certificate[0]->center_header; ?></div>
                <div class="header-right"><?php echo $certificate[0]->right_header; ?></div>
            </div>

            <!-- Body -->
            <div class="body-wrap no-break" style="margin-top:<?php echo max(0,$contentTop); ?>px">
                <div class="body-inner">
                    <p><?php echo $certificate_body; ?></p>
                </div>
            </div>

            <!-- Signature row with dotted lines (anchored above bottom, independent of footer height) -->
            <div class="signature-row signature-fixed no-break">
                <div class="sign-block">
                    <div class="sign-line"></div>
                    <div class="sign-caption"><?php echo !empty($certificate[0]->left_footer) ? $certificate[0]->left_footer : 'Prepared by'; ?></div>
                </div>
                <div class="sign-block">
                    <div class="sign-line"></div>
                    <div class="sign-caption"><?php echo !empty($certificate[0]->center_footer) ? $certificate[0]->center_footer : 'Checked by'; ?></div>
                </div>
                <div class="sign-block">
                    <div class="sign-line"></div>
                    <div class="sign-caption"><?php echo !empty($certificate[0]->right_footer) ? $certificate[0]->right_footer : 'Campus Chief'; ?></div>
                </div>
            </div>
        </div>
    </div>
<?php endforeach; ?>
