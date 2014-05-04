<?php

require_once("common.php");

unset($_SESSION["id"]);
session_write_close();

header("Location: /?msg=".urlencode(_("You have been logged out")));
exit();
