<?php
# @(#) $Id$

require_once('lib/ctypes.php');
require_once('lib/track.php');
require_once('lib/bug.php');
require_once('lib/cache.php');
require_once('lib/sql.php');

function isId($ident)
{
return is_int($ident) || $ident<0 || $ident!='' && c_digit($ident);
}

function idByIdent($table,$ident)
{
if(isId($ident))
  return $ident;
if($ident=='')
  return 0;
if(hasCachedValue('ident',$table,$ident))
  return getCachedValue('ident',$table,$ident);
dbOpen();
$result=sql("select id
	     from $table
	     where ident='$ident'",
	    'idByIdent');
$id=mysql_num_rows($result)>0 ? mysql_result($result,0,0) : 0;
setCachedValue('ident',$table,$ident,$id);
return $id;
}

function identById($table,$id)
{
if(!isId($id))
  return $id;
dbOpen();
$result=sql("select ident
	     from $table
	     where id=$id",
	    'identById');
return mysql_num_rows($result)>0 ? mysql_result($result,0,0) : 0;
}
?>
