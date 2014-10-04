<?php

require_once("common.php");

if (!($me["role"] & ROLE_ADMIN)
    && !($me["id"]==$_REQUEST["id"] && ($_REQUEST["action"]=="edit" || $_REQUEST["action"]=="doedit") )
    ) {
  $_REQUEST["error"]=_("You are not allowed to see this page. Sorry"); 
  require_once("nothing.php");
  exit();
}


if (!isset($_REQUEST["action"])) {
  $_REQUEST["action"]="";
}

  require_once("doaccounts.php");

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
<p>
      <input type="submit" name="go" value="<?php  
if ($_REQUEST["action"]=="edit") __("Edit this account"); 
else __("Create this account");
?>" />
 <input type="button" name="cancel" value="<?php __("Cancel"); ?>" onclick="document.location='/accounts'" />
</p>
<p>
 <input type="button" name="sendpassword" value="<?php __("Reset and Send a new password by mail"); ?>" onclick="document.location='/accounts?action=sendpassword&id=<?php echo intval($_REQUEST["id"]); ?>'" />
</p>
</div>
</form>
<?php
   break; // ACTION EDIT / CREATE

case "delete":
  
  break; // ACTION DELETE

default:

$r=mq("SELECT * FROM users ORDER BY login;");
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
    <th><?php __("Last Login"); ?></th>
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
      <td><?php echo date_my2fr($c["lastlogin"],true); ?></td>
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
