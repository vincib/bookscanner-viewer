<?php
require_once("common.php");
?>
    <!-- Navbar
    ================================================== -->
    <div class="navbar navbar-fixed-top">
      <div class="navbar-inner">
        <div class="container">
          <button type="button" class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
       <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>

          <div class="nav-collapse collapse">
            <ul class="nav">
       <li<?php if (substr($_SERVER["REQUEST_URI"],0,9)=="/booklist") echo ' class="active"'; ?>>
    <a href="/booklist"><?php __("Book Listing"); ?></a>
              </li>
<?php
    if ($me["id"]!=0) /* FIXME */ { ?>
       <li<?php if (substr($_SERVER["REQUEST_URI"],0,9)=="/bookedit") echo ' class="active"'; ?>>
    <a href="/bookedit"><?php __("Book Editor"); ?></a>
              </li>

       <li<?php if (substr($_SERVER["REQUEST_URI"],0,7)=="/events") echo ' class="active"'; ?>>
    <a href="/events"><?php __("Last events"); ?></a>
              </li>

       <li<?php if (substr($_SERVER["REQUEST_URI"],0,10)=="/proofread") echo ' class="active"'; ?>>
    <a href="/proofread"><?php __("Proof reading"); ?></a>
              </li>
       <li<?php if (substr($_SERVER["REQUEST_URI"],0,5)=="/misc") echo ' class="active"'; ?>>
    <a href="/misc"><?php __("Misc."); ?></a>
              </li>
  <?php } ?>
              <li class="">
                <a href=""></a>
              </li>

            </ul>
       <?php if ($_SESSION["id"]) { ?>
<div id="gravatar" class="pull-right" style="padding-left: 10px; padding-top:4px"><img src="https://www.gravatar.com/avatar/<?php echo md5(strtolower($me["email"])); ?>?s=32"></div>
<div id="user" class="pull-right btn-group"><button class="btn dropdown-toggle btn-info" data-toggle="dropdown">
<?php echo $me["firstname"]." ".$me["lastname"]; ?>  <span class="caret"></span>
   </button>
   <ul class="dropdown-menu">
   <li id="ca-logout"><a href="/logout"><?php __("Logout"); ?></a></li>
 <?php if ($me["role"] & ROLE_ADMIN) { ?> 
  <li id="ca-edit"><a href="/accounts"><?php __("Edit accounts"); ?></a></li>
  <li id="ca-edit"><a href="/collections"><?php __("Edit collections"); ?></a></li>
 <?php } ?>
<li id="ca-my"><a href="/accounts?action=edit&id=<?php echo $me["id"]; ?>"><?php __("My account"); ?></a></li>
   </ul>
</div>
 <?php } else { ?> 
<div id="gravatar" class="pull-right" style="padding-left: 10px; padding-top:4px"><img src="user2.png"></div>
<div id="user" class="pull-right btn-group"><button class="btn dropdown-toggle btn-info" onclick="document.location='/signin'">
   <?php __("Sign in"); ?> <span class="caret"></span>
   </button>  
 <?php } ?>


          </div>
        </div>
      </div>
    </div>

</div>
