<?php

require_once("config.php");

function connect() {
  global $MY;
  mysql_connect($MY[0],$MY[1],$MY[2]);
  mysql_select_db($MY[3]);
  mysql_query("SET NAMES UTF8;");
}

connect();

session_name("bookviewer");
session_start();

if (!isset($_SESSION["id"])) {
  $_SESSION["id"]=0;
  $_SESSION["me"]=array("id" => 0, "name" => "anonymous", "firstname" => "", "email" => "");
}
$me=$_SESSION["me"];

require_once("functions.php");
require_once("bookfuncs.php");

