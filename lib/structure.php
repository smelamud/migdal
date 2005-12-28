<?php
# @(#) $Id$

require_once('grp/structure.php');

class LocationInfo
{
var $script;

function LocationInfo()
{
}

function getScript()
{
return $this->script;
}

function setScript($script)
{
$this->script=$script;
}

}
?>
