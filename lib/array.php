<?php
# @(#) $Id$

require_once('lib/iterator.php');

class MArrayIterator
      extends MIterator
{
var $vars;

function MArrayIterator($vars)
{
parent::MIterator();
$this->vars=$vars;
reset($this->vars);
}

function create($key,$value)
{
return $value;
}

function getNext()
{
parent::getNext();
$key=key($this->vars);
$val=current($this->vars);
next($this->vars);
return $val ? $this->create($key,$val) : $val;
}

function getCount()
{
return count($this->vars);
}

function isLast()
{
return !(boolean)current($this->vars);
}

}

class SortedArrayIterator
      extends MArrayIterator
{

function SortedArrayIterator($vars)
{
sort($vars);
$vars=array_unique($vars);
parent::MArrayIterator($vars);
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
      extends MArrayIterator
{
var $class;

function AssocArrayIterator($vars,$class='Association')
{
parent::MArrayIterator($vars);
$this->class=$class;
}

function create($key,$value)
{
return new $this->class($key,$value);
}

}
?>
