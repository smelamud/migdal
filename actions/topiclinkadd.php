<?php
# @(#) $Id$

require_once('lib/errorreporting.php');
require_once('lib/database.php');
require_once('lib/session.php');
require_once('lib/post.php');
require_once('lib/errors.php');
require_once('lib/topics.php');

function addTopicLink($topic_id,$topic_grp,$peer_id,$peer_grp)
{
global $userAdminTopics;

if(!$userAdminTopics)
  return ETLA_NO_ADD;
if(!topicExists($topic_id) || !topicExists($peer_id))
  return ETLA_TOPIC_ABSENT;
$result=mysql_query("select count(*)
                     from cross_topics
		     where topic_id=$topic_id and topic_grp=$topic_grp and
		           peer_id=$peer_id and peer_grp=$peer_grp");
if(!$result)
  return ETLA_SQL_CHECK;
if(mysql_result($result,0,0)!=0)
  return ETLA_OK;
$result=mysql_query("insert into cross_topics(topic_id,topic_grp,
                                              peer_id,peer_grp)
                     values($topic_id,$topic_grp,$peer_id,$peer_grp),
		           ($peer_id,$peer_grp,$topic_id,$topic_grp)");
journal('insert into cross_topics(topic_id,topic_grp,peer_id,peer_grp)
         values('.journalVar('topics',$topic_id).',
                '.journalVar('topics',$topic_grp).',
	        '.journalVar('topics',$peer_id).',
	        '.journalVar('topics',$peer_grp).'),
	       ('.journalVar('topics',$peer_id).',
	        '.journalVar('topics',$peer_grp).',
	        '.journalVar('topics',$topic_id).',
	        '.journalVar('topics',$topic_grp).')');
return !$result ? ETLA_SQL_INSERT : ETLA_OK;
}

postInteger('topic_id');
postInteger('topic_grp');
postInteger('peer_id');
postInteger('peer_grp');

dbOpen();
session();
$err=addTopicLink($topic_id,$topic_grp,$peer_id,$peer_grp);
if($err==ETLA_OK)
  header('Location: '.remakeURI($okdir,array('err')));
else
  header('Location: '.remakeURI($faildir,array(),array('err' => $err)).'#error');
dbClose();
?>
