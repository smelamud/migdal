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

function MenuItem($row)
{
$this->DataObject($row);
}

}

class MenuIterator
      extends SelectIterator
{

function MenuIterator()
{
$this->SelectIterator('MenuItem',
                      'select *
		       from menu
		       where hidden=0
		       order by menu_index');
}

}
?>
