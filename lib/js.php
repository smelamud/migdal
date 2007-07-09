<?php
# @(#) $Id: style.php 2372 2007-01-29 20:14:23Z balu $

function createJSList()
{
global $jsList;

if(!isset($jsList))
  $jsList=array();
}

function declareJS($src)
{
global $jsList;

createJSList();
if(!in_array($src,$jsList))
  $jsList[]=$src;
}
?>
