<?php
# @(#) $Id$

require_once('conf/migdal.conf');

require_once('lib/dataobject.php');
require_once('lib/limitselect.php');
require_once('lib/utils.php');
require_once('lib/bug.php');
require_once('lib/alphabet.php');
require_once('lib/sort.php');
require_once('lib/sql.php');

class Prisoner
      extends DataObject
{
var $id;
var $name;
var $name_russian;
var $location;
var $ghetto_name;
var $sender_name;
var $sum;
var $search_data;

function Prisoner($row)
{
parent::DataObject($row);
}

function getId()
{
return $this->id;
}

function getName()
{
return $this->name;
}

function getNameRussian()
{
return $this->name_russian;
}

function getLocation()
{
return $this->location;
}

function getGhettoName()
{
return $this->ghetto_name;
}

function getSenderName()
{
return $this->sender_name;
}

function getSum()
{
return $this->sum;
}

function getSearchData()
{
return $this->search_data;
}

}

class PrisonerListIterator
      extends SelectIterator
{

function PrisonerListIterator($prefix,$sort=SORT_NAME)
{
$sortFields=array(SORT_NAME         => 'name',
		  SORT_NAME_RUSSIAN => 'name_russian',
		  SORT_LOCATION     => 'location',
		  SORT_GHETTO_NAME  => 'ghetto_name',
		  SORT_SENDER_NAME  => 'sender_name');
if($prefix!='')
  {
  $prefixS=addslashes($prefix);
  $sortField=@$sortFields[$sort]!='' ? $sortFields[$sort] : 'name';
  $fieldFilter="$sortField like '$prefixS%'";
  }
else
  $fieldFilter='';
$order=getOrderBy($sort, $sortFields);
parent::SelectIterator(
	'Prisoner',
	"select id,name,name_russian,location,ghetto_name,sender_name,sum,
	        search_data
	 from prisoners
	 where $fieldFilter
	 $order");
}

}

class PrisonerAlphabetIterator
      extends AlphabetIterator
{

function PrisonerAlphabetIterator($limit=0,$sort=SORT_NAME)
{
$fields=array(SORT_NAME         => 'name',
	      SORT_NAME_RUSSIAN => 'name_russian',
	      SORT_LOCATION     => 'location',
	      SORT_GHETTO_NAME  => 'ghetto_name',
	      SORT_SENDER_NAME  => 'sender_name');
$field=@$fields[$sort]!='' ? $fields[$sort] : 'name';
$order=getOrderBy($sort,$fields);
parent::AlphabetIterator("select left($field,@len@) as letter,1 as count
                          from prisoners
			  where $field<>'' and $field like '@prefix@%'
			  $order",$limit);
}

}

function getPrisonersSummary()
{
$result=sql("select count(*)
	     from prisoners",
	    __FUNCTION__);
return mysql_num_rows($result)>0 ? mysql_result($result,0,0) : 0;
}
?>
