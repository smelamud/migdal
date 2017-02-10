<?php
# @(#) $Id$

interface Pageable {

    public function getSize();
    public function getLimit();
    public function getOffset();
    public function getPrevOffset();
    public function getNextOffset();
    public function getBeginValue();
    public function getEndValue();
    public function getPage();
    public function getPageCount();

}

trait AbstractPager {

    private $limit;
    private $offset;

    public abstract function getCount();

    public abstract function getSize();

    public function getLimit() {
        return $this->limit;
    }

    public function getOffset() {
        return $this->offset;
    }

    public function getPrevOffset() {
        $n = $this->offset - $this->limit;
        return $n < 0 ? 0 : $n;
    }

    public function getNextOffset() {
        return $this->offset + $this->limit;
    }

    public function getBeginValue() {
        return $this->offset + 1;
    }

    public function getEndValue() {
        return $this->offset + $this->getCount();
    }

    public function getPage() {
        return (int)($this->offset / $this->limit) + 1;
    }

    public function getPageCount() {
        $size = $this->getSize();
        return $size == 0 ? 0 : (int)(($size - 1) / $this->limit) + 1;
    }

}

class Pager
        implements Pageable {

    use AbstractPager;

    private $size;

    public function __construct($size, $limit, $offset) {
        $this->size = $size;
        $this->limit = $limit;
        $this->offset = $offset;
    }

    public function getCount() {
        if ($this->getNextOffset() < $this->getSize())
            return $this->limit;
        else
            return $this->getSize() - $this->offset;
    }

    public function getSize() {
        return $this->size;
    }

}
?>
