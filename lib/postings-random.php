<?php
# @(#) $Id$

require_once('lib/array.php');

function getRandomPostingIds($limit,$grp=GRP_ALL,$topic_id=-1,$user_id=0,
                             $index1=-1)
{
$hide=messagesPermFilter(PERM_READ);
$grpFilter=grpFilter($grp);
$topicFilter=$topic_id>=0 ? " and topic_id=$topic_id " : '';
$userFilter=$user_id<=0 ? '' : " and messages.sender_id=$user_id ";
$index1Filter=$index1>=0 ? "and postings.index1=$index1" : '';
$result=mysql_query(
        "select priority,count(*)
         from postings
	      left join messages
	           on postings.message_id=messages.id
	 where $hide and priority<=0 and $grpFilter $topicFilter $userFilter
	       $index1Filter
	 group by priority
	 order by priority")
 or sqlbug('Ошибка SQL при определении количества постингов по приоритетам');

$ptotal=mysql_num_rows($result);
if($limit>$ptotal)
  $limit=$ptotal;
if($limit==0)
  return array();

$counts=array();
$total=0;
while($row=mysql_fetch_row($result))
     {
     $row[2]=(1-$row[0])*$row[1];
     $counts[]=$row;
     $total+=$row[2];
     }

$positions=array();
while(count($positions)<$limit)
     {
     $pos=random(0,$total-1);
     $realpos=0;
     foreach($counts as $c)
	    if($pos>=$c[2])
	      {
	      $pos-=$c[2];
	      $realpos+=$c[1];
	      }
	    else
	      {
	      $realpos+=(int)($pos/(1-$c[0]));
	      break;
	      }
     if(!in_array($realpos,$positions))
       $positions[]=$realpos;
     }

$ids=array();
foreach($positions as $pos)
       {
       $result=mysql_query(
	       "select postings.id
		from postings
		     left join messages
			  on postings.message_id=messages.id
		where $hide and priority<=0 and $grpFilter $topicFilter
		      $userFilter $index1Filter
		order by priority,sent desc
		limit $pos,1")
	or sqlbug('Ошибка SQL при получении идентификатора постинга по позиции');
       $ids[]=mysql_num_rows($result)>0 ? mysql_result($result,0,0) : 0;
       }

return $ids;
}

function getRandomPostingId($grp=GRP_ALL,$topic_id=-1,$user_id=0,$index1=-1)
{
list($id)=getRandomPostingIds(1,$grp,$topic_id,$user_id,$index1);
return (int)$id;
}

class RandomPostingsIterator
      extends ArrayIterator
{

function RandomPostingsIterator($limit,$grp=GRP_ALL,$topic_id=-1,$user_id=0,
                                $index1=-1)
{
$this->ArrayIterator(getRandomPostingIds($limit,$grp,$topic_id,$user_id,
                                         $index1));
}

}
?>
