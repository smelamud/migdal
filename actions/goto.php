<?php
# @(#) $Id$

require_once('lib/errorreporting.php');
require_once('lib/uri.php');
require_once('lib/post.php');

$offset=($value-1)*(empty($pagesize) ? 1 : $pagesize);
if($offset<0)
  $offset=0;
if($offset>=$size)
  $offset=$size-1;
header('Location: '.remakeURI($okdir,array(),array('offset' => $offset)));
?>
