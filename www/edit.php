<?php

require_once("common.php");

// used by scanner()
function debug($str) {
  
}

if (!$_SESSION["id"]) {
  $_REQUEST["error"]=_("You are not allowed to see this page. Sorry"); 
  require_once("nothing.php");
  exit();
}

$id=intval($_REQUEST["id"]);

if (!isset($_REQUEST["action"])) {
  $_REQUEST["action"]="edit";
}

$book=mqone("SELECT * FROM books WHERE id=".intval($_REQUEST["id"]).";");
if (!$book) {
  $_REQUEST["error"]=_("Book not found");
  require_once("nothing.php");
  exit();
}


switch ($_REQUEST["action"]) {

case "status":
  $status=intval($_REQUEST["status"]);
  mq("UPDATE books SET status=".$status." WHERE id=".$id.";");
  header("Location: /edit?id=".$id."&msg=".urlencode(_("Status changed")));
  exit();
  break;

case "del_last_left":
  // for real we *move* it and *remember* its previous place, just in case ...
  $pic=$_REQUEST["pic"];
  if ($_REQUEST["code"]==md5(SECRET_CODE."-".$pic)) {
    $f=fopen(DELETE_LOG,"ab");
    if ($f) {
      fputs($f,"moving for project '".$book["projectname"]."' the last left named '$pic' to ".DELETE_BIN."\n");
      fclose($f);
      rename(PROJECT_ROOT."/".$book["projectname"]."/left/".$pic,DELETE_BIN."/".$pic);
      header("Location: /edit?id=".$id."&msg=".urlencode(_("Last left picture deleted")));
      exit();
    }
  }
  break;
case "del_first_right":
  // for real we *move* it and *remember* its previous place, just in case ...
  $pic=$_REQUEST["pic"];
  if ($_REQUEST["code"]==md5(SECRET_CODE."-".$pic)) {
    $f=fopen(DELETE_LOG,"ab");
    if ($f) {
      fputs($f,"moving for project '".$book["projectname"]."' the first right named '$pic' to ".DELETE_BIN."\n");
      fclose($f);
      rename(PROJECT_ROOT."/".$book["projectname"]."/right/".$pic,DELETE_BIN."/".$pic);
      header("Location: /edit?id=".$id."&msg=".urlencode(_("First right picture deleted")));
      exit();
    }
  }
  break;

case "scantailor":
  include("gen-scantailor.php"); 
  $_REQUEST["substitute"]=PROJECT_ROOT;
  $_REQUEST["out"]=$book["projectname"].".scantailor";
  gen_scantailor($book["projectname"]);
  // update the project's status :
  scanproject($book["projectname"]);
  header("Location: /edit?id=".$id."&msg=".urlencode("Scantailor project created"));
  exit();
  break;

case "pdfimage":
  touch(PROJECT_ROOT."/".$book["projectname"]."/genpdf");
  header("Location: /edit?id=".$id."&msg=".urlencode("PDF Image building requested, please come back later"));
  exit();
  break;

case "ocr":
  touch(PROJECT_ROOT."/".$book["projectname"]."/genocr");
  header("Location: /edit?id=".$id."&msg=".urlencode("OCR requested, please come back later"));
  exit();
  break;

case "edit":
case "doedit":
  $id=intval($_REQUEST["id"]);
  $book=mqone("SELECT * FROM books WHERE id='$id';");
  $attribs=@json_decode($book["attribs"],true);
  if (!$book) {
    $_REQUEST["error"]=_("Book not found"); 
    require_once("head.php");
    require_once("menu.php");
    require("messagebox.php");
    require_once("foot.php");
    exit();
  }
  if ($_REQUEST["action"]=="edit") {
    foreach($book as $k=>$v) $_REQUEST[$k]=$v;
  }
  if ($_REQUEST["action"]=="doedit") {
    // UPDATE
    if ($book["locked"]!=$_POST["locked"]) {
      $locktime=", locktime=NOW() ";
    } else {
      $locktime="";
    }
    mq("UPDATE books SET title='".addslashes($_POST["title"])."', license='".intval($_POST["license"])."', status='".intval($_POST["status"])."', authors='".addslashes($_POST["authors"])."', publisher='".addslashes($_POST["publisher"])."', isbn='".addslashes($_POST["isbn"])."', collection='".addslashes($_POST["collection"])."', `locked`='".addslashes($_POST["locked"])."' $locktime WHERE id='".intval($_POST["id"])."';");
    header("Location: /edit?id=".$id."&msg=".urlencode("Book edited successfully"));
    exit();
  }
  break;

} // SWITCH 

  require_once("head.php");
  require_once("menu.php");
  require("messagebox.php");

?>
<div class="container-fluid main"> 

<div class="row">
<div class="span6">

    <h1><?php __("Book Editor"); ?></h1>

<?php

switch ($_REQUEST["action"]) {
case "edit":
case "create":
?>
  <h2><?php
  if ($_REQUEST["action"]=="edit")  printf(_("Editing book %s"),htmlentities($_REQUEST["projectname"])); 
  else __("Creating new book");  // useless, I know
?></h2>
<form method="post" action="/edit">
  <input type="hidden" name="id" value="<?php echo $id; ?>" />
  <input type="hidden" name="action" value="do<?php echo $_REQUEST["action"]; ?>" />
   <label for="title"><?php __("Book Title"); ?></label><input type="text" name="title" id="title" value="<?php eher("title"); ?>" style="width: 300px"/>
   <label for="authors"><?php __("Book Authors"); ?></label><textarea name="authors" id="authors" style="width: 300px; height: 80px"><?php eher("authors"); ?></textarea>
   <label for="publisher"><?php __("Book Publisher"); ?></label><input type="text" name="publisher" id="publisher" value="<?php eher("publisher"); ?>" style="width: 300px"/>
   <label for="isbn"><?php __("Book Isbn"); ?></label><input type="text" name="isbn" id="isbn" value="<?php eher("isbn"); ?>" style="width: 300px"/>
   <label for="collection"><?php __("Book Collection"); ?></label><select name="collection" id="collection"><option value="0"><?php __("--- No collection ---"); ?></option><?php eoption("collections",$_REQUEST["collection"],array("id","name")); ?></select>
   <label for="license"><?php __("Book License"); ?></label><select name="license" id="license"><?php eoption($alicense2,$_REQUEST["license"]); ?></select>

 <a href="#" onclick="$('#bst').show(); return false"><?php __("Set status too"); ?></a>
<div id="bst" style="display: none">
   <label for="status"><?php __("Book Scan Status"); ?></label><select name="status" id="status"><?php eoption($astatus2,$_REQUEST["status"]); ?></select>
</div>
																															   <label for="locked"><?php __("Locked by"); ?></label><select name="locked" id="locked"><option value="0"><?php __("--- Nobody ---"); ?></option><?php eoption("users",$_REQUEST["locked"],array("id","login")); ?></select><?php if ($_REQUEST["locktime"] && $_REQUEST["locktime"]!="0000-00-00 00:00:00") echo  " ".sprintf("Locked on %s",date_my2fr($_REQUEST["locktime"])); ?>
<div>
      <input type="submit" name="go" value="<?php  
if ($_REQUEST["action"]=="edit") __("Edit this book"); 
else __("Create this book");
?>" />
 <input type="button" name="cancel" value="<?php __("Cancel"); ?>" onclick="document.location='/bookedit'" />
</div>
</form>
<?php
   break; // ACTION EDIT / CREATE

    } // SWITCH ACTION

function dateif($ts) {
  if ($ts) 
    return date("Y-m-d H:i:s",$ts);
}

?>
</div> <!-- col -->

<div class="span6">

<!-- Status table -->
<?php

  echo "<h3>"._("Current status of the project:")."</h3>";
echo "<div style=\"float: right\"><a onclick=\"return confirm('".str_replace("'","\\'",_("Please confirm you want to set this project to ERROR status"))."');\" href=\"edit.php?id=".$book["id"]."&action=status&status=".STATUS_UNKNOWN."\" title=\""._("Click to set the project to ERROR status")."\"><img src=\"/assets/img/delete.png\"></a></div>";
echo "<p><b>".$astatus[$book["status"]][0]."</b>";
if ($book["status"]==STATUS_DOINGTAILOR1 || $book["status"]==STATUS_DOINGTAILOR6 || $book["status"]==STATUS_OCRING) {
  echo " (<img src=\"/assets/img/loading_web.gif\"> "._("Please wait").")";
}
echo "</p>\n";

$changeto=0; // Which status changes are allowed FROM THE USER will:
if ($book["status"] == STATUS_SCANNING) {  $changeto=STATUS_SCANOK;  }
if ($book["status"] == STATUS_WAITUSERTAILOR) {  $changeto=STATUS_USEROKTAILOR6;  }

echo "<p>";
if ($book["status"] == STATUS_WAITUSERTAILOR) {
  if ($_SERVER["REMOTE_ADDR"]=="127.0.0.1") {
    echo "<p><a href=\"#\" onclick=\"$.ajax('launch-scantailor.php?id=".$book["id"]."'); return false;\"><img src=\"/assets/img/scantailor.png\"> "._("Launch Scantailor to check the pages")."</a></p>";
  }
}
if ($changeto) {
  echo "<a href=\"edit.php?id=".$book["id"]."&action=status&status=".$changeto."\">".sprintf(_("Change the status to '%s'"),$astatus[$changeto][0])."</a><br />";
}
echo "</p>";
?>
<p><?php __("To know the status list, click on the '<a href=\"/misc\">Misc</a>' link in the menu"); ?></p>
<p>&nbsp;</p>

<!-- thumbnail of first and last page of right/left -->
<?php 

$d=opendir(PROJECT_ROOT."/".$book["projectname"]."/left");
$firstl="";
$lastl="";
if ($d) {
while (($file=readdir($d))!==false) {
  if (preg_match("#.jpg#i",$file)) {
    if (!$firstl) $firstl=$file;
    if (!$lastl) $lastl=$file;
    if ($file<$firstl) $firstl=$file;
    if ($file>$lastl) $lastl=$file;
  }
}
closedir($d);
} else {
  echo "<p>"._("Can't read left folder, please check")."</p>";
}
$d=opendir(PROJECT_ROOT."/".$book["projectname"]."/right");
$firstr="";
$lastr="";
if ($d) {
while (($file=readdir($d))!==false) {
  if (preg_match("#.jpg#i",$file)) {
    if (!$firstr) $firstr=$file;
    if (!$lastr) $lastr=$file;
    if ($file<$firstr) $firstr=$file;
    if ($file>$lastr) $lastr=$file;
  }
}
closedir($d);
} else {
  echo "<p>"._("Can't read right folder, please check")."</p>";
}

function pic($path) {
  if (!is_file(THUMBNAILS_ROOT."/normal/".md5("file://".$path).".png")) {
    // generate the thumbnail
    exec("/home/bookscanner/bin/pics_thumbnailer ".escapeshellarg($path)." ".escapeshellarg(THUMBNAILS_ROOT."/normal/".md5("file://".$path).".png"));
  }
  return THUMBNAILS_URL."/normal/".md5("file://".$path).".png";
}
echo "<p>"._("First and last <b>left</b> pictures")." &nbsp; ";
$code=md5(SECRET_CODE."-".$lastl);
echo "<a href=\"edit?id=".$book["id"]."&action=del_last_left&pic=".$lastl."&code=".$code."\" onclick=\"return confirm('".str_replace("'","\\'",_("Please confirm you want to delete the last picture"))."');\">"._("Delete last")."</a>";
echo "</p>";
echo "<p><img alt=\"first left : ".$firstl."\" src=\"".pic(PROJECT_ROOT."/".$book["projectname"]."/left/".$firstl)."\"> ";
echo "<img alt=\"last left : ".$lastl."\" src=\"".pic(PROJECT_ROOT."/".$book["projectname"]."/left/".$lastl)."\"></p>";
echo "<p>"._("First and last <b>right</b> pictures")." &nbsp; ";
$code=md5(SECRET_CODE."-".$firstr);
echo "<a href=\"edit?id=".$book["id"]."&action=del_first_right&pic=".$firstr."&code=".$code."\" onclick=\"return confirm('".str_replace("'","\\'",_("Please confirm you want to delete the first picture"))."');\">"._("Delete first")."</a>";
echo "</p>";
echo "<p><img alt=\"first right : ".$firstr."\" src=\"".pic(PROJECT_ROOT."/".$book["projectname"]."/right/".$firstr)."\"> ";
echo "<img alt=\"last right : ".$lastr."\" src=\"".pic(PROJECT_ROOT."/".$book["projectname"]."/right/".$lastr)."\"></p>";
?>




<p>&nbsp;</p>
<p>&nbsp;</p>
<p>&nbsp;</p>
<p>&nbsp;</p>


<h3>Ancienne table d'état, ne plus utiliser !!</h3>

<table class="matable">
    <tr><td><?php __("Metadata changed on"); ?></td>
    <td><?php echo dateif($_REQUEST["meta_ts"]); ?></td></tr>

    <tr><td><?php __("Last scanned picture on"); ?></td>
    <td><?php echo dateif($_REQUEST["scan_ts"]); ?></td></tr>

<?php    if ($attribs["leftcount"]>0 && $attribs["rightcount"]>0) { $ok1=true;  ?>
    <tr><td><?php __("Number of pictures"); ?></td>
    <td><?php echo $attribs["leftcount"]." left and ".$attribs["rightcount"]." right"; ?></td></tr>
<?php } else { $ok1=false; ?>
    <tr><td><?php __("No pictures scanned yet"); ?></td>
    <td></td></tr>
<?php } ?>

<?php    if ($_REQUEST["scantailor_ts"]>0) { $ok2=true; ?>
    <tr><td><?php __("Scantailor project made on"); ?></td>
    <td><?php echo dateif($_REQUEST["scantailor_ts"]); ?> <a onclick="return confirm('<?php __("Do you really want to overwrite the scantailor and booktif files ?"); ?>')" href="edit.php?action=scantailor&id=<?php echo $book["id"]; ?>"><?php __("Regenerate"); ?></a></td></tr>
<?php } else { $ok2=false; ?>
    <tr><td><?php __("No scantailor project created yet"); ?></td>
    <td><?php if ($ok1) { ?><a href="edit.php?action=scantailor&id=<?php echo $book["id"]; ?>"><?php __("Create this book's scantailor's project"); ?></a><?php } ?></td></tr>
<?php } ?>


<?php    if ($_REQUEST["booktif_ts"]>0) { $ok3=true; ?>
    <tr><td><?php __("Scantailor output made on"); ?></td>
    <td><?php echo dateif($_REQUEST["booktif_ts"]); ?></td></tr>
<?php } else { $ok3=false; ?>
    <tr><td><?php __("No scantailor output made yet"); ?></td>
    <td><?php if ($ok2) {  __("Use VNC to make the scantailor process"); } ?></td></tr>
<?php } ?>


<?php    if ($attribs["bookpdf_size"]>0) { $ok4=true; ?>
    <tr><td><?php __("Image PDF created"); ?></td>
    <td><?php printf(_("It has %d pages"),$attribs["bookpdf_pages"]); ?></td></tr>
<?php } else { $ok4=false; ?>
    <tr><td><?php __("No PDF Image created yet"); ?></td>
    <td><?php if ($ok3) {
  if (file_exists(PROJECT_ROOT."/".$book["projectname"]."/genpdf")) {
    printf(_("PDF Image requested on %s"),date("Y-m-d H:i:s",filemtime(PROJECT_ROOT."/".$book["projectname"]."/genpdf")));;
  } else {
    ?><a href="edit.php?action=pdfimage&id=<?php echo $book["id"]; ?>"><?php __("Create a PDF Image"); ?></a><?php } } ?></td></tr>
<?php } ?>


<?php    if ($_REQUEST["ocr_ts"]>0) { $ok5=true;  ?>
    <tr><td><?php __("OCR files made on"); ?></td>
    <td><?php echo dateif($_REQUEST["ocr_ts"]); ?></td></tr>
<?php } else { $ok5=false; ?>
    <tr><td><?php __("No OCR made yet"); ?></td>
    <td><?php if ($ok3) {
  if (file_exists(PROJECT_ROOT."/".$book["projectname"]."/genocr")) {
    printf(_("OCR requested on %s"),date("Y-m-d H:i:s",filemtime(PROJECT_ROOT."/".$book["projectname"]."/genocr")));;
  } else {
    ?><a href="edit.php?action=ocr&id=<?php echo $book["id"]; ?>"><?php __("Do the OCR"); ?></a><?php } } ?></td></tr>
<?php } ?>


</table>


</div> <!-- col -->


</div></div>

<?php
  require_once("foot.php");
?>
