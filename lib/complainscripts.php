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
		     where id=$id")
             or die('Ошибка SQL при получении тела скрипта реакции на жалобу');
return mysql_num_rows($result)>0 ? mysql_result($result,0,0) : '';
}

function complainScriptExists($id)
{
$result=mysql_query("select id
                     from complain_scripts
		     where id=$id")
             or die('Ошибка SQL при проверке наличия скрипта реакции на жалобу');
return mysql_num_rows($result)>0;
}

function checkScriptToTypeBinding($script_id,$type_id)
{
$result=mysql_query("select ident
                     from complain_types
		          left join complain_scripts
			       on complain_types.ident
			          =complain_scripts.type_ident
				  or complain_scripts.type_ident=''
		     where complain_scripts.id=$script_id
		           and complain_types.id=$type_id")
     or die('Ошибка SQL при проверке соответствия скрипта реакции типу жалобы');
return mysql_num_rows($result)>0;
}
?>
