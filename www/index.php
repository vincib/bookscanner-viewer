<?php

require_once("common.php");

switch($_SERVER["REQUEST_URI"]) {
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
default:
  require_once("head.php");
  require_once("menu.php");
  require("messagebox.php");
  require_once("foot.php");
  break;
}

?>