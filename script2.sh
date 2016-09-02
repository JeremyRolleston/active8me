#!/bin/bash

DIR1="/var/www/html/active8me/app/tmp/cache/models/"
DIR2="/var/www/html/active8me/app/tmp/cache/persistent/"
DIR3="/var/www/html/active8me/app/tmp/logs/"

if [ "$(ls -A $DIR1)" ]; then
     rm -rf /var/www/html/active8me/app/tmp/cache/models/*
else
    echo "$DIR1 is Empty"
fi

if [ "$(ls -A $DIR2)" ]; then
     rm -rf /var/www/html/active8me/app/tmp/cache/persistent/*
else
    echo "$DIR2 is Empty"
fi

if [ "$(ls -A $DIR3)" ]; then
     rm -rf /var/www/html/active8me/app/tmp/logs/*
else
    echo "$DIR3 is Empty"
fi