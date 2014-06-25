<?php

require_once("common.php");
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

$r=mq("SELECT booklog.*, books.title, books.projectname FROM booklog, books WHERE books.id=booklog.book ORDER BY booklog.ts DESC LIMIT $offset,$count;");
echo mysql_error();
?>

<div class="container-fluid main">
    <div class="container">

    <h1><?php __("Last events"); ?></h1>

<table class="matable">
    <tr>
    <th><?php __("Date of event"); ?></th>
    <th><?php __("Event Type"); ?></th>
    <th style="width: 600px"><?php __("Book"); ?></th>
    <th><?php __("Event"); ?></th>
    </tr>
<?php
    while ($c=mysql_fetch_array($r)) { 
?>
<tr>
	<td><?php echo date_my2fr($c["ts"],true); ?></td>
	<td><?php echo $abltype[$c["type"]]; ?></td>
	<td><?php 
						   if ($c["title"]) {
						     echo $c["title"];
						       } else {
						     echo "<i>".$c["projectname"]."</i>";
						   }
?></td>
	<td><?php echo $c["message"]; ?></td>
</tr>
	<?php } ?>

</table>
</div>
</div>

<?php
  require_once("foot.php");
?>
