<?php

require_once("common.php");

if ($_SERVER["REMOTE_ADDR"]!="127.0.0.1" && $_SERVER["REMOTE_ADDR"]!=$MYIP) {
  echo "You should not be here ;(";
  exit();
}

$id=intval($_REQUEST["id"]);

$book=mqone("SELECT projectname FROM books WHERE id=".$id.";");
if ($book) {
  if (defined("DISPLAY")) {  putenv(DISPLAY); }
  putenv("HOME=/home/bookscanner");
  exec("nohup scantailor ".escapeshellarg(PROJECT_ROOT."/".$book["projectname"]."/".$book["projectname"]."_5.scantailor")." 0<&- &>/dev/null &");
}

//header("Location: ".$_SERVER["HTTP_REFERER"]);
exit();

