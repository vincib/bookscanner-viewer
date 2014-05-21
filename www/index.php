<?php

require_once("common.php");

$url=$_SERVER["REQUEST_URI"];
if (($p=strpos($_SERVER["REQUEST_URI"],"?"))!==false) {
  $url=substr($_SERVER["REQUEST_URI"],0,$p);
}
switch($url) {
case "/signin":
  require_once("login.php"); 
  break;
case "/logout":
  require_once("logout.php"); 
  break;
case "/booklist":
  require_once("booklist.php"); 
  break;
case "/events":
  require_once("events.php"); 
  break;
case "/accounts":
  require_once("accounts.php"); 
  break;
default:
  require_once("head.php");
  require_once("menu.php");
  require("messagebox.php");
  //  print_r($_SERVER);
  require_once("foot.php");
  break;
}

?>