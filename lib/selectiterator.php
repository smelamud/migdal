<?php
# @(#) $Id$

require_once('lib/iterator.php');

class SelectIterator
      extends Iterator
{
var $result;
var $class;

function SelectIterator($aClass,$query)
{
$this->result=mysql_query($query) or die ("������ SQL � ���������: $query");
$this->class=$aClass;
}

function create($row)
{
$c=$this->class;
return new $c($row);
}

function next()
{
$row=mysql_fetch_assoc($this->result);
return $row ? $this->create($row) : 0;
}

}
?>
