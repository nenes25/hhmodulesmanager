#!/bin/bash
moduleName=hhmodulesmanager
buildDir="/home/herve/www/build/"
cd $buildDir
rm -rf $moduleName
git clone git@github.com:nenes25/${moduleName}.git $moduleName
cd $moduleName
php7.4 `which composer2` install
rm -rf .git/
rm -rf .github/
rm -rf .gitignore
rm -rf config_fr.xml
rm -rf .php_cs.cache
rm -rf .php_cs.dist
rm -rf tests/
rm -rf _dev
cd ../
rm -rf $moduleName.zip
zip $moduleName.zip -r ${moduleName}/
echo "Build zip make in ${buildDir}${moduleName}.zip"