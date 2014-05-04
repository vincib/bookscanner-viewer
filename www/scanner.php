<?php

// This php-cli script scans all the project folders
// search for metadata, and update the database accordingly

require_once("common.php"); 

$d=opendir(PROJECT_ROOT);
while (($project=readdir($d))!==false) {
  if (substr($project,0,1)==".") continue;
  if (
      is_dir(PROJECT_ROOT."/".$project) &&
      is_file(PROJECT_ROOT."/".$project."/meta.json")
      ) {
    scanproject($project);
  }
}
closedir($d);

function debug($str) {
  echo "[".date("Y-m-d H:i:s")."] $str\n";
}

/**
 * Scanning ONE $project folder for changes and store them into the database
 * log everything into booklog table
 * scan metadata, original images, scantailor file, low-resolution image pdf file, 
 * tif from scantailor, tif.txt from tesseract, and odt result timestamps.
 */
function scanproject($project) {
  debug("Scanning $project");
  $root=PROJECT_ROOT."/".$project."/";
  $data=mqone("SELECT * FROM books WHERE projectname='".addslashes($project)."';");
  $created=false;

  // New book ?
  if (!$data) {
    mq("INSERT INTO books SET projectname='".addslashes($project)."';");
    $data=mqone("SELECT * FROM books WHERE projectname='".addslashes($project)."';");
    booklog($data["id"],BOOKLOG_BOTINFO,"Book detected and indexed in project folder");
    $created=true;
  }
  
  // metadata changed ?
  if ($data["meta_ts"]<filemtime($root."meta.json")) {
    if (!$created) booklog($data["id"],BOOKLOG_BOTINFO,"Metadata changed and indexed");
    $meta=json_decode(file_get_contents($root."meta.json"),true);
    $dateprecision=0;
    if (preg_match('#^([0-9]+)-([0-9]+)-([0-9]+)$#',$meta["date"],$mat)) {
      $date=$mat[1]."-".$mat[2]."-".$mat[3]; $dateprecision=3;
    }
    if (preg_match('#^([0-9]+)-([0-9]+)$#',$meta["date"],$mat)) {
      $date=$mat[1]."-".$mat[2]."-"."01"; $dateprecision=2;
    }
    if (preg_match('#^([0-9]+)$#',$meta["date"],$mat)) {
      $date=$mat[1]."-"."01"."-"."01"; $dateprecision=1;
    }
    $isbn=preg_replace("[^0-9]","",$meta["ean13"]);
    mq("UPDATE books SET
    date='".addslashes($date)."',
    dateprecision='".addslashes($dateprecision)."',
    title='".addslashes($meta["title"])."',
    authors='".addslashes(implode("\n",$meta["author"]))."',
    publisher='".addslashes($meta["publisher"])."',
    isbn='".addslashes($isbn)."',
    lang='".addslashes($meta["lang"])."',
    meta_ts=".filemtime($root."meta.json")." 
    WHERE id=".$data["id"].";");
    if (mysql_errno()) echo "ERR: ".mysql_error()."\n";
  } // metadata changed / created ? 

  // original picture changed ? 
  if (is_dir($root."left") && is_dir($root."right")
      && ( $data["scan_ts"]<filemtime($root."left") || $data["scan_ts"]<filemtime($root."right"))
      ) {
    // find the latest JPG file : 
    if (!$created) booklog($data["id"],BOOKLOG_BOTINFO,"Scan folder updated");
    $scan_ts=max(filemtime($root."left"),filemtime($root."right"));
    $d=opendir($root."left");
    while (($c=readdir($d))!==false) {
      if (substr($c,0,1)==".") continue;
      if (is_file($root."left/".$c)) $scan_ts=max($scan_ts,filemtime($root."left/".$c));
    } 
    closedir($d);
    $d=opendir($root."right");
    while (($c=readdir($d))!==false) {
      if (substr($c,0,1)==".") continue;
      if (is_file($root."right/".$c)) $scan_ts=max($scan_ts,filemtime($root."right/".$c));
    } 
    closedir($d);
    mq("UPDATE books SET scan_ts=$scan_ts WHERE id=".$data["id"].";");
    if (mysql_errno()) echo "ERR: ".mysql_error()."\n";
  } // scan_ts changed / created ?

  // Scantailor project file
  if (is_file($root.$project.".scantailor")
      && $data["scantailor_ts"]<filemtime($root.$project.".scantailor")
      ) {
    if (!$created) booklog($data["id"],BOOKLOG_BOTINFO,"Scantailor project updated");
    mq("UPDATE books SET scantailor_ts=".filemtime($root.$project.".scantailor")." WHERE id=".$data["id"].";");
    if (mysql_errno()) echo "ERR: ".mysql_error()."\n";
  } // scantailor_ts changed / created ?

  // low resolution image pdf file 
  if (is_file($root."book.pdf")
      && $data["bookpdf_ts"]<filemtime($root."book.pdf")
      ) {
    if (!$created) booklog($data["id"],BOOKLOG_BOTINFO,"Image PDF updated");
    mq("UPDATE books SET bookpdf_ts=".filemtime($root."book.pdf")." WHERE id=".$data["id"].";");
    if (mysql_errno()) echo "ERR: ".mysql_error()."\n";
  } // bookpdf_ts changed / created ?
  
  // ODT result
  if (is_file($root."book.odt")
      && $data["odt_ts"]<filemtime($root."book.odt")
      ) {
    if (!$created) booklog($data["id"],BOOKLOG_BOTINFO,"ODT text updated");
    mq("UPDATE books SET odt_ts=".filemtime($root."book.odt")." WHERE id=".$data["id"].";");
    if (mysql_errno()) echo "ERR: ".mysql_error()."\n";
  } // odt_ts changed / created ?


  // booktif and ocr requires booktif/ folder
  if (!is_dir($root."booktif")) {
    return;
  }

  // scanning booktif for TIF and TXT files, get the latest of each for booktif_ts and ocr_ts.
  if ($data["booktif_ts"]<filemtime($root."booktif") || $data["ocr_ts"]<filemtime($root."booktif")
      ) {
    $ocr_ts=$data["ocr_ts"];
    $booktif_ts=$data["booktif_ts"];
    $d=opendir($root."booktif");
    while (($c=readdir($d))!==false) {
      if (substr($c,0,1)==".") continue;
      if (is_file($root."booktif/".$c) && substr($c,-4)==".tif" && filemtime($root."booktif/".$c)>$booktif_ts) $booktif_ts=filemtime($root."booktif/".$c);
      if (is_file($root."booktif/".$c) && substr($c,-4)==".txt" && filemtime($root."booktif/".$c)>$ocr_ts) $ocr_ts=filemtime($root."booktif/".$c);
    } 
    closedir($d);
    if (!$created) {
      if ($ocr_ts!=$data["ocr_ts"]) booklog($data["id"],BOOKLOG_BOTINFO,"OCR text files updated");
      if ($booktif_ts!=$data["booktif_ts"]) booklog($data["id"],BOOKLOG_BOTINFO,"scantailor output files updated");
    }
    if ($ocr_ts!=$data["ocr_ts"] || $booktif_ts!=$data["booktif_ts"]) {
      mq("UPDATE books SET ocr_ts=$ocr_ts, booktif_ts=$booktif_ts WHERE id=".$data["id"].";");
      if (mysql_errno()) echo "ERR: ".mysql_error()."\n";
    }
  } // booktif_ts or ocr_ts changed / created ?

}
