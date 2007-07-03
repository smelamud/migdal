<?php
# @(#) $Id$

require_once('lib/ip.php');
require_once('lib/sql.php');

function getRatingSQL($vote,$vote_count)
{
return "if($vote_count=0,3,".
       "$vote_count/($vote_count+1)*($vote/$vote_count-3)+3)";
}

function getRating($vote,$vote_count)
{
return $vote_count==0 ? 3
                      : $vote_count/($vote_count+1)*($vote/$vote_count-3)+3;
}

function getVote($id)
{
global $userId;

$uidFilter=$userId<=0 ? '' : "and user_id=$userId";
$ipFilter=$userId<=0 ? 'and ip='.IPToInteger($_SERVER['REMOTE_ADDR']) : '';
$result=sql("select vote
	     from votes
	     where entry_id=$id $uidFilter $ipFilter",
	    __FUNCTION__);
return mysql_num_rows($result)>0 ? mysql_result($result,0,0) : 0;
}

function addVote($id,$vote)
{
global $userId,$userModerator,$userVoteWeight,$moderatorVoteWeight;

$uid=$userId<=0 ? 0 : $userId;
$ip=$userId<=0 ? IPToInteger($_SERVER['REMOTE_ADDR']) : 0;
$result=sql("insert into votes(entry_id,ip,user_id,vote)
	     values($id,$ip,$uid,$vote)",
	    __FUNCTION__,'insert');
journal("insert into votes(entry_id,ip,user_id,vote)
         values(".journalVar('entries',$id).",$ip,
	        ".journalVar('users',$uid).",$vote)");

$weight=$userModerator ? $moderatorVoteWeight
                       : ($userId>0 ? $userVoteWeight : 1);
sql("update entries
     set vote=vote+$weight*$vote,vote_count=vote_count+$weight
     where id=$id",
    __FUNCTION__,'update_votes');
journal("update entries
         set vote=vote+$weight*$vote,vote_count=vote_count+$weight
	 where id=".journalVar('entries',$id));
sql('update entries
     set rating='.getRatingSQL('vote','vote_count')."
     where id=$id",
    __FUNCTION__,'update_rating');
journal('update entries
         set rating='.getRatingSQL('vote','vote_count')."
	 where id=".journalVar('entries',$id));
return $result;
}

function deleteExpiredVotes()
{
global $anonVoteTimeout,$userVoteTimeout;

sql("delete
     from votes
     where user_id=0 and sent+interval $anonVoteTimeout hour<now()",
    __FUNCTION__,'delete_anonymous');
sql("delete
     from votes
     where user_id<>0 and sent+interval $userVoteTimeout hour<now()",
    __FUNCTION__,'delete_registered');
sql("optimize table votes",
    __FUNCTION__,'optimize');
}
?>
