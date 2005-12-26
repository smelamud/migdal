<?php
# @(#) $Id$

require_once('lib/iterator.php');

class ArrayIterator
      extends Iterator
{
var $vars;

function ArrayIterator($vars)
{
parent::Iterator();
$this->vars=$vars;
reset($this->vars);
}

function next()
{
parent::next();
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
parent::ArrayIterator($vars);
}

}
?>
