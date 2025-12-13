#!/bin/sh
npm install
bower install --allow-root
gulp sass
ionic cordova build android
