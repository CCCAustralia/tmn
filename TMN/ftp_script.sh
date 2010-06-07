cd ~/svn_temp
svn export --force svn://harro@10.32.16.4/svn/tmn/tags/TMN%202.1.1
cd ~/svn_temp/TMN\ 2.1.1
perl -pi -e 's/VERSIONNUMBER\ =\ \"2-1-1\"/VERSIONNUMBER\ =\ \"2-1-2\"/g;' index.php
perl -pi -e 's/DEBUG\ =\ 1/DEBUG\ =\ 0/g;' *.php
perl -pi -e 's/DEBUG\ =\ 1/DEBUG\ =\ 0/g;' php/*.php
perl -pi -e 's/console/\/\/console/g;' ui/*.js


cd ui
cat PrintForm.js InternalTransfers.js FinancialDetailsForm.js PersonalDetailsForm.js TmnView.js TmnController.js > tmn-all_long.js
java -jar /Applications/yuicompressor-2.4.2/build/yuicompressor-2.4.2.jar -o tmn-all.js tmn-all_long.js
rm tmn-all_long.js

cd ../lib
cat resources/css/loading.css resources/css/ext-all.css resources/css/customstyles.css statusbar/css/statusbar.css > css-all_long.css
java -jar /Applications/yuicompressor-2.4.2/build/yuicompressor-2.4.2.jar -o resources/css/css-all.css css-all_long.js
rm css-all_long.css

cat ext-base.js ext-all.js > ext.js

cat DateRangeValidationType.js statusbar/StatusBar.js statusbar/ValidationStatus.js Printer-all.js > custom-libraries-all_long.js
java -jar /Applications/yuicompressor-2.4.2/build/yuicompressor-2.4.2.jar -o custom-libraries-all.js custom-libraries-all_long.js
rm custom-libraries-all_long.js

ftp -inv mportal.ccca.org.au<<ENDFTP
user mportal ***REMOVED***
cd public_html/TMN
lcd "~/svn_temp/TMN 2.1.1"
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
bye
ENDFTP