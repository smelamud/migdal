<?php
# @(#) $Id$

require_once('lib/array.php');
require_once('lib/text-formats.php');

function getQuote($s, $width) {
    $lines = explode("\n", $s);
    $out = '';
    foreach ($lines as $line) {
        $pos = 0;
        while ($pos < strlen($line) &&
               ($line[$pos] == ' ' || $line[$pos] == '>'))
            $pos++;
        $prefix = '> '.substr($line, 0, $pos);
        $qline = wordwrap(substr($line, $pos), $width - strlen($prefix));
        $out .= preg_replace('/^/m', $prefix, $qline);
    }
    return $out;
}

$TextFormats = array(TF_PLAIN => 'Простой текст (без переносов строк)',
                     TF_TEX   => 'Простой текст (с переносами строк)',
                     TF_XML   => 'XML');

class TextFormatsIterator
        extends AssocArrayIterator {

    public function __construct() {
        global $TextFormats;

        parent::__construct($TextFormats);
    }

}
?>
