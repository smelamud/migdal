<?php
# @(#) $Id$

require_once('lib/iterator.php');
require_once('lib/bug.php');

class SelectIterator
        extends MIterator
        implements Countable {

    use CountableIterator;

    private $query;
    private $result;
    private $rowCount;
    private $class;
    private $current;

    public function __construct($aClass, $query) {
        parent::__construct();
        $this->query = $query;
        $this->result = 0;
        $this->class = $aClass;
        $this->current = 0;
    }

    protected function select() {
        if ($this->result != 0)
            return;
        $METHOD = get_method($this, 'select');
        $this->result = sql($this->query,
                            $METHOD);
        $this->rowCount = mysql_num_rows($this->result);
    }

    protected function fetch() {
        $row = mysql_fetch_assoc($this->result);
        return $row ? $this->create($row) : 0;
    }

    protected function create($row) {
        $c = $this->class;
        return new $c($row);
    }

    public function current() {
        return $this->current;
    }

    public function next() {
        parent::next();
        $this->current = $this->fetch();
    }

    public function rewind() {
        parent::rewind();
        $this->select();
        if ($this->rowCount > 0)
            mysql_data_seek($this->result, 0);
        $this->current = $this->fetch();
    }

    // DEPRECATED alias of rewind()
    public function reset() {
        $this->rewind();
    }

    public function valid() {
        return (boolean) $this->current;
    }

    public function getQuery() {
        return $this->query;
    }

    public function setQuery($query) {
        $this->query=$query;
    }

    public function count() {
        $this->select();
        return $this->rowCount;
    }

    protected function getResult() {
        $this->select();
        return $this->result;
    }

}

class ReverseSelectIterator
        extends SelectIterator {

    private $index;
    private $reverse;

    public function __construct($class, $query, $reverse = true) {
        parent::__construct($class, $query);
        $this->reverse = $reverse;
    }

    public function rewind() {
        parent::rewind();
        if ($this->reverse)
            $this->index = $this->getCount() - 1;
    }

    protected function fetch() {
        if ($this->reverse)
            if ($this->index < 0)
                return 0;
            else
                mysql_data_seek($this->getResult(), $this->index--);
        return parent::fetch();
    }

}
?>
