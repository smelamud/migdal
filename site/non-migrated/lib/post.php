<?php
# @(#) $Id$

require_once('lib/charsets.php');
require_once('lib/ident.php');
require_once('lib/users.php');

$Args = array();

function httpValue($name, $value) {
    global $Args;

    $Args[$name] = $value;
    $GLOBALS[$name] = $value;
}

function postProcessInteger($value) {
    return (int)$value;
}

function httpInteger(array $httpVars, $name) {
    global $Args;

    if (!isset($httpVars[$name]) && isset($Args[$name]))
      return;
    httpValue($name,
              postProcessInteger(isset($httpVars[$name])
                                 ? $httpVars[$name]
                                 : 0));
}

function httpGetInteger($name) {
    return httpInteger($_GET, $name);
}

function httpPostInteger($name) {
    return httpInteger($_POST, $name);
}

function httpRequestInteger($name) {
    return httpInteger($_REQUEST, $name);
}

function postProcessIntegerArray($value) {
    $result = array();
    if (!is_array($value))
        $result[] = (int)$value;
    else
        foreach ($value as $key => $var)
            $result[$key] = (int)$var;
    return $result;
}

function httpIntegerArray(array $httpVars, $name) {
    global $Args;

    if (!isset($httpVars[$name]) && isset($Args[$name]))
        return;
    httpValue($name,
              postProcessIntegerArray(isset($httpVars[$name])
                                      ? $httpVars[$name]
                                      : array()));
}

function httpGetIntegerArray($name) {
    return httpIntegerArray($_GET, $name);
}

function httpPostIntegerArray($name) {
    return httpIntegerArray($_POST, $name);
}

function httpRequestIntegerArray($name) {
    return httpIntegerArray($_REQUEST, $name);
}

function postProcessIntegerArray2D($value) {
    $result = array();
    if (!is_array($value))
        $result[] = array((int)$value);
    else
        foreach($value as $key => $var)
            $result[$key] = postProcessIntegerArray($var);
    return $result;
}

function httpIntegerArray2D(array $httpVars, $name) {
    global $Args;

    if (!isset($httpVars[$name]) && isset($Args[$name]))
        return;
    httpValue($name,
              postProcessIntegerArray2D(isset($httpVars[$name])
                                        ? $httpVars[$name]
                                        : array()));
}

function httpGetIntegerArray2D($name) {
    return httpIntegerArray2D($_GET, $name);
}

function httpPostIntegerArray2D($name) {
    return httpIntegerArray2D($_POST, $name);
}

function httpRequestIntegerArray2D($name) {
    return httpIntegerArray2D($_REQUEST, $name);
}

function postProcessIdent($value, $table = 'entries') {
    return idByIdent($value, $table);
}

function httpIdent(array $httpVars, $name, $table = 'entries') {
    global $Args;

    if (!isset($httpVars[$name]) && isset($Args[$name]))
        return;
    httpValue($name,
              postProcessIdent(isset($httpVars[$name])
                               ? $httpVars[$name]
                               : 0,
                               $table));
}

function httpGetIdent($name, $table = 'entries') {
    return httpIdent($_GET, $name);
}

function httpPostIdent($name, $table = 'entries') {
    return httpIdent($_POST, $name);
}

function httpRequestIdent($name, $table = 'entries') {
    return httpIdent($_REQUEST, $name);
}

function postProcessString($value) {
    return $value;
}

function httpString(array $httpVars, $name, $convert = true) {
    global $Args;

    if (!isset($httpVars[$name]) && !isset($httpVars["${name}_i"])
        && isset($Args[$name]))
        return;
    if (isset($httpVars["${name}_i"]))
        $value = tmpTextRestore($httpVars["${name}_i"]);
    else {
        $value = isset($httpVars[$name]) ? $httpVars[$name] : '';
        if ($convert)
            $value = convertInputString($value);
    }
    httpValue($name, postProcessString($value));
}

function httpGetString($name, $convert = true) {
    return httpString($_GET, $name);
}

function httpPostString($name, $convert = true) {
    return httpString($_POST, $name);
}

function httpRequestString($name, $convert = true) {
    return httpString($_REQUEST, $name);
}

function postProcessUser($value) {
    return idByLogin($value);
}

function httpUser(array $httpVars, $name) {
    global $Args;

    if (!isset($httpVars[$name]) && isset($Args[$name]))
        return;
    httpValue($name,
              postProcessUser(isset($httpVars[$name])
                              ? $httpVars[$name]
                              : 0));
}

function httpGetUser($name) {
    return httpUser($_GET, $name);
}

function httpPostUser($name) {
    return httpUser($_POST, $name);
}

function httpRequestUser($name) {
    return httpUser($_REQUEST, $name);
}

function commandLineArgs() {
    global $Args;

    $Args = array_slice($_SERVER['argv'], 1);
}
?>
