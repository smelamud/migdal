<?php
# @(#) $Id$

require_once('lib/iterator.php');

class SelectIterator
      extends Iterator
{
var $result;
var $count;
var $class;

function SelectIterator($aClass,$query)
{
$this->Iterator();
$this->result=mysql_query($query)
       or die ("Ошибка SQL в итераторе: $query: ".mysql_error());
$this->count=mysql_num_rows($this->result);
$this->class=$aClass;
}

function create($row)
{
$c=$this->class;
return new $c($row);
}

function next()
{
Iterator::next();
$row=mysql_fetch_assoc($this->result);
return $row ? $this->create($row) : 0;
}

function isLast()
{
return $this->getPosition()>=$this->count-1;
}

function getCount()
{
return $this->count;
}

function getResult()
{
return $this->result;
}

}
?>
