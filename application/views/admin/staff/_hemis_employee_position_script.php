<script type="text/javascript">
(function ($) {
    function positionIsTeaching($opt) {
        var cat = String($opt.data('category') || $opt.attr('data-category') || '').toLowerCase();
        var typ = String($opt.data('type') || $opt.attr('data-type') || '').toLowerCase();
        if ($opt.data('is-teaching') === 1 || $opt.data('is-teaching') === '1' || $opt.attr('data-is-teaching') === '1') {
            return true;
        }
        return cat === 'teaching' || typ.indexOf('teach') !== -1;
    }

    function applyHemisEmployeePosition($opt) {
        if (!$opt || !$opt.length || !$opt.val()) {
            $('#hemis_position_summary').html('<span class="text-muted">Select Employee Position to see type, post, and category.</span>');
            if (typeof window.staffRefreshOrgUnitDropdown === 'function') {
                window.staffRefreshOrgUnitDropdown(true, '', 0);
            }
            return;
        }

        var postName = $opt.data('post-name') || $opt.attr('data-post-name') || '';
        var empType = $opt.data('employee-type') || $opt.attr('data-employee-type') || '';
        var category = $opt.data('category') || $opt.attr('data-category') || '';
        var isTeaching = positionIsTeaching($opt);
        var deptId = parseInt($opt.data('department-id') || $opt.attr('data-department-id') || 0, 10) || 0;
        var secId = parseInt($opt.data('section-id') || $opt.attr('data-section-id') || 0, 10) || 0;
        var orgId = isTeaching ? deptId : secId;
        var savedSel = $('#department').data('selected') || '';

        if (postName) {
            $('#post_name').val(postName);
        }
        if (empType) {
            $('#employee_type').val(empType);
        }
        if (category) {
            $('#hemis_position_category').val(category);
        }
        if (!$('#position_type').val()) {
            $('#position_type').val('Other');
        }

        var summary = [];
        if (empType) {
            summary.push('<strong>Type:</strong> ' + $('<div>').text(empType).html());
        }
        if (postName) {
            summary.push('<strong>Post:</strong> ' + $('<div>').text(postName).html());
        }
        if (category) {
            summary.push('<strong>Category:</strong> ' + $('<div>').text(category).html());
        }
        $('#hemis_position_summary').html(summary.length ? summary.join(' &nbsp;|&nbsp; ') : '<span class="text-muted">—</span>');

        if (typeof window.staffRefreshOrgUnitDropdown === 'function') {
            window.staffRefreshOrgUnitDropdown(isTeaching, savedSel, orgId);
        }
    }

    $(function () {
        var $pos = $('#employee_position_id');
        if (!$pos.length) {
            return;
        }
        $pos.on('change', function () {
            applyHemisEmployeePosition($(this).find('option:selected'));
        });
        if ($pos.val()) {
            applyHemisEmployeePosition($pos.find('option:selected'));
        }
    });
})(jQuery);
</script>
