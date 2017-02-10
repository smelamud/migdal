<?php
# @(#) $Id$

require_once('conf/migdal.conf');

require_once('lib/structure.php');
require_once('lib/post.php');
require_once('lib/database.php');
require_once('lib/redirs.php');
require_once('lib/logs.php');
require_once('lib/session.php'); // FIXME Не очень хорошее решение

require_once('conf/subdomains.php');

function dispatch404($requestPath) {
    $info = getLocationInfo('/404/', 0);
    if ($info->getScript() == '' && $info->getPath() == $requestPath) {
        $info = new LocationInfo();
        $info->setPath($requestPath);
        $info->setScript('404.php');
    }
    return $info;
}

function dispatchScript($requestPath, $parts) {
    global $directScripts;

    $info = new LocationInfo();
    $pos = strpos($requestPath, '.php');
    $scriptName = substr($requestPath, 1, $pos + 3);
    $trapFunc='trap'.ucfirst(substr(strtr($scriptName, '/-', '__'), 0, -4));
    if (function_exists($trapFunc)) {
        $path = $trapFunc(parseQuery(
                    isset($parts['query']) ? $parts['query'] : ''));
        if ($path != '') {
            $info->setPath($path);
            return $info;
        } else {
            logEvent('trap-fail', $_SERVER['REQUEST_URI']);
            return dispatch404($requestPath);
        }
    }
    if (!$directScripts || !file_exists($scriptName)) {
        logEvent('trap-fail', $requestPath);
        return dispatch404($requestPath);
    }
    $info->setPath($requestPath);
    $info->setScript($scriptName);
    return $info;
}

function dispatchLocation($requestPath) {
    global $redirid;

    if ($redirid != 0 && !redirExists($redirid)) {
        $info = new LocationInfo();
        $info->setPath(remakeURI($_SERVER['REQUEST_URI'], array('redirid')));
        return $info;
    }
    $info = getLocationInfo($requestPath, $redirid);
    if ($info->getScript() == '' && $info->getPath() == $requestPath)
        $info = dispatch404($requestPath);
    return $info;
}

function dispatch() {
    $info = dispatchSubDomain($_SERVER['REQUEST_URI']);
    if (!is_null($info))
        return $info;
    $parts = parse_url($_SERVER['REQUEST_URI']);
    $requestPath = $parts['path'];
    if ($requestPath == '/dispatcher.php')
        return dispatch404($requestPath);
    elseif (strpos($requestPath, '.php') !== false)
        return dispatchScript($requestPath, $parts);
    elseif (substr($requestPath, 0, 9) != '/actions/'
            && substr($requestPath, -1) != '/'
            && substr($requestPath, 5) != '.html')
        return dispatchAddSlash($requestPath, $parts);
    else
        return dispatchLocation($requestPath);
}

function exposeArgs($args) {
    foreach ($args as $name => $value)
        httpValue($name, $value);
}

httpRequestInteger('redirid');
unset($Args['redirid']);

dbOpen();
session(); // FIXME Не очень хорошее решение для того, чтобы можно было в
           // structure.conf проверять права юзера
$LocationInfo = dispatch();
$ScriptName = $LocationInfo->getScript();
if ($ScriptName!='') {
    exposeArgs($LocationInfo->getArgs());
    redirect();
    include($ScriptName);
} else
    header('Location: '.$LocationInfo->getPath());
dbClose();
?>
