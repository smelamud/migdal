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
global $userId;

$uid=$userId<=0 ? 0 : $userId;
$ip=$userId<=0 ? IPToInteger($_SERVER['REMOTE_ADDR']) : 0;
$result=sql("insert into votes(entry_id,ip,user_id,vote)
	     values($id,$ip,$uid,$vote)",
	    __FUNCTION__);
journal("insert into votes(entry_id,ip,user_id,vote)
         values(".journalVar('entries',$id).",$ip,
	        ".journalVar('users',$uid).",$vote)");
return $result;
}
?>
