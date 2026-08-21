<script type="text/javascript">
(function ($) {
    function staffOrgUnitLabel(isTeaching) {
        return isTeaching
            ? '<?php echo $this->lang->line('department'); ?> (UGC / HEMIS departmentId)'
            : 'Section (UGC / HEMIS sectionId)';
    }

    window.staffRefreshOrgUnitDropdown = function (isTeaching, selectedId, positionOrgId) {
        var $sel = $('#department');
        var $wrap = $('#hemis_org_unit_wrap');
        if (!$sel.length || !window.staffUgcOrgUnits) {
            return;
        }

        var orgFromPosition = parseInt(positionOrgId, 10) || 0;
        if (orgFromPosition > 0) {
            $wrap.hide();
            $sel.html('<option value="' + orgFromPosition + '" selected="selected">' + orgFromPosition + ' (from position)</option>');
            $sel.val(String(orgFromPosition));
            return;
        }

        $wrap.show();
        $('#hemis_org_unit_label').html(
            staffOrgUnitLabel(!!isTeaching) + ' <span class="label label-info">HEMIS</span>'
        );

        var list = isTeaching ? (window.staffUgcOrgUnits.departments || []) : (window.staffUgcOrgUnits.sections || []);
        var html = '<option value=""><?php echo $this->lang->line('select'); ?></option>';
        $.each(list, function (_, row) {
            var id = row.id;
            var name = isTeaching
                ? (row.departmentName || row.department_name || '')
                : (row.sectionName || row.section_name || '');
            if (!id) {
                return;
            }
            var sel = (String(selectedId) === String(id)) ? ' selected' : '';
            html += '<option value="' + id + '"' + sel + '>' + $('<div>').text(name).html() + '</option>';
        });
        $sel.html(html);
        if (selectedId) {
            $sel.val(String(selectedId));
        } else if ($sel.data('selected')) {
            $sel.val(String($sel.data('selected')));
        }
    };

    $(function () {
        var $pos = $('#employee_position_id');
        if ($pos.length && $pos.val()) {
            $pos.trigger('change');
        }
    });
})(jQuery);
</script>
