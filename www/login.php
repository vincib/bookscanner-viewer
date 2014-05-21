<?php

require_once("common.php");
$error="";

if (isset($_POST["login"]) && isset($_POST["password"])) {
  $me=false;
  $me=mqone("SELECT * FROM users WHERE `login`='".addslashes($_POST["login"])."';");
  if ($me) {
    print_r($me);
    if ($me["pass"]!=crypt($_POST["password"],$me["pass"])) {
      $error=_("Incorrect username or password");
    } else {
      $_SESSION["id"]=$me["id"];
      $_SESSION["me"]=$me;
      session_write_close();
      header("Location: /?msg="._("Welcome"));
      exit();
    }
  } else {
    $error=_("Incorrect username or password");
  }
}
if ($error) { $_REQUEST["error"]=$error; }
require_once("head.php");
require_once("menu.php");
require_once("messagebox.php");

?>
<div class="container-fluid main">


    <div class="container">

<form method="post" action="login.php" class="form-signin">
    <h2 class="form-signin-heading"><?php __("Please sign in"); ?></h2>
        <input type="text" class="input-block-level" placeholder="<?php __("Login"); ?>" name="login" id="login">
        <input type="password" class="input-block-level" placeholder="<?php __("Password"); ?>" name="password" id="password">
    <button class="btn btn-large btn-primary" type="submit"><?php __("Sign in"); ?></button>
      </form>

    </div> 


<?php

require_once("foot.php");

?>