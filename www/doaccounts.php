<?php


require_once("common.php");

if (!($me["role"] & ROLE_ADMIN)
    && !($me["id"]==$_REQUEST["id"] && ($_REQUEST["action"]=="edit" || $_REQUEST["action"]=="doedit") )
    ) {
  $_REQUEST["error"]=_("You are not allowed to see this page. Sorry"); 
  require_once("nothing.php");
  exit();
}


switch ($_REQUEST["action"]) {
case "edit":
case "doedit":
  $id=intval($_REQUEST["id"]);
  $account=mqone("SELECT * FROM users WHERE id='$id';");
  if (!$account) {
    $_REQUEST["error"]=_("Account not found"); 
    require_once("head.php");
    require_once("menu.php");
    require("messagebox.php");
    require_once("foot.php");
    exit();
  }
  if ($_REQUEST["action"]=="edit") {
    foreach($account as $k=>$v) $_REQUEST[$k]=$v;
  }
  if ($_REQUEST["action"]=="doedit") {
    // UPDATE
    // search for existing login : 
    $already=mqone("SELECT * FROM users WHERE  id!='".intval($_POST["id"])."' AND login='".addslashes($_POST["login"])."';");
    if ($already) {
      $_REQUEST["error"]=_("This login is already used, please choose another one"); 
      $_REQUEST["action"]="edit";
    }
    mq("UPDATE users SET firstname='".addslashes($_POST["firstname"])."', lastname='".addslashes($_POST["lastname"])."', login='".addslashes($_POST["login"])."', email='".addslashes($_POST["email"])."' WHERE id='".intval($_POST["id"])."';");
    $_REQUEST["msg"]=_("Account edited successfully"); 
    $_REQUEST["action"]="";
  }
  break;


case "docreate":
  // CREATE
  // search for existing login : 
    $already=mqone("SELECT * FROM users WHERE login='".addslashes($_POST["login"])."';");
    if ($already) {
      $_REQUEST["error"]=_("This login is already used, please choose another one"); 
      $_REQUEST["action"]="create";
    } else {
      $pass=mkpass();
      mq("INSERT INTO users SET firstname='".addslashes($_POST["firstname"])."', lastname='".addslashes($_POST["lastname"])."', login='".addslashes($_POST["login"])."', email='".addslashes($_POST["email"])."', pass='".crypt($pass,getSalt())."', role=0;"); // FIXME: set the default role
      // Send the new password to the user's email :
      mail($_POST["email"],sprintf(_("Account created on https://%s"),$_SERVER["HTTP_HOST"]), 
	   sprintf(_("Hello,
Your new account has just been created on https://%s
Please go there to login and change your password.
Your login is %s
and your password is %s

Thanks
"),$_SERVER["HTTP_HOST"],$_REQUEST["login"],$pass)
	   );
      
      $_REQUEST["msg"]=_("Account created successfully"); 
      $_REQUEST["action"]="";
    }
  break;


case "sendpassword":
  // CHANGE PASSWORD and SEND it by mail
  // search for existing login : 
  $pass=mkpass();
  $id=intval($_REQUEST["id"]);
  $him=mqone("SELECT * FROM users WHERE id='$id';");
  if (!$him) {
    $_REQUEST["error"]=_("User not found");
    unset($_REQUEST["action"]);
  } else {
    mq("UPDATE users SET pass='".crypt($pass,getSalt())."' WHERE id='$id';"); 
    // Send the new password to the user's email :
    mail($him["email"],sprintf(_("Password changed on https://%s"),$_SERVER["HTTP_HOST"]), 
	 sprintf(_("Hello,
An administrator requested a new password for your account on https://%s
Please go there to login 
Your login is %s
and your password is %s

Thanks
"),$_SERVER["HTTP_HOST"],$him["login"],$pass)
	 );
      
    $_REQUEST["msg"]=_("Password changed and sent successfully"); 
    $_REQUEST["action"]="";
  }
  break;


case "delete":
  // DELETE
  $already=mqone("SELECT * FROM users WHERE id='".intval($_GET["id"])."';");
  if (!$already) {
    $_REQUEST["error"]=_("Can't find This login, please check");
    unset($_REQUEST["action"]);
  } else {
    mq("DELETE FROM users WHERE id='".intval($_GET["id"])."';");
    $_REQUEST["msg"]=_("Account deleted successfully");
    $_REQUEST["action"]="";
  }
  break;
} // SWITCH 



