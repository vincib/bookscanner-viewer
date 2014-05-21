<?php

require_once("config.php");

session_name("bookviewer");
session_start();

if (!isset($_SESSION["id"])) {
  $_SESSION["id"]=0;
  $_SESSION["me"]=array("id" => 0, "name" => "anonymous", "firstname" => "", "email" => "");
}
$me=$_SESSION["me"];

require_once("functions.php");
require_once("bookfuncs.php");

