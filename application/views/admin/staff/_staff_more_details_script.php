<script type="text/javascript">
(function ($) {
    'use strict';

    function toggleStaffMoreDetails($link) {
        var $box = $link.closest('.box');
        if (!$box.length) {
            return;
        }
        var $body = $box.children('.box-body');
        var $icon = $link.find('i.fa').first();
        var speed = 200;

        if ($box.hasClass('collapsed-box')) {
            $box.removeClass('collapsed-box');
            $link.removeClass('collapsed');
            $body.stop(true, true).slideDown(speed);
            $icon.removeClass('fa-plus').addClass('fa-minus');
        } else {
            $box.addClass('collapsed-box');
            $link.addClass('collapsed');
            $body.stop(true, true).slideUp(speed);
            $icon.removeClass('fa-minus').addClass('fa-plus');
        }
    }

    $(function () {
        $(document).off('click.staffMoreDetails', '.staff-more-details-toggle');
        $(document).on('click.staffMoreDetails', '.staff-more-details-toggle', function (e) {
            e.preventDefault();
            e.stopImmediatePropagation();
            toggleStaffMoreDetails($(this));
        });
    });
})(jQuery);
</script>
