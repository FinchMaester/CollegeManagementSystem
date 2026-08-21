<?php
// Default values - DO NOT auto-set today's date (AD or BS)
// Only use values if explicitly provided via POST
$date_from_display = '';
$date_to_display = '';

// Check for POST data (from AJAX request) - use Customlib methods
if (isset($_POST['date_from']) && !empty($_POST['date_from'])) {
    // Use Customlib to normalize date format
    $date_from_normalized = $this->customlib->dateFormatToYYYYMMDD($_POST['date_from']);
    if ($date_from_normalized && preg_match('/^20\d{2}-\d{2}-\d{2}$/', $date_from_normalized)) {
        $date_from_display = $date_from_normalized;
    }
}

if (isset($_POST['date_to']) && !empty($_POST['date_to'])) {
    // Use Customlib to normalize date format
    $date_to_normalized = $this->customlib->dateFormatToYYYYMMDD($_POST['date_to']);
    if ($date_to_normalized && preg_match('/^20\d{2}-\d{2}-\d{2}$/', $date_to_normalized)) {
        $date_to_display = $date_to_normalized;
    }
}
?>
<div class="col-sm-6 col-md-3">
    <div class="form-group">
        <label><?php echo $this->lang->line('date_from'); ?></label>
        <input name="date_from" id="date_from" placeholder="" type="text" class="form-control income-date-bs nepali-datepicker date-bs" value="<?php echo htmlspecialchars($date_from_display, ENT_QUOTES, 'UTF-8'); ?>" data-nepali-date="true" data-prevent-english-calendar="true" data-ndp-initialized="false" autocomplete="off" />
        <span class="text-danger"><?php echo form_error('date_from'); ?></span>
    </div>
</div> 

<div class="col-sm-6 col-md-3">
    <div class="form-group">
        <label><?php echo $this->lang->line('date_to'); ?></label>
        <input name="date_to" id="date_to" placeholder="" type="text" class="form-control income-date-bs nepali-datepicker date-bs" value="<?php echo htmlspecialchars($date_to_display, ENT_QUOTES, 'UTF-8'); ?>" data-nepali-date="true" data-prevent-english-calendar="true" data-ndp-initialized="false" autocomplete="off" />
        <span class="text-danger"><?php echo form_error('date_to'); ?></span>
    </div>
</div>

