<?php
# @(#) $Id$

define('SELECT_GENERAL',0);
define('SELECT_IMAGES',1);
define('SELECT_ANSWERS',2);
define('SELECT_TOPICS',4);
define('SELECT_INFO',8);
define('SELECT_ALLPOSTING',SELECT_GENERAL|SELECT_IMAGES|SELECT_ANSWERS|
                           SELECT_TOPICS);
define('SELECT_ALLTOPIC',SELECT_GENERAL|SELECT_INFO|SELECT_TOPICS);
?>
