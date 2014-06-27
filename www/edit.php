<?php

require_once("common.php");

if (!$_SESSION["id"]) {
  $_REQUEST["error"]=_("You are not allowed to see this page. Sorry"); 
  require_once("nothing.php");
  exit();
}

$id=intval($_REQUEST["id"]);

if (!isset($_REQUEST["action"])) {
  $_REQUEST["action"]="edit";
}
switch ($_REQUEST["action"]) {
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
    mq("UPDATE books SET title='".addslashes($_POST["title"])."', authors='".addslashes($_POST["authors"])."', publisher='".addslashes($_POST["publisher"])."', isbn='".addslashes($_POST["isbn"])."', collection='".addslashes($_POST["collection"])."', `locked`='".addslashes($_POST["locked"])."' $locktime WHERE id='".intval($_POST["id"])."';");
    $_REQUEST["msg"]=_("Book edited successfully"); 
    $_REQUEST["action"]="edit";
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

<table class="matable">
    <tr><td><?php __("Metadata changed on"); ?></td>
    <td><?php echo dateif($_REQUEST["meta_ts"]); ?></td></tr>

    <tr><td><?php __("Last scanned picture on"); ?></td>
    <td><?php echo dateif($_REQUEST["scan_ts"]); ?></td></tr>

<?php    if ($attribs["leftcount"]>0 && $attribs["rightcount"]>0) { ?>
    <tr><td><?php __("Number of pictures"); ?></td>
    <td><?php echo $attribs["leftcount"]." left and ".$attribs["rightcount"]." right"; ?></td></tr>
<?php } else { ?>
    <tr><td><?php __("No pictures scanned yet"); ?></td>
    <td></td></tr>
<?php } ?>

<?php    if ($attribs["scantailor_ts"]>0) { ?>
    <tr><td><?php __("Scantailor project made on"); ?></td>
    <td><?php echo dateif($_REQUEST["scantailor_ts"]); ?></td></tr>
<?php } else { ?>
    <tr><td><?php __("No scantailor project created yet"); ?></td>
    <td><a href="scantailor.php?id=<?php echo $book["id"]; ?>"><?php __("Create this book's scantailor's project"); ?></a></td></tr>
<?php } ?>


<?php    if ($attribs["booktif_ts"]>0) { ?>
    <tr><td><?php __("Scantailor output made on"); ?></td>
    <td><?php echo dateif($_REQUEST["booktif_ts"]); ?></td></tr>
<?php } else { ?>
    <tr><td><?php __("No scantailor output made yet"); ?></td>
    <td></td></tr>
<?php } ?>


<?php    if ($attribs["boopdf_ts"]>0) { ?>
    <tr><td><?php __("Image PDF created on"); ?></td>
    <td><?php echo dateif($_REQUEST["bookpdf_ts"]); ?></td></tr>
<?php } else { ?>
    <tr><td><?php __("No Image PDF created yet"); ?></td>
    <td></td></tr>
<?php } ?>


<?php    if ($attribs["ocr_ts"]>0) { ?>
    <tr><td><?php __("OCR files made on"); ?></td>
    <td><?php echo dateif($_REQUEST["ocr_ts"]); ?></td></tr>
<?php } else { ?>
    <tr><td><?php __("No OCR made yet"); ?></td>
    <td></td></tr>
<?php } ?>


</table>


</div> <!-- col -->


</div></div>

<?php
  require_once("foot.php");
?>
