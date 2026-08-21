<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$this->load->helper('marksheet_layout');
$ms = isset($marksheet) ? $marksheet : null;
$mf_cfg = marksheet_parse_footer_json($ms && !empty($ms->marksheet_footer_json) ? $ms->marksheet_footer_json : null);
$mf_json = json_encode($mf_cfg);
?>
<div class="form-group" style="border-top:1px solid #eee;padding-top:12px;margin-top:12px;">
    <label><?php echo $this->lang->line('marksheet_footer_title'); ?></label>
    <p class="help-block text-muted small"><?php echo $this->lang->line('marksheet_footer_help'); ?></p>
    <table class="table table-bordered table-condensed" style="margin-bottom:8px;max-width:640px;">
        <thead>
            <tr>
                <th><?php echo $this->lang->line('marksheet_footer_slot'); ?></th>
                <th style="width:90px;"><?php echo $this->lang->line('marksheet_footer_show'); ?></th>
                <th><?php echo $this->lang->line('marksheet_footer_label'); ?></th>
            </tr>
        </thead>
        <tbody>
            <tr data-mf-key="program_teacher">
                <td><?php echo $this->lang->line('marksheet_footer_slot_program_teacher'); ?></td>
                <td class="text-center"><input type="checkbox" class="mf-row-show" <?php echo !empty($mf_cfg['program_teacher']['show']) ? 'checked' : ''; ?>></td>
                <td><input type="text" class="form-control input-sm mf-row-label" value="<?php echo htmlspecialchars($mf_cfg['program_teacher']['label'], ENT_QUOTES, 'UTF-8'); ?>"></td>
            </tr>
            <tr data-mf-key="date">
                <td><?php echo $this->lang->line('marksheet_footer_slot_date'); ?></td>
                <td class="text-center"><input type="checkbox" class="mf-row-show" <?php echo !empty($mf_cfg['date']['show']) ? 'checked' : ''; ?>></td>
                <td><input type="text" class="form-control input-sm mf-row-label" value="<?php echo htmlspecialchars($mf_cfg['date']['label'], ENT_QUOTES, 'UTF-8'); ?>"></td>
            </tr>
            <tr data-mf-key="college_chief">
                <td><?php echo $this->lang->line('marksheet_footer_slot_college_chief'); ?></td>
                <td class="text-center"><input type="checkbox" class="mf-row-show" <?php echo !empty($mf_cfg['college_chief']['show']) ? 'checked' : ''; ?>></td>
                <td><input type="text" class="form-control input-sm mf-row-label" value="<?php echo htmlspecialchars($mf_cfg['college_chief']['label'], ENT_QUOTES, 'UTF-8'); ?>"></td>
            </tr>
            <tr data-mf-key="college_seal">
                <td><?php echo $this->lang->line('marksheet_footer_slot_college_seal'); ?></td>
                <td class="text-center"><input type="checkbox" class="mf-row-show" <?php echo !empty($mf_cfg['college_seal']['show']) ? 'checked' : ''; ?>></td>
                <td><input type="text" class="form-control input-sm mf-row-label" value="<?php echo htmlspecialchars($mf_cfg['college_seal']['label'], ENT_QUOTES, 'UTF-8'); ?>"></td>
            </tr>
        </tbody>
    </table>
    <input type="hidden" name="marksheet_footer_json" id="marksheet_footer_json" value="">
</div>
<script>
(function () {
    var mfDefault = <?php echo $mf_json; ?>;
    function rowToCfg($tr) {
        var k = $tr.data('mf-key');
        return {
            show: $tr.find('.mf-row-show').is(':checked'),
            label: $tr.find('.mf-row-label').val() || (mfDefault[k] && mfDefault[k].label) || ''
        };
    }
    function syncMfHidden() {
        var out = {};
        $('#marksheet_footer_json').closest('.form-group').find('tbody tr').each(function () {
            var $tr = $(this);
            var k = $tr.data('mf-key');
            if (k) {
                out[k] = rowToCfg($tr);
            }
        });
        $('#marksheet_footer_json').val(JSON.stringify(out));
    }
    $('.mf-row-show, .mf-row-label').on('change keyup', syncMfHidden);
    syncMfHidden();
    $('form#form1, form#editform').on('submit', function () { syncMfHidden(); });
})();
</script>
