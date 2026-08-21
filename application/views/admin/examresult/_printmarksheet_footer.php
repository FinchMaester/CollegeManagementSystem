<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Footer signature row from template.marksheet_footer_json.
 *
 * @var object $template
 */

$this->load->helper('marksheet_layout');
$__mf = marksheet_parse_footer_json(isset($template->marksheet_footer_json) ? $template->marksheet_footer_json : null);
$__order = array('program_teacher', 'date', 'college_chief', 'college_seal');
$__types = array(
    'program_teacher' => 'sig',
    'date'            => 'date',
    'college_chief'   => 'sig',
    'college_seal'    => 'sig',
);
$__visible = array();
foreach ($__order as $__k) {
    if (!empty($__mf[$__k]['show'])) {
        $__visible[] = $__k;
    }
}
if (empty($__visible)) {
    return;
}
$__n = count($__visible);
$__pct = (int) floor(100 / max(1, $__n));
$__date_disp = !empty($template->date) ? $template->date : date('Y-m-d');
?>
    <table cellpadding="0" cellspacing="0" width="100%" style="margin-top:14px">
        <tr>
        <?php foreach ($__visible as $__k) {
            $__lab = isset($__mf[$__k]['label']) ? $__mf[$__k]['label'] : '';
            $__lab_esc = htmlspecialchars($__lab, ENT_QUOTES, 'UTF-8');
            $__t = isset($__types[$__k]) ? $__types[$__k] : 'sig';
            ?>
            <td align="center" style="width:<?php echo $__pct; ?>%;vertical-align:bottom;">
            <?php if ($__t === 'date') { ?>
                <div><?php echo htmlspecialchars((string) $__date_disp, ENT_QUOTES, 'UTF-8'); ?></div>
                <div style="margin-top:5px;"><?php echo $__lab_esc; ?></div>
            <?php } else { ?>
                <div class="sig-line"></div><?php echo $__lab_esc; ?>
            <?php } ?>
            </td>
        <?php } ?>
        </tr>
    </table>
