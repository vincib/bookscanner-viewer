

ALTER TABLE `books` 
  ADD `locked` INT UNSIGNED NOT NULL ,
  ADD `locktime` DATETIME NOT NULL ,
  ADD INDEX ( `locked` ),
  ADD INDEX ( `locktime` )
  ;


  