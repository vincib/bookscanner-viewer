<?php
function bsm_imagesize($file) {
  // return Width and Height of an image from its filename
  // false if the image has not been recognized or the file does not exist
  exec("identify ".escapeshellarg($file),$out);
  list($file,$type,$size,$other)=explode(" ",$out[0]);
  if (preg_match("#^([0-9]*)x([0-9]*)$#",trim($size),$mat)) {
    return array($mat[1],$mat[2]);
  } else {
    return false;
  }
}

function bsm_orientation($file) {
  // return orientation (6 or 8) of the image
  // 6 = turn right   8 = turn left  (90°, to get a proper picture)
  exec("jpegexiforient ".escapeshellarg($file),$out);
  $o=intval($out[0]);
  return $o;
}

// Params : out (file to save into)  substitute (path of the projets in the machine that will use scantailor)   name (project to generate)




function gen_scantailor($name) {

  if (defined("SCANTAILOR_GENERATOR_MODE") && SCANTAILOR_GENERATOR_MODE=="spreads") {
    return gen_scantailor_spreads($name);
  }
  // leftright mode:

  if (!is_dir(PROJECT_ROOT."/".$name."/left")) 
    mkdir(PROJECT_ROOT."/".$name."/left",0777);
  if (!is_dir(PROJECT_ROOT."/".$name."/right")) 
    mkdir(PROJECT_ROOT."/".$name."/right",0777);
  if (!is_dir(PROJECT_ROOT."/".$name."/book")) 
    mkdir(PROJECT_ROOT."/".$name."/book",0777);
  
  
  if (isset($_REQUEST["substitute"]) && $_REQUEST["substitute"]) {
    if (preg_match("#^[a-zA-Z]:\\.*#",$_REQUEST["substitute"])) {
      define("XML_WINDOWS",true);
    } else {
      define("XML_WINDOWS",false);
    }
    define("XML_ROOT",$_REQUEST["substitute"]);
  } else {
    define("XML_ROOT",PROJECT_ROOT);
    define("XML_WINDOWS",false);
  }
  
  $left=0; $right=0;
  $allleft=array(); $allright=array();
  
  $d=opendir(PROJECT_ROOT."/".$name."/left");
  while (($c=readdir($d))!==false) {
    if (is_file(PROJECT_ROOT."/".$name."/left/".$c)) {
      $left++;
      $allleft[]=$c;
    }
  }
  closedir($d);
  $d=opendir(PROJECT_ROOT."/".$name."/right");
  while (($c=readdir($d))!==false) {
    if (is_file(PROJECT_ROOT."/".$name."/right/".$c)) {
      $right++;
      $allright[]=$c;
    }
  }
  closedir($d);
  
  sort($allleft);
  sort($allright);
  
  if (!$right && !$left) {
    echo "No Picture File in this project !!";
    exit();
  }
  
  $id=1;
  
  // We purge the book/ subfolder in the project folder.
  exec("rm -rf ".escapeshellarg(PROJECT_ROOT."/".$name."/book"));
  mkdir(PROJECT_ROOT."/".$name."/book",0777);
  exec("rm -rf ".escapeshellarg(PROJECT_ROOT."/".$name."/booktif"));
  mkdir(PROJECT_ROOT."/".$name."/booktif",0777);
  
  ob_start();
  ?>
<project outputDirectory="<?php 
// "
if (XML_WINDOWS) {
  echo XML_ROOT.$name."\\booktif"; 
} else {
  echo XML_ROOT."/".$name."/booktif"; 
}

?>" layoutDirection="LTR">
  <directories>
    <directory path="<?php 
if (XML_WINDOWS) {
  echo str_replace("\\","/",XML_ROOT).$name."/book"; 
} else {
  echo XML_ROOT."/".$name."/book"; 
}
?>" id="<?php echo $id++; ?>"/>
  </directories>
  <files>
  <?php $firstfileid=$id; ?>
  <?php $found=true;
  reset($allleft);
  reset($allright);
$images=array();
$page=0;
while ($found) {
  $found=false;
  if ($v=each($allleft)) {

    $s=bsm_imagesize(PROJECT_ROOT."/".$name."/left/".$v[1]);
    if (count($s)==2) {
      // We symlink every picture file from the first left one from page 1
      symlink("../left/".$v[1], PROJECT_ROOT."/".$name."/book/i".sprintf("%05d",$page).".jpg");
      echo '    <file dirId="1" id="'.$id.'" name="'.'i'.sprintf("%05d",$page).'.jpg'.'"/>
';
      $images[$id]=array("name" => 'i'.sprintf("%05d",$page).'.jpg', "width" => $s[0], "height" => $s[1], "rotate" => 90,
			 "original" => $v[1]);
      $id++;
      $page++;
      $found=true;
    }
  }
  if ($v=each($allright)) {
    $s=bsm_imagesize(PROJECT_ROOT."/".$name."/right/".$v[1]);
    if (count($s)==2) {
      symlink("../right/".$v[1], PROJECT_ROOT."/".$name."/book/i".sprintf("%05d",$page).".jpg");
      echo '    <file dirId="1" id="'.$id.'" name="'.'i'.sprintf("%05d",$page).'.jpg'.'"/>
';
      $images[$id]=array("name" => 'i'.sprintf("%05d",$page).'.jpg', "width" => $s[0], "height" => $s[1], "rotate" => 270,
			 "original" => $v[1]);
      $id++;
      $page++;
      $found=true;
    }
  }
}
$lastfileid=$id;
?>
  </files>
  <images>
<?php
  $firstimageid=$id;
foreach($images as $i=>$image) { ?>
    <image subPages="1" fileImage="0" fileId="<?php echo $i; ?>" id="<?php echo $id; ?>">
      <size width="<?php echo $image["width"]; ?>" height="<?php echo $image["height"]; ?>"/>
      <dpi vertical="300" horizontal="300"/>
    </image>
      <?php 
    $images[$i]["id"]=$id;
      $id++;
}
$lastimageid=$id;
 ?>
  </images>
  <pages>
<?php
  $first=true;
  foreach($images as $i=>$image) { ?>
    <page imageId="<?php echo $image["id"]; ?>" subPage="single"<?php if ($first) echo " selected=\"selected\""; ?> id="<?php echo $id; ?>"/>
<?php
    $first=false;
    $images[$i]["page"]=$id;
    $id++;
  }
?>
  </pages>
  <file-name-disambiguation>
<?php
  foreach($images as $i=>$image) { 
    echo "<mapping file=\"".$i."\" label=\"0\"/>\n";
  }
?>
  </file-name-disambiguation>
  <filters>
    <fix-orientation>
<?php
    //print_r($images);
  foreach($images as $i=>$image) {
    echo "<image id=\"".$image["id"]."\">
        <rotation degrees=\"".$image["rotate"]."\"/>
      </image>
";
  }
?>
    </fix-orientation>
    <page-split defaultLayoutType="single-cut">
<?php
	foreach($images as $i=>$image) {
?>
      <image layoutType="auto-detect" id="<?php echo $image["id"]; ?>">
        <params mode="auto">
          <pages type="single-cut">
            <outline>
              <point x="0" y="0"/>
              <point x="<?php echo $image["height"]; ?>" y="0"/>
              <point x="<?php echo $image["height"]; ?>" y="<?php echo $image["width"]; ?>"/>
              <point x="0" y="<?php echo $image["width"]; ?>"/>
              <point x="0" y="0"/>
            </outline> 
            <cutter1>
              <p1 x="0" y="<?php echo $image["width"]; ?>"/>
              <p2 x="0" y="0"/>
            </cutter1>
            <cutter2>
              <p1 x="<?php echo $image["height"]; ?>" y="<?php echo $image["width"]; ?>"/>
              <p2 x="<?php echo $image["height"]; ?>" y="0"/>
            </cutter2>
          </pages>
          <dependencies>
            <rotation degrees="<?php echo $image["rotate"]; ?>"/>
            <size width="<?php echo $image["width"]; ?>" height="<?php echo $image["height"]; ?>"/>
            <layoutType>auto-detect</layoutType>
          </dependencies>
        </params>
      </image>
<?php } ?>
    </page-split>

    <deskew/>

    <select-content/>

    <page-layout/>

    <output>
	<?php foreach($images as $i=>$image) { ?>
      <page id="<?php echo $image["page"]; ?>">
        <zones/>
        <fill-zones/>
        <params depthPerception="2" despeckleLevel="cautious" dewarpingMode="off">
          <dpi vertical="300" horizontal="300"/>
          <color-params colorMode="bw">
            <color-or-grayscale whiteMargins="0" normalizeIllumination="0"/>
            <bw thresholdAdj="0"/>
          </color-params>
        </params>
      </page>
	<?php } ?>
    </output>
  </filters>
</project>
<?php

    if (isset($_REQUEST["out"])) {
      $out=str_replace("/","",str_replace("..","",$_REQUEST["out"]));
      file_put_contents(PROJECT_ROOT."/".$name."/".$out,ob_get_clean());
    }

?>

<?php
}
// '"
function gen_scantailor_spreads($name) {

    $rotate=array(0 => 0, 6 => 90, 8 => 270);
    
    if (!is_dir(PROJECT_ROOT."/".$name."/raw")) 
      mkdir(PROJECT_ROOT."/".$name."/raw",0777);
   
  if (isset($_REQUEST["substitute"]) && $_REQUEST["substitute"]) {
    if (preg_match("#^[a-zA-Z]:\\.*#",$_REQUEST["substitute"])) {
      define("XML_WINDOWS",true);
    } else {
      define("XML_WINDOWS",false);
    }
    define("XML_ROOT",$_REQUEST["substitute"]);
  } else {
    define("XML_ROOT",PROJECT_ROOT);
    define("XML_WINDOWS",false);
  }
  
  $raw=0;
  $allraw=array();
  
  $d=opendir(PROJECT_ROOT."/".$name."/raw");
  while (($c=readdir($d))!==false) {
    if (is_file(PROJECT_ROOT."/".$name."/raw/".$c)) {
      $raw++;
      $allraw[]=$c;
    }
  }
  closedir($d);
  
  sort($allraw);
  
  if (!$raw) {
    echo "No Picture File in this project !!";
    exit();
  }
  
  $id=1;
  
  // We purge the book/ subfolder in the project folder.
  exec("rm -rf ".escapeshellarg(PROJECT_ROOT."/".$name."/booktif"));
  mkdir(PROJECT_ROOT."/".$name."/booktif",0777);
  
  ob_start();
  ?>
<project outputDirectory="<?php 
// "
if (XML_WINDOWS) {
  echo XML_ROOT.$name."\\booktif"; 
} else {
  echo XML_ROOT."/".$name."/booktif"; 
}

?>" layoutDirection="LTR">
  <directories>
    <directory path="<?php 
if (XML_WINDOWS) {
  echo str_replace("\\","/",XML_ROOT).$name."/raw"; 
} else {
  echo XML_ROOT."/".$name."/raw"; 
}
?>" id="<?php
  //"
 echo $id++; ?>"/>
  </directories>
  <files>
  <?php
//"
 $firstfileid=$id; ?>
  <?php $found=true;
  reset($allraw);

$images=array();
$page=0;
while ($found) {
  $found=false;
  if ($v=each($allraw)) {

    $s=bsm_imagesize(PROJECT_ROOT."/".$name."/raw/".$v[1]);
    $o=bsm_orientation(PROJECT_ROOT."/".$name."/raw/".$v[1]);
    if (count($s)==2) {
      echo '    <file dirId="1" id="'.$id.'" name="'.$v[1].'"/>
';
      $images[$id]=array("name" => $v[1], "width" => $s[0], "height" => $s[1], "rotate" => $rotate[$o],
			 "original" => $v[1]);
      $id++;
      $page++;
      $found=true;
    }
  }
}
$lastfileid=$id;
?>
  </files>
  <images>
<?php
  $firstimageid=$id;
foreach($images as $i=>$image) { ?>
    <image subPages="1" fileImage="0" fileId="<?php echo $i; ?>" id="<?php echo $id; ?>">
      <size width="<?php echo $image["width"]; ?>" height="<?php echo $image["height"]; ?>"/>
      <dpi vertical="300" horizontal="300"/>
    </image>
      <?php 
    $images[$i]["id"]=$id;
      $id++;
}
$lastimageid=$id;
 ?>
  </images>
  <pages>
<?php
  $first=true;
  foreach($images as $i=>$image) { ?>
    <page imageId="<?php echo $image["id"]; ?>" subPage="single"<?php if ($first) echo " selected=\"selected\""; ?> id="<?php echo $id; ?>"/>
<?php
    $first=false;
    $images[$i]["page"]=$id;
    $id++;
  }
?>
  </pages>
  <file-name-disambiguation>
<?php
  foreach($images as $i=>$image) { 
    echo "<mapping file=\"".$i."\" label=\"0\"/>\n";
  }
?>
  </file-name-disambiguation>
  <filters>
    <fix-orientation>
<?php
    //print_r($images);
  foreach($images as $i=>$image) {
    echo "<image id=\"".$image["id"]."\">
        <rotation degrees=\"".$image["rotate"]."\"/>
      </image>
";
  }
?>
    </fix-orientation>
    <page-split defaultLayoutType="single-cut">
<?php
	foreach($images as $i=>$image) {
?>
      <image layoutType="auto-detect" id="<?php echo $image["id"]; ?>">
        <params mode="auto">
          <pages type="single-cut">
            <outline>
              <point x="0" y="0"/>
              <point x="<?php echo $image["height"]; ?>" y="0"/>
              <point x="<?php echo $image["height"]; ?>" y="<?php echo $image["width"]; ?>"/>
              <point x="0" y="<?php echo $image["width"]; ?>"/>
              <point x="0" y="0"/>
            </outline> 
            <cutter1>
              <p1 x="0" y="<?php echo $image["width"]; ?>"/>
              <p2 x="0" y="0"/>
            </cutter1>
            <cutter2>
              <p1 x="<?php echo $image["height"]; ?>" y="<?php echo $image["width"]; ?>"/>
              <p2 x="<?php echo $image["height"]; ?>" y="0"/>
            </cutter2>
          </pages>
          <dependencies>
            <rotation degrees="<?php echo $image["rotate"]; ?>"/>
            <size width="<?php echo $image["width"]; ?>" height="<?php echo $image["height"]; ?>"/>
            <layoutType>auto-detect</layoutType>
          </dependencies>
        </params>
      </image>
<?php } ?>
    </page-split>

    <deskew/>

    <select-content/>

    <page-layout/>

    <output>
	<?php foreach($images as $i=>$image) { ?>
      <page id="<?php echo $image["page"]; ?>">
        <zones/>
        <fill-zones/>
        <params depthPerception="2" despeckleLevel="cautious" dewarpingMode="off">
          <dpi vertical="300" horizontal="300"/>
          <color-params colorMode="bw">
            <color-or-grayscale whiteMargins="0" normalizeIllumination="0"/>
            <bw thresholdAdj="0"/>
          </color-params>
        </params>
      </page>
	<?php } ?>
    </output>
  </filters>
</project>
<?php

    if (isset($_REQUEST["out"])) {
      $out=str_replace("/","",str_replace("..","",$_REQUEST["out"]));
      file_put_contents(PROJECT_ROOT."/".$name."/".$out,ob_get_clean());
    }

?>

<?php
}

