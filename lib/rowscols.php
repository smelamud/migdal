<?php
# @(#) $Id$

require_once('lib/iterator.php');

class RowsIterator
      extends MIterator
{
var $iterator;
var $cols;

function RowsIterator($iterator,$cols)
{
parent::MIterator();
$this->iterator=$iterator;
$this->cols=$cols;
}

function isEol()
{
return ($this->iterator->getPosition() % $this->cols)==$this->cols-1;
}

function next()
{
parent::next();
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
parent::RowsIterator($iterator,$cols);
}

}
?>
