<?php

require_once("common.php");
require_once("head.php");
require_once("menu.php");
require("messagebox.php");

define("ISEDIT",false);

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
// FIXME: exclude private books ?

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
if (isset($_REQUEST["status"]) && $_REQUEST["status"]) {
    $sql.=" AND status = ".intval($_REQUEST["status"])." ";
} else {
  $sql.=" AND status != ".intval(STATUS_UNKNOWN)." ";
}

if (!isset($_SESSION["id"])) {
  // anonymous
  $sql.=" AND privateid=0 ";
} else if (!($me["role"] & ROLE_ADMIN)) {
  // non admin
  $sql.=" AND privateid IN (0,".$_SESSION["id"].") ";
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
    <th colspan="2" style="width: 240px"><?php __("Status"); ?></th>
    <th><?php __("License"); ?></th>
    </tr>
<?php
    while ($c=mysql_fetch_array($r)) { 
      $attribs=json_decode($c["attribs"],true);
?>
<tr<?php if ($c["privateid"]) echo " class=\"private\"" ; ?>>
	<td><?php 
   // Which download are available ? (tar, tarbook, tartiff)
   if ($c["status"] < STATUS_DOINGTAILOR1) {
     echo "<a href=\"/download?id=".$c["id"]."&type=tar\">";
     echo "<img src=\"/assets/img/tarball.png\" alt=\"".sprintf(_("Download TAR of all original pictures (UNSORTED) (%sMB)"),intval($attribs["scan_size"]/1024/102.4)/10)."\" title=\"".sprintf(_("Download TAR of all original pictures (%sMB)"),intval($attribs["scan_size"]/1024/102.4)/10)."\" />";
     echo "</a>";
   } else if ($c["status"] < STATUS_TAILOROK) {
     echo "<a href=\"/download?id=".$c["id"]."&type=tarbook\">";
     echo "<img src=\"/assets/img/tarball.png\" alt=\"".sprintf(_("Download TAR of all original pictures (SORTED) (%sMB)"),intval($attribs["scan_size"]/1024/102.4)/10)."\" title=\"".sprintf(_("Download TAR of all original pictures (%sMB)"),intval($attribs["scan_size"]/1024/102.4)/10)."\" />";
     echo "</a>";
   } else if ($c["status"] >= STATUS_TAILOROK) {
     echo "<a href=\"/download?id=".$c["id"]."&type=tartiff\">";
     echo "<img src=\"/assets/img/tarball.png\" alt=\"".sprintf(_("Download TAR of all original pictures (SORTED AND ENHANCED) (%sMB)"),intval($attribs["scan_size"]/1024/102.4)/10)."\" title=\"".sprintf(_("Download TAR of all original pictures (%sMB)"),intval($attribs["scan_size"]/1024/102.4)/10)."\" />";
     echo "</a>";
   } else {
     echo "<img src=\"/assets/img/nothing.png\" />";
   }
      
      if ($c["status"] >= STATUS_TAILOROK) {

	// pdf
	echo "<a href=\"/download?id=".$c["id"]."&type=pdf\">";
	$fsize=intval(filesize(PROJECT_ROOT."/".$c["projectname"]."/".$c["projectname"].".pdf")/1024/102.4)/10;
	echo "<img src=\"/assets/img/pdf.png\" alt=\"".sprintf(_("Download PDF image file (%sMB)"),$fsize)."\" title=\"".sprintf(_("Download PDF image (%sMB)"),$fsize)."\" />";
	echo "</a>";

	// djvu
	echo "<a href=\"/download?id=".$c["id"]."&type=djvu\">";
	$fsize=intval(filesize(PROJECT_ROOT."/".$c["projectname"]."/".$c["projectname"].".djvu")/1024/102.4)/10;
	echo "<img src=\"/assets/img/djvu.png\" alt=\"".sprintf(_("Download DJVU image file (%sMB)"),$fsize)."\" title=\"".sprintf(_("Download DJVU image (%sMB)"),$fsize)."\" />";
	echo "</a>";
	
      } else {
	echo "<img src=\"/assets/img/nothing.png\" />";
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

?></td>
        <td title="<?php echo htmlentities($c["projectname"]) ; ?>"><?php echo htmlentities($c["title"]); 
	if (!$c["title"]) echo "<i>".htmlentities($c["projectname"])."</i>";
      $author=explode("\n",$c["authors"]);
      echo "<br />".htmlentities($author[0]);
      if (count($author)>1) echo " ...";
?></td>

      <?php if ($c["status"]!=STATUS_UNKNOWN) { ?> 
      <td><img src="/assets/img/blue.png" style="width: <?php echo $c["status"]; ?>px; height: 16px" /></td>
	<?php } else { ?> 
	<td><img src="/assets/img/orange.png" style="width: 100px; height: 16px" /></td>
	<?php } ?>
	<td><?php echo $astatus[$c["status"]][0]; ?></td>
<?php

 $attribs=@json_decode($c["attribs"],true);
 
 if ($c["scan_ts"]) {
   $date=$c["scan_ts"];
 }
 if ($c["scantailor_ts"]) {
   $date=$c["scantailor_ts"];
 }
 if ($c["booktif_ts"]) {
   $date=$c["booktif_ts"];
 }
 if ($c["bookpdf_ts"]) {
   $date=$c["bookpdf_ts"];
 }
 if ($c["ocr_ts"]) {
   $date=$c["ocr_ts"];
 }
 if ($c["odt_ts"]) {
   $date=$c["odt_ts"];
 }
 if ($c["epub_ts"]) {
   $date=$c["epub_ts"];
 }

?>
 <td><?php echo date(_("Y-m-d"),$date); ?></td>
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
