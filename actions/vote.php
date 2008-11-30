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

function vote($id,$vote,$isSelect)
{
if(!postingExists($id))
  return EV_NO_POSTING;
if(!$isSelect)
  {
  if(getVote($id))
    return EV_ALREADY_VOTED;
  addVote($id,$vote);
  }
else
  {
  if(getSelectVote(getParentIdByEntryId($id)))
    return EV_ALREADY_VOTED;
  addSelectVote($id,$vote);
  }
logEvent('vote',"post($id)");
return EG_OK;
}

postString('okdir');
postString('faildir');

postInteger('postid');
postInteger('vote');
postInteger('isselect');

dbOpen();
session();
$err=vote($postid,$vote,$isselect);
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
