<?php
# @(#) $Id$

abstract class MIterator
        implements Iterator {

    private $position;

    public function __construct() {
        $this->position = -1;
    }

    public function isFirst() {
        return $this->position <= 0;
    }

    public function isOdd() {
        return $this->position < 0 || $this->position % 2 == 0;
    }

    public function getPosition() {
        return $this->position;
    }

    public function getNextPosition() {
        return $this->position + 1;
    }

    public abstract function current();

    public function key() {
        return $this->getPosition();
    }

    public function next() {
        $this->position++;
    }

    public function rewind() {
        $this->position = 0;
    }

    public abstract function valid();

}

trait CountableIterator {

    public abstract function count();

    public function getCount() {
        return $this->count();
    }

    public function isLast() {
        return $this->getPosition() >= $this->getCount() - 1;
    }

}

class MForwardIterator
        extends MIterator {

    protected $iterator;

    public function __construct(Iterator $iterator) {
        parent::__construct();
        $this->iterator = $iterator;
    }

    public function current() {
        return $this->iterator->current();
    }

    public function next() {
        parent::next();
        $this->iterator->next();
    }

    public function rewind() {
        parent::rewind();
        $this->iterator->rewind();
    }

    public function valid() {
        return $this->iterator->valid();
    }

}
?>
