<?php
# @(#) $Id$

function byIdent($id,$byId='id',$byIdent='ident')
{
return (is_int($id) || $id!='' && ctype_digit($id,0,1)) ? "$byId=$id" 
                                                        : "$byIdent='$id'";
}

function idByIdent($table,$ident)
{
$result=mysql_query("select id
                     from $table
		     where ident='$ident'")
	     or die("������ SQL ��� �������� ������� �������������� � $table");
return mysql_num_rows($result)>0 ? mysql_result($result,0,0) : 0;
}

?>
