<?php


define("ROLE_ADMIN","1");
define("ROLE_EDITOR","2");
$arole=array(
	      "1" => "Administrator",
	      "2" => "Editor",
	      );

$alang= array(
	      "fre" => "French",
	      "eng" => "English",
	      "deu" => "Deutsch",
	      );
define("BOOKLOG_BOTINFO","1");
define("BOOKLOG_BOTERROR","2");
define("BOOKLOG_HUMAN","10");
$abltype=array(
	       BOOKLOG_BOTINFO => "Bot Information",
	       BOOKLOG_BOTERROR => "Bot Error",
	       BOOKLOG_HUMAN => "Human Information",
	       );

define("STATUS_SCANNING","10");
define("STATUS_SCANOK","20");
define("STATUS_DOINGTAILOR1","30");
define("STATUS_WAITUSERTAILOR","40");
define("STATUS_USEROKTAILOR6","50");
define("STATUS_DOINGTAILOR6","60");
define("STATUS_TAILOROK","70");
define("STATUS_OCRING","80");
define("STATUS_PROOFREADING","90");

define("STATUS_UNKNOWN","999");

$astatus=array(
	       STATUS_SCANNING => array(_("Scanning"),_("the project is in scanning state. we are scanning the book, it's not ended yet.")),
	       STATUS_SCANOK   => array(_("Scan OK, wait Scantailor"),_("all the pictures are here in right order, empty pictures at the start of left folder and end of right folder have been removed, ready for scantailor !")),
	       STATUS_DOINGTAILOR1 => array(_("Doing Scantailor 1-5"),_("the project is currently processed by scantailor for step 1 to 5 by an automatic program. the scantailor project named projectname_5.scantailor will be saved in projectname_6.scantailor")),
	       STATUS_WAITUSERTAILOR => array(_("Scantailor waiting for user"),_("The user need to manually open the scantailor project and check step 4 for proper content recognition. After changing it and saving it, the user can allow the project to go to step 5.")),
	       STATUS_USEROKTAILOR6 => array(_("User OK, wait Scantailor 6"),_("the project is ready to be processed by Scantailor at step 6 (and other treatments)")),
	       STATUS_DOINGTAILOR6 => array(_("Doing Scantailor step 6"),_("A program is taking the last scantailor project, encoding everything to tiff")),
	       STATUS_TAILOROK => array(_("Scantailor OK, wait for OCR/PDF"),_("Scantailor built the TIFF files, project is ready to be OCR-ed, PDF and DJVU + TEXT version made")),
	       STATUS_OCRING => array(_("OCR in progress"),_("the OCR, the PDF image, the DJVU and the TEXT ocr are in progress")),
	       STATUS_PROOFREADING => array(_("OCR done, Proofread in progress"),_("The OCR PDF DJVU and TEXT ocr have been made, the humans are now proofreading the book")),
	       STATUS_UNKNOWN => array(_("Status Unknown, ERROR"),_("Project in erroneous state, to be fixed by a human")),
	       );



function booklog($book,$type,$message) {
  global $abltype;
  if (!isset($abltype[$type])) {
    echo "ERROR: booklog called with bad type:$type ($message)\n";
  } else {
    mq("INSERT INTO booklog SET book='".intval($book)."', type='".intval($type)."', message='".addslashes($message)."';");
  }
}

// FIXME : cache this in a serialized txt file.
$alicense=array();
$t=mq("SELECT * FROM license");
$freelicenses=array();
while($c=mysql_fetch_assoc($t)) {
  $alicense[$c["id"]]=$c;
  if ($c["free"]) $freelicenses[]=$c["id"];
}


function user_login($id) {
  list($login)=@mysql_fetch_array(mysql_query("SELECT login FROM users WHERE id='".intval($id)."';"));
  return $login;
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
    $data["changed"]=0;
    $changed=0; //time();
    $attribs=array();
    $created=true;
  } else {
    $changed=$data["changed"];
    $attribs=@json_decode($data["attribs"],true);
  }
  // metadata changed ?
  if ($data["meta_ts"]<filemtime($root."meta.json")) {
    if (!$created) booklog($data["id"],BOOKLOG_BOTINFO,"Metadata changed and indexed");
    $meta=json_decode(file_get_contents($root."meta.json"),true);

    $allmeta=array("date","title","publisher","ean13","lang");
    foreach($allmeta as $m) if (!isset($meta[$m])) $meta[$m]="";
    if (!isset($meta["author"])) $meta["author"]=array();

    $dateprecision=0;
    $date="";
    if (preg_match('#^([0-9]+)-([0-9]+)-([0-9]+)$#',$meta["date"],$mat)) {
      $date=$mat[1]."-".$mat[2]."-".$mat[3]; $dateprecision=3;
    }
    if (preg_match('#^([0-9]+)-([0-9]+)$#',$meta["date"],$mat)) {
      $date=$mat[1]."-".$mat[2]."-"."01"; $dateprecision=2;
    }
    if (preg_match('#^([0-9]+)$#',$meta["date"],$mat)) {
      $date=$mat[1]."-"."01"."-"."01"; $dateprecision=1;
    }
    $isbn=preg_replace("#[^0-9]#","",$meta["ean13"]);
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
    $changed=max($changed,filemtime($root."meta.json"));
  } // metadata changed / created ? 
  
  // original picture changed ? 
  if (is_dir($root."left") && is_dir($root."right")
      && ( $data["scan_ts"]<filemtime($root."left") || $data["scan_ts"]<filemtime($root."right"))
      ) {
    // find the latest JPG file : 
    if (!$created) booklog($data["id"],BOOKLOG_BOTINFO,"Scan folder updated");
    $scan_ts=max(filemtime($root."left"),filemtime($root."right"));
    $d=opendir($root."left");
    $leftcount=0;
    $scansize=0;
    while (($c=readdir($d))!==false) {
      if (substr($c,0,1)==".") continue;
      if (is_file($root."left/".$c)) {
	$scan_ts=max($scan_ts,filemtime($root."left/".$c));
	$leftcount++;
	$scansize+=filesize($root."left/".$c);
      }
    } 
    closedir($d);
    $d=opendir($root."right");
    $rightcount=0;
    while (($c=readdir($d))!==false) {
      if (substr($c,0,1)==".") continue;
      if (is_file($root."right/".$c)) {
	$scan_ts=max($scan_ts,filemtime($root."right/".$c));
	$rightcount++;
	$scansize+=filesize($root."right/".$c);
      }
    } 
    closedir($d);
    mq("UPDATE books SET scan_ts=$scan_ts WHERE id=".$data["id"].";");
    if (mysql_errno()) echo "ERR: ".mysql_error()."\n";
    $changed=max($changed,intval($scan_ts));
    $attribs["leftcount"]=$leftcount;
    $attribs["rightcount"]=$rightcount;
    $attribs["scan_size"]=$scansize;
  } // scan_ts changed / created ?

  // Scantailor project file
  if (is_file($root.$project.".scantailor")
      && $data["scantailor_ts"]<filemtime($root.$project.".scantailor")
      ) {
    if (!$created) booklog($data["id"],BOOKLOG_BOTINFO,"Scantailor project updated");
    mq("UPDATE books SET scantailor_ts=".filemtime($root.$project.".scantailor")." WHERE id=".$data["id"].";");
    if (mysql_errno()) echo "ERR: ".mysql_error()."\n";
    $changed=max($changed,filemtime($root.$project.".scantailor"));
  } // scantailor_ts changed / created ?

  // low resolution image pdf file 
  if (is_file($root."book.pdf")
      && $data["bookpdf_ts"]<filemtime($root."book.pdf")
      ) {
    if (!$created) booklog($data["id"],BOOKLOG_BOTINFO,"Image PDF updated");
    mq("UPDATE books SET bookpdf_ts=".filemtime($root."book.pdf")." WHERE id=".$data["id"].";");
    if (mysql_errno()) echo "ERR: ".mysql_error()."\n";
    $changed=max($changed,filemtime($root."book.pdf"));
    $attribs["bookpdf_size"]=filesize($root."book.pdf");
    unset($out);
    exec("pdfinfo ".escapeshellarg($root."book.pdf"),$out);
    foreach($out as $o) 
      if (preg_match("#Pages: *([0-9]*)#",$o,$mat))
	$attribs["bookpdf_pages"]=intval($mat[1]);
  } // bookpdf_ts changed / created ?
 
  // ODT result
  if (is_file($root."book.odt")
      && $data["odt_ts"]<filemtime($root."book.odt")
      ) {
    if (!$created) booklog($data["id"],BOOKLOG_BOTINFO,"ODT text updated");
    mq("UPDATE books SET odt_ts=".filemtime($root."book.odt")." WHERE id=".$data["id"].";");
    if (mysql_errno()) echo "ERR: ".mysql_error()."\n";
    $changed=max($changed,filemtime($root."book.odt"));
    $attribs["odt_size"]=filesize($root."book.odt");
  } // odt_ts changed / created ?

  // EPUB result
  if (is_file($root."book.epub")
      && $data["epub_ts"]<filemtime($root."book.epub")
      ) {
    if (!$created) booklog($data["id"],BOOKLOG_BOTINFO,"EPUB text updated");
    mq("UPDATE books SET epub_ts=".filemtime($root."book.epub")." WHERE id=".$data["id"].";");
    if (mysql_errno()) echo "ERR: ".mysql_error()."\n";
    $changed=max($changed,filemtime($root."book.epub"));
    $attribs["epub_size"]=filesize($root."book.epub");
  } // odt_ts changed / created ?

  // booktif and ocr requires booktif/ folder
  if (is_dir($root."booktif")) {
    
    // scanning booktif for TIF and TXT files, get the latest of each for booktif_ts and ocr_ts.
    if ($data["booktif_ts"]<filemtime($root."booktif") || $data["ocr_ts"]<filemtime($root."booktif")
	) {
      $booktifcount=0;
      $ocrcount=0;
      $ocr_ts=$data["ocr_ts"];
      $booktif_ts=$data["booktif_ts"];
      $d=opendir($root."booktif");
      while (($c=readdir($d))!==false) {
	if (substr($c,0,1)==".") continue;
	if (is_file($root."booktif/".$c) && substr($c,-4)==".tif") {
	  if (filemtime($root."booktif/".$c)>$booktif_ts) $booktif_ts=filemtime($root."booktif/".$c);
	  $booktifcount++;
	}
	if (is_file($root."booktif/".$c) && substr($c,-4)==".txt") {
	  if (filemtime($root."booktif/".$c)>$ocr_ts) $ocr_ts=filemtime($root."booktif/".$c);
	  $ocrcount++;
	}
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
      $changed=max($changed,intval($ocr_ts));
      $changed=max($changed,intval($booktif_ts));
      $attribs["booktifcount"]=$booktifcount;
      $attribs["ocrcount"]=$ocrcount;
    } // booktif_ts or ocr_ts changed / created ?
    
  }
  
  $jattribs=json_encode($attribs);
  if ($jattribs!=$data["attribs"]) {
    mq("UPDATE books SET attribs='".addslashes($jattribs)."' WHERE id=".$data["id"].";");
  }
  if ($changed>$data["changed"]) {
    mq("UPDATE books SET changed=$changed WHERE id=".$data["id"].";");
  }
}


/* create the scantailor project
   then launch scantailor-cli for step 1 to 5, save the project to projectname_5.scantailor
*/
function scantailor1($book) {
  require_once("gen-scantailor.php");
  ob_start();
  gen_scantailor($book["projectname"]);
  $content=ob_get_clean();
  file_put_contents(PROJECT_ROOT."/".$book["projectname"]."/".$book["projectname"].".scantailor",$content);
  if (!is_file(PROJECT_ROOT."/".$book["projectname"]."/".$book["projectname"].".scantailor") ||
      filesize(PROJECT_ROOT."/".$book["projectname"]."/".$book["projectname"].".scantailor")<1000) {
    echo "gen-scantailor failed\n";
    return false;
  }
  // now launch scantailor
  chdir(PROJECT_ROOT."/".$book["projectname"]);
  passthru("scantailor-cli --start-filter=1 --end-filter=5 --output-project=".escapeshellarg($book["projectname"]."_5.scantailor")."  ".escapeshellarg($book["projectname"].".scantailor")." ".escapeshellarg(PROJECT_ROOT."/".$book["projectname"]."/booktif")." 2>&1",$ret);
  if ($ret!=0) {
    echo "scantailor-cli failed\n";
    return false;
  }
  return true;
}


/* launch scantailor-cli for step 6, save the project to projectname_6.scantailor
*/
function scantailor6($book) {

  chdir(PROJECT_ROOT."/".$book["projectname"]);
  passthru("scantailor-cli --start-filter=6 --end-filter=6 --output-project=".escapeshellarg($book["projectname"]."_6.scantailor")."  ".escapeshellarg($book["projectname"]."_5.scantailor")." ".escapeshellarg(PROJECT_ROOT."/".$book["projectname"]."/booktif")." 2>&1",$ret);
  if ($ret!=0) {
    echo "scantailor-cli failed\n";
    return false;
  }
  return true;
}

