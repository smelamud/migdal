<?php
# @(#) $Id$

require_once('lib/dataobject.php');
require_once('lib/iterator.php');
require_once('lib/complains.php');

class ComplainTypeListIterator
      extends Iterator
{
var $id;

function ComplainTypeListIterator()
{
$this->id=COMPL_NONE;
}

function next()
{
$this->id++;
return $this->id<=COMPL_MAX ? newComplain($this->id) : 0;
}

}
?>
