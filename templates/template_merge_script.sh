cat summary_template_single.html > input-aussie-based-single
sed -n '/^<!-- START aussie-based-single -->/,/^<!-- END aussie-based-single -->/p' input-aussie-based-single > output-aussie-based-single
perl -pi -e 's/^[ \t]*//' output-aussie-based-single
perl -pi -e 's/[\n\r]*$//' output-aussie-based-single
#insert code to copy output into SummaryPanel

cat summary_template_couple.html > input-aussie-based-couple
sed -n '/^<!-- START aussie-based-couple -->/,/^<!-- END aussie-based-couple -->/p' input-aussie-based-couple > output-aussie-based-couple
perl -pi -e 's/^[ \t]*//' output-aussie-based-couple
perl -pi -e 's/[\n\r]*$//' output-aussie-based-couple
#insert code to copy output into SummaryPanel

cat summary_template_international_single.html > input-international-assignment-single
sed -n '/^<!-- START international-assignment-single -->/,/^<!-- END international-assignment-single -->/p' input-international-assignment-single > output-international-assignment-single
perl -pi -e 's/^[ \t]*//' output-international-assignment-single
perl -pi -e 's/[\n\r]*$//' output-international-assignment-single
#insert code to copy output into SummaryPanel

cat summary_template_international_single.html > input-home-assignment-single
sed -n '/^<!-- START home-assignment-single -->/,/^<!-- END home-assignment-single -->/p' input-home-assignment-single > output-home-assignment-single
perl -pi -e 's/^[ \t]*//' output-home-assignment-single
perl -pi -e 's/[\n\r]*$//' output-home-assignment-single
#insert code to copy output into SummaryPanel

cat summary_template_international_couple.html > input-international-assignment-couple
sed -n '/^<!-- START international-assignment-couple -->/,/^<!-- END international-assignment-couple -->/p' input-international-assignment-couple > output-international-assignment-couple
perl -pi -e 's/^[ \t]*//' output-international-assignment-couple
perl -pi -e 's/[\n\r]*$//' output-international-assignment-couple
#insert code to copy output into SummaryPanel

cat summary_template_international_couple.html > input-home-assignment-couple
sed -n '/^<!-- START home-assignment-couple -->/,/^<!-- END home-assignment-couple -->/p' input-home-assignment-couple > output-home-assignment-couple
perl -pi -e 's/^[ \t]*//' output-home-assignment-couple
perl -pi -e 's/[\n\r]*$//' output-home-assignment-couple
#insert code to copy output into SummaryPanel

#ABS: Aussie-Based-Single
sed -n '1,/\/\/ABS/p' ../ui/SummaryPanel_TEMPLATE.js > TOP
sed -n '/\/\/ABS/,$p' ../ui/SummaryPanel_TEMPLATE.js > BOT
echo "this.templates['aussie-based']['single'] = new Ext.XTemplate('" > MIDTOP
echo "');" > MIDBOT
cat MIDTOP output-aussie-based-single MIDBOT > MIDTOPBOT
sed 'N;s/\n//;P;D' MIDTOPBOT > MID
rm MIDTOP MIDBOT MIDTOPBOT
cat TOP MID BOT > SummaryPanel_output.js
rm TOP MID BOT

#ABC: Aussie-Based-Couple
sed -n '1,/\/\/ABC/p' SummaryPanel_output.js > TOP
sed -n '/\/\/ABC/,$p' SummaryPanel_output.js > BOT
echo "this.templates['aussie-based']['spouse'] = new Ext.XTemplate('" > MIDTOP
echo "');" > MIDBOT
cat MIDTOP output-aussie-based-couple MIDBOT > MIDTOPBOT
sed 'N;s/\n//;P;D' MIDTOPBOT > MID
rm MIDTOP MIDBOT MIDTOPBOT
cat TOP MID BOT > SummaryPanel_output.js
rm TOP MID BOT

#IAS: International-Assignment-Single
sed -n '1,/\/\/IAS/p' SummaryPanel_output.js > TOP
sed -n '/\/\/IAS/,$p' SummaryPanel_output.js > BOT
echo "this.templates['international-assignment']['single'] = new Ext.XTemplate('" > MIDTOP
echo "');" > MIDBOT
cat MIDTOP output-international-assignment-single MIDBOT > MIDTOPBOT
sed 'N;s/\n//;P;D' MIDTOPBOT > MID
rm MIDTOP MIDBOT MIDTOPBOT
cat TOP MID BOT > SummaryPanel_output.js
rm TOP MID BOT

#IAC: International-Assignment-Couple
sed -n '1,/\/\/IAC/p' SummaryPanel_output.js > TOP
sed -n '/\/\/IAC/,$p' SummaryPanel_output.js > BOT
echo "this.templates['international-assignment']['spouse'] = new Ext.XTemplate('" > MIDTOP
echo "');" > MIDBOT
cat MIDTOP output-international-assignment-couple MIDBOT > MIDTOPBOT
sed 'N;s/\n//;P;D' MIDTOPBOT > MID
rm MIDTOP MIDBOT MIDTOPBOT
cat TOP MID BOT > SummaryPanel_output.js
rm TOP MID BOT

#HAS: Home-Assignment-Single
sed -n '1,/\/\/HAS/p' SummaryPanel_output.js > TOP
sed -n '/\/\/HAS/,$p' SummaryPanel_output.js > BOT
echo "this.templates['home-assignment']['single'] = new Ext.XTemplate('" > MIDTOP
echo "');" > MIDBOT
cat MIDTOP output-home-assignment-single MIDBOT > MIDTOPBOT
sed 'N;s/\n//;P;D' MIDTOPBOT > MID
rm MIDTOP MIDBOT MIDTOPBOT
cat TOP MID BOT > SummaryPanel_output.js
rm TOP MID BOT

#IAC: Home-Assignment-Couple
sed -n '1,/\/\/HAC/p' SummaryPanel_output.js > TOP
sed -n '/\/\/HAC/,$p' SummaryPanel_output.js > BOT
echo "this.templates['home-assignment']['spouse'] = new Ext.XTemplate('" > MIDTOP
echo "');" > MIDBOT
cat MIDTOP output-home-assignment-couple MIDBOT > MIDTOPBOT
sed 'N;s/\n//;P;D' MIDTOPBOT > MID
rm MIDTOP MIDBOT MIDTOPBOT
cat TOP MID BOT > SummaryPanel_output.js
rm TOP MID BOT

rm input-aussie-based-single
rm output-aussie-based-single
rm input-aussie-based-couple
rm output-aussie-based-couple
rm input-international-assignment-single
rm output-international-assignment-single
rm input-home-assignment-single
rm output-home-assignment-single
rm input-international-assignment-couple
rm output-international-assignment-couple
rm input-home-assignment-couple
rm output-home-assignment-couple