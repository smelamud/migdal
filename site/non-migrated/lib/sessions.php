<?php
# @(#) $Id$

require_once('lib/random.php');
require_once('lib/bug.php');
require_once('lib/sql.php');
require_once('lib/time.php');

function getUserIdBySessionId($sessionId) {
    $sessionIdS = addslashes($sessionId);
    $result = sql("select user_id
                   from sessions
                   where sid='$sessionIdS'",
                  __FUNCTION__);
    return mysql_num_rows($result) > 0 ? mysql_result($result, 0, 0) : -1;
}

function sessionExists($sessionId) {
    return getUserIdBySessionId($sessionId) >= 0;
}

function getUserIdsBySessionId($sessionId) {
    $sessionIdS = addslashes($sessionId);
    $result = sql("select user_id,real_user_id,duration
                   from sessions
                   where sid='$sessionIdS'",
                  __FUNCTION__);
    return mysql_num_rows($result) > 0 ? mysql_fetch_array($result) : 0;
}

function updateSessionTimestamp($sessionId) {
    $sessionIdS = addslashes($sessionId);
    $now = sqlNow();
    sql("update sessions
         set last='$now'
         where sid='$sessionIdS'",
        __FUNCTION__);
}

function createSessionId() {
    do {
        $sid = md5(uniqid(rand(), true));
    } while(sessionExists($sid));
    return $sid;
}

function createSession($userId, $realUserId) {
    global $shortSessionTimeout;

    $sid = createSessionId();
    $now = sqlNow();
    sql("insert into sessions(user_id,real_user_id,duration,sid,last)
         values($userId,$realUserId,$shortSessionTimeout,'$sid','$now')",
        __FUNCTION__);
    return $sid;
}

function updateSession($sessionId, $userId, $realUserId, $duration) {
    $sessionIdS = addslashes($sessionId);
    $now = sqlNow();
    sql("update sessions
         set user_id=$userId,real_user_id=$realUserId,duration=$duration,
             last='$now'
         where sid='$sessionIdS'",
        __FUNCTION__);
}

function updateSessionUserId($sessionId, $userId) {
    $sessionIdS = addslashes($sessionId);
    $now = sqlNow();
    sql("update sessions
         set user_id=$userId,last='$now'
         where sid='$sessionIdS'",
        __FUNCTION__);
}

function deleteSession($sessionId) {
    $sessionIdS = addslashes($sessionId);
    sql("delete from sessions
         where sid='$sessionIdS'",
        __FUNCTION__);
}

function deleteClosedSessions() {
    $now = sqlNow();
    sql("delete from sessions
         where last+interval duration hour<'$now'",
        __FUNCTION__,'delete');
    sql("delete from sessions
         where user_id > (select max(id) from users)",
        __FUNCTION__,'delete_garbage');
    sql('optimize table sessions',
        __FUNCTION__,'optimize');
}

function getSubdomainCookie($name) {
    global $siteDomain;

    $sd = strtr($siteDomain, '.', '_');
    return isset($_COOKIE["$name-$sd"]) ? $_COOKIE["$name-$sd"] : '';
}

function setSubdomainCookie($name, $value, $expire, $path, $domain) {
    global $siteDomain;

    $sd = strtr($siteDomain, '.', '_');
    setcookie("$name-$sd", $value, $expire, $path, $domain);
}

function getSessionCookie() {
    return getSubdomainCookie('sessionid');
}

function setSessionCookie($sessionId) {
    global $siteDomain, $longSessionTimeout;

    if ($sessionId === 0)
        setSubdomainCookie('sessionid', 0, 0, '/', $siteDomain);
    else
        setSubdomainCookie('sessionid', $sessionId,
                           time() + ($longSessionTimeout + 24) * 3600,
                           '/', $siteDomain);
}
?>