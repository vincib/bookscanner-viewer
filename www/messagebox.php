
<div style="position: fixed; left: 20px; top: 60px; width: 800px; height: 48px;" id="messagebox">
<?php 
if (isset($_REQUEST["msg"])) {
  ?>
  <div id="alertmsg" class="alert alert-success">  <button type="button" class="close" data-dismiss="alert">&times;</button> <?php ehe($_REQUEST["msg"]); ?></div>
<script type="text/javascript">
  $(document).ready(
		    function () { window.setTimeout(function() { $("#alertmsg").fadeTo(500,0, function () { $("#alertmsg").alert('close'); } ) }, 3000); }
		    );
</script>
<?php
}
?>
<?php 
if (isset($_REQUEST["error"])) {
  ?>
  <div id="alertmsg" class="alert alert-error">  <button type="button" class="close" data-dismiss="alert">&times;</button> <?php ehe($_REQUEST["error"]); ?></div>
<script type="text/javascript">
  $(document).ready(
		    function () { window.setTimeout(function() { $("#alertmsg").fadeTo(500,0, function () { $("#alertmsg").alert('close'); } ) }, 3000); }
		    );
</script>
<?php
}
?>
</div>
