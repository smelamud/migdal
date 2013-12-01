<?php
# @(#) $Id$

require_once('lib/database.php');

function reload($href) {
    header("Location: $href");
    dbClose();
    exit;
}

function displayImage($src = '', $alt = '', $title = '', $class = '',
                      $id = '', $data_id = '', $data_value = '') {
    static $sizes = array();

    $s = '<img';
    if ($src != '') {
        $s .= " src='$src' ";
        if (isset($sizes[$src]))
            $s .= $sizes[$src];
        else {
            $is = getImageSize("./$src");
            $s .= $is[3];
            $sizes[$src] = $is[3];
        }
    }
    $s .= " alt='$alt'";
    if ($title != '')
        $s .= " title='$title'";
    if ($class != '')
        $s .= " class='$class'";
    if ($id != '')
        $s .= " id='$id'";
    if ($data_id != '')
        $s .= " data-id='$data_id'";
    if ($data_value != '')
        $s .= " data-value='$data_value'";
    echo "$s>";
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

function displayCheckbox_button($name, $value = 1, $checked = false, $id = '',
                                $class = '') {
    $s = '';
    if ($checked)
        $s .= " checked";
    if ($id != '')
        $s .= " id='$id'";
    if ($class != '')
        $s .= " class='$class'";
    echo "<input type='checkbox' name='$name' value='$value'$s>";
}

function displayRadio_button($name, $value, $checked = false, $id = '',
                             $class = '', $onclick = '') {
    $s = '';
    if ($checked)
        $s .= " checked";
    if ($id != '')
        $s .= " id='$id'";
    if ($class != '')
        $s .= " class='$class'";
    if ($onclick != '')
        $s .= " onclick='$onclick'";
    echo "<input type='radio' name='$name' value='$value'$s>";
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
