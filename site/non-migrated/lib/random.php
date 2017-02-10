<?php
# @(#) $Id$

require_once('lib/array.php');

function random($min, $max) {
    return mt_rand($min, $max);
}

function array_random($array) {
    return $array[random(0, count($array) - 1)];
}

class RandomSequenceIterator
        extends MArrayIterator {

    public function __construct($n, $min, $max) {
        $seq = array();
        while(count($seq) < $n && count($seq) < $max - $min + 1) {
            $k = random($min, $max);
            if (!in_array($k, $seq))
                $seq[]=$k;
        }
        parent::__construct($seq);
    }

}
?>
