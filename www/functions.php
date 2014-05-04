<?php

define("BOOKLOG_BOTINFO","1");
define("BOOKLOG_BOTERROR","2");
define("BOOKLOG_HUMAN","10");
$abltype=array(
	       BOOKLOG_BOTINFO => "Bot Information",
	       BOOKLOG_BOTERROR => "Bot Error",
	       BOOKLOG_HUMAN => "Human Information",
	       );
function booklog($book,$type,$message) {
  global $abltype;
  if (!isset($abltype[$type])) {
    echo "ERROR: booklog called with bad type:$type ($message)\n";
  } else {
    mq("INSERT INTO booklog SET book='".intval($book)."', type='".intval($type)."', message='".addslashes($message)."';");
  }
}


function ifcheck($cond) {
  if ($cond) echo " checked=\"checked\""; 
}


function do_radio($name,$mat,$cur) {
  foreach($mat as $k=>$v) {
    echo "<label for=\"${name}_$k\" class=\"radio\"><input type=\"radio\" name=\"$name\" id=\"${name}_$k\" value=\"$k\" ";
    if ($k==$cur) echo " checked=\"checked\"";
    echo "/> $v</label>";
  }
}


/*
Affiche (echo) les champs de sélection du tableau $mat avec pour valeur par défaut $current
*/
function do_select($mat,$current,$style=false) {
	global $db;
	if (is_array($mat)) {
		reset($mat);
		while (list($key,$val)=each($mat)) {
			echo "<option value=\"$key\"";
			if ($style!==false) {
				if ($style[$key]) echo "style=\"color:red\"";
			}
			if ($key==$current) echo " SELECTED";
			echo ">$val</option>\n";
		}
	} else {
		$db->query("SELECT id,data FROM $mat;");
		while ($db->next_record()) {
			echo "<option value=\"".$db->Record["id"]."\"";
			if ($db->Record["id"]==$current) echo " SELECTED";
			echo ">".$db->Record["data"]."</option>\n";
		}
	}
}

/* Check an email address, use checkloginmail and checkfqdn */
function checkmail($mail,$force=0) {
	// Retourne 0 si tout va bien, sinon retourne un code erreur...
	// 6 si le mail contient aucun ou plus d'un @
	// 1 2 3 ou 4 si le domaine est incorrect.
	// 5 s'il y a caractères interdits dans la partie gauche du @
	if ($force==0 && trim($mail)=="") {
		return 0;
	}
	$t=explode("@",$mail);
	if (count($t)!=2) {
		return 6;
	}
	$c=checkfqdn($t[1]);
	if ($c)
		return $c;
	// Verification de la partie gauche :
	if (!checkloginmail($t[0])) {
		return 5;
	}
	return 0;
}

/* Check that a domain name is fqdn compliant */
function checkfqdn($fqdn) {
	// (RFC 1035 http://www.cis.ohio-state.edu/cgi-bin/rfc/rfc1035.html)
	// Retourne 0 si tout va bien, sinon, retourne un code erreur...
	// 1. Nom de domaine complet trop long.
	// 2. L'un des membres est trop long.
	// 3. Caractère interdit dans l'un des membres.
	if (strlen($fqdn)>255)
		return 1;
	$members=explode(".", $fqdn);
	if (count($members)>1) {
		reset($members);
		while (list ($key, $val) = each ($members)) {
			if (strlen($val)>63)
				return 2;
			if (!preg_match("#^[a-z0-9]([a-z0-9-]*[a-z0-9])?$#i",$val)) {
				return 3;
			}
		}
	} else {
		return 4;
	}
	return 0;
}

/*
2002-11-12
12/11/2002
0123456789
		Convertion d'une date MYsql en une date FRancaise et l'inverse :
*/
function date_my2fr($str,$long) {
	if (!$str) return "";
	if ($long) {
	  return substr($str,8,2)."/".substr($str,5,2)."/".substr($str,0,4)." ".substr($str,11,8);
	} else {
	  return substr($str,8,2)."/".substr($str,5,2)."/".substr($str,0,4);
	}
}
function date_ts2fr($str) {
	if (!$str) return "";
	return substr($str,6,2)."/".substr($str,4,2)."/".substr($str,0,4);
}
function date_fr2my($str) {
	if (!$str) return "";
	if (!ereg("([0-9]+)/([0-9]+)/([0-9]+)",$str,$match)) {
		if (!ereg("([0-9]+)/([0-9+)",$str,$match)) {
			return false;
		} else {
			// mm/yyyy : 
			$m=intval($match[1]);
			$d=1;
			$y=intval($match[2]);
		}
	} else {
	  // dd/mm/yyyy : 
		$d=intval($match[1]);
		$m=intval($match[2]);
		$y=intval($match[3]);
	}
	if ($d<1 || $d>31) return false;
	if ($m<1 || $m>12) return false;
	return $y."-".$m."-".$d;
}

function maxstr($str,$max) {
	if (strlen($str)>$max)
		return substr($str,0,$max-3)."...";
	else
		return $str;
}

// Envoi d'un mail à $to de l'expéditeur $from
// en utilisant le modèle $template (fichier .txt avec balises %%XX%%)
// en remplacant les %%XX%% par les champs du tableau (array) $fields
// Les templates ont pour première ligne le sujet du mail.
function mail_tpl_str($from, $to, $subject,$text, $fields) {
  global $errno,$er;
  $er->log(ERROR_LEVEL_FPUT,"mail_tpl",array("from"=>$from,"to"=>$to,"template"=>$template));
  //  echo "mailtpl : $from : $to : $template <br>\n";

  /* Envoi d'un mail avec substitution de patron */
  
  reset($fields);
  while (list($k,$v)=each($fields)) {
    $subject=str_replace("%%".$k."%%",$v,$subject);
    $text=str_replace("%%".$k."%%",$v,$text);
  }
  $subject=stripslashes($subject);
  $text=stripslashes($text);
  
  return mail($to,$subject,$text,"From: $from\nReply-to: $from\nReturn-Path: $from\n");
}

function eher($str) {
  echo htmlentities($_REQUEST[$str]);
}
function eheri($str) {
  if (intval($_REQUEST[$str])!=0)  {
    echo htmlentities($_REQUEST[$str]);
  }
}

function order($field,$asc) {
  $ways=array("ASC","DESC");
  list($cfield,$cway)=explode(" ",$_REQUEST["order"]);
  if ($cway=="ASC") $cway=0; else $cway=1;
  if ($asc=="ASC") $asc=0; else $asc=1;
  if ($cfield==$field) {
    $asc=1-$cway;
  }
  echo $field."%20".$ways[$asc];
}
function order2($field) {
  list($cfield,$cway)=explode(" ",$_REQUEST["order"]);
  if ($field!=$cfield) return;
  echo "<img src=\"/fact/";
  if ($cway=="ASC") {
    echo "up-arrow.gif";
  } else {
    echo "down-arrow.gif";
  }
  echo "\" alt=\"\" title=\"\" />";
}

/* Affiche un pager sous la forme suivante : 
  Page précédente 0 1 2 ... 16 17 18 19 20 ... 35 36 37 Page suivante
  Les arguments sont comme suit : 
  $offset = L'offset courant de la page.
  $count = Le nombre d'éléments affiché par page.
  $total = Le nombre total d'éléments dans l'ensemble
  $url = L'url à afficher. %%offset%% sera remplacé par le nouvel offset des pages.
  $before et $after sont le code HTML à afficher AVANT et APRES le pager SI CELUI CI DOIT ETRE AFFICHE ...
  TODO : ajouter un paramètre class pour les balises html A.
*/
function pager($offset,$count,$total,$url,$before="",$after="") {
  // On nettoie les variables hein ...
  //  echo "PAGER : offset:$offset, count:$count, total:$total, url:$url<br />";
  $offset=intval($offset); 
  $count=intval($count); 
  $total=intval($total); 
  if ($offset<=0) $offset="0";
  if ($count<=1) $count="1";
  if ($total<=0) $total="0";
  if ($total<$offset) $offset=max(0,$total-$count);

  if ($total<=$count) { // Cas où l'on n'a pas assez d'éléments pour afficher un pager :) 
    return true;
  }
  echo $before;
  // Doit-on afficher Page précédente ?
  if ($offset) {
    $o=max($offset-$count,0);
    echo "<li><a href=\"".str_replace("%%offset%%",$o,$url)."\" alt=\"(Ctl/Alt-p)\" title=\"(Alt-p)\" accesskey=\"p\">&laquo;</a></li>";
  } else {
    echo "<li class=\"disabled\"><a>&laquo;</a></li>";
  }

  if ($total>(2*$count)) { // On n'affiche le pager central (0 1 2 ...) s'il y a au moins 2 pages.

    if (($total<($count*10)) && ($total>$count)) {  // moins de 10 pages : 
      for($i=0;$i<$total/$count;$i++) {
        $o=$i*$count;
        if ($offset==$o) {
	    echo "<li class=\"active\"><a>".$i."</a></li> "; 
        } else {
          echo "<li><a href=\"".str_replace("%%offset%%",$o,$url)."\">$i</a></li> ";
        }
      }
    } else { // Plus de 10 pages, on affiche 0 1 2 , 2 avant et 2 après la page courante, et les 3 dernieres
      for($i=0;$i<=2;$i++) {
        $o=$i*$count;
        if ($offset==$o) {
	    echo "<li class=\"active\"><a>".$i."</a></li> "; 
        } else {
          echo "<li><a href=\"".str_replace("%%offset%%",$o,$url)."\">$i</a></li> ";
        }
      }
      if ($offset>=$count && $offset<($total-2*$count)) { // On est entre les milieux
        // On affiche 2 avant jusque 2 après l'offset courant mais sans déborder sur les indices affichés autour
        $start=max(3,intval($offset/$count)-2);
        $end=min(intval($offset/$count)+3,intval($total/$count)-3);
        if ($start!=3) echo "<li class=\"disabled\"><a>...</a></li> ";
        for($i=$start;$i<$end;$i++) {
          $o=$i*$count;
          if ($offset==$o) {
	    echo "<li class=\"active\"><a>".$i."</a></li> "; 
          } else {
          echo "<li><a href=\"".str_replace("%%offset%%",$o,$url)."\">$i</a></li> ";
          }
        }
        if ($end!=intval($total/$count)-3) echo "<li class=\"disabled\"><a>...</a></li> ";
      } else {
	echo "<li class=\"disabled\"><a>...</a></li> ";
      }
      for($i=intval($total/$count)-3;$i<$total/$count;$i++) {
        $o=$i*$count;
        if ($offset==$o) {
	    echo "<li class=\"active\"><a>".$i."</a></li> "; 
        } else {
          echo "<li><a href=\"".str_replace("%%offset%%",$o,$url)."\">$i</a></li> ";
        }
      }

    } // PLUS de 10 Pages ? 
  }
  // Doit-on afficher Page suivante ? 
  if ($offset+$count<$total) {
    $o=$offset+$count;
    echo "<li><a href=\"".str_replace("%%offset%%",$o,$url)."\" alt=\"(Ctl/Alt-s)\" title=\"(Alt-s)\" accesskey=\"s\">&raquo;</a></li> ";
  } else {
    echo "<li class=\"disabled\"><a>&raquo;</a></li> ";
  }
  echo $after;
}



function _l($str) {
  if ($str=="Next") return "Suivant";
  if ($str=="Previous") return "Précédent";
  return $str;
}


/* Lance une requete mysql et loggue éventuellement l'erreur) */
function mq($query) {
  global $er;
  $r=@mysql_query($query);
  if (mysql_errno()) {
    // TODO : probleme lors du RAISE : il lance un "log" donc fait un mysql insert !!!
    //       echo "ERREUR MYSQL : ".mysql_error()."<br>QUERY: ".$query."<br>\n";
    //    $er->raise(1,mysql_error());
    //$er->log(ERROR_LEVEL_FPUT,"mqerr",array("query"=>$query,"ERROR"=>mysql_error()));
    echo "ERR: ".mysql_error()." <br />"; 
  } else {
    // Uncomment this to log every request : 
    //$er->log(ERROR_LEVEL_FPUT,"mqok",array("query"=>$query));
  }
  return $r;
}

/* Lance une requete mysql et loggue éventuellement l'erreur), et retourne la liste des résultats dans un tableau de tableaux associatifs */
function mqlist($query) {
  global $er;
  $r=mq($query);
  if (mysql_errno()) {
    echo "ERR: ".mysql_error()." <br />"; 
    //$er->raise(1,mysql_error()."Q:".$query);
    return false;
  }
  $res=array();
  while ($c=mysql_fetch_array($r)) {
    $res[]=$c;
  }
  return $res;
}

/* Lance une requete mysql et loggue éventuellement l'erreur), et retourne la liste des résultats dans un tableau associatif (champ unique) */
function mqlistone($query) {
  global $er;
  $r=mq($query);
  if (mysql_errno()) {
    echo "ERR: ".mysql_error()." <br />"; 
    //$er->raise(1,mysql_error()."Q:".$query);
    return false;
  }
  $res=array();
  while ($c=mysql_fetch_array($r)) {
    $res[]=$c[0];
  }
  return $res;
}

/* Lance une requete mysql et loggue éventuellement l'erreur), et retourne le résultat unique dans un tableau associatif */
function mqone($query) {
  global $er;
  $r=mq($query);
  if (mysql_errno()) {
    //$er->raise(1,mysql_error()."Q:".$query);
    echo "ERR: ".mysql_error()." <br />"; 
    return false;
  }
  return mysql_fetch_array($r);
}

/* Lance une requete mysql et loggue éventuellement l'erreur), et retourne le champ unique du résultat unique. */
function mqonefield($query) {
  global $er;
  $r=mq($query);
  if (mysql_errno()) {
    echo "ERR: ".mysql_error()." <br />"; 
    //$er->raise(1,mysql_error()."Q:".$query);
    return false;
  }
  if (list($res)=mysql_fetch_array($r)) 
    return $res;
  else 
    return false;
}
function ehe($s) {
  echo htmlentities($s,ENT_COMPAT,"UTF-8");
}

function __($s) {
  echo _($s);
}

/** Returns a hash for the crypt password hashing function.
 * as of now, we use php5.3 almost best hashing function: SHA-256 with 1000 rounds and a random 16 chars salt.
 */
function getSalt() {
  $salt = substr(str_replace('+', '.', base64_encode(pack('N4', mt_rand(), mt_rand(), mt_rand(), mt_rand()))), 0, 16);
  return '$5$rounds=1000$'.$salt.'$';
}
  
