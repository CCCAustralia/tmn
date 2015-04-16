UPDATE Tmn_Sessions SET Tmn_Sessions.ADDITIONAL_LIFE_COVER = 3 WHERE Tmn_Sessions.ADDITIONAL_LIFE_COVER = 13 OR Tmn_Sessions.ADDITIONAL_LIFE_COVER = 56;
UPDATE Tmn_Sessions SET Tmn_Sessions.ADDITIONAL_LIFE_COVER = 4 WHERE Tmn_Sessions.ADDITIONAL_LIFE_COVER = 17 OR Tmn_Sessions.ADDITIONAL_LIFE_COVER = 74 OR Tmn_Sessions.ADDITIONAL_LIFE_COVER = 82 OR Tmn_Sessions.ADDITIONAL_LIFE_COVER = 321;
UPDATE Tmn_Sessions SET Tmn_Sessions.ADDITIONAL_LIFE_COVER = 5 WHERE Tmn_Sessions.ADDITIONAL_LIFE_COVER = 22 OR Tmn_Sessions.ADDITIONAL_LIFE_COVER = 95;
UPDATE Tmn_Sessions SET Tmn_Sessions.ADDITIONAL_LIFE_COVER = 6 WHERE Tmn_Sessions.ADDITIONAL_LIFE_COVER = 26 OR Tmn_Sessions.ADDITIONAL_LIFE_COVER = 113;
UPDATE Tmn_Sessions SET Tmn_Sessions.ADDITIONAL_LIFE_COVER = 7 WHERE Tmn_Sessions.ADDITIONAL_LIFE_COVER = 30;
UPDATE Tmn_Sessions SET Tmn_Sessions.ADDITIONAL_LIFE_COVER = 8 WHERE Tmn_Sessions.ADDITIONAL_LIFE_COVER = 35 OR Tmn_Sessions.ADDITIONAL_LIFE_COVER = 152;
UPDATE Tmn_Sessions SET Tmn_Sessions.ADDITIONAL_LIFE_COVER = 9 WHERE Tmn_Sessions.ADDITIONAL_LIFE_COVER = 39 OR Tmn_Sessions.ADDITIONAL_LIFE_COVER = 169;

UPDATE Tmn_Sessions SET Tmn_Sessions.S_ADDITIONAL_LIFE_COVER = 3 WHERE Tmn_Sessions.S_ADDITIONAL_LIFE_COVER = 13 OR Tmn_Sessions.S_ADDITIONAL_LIFE_COVER = 56;
UPDATE Tmn_Sessions SET Tmn_Sessions.S_ADDITIONAL_LIFE_COVER = 4 WHERE Tmn_Sessions.S_ADDITIONAL_LIFE_COVER = 17 OR Tmn_Sessions.S_ADDITIONAL_LIFE_COVER = 74 OR Tmn_Sessions.S_ADDITIONAL_LIFE_COVER = 82 OR Tmn_Sessions.S_ADDITIONAL_LIFE_COVER = 321;
UPDATE Tmn_Sessions SET Tmn_Sessions.S_ADDITIONAL_LIFE_COVER = 5 WHERE Tmn_Sessions.S_ADDITIONAL_LIFE_COVER = 22 OR Tmn_Sessions.S_ADDITIONAL_LIFE_COVER = 95;
UPDATE Tmn_Sessions SET Tmn_Sessions.S_ADDITIONAL_LIFE_COVER = 6 WHERE Tmn_Sessions.S_ADDITIONAL_LIFE_COVER = 26 OR Tmn_Sessions.S_ADDITIONAL_LIFE_COVER = 113;
UPDATE Tmn_Sessions SET Tmn_Sessions.S_ADDITIONAL_LIFE_COVER = 7 WHERE Tmn_Sessions.S_ADDITIONAL_LIFE_COVER = 30;
UPDATE Tmn_Sessions SET Tmn_Sessions.S_ADDITIONAL_LIFE_COVER = 8 WHERE Tmn_Sessions.S_ADDITIONAL_LIFE_COVER = 35 OR Tmn_Sessions.S_ADDITIONAL_LIFE_COVER = 152;
UPDATE Tmn_Sessions SET Tmn_Sessions.S_ADDITIONAL_LIFE_COVER = 9 WHERE Tmn_Sessions.S_ADDITIONAL_LIFE_COVER = 39 OR Tmn_Sessions.S_ADDITIONAL_LIFE_COVER = 169;

INSERT INTO `Constants`
(`STIPEND_MIN`, `MIN_SUPER_RATE`, `MIN_ADD_SUPER_RATE`, `OS_STIPEND_MAX`,
`x_resident_band_1`, `x_resident_band_2`, `x_resident_band_3`, `x_resident_band_4`, `x_resident_band_5`, `x_resident_band_6`, `x_resident_band_7`, `x_resident_band_8`,
`a_resident_band_1`, `a_resident_band_2`, `a_resident_band_3`, `a_resident_band_4`, `a_resident_band_5`, `a_resident_band_6`, `a_resident_band_7`, `a_resident_band_8`, `a_resident_band_9`,
`b_resident_band_1`, `b_resident_band_2`, `b_resident_band_3`, `b_resident_band_4`, `b_resident_band_5`, `b_resident_band_6`, `b_resident_band_7`, `b_resident_band_8`, `b_resident_band_9`,
`x_non_resident_band_1`, `x_non_resident_band_2`, `x_non_resident_band_3`, `x_non_resident_band_4`, `a_non_resident_band_1`, `a_non_resident_band_2`, `a_non_resident_band_3`, `a_non_resident_band_4`, `b_non_resident_band_1`, `b_non_resident_band_2`, `b_non_resident_band_3`, `b_non_resident_band_4`,
`MAX_HOUSING_MFB`, `MAX_HOUSING_MFB_COUPLES`, `WORKERS_COMP_RATE`, `CCCA_LEVY_RATE`, `BAND_FP_COUPLE`, `BAND_FP_SINGLE`, `BAND_TMN_COUPLE_MIN`, `BAND_TMN_COUPLE_MAX`, `BAND_TMN_SINGLE_MIN`, `BAND_TMN_SINGLE_MAX`, `STUDENT_LIFE_ACTIVE_DATE`, `EVERYONE_ACTIVE_DATE`)
VALUES
(101, 0.095, 0.095, 1180,
355, 395, 493, 711, 1282, 1538, 3461, 3462,
0, 0.19, 0.29, 0.21, 0.3477, 0.345, 0.39, 0.49, 0.49,
0, 67.4635, 106.9673, 67.4642, 165.4431, 161.9815, 231.2123, 577.3662, 577.3662,
0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0,
1260, 2100, 0.015, 0.1, 7800, 4350, 4500, 9100, 3150, 5050, '2015-04-17', '2015-05-01');
