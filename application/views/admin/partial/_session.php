<div class="form-group">
    <label class="control-label col-sm-3" for="email"><?php echo $this->lang->line('session'); ?></label>
    <div class="col-sm-9">
        <select class="form-control" name="popup_session">
            <?php
foreach ($sessionList as $session_key => $session_value) {
    // Sessions are already in BS format from controller, but double-check
    $sessionText = $this->customlib->convertSessionToBSIfNeeded($session_value['session']);
    
    // Final validation - only show if in valid range
    $parts = explode('-', $sessionText);
    $year1 = isset($parts[0]) ? intval($parts[0]) : 0;
    
    if ($year1 >= 2070 && $year1 <= 2090) {
        ?>
                <option value="<?php echo $session_value['id']; ?>" <?php
if ($sessionData['session_id'] == $session_value['id']) {
            echo "selected='selected'";
        }
        ?>><?php echo $sessionText; ?></option>
                        <?php
    }
}
?>
        </select>
    </div>
</div>

