<?php

// we will see that later :) 
$lang="en";

mysql_connect("localhost","bv","poipoi");
mysql_select_db("bv");
mysql_query("SET NAMES UTF8;");

// Path where the projects will be stored (in the filesystem)
define("PROJECT_ROOT","/home/benjamin/lqdn2/bookscanner/p");

