<?php
# @(#) $Id$

require_once('lib/ctypes.php');

function byIdent($id,$byId='id',$byIdent='ident')
{
return (is_int($id) || $id!='' && c_digit($id)) ? "$byId=$id" 
                                                : "$byIdent='$id'";
}

function idByIdent($table,$ident)
{
if(is_int($ident) || $ident!='' && c_digit($ident))
  return $ident;
$result=mysql_query("select id
                     from $table
		     where ident='$ident'")
	     or die("Ошибка SQL при проверке наличия идентификатора в $table");
return mysql_num_rows($result)>0 ? mysql_result($result,0,0) : 0;
}

?>
