<?php
# @(#) $Id$

require_once('lib/errorreporting.php');
require_once('lib/database.php');
require_once('lib/session.php');
require_once('lib/post.php');
require_once('lib/errors.php');
require_once('lib/topics.php');
require_once('lib/sql.php');

function delTopicLink($topic_id,$topic_grp,$peer_id,$peer_grp)
{
global $userAdminTopics;

if(!$userAdminTopics)
  return ETLD_NO_DEL;
$result=sql("select count(*)
	     from cross_topics
	     where topic_id=$topic_id and topic_grp=$topic_grp and
		   peer_id=$peer_id and peer_grp=$peer_grp",
	    'delTopicLink','check_existence');
if(mysql_result($result,0,0)==0)
  return ETLD_OK;
sql("delete from cross_topics
     where topic_id=$topic_id and topic_grp=$topic_grp and
	   peer_id=$peer_id and peer_grp=$peer_grp or
	   topic_id=$peer_id and topic_grp=$peer_grp and
	   peer_id=$topic_id and peer_grp=$topic_grp",
    'delTopicLink','delete');
journal('delete from cross_topics
         where topic_id='.journalVar('topics',$topic_id).' and
               topic_grp='.journalVar('topics',$topic_grp).' and
	       peer_id='.journalVar('topics',$peer_id).' and
	       peer_grp='.journalVar('topics',$peer_grp).' or
	       topic_id='.journalVar('topics',$peer_id).' and
	       topic_grp='.journalVar('topics',$peer_grp).' and
	       peer_id='.journalVar('topics',$topic_id).' and
	       peer_grp='.journalVar('topics',$topic_grp));
return ETLD_OK;
}

postInteger('topic_id');
postInteger('topic_grp');
postInteger('peer_id');
postInteger('peer_grp');

dbOpen();
session();
$err=delTopicLink($topic_id,$topic_grp,$peer_id,$peer_grp);
if($err==ETLD_OK)
  header('Location: '.remakeURI($okdir,
                                array('err'),
				array('reload' => random(0,999))));
else
  header('Location: '.remakeURI($faildir,
                                array(),
				array('err' => $err)).'#error');
dbClose();
?>
