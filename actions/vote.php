<?php
# @(#) $Id$

require_once('lib/errorreporting.php');
require_once('lib/database.php');
require_once('lib/session.php');
require_once('lib/post.php');
require_once('lib/postings.php');
require_once('lib/votes.php');
require_once('lib/errors.php');
require_once('lib/random.php');
require_once('lib/logs.php');
require_once('lib/sql.php');

function vote($id,$vote)
{
global $userId,$userModerator,$userVoteWeight,$moderatorVoteWeight;

if(!postingExists($id))
  return EV_NO_POSTING;
if(getVote($id))
  return EV_ALREADY_VOTED;
addVote($id,$vote);
logEvent('vote',"post($id)");
$weight=$userModerator ? $moderatorVoteWeight : ($userId>0 ? $userVoteWeight : 1);
sql("update postings
     set vote=vote+$weight*$vote,vote_count=vote_count+$weight
     where id=$id",
    'vote');
journal("update postings
         set vote=vote+$weight*$vote,vote_count=vote_count+$weight
	 where id=".journalVar('postings',$id));
return EG_OK;
}

postInteger('postid');
postInteger('vote');

dbOpen();
session();
$err=vote($postid,$vote);
if($err==EG_OK)
  header('Location: '.remakeURI($okdir,
                                array('err'),
				array('reload' => random(0,999))));
else
  header('Location: '.remakeURI($faildir,
                                array(),
				array('err' => $err)));
dbClose();
?>
