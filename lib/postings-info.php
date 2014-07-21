<?php
# @(#) $Id$

require_once('lib/dataobject.php');
require_once('lib/bug.php');
require_once('lib/track.php');
require_once('lib/sql.php');
require_once('lib/entries.php');
require_once('lib/postings.php');

class PostingsInfo
        extends DataObject {

    protected $total;
    protected $max_sent;

    public function __construct(array $row = array()) {
        parent::__construct($row);
    }

    public function getTotal() {
        return $this->total;
    }

    public function getMaxSent() {
        return $this->max_sent;
    }

}

function getLastPostingDate($grp = GRP_ALL, $topic_id = -1, $answers = GRP_NONE,
                            $user_id = 0, $recursive = false,
                            $asGuest = false) {
    $info = getPostingsInfo($grp, $topic_id, $answers, $user_id, $recursive,
                            $asGuest);
    return $info->getMaxSent();
}

function getInfoTopicFilter($topic_id = -1, $recursive = false) {
    if (is_array($topic_id)) {
        $topicFilter = '';
        $op = '';
        for ($i = 0; $i < count($topic_id); $i++) {
            $topicFilter .= " $op ".subtree('entries', $topic_id[$i],
                                            $recursive[$i]);
            $op = 'or';
        }
        $topicFilter = " and ($topicFilter)";
    } else {
        $topicFilter = $topic_id < 0
                       ? '' : ' and '.subtree('entries', $topic_id, $recursive);
    }
    return $topicFilter;
}

function getPostingsMessagesInfo($grp = GRP_ALL, $topic_id = -1, $user_id = 0,
                                 $recursive = false, $asGuest = false) {
    if ($grp == GRP_NONE)
        return new PostingsInfo(array());
    $hide = 'and '.postingsPermFilter(PERM_READ, 'entries', $asGuest);
    $grpFilter = 'and '.grpFilter($grp, 'grp');
    $topicFilter = getInfoTopicFilter($topic_id, $recursive);
    $userFilter = $user_id > 0 ? " and user_id=$user_id " : '';
    $result = sql('select count(*) as total,max(sent) as max_sent
                   from entries
                   where entry='.ENT_POSTING." $hide $grpFilter $topicFilter
                   $userFilter",
                  __FUNCTION__);
    $row = mysql_fetch_assoc($result);
    $row['max_sent'] = $row['max_sent'] != '' ? strtotime($row['max_sent']) : 0;
    return new PostingsInfo($row);
}

function getPostingsAnswersInfo($grp = GRP_NONE, $topic_id = -1, $user_id = 0,
                                $recursive = false, $asGuest = false) {
    if (count($grp) == 0)
        return new PostingsInfo(array());
    $hide = 'and '.postingsPermFilter(PERM_READ, 'entries', $asGuest);
    $grpFilter = 'and '.grpFilter($grp, 'grp');
    $topicFilter = getInfoTopicFilter($topic_id, $recursive);
    $userFilter = $user_id > 0 ? " and user_id=$user_id " : '';
    $result = sql('select max(last_answer) as max_sent
                   from entries
                   where entry='.ENT_POSTING." $hide $grpFilter $topicFilter
                   $userFilter",
                  __FUNCTION__);
    $row = mysql_fetch_assoc($result);
    $row['max_sent'] = $row['max_sent'] != '' ? strtotime($row['max_sent']) : 0;
    $row['total'] = 0; // Общее количество ответов не используется, поэтому
                       // убрано для экономии времени
    return new PostingsInfo($row);
    }

function getPostingsInfo($grp = GRP_ALL, $topic_id = -1, $answers = GRP_NONE,
                         $user_id = 0, $recursive = false, $asGuest = false) {
    $grp = grpArray($grp);
    $answers = grpArray($answers);
    $msgInfo = getPostingsMessagesInfo($grp, $topic_id, $user_id, $recursive,
                                       $asGuest);
    $ansInfo = getPostingsAnswersInfo($answers, $topic_id, $user_id, $recursive,
                                      $asGuest);
    $info = new PostingsInfo(array('total' => $msgInfo->getTotal()+
                                              $ansInfo->getTotal(),
                                   'max_sent' => max($msgInfo->getMaxSent(),
                                                     $ansInfo->getMaxSent())));
    return $info;
}
?>
