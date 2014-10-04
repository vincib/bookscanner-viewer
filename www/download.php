<?php

require_once("common.php");

// Search for book
$id=intval($_REQUEST["id"]);
$book=mqone("SELECT * FROM books WHERE id=$id;");
if (!$book) {
  $_REQUEST["error"]=_("The requested book has not been found"); 
  require_once("head.php");
  require_once("menu.php");
  require("messagebox.php");
  require_once("foot.php");
  exit();
}

$license=mqone("SELECT * FROM license WHERE id='".$book["license"]."';");

// check identity : refuse a download for a non-logged-user non-free book
if (!isset($_SESSION["id"]) && $license["free"]==0) {
  $_REQUEST["error"]=_("This book is not under a proper license"); 
  require_once("head.php");
  require_once("menu.php");
  require("messagebox.php");
  require_once("foot.php");
  exit();  
}

// check identity : refuse a download for a non-admin non-owner private book
if ($book["privateid"]) {
  if (!isset($_SESSION["id"]) || 
      !($me["role"] & ROLE_ADMIN) ||
      $_SESSION["id"]!=$book["privateid"]
      ) {
    $_REQUEST["error"]=_("This book is not available to you, sorry");
    require_once("head.php");
    require_once("menu.php");
    require("messagebox.php");
    require_once("foot.php");
    exit();
  }
}

// Now search for the requested file
if (
    ($_REQUEST["type"]=="pdf" && !$book["bookpdf_ts"])
    ||      ($_REQUEST["type"]=="djvu" && !$book["bookpdf_ts"])
    ||      ($_REQUEST["type"]=="odt" && !$book["odt_ts"])
    ||      ($_REQUEST["type"]=="epub" && !$book["epub_ts"])
) {
  $_REQUEST["error"]=_("This book's requested file is not available."); 
  require_once("head.php");
  require_once("menu.php");
  require("messagebox.php");
  require_once("foot.php");
  exit();    
}

switch($_REQUEST["type"]) {

  // send a tar file of all jpg files, either 
case "tar":
  $root=PROJECT_ROOT."/".$book["projectname"]."/";
  header("Content-Type: application/x-tar");
  header("Content-Disposition: attachment; filename=\"".$book["projectname"].".origin.tar"."\""); // TODO: compute a better filename here ?
  header("Cache-Control: no-cache, must-revalidate");
  header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
  chdir($root);
  passthru("tar -hc left right");
  break;

case "tarbook":
  $root=PROJECT_ROOT."/".$book["projectname"]."/";
  header("Content-Type: application/x-tar");
  header("Content-Disposition: attachment; filename=\"".$book["projectname"].".book.tar"."\""); // TODO: compute a better filename here ?
  header("Cache-Control: no-cache, must-revalidate");
  header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
  chdir($root);
  passthru("tar -hc book");
  break;

case "tartiff":
  $root=PROJECT_ROOT."/".$book["projectname"]."/";
  header("Content-Type: application/x-tar");
  header("Content-Disposition: attachment; filename=\"".$book["projectname"].".tiff.tar"."\""); // TODO: compute a better filename here ?
  header("Cache-Control: no-cache, must-revalidate");
  header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
  chdir($root);
  passthru("tar -hc booktif");
  break;

case "pdf":
  $file=PROJECT_ROOT."/".$book["projectname"]."/".$book["projectname"].".pdf";
  header("Content-Type: text/pdf");
  header("Content-Disposition: attachment; filename=\"".$book["projectname"].".pdf"."\""); // TODO: compute a better filename here ?
  header("Cache-Control: no-cache, must-revalidate");
  header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
  header("Content-Length: ".filesize($file));
  readfile($file);
  break;

case "djvu":
  $file=PROJECT_ROOT."/".$book["projectname"]."/".$book["projectname"].".djvu";
  header("Content-Type: image/x.djvu");
  header("Content-Disposition: attachment; filename=\"".$book["projectname"].".djvu"."\""); // TODO: compute a better filename here ?
  header("Cache-Control: no-cache, must-revalidate");
  header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
  header("Content-Length: ".filesize($file));
  readfile($file);
  break;

case "odt":
  $file=PROJECT_ROOT."/".$book["projectname"]."/book.odt";
  header("Content-Type: application/vnd.oasis.opendocument.text");
  header("Content-Disposition: attachment; filename=\"".$book["projectname"].".odt"."\""); // TODO: compute a better filename here ?
  header("Cache-Control: no-cache, must-revalidate");
  header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
  header("Content-Length: ".filesize($file));
  readfile($file);
  break;

case "epub":
  $file=PROJECT_ROOT."/".$book["projectname"]."/book.epub";
  header("Content-Type: application/epub+zip");
  header("Content-Disposition: attachment; filename=\"".$book["projectname"].".epub"."\""); // TODO: compute a better filename here ?
  header("Cache-Control: no-cache, must-revalidate");
  header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
  header("Content-Length: ".filesize($file));
  readfile($file);
  break;

}
