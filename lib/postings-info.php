<?php
# @(#) $Id$

require_once('lib/dataobject.php');
require_once('lib/bug.php');
require_once('lib/track.php');

class PostingsInfo
      extends DataObject
{
var $total;
var $max_sent;

function PostingsInfo($row)
{
$this->DataObject($row);
}

function getTotal()
{
return $this->total;
}

function getMaxSent()
{
return $this->max_sent;
}

}

function getLastPostingDate($grp=GRP_ALL,$topic_id=-1,$answers=GRP_NONE,
                            $user_id=0,$recursive=false)
{
$info=getPostingsInfo($grp,$topic_id,$answers,$user_id,$recursive);
return $info->getMaxSent();
}

function getPostingsMessagesInfo($grp=GRP_ALL,$topic_id=-1,$user_id=0,
                                 $recursive=false)
{
global $userId,$userModerator;

if($grp==GRP_NONE)
  return new PostingsInfo();
$hide=$userModerator ? 2 : 1;
$topicFilter=$topic_id<0 ? '' : " and topics.".subtree($topic_id,$recursive);
$userFilter=$user_id>0 ? " and messages.sender_id=$user_id " : '';
$result=mysql_query(
        "select count(*) as total,max(messages.sent) as max_sent
         from messages
	      left join postings
	           on postings.message_id=messages.id
	      left join topics
	           on topics.id=postings.topic_id
	 where postings.id is not null and
	       (messages.hidden<$hide or messages.sender_id=$userId) and
	       (messages.disabled<$hide or messages.sender_id=$userId) and
               (postings.grp & $grp)<>0 $topicFilter $userFilter")
 or sqlbug('Ошибка SQL при получении информации о постингах');
$row=mysql_fetch_assoc($result);
$row['max_sent']=$row['max_sent']!='' ? strtotime($row['max_sent']) : 0;
return new PostingsInfo($row);
}

function getPostingsAnswersInfo($grp=GRP_NONE,$topic_id=-1,$user_id=0,
                                $recursive=false)
{
global $userId,$userModerator;

if($grp==GRP_NONE)
  return new PostingsInfo();
$hide=$userModerator ? 2 : 1;
$topicFilter=$topic_id<0 ? '' : " and topics.".subtree($topic_id,$recursive);
$userFilter=$user_id>0 ? " and messages.sender_id=$user_id " : '';
$result=mysql_query(
        "select count(*) as total,max(messages.sent) as max_sent
         from messages
	      left join forums
	           on forums.message_id=messages.id
	      left join messages as msgs
	           on forums.parent_id=msgs.id
	      left join postings
	           on postings.message_id=msgs.id
	      left join topics
	           on topics.id=postings.topic_id
	 where forums.id is not null and
	       (messages.hidden<$hide or messages.sender_id=$userId) and
	       (messages.disabled<$hide or messages.sender_id=$userId) and
	       (msgs.hidden<$hide or msgs.sender_id=$userId) and
	       (msgs.disabled<$hide or msgs.sender_id=$userId) and
               (postings.grp & $grp)<>0 $topicFilter $userFilter")
 or sqlbug('Ошибка SQL при получении информации об ответах на постинги');
$row=mysql_fetch_assoc($result);
$row['max_sent']=$row['max_sent']!='' ? strtotime($row['max_sent']) : 0;
return new PostingsInfo($row);
}

function getPostingsInfo($grp=GRP_ALL,$topic_id=-1,$answers=GRP_NONE,
                         $user_id=0,$recursive=false)
{
$msgInfo=getPostingsMessagesInfo($grp,$topic_id,$user_id,$recursive);
$ansInfo=getPostingsAnswersInfo($answers,$topic_id,$user_id,$recursive);
return new PostingsInfo(array('total' => $msgInfo->getTotal()+
                                        $ansInfo->getTotal(),
			      'max_sent' => max($msgInfo->getMaxSent(),
						$ansInfo->getMaxSent())));
}
?>
