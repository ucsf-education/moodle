-- Make a back up of the tables we are about to change
CREATE TABLE mdl_user_info_category_original AS SELECT * FROM mdl_user_info_category;
CREATE TABLE mdl_user_info_field_original AS SELECT * FROM mdl_user_info_field;

-- Create a new user field category on user_info_category table
-- Also fix the sortorder                                 
UPDATE mdl_user_info_category SET sortorder = 3 WHERE name LIKE 'Voluntary Self-Identification of Disability';
INSERT INTO mdl_user_info_category VALUES (3, 'Programs', 2);

-- Change categoryid from 1 to 3 (the one we just created for 'Programs') in mdl_user_info_field, except for the ones created by Moodle
UPDATE mdl_user_info_field SET categoryid = 3 WHERE categoryid = 1 AND datatype <> 'social';

-- Turn off required on these fields
UPDATE mdl_user_info_field SET required = 0 WHERE shortname IN ('LHD','age','education','origin', 'race', 'sex', 'orientation', 'orgtype', 'PartLHD', 'SoC', 'Tribal', 'gender', 'jobtitle', 'EngProf', 'spanprof', 'Reserve', 'Pathways');
