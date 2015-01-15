#!/usr/bin/env php
<?php

   /**
    * This php-cli script scans all the project folders
    * search for metadata, and update the database accordingly
    * it should be scheduled once a day or a little bit more
    * (better not schedule it during a scan/post-process meeting) 
    */

require_once("../www/common.php"); 

$d=opendir(PROJECT_ROOT);
while (($project=readdir($d))!==false) {
  if (substr($project,0,1)==".") continue;
  if (
      is_dir(PROJECT_ROOT."/".$project) &&
      is_dir(PROJECT_ROOT."/".$project."/raw")
      ) {
    scanproject($project);
  }
}
closedir($d);

function debug($str) {
  echo "[".date("Y-m-d H:i:s")."] $str\n";
}
