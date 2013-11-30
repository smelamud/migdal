<?php
# @(#) $Id$

require_once('lib/errorreporting.php');
require_once('lib/uri.php');
require_once('lib/post.php');

httpRequestString('okdir');

httpRequestInteger('value');
httpRequestInteger('pagesize');
httpRequestInteger('size');

$pagesize=empty($pagesize) ? 1 : $pagesize;
$offset=($value-1)*$pagesize;
if($offset<0)
  $offset=0;
if($offset>=$size)
  $offset=(int)(($size-1)/$pagesize)*$pagesize;
header('Location: '.remakeURI($okdir,
                              array('tid'),
			      array('offset' => $offset)));
?>
