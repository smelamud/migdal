<?php
# @(#) $Id: daily.php 2762 2014-06-16 10:51:04Z balu $

require_once('conf/migdal.conf');
$debug=true;

require_once('lib/errorreporting.php');
require_once('lib/database.php');
require_once('lib/sql.php');
require_once('lib/session.php');
require_once('lib/postings.php');
require_once('lib/cross-entries.php');

function publish($topic_id, $user_id, $group_id, $sent, array $ids) {
    if (count($ids) == 0)
        return;
    $posting = new Posting(array(
        'up' => $topic_id,
        'user_id' => $user_id,
        'group_id' => $group_id,
        'perms' => PERM_UR | PERM_UW | PERM_UA | PERM_UP
                   | PERM_GR | PERM_GW | PERM_GA | PERM_GP
                   | PERM_OR | PERM_OP
                   | PERM_ER | PERM_EP,
        'index1' => count($ids),
        'parent_id' => $topic_id,
        'grp' => GRP_GALLERY_UPDATE,
        'sent' => sqlDate($sent)
    ));
    storePosting($posting);
    foreach ($ids as $id) {
        $cross = new CrossEntry(array(
            'source_id' => $posting->getId(),
            'link_type' => LINKT_PUBLISH,
            'peer_id' => $id
        ));
        storeCrossEntry($cross);
    }
}

dbOpen();
session(getShamesId());
$userModerator = true;

$iter = new PostingListIterator(GRP_GALLERY, -1, true, 0, 0);
$topic_id = 0;
$user_id = 0;
$group_id = 0;
$sent = 0;
$last_sent = 0;
$ids = array();
foreach($iter as $item) {
    if ($topic_id != $item->getTopicId() || $user_id != $item->getUserId()
        || ($sent - $item->getSent()) / 3600 > 6) {
        if ($topic_id != 0) {
            publish($topic_id, $user_id, $group_id, $last_sent, $ids);
        }
        $topic_id = $item->getTopicId();
        $user_id = $item->getUserId();
        $group_id = $item->getGroupId();
        $last_sent = $item->getSent();
        $ids = array($item->getId());
    } else {
        array_push($ids, $item->getId());
    }
    $sent = $item->getSent();
}
if ($topic_id != 0) {
    publish($topic_id, $user_id, $group_id, $last_sent, $ids);
}

dbClose();
?>
