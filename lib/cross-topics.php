<?php
# @(#) $Id$

require_once('lib/dataobject.php');
require_once('lib/postings.php');

class CrossTopic
      extends DataObject
{
var $topic_id;
var $topic_name;
var $topic_grp;
var $peer_id;
var $peer_name;
var $peer_grp;

function CrossTopic($row)
{
$this->DataObject($row);
}

function getTopicId()
{
return $this->topic_id;
}

function getTopicName()
{
return $this->topic_name;
}

function getTopicGrp()
{
return $this->topic_grp;
}

function getTopicPosting()
{
$row=array('topic_id'   => $this->topic_id,
           'topic_name' => $this->topic_name,
	   'grp'        => $this->topic_grp);
return newPosting($row);
}

function getPeerId()
{
return $this->peer_id;
}

function getPeerName()
{
return $this->peer_name;
}

function getPeerGrp()
{
return $this->peer_grp;
}

function getPeerPosting()
{
$row=array('topic_id'   => $this->peer_id,
           'topic_name' => $this->peer_name,
	   'grp'        => $this->peer_grp);
return newPosting($row);
}

}

class CrossTopicIterator
      extends SelectIterator
{

function CrossTopicIterator($topic_id,$peer_grp=GRP_ALL,$topic_grp=GRP_ALL)
{
$grpFilter='';
$grpFilter.=" and ".grpFilter($topic_grp,'topic_grp');
$grpFilter.=" and ".grpFilter($peer_grp,'peer_grp');
$this->SelectIterator('CrossTopic',
                      "select topic_id,topic_grp,peer_id,name as peer_name,
		              peer_grp
		       from cross_topics
		            left join topics
			         on topics.id=cross_topics.peer_id
		       where topic_id=$topic_id $grpFilter
		       order by track");
}

}
?>
