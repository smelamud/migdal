<?php
# @(#) $Id$

require_once('lib/iterator.php');

class RowsIterator
      extends Iterator
{
var $iterator;
var $cols;

function RowsIterator($iterator,$cols)
{
$this->iterator=$iterator;
$this->cols=$cols;
}

function isEol()
{
return ($this->iterator->getPosition() % $this->cols)==$this->cols-1;
}

function next()
{
return $this->iterator->next();
}

}

?>
