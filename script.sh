#!/bin/bash

DIR1="/var/www/html/active8me/app/tmp/cache/models/"
DIR2="/var/www/html/active8me/app/tmp/cache/persistent/"
RELOAD=false
PERM1="/var/www/html/active8me/app/webroot/img/users"
PERM2="/var/www/html/active8me/app/tmp/cache"
PERM3="/var/www/html/active8me/app/tmp/logs"

#set permissions as a double check
chmod -R 777 $PERM1
chmod -R 777 $PERM2
chmod -R 777 $PERM3
# look for empty dir
if [ "$(ls -A $DIR1)" ]; then
     rm -rf /var/www/html/active8me/app/tmp/cache/models/*
     RELOAD=true
fi

if [ "$(ls -A $DIR2)" ]; then
     rm -rf /var/www/html/active8me/app/tmp/cache/persistent/*
     RELOAD=true
fi
service php-fpm reload
exit
