<?php
# @(#) $Id$

require_once('lib/dataobject.php');
require_once('lib/selectiterator.php');
require_once('lib/bug.php');

class Rating
      extends DataObject
{
var $id;
var $ident;
var $regid;
var $url;

function Rating($row)
{
$this->DataObject($row);
}

function getId()
{
return $this->id;
}

function getIdent()
{
return $this->ident;
}

function getRegId()
{
return $this->regid;
}

function getURL()
{
return $this->url;
}

}

class RatingsListIterator
      extends SelectIterator
{

function RatingsListIterator()
{
$this->SelectIterator('Rating',
                      'select id,ident,regid,url
                       from ratings');
}

}

function addRatingPosition($id,$topicDay,$topicWeek,$globalDay,$globalWeek)
{
mysql_query("insert into rating_positions(rating_id,scan_date,topic_day,
                                          topic_week,global_day,global_week)
	     values($id,CURRENT_DATE(),$topicDay,$topicWeek,
	                               $globalDay,$globalWeek)")
  or sqlbug('Ошибка SQL при добавлении позиции в рейтинге');
}

?>
