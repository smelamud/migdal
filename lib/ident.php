<?php
# @(#) $Id$

require_once('lib/ctypes.php');
require_once('lib/track.php');
require_once('lib/bug.php');

function isId($ident)
{
return is_int($ident) || $ident<0 || $ident!='' && c_digit($ident);
}

function byIdent($id,$byId='id',$byIdent='ident')
{
return isId($id) ? "$byId=$id" : "$byIdent='$id'";
}

function byIdentRecursive($table,$id,$recursive,$byId='id',
                          $byIdent='ident',$byTrack='track')
{
return !$recursive ? isId($id) ? "$byId=$id" : "$byIdent='$id'"
                   : $byTrack." like '%".track(idByIdent($table,$id))."%'";
}

function idByIdent($table,$ident)
{
if(isId($ident))
  return $ident;
$result=mysql_query("select id
                     from $table
		     where ident='$ident'")
	  or sqlbug("������ SQL ��� �������� ������� �������������� � $table");
return mysql_num_rows($result)>0 ? mysql_result($result,0,0) : 0;
}

function identById($table,$id)
{
if(!isId($id))
  return $id;
$result=mysql_query("select ident
                     from $table
		     where id=$id")
	  or sqlbug("������ SQL ��� �������� ������� ����������� � $table");
return mysql_num_rows($result)>0 ? mysql_result($result,0,0) : 0;
}
?>
