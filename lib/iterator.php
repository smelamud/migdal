<?php
# @(#) $Id$

class Iterator
{
var $first;

function Iterator()
{
$this->first=2;
}

function isFirst()
{
return $this->first!=0;
}

function next()
{
if($this->first>0)
  $this->first--;
return 0;
}

}
?>
