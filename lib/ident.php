<?php
# @(#) $Id$

function byIdent($id)
{
return ($id!='' && ctype_digit($id[0])) ? "id=$id" : "ident='$id'";
}

function idByIdent($table,$ident)
{
$result=mysql_query("select id
                     from $table
		     where ident='$ident'")
	     or die("Ошибка SQL при проверке наличия идентификатора в $table");
return mysql_num_rows($result)>0 ? mysql_result($result,0,0) : 0;
}

?>
