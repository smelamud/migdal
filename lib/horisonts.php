<?php
# @(#) $Id$

require_once('lib/journal.php');
require_once('lib/dataobject.php');
require_once('lib/selectiterator.php');

define('HOR_WE_KNOW',true);
define('HOR_THEY_KNOW',false);

function getHorisont($host,$weKnow)
{
global $dbName;

$result=mysql_query('select '.($weKnow ? 'we_know' : 'they_know')."
                     from $dbName.horisonts
		     where host='$host'")
  or journalFailure('Cannot get horisont.');
return mysql_num_rows($result)>0 ? mysql_result($result,0,0) : 0;
}

function setHorisont($host,$horisont,$weKnow)
{
global $dbName;

$host=addslashes($host);
$result=mysql_query("select host
                     from $dbName.horisonts
		     where host='$host'")
  or journalFailure('Cannot select horisont.');
if(mysql_num_rows($result)<=0)
  mysql_query("insert into $dbName.horisonts(host,".($weKnow ? 'we_know'
                                                     : 'they_know').")
			           values('$host',$horisont)")
    or journalFailure('Cannot insert horisont.');
else
  mysql_query("update $dbName.horisonts
               set ".($weKnow ? 'we_know' : 'they_know')."=$horisont
	       where host='$host'")
    or journalFailure('Cannot update horisont.');
}

function lockReplication($host)
{
global $dbName;

mysql_query("update $dbName.horisonts
             set `lock`=now()
	     where host='".addslashes($host)."'")
  or journalFailure('Cannot lock replication process.');
}

function unlockReplication($host)
{
global $dbName;

mysql_query("update $dbName.horisonts
             set `lock`=null
	     where host='".addslashes($host)."'")
  or journalFailure('Cannot unlock replication process.');
}

function updateReplicationLock($host)
{
lockReplication($host);
}

function isReplicationLocked($host)
{
global $replicationLockTimeout,$dbName;

$result=mysql_query("select host
                     from $dbName.horisonts
	             where host='".addslashes($host)."' and
		           `lock` is not null and
	                   `lock`+interval $replicationLockTimeout minute>now()")
  or journalFailure('Cannot get replication lock.');
return mysql_num_rows($result)>0;
}

class Horisont
      extends DataObject
{
var $host;
var $we_know;
var $they_know;

function Horisont($row)
{
$this->DataObject($row);
}

function getHost()
{
return $this->host;
}

function getWeKnow()
{
return $this->we_know;
}

function getTheyKnow()
{
return $this->they_know;
}

function setup($vars)
{
if(!isset($vars['edittag']))
  return;
$this->we_know=$vars['we_know'];
$this->they_know=$vars['they_know'];
}

function store()
{
setHorisont($this->host,$this->we_know,HOR_WE_KNOW);
setHorisont($this->host,$this->they_know,HOR_THEY_KNOW);
}

}

class HorisontsIterator
      extends SelectIterator
{

function HorisontsIterator()
{
global $dbName;

$this->SelectIterator('Horisont',
                      "select host,we_know,they_know
		       from $dbName.horisonts
		       order by host");
}

}

function getHorisontByHost($host)
{
global $dbName;

$result=mysql_query("select host,we_know,they_know
	             from $dbName.horisonts
		     where host='".addslashes($host)."'")
          or sqlbug('Ошибка SQL при выборке горизонта');
return new Horisont(mysql_num_rows($result)>0 ? mysql_fetch_assoc($result)
                                              : array());
}
?>
