<?php
# @(#) $Id$

abstract class MIterator implements Iterator {

    protected $iPosition;
    protected $iCurrent;

    public function __construct() {
        $this->iPosition = -1;
        $this->iCurrent = 0;
    }

    public function isFirst() {
        return $this->iPosition <= 0;
    }

    public function isOdd() {
        return $this->iPosition < 0 || $this->iPosition % 2 == 0;
    }

    public function getNext() {
        $this->iPosition++;
        return 0;
    }

    public function getPosition() {
        return $this->iPosition;
    }

    public function getNextPosition() {
        return $this->iPosition + 1;
    }

    public function current() {
        return $this->iCurrent;
    }

    public function key() {
        return $this->getPosition();
    }

    public function next() {
        $this->iCurrent = $this->getNext();
    }

    public function rewind() {
        $this->iPosition = -1;
        $this->iCurrent = $this->getNext();
    }

    public function valid() {
        return !($this->iCurrent);
    }

}
?>
