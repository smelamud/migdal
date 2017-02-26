<?php
# @(#) $Id$

require_once('lib/xml.php');
require_once('lib/charsets.php');
require_once('lib/text.php');

function strpos_all($haystack, $needle, &$found) {
    if (is_array($needle)) {
        foreach ($needle as $s)
            strpos_all($haystack, $s, $found);
    } else {
        $pos = strpos($haystack, $needle);
        while ($pos !== false) {
            $found[] = $pos;
            $pos = strpos($haystack, $needle, $pos + 1);
        }
    }
    return count($found);
}

function getShortenLength($s, $len, $mdlen, $pdlen) {
    debugLog(LL_FUNCTIONS, 'getShortenLength(s=%,len=%,mdlen=%,pdlen=%)',
            array($s, $len, $mdlen, $pdlen));
    if (strlen($s) <= $len + $pdlen) {
        $return = strlen($s);
        debugLog(LL_FUNCTIONS, 'getShortenLength() returned %',
                array($return));
        return $return;
    }
    $st = $len - $mdlen;
    $st = $st < 0 ? 0 : $st;
    $c = substr($s, $st, $mdlen + $pdlen);
    $patterns = array("\x1F",
                      array('. ', '! ', '? ', ".\n", "!\n", "?\n"),
                      array(': ', ', ', '; ', ') ', ":\n", ",\n", ";\n", ")\n"));
    foreach ($patterns as $pat) {
        $matches = array();
        if (!strpos_all($c, $pat, $matches))
            continue;
        $bestpos = -1;
        foreach ($matches as $pos) {
            if ($bestpos < 0 || abs($bestpos - $mdlen) > abs($pos - $mdlen))
                $bestpos = $pos;
        }
        $matchLen = is_array($pat) ? 2 : 1;
        $return = $bestpos + $st + $matchLen;
        debugLog(LL_FUNCTIONS, 'getShortenLength() returned %', array($return));
        return $return;
    }
    debugLog(LL_FUNCTIONS, 'getShortenLength() returned %', array($len));
    return $len;
}

function hasMarkup($s) {
    for ($i = 0; $i < strlen($s); $i++) {
        if (strpos("<>&=~_^[]{}'", $s[$i]) !== false)
            return true;
        if ($s[$i] == '/' && ($i == 0 || $s[$i - 1] == ' ' || $s[$i - 1] == ':'))
            return true;
    }
    return false;
}

function cleanLength($s) {
    if (!hasMarkup($s))
        return strlen($s);
    $xml = new MTextToLineXML();
    $xml->parse(convertToXMLText($s));
    $xml->free();
    return strlen($xml->getLine());
}

function shortenUniversal($s, $len, $mdlen, $pdlen, $clearTags = false,
                          $suffix = '') {
    debugLog(LL_FUNCTIONS,
             'shortenUniversal(s=%,len=%,mdlen=%,pdlen=%,clearTags=%,suffix=%)',
             array($s, $len, $mdlen, $pdlen, $clearTags, $suffix));
    $hasMarkup = hasMarkup($s);
    if ($hasMarkup) {
        $xml = new MTextToLineXML();
        $xmlText = convertToXMLText($s);
        debugLog(LL_DETAILS, 'convertToXMLText(s)=%', array($xmlText));
        $xml->parse($xmlText);
        $xml->free();
        $line = $xml->getLine();
    } else {
        $line = $s;
    }
    $n = getShortenLength($line, $len, $mdlen, $pdlen);
    $c = $n >= strlen($line) ? '' : $suffix;
    if ($hasMarkup) {
        $xml1 = new MTextShortenXML($n, $clearTags);
        $xml1->parse(convertToXMLText($s));
        $xml1->free();
        $return = $xml1->getShort().$c;
    } else {
        $return = substr($s, 0, $n).$c;
    }
    debugLog(LL_FUNCTIONS, 'shortenUniversal() returned %', array($return));
    return delicateAmps($return);
}

function shorten($s, $len, $mdlen, $pdlen) {
    return shortenUniversal($s, $len, $mdlen, $pdlen);
}

function shortenNote($s, $len, $mdlen, $pdlen) {
    return shortenUniversal($s, $len, $mdlen, $pdlen, true, '...');
}
?>
