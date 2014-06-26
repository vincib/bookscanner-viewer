<?php

require_once("common.php");

if (!($me["role"] & ROLE_ADMIN)
    ) {
  $_REQUEST["error"]=_("You are not allowed to see this page. Sorry"); 
  require_once("nothing.php");
  exit();
}

if (!isset($_REQUEST["action"])) {
  $_REQUEST["action"]="";
}
switch ($_REQUEST["action"]) {
case "edit":
case "doedit":
  $id=intval($_REQUEST["id"]);
  $account=mqone("SELECT * FROM collections WHERE id='$id';");
  if (!$account) {
    $_REQUEST["error"]=_("Collection not found"); 
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
    // search for existing name : 
    $already=mqone("SELECT * FROM collections WHERE  id!='".intval($_POST["id"])."' AND name='".addslashes($_POST["name"])."';");
    if ($already) {
      $_REQUEST["error"]=_("This collection is already used, please choose another one"); 
      $_REQUEST["action"]="edit";
    }
    mq("UPDATE collections SET name='".addslashes($_POST["name"])."' WHERE id='".intval($_POST["id"])."';");
    $_REQUEST["msg"]=_("Collection renamed successfully"); 
    $_REQUEST["action"]="";
  }
  break;


case "docreate":
  // CREATE
  // search for existing name : 
    $already=mqone("SELECT * FROM collections WHERE name='".addslashes($_POST["name"])."';");
    if ($already) {
      $_REQUEST["error"]=_("This collection is already used, please choose another one"); 
      $_REQUEST["action"]="create";
    } else {
      $pass=mkpass();
      mq("INSERT INTO collections SET name='".addslashes($_POST["name"])."';");
      $_REQUEST["msg"]=_("Collection created successfully"); 
      $_REQUEST["action"]="";
    }
  break;
case "delete":
  // DELETE
      mq("DELETE FROM collections WHERE id='".addslashes($_REQUEST["id"])."';");
      mq("UPDATE books SET collection=0 WHERE collection='".addslashes($_REQUEST["id"])."';");
      
      $_REQUEST["msg"]=_("Collection deleted successfully"); 
      $_REQUEST["action"]="";
  break;

} // SWITCH 

  require_once("head.php");
  require_once("menu.php");
  require("messagebox.php");

?>
<div class="container-fluid main"> 

<div class="row">
<div class="span12">

    <h1><?php __("Collections"); ?></h1>

<?php

switch ($_REQUEST["action"]) {
case "edit":
case "create":
?>
  <h2><?php
  if ($_REQUEST["action"]=="edit")  printf(_("Editing collection %s"),htmlentities($_REQUEST["name"])); 
  else __("Creating new collection"); 
?></h2>
<form method="post" action="/collections">
  <input type="hidden" name="id" value="<?php echo $id; ?>" />
  <input type="hidden" name="action" value="do<?php echo $_REQUEST["action"]; ?>" />
		   <label for="name"><?php __("Collection name"); ?></label><input type="text" name="name" id="name" value="<?php eher("name"); ?>" style="width: 150px"/>
<div>
      <input type="submit" name="go" value="<?php  
if ($_REQUEST["action"]=="edit") __("Edit this collection"); 
else __("Create this collection");
?>" />
 <input type="button" name="cancel" value="<?php __("Cancel"); ?>" onclick="document.location='/collections'" />
</div>
</form>
<?php
   break; // ACTION EDIT / CREATE

case "delete":
  
  break; // ACTION DELETE

default:

$r=mq("SELECT c.id,c.name,count(b.id) AS books FROM collections c LEFT JOIN books b ON b.collection=c.id GROUP BY c.id ORDER BY c.name;");
?>

<p>
   <a class="btn" href="/collections?action=create"><?php __("Create new collection"); ?></a>
</p>
<table class="matable">
    <tr>
  <th><?php __("Edit"); ?></th>
    <th><?php __("Name"); ?></th>
    <th><?php __("# books"); ?></th>
    </tr>
<?php
    while ($c=mysql_fetch_array($r)) { 
?>
<tr>
	<td>
  <a class="btn" href="/collections?id=<?php echo $c["id"]; ?>&action=edit"><?php __("Edit"); ?></a>
  <a class="btn" onclick="return confirm('Confirm the deletion of collection <?php echo addslashes(htmlentities($c["name"])); ?>')" href="/collections?id=<?php echo $c["id"]; ?>&action=delete"><?php __("Delete"); ?></a>
        </td>
        <td><?php echo htmlentities($c["name"]); ?></td>
        <td><?php echo htmlentities($c["books"]); ?></td>
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
