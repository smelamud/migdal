<?php
# @(#) $Id$

require_once('lib/dataobject.php');
require_once('lib/selectiterator.php');

class MenuItem
      extends DataObject
{
var $id;
var $name;
var $link;
var $hidden;
var $menu_index;
var $current;

function MenuItem($row)
{
$this->DataObject($row);
}

function getName()
{
return $this->name;
}

function getLink()
{
return $this->link;
}

function isCurrent()
{
return $this->current;
}

}

class MenuIterator
      extends SelectIterator
{

function MenuIterator($current)
{
$this->SelectIterator('MenuItem',
                      'select *
		       from menu
		       where hidden=0
		       order by menu_index');
}

}
?>
