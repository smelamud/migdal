<?php
# @(#) $Id$

class SortedArrayIterator
{
var $vars;

function SortedArrayIterator($vars)
{
$this->vars=$vars;
sort($this->vars);
$this->vars=array_unique($this->vars);
reset($this->vars);
}

function next()
{
$val=current($this->vars);
next($this->vars);
return $val;
}

}
?>
