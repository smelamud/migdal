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
$this->Iterator();
$this->iterator=$iterator;
$this->cols=$cols;
}

function isEol()
{
return ($this->iterator->getPosition() % $this->cols)==$this->cols-1;
}

function next()
{
Iterator::next();
return $this->iterator->next();
}

}

class FixedRowsIterator
      extends RowsIterator
{

function FixedRowsIterator($iterator,$rows,$minCols)
{
$cols=ceil($iterator->getCount()/$rows);
$cols=$cols<$minCols ? $minCols : $cols;
$this->RowsIterator($iterator,$cols);
}

}

?>
