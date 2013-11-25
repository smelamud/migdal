<?php
# @(#) $Id$

require_once('lib/iterator.php');

class MArrayIterator
        extends MForwardIterator
        implements Countable {

    use CountableIterator;

    public function __construct(array $vars) {
        parent::__construct(new ArrayIterator($vars));
    }

    protected function create($key, $value) {
        return $value;
    }

    public function current() {
        return $this->create($this->iterator->key(),
                             $this->iterator->current());
    }

    public function count() {
        return $this->iterator->count();
    }

}

class SortedArrayIterator
        extends MArrayIterator {

    public function __construct(array $vars) {
        sort($vars);
        $vars = array_unique($vars);
        parent::__construct($vars);
    }

}

class Association {

    private $name;
    private $value;

    public function __construct($name, $value) {
        $this->name = $name;
        $this->value = $value;
    }

    public function getName() {
        return $this->name;
    }

    public function getValue() {
        return $this->value;
    }

}

class AssocArrayIterator
        extends MArrayIterator {

    private $class;

    public function __construct(array $vars, $class = 'Association') {
        parent::__construct($vars);
        $this->class = $class;
    }

    protected function create($key, $value) {
        return new $this->class($key, $value);
    }

}
?>
