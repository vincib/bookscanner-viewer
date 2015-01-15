<?php

// we will see that later :) 
$lang="en";

$MY=array("localhost","bv","poipoi","bv"); // host user pass database

// Path where the projects will be stored (in the filesystem) NO ENDING / !!!
define("PROJECT_ROOT","/tr2/bookscanner/bookscanner");

// Path where we find up-to-date thumbnails of the pictures we would like to read, 
// see freedesktop specifications here for .thumbnails support http://web.archive.org/web/20060117053121/jens.triq.net/thumbnail-spec/index.html
define("THUMBNAILS_ROOT","/tr2/bookscanner/.thumbnails");
define("THUMBNAILS_URL","/thumbs");
define("SECRET_CODE","123345647854");
define("DELETE_LOG","/tr2/bookscanner/delete.log");
define("DELETE_BIN","/tr2/bookscanner/.trash");

// can be either "spreads" or "leftright"
// defaults to leftright
//define("SCANTAILOR_GENERATOR_MODE","spreads");
define("SCANTAILOR_GENERATOR_MODE","leftright");
// X11 display where we launch scantailor?
define("DISPLAY","DISPLAY=:1.0");

