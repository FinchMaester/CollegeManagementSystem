-- Enable English (id 4) and Nepali (id 53) in the header language switcher
UPDATE sch_settings SET languages = '["4","53"]' WHERE id = 1;

-- Nepal flag code for flag-icon CSS (np = Nepal)
UPDATE languages SET country_code = 'np' WHERE id = 53;
