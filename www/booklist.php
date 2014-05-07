<?php

  require_once("head.php");
  require_once("menu.php");
  require("messagebox.php");

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

$r=mq("SELECT * FROM books ORDER BY changed DESC LIMIT $offset,$count;");
echo mysql_error();
?>

<div class="container-fluid main"> 

<div class="row">
<div class="span12">

    <h1><?php __("Books"); ?></h1>

<table class="matable">
    <tr>
    <th><?php __("Project"); ?></th>
    <th><?php __("Title, Author"); ?></th>
    <th><?php __("Date"); ?></th>
    <th><?php __("Step"); ?></th>
    <th style="width: 140px"><?php __("Status"); ?></th>
    <th><?php __("Page Count"); ?></th>
    </tr>
<?php
    while ($c=mysql_fetch_array($r)) { 
?>
<tr>
	<td><?php echo htmlentities($c["projectname"]); ?></td>
        <td><?php echo htmlentities($c["title"]); 
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
   $stepstring=_("odt / epub");
   $date=$c["odt_ts"];
 }


?>
 <td><?php echo date(_("Y-m-d"),$date); ?></td>
 <td><img src="blue.png" style="width: <?php echo $step*10; ?>px; height: 16px" /></td>
 <td><?php echo $stepstring; ?></td>
 <td><?php echo $status; ?></td>
<!--
 <td><?php if ($c["booktif_ts"]) echo date(_("Y-m-d"),$c["booktif_ts"]); ?></td>
 <td><?php if ($c["bookpdf_ts"]) echo date(_("Y-m-d"),$c["bookpdf_ts"]); ?></td>
 <td><?php if ($c["ocr_ts"]) echo date(_("Y-m-d"),$c["ocr_ts"]); ?></td>
 <td><?php if ($c["odt_ts"]) echo date(_("Y-m-d"),$c["odt_ts"]); ?></td>
 -->
</tr>
	<?php } ?>

</table>

</div>
</div>
</div>

<?php
  require_once("foot.php");
?>
