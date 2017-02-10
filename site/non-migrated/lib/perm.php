<?php
# @(#) $Id$

const PB_USER = 0;
const PB_GROUP = 4;
const PB_OTHER = 8;
const PB_GUEST = 12;

const PERM_READ = 1;
const PERM_WRITE = 2;
const PERM_APPEND = 4;
const PERM_POST = 8;

const PERM_UR = 0x0001;
const PERM_UW = 0x0002;
const PERM_UA = 0x0004;
const PERM_UP = 0x0008;
const PERM_GR = 0x0010;
const PERM_GW = 0x0020;
const PERM_GA = 0x0040;
const PERM_GP = 0x0080;
const PERM_OR = 0x0100;
const PERM_OW = 0x0200;
const PERM_OA = 0x0400;
const PERM_OP = 0x0800;
const PERM_ER = 0x1000;
const PERM_EW = 0x2000;
const PERM_EA = 0x4000;
const PERM_EP = 0x8000;

const PERM_NONE = 0x0000;
const PERM_ALL = 0xFFFF;

// Проверка, разрешают ли указанные permission'ы указанное действие
function perm($user_id, $group_id, $perms, $right) {
    global $userId, $userGroups;

    return $userId == $user_id &&
           ($perms & $right << PB_USER) != 0
           ||
           ($userId == $group_id || in_array($group_id, $userGroups)) &&
           ($perms & $right << PB_GROUP) != 0
           ||
           $userId > 0 &&
           ($perms & $right << PB_OTHER) != 0
           ||
           ($perms & $right << PB_GUEST) != 0;
}

// Преобразование строки прав в маску. Вопросительные знаки заменяются
// значениями из $default.
function permString($s, $default = '----------------') {
    $tmpl = "rwaprwaprwaprwap";
    if (strlen($s) != strlen($tmpl))
        return -1;
    $s = strtolower($s);
    $perm = 0;
    $right = 1;
    for ($i = 0; $i < strlen($tmpl); $i++, $right *= 2) {
        $c = $s{$i} == '?' ? $default{$i} : $s{$i};
        if ($c == $tmpl{$i})
            $perm |= $right;
        else
            if ($c != '-')
                return -1;
    }
    return $perm;
}

// Раскладывает строку прав на две маски - AND и OR
function permStringMask($s, &$andMask, &$orMask) {
    $tmpl = "rwaprwaprwaprwap";
    $andMask = PERM_ALL;
    $orMask = PERM_NONE;
    if (strlen($s) != strlen($tmpl))
        return -1;
    $s = strtolower($s);
    $right = 1;
    for ($i = 0; $i < strlen($tmpl); $i++, $right *= 2)
        if ($s{$i} == $tmpl{$i})
            $orMask |= $right;
        else
            if ($s{$i} == '-')
                $andMask &= ~$right;
            else
                if ($s{$i} != '?')
                    return -1;
    return 0;
}

// Преобразует маску в строку прав
function strPerms($perm, $escape = false) {
    $tmpl = "rwaprwaprwaprwap";
    $s = '';
    $right = 1;
    for ($i = 0; $i < strlen($tmpl); $i++, $right *= 2)
        if (($perm & $right) != 0)
            $s .= $tmpl{$i};
        else
            $s .= $escape ? '&nil;-' : '-';
    return $s;
}
?>
