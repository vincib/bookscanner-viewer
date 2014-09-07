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
    <h1><?php __("Misc."); ?></h1>

    <h2><?php __("Possible status of book projects"); ?></h2>
<ul>
<?php 
    foreach($astatus as $k=>$v) {
      echo "<li>[".$k."] <b>".$v[0]."</b>  &nbsp;-&nbsp; ".$v[1]."</li>\n";
    }
?>
</ul>
</div>
</div>
</div>

<?php
  require_once("foot.php");
?>
