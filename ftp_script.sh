#!/bin/sh

version='2.4'
repo_url='git@10.32.16.4:/srv/repos/git/tmn.git'
yuicompressor_path='lib/yuicompressor-2.4.2/build/yuicompressor-2.4.2.jar'
ftp_uname='mportal'
ftp_pword='***REMOVED***'
ftp_destination='TMN'
create_tag=false
full_refresh=true

#save the current directory so the user can be returned here
pushd . > /dev/null

echo ''
echo 'Start Publishing TMN'

echo 'Start Clone'
echo ''

mkdir ~/tmn_temp
cd ~/tmn_temp
git clone ${repo_url}
cd tmn/TMN

echo ''
echo 'Clone Complete'
echo 'Start String Replacement'
echo ''

if $create_tag ; then
echo ''
echo 'Start Creating Tag'
echo ''

git tag "TMN${version}"
git push --tags
#svn copy "svn://${svn_uname}@10.32.16.4/svn/tmn/trunk/TMN" "svn://${svn_uname}@10.32.16.4/svn/tmn/tags/TMN%20${version}" --password ${svn_pword} -m "Created tag for version ${version}"

echo ''
echo 'Tag Creation Complete'
fi

ls
perl -pi -e 's/BUILDNUMBER[\ \t]*=[\ \t]*\"current_build_number_will_be_inserted_by_upload_script\"/BUILDNUMBER\ =\ \"${version}\"/g;' *.php
perl -pi -e 's/DEBUG[\ \t]*=[\ \t]*1/DEBUG\ =\ 0/g;' *.php
perl -pi -e 's/DEBUG[\ \t]*=[\ \t]*1/DEBUG\ =\ 0/g;' php/*.php
perl -pi -e 's/[\ \t]*\$this->DEBUG[\ \t]*=[\ \t]*1/\t\t\$this->DEBUG\ =\ 0/g;' php/classes/Reporter.php
perl -pi -e 's/console/\/\/console/g;' ui/*.js

echo 'String Replacement Complete'
echo ''

echo 'Starting File Compression'
echo ''

echo 'Compressing JavaScript Files ...'
cd ui
cat AuthorisationPanel.js SummaryPanel.js PrintForm.js InternalTransfers.js FinancialDetailsForm.js PersonalDetailsForm.js TmnView.js TmnController.js > tmn-all_long.js
java -jar ${yuicompressor_path} -o tmn-all.js tmn-all_long.js
rm tmn-all_long.js

cat AuthorisationViewerControlPanel.js AuthorisationPanel.js SummaryPanel.js authviewer.js > tmn-authviewer-all_long.js
java -jar ${yuicompressor_path} -o tmn-authviewer-all.js tmn-authviewer-all_long.js
rm tmn-authviewer-all_long.js

cat AdminViewerControlPanel.js AuthorisationPanel.js SummaryPanel.js adminviewer.js > tmn-adminviewer-all_long.js
java -jar ${yuicompressor_path} -o tmn-adminviewer-all.js tmn-adminviewer-all_long.js
rm tmn-adminviewer-all_long.js

cat SummaryPanel.js viewer.js > viewer-all_long.js
java -jar ${yuicompressor_path} -o viewer-all.js viewer-all_long.js
rm viewer-all_long.js

echo 'Compressing CSS Files ...'
cd ../lib
cat resources/css/loading.css resources/css/ext-all.css resources/css/customstyles.css customclasses/statusbar/css/statusbar.css > tmn-all_long.css
java -jar ${yuicompressor_path} -o resources/css/tmn-all.css tmn-all_long.css
rm tmn-all_long.css

echo 'Compressing ExtJS Files ...'
cat ext-base.js ext-all.js > ext.js

echo 'Compressing Custom Library Files ...'
cd customclasses
cat Ext.LinkButton.js DateRangeValidationType.js statusbar/StatusBar.js statusbar/ValidationStatus.js Printer-all.js Ext.ux.IconCombo.js > custom-libraries-all_long.js
java -jar ${yuicompressor_path} -o custom-libraries-all.js custom-libraries-all_long.js
rm custom-libraries-all_long.js

cd ../../

echo ''
echo 'File Compression Complete'


if $full_refresh ; then

echo 'Starting Full FTP Upload'
echo ''
echo 'Starting lib Compression'
echo ''

zip -r lib.zip lib/

echo 'lib Compression Complete'
echo ''

ftp -inv mportal.ccca.org.au<<ENDFTP
user ${ftp_uname} ${ftp_pword}
mkdir "public_html/${ftp_destination}"
cd "public_html/${ftp_destination}"
lcd "~/svn_temp/TMN ${version}"
mput *.php
mkdir images
mput images/*
mkdir pdf
mput pdf/*
mkdir php
mput php/*
mkdir php/admin
mput php/admin/*
mkdir php/auth
mput php/auth/*
mkdir php/classes
mput php/classes/*
mkdir php/imp
mput php/imp/*
mkdir php/interfaces
mput php/interfaces/*
mkdir php/logs
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
mkdir php/admin
mput php/admin/*
mkdir php/auth
mput php/auth/*
mkdir php/classes
mput php/classes/*
mkdir php/imp
mput php/imp/*
mkdir php/interfaces
mput php/interfaces/*
mkdir php/logs
mkdir ui
mput ui/*
bye
ENDFTP
fi



echo ''
echo 'FTP Upload Complete'
echo 'Starting Cleaning Up'
echo ''

rm -rf ~/tmn_temp

#return user to there original directory
popd > /dev/null

echo 'Clean Up Complete'
echo 'TMN has been Published, Good Bye.'
