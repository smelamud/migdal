<?php
# @(#) $Id$

require_once('lib/iterator.php');

class ArrayIterator
      extends Iterator
{
var $vars;

function ArrayIterator($vars)
{
$this->Iterator();
$this->vars=$vars;
reset($this->vars);
}

function getCurrent()
{
}

function next()
{
Iterator::next();
$val=current($this->vars);
next($this->vars);
return $val;
}

function getCount()
{
return count($this->vars);
}

}

class SortedArrayIterator
      extends ArrayIterator
{

function SortedArrayIterator($vars)
{
sort($vars);
$vars=array_unique($vars);
$this->ArrayIterator($vars);
}

}
?>
