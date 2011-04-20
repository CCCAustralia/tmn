
version='2.2.3'
svn_uname='harro'
svn_pword='jonathan'
ftp_uname='mportal'
ftp_pword='***REMOVED***'
ftp_destination='TMN'
create_tag=true
full_refresh=true

#save the current directory so the user can be returned here
pushd . > /dev/null

echo ''
echo 'Start Publishing TMN'

if $create_tag ; then
echo ''
echo 'Start Creating Tag'
echo ''

svn copy "svn://${svn_uname}@10.32.16.4/svn/tmn/trunk/TMN" "svn://${svn_uname}@10.32.16.4/svn/tmn/tags/TMN%20${version}" --password ${svn_pword} -m "Created tag for version ${version}"
fi

echo ''
echo 'Tag Creation Complete'
echo 'Start Export'
echo ''

mkdir ~/svn_temp
cd ~/svn_temp
svn export --force "svn://${svn_uname}@10.32.16.4/svn/tmn/tags/TMN%20${version}"

echo ''
echo 'Export Complete'
echo 'Start String Replacement'
echo ''

ls
cd "TMN ${version}"
perl -pi -e 's/DEBUG[\ \t]*=[\ \t]*1/DEBUG\ =\ 0/g;' *.php
perl -pi -e 's/DEBUG[\ \t]*=[\ \t]*1/DEBUG\ =\ 0/g;' php/*.php
perl -pi -e 's/[\ \t]*$this->DEBUG[\ \t]*=[\ \t]*1/\t\t\$this->DEBUG\ =\ 0/g;' php/classes/Reporter.php
perl -pi -e 's/console/\/\/console/g;' ui/*.js

echo 'String Replacement Complete'
echo 'Starting File Compression'
echo ''

echo 'Compressing JavaScript Files ...'
cd ui
cat AuthorisationPanel.js SummaryPanel.js PrintForm.js InternalTransfers.js FinancialDetailsForm.js PersonalDetailsForm.js TmnView.js TmnController.js > tmn-all_long.js
java -jar /Applications/yuicompressor-2.4.2/build/yuicompressor-2.4.2.jar -o tmn-all.js tmn-all_long.js
rm tmn-all_long.js

cat AuthorisationViewerControlPanel.js AuthorisationPanel.js SummaryPanel.js authviewer.js > tmn-authviewer-all_long.js
java -jar /Applications/yuicompressor-2.4.2/build/yuicompressor-2.4.2.jar -o tmn-authviewer-all.js tmn-authviewer-all_long.js
rm tmn-authviewer-all_long.js

cat SummaryPanel.js viewer.js > viewer-all_long.js
java -jar /Applications/yuicompressor-2.4.2/build/yuicompressor-2.4.2.jar -o viewer-all.js viewer-all_long.js
rm viewer-all_long.js

echo 'Compressing CSS Files ...'
cd ../lib
cat resources/css/loading.css resources/css/ext-all.css resources/css/customstyles.css customclasses/statusbar/css/statusbar.css > tmn-all_long.css
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


if $full_refresh ; then

echo 'Starting Full FTP Upload'
echo ''
echo 'Starting lib Compression'
echo ''

cd ../../
zip -r lib.zip lib/

echo 'lib Compression Complete'
echo ''

ftp -inv mportal.ccca.org.au<<ENDFTP
user ${ftp_uname} ${ftp_pword}
cd "public_html/${ftp_destination}"
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
put lib.zip
bye
ENDFTP

rm lib.zip

else

echo 'Starting Partial FTP Upload'
echo ''
echo 'Starting FTP Upload'
echo ''

ftp -inv mportal.ccca.org.au<<ENDFTP
user ${ftp_uname} ${ftp_pword}
mkdir "public_html/${ftp_destination}"
cd "public_html/${ftp_destination}"
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
put lib/customclasses/custom-libraries-all.js
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

#return user to there original directory
popd > /dev/null

echo 'Clean Up Complete'
echo 'TMN has been Published, Good Bye.'
