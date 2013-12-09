<?php
# @(#) $Id$

require_once('lib/charsets.php');

function isListArray($data) {
    if (!is_array($data))
        return false;
    $prev = -1;
    foreach ($data as $key => $value)
        if (!is_int($key) || $key != $prev + 1)
            return false;
        else
            $prev = $key;
    return true;
}

function jsonEncode($data) {
/* TODO Will be able to use json_encode() here after complete switch to
        Unicode for internal strings.
    return json_encode($data, JSON_FORCE_OBJECT | JSON_UNESCAPED_UNICODE);*/
    if (is_null($data))
        return 'null';
    if (is_bool($data))
        return $data ? 'true' : 'false';
    if (is_int($data))
        return (string) $data;
    if (!is_array($data))
        return '"'.str_replace('"', '\"', $data).'"';
    if(isListArray($data)) {
        $s = '[';
        $first = true;
        foreach ($data as $val) {
            if (!$first)
                $s .= ', ';
            $first = false;
            $s .= jsonEncode($data);
        }
        $s .= ']';
        return $s;
    }
    $s = '{';
    $first = true;
    foreach ($data as $key => $val) {
        if (!$first)
            $s .= ', ';
        $first = false;
        $s .= "\"$key\": ".jsonEncode($val);
    }
    $s .= '}';
    return $s;
}

function jsonOutput($data) {
    echo convertOutput(jsonEncode($data));
}
?>
