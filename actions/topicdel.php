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

function deleteTopic($id,$destid)
{
global $userAdminTopics;

if(!$userAdminTopics)
  return ETD_NO_DELETE;
if(!topicExists($id))
  return ETD_TOPIC_ABSENT;
$result=mysql_query("select id
                     from topics
		     where up=$id");
if(!$result)
  return ETD_TOPICS_SQL;
$tops=array();
while($row=mysql_fetch_row($result))
     $tops[]=$row[0];
$result=mysql_query("select count(*)
                     from postings
		     where topic_id=$id");
if(!$result)
  return ETD_POSTINGS_SQL;
if(mysql_result($result,0,0)!=0
   && ($destid<=0 || $destid==$id || !topicExists($destid)))
  return ETD_DEST_ABSENT;
$result=mysql_query("delete from topics
                     where id=$id");
if(!$result)
  return ETD_DELETE_SQL;
journal('delete from topics
         where id='.journalVar('topics',$id));
$result=mysql_query("delete from cross_topics
                     where topic_id=$id or peer_id=$id");
if(!$result)
  return ETD_CROSS_SQL;
journal('delete from cross_topics
         where topic_id='.journalVar('topics',$id).' or
	       peer_id='.journalVar('topics',$id));
$result=mysql_query("update topics
		     set up=$destid
		     where up=$id");
if(!$result)
  return ETD_NEW_UP_SQL;
journal('update topics
         set up='.journalVar('topics',$destid).'
	 where up='.journalVar('topics',$id));
$result=mysql_query("update postings
		     set topic_id=$destid
		     where topic_id=$id");
if(!$result)
  return ETD_NEW_TOPIC_SQL;
journal('update postings
         set topic_id='.journalVar('topics',$destid).'
	 where topic_id='.journalVar('topics',$id));
foreach($tops as $tid)
       if(!updateTracks('topics',$tid))
         return ETD_UPDATE_SQL;
return ETD_OK;
}

postInteger('id');
postInteger('destid');

dbOpen();
session();
$err=deleteTopic($id,$destid);
if($err==ETD_OK)
  header('Location: '.remakeURI($okdir,
                                array('err','destid'),
				array('reload' => random(0,999))));
else
  header('Location: '.remakeURI($faildir,
                                array(),
				array('destid' => $destid,
				      'err'    => $err)).'#error');
dbClose();
?>
