<?php
# @(#) $Id$

require_once('lib/charsets.php');

const DSC_NO_GT = 0;
const DSC_GT = 1;
const DSC_NO_SQ = 0;
const DSC_SQ = 2;

function delicateSpecialChars($s, $gt = DSC_NO_GT) {
    $specials = array('<' => '&lt;',
                      '"' => '&quot;');
    if (($gt & DSC_GT) != 0)
        $specials['>']='&gt;';
    if (($gt & DSC_SQ) != 0)
        $specials["'"]='&#39;';
    return strtr($s,$specials);
}

function delicateAmps($s, $xmlEntities = true) {
    $entities = $xmlEntities ? 'lt|amp|quot' : '[A-Za-z]+';
    $c = '';
    for ($i = 0; $i < strlen($s); $i++) {
        switch ($s{$i}) {
            case '&':
                if (preg_match("/^&(?:#[0-9]{1,5}|#x[0-9A-Fa-f]{1,4}|$entities);/",
                               substr($s, $i)))
                    $c .= '&';
                else
                    $c .= '&amp;';
                break;

            default:
                $c .= $s{$i};
        }
    }
    return $c;
}

function makeTag($name, $attrs = array(), $empty = false) {
    $s = '<'.strtolower($name);
    foreach ($attrs as $key => $value) {
        $key = strtolower($key);
        $value = delicateSpecialChars(convertFromXMLText($value));
        $s .= " $key=\"$value\"";
    }
    $s .= $empty ? ' />' : '>';
    return $s;
}

function makeText($text) {
    return delicateSpecialChars(convertFromXMLText($text), DSC_GT);
}
