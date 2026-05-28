#!/bin/bash
moduleName=hhmodulesmanager
buildDir="/home/herve/www/build/"
cd $buildDir
rm -rf $moduleName
git clone git@github.com:nenes25/${moduleName}.git $moduleName
cd $moduleName
php8.1 `which composer` install --no-dev
rm -rf .git/
rm -rf .github/
rm -rf .gitignore
rm -rf config_fr.xml
rm -rf .php_cs.cache
rm -rf .php_cs.dist
rm -rf tests/
rm -rf _dev
rm phpunit.xml.dist
cd ../
rm -rf $moduleName.zip
zip $moduleName.zip -r ${moduleName}/
echo "Build zip make in ${buildDir}${moduleName}.zip"