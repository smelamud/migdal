<?php
# @(#) $Id$

require_once('lib/iterator.php');
require_once('lib/bug.php');

class SelectIterator
      extends Iterator
{
var $query;
var $result;
var $count;
var $class;

function SelectIterator($aClass,$query)
{
$this->Iterator();
$this->query=$query;
$this->result=0;
$this->class=$aClass;
}

function select()
{
if($this->result!=0)
  return;
$this->result=sql($this->query,
                  get_method($this,'select'));
$this->count=mysql_num_rows($this->result);
}

function create($row)
{
$c=$this->class;
return new $c($row);
}

function next()
{
Iterator::next();
$this->select();
$row=mysql_fetch_assoc($this->result);
return $row ? $this->create($row) : 0;
}

function reset()
{
$this->select();
mysql_data_seek($this->result,0);
$this->first=2;
$this->odd=0;
$this->position=-1;
}

function isLast()
{
$this->select();
return $this->getPosition()>=$this->count-1;
}

function getQuery()
{
return $this->query;
}

function setQuery($query)
{
$this->query=$query;
}

function getCount()
{
$this->select();
return $this->count;
}

function getResult()
{
$this->select();
return $this->result;
}

}

class ReverseSelectIterator
{
var $index;

function ReverseSelectIterator($class,$query,$reverse=true)
{
$this->SelectIterator($class,$query);
if($reverse)
  $this->index=$this->getCount()-1;
}

function next()
{
if($reverse)
  if($this->index<0)
    return 0;
  else
    {
    mysql_data_seek($this->getResult(),$this->index--);
    return SelectIterator::next();
    }
else
  return SelectIterator::next();
}

}
?>
