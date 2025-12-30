-- SQL Script to Update Zone Timezones
-- Run this script to set timezones for your existing zones

-- Example: Update zones for Egypt
-- Replace 'Egypt Zone Name' with your actual zone names
UPDATE zones SET timezone = 'Africa/Cairo' WHERE name LIKE '%Egypt%' OR name LIKE '%Cairo%';

-- Example: Update zones for Jordan
-- Replace 'Jordan Zone Name' with your actual zone names
UPDATE zones SET timezone = 'Asia/Amman' WHERE name LIKE '%Jordan%' OR name LIKE '%Amman%';

-- Example: Update zones for Saudi Arabia
UPDATE zones SET timezone = 'Asia/Riyadh' WHERE name LIKE '%Saudi%' OR name LIKE '%Riyadh%' OR name LIKE '%KSA%';

-- Example: Update zones for UAE
UPDATE zones SET timezone = 'Asia/Dubai' WHERE name LIKE '%UAE%' OR name LIKE '%Dubai%';

-- Example: Update zones for Kuwait
UPDATE zones SET timezone = 'Asia/Kuwait' WHERE name LIKE '%Kuwait%';

-- Example: Update zones for Lebanon
UPDATE zones SET timezone = 'Asia/Beirut' WHERE name LIKE '%Lebanon%' OR name LIKE '%Beirut%';

-- View all zones with their current timezones
SELECT id, name, timezone FROM zones ORDER BY name;

-- If you want to update specific zones by ID:
-- UPDATE zones SET timezone = 'Asia/Amman' WHERE id = 1;
-- UPDATE zones SET timezone = 'Africa/Cairo' WHERE id = 2;

