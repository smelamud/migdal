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

function reset()
{
mysql_data_seek($this->result,0);
$this->first=2;
$this->odd=0;
$this->position=-1;
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
