<?php
# @(#) $Id$

require_once('lib/iterator.php');

class RowsIterator
      extends MIterator
{
var $iterator;
var $cols;

function __construct($iterator,$cols)
{
parent::__construct();
$this->iterator=$iterator;
$this->cols=$cols;
}

function isEol()
{
return ($this->iterator->getPosition() % $this->cols)==$this->cols-1;
}

function getNext()
{
parent::getNext();
return $this->iterator->getNext();
}

}

class FixedRowsIterator
      extends RowsIterator
{

function __construct($iterator,$rows,$minCols)
{
$cols=ceil($iterator->getCount()/$rows);
$cols=$cols<$minCols ? $minCols : $cols;
parent::__construct($iterator,$cols);
}

}
?>
