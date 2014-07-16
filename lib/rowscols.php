<?php
# @(#) $Id$

require_once('lib/iterator.php');

class RowsIterator
        extends MForwardIterator {

    private $cols;

    public function __construct(MIterator $iterator, $cols) {
        parent::__construct($iterator);
        $this->cols = $cols;
    }

    public function isBol() {
        return $this->iterator->getPosition() % $this->cols == 0;
    }

    public function isEol() {
        return $this->iterator->getPosition() % $this->cols == $this->cols - 1;
    }

}

class FixedRowsIterator
        extends RowsIterator {

    public function __construct(MIterator $iterator, $rows, $minCols) {
        $cols = ceil($iterator->getCount() / $rows);
        $cols = $cols < $minCols ? $minCols : $cols;
        parent::__construct($iterator, $cols);
    }

}
?>
