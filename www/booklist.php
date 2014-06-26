<?php

require_once("common.php");
require_once("head.php");
require_once("menu.php");
require("messagebox.php");

if ($url=="/bookedit") {
  if (!$_SESSION["id"]) {
    $_REQUEST["error"]=_("You are not allowed to see this page. Sorry"); 
    require_once("nothing.php");
    exit();
  }
  define("ISEDIT",true);
} else {
  define("ISEDIT",false);
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

if ($count<=0 || $count>1000) $count=100;
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

if (isset($_REQUEST["q"])) {
  $q=explode(" ",$_REQUEST["q"]);
  foreach($q as $word) {
    if (trim($word)) {
      $sql.=" AND (title LIKE '%".addslashes($word)."%' OR authors LIKE '%".addslashes($word)."%' OR ISBN='".addslashes($word)."' ) ";
    }
  }
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

<!--
  <label for="step"><?php __("At step"); ?></label>
  <select name="step" class="form-control" id="step" onchange="form.submit()">
  <option value=""><?php __("--- Any step ---"); ?></option>
  <option value="1"><?php __("Scanned"); ?></option>
  <option value="2"><?php __("Scantailor-ing"); ?></option>
  <option value="3"><?php __("Scantailor-ed"); ?></option>
  <option value="4"><?php __("Image PDF"); ?></option>
  <option value="5"><?php __("ocr"); ?></option>
  </select>
 -->
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
<?php if (ISEDIT) { ?>
    <th><?php __("Step"); ?></th>
<?php } ?>
    <th style="width: 140px"><?php __("Status"); ?></th>
    <th><?php __("Page Count"); ?></th>
    <th><?php __("License"); ?></th>
    </tr>
<?php
    while ($c=mysql_fetch_array($r)) { 
      $attribs=json_decode($c["attribs"],true);
?>
<tr>
	<td><?php 
 if (!ISEDIT) { 
	if ($c["scan_ts"]) {
	  echo "<a href=\"/download?id=".$c["id"]."&type=tar\">";
	  echo "<img src=\"/assets/img/tarball.png\" alt=\"".sprintf(_("Download TAR of all original pictures (%sMB)"),intval($attribs["scan_size"]/1024/102.4)/10)."\" title=\"".sprintf(_("Download TAR of all original pictures (%sMB)"),intval($attribs["scan_size"]/1024/102.4)/10)."\" />";
	  echo "</a>";
	} else {
	  echo "<img src=\"/assets/img/nothing.png\" />";
	}

	if ($c["bookpdf_ts"]) {
	  echo "<a href=\"/download?id=".$c["id"]."&type=pdf\">";
	  echo "<img src=\"/assets/img/pdf.png\" alt=\"".sprintf(_("Download PDF image (%sMB)"),intval($attribs["bookpdf_size"]/1024/102.4)/10)."\" title=\"".sprintf(_("Download PDF image (%sMB)"),intval($attribs["bookpdf_size"]/1024/102.4)/10)."\" />";
	  echo "</a>";
	} else {
	  echo "<img src=\"/assets/img/nothing.png\" />";
	}

	if ($c["odt_ts"]) {
	  echo "<a href=\"/download?id=".$c["id"]."&type=odt\">";
	  echo "<img src=\"/assets/img/odt.png\" alt=\"".sprintf(_("Download ODT text file (%sKB)"),intval($attribs["odt_size"]/102.4)/10)."\" title=\"".sprintf(_("Download ODT text file (%sKB)"),intval($attribs["odt_size"]/102.4)/10)."\" />";
	  echo "</a>";
	} else {
	  echo "<img src=\"/assets/img/nothing.png\" />";
	}

	if ($c["epub_ts"]) {
	  echo "<a href=\"/download?id=".$c["id"]."&type=epub\">";
	  echo "<img src=\"/assets/img/epub.png\" alt=\"".sprintf(_("Download EPUB book (%sKB)"),intval($attribs["epub_size"]/102.4)/10)."\" title=\"".sprintf(_("Download EPUB book (%sKB)"),intval($attribs["epub_size"]/102.4)/10)."\" />";
	  echo "</a>";
	} else {
	  echo "<img src=\"/assets/img/nothing.png\" />";
	}
 } else {
   echo "<a href=\"edit?id=".$c["id"]."\"><img src=\"/assets/img/edit.png\" /> "._("Edit")."</a>";
 }
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
<?php  if (ISEDIT) { ?>
 <td><img src="/assets/img/blue.png" style="width: <?php echo $step*10; ?>px; height: 16px" /></td>
<?php } ?>
 <td><?php echo $stepstring; ?></td>
 <td><?php echo $status; ?></td>
			       <td><?php if (isset($alicense[$c["license"]])) { __($alicense[$c["license"]]["name"]); } ?></td>
</tr>
	<?php } ?>
</table>

</div>
</div>
</div>

<?php
  require_once("foot.php");
?>
