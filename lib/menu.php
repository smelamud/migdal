<?php
# @(#) $Id$

require_once('lib/dataobject.php');
require_once('lib/selectiterator.php');

class MenuItem
      extends DataObject
{
var $id;
var $name;
var $ident;
var $link;
var $hidden;
var $menu_index;
var $current;

function MenuItem($row,$aCurrent)
{
$this->DataObject($row);
$this->current=$this->ident==$aCurrent;
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
var $current;

function MenuIterator($aCurrent)
{
$this->SelectIterator('MenuItem',
                      'select *
		       from menu
		       where hidden=0
		       order by menu_index');
$this->current=$aCurrent;
}

function create($row)
{
return new MenuItem($row,$this->current);
}

}
?>
