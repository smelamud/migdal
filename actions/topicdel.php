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

function deleteTopic($id,$destid)
{
$perms=getPermsById('topics',$id);
if(!$perms)
  return ETD_TOPIC_ABSENT;
if(!$perms->isWritable())
  return ETD_NO_DELETE;
$result=sql("select id
	     from topics
	     where up=$id",
	    'deleteTopic','get_descedants');
$tops=array();
while($row=mysql_fetch_row($result))
     $tops[]=$row[0];
$result=sql("select count(*)
	     from postings
	     where topic_id=$id",
	    'deleteTopic','count_postings');
if(mysql_result($result,0,0)!=0)
  {
  if($destid<=0 || $destid==$id || !($perms=getPermsById('topics',$destid)))
    return ETD_DEST_ABSENT;
  if(!$perms->isPostable())
    return ETD_DEST_ACCESS;
  }
sql("delete from topics
     where id=$id",
    'deleteTopic','delete_topic');
journal('delete from topics
         where id='.journalVar('topics',$id));
sql("delete from cross_topics
     where topic_id=$id or peer_id=$id",
    'deleteTopic','delete_cross_topics');
journal('delete from cross_topics
         where topic_id='.journalVar('topics',$id).' or
	       peer_id='.journalVar('topics',$id));
sql("update topics
     set up=$destid
     where up=$id",
    'deleteTopic','move_children');
journal('update topics
         set up='.journalVar('topics',$destid).'
	 where up='.journalVar('topics',$id));
sql("update postings
     set topic_id=$destid
     where topic_id=$id",
    'deleteTopic','move_postings');
journal('update postings
         set topic_id='.journalVar('topics',$destid).'
	 where topic_id='.journalVar('topics',$id));
foreach($tops as $tid)
       updateTracks('topics',$tid);
return ETD_OK;
}

postInteger('id');
postInteger('destid');

dbOpen();
session();
$err=deleteTopic($id,$destid);
if($err==ETD_OK)
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
