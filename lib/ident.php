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

$idents=array();

function idByIdent($table,$ident)
{
global $idents;

if(isId($ident))
  return $ident;
if($ident=='')
  return 0;
if(isset($idents[$table]) && isset($idents[$table][$ident]))
  return $idents[$table][$ident];
dbOpen();
$result=mysql_query("select id
                     from $table
		     where ident='$ident'")
	  or sqlbug("Ошибка SQL при проверке наличия идентификатора в $table");
$id=mysql_num_rows($result)>0 ? mysql_result($result,0,0) : 0;
if(!isset($idents[$table]))
  $idents[$table]=array();
$idents[$table][$ident]=$id;
return $id;
}

function identById($table,$id)
{
if(!isId($id))
  return $id;
dbOpen();
$result=mysql_query("select ident
                     from $table
		     where id=$id")
	  or sqlbug("Ошибка SQL при проверке наличия обозначения в $table");
return mysql_num_rows($result)>0 ? mysql_result($result,0,0) : 0;
}
?>
