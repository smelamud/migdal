<?php
# @(#) $Id$

require_once('lib/dataobject.php');
require_once('lib/selectiterator.php');

class ComplainScript
      extends DataObject
{
var $id;
var $name;
var $script;
var $type_ident;

function ComplainScript($row)
{
$this->DataObject($row);
}

function getId()
{
return $this->id;
}

function getName()
{
return $this->name;
}

function getScript()
{
return $this->script;
}

function getTypeIdent()
{
return $this->type_ident;
}

}

class ComplainScriptListIterator
      extends SelectIterator
{

function ComplainScriptListIterator($ident)
{
$this->SelectIterator('ComplainScript',
                      "select id,name
		       from complain_scripts
		       where type_ident='$ident' or type_ident=''");
}

}

function getScriptBodyById($id)
{
$result=mysql_query("select script
		     from complain_scripts
		     where id=$id");
if(!$result)
  die('Ошибка SQL при получении тела скрипта реакции на жалобу');
return mysql_num_rows($result)>0 ? mysql_result($result,0,0) : '';
}
?>
