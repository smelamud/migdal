<?php
# @(#) $Id$

$months=array(1 => 'января','февраля','марта','апреля','мая','июня',
                   'июля','августа','сентября','октября','ноября','декабря');

function getRussianMonth($month)
{
global $months;

return $months[$month];
}

class Month
{
var $id;
var $name;

function Month($id,$name)
{
$this->id=$id;
$this->name=$name;
}

function getId()
{
return $this->id;
}

function getName()
{
return $this->name;
}

}

class MonthsIterator
{
var $current;

function MonthsIterator()
{
$this->current=1;
}

function next()
{
global $months;

$month=isset($months[$this->current]) ?
       new Month($this->current,$months[$this->current]) : 0;
$this->current++;
return $month;
}

}
?>
