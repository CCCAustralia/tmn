
version         = '2.2.0'
ftp_uname       = 'mportal'
ftp_pword       = '***REMOVED***'
full_refresh    = true

echo ''
echo 'Start Publishing TMN'
echo ''
echo 'Start Creating Tag'
echo ''

svn copy --force "svn://harro@10.32.16.4/svn/tmn/trunk/TMN" "svn://harro@10.32.16.4/svn/tmn/tags/TMN%20${version}"

echo ''
echo 'Tag Creation Complete'
echo 'Start Export'
echo ''

mkdir ~/svn_temp
cd ~/svn_temp
svn export --force "svn://harro@10.32.16.4/svn/tmn/tags/TMN%20${version}"

echo ''
echo 'Export Complete'
echo 'Start String Replacement'
echo ''

cd "~/svn_temp/TMN ${version}"
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
cd customclasses
cat Ext.LinkButton.js DateRangeValidationType.js statusbar/StatusBar.js statusbar/ValidationStatus.js Printer-all.js Ext.ux.IconCombo.js > custom-libraries-all_long.js
java -jar /Applications/yuicompressor-2.4.2/build/yuicompressor-2.4.2.jar -o custom-libraries-all.js custom-libraries-all_long.js
rm custom-libraries-all_long.js

echo ''
echo 'File Compression Complete'
echo 'Starting FTP Upload'
echo ''

if $full_refresh ;
then
    cd ../../
    tar tar -czf lib.gz lib

    ftp -inv mportal.ccca.org.au<<ENDFTP
    user ${ftp_uname} ${ftp_pword}
    cd public_html/TMN
    lcd "~/svn_temp/TMN ${version}"
    mput *.php
    mkdir images
    mput images/*
    mkdir pdf
    mput pdf/*
    mkdir php
    mput php/*
    mkdir php/auth
    mput php/auth/*
    mkdir php/classes
    mput php/classes/*
    mkdir php/imp
    mput php/imp/*
    mkdir php/interfaces
    mput php/interfaces/*
    mkdir ui
    mput ui/*
    put lib.gz
    bye
    ENDFTP

    rm lib.gz

else
    ftp -inv mportal.ccca.org.au<<ENDFTP
    user ${ftp_uname} ${ftp_pword}
    cd public_html/TMN
    lcd "~/svn_temp/TMN ${version}"
    mput *.php
    mkdir php
    mput php/*
    mkdir php/auth
    mput php/auth/*
    mkdir php/classes
    mput php/classes/*
    mkdir php/imp
    mput php/imp/*
    mkdir php/interfaces
    mput php/interfaces/*
    mkdir ui
    mput ui/*
    bye
    ENDFTP
fi



echo ''
echo 'FTP Upload Complete'
echo 'Starting Cleaning Up'
echo ''

rm -rf ~/svn_temp
cd ~

echo 'Clean Up Complete'
echo 'You are now in your Home directory.'
echo 'TMN has been Published, Good Bye.'
