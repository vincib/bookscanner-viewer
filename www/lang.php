<?php
/*
    Prosody Account Manager
    Copyright (C) 2014 Benjamin Sonntag <benjamin@sonntag.fr>, SKhaen <skhaen@cyphercat.eu>   

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU Affero General Public License as
    published by the Free Software Foundation, either version 3 of the
    License, or (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU Affero General Public License for more details.

    You should have received a copy of the GNU Affero General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.

    You can find the source code of this software at https://github.com/LaQuadratureDuNet/JabberService
 */

$languages = array("fr" => "fr_FR",
		   "en" => "en_US",
		   /*
		   "es" => "es_ES",
		   "de" => "de_DE",
		   "it" => "it_IT",
		   */
		   );

$lang = "en_US";
$lang_short = "en";
bindtextdomain("messages", dirname(__FILE__)."/locales");

if (!empty($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
  $lang_short  = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
  if (isset($languages[$lang_short])) {
    $lang=$languages[$lang_short];
  }
}

putenv("LC_MESSAGES=".$lang);
putenv("LANG=".$lang);
putenv("LANGUAGE=".$lang);
setlocale(LC_ALL,$lang);
textdomain("messages");
$charset = "UTF-8";
bind_textdomain_codeset("messages",$charset);
