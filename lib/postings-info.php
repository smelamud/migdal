<?php
# @(#) $Id$

require_once('lib/dataobject.php');
require_once('lib/bug.php');

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

function getPostingsInfo($grp=GRP_ALL,$topic_id=-1,$answers=GRP_NONE,
                         $user_id=0,$recursive=false)
{
global $userId,$userModerator;

$hide=$userModerator ? 2 : 1;
$tpf=$topic_id<0 ? '' : " and topics.".byIdentRecursive('topics',$topic_id,
                                                        $recursive);
$taf=$topic_id<0 ? '' : " and tops.".byIdentRecursive('topics',$topic_id,
                                                      $recursive);
$uf=$user_id>0 ? " and messages.sender_id=$user_id " : '';
$result=mysql_query(
        "select count(*) as total,max(messages.sent) as max_sent
         from messages
	      left join postings
	           on postings.message_id=messages.id
	      left join topics
	           on topics.id=postings.topic_id
	      left join forums
	           on forums.message_id=messages.id
	      left join messages as msgs
	           on forums.parent_id=msgs.id
	      left join postings as posts
	           on posts.message_id=msgs.id
	      left join topics as tops
	           on tops.id=posts.topic_id
	 where (postings.id is not null or forums.id is not null) and
	       (messages.hidden<$hide or messages.sender_id=$userId) and
	       (messages.disabled<$hide or messages.sender_id=$userId) and
	       (forums.id is null or
	        (msgs.hidden<$hide or msgs.sender_id=$userId) and
	        (msgs.disabled<$hide or msgs.sender_id=$userId)) and
               (postings.id is null or (postings.grp & $grp)<>0 $tpf) and
               (forums.id is null or (posts.grp & $answers)<>0 $taf) $uf")
 or sqlbug('Ошибка SQL при получении информации о постингах');
$row=mysql_fetch_assoc($result);
$row['max_sent']=$row['max_sent']!='' ? strtotime($row['max_sent']) : 0;
return new PostingsInfo($row);
}

?>
