<?php
# @(#) $Id$

class Iterator
{
var $first;
var $position;

function Iterator()
{
$this->first=2;
$this->position=-1;
}

function isFirst()
{
return $this->first!=0;
}

function next()
{
if($this->first>0)
  $this->first--;
$this->position++;
return 0;
}

function getPosition()
{
return $this->position;
}

}
?>
