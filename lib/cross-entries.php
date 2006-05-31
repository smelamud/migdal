<?php
# @(#) $Id$

require_once('lib/dataobject.php');
require_once('lib/selectiterator.php');

require_once('grp/cross-entries.php');

define('LINKT_NONE',0);

class CrossEntry
      extends DataObject
{
var $source_name;
var $source_id;
var $link_type;
var $peer_name;
var $peer_id;
var $peer_path;
var $peer_subject;
var $peer_icon;

function CrossEntry($row)
{
parent::DataObject($row);
}

function getSourceName()
{
return $this->source_name;
}

function getSourceId()
{
return $this->source_id;
}

function getLinkType()
{
return $this->link_type;
}

function getPeerName()
{
return $this->peer_name;
}

function getPeerId()
{
return $this->peer_id;
}

function getPeerPath()
{
return $this->peer_path;
}

function getPeerSubject()
{
return $this->peer_subject;
}

function getPeerIcon()
{
return $this->peer_icon;
}

}

class CrossEntryIterator
      extends SelectIterator
{

function CrossEntryIterator($source_name='',$source_id=0,$link_type=LINKT_NONE)
{
$filter='';
if($source_name!='')
  $filter.=" and source_name='$source_name'";
if($source_id>0)
  $filter.=" and source_id=$source_id";
if($link_type!=LINKT_NONE)
  $filter.=" and link_type=$link_type";
$this->SelectIterator('CrossEntry',
                      "select source_name,source_id,link_type,peer_name,
		              peer_id,peer_path,peer_subject,peer_icon
		       from cross_entries
		       where 1 $filter
		       order by peer_icon,peer_subject_sort");
}

}
?>
