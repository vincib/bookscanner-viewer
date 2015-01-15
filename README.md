bookscanner-viewer
==================

Viewer and Proofreader web interface for a Bookscanner

This is the book scanner project viewer, proofreader and sharing platform

Requires: php5.3+ mysql5+ a webserver and r/w access to a bookscanner project folder.


install
=======

To install the bookscanner project viewer & proofreader, do as follow : 

* first ensure you have a webserver with php5.3 or better, a mysql-server with a fresh database available
* create a username that can access your database
* inject the dump.sql file into this database, this will create an empty DB with a default benjamin/poipoi username
* copy config.php.sample to config.php and setup the database login and the root book/projects folder inside it.
* point a http (or better https) vhost into the www/ folder.
* if you are using Apache, please allow .htaccess parsing using "AllowOverride All" 
* if you are using another web server, setup the rewriting so that every non-file points to index.php
* setup a crontab (scheduled-task) to launch the sh/scanner.php file at least once a day.

dependencies
============

If you want to be able to do image enhancement, please install the latest Scantailor. 
On Debian, you may need to use the backport version or our private repository at http://debian.octopuce.fr/octopuce/
(on Debian: # aptitude install scantailor )

If you want to be able to do PDF image generation, please install ImageMagick and pdftk and libjpeg-progs
(on Debian: # aptitude install imagemagick pdftk )

If you want to be able to pass the images through the OCR process, please install tesseract
(on Debian: # aptitude install tesseract-ocr tesseract-ocr-fra tesseract-ocr-reng tesseract-ocr-deu )

for DJVU generation, you'll need djvubind.
git clone https://github.com/strider1551/djvubind.git



(C) Benjamin Sonntag 2014, Licensed under GPL-v3+ 

