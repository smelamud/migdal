<?php
# @(#) $Id$

require_once('lib/errorreporting.php');
require_once('lib/database.php');
require_once('lib/session.php');
require_once('lib/post.php');
require_once('lib/errors.php');
require_once('lib/topics.php');

function delTopicLink($topic_id,$peer_id)
{
global $userAdminTopics;

if(!$userAdminTopics)
  return ETLD_NO_DEL;
$result=mysql_query("select count(*)
                     from cross_topics
		     where topic_id=$topic_id and peer_id=$peer_id");
if(!$result)
  return ETLD_SQL_CHECK;
if(mysql_result($result,0,0)==0)
  return ETLD_OK;
$result=mysql_query("delete from cross_topics
                     where topic_id=$topic_id and peer_id=$peer_id or
		           topic_id=$peer_id and peer_id=$topic_id");
journal('delete from cross_topics
         where topic_id='.journalVar('topics',$topic_id).' and
	       peer_id='.journalVar('topics',$peer_id).' or
	       topic_id='.journalVar('topics',$peer_id).' and
	       peer_id='.journalVar('topics',$topic_id));
return !$result ? ETLD_SQL_DELETE : ETLD_OK;
}

postInteger('topic_id');
postInteger('peer_id');

dbOpen();
session();
$err=delTopicLink($topic_id,$peer_id);
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
