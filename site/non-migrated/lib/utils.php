<?php
# @(#) $Id$

require_once('lib/database.php');

function reload($href) {
    header("Location: $href");
    dbClose();
    exit;
}

function displayTdspan($span = 0, $class = '', $align = '', $width = '',
                       $height = '') {
    $s = '';
    if ($span > 1)
        $s .= " colspan=$span";
    if ($class != '')
        $s .= " class='$class'";
    if ($align != '')
        $s .= " align='$align'";
    if ($width != '')
        $s .= " width='$width'";
    if ($height != '')
        $s .= " height='$height'";
    echo "<td$s>";
}

function disjunct($values) {
    if (!is_array($values))
        return $values;
    $sum = 0;
    foreach ($values as $value)
        $sum |= $value;
    return $sum;
}
?>
