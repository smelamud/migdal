<?php
# @(#) $Id$

require_once('lib/ip.php');

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
global $userId,$REMOTE_ADDR;

$uidFilter=$userId<=0 ? '' : "and user_id=$userId";
$ipFilter=$userId<=0 ? 'and ip='.IPToInteger($REMOTE_ADDR) : '';
$result=mysql_query("select vote
                     from votes
		     where posting_id=$id $uidFilter $ipFilter");
return mysql_num_rows($result)>0 ? mysql_result($result,0,0) : 0;
}

function addVote($id,$vote)
{
global $userId,$REMOTE_ADDR;

$uid=$userId<=0 ? 0 : $userId;
$ip=$userId<=0 ? IPToInteger($REMOTE_ADDR) : 0;
$result=mysql_query("insert into votes(posting_id,ip,user_id,vote)
                     values($id,$ip,$uid,$vote)");
journal("insert into votes(posting_id,ip,user_id,vote)
         values(".journalVar('postings',$id).",$ip,
	        ".journalVar('users',$uid).",$vote)");
return $result;
}
?>
