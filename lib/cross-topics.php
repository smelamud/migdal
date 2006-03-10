<?php
# @(#) $Id$

require_once('lib/dataobject.php');
require_once('lib/postings.php');

class CrossTopic
      extends DataObject
{
var $source_id;
var $source_subject;
var $source_grp;
var $peer_id;
var $peer_subject;
var $peer_grp;

function CrossTopic($row)
{
parent::DataObject($row);
}

function getTopicId()
{
return $this->source_id;
}

function getTopicName()
{
return $this->source_subject;
}

function getTopicGrp()
{
return $this->source_grp;
}

function getTopicPosting()
{
$row=array('parent_id'     => $this->source_id,
           'topic_subject' => $this->source_subject,
	   'grp'           => $this->source_grp);
return new Posting($row);
}

function getPeerId()
{
return $this->peer_id;
}

function getPeerName()
{
return $this->peer_subject;
}

function getPeerGrp()
{
return $this->peer_grp;
}

function getPeerPosting()
{
$row=array('parent_id'     => $this->peer_id,
           'topic_subject' => $this->peer_subject,
	   'grp'           => $this->peer_grp);
return new Posting($row);
}

}

class CrossTopicIterator
      extends SelectIterator
{

function CrossTopicIterator($topic_id,$peer_grp=GRP_ALL,$topic_grp=GRP_ALL)
{
$grpFilter='';
$grpFilter.=" and ".grpFilter($topic_grp,'source_grp');
$grpFilter.=" and ".grpFilter($peer_grp,'peer_grp');
$this->SelectIterator('CrossTopic',
                      "select source_id,source_grp,peer_id,
		              subject as peer_subject,peer_grp
		       from cross_entries
		            left join entries
			         on entries.id=cross_entries.peer_id
		       where source_id=$topic_id $grpFilter
		       order by subject_sort");
}

}
?>
