<?php
# @(#) $Id$

require_once('lib/bug.php');

class StrSeq {

    private $start;
    private $length;

    public function __construct($start, $length = 0) {
        $this->start = $start;
        $this->length = $length;
    }

    public function getStart() {
        return $this->start;
    }

    public function getLength() {
        return $this->length;
    }

    public function setLength($length) {
        $this->length = $length;
    }

}

class Outer {

    private $start;
    private $commons;
    private $inners;

    public function __construct($start) {
        $this->start = $start;
        $this->commons = array();
        $this->inners = array();
    }

    public function getStart() {
        return $this->start;
    }

    private static function getConcat(array $seqs) {
        $s = ob_get_contents();
        $c = '';
        foreach($seqs as $seq)
            $c .= substr($s, $seq->getStart(), $seq->getLength());
        return $c;
    }

    public function isInnersEmpty() {
        return trim(self::getConcat($this->inners)) == '';
    }

    public function getCommons() {
        return self::getConcat($this->commons);
    }

    public function addCommon($start) {
        array_push($this->commons, new StrSeq($start));
    }

    public function closeCommon($end) {
        if (count($this->commons) == 0)
            return false;
        $seq = array_pop($this->commons);
        if ($seq->getLength() != 0)
            return false;
        $seq->setLength($end - $seq->getStart());
        array_push($this->commons, $seq);
        return true;
    }

    public function addInner($start) {
        array_push($this->inners, new StrSeq($start));
    }

    public function closeInner($end) {
        if (count($this->inners) == 0)
            return false;
        $seq = array_pop($this->inners);
        if ($seq->getLength() != 0)
            return false;
        $seq->setLength($end - $seq->getStart());
        array_push($this->inners, $seq);
        return true;
    }

}

$Outers = array();

function beginOuter() {
    global $Outers;

    $Outers[] = new Outer(ob_get_length());
}

function endOuter() {
    global $Outers;

    if (count($Outers) == 0)
        bug('&lt;/outer&gt; without corresponding &lt;outer&gt;.');
    $outer = array_pop($Outers);
    if ($outer->isInnersEmpty()) {
        $s = substr_replace(ob_get_contents(), $outer->getCommons(),
                            $outer->getStart());
        ob_clean();
        echo $s;
    }
}

function beginCommon() {
    global $Outers;

    if (count($Outers) == 0)
        bug('&lt;common&gt; outside &lt;outer&gt;.');
    $outer = array_pop($Outers);
    $outer->addCommon(ob_get_length());
    array_push($Outers, $outer);
}

function endCommon() {
    global $Outers;

    if (count($Outers) == 0)
        bug('&lt;/common&gt; outside &lt;outer&gt;.');
    $outer = array_pop($Outers);
    if (!$outer->closeCommon(ob_get_length()))
        bug('&lt;/common&gt; without corresponding &lt;common&gt;.');
    array_push($Outers, $outer);
}

function beginInner() {
    global $Outers;

    if (count($Outers) == 0)
        bug('&lt;inner&gt; outside &lt;outer&gt;.');
    $outer = array_pop($Outers);
    $outer->addInner(ob_get_length());
    array_push($Outers, $outer);
}

function endInner() {
    global $Outers;

    if (count($Outers) == 0)
        bug('&lt;/inner&gt; outside &lt;outer&gt;.');
    $outer = array_pop($Outers);
    if (!$outer->closeInner(ob_get_length()))
        bug('&lt;/inner&gt; without corresponding &lt;inner&gt;.');
    array_push($Outers, $outer);
}
?>
