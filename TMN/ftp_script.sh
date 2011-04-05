
echo ''
echo 'Start Publishing TMN'
echo ''
echo 'Start Export'
echo ''

mkdir ~/svn_temp
cd ~/svn_temp
svn export --force svn://harro@10.32.16.4/svn/tmn/tags/TMN%202.1.5

echo ''
echo 'Export Complete'
echo 'Start String Replacement'
echo ''

cd ~/svn_temp/TMN\ 2.1.5
perl -pi -e 's/VERSIONNUMBER\ =\ \"2-1-1\"/VERSIONNUMBER\ =\ \"2-1-5\"/g;' index.php
perl -pi -e 's/DEBUG\ =\ 1/DEBUG\ =\ 0/g;' *.php
perl -pi -e 's/DEBUG\ =\ 1/DEBUG\ =\ 0/g;' php/*.php
perl -pi -e 's/console/\/\/console/g;' ui/*.js

echo 'String Replacement Complete'
echo 'Starting File Compression'
echo ''

echo 'Compressing JavaScript Files ...'
cd ui
cat AuthorisationPanel.js SummaryPanel.js PrintForm.js InternalTransfers.js FinancialDetailsForm.js PersonalDetailsForm.js TmnView.js TmnController.js > tmn-all_long.js
java -jar /Applications/yuicompressor-2.4.2/build/yuicompressor-2.4.2.jar -o tmn-all.js tmn-all_long.js
rm tmn-all_long.js

echo 'Compressing CSS Files ...'
cd ../lib
cat resources/css/loading.css resources/css/ext-all.css resources/css/customstyles.css statusbar/css/statusbar.css > tmn-all_long.css
java -jar /Applications/yuicompressor-2.4.2/build/yuicompressor-2.4.2.jar -o resources/css/tmn-all.css tmn-all_long.css
rm tmn-all_long.css

echo 'Compressing ExtJS Files ...'
cat ext-base.js ext-all.js > ext.js

echo 'Compressing Custom Library Files ...'
cat DateRangeValidationType.js statusbar/StatusBar.js statusbar/ValidationStatus.js Printer-all.js iconcombo/Ext.ux.IconCombo.js > custom-libraries-all_long.js
java -jar /Applications/yuicompressor-2.4.2/build/yuicompressor-2.4.2.jar -o custom-libraries-all.js custom-libraries-all_long.js
rm custom-libraries-all_long.js

echo ''
echo 'File Compression Complete'
echo 'Starting FTP Upload'
echo ''

ftp -inv mportal.ccca.org.au<<ENDFTP
user mportal ***REMOVED***
cd public_html/TMN
lcd "~/svn_temp/TMN 2.1.5"
mput *.php
mkdir pdf
mput pdf/*
mkdir php
mput php/*
mkdir php/calc
mput php/calc/*
mkdir ui
mput ui/*
mkdir lib
mput lib/*
mkdir lib/resources
mput lib/resources/*
mkdir lib/resources/css
mput lib/resources/css/*
mkdir lib/statusbar
mput lib/statusbar/*
mkdir lib/statusbar/css
mput lib/statusbar/css/*
bye
ENDFTP

echo ''
echo 'FTP Upload Complete'
echo 'Starting Cleaning Up'
echo ''

rm -rf ~/svn_temp
cd ~

echo 'Clean Up Complete'
echo 'You are now in your Home directory.'
echo 'TMN has been Published, Good Bye.'
