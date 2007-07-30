<?php
# @(#) $Id$

require_once('lib/dataobject.php');
require_once('lib/bug.php');
require_once('lib/track.php');
require_once('lib/sql.php');
require_once('lib/entries.php');
require_once('lib/postings.php');

class PostingsInfo
      extends DataObject
{
var $total;
var $max_sent;

function PostingsInfo($row)
{
parent::DataObject($row);
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
                            $user_id=0,$recursive=false,$asGuest=false)
{
$info=getPostingsInfo($grp,$topic_id,$answers,$user_id,$recursive,$asGuest);
return $info->getMaxSent();
}

function getInfoTopicFilter($topic_id=-1,$recursive=false)
{
if(is_array($topic_id))
  {
  $topicFilter='';
  $op='';
  foreach($topic_id as $id => $recursive)
         {
         $topicFilter.=" $op ".subtree('entries',$id,$recursive);
	 $op='or';
	 }
  $topicFilter=" and ($topicFilter)";
  }
else
  $topicFilter=$topic_id<0 ? '' : ' and '.subtree('entries',$topic_id,
                                                  $recursive);
return $topicFilter;
}

function getPostingsMessagesInfo($grp=GRP_ALL,$topic_id=-1,$user_id=0,
                                 $recursive=false,$asGuest=false)
{
if($grp==GRP_NONE)
  return new PostingsInfo(array());
$hide='and '.postingsPermFilter(PERM_READ,'entries',$asGuest);
$grpFilter='and '.grpFilter($grp,'grp');
$topicFilter=getInfoTopicFilter($topic_id,$recursive);
$userFilter=$user_id>0 ? " and user_id=$user_id " : '';
$result=sql('select count(*) as total,max(sent) as max_sent
	     from entries
	     where entry='.ENT_POSTING." $hide $grpFilter $topicFilter
	     $userFilter",
	    __FUNCTION__);
$row=mysql_fetch_assoc($result);
$row['max_sent']=$row['max_sent']!='' ? strtotime($row['max_sent']) : 0;
return new PostingsInfo($row);
}

function getPostingsAnswersInfo($grp=GRP_NONE,$topic_id=-1,$user_id=0,
                                $recursive=false,$asGuest=false)
{
if($grp==GRP_NONE)
  return new PostingsInfo(array());
$hide='and '.postingsPermFilter(PERM_READ,'entries',$asGuest);
$grpFilter='and '.grpFilter($grp,'grp');
$topicFilter=getInfoTopicFilter($topic_id,$recursive);
$userFilter=$user_id>0 ? " and user_id=$user_id " : '';
$result=sql('select max(last_answer) as max_sent
	     from entries
	     where entry='.ENT_POSTING." $hide $grpFilter $topicFilter
	     $userFilter",
	    __FUNCTION__);
$row=mysql_fetch_assoc($result);
$row['max_sent']=$row['max_sent']!='' ? strtotime($row['max_sent']) : 0;
$row['total']=0; // ����� ���������� ������� �� ������������, ������� ������
                 // ��� �������� �������
return new PostingsInfo($row);
}

function getPostingsInfo($grp=GRP_ALL,$topic_id=-1,$answers=GRP_NONE,
                         $user_id=0,$recursive=false,$asGuest=false)
{
$grp=grpArray($grp);
$msgInfo=getPostingsMessagesInfo($grp,$topic_id,$user_id,$recursive,$asGuest);
$ansInfo=getPostingsAnswersInfo($answers,$topic_id,$user_id,$recursive,$asGuest);
$info=new PostingsInfo(array('total' => $msgInfo->getTotal()+
					$ansInfo->getTotal(),
			     'max_sent' => max($msgInfo->getMaxSent(),
					       $ansInfo->getMaxSent())));
return $info;
}
?>
