<?php
# @(#) $Id$

class Iterator
{
var $first;
var $odd;
var $position;

function Iterator()
{
$this->first=2;
$this->odd=0;
$this->position=-1;
}

function isFirst()
{
return $this->first!=0;
}

function isOdd()
{
return $this->odd!=0;
}

function next()
{
if($this->first>0)
  $this->first--;
$this->odd=1-$this->odd;
$this->position++;
return 0;
}

function getPosition()
{
return $this->position;
}

}
?>
