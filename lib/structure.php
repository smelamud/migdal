<?php
# @(#) $Id$

require_once('grp/structure.php');

class LocationInfo
{
var $script;
var $args = array();

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

function getArgs()
{
return $this->args;
}

function setArgs($args)
{
$this->args=$args;
}

}
?>
