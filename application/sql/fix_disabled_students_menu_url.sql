-- Fix "Disabled Students" menu link so it does not log out (wrong URL).
-- Run this if the Disabled Students menu under Student Information redirects to login or logs you out.
-- The correct URL is student/disablestudents (GET shows the page). student/disablestudentslist is for POST only.

UPDATE sidebar_sub_menus
SET url = 'student/disablestudents'
WHERE lang_key = 'disabled_students'
  AND (url IS NULL OR url != 'student/disablestudents');
