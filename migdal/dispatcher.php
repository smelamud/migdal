<?php
# @(#) $Id$

require_once('lib/structure.php');

$info=getLocationInfo($_SERVER['REQUEST_URI']);
$ScriptName=$info->getScript();
if($ScriptName!='')
  include($ScriptName);
else
  include('404.php');
?>
