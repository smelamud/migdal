<?php
# @(#) $Id$

require_once('lib/bug.php');
require_once('lib/journal.php');

define('MOD_NONE',0x0000);
define('MOD_MODERATE',0x0001);
define('MOD_HTML',0x0002);
define('MOD_ALL',0xffff);

function getModbitsByMessageId($id)
{
$result=mysql_query("select modbits
                     from messages
		     where id=$id")
          or sqlbug('Ошибка SQL при выборке флагов модерирования');
return mysql_num_rows($result)>0 ? mysql_result($result,0,0) : 0;
}

function setModbitsByMessageId($id,$bits)
{
mysql_query("update messages
             set modbits=modbits | $bits
	     where id=$id")
  or sqlbug('Ошибка SQL при установке флагов модерирования');
journal("update messages
         set modbits=modbits | $bits
	 where id=".journalVar('messages',$id));
}

function resetModbitsByMessageId($id,$bits)
{
mysql_query("update messages
             set modbits=modbits & ~$bits
	     where id=$id")
  or sqlbug('Ошибка SQL при сбросе флагов модерирования');
journal("update messages
         set modbits=modbits & ~$bits
	 where id=".journalVar('messages',$id));
}
?>
