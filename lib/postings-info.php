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
                            $user_id=0,$recursive=false)
{
$info=getPostingsInfo($grp,$topic_id,$answers,$user_id,$recursive);
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
                                 $recursive=false)
{
if($grp==GRP_NONE)
  return new PostingsInfo();
$hide='and '.postingsPermFilter(PERM_READ);
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
                                $recursive=false)
{
if($grp==GRP_NONE)
  return new PostingsInfo();
$hide='and '.postingsPermFilter(PERM_READ);
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
$row['total']=0; // Общее количество ответов не используется, поэтому убрано
                 // для экономии времени
return new PostingsInfo($row);
}

function getPostingsInfo($grp=GRP_ALL,$topic_id=-1,$answers=GRP_NONE,
                         $user_id=0,$recursive=false)
{
if(!is_array($topic_id))
  {
  $info=loadPostingsInfoCache($grp,$topic_id,$answers,$user_id,$recursive);
  if($info)
    return $info;
  }
$grp=grpArray($grp);
$msgInfo=getPostingsMessagesInfo($grp,$topic_id,$user_id,$recursive);
$ansInfo=getPostingsAnswersInfo($answers,$topic_id,$user_id,$recursive);
$info=new PostingsInfo(array('total' => $msgInfo->getTotal()+
					$ansInfo->getTotal(),
			     'max_sent' => max($msgInfo->getMaxSent(),
					       $ansInfo->getMaxSent())));
if(!is_array($topic_id))
  storePostingsInfoCache($grp,$topic_id,$answers,$user_id,$recursive,$info);
return $info;
}

function loadPostingsInfoCache($grp,$topic_id,$answers,$user_id,$recursive)
{
/*
global $userId;

$recursive=$recursive ? 1 : 0;
$result=sql("select total,max_sent
	     from postings_info
	     where reader_id=$userId and grp=$grp
		   and topic_id=$topic_id and answers=$answers
		   and user_id=$user_id and recursive=$recursive",
	    'loadPostingsInfoCache');
return mysql_num_rows($result)>0 ? new PostingsInfo(mysql_fetch_assoc($result))
                                 : 0;
*/
// FIXME postings info cache
return 0;
}

function storePostingsInfoCache($grp,$topic_id,$answers,$user_id,$recursive,
                                $info)
{
/*
global $userId;

$recursive=$recursive ? 1 : 0;
$total=$info->getTotal();
$max_sent=$info->getMaxSent();
sql("insert into postings_info(reader_id,grp,topic_id,answers,user_id,
			       recursive,total,max_sent)
     values($userId,$grp,$topic_id,$answers,$user_id,
	    $recursive,$total,$max_sent)",
    '');
// На ошибку не проверяем, чтобы избежать race condition
*/
// FIXME postings info cache
}

define('DPIC_NONE',0);
define('DPIC_POSTINGS',1);
define('DPIC_FORUMS',2);
define('DPIC_BOTH',3);

function dropPostingsInfoCache($flag=DPIC_BOTH)
{
/*
if($flag==DPIC_NONE)
  return;
$cond=$flag==DPIC_POSTINGS ? 'grp<>0'
      : ($flag==DPIC_FORUMS ? 'answers<>0'
      : '1' );
sql("delete from postings_info
     where $cond",
    'dropPostingsInfoCache');
*/
// FIXME postings info cache
}
?>
