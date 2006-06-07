<?php
# @(#) $Id$

require_once('lib/dataobject.php');
require_once('lib/selectiterator.php');

require_once('grp/cross-entries.php');

define('LINKT_NONE',0);

class CrossEntry
      extends DataObject
{
var $id;
var $source_name=null;
var $source_id=null;
var $link_type;
var $peer_name=null;
var $peer_id=null;
var $peer_path;
var $peer_subject;
var $peer_icon;

function CrossEntry($row)
{
parent::DataObject($row);
}

function getId()
{
return $this->id;
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
                      "select id,source_name,source_id,link_type,peer_name,
		              peer_id,peer_path,peer_subject,peer_icon
		       from cross_entries
		       where 1 $filter
		       order by peer_icon,peer_subject_sort");
}

}

function storeCrossEntry(&$cross)
{
$jencoded=array('source_name' => '','source_id' => 'entries','peer_name' => '',
                'peer_id' => 'entries','peer_path' => '','peer_subject' => '',
		'peer_subject_sort' => '','peer_icon' => '');
$vars=array('source_name' => $cross->source_name,
            'source_id' => $cross->source_id,
            'link_type' => $cross->link_type,
            'peer_name' => $cross->peer_name,
            'peer_id' => $cross->peer_id,
            'peer_path' => $cross->peer_path,
            'peer_subject' => $cross->peer_subject,
            'peer_subject_sort' => $cross->peer_subject_sort,
            'peer_icon' => $cross->peer_icon);
if($cross->id)
  {
  $result=sql(makeUpdate('cross_entries',
                         $vars,
			 array('id' => $cross->id)),
	      __FUNCTION__,'update');
  journal(makeUpdate('cross_entries',
                     jencodeVars($vars,$jencoded),
		     array('id' => journalVar('cross_entries',$cross->id))));
  }
else
  {
  $result=sql(makeInsert('cross_entries',
                         $vars),
	      __FUNCTION__,'insert');
  $cross->id=sql_insert_id();
  journal(makeInsert('cross_entries',
                     jencodeVars($vars,$jencoded)),
	  'cross_entries',$cross->id);
  }
return $result;
}

function deleteCrossEntry($id)
{
sql("delete
     from cross_entries
     where id=$id",
    __FUNCTION__);
journal('delete
	 from cross_entries
	 where id='.journalVar('cross_entries',$id),
	__FUNCTION__);
}
?>
