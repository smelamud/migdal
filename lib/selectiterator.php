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
$this->result=mysql_query($query) or die ("Query failed: $query");
$this->class=$aClass;
}

function next()
{
$row=mysql_fetch_assoc($this->result);
$c=$this->class;
return $row ? new $c($row) : 0;
}

}
?>
