<?php

require_once("../www/common.php");

$t="";
if (isset($argv[1])) $t="-".$argv[1];
$PIDFILE="/tmp/bookscanner-viewer-daemon".$t.".pid";
if (is_file($PIDFILE)) {
  $pid=intval(file_get_contents($PIDFILE));
  if (is_dir("/proc/".$pid)) {
    echo "Daemon already running, exit\n";
    exit();
  } else {
    echo "Remaining PID file $PIDFILE but no process associated to it, overwriting\n";
  }
}

file_put_contents($PIDFILE,getmypid());


mq("LOCK TABLES books WRITE");
$r=mq("SELECT * FROM books WHERE status=".STATUS_SCANOK." LIMIT 1;");
$c=mysql_fetch_assoc($r);

if ($c) {
  mq("UPDATE books SET status=".STATUS_DOINGTAILOR1." WHERE id=".$c["id"].";");
}
mq("UNLOCK TABLES");

if ($c) {
  echo "doing scantailor1 for book '".$c["projectname"]."' having id ".$c["id"]."\n";
  // Create its scantailor project (named projectname_5.scantailor) then process it through scantailor step 1 to 5, and save it to projectname_6.scantailor
  if (scantailor1($c)) {
    mq("UPDATE books SET status=".STATUS_WAITUSERTAILOR." WHERE id=".$c["id"].";");
    mq("INSERT INTO booklog SET book=".$c["id"].", type=2, message='Scantailor step 1 to 5 done';");
  } else {
    mq("UPDATE books SET status=".STATUS_UNKNOWN." WHERE id=".$c["id"].";");
    mq("INSERT INTO booklog SET book=".$c["id"].", type=2, message='ERROR DOING Scantailor step 1 to 5';");
  }
}

mq("LOCK TABLES books WRITE");
$r=mq("SELECT * FROM books WHERE status=".STATUS_USEROKTAILOR6." LIMIT 1;");
$c=mysql_fetch_assoc($r);

if ($c) {
  mq("UPDATE books SET status=".STATUS_DOINGTAILOR6." WHERE id=".$c["id"].";");
}
mq("UNLOCK TABLES");
if ($c) {
  echo "doing scantailor6 for book '".$c["projectname"]."' having id ".$c["id"]."\n";

  // Create its TIFF files from projectname_6.scantailor using Scantailor
  if (scantailor6($c)) {
    mq("UPDATE books SET status=".STATUS_TAILOROK." WHERE id=".$c["id"].";");
    mq("INSERT INTO booklog SET book=".$c["id"].", type=2, message='Scantailor step 6 done';");
  } else {
    mq("UPDATE books SET status=".STATUS_UNKNOWN." WHERE id=".$c["id"].";");
    mq("INSERT INTO booklog SET book=".$c["id"].", type=2, message='ERROR DOING Scantailor step 6';");
  }
  
}

@unlink($PIDFILE);

