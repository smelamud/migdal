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

function MenuItem($row,$aCurrent=0)
{
$this->DataObject($row);
$this->current=is_integer($aCurrent) ? $this->id==$aCurrent
                                     : $this->ident==$aCurrent;
}

function setup($vars)
{
if(!isset($vars['edittag']))
  return;
foreach($this->getCorrespondentVars() as $var)
       $this->$var=htmlspecialchars($vars[$var],ENT_QUOTES);
}

function getCorrespondentVars()
{
return array('name','ident','link','hidden');
}

function getWorldVars()
{
return array('name','ident','link','hidden','menu_index');
}

function store()
{
$normal=$this->getNormal();
$result=mysql_query($this->id 
                    ? makeUpdate('menu',$normal,array('id' => $this->id))
                    : makeInsert('menu',$normal));
if(!$this->id)
  $this->id=mysql_insert_id();
return $result;
}

function getId()
{
return $this->id;
}

function getName()
{
return $this->name;
}

function getIdent()
{
return $this->ident;
}

function getLink()
{
return $this->link;
}

function isHidden()
{
return $this->hidden;
}

function getMenuIndex()
{
return $this->menu_index;
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
global $userAdminMenu;

$hide=$userAdminMenu ? 2 : 1;
$this->SelectIterator('MenuItem',
                      "select *
		       from menu
		       where hidden<$hide
		       order by menu_index");
$this->current=$aCurrent;
}

function create($row)
{
return new MenuItem($row,$this->current);
}

}

function getMenuItemById($id=0)
{
$result=mysql_query("select id,name,ident,link,hidden,menu_index
                     from menu
		     where id=$id")
	     or die('Ошибка SQL при выборке пункта меню');
if(mysql_num_rows($result)>0)
  return new MenuItem(mysql_fetch_assoc($result));
else
  {
  $result=mysql_query('select max(menu_index)+1 as menu_index
                       from menu');
  return new MenuItem(mysql_fetch_assoc($result));
  }
}

function menuIdentExists($ident)
{
$result=mysql_query("select id
                     from menu
		     where ident='$ident'")
	     or die('Ошибка SQL при выборке идентификатора пункта меню');
return mysql_num_rows($result)>0;
}
?>
