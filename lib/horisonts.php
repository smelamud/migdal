<?php
# @(#) $Id$

require_once('lib/journal.php');

define('HOR_WE_KNOW',true);
define('HOR_THEY_KNOW',false);

function getHorisont($host,$weKnow)
{
$result=mysql_query('select '.($weKnow ? 'we_know' : 'they_know')."
                     from horisonts
		     where host='$host'")
  or journalFailure('Cannot get horisont.');
return mysql_num_rows($result)>0 ? mysql_result($result,0,0) : 0;
}

function setHorisont($host,$horisont,$weKnow)
{
$host=addslashes($host);
$result=mysql_query("select host
                     from horisonts
		     where host='$host'")
  or journalFailure('Cannot select horisont.');
if(mysql_num_rows($result)<=0)
  mysql_query('insert into horisonts(host,'.($weKnow ? 'we_know'
                                                     : 'they_know').")
			      values('$host',$horisont)")
    or journalFailure('Cannot insert horisont.');
else
  mysql_query('update horisonts
               set '.($weKnow ? 'we_know' : 'they_know')."=$horisont
	       where host='$host'")
    or journalFailure('Cannot update horisont.');
}

function lockReplication($host)
{
mysql_query("update horisonts
             set `lock`=now()
	     where host='".addslashes($host)."'")
  or journalFailure('Cannot lock replication process.');
}

function unlockReplication($host)
{
mysql_query("update horisonts
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
global $replicationLockTimeout;

$result=mysql_query("select host
                     from horisonts
	             where host='".addslashes($host)."' and
		           `lock` is not null and
	                   `lock`+interval $replicationLockTimeout minute>now()")
  or journalFailure('Cannot get replication lock.');
return mysql_num_rows($result)>0;
}
?>
