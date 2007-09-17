<?php
# @(#) $Id: vote.php 2410 2007-03-14 20:17:18Z balu $

require_once('lib/errorreporting.php');
require_once('lib/database.php');
require_once('lib/session.php');
require_once('lib/post.php');
require_once('lib/postings.php');
require_once('lib/votes.php');
require_once('lib/errors.php');
require_once('lib/logs.php');
require_once('lib/json.php');
require_once('lib/entries.php');

function vote($id,$vote)
{
global $userId,$userModerator,$userVoteWeight,$moderatorVoteWeight;

if(!postingExists($id))
  return EV_NO_POSTING;
if(getVote($id))
  return EV_ALREADY_VOTED;
addVote($id,$vote);
logEvent('vote',"post($id)");
return EG_OK;
}

postInteger('postid');
postInteger('vote');

dbOpen();
session();
$err=vote($postid,$vote);
header('Content-Type: application/json');
$data=array('id' => $postid,
            'vote' => $vote);
if($err==EG_OK)
  $data['rating']=getRatingByEntryId($postid);
else
  $data['err']=$err;
jsonOutput($data);
dbClose();
?>
