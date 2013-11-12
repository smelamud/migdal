<?php
# @(#) $Id: js.php 2580 2007-09-10 16:09:04Z balu $

function createOGImageList()
{
global $ogImageList;

if(!isset($ogImageList))
  $ogImageList=array();
}

function declareOGImage($src)
{
global $ogImageList,$userDomain,$siteDomain;

createOGImageList();
if(!in_array($src,$ogImageList))
  $ogImageList[]="http://$userDomain.$siteDomain$src";
}
?>
