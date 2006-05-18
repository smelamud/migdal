<?php
# @(#) $Id$

require_once('lib/errorreporting.php');
require_once('lib/database.php');
require_once('lib/session.php');
require_once('lib/post.php');
require_once('lib/errors.php');
require_once('lib/postings-info.php');
require_once('lib/track.php');
require_once('lib/topics.php');
require_once('lib/sql.php');

function deleteTopicAction($id,$destid)
{
$perms=getPermsById($id);
if(!$perms)
  return ETD_TOPIC_ABSENT;
if(!$perms->isWritable())
  return ETD_NO_DELETE;
if(topicHasContent($id))
  {
  if($destid<=0 || $destid==$id || !topicExists($destid))
    return ETD_DEST_ABSENT;
  $perms=getPermsById($destid);
  if(!$perms->isPostable())
    return ETD_DEST_ACCESS;
  }
else
  $destid=0;
deleteTopic($id,$destid);
return EG_OK;
}

postString('okdir');
postString('faildir');

postInteger('id');
postInteger('destid');

dbOpen();
session();
$err=deleteTopicAction($id,$destid);
if($err==EG_OK)
  {
  header('Location: '.remakeURI($okdir,
                                array('err','destid'),
				array('reload' => random(0,999))));
  dropPostingsInfoCache(DPIC_POSTINGS);
  }
else
  header('Location: '.remakeURI($faildir,
                                array(),
				array('destid' => $destid,
				      'err'    => $err)).'#error');
dbClose();
?>
