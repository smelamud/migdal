<?php
# @(#) $Id$

require_once('lib/array.php');
require_once('lib/head.php');

function createStylesheetList() {
    global $stylesheetList;

    if (!isset($stylesheetList))
        $stylesheetList = array();
}

function createStyleList() {
    global $styleList, $styleNames;

    if (!isset($styleList)) {
        $styleList = array(-1 => 'print',
                            1 => 'default');
        $styleNames = array(1 => '�� ���������');
    }
}

function declareStylesheet($ident) {
    global $stylesheetList;

    createStylesheetList();
    if (!in_array(strtolower($ident), $stylesheetList))
        $stylesheetList[] = strtolower($ident);
}

function displayStylesheets() {
    global $stylesheetList, $userStyle;

    beginHead();
    foreach ($stylesheetList as $sheet)
        echo "<link rel='stylesheet' href='/styles/$sheet-".
              getStyle($userStyle).".min.css'>\n";
    endHead();
}

function declareStyle($ident, $name) {
    global $styleList, $styleNames;

    createStyleList();
    if (!in_array(strtolower($ident), $styleList)) {
        $styleList[] = strtolower($ident);
        $styleNames[] = $name;
    }
}

function getStyle($n) {
    global $styleList;

    createStyleList();
    return $styleList[isset($styleList[$n]) ? $n : 1];
}

class StyleNamesIterator
        extends MArrayIterator {

    public function __construct() {
        global $styleNames;

        parent::__construct($styleNames);
    }

}
?>