<?php
# @(#) $Id$

require_once('lib/calendar.php');

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
global $rusMonthRL;

$month=isset($rusMonthRL[$this->current]) ?
       new Month($this->current,$rusMonthRL[$this->current]) : 0;
$this->current++;
return $month;
}

}
?>
