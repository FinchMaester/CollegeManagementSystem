-- Fix Front CMS navbar "Online Admission" pointing at demo host yourschoolurl.com
-- Run once on each environment (local + production).

UPDATE front_cms_menu_items
SET ext_url = NULL,
    ext_url_link = NULL
WHERE ext_url = '1'
  AND (
    ext_url_link LIKE '%yourschoolurl.com%'
    OR ext_url_link LIKE '%/online_admission'
  )
  AND menu = 'Online Admission';
