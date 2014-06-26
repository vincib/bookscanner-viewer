<?php


define("ROLE_ADMIN","1");
define("ROLE_EDITOR","2");
$arole=array(
	      "1" => "Administrator",
	      "2" => "Editor",
	      );

$alang= array(
	      "fre" => "French",
	      "eng" => "English",
	      "deu" => "Deutsch",
	      );
define("BOOKLOG_BOTINFO","1");
define("BOOKLOG_BOTERROR","2");
define("BOOKLOG_HUMAN","10");
$abltype=array(
	       BOOKLOG_BOTINFO => "Bot Information",
	       BOOKLOG_BOTERROR => "Bot Error",
	       BOOKLOG_HUMAN => "Human Information",
	       );

function booklog($book,$type,$message) {
  global $abltype;
  if (!isset($abltype[$type])) {
    echo "ERROR: booklog called with bad type:$type ($message)\n";
  } else {
    mq("INSERT INTO booklog SET book='".intval($book)."', type='".intval($type)."', message='".addslashes($message)."';");
  }
}

// FIXME : cache this in a serialized txt file.
$alicense=array();
$t=mq("SELECT * FROM license");
$freelicenses=array();
while($c=mysql_fetch_assoc($t)) {
  $alicense[$c["id"]]=$c;
  if ($c["free"]) $freelicenses[]=$c["id"];
}


