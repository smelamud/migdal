<?php
# @(#) $Id$

require_once('lib/selectiterator.php');
require_once('lib/bug.php');
require_once('lib/sql.php');

class LimitSelectIterator
        extends SelectIterator {

    private $sizeQuery;
    private $size;
    private $limit;
    private $offset;

    public function __construct($aClass, $query, $limit = 10, $offset = 0,
                                $squery = '') {
        if ($squery == '') {
            preg_match('/^(.*select)(.*)(from.*)$/is', $query, $parts);
            $cquery = $parts[1].' count(*) '.$parts[3];
        }
        $this->sizeQuery = $squery;
        $this->size = -1;
        $this->limit = $limit;
        $this->offset = $offset;
        parent::__construct($aClass,
                            $limit == 0 ? $query
                                        : "$query limit $offset,$limit");
    }

    protected function sizeSelect() {
        $METHOD = get_method($this, 'sizeSelect');
        if ($this->size < 0) {
            $result = sql($this->sizeQuery,
                          $METHOD);
            $this->size = mysql_result($result, 0, 0);
        }
    }

    public function getSizeQuery() {
        return $this->sizeQuery;
    }

    public function setSizeQuery($squery) {
        $this->sizeQuery = $squery;
    }

    public function getSize() {
        $this->sizeSelect();
        return $this->size;
    }

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
        $this->sizeSelect();
        return $this->size == 0 ? 0
                                : (int)(($this->size - 1) / $this->limit) + 1;
    }

}
?>
