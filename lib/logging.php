<?php
# @(#) $Id$

require_once('lib/errors.php');
require_once('lib/logs.php');
require_once('lib/sessions.php');
require_once('lib/users.php');
require_once('lib/settings.php');

const RELOGIN_GUEST = 1;
const RELOGIN_SAME = 2;
const RELOGIN_LOGIN = 3;

function login($login, $password, $duration) {
    global $sessionid, $userId, $realUserId, $userMyComputerHint,
           $shortSessionTimeout, $longSessionTimeout;

    $id = getUserIdByLoginPassword($login, $password);
    if ($id == 0)
        return EL_INVALID;
    if ($duration) {
        logEvent('login', "user($id)");
        if ($duration < 0)
            $duration = $userMyComputerHint ? $longSessionTimeout
                                            : $shortSessionTimeout;
        updateSession($sessionid, $id, $id, $duration);
    }
    session($id);
    return EG_OK;
}

function logout($remember = true) {
    global $sessionid, $shortSessionTimeout;

    if ($remember) {
        $row = getUserIdsBySessionId($sessionid);
        $guestId = getGuestId();
        if ($row) {
            list($userId, $realUserId, $duration) = $row;
            logEvent('logout', "user($userId)");
            if ($userId != 0 && $userId != $realUserId) {
                updateSession($sessionid, $realUserId, $realUserId, $duration);
                session();
                return EG_OK;
            }
        }
        updateSession($sessionid, 0, $guestId, $shortSessionTimeout);
        session();
    } else
        session(0);
    return EG_OK;
}

function relogin($relogin, $login, $password, $remember, $guest_login = '') {
    global $userGuestLogin;

    if (!$relogin)
        return EG_OK;
    switch($relogin) {
        case RELOGIN_LOGIN:
            return login($login,$password,$remember ? -1 : 0);
        case RELOGIN_GUEST:
            if (empty($guest_login))
                return EL_GUEST_LOGIN_ABSENT;
            $userGuestLogin = $guest_login;
            updateSettingsCookie();
            return logout(false);
    }
}

function loginHints($login,$my_computer) {
    global $userLoginHint, $userMyComputerHint;

    $userLoginHint = $login;
    $userMyComputerHint = $my_computer;
    updateSettingsCookie();
}
?>
