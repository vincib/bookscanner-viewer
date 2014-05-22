<?php

require_once("common.php");

$url=$_SERVER["REQUEST_URI"];
if (($p=strpos($_SERVER["REQUEST_URI"],"?"))!==false) {
  $url=substr($_SERVER["REQUEST_URI"],0,$p);
}

if ($url=="/") { header("Location: /booklist"); exit(); }

$urls=array(
	   "/download" => "download.php",
	   "/signin" => "login.php",
	   "/logout" => "logout.php",
	   "/booklist" => "booklist.php",
	   "/bookedit" => "booklist.php",
	   "/events" => "events.php",
	   "/accounts" => "accounts.php",
	   );
if (isset($urls[$url])) {

  require_once($urls[$url]);

} else {

  require_once("head.php");
  require_once("menu.php");
  require("messagebox.php");
  //  print_r($_SERVER);
  require_once("foot.php");
  break;

}

?>