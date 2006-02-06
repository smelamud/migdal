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

function create($key,$value)
{
return $value;
}

function next()
{
parent::next();
$key=key($this->vars);
$val=current($this->vars);
next($this->vars);
return $val ? $this->create($key,$val) : $val;
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

class Association
{
var $name;
var $value;

function Association($name,$value)
{
$this->name=$name;
$this->value=$value;
}

function getName()
{
return $this->name;
}

function getValue()
{
return $this->value;
}

}

class AssocArrayIterator
      extends ArrayIterator
{
var $class;

function AssocArrayIterator($vars,$class='Association')
{
parent::ArrayIterator($vars);
$this->class=$class;
}

function create($key,$value)
{
return new $this->class($key,$value);
}

}
?>
