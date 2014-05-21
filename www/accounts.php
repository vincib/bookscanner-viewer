<?php

if (!($me["role"] & ROLE_ADMIN)) {
  header("Location: /");
  exit();
}


if (!isset($_REQUEST["action"])) {
  $_REQUEST["action"]="";
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
    mq("UPDATE users SET firstname='".addslashes($_POST["firstname"])."', lastname=='".addslashes($_POST["lasttname"])."', login='".addslashes($_POST["login"])."', email='".addslashes($_POST["email"])."' WHERE id='".intval($_POST["id"])."';");
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
      mq("INSERT INTO users SET firstname='".addslashes($_POST["firstname"])."', lastname='".addslashes($_POST["lasttname"])."', login='".addslashes($_POST["login"])."', email='".addslashes($_POST["email"])."', pass='".crypt($pass,getSalt())."', role=0;"); // FIXME: set the default role
      // Send the new password to the user's email :
      mail($_POST["email"],sprintf(_("Account created on http://%s"),$_SERVER["HTTP_HOST"]), 
	   sprintf(_("Hello,
Your new account has just been created on http://%s
Please go there to login and change your password.
Your login is %s
and your passwrd is %s

Thanks
"),$_SERVER["HTTP_HOST"],$_REQUEST["login"],$pass)
	   );
      
      $_REQUEST["msg"]=_("Account created successfully"); 
      $_REQUEST["action"]="";
    }
  break;

} // SWITCH 

  require_once("head.php");
  require_once("menu.php");
  require("messagebox.php");

?>
<div class="container-fluid main"> 

<div class="row">
<div class="span12">

    <h1><?php __("Accounts"); ?></h1>

<?php

switch ($_REQUEST["action"]) {
case "edit":
case "create":
?>
  <h2><?php
  if ($_REQUEST["action"]=="edit")  printf(_("Editing account %s"),htmlentities($_REQUEST["login"])); 
  else __("Creating new account"); 
?></h2>
<form method="post" action="/accounts">
  <input type="hidden" name="id" value="<?php echo $id; ?>" />
  <input type="hidden" name="action" value="do<?php echo $_REQUEST["action"]; ?>" />
		   <label for="firstname"><?php __("Firstname"); ?></label><input type="text" name="firstname" id="firstname" value="<?php eher("firstname"); ?>" style="width: 150px"/>
		   <label for="lastname"><?php __("Lastname"); ?></label><input type="text" name="lastname" id="lastname" value="<?php eher("lastname"); ?>" style="width: 150px"/>
		   <label for="email"><?php __("Email"); ?></label><input type="text" name="email" id="email" value="<?php eher("email"); ?>" style="width: 200px"/>
		   <label for="login"><?php __("Login"); ?></label><input type="text" name="login" id="login" value="<?php eher("login"); ?>" style="width: 100px"/>
<div>
      <input type="submit" name="go" value="<?php  
if ($_REQUEST["action"]=="edit") __("Edit this account"); 
else __("Create this account");
?>" />
 <input type="button" name="cancel" value="<?php __("Cancel"); ?>" onclick="document.location='/accounts'" />
</div>
</form>
<?php
   break; // ACTION EDIT / CREATE

case "delete":
  
  break; // ACTION DELETE

default:

$r=mq("SELECT * FROM users ORDER BY login;");
echo mysql_error();
?>


<p>
   <a class="btn" href="/accounts?action=create"><?php __("Create new account"); ?></a>
</p>
<table class="matable">
    <tr>
  <th><?php __("Edit"); ?></th>
    <th></th>
    <th><?php __("Login"); ?></th>
    <th><?php __("Name"); ?></th>
    <th><?php __("Role"); ?></th>
    </tr>
<?php
    while ($c=mysql_fetch_array($r)) { 
?>
<tr>
	<td>
  <a class="btn" href="/accounts?id=<?php echo $c["id"]; ?>&action=edit"><?php __("Edit"); ?></a>
  <a class="btn" href="/accounts?id=<?php echo $c["id"]; ?>&action=delete"><?php __("Delete"); ?></a>
        </td>
												     <td><img src="http://www.gravatar.com/avatar/<?php echo md5(strtolower($c["email"])); ?>?s=32">
        <td><?php echo htmlentities($c["login"]); ?></td>
        <td><?php echo htmlentities($c["firstname"]." ".$c["lastname"]); ?></td>
 <td><?php 
$first=true;
      foreach($arole as $k=>$v) {
	if ($c["role"] & $k) {
	  if (!$first) echo ", ";
	  echo _($v);
	  $first=false;
	}
      }
 ?></td>
      </tr>
	<?php } ?>

</table>
<?php 

    } // SWITCH ACTION
?>
</div>
</div>
</div>

<?php
  require_once("foot.php");
?>
