<?php

require_once("common.php");
require_once("head.php");
require_once("menu.php");
require("messagebox.php");

if (!$_SESSION["id"]) {
  $_REQUEST["error"]=_("You are not allowed to see this page. Sorry"); 
  require_once("nothing.php");
  exit();
}

$params=array("count","offset","collection","status","q");
$hasparams=false;
foreach($params as $param) {
  if (isset($_REQUEST[$param])) $hasparams=true;
}
if (!$hasparams) {
  foreach($params as $param) {
    if (isset($_SESSION["bookedit"][$param])) 
      $_REQUEST[$param]=$_SESSION["bookedit"][$param];
  }
}


if (!isset($_REQUEST["count"])) {
  $count=0;
} else {
  $count=intval($_REQUEST["count"]);
}

if (!isset($_REQUEST["offset"])) {
  $offset=0;
} else {
  $offset=intval($_REQUEST["offset"]);
}

if ($count<=0 || $count>1000) $count=1000;
if ($offset<0) $offset=0;

if (!$_SESSION["id"]) {
  $sql.=" AND license IN (".implode(",",$freelicenses).")";
}

if (isset($_REQUEST["collection"]) && $_REQUEST["collection"] ) {
  if ($_REQUEST["collection"]=="-1") {
    $sql.=" AND collection = 0 ";
  } else {
    $sql.=" AND collection = ".intval($_REQUEST["collection"])." ";
  }
}

if (isset($_REQUEST["status"]) && $_REQUEST["status"]) {
    $sql.=" AND status = ".intval($_REQUEST["status"])." ";
}

if (!$me["role"] & ROLE_ADMIN) {
  // non admin
  $sql.=" AND privateid IN (0,".$_SESSION["id"].") ";
}

if (isset($_REQUEST["q"])) {
  $q=explode(" ",$_REQUEST["q"]);
  foreach($q as $word) {
    if (trim($word)) {
      $sql.=" AND (title LIKE '%".addslashes($word)."%' OR authors LIKE '%".addslashes($word)."%' OR ISBN='".addslashes($word)."' ) ";
    }
  }
}


foreach($params as $param) {
  $_SESSION["bookedit"][$param] = $_REQUEST[$param];
}

$r=mq("SELECT * FROM books WHERE 1 $sql ORDER BY changed DESC LIMIT $offset,$count;");
echo mysql_error();
?>

<div class="container-fluid main"> 

<div class="row">
<div class="span12">
    <h1><?php __("Book Listing"); ?></h1>
<?php
  // echo $sql; 
?>
<form method="get" action="" class="form-inline">
  <label for="q"><?php __("Search for"); ?></label>
  <input type="text" class="form-control" name="q" id="q" value="<?php eher("q"); ?>" />

  <label for="status"><?php __("Having status"); ?></label>
  <select name="status" class="form-control" id="status" onchange="form.submit()">
  <option value=""><?php __("--- Any status ---"); ?></option>
  <?php foreach($astatus as $k=>$v) {
  echo "<option value=\"".$k."\"";
if ($_REQUEST["status"]==$k) echo " selected=\"selected\"";
echo ">[".$k."] ".$v[0]."</option>";
  }
?>
  </select>

  <label for="collection"><?php __("In collection"); ?></label>
  <select name="collection" class="form-control" id="collection" onchange="form.submit()">
  <option value=""><?php __("--- Any collection ---"); ?></option>
  <option value="-1" <?php if ($_REQUEST["collection"]=="-1") echo "selected=\"selected\" "; ?>><?php __("--- No collection ---"); ?></option>
<?php eoption("collections",$_REQUEST["collection"],array("id","name")); ?>
  </select>
  <input type="submit" name="go" value="<?php __("Search"); ?>" />
</form>
</div>
</div>

<div class="row">
<div class="span12">



<table class="matable">
    <tr>
    <th><?php __("Download"); ?></th>
    <th><?php __("Title, Author"); ?></th>
    <th><?php __("Date"); ?></th>
    <th style="width: 140px"><?php __("(Old Status)"); ?></th>
    <th><?php ?></th>
    <th style="width: 140px"><?php __("Status"); ?></th>
    <th><?php __("License"); ?></th>
    </tr>
<?php
    while ($c=mysql_fetch_array($r)) { 
      $attribs=json_decode($c["attribs"],true);
?>
<tr>
	<td><?php 
   echo "<a href=\"edit?id=".$c["id"]."\"><img src=\"/assets/img/edit.png\" /> "._("Edit")."</a>";
?></td>
        <td title="<?php echo htmlentities($c["projectname"]) ; ?>"><?php echo htmlentities($c["title"]); 
	if (!$c["title"]) echo "<i>".htmlentities($c["projectname"])."</i>";
      $author=explode("\n",$c["authors"]);
      echo "<br />".htmlentities($author[0]);
      if (count($author)>1) echo " ...";
?></td>
<?php

 $attribs=@json_decode($c["attribs"],true);

 if ($c["scan_ts"]) {
   $step=1; 
   $status="";
   if (isset($attribs["leftcount"]) && isset($attribs["rightcount"])) 
     $status=$attribs["leftcount"].",".$attribs["rightcount"];
   $stepstring=_("Scanned");
   $date=$c["scan_ts"];
 }
 if ($c["scantailor_ts"]) {
   $step=2;
   $status="";
   $stepstring=_("Scantailor-ing");
   $date=$c["scantailor_ts"];
 }
 if ($c["booktif_ts"]) {
   $step=3;
   $status="";
   if (isset($attribs["booktifcount"])) 
     $status=$attribs["booktifcount"];
   $stepstring=_("Scantailor-ed");
   $date=$c["booktif_ts"];
 }
 if ($c["bookpdf_ts"]) {
   $step=4;
   $status="";
   if (isset($attribs["bookpdf_pages"])) 
     $status=$attribs["bookpdf_pages"];
   $stepstring=_("Image PDF");
   $date=$c["bookpdf_ts"];
 }
 if ($c["ocr_ts"]) {
   $step=5;
   $status="";
   if (isset($attribs["ocrcount"])) 
     $status=$attribs["ocrcount"];
   $stepstring=_("ocr");
   $date=$c["ocr_ts"];
 }
 if ($c["odt_ts"]) {
   $step=6;
   $status="";
   $stepstring=_("odt");
   $date=$c["odt_ts"];
 }
 if ($c["epub_ts"]) {
   $step=7;
   $status="";
   $stepstring=_("epub");
   $date=$c["epub_ts"];
 }

?>
 <td><?php echo date(_("Y-m-d"),$date); ?></td>
 <td><?php echo $stepstring; ?></td>
				   <?php if ($c["status"]!=STATUS_UNKNOWN) { ?> 
<td><img src="/assets/img/blue.png" style="width: <?php echo 2*$c["status"]; ?>px; height: 16px" /></td>
				   <?php } else { ?> 
<td><img src="/assets/img/orange.png" style="width: 180px; height: 16px" /></td>
<?php } ?>
	<td><?php echo $astatus[$c["status"]][0]; ?></td>
 <td><?php if ($c["locked"]) echo sprintf("Locked by %s on %s",user_login($c["locked"]),date_my2fr($c["locktime"]));; ?></td>
 <td><?php if (isset($alicense[$c["license"]])) { __($alicense[$c["license"]]["name"]); } ?></td>
</tr>
	<?php } ?>
</table>


<p>&nbsp;</p>


</div>
</div>
</div>

<?php
  require_once("foot.php");
?>
