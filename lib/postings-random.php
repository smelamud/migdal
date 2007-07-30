<?php
# @(#) $Id$

require_once('lib/array.php');
require_once('lib/sql.php');
require_once('lib/postings.php');

function getRandomPostingIds($limit,$grp=GRP_ALL,$topic_id=-1,$user_id=0,
                             $index1=-1,$asGuest=false)
{
$hide='and '.postingsPermFilter(PERM_READ,'entries',$asGuest);
$grpFilter=grpFilter($grp);
$topicFilter=$topic_id>=0 ? "and parent_id=$topic_id " : '';
$userFilter=$user_id>0 ? " and user_id=$user_id " : '';
$index1Filter=$index1>=0 ? "and index1=$index1" : '';
$result=sql("select priority,count(*)
	     from entries
	     where entry=".ENT_POSTING." $hide and priority<=0 and $grpFilter
	           $topicFilter $userFilter $index1Filter
	     group by priority
	     order by priority",
	    __FUNCTION__,'calculate');

$counts=array();
$total=0;
$ptotal=0;
while($row=mysql_fetch_row($result))
     {
     $row[2]=(1-$row[0])*$row[1];
     $counts[]=$row;
     $total+=$row[2];
     $ptotal+=$row[1];
     }

if($limit>$ptotal)
  $limit=$ptotal;
if($limit==0)
  return array();

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
       $result=sql("select id
		    from entries
		    where entry=".ENT_POSTING." $hide and priority<=0
		          and $grpFilter $topicFilter $userFilter $index1Filter
		    order by priority,sent desc
		    limit $pos,1",
		   __FUNCTION__,'get_id');
       $ids[]=mysql_num_rows($result)>0 ? mysql_result($result,0,0) : 0;
       }

return $ids;
}

function getRandomPostingId($grp=GRP_ALL,$topic_id=-1,$user_id=0,$index1=-1,
                            $asGuest=false)
{
$ids=getRandomPostingIds(1,$grp,$topic_id,$user_id,$index1,$asGuest);
return count($ids)>0 ? (int)$ids[0] : 0;
}

class RandomPostingsIterator
      extends MArrayIterator
{

function RandomPostingsIterator($limit,$grp=GRP_ALL,$topic_id=-1,$user_id=0,
                                $index1=-1,$asGuest=false)
{
parent::MArrayIterator(getRandomPostingIds($limit,$grp,$topic_id,$user_id,
                                           $index1,$asGuest));
}

}
?>
