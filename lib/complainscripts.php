<?php
# @(#) $Id$

require_once('lib/bug.php');
require_once('lib/sql.php');

define('CSCR_NONE',0x0000);
define('CSCR_CLOSE',0x0001);
define('CSCR_OPEN',0x0002);
define('CSCR_ALL',0x0003);

$cscrProcNames = array(CSCR_CLOSE => 'cscrClose',
		               CSCR_OPEN  => 'cscrOpen');

$cscrTitles = array(CSCR_CLOSE => 'Закрыть жалобу',
		            CSCR_OPEN  => 'Возобновить жалобу');

function cscrClose($complain) {
    closeComplain($complain->getId());
}

function cscrOpen($complain) {
    openComplain($complain->getId());
}

class ComplainScript {

    private $id;

    public function __construct($id) {
        $this->id = $id;
    }

    public function exec($complain) {
        global $cscrProcNames;

        if (isset($cscrProcNames[$this->id])) {
            $proc = $cscrProcNames[$this->id];
            $proc($complain);
        }
    }

    public function getId() {
        return $this->id;
    }

    public function getTitle() {
        global $cscrTitles;

        return isset($cscrTitles[$this->id]) ? $cscrTitles[$this->id] : '';
    }

}

class ComplainScriptListIterator
        extends MIterator {

    private $id;
    private $mask;

    public function __construct($mask = CSCR_ALL) {
        parent::__construct();
        $this->mask = $mask;
    }

    public function current() {
        $script=new ComplainScript($this->id);
    }

    public function next() {
        while (($this->id & $this->mask) == 0 && $this->id <= CSCR_ALL)
            $this->id *= 2;
    }

    public function rewind() {
        $this->id = 1;
    }

    public function valid() {
        return $this->id <= CSCR_ALL;
    }

}

function getComplainScriptById($id) {
    return new ComplainScript($id);
}
?>
