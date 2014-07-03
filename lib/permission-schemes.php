<?php
# @(#) $Id$

require_once('lib/entry-types.php');
require_once('lib/perm.php');
require_once('lib/debug-log.php');

/*
 * Эти функции необходимы для того, чтобы проверять права доступа на entry, не
 * создавая соответствующего наследника класса Entry. Это применяется,
 * например, при прямом редактировании или проверке permissions через класс
 * Perms.
 */

$permSchemes = array(ENT_NULL     => '',
                     ENT_POSTING  => 'isPermittedPosting',
                     ENT_FORUM    => 'isPermittedForum',
                     ENT_TOPIC    => 'isPermittedTopic',
                     ENT_IMAGE    => '',
                     ENT_VERSION  => '');

function isPermittedEntry(Entry $entry, $right) {
    global $permSchemes;

    if (isset($permSchemes[$entry->getEntry()])) {
        $func = $permSchemes[$entry->getEntry()];
        if ($func != '' && function_exists($func)) {
            $result = $func($entry, $right);
            debugLog(LL_DETAILS, 'Permission check: %(entry %,%)=%',
                     array($func, (int)$entry->getId(),
                           sprintf('0x%04X', $right), $result));
            return $result;
        }
        debugLog(LL_DETAILS, 'Permission check: registered permission function'
                 .' % for entry type % does not exists',
                 array($func, $entry->getEntry()));
        return true;
    }
    debugLog(LL_DETAILS, 'Permission check: permission function for entry type'
                       .' % is not registered', array($entry->getEntry()));
    return true;
}

function isPermittedPosting(Entry $posting, $right) {
    global $userModerator, $userId;

    return $userModerator
           ||
           (!$posting->isDisabled() || $posting->getUserId() == $userId)
           && perm($posting->getUserId(), $posting->getGroupId(),
                   $posting->getPerms(), $right);
}

function isPermittedForum(Entry $forum, $right) {
    global $userModerator, $userId;

    return $userModerator
           ||
           (!$forum->isDisabled() || $forum->getUserId() == $userId)
           && perm($forum->getUserId(), $forum->getGroupId(),
                   $forum->getPerms(), $right);
}

function isPermittedTopic(Entry $topic, $right) {
    global $userAdminTopics, $userModerator;

    return $userAdminTopics && $right != PERM_POST
           ||
           $userModerator && $right == PERM_POST
           ||
           perm($topic->getUserId(), $topic->getGroupId(),
                $topic->getPerms(), $right);
}
?>
