<option value=""><?php echo $this->lang->line('select'); ?></option>
<?php
if (!empty($resultlist)) {
    foreach ($resultlist as $student) {
        $fullname = $this->customlib->getFullName($student['firstname'], $student['middlename'], $student['lastname'], $sch_setting->middlename, $sch_setting->lastname);
        
        // Check if we have a selected student ID to maintain selection
        $selected = '';
        if (isset($selected_student_id) && $selected_student_id == $student['id']) {
            $selected = 'selected="selected"';
        }
        ?>
        <option value="<?php echo $student['id']; ?>" <?php echo $selected; ?>>
            <?php echo $fullname . " (" . $student['admission_no'] . ")"; ?>
        </option>
        <?php
    }
} else {
    ?>
    <option value=""><?php echo $this->lang->line('no_record_found'); ?></option>
    <?php
}
?>