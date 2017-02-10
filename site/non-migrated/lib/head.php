<?php
$headBuffer = '';
$inHeadLevel = 0;

function beginHead() {
    global $inHeadLevel;

    $inHeadLevel++;
    if ($inHeadLevel > 0) {
        ob_start();
    }
}

function endHead() {
    global $inHeadLevel, $headBuffer;

    $inHeadLevel--;
    if ($inHeadLevel == 0) {
        $headBuffer .= ob_get_clean();
    }
}

function getHead() {
    global $headBuffer;

    return $headBuffer;
}
?>
