<?php
# @(#) $Id$

class MIterator
{
var $first;
var $odd;
var $position;

function __construct()
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

function getNext()
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

function getNextPosition()
{
return $this->position+1;
}

}
?>
