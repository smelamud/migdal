<?php
# @(#) $Id$

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
