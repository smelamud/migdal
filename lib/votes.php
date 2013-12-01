<?php
# @(#) $Id$

require_once('lib/ip.php');
require_once('lib/sql.php');
require_once('lib/entries.php');

const MIN_VOTE = 2;
const MAX_VOTE = 4;

function getRatingSQL($vote, $vote_count) {
    return "$vote-3*cast($vote_count as signed)";
}

function getRating($vote, $vote_count) {
    return $vote - 3 * $vote_count;
}

function getVote($id) {
    global $userId;

    $uidFilter = $userId <= 0 ? '' : "and user_id=$userId";
    $ipFilter = $userId <= 0 ? 'and ip='.IPToInteger($_SERVER['REMOTE_ADDR'])
                             : '';
    $result = sql("select vote
                   from votes
                   where entry_id=$id $uidFilter $ipFilter",
                  __FUNCTION__);
    return mysql_num_rows($result) > 0 ? mysql_result($result, 0, 0) : -1;
}

function addVote($id, $vote) {
    global $userId, $userModerator, $userVoteWeight, $moderatorVoteWeight;

    $uid = $userId <= 0 ? 0 : $userId;
    $ip = $userId <= 0 ? IPToInteger($_SERVER['REMOTE_ADDR']) : 0;
    $result = sql("insert into votes(entry_id,ip,user_id,vote)
                   values($id,$ip,$uid,$vote)",
                  __FUNCTION__,'insert');

    if ($vote > (MIN_VOTE + MAX_VOTE) / 2)
        $weight = $userModerator ? $moderatorVoteWeight
                                 : ($userId > 0 ? $userVoteWeight : 1);
    else
        $weight = 1;
    sql("update entries
         set vote=vote+$weight*$vote,vote_count=vote_count+$weight
         where id=$id",
        __FUNCTION__,'update_votes');
    sql('update entries
         set rating='.getRatingSQL('vote','vote_count')."
         where id=$id",
        __FUNCTION__,'update_rating');
    return $result;
}

function getSelectVote($parent_id) {
    global $userId;

    $uidFilter = $userId <= 0 ? '' : "and votes.user_id=$userId";
    $ipFilter = $userId <= 0
                ? 'and votes.ip='.IPToInteger($_SERVER['REMOTE_ADDR'])
                : '';
    $result = sql("select votes.vote
                   from votes
                        left join entries
                             on votes.entry_id=entries.id
                   where entries.parent_id=$parent_id $uidFilter $ipFilter",
                  __FUNCTION__);
    return mysql_num_rows($result) > 0 ? mysql_result($result, 0, 0) : -1;
}

function addSelectVote($id, $vote) {
    global $userId;

    $uid = $userId <= 0 ? 0 : $userId;
    $ip = $userId <= 0 ? IPToInteger($_SERVER['REMOTE_ADDR']) : 0;
    $result = sql("insert into votes(entry_id,ip,user_id,vote)
                   values($id,$ip,$uid,$vote)",
                  __FUNCTION__,'insert');

    sql("update entries
         set vote=vote+$vote,vote_count=vote_count+1
         where id=$id",
        __FUNCTION__,'update_votes');
    sql('update entries
         set rating='.getRatingSQL('vote','vote_count')."
         where id=$id",
        __FUNCTION__,'update_rating');
    return $result;
}

function deleteExpiredVotes() {
    global $anonVoteTimeout, $userVoteTimeout;

    $now = sqlNow();
    sql("delete
         from votes
         where user_id=0 and sent+interval $anonVoteTimeout hour<'$now'",
        __FUNCTION__,'delete_anonymous');
    sql("delete
         from votes
         where user_id<>0 and sent+interval $userVoteTimeout hour<'$now'",
        __FUNCTION__,'delete_registered');
    sql("optimize table votes",
        __FUNCTION__,'optimize');
}
?>
