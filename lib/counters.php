<?php
# @(#) $Id$

require_once('grp/counters.php');
require_once('lib/ip.php');
require_once('lib/sql.php');

function storeCounterIP($id,$mode)
{
global $counterModes,$REMOTE_ADDR;

$period=$counterModes[$mode]['period'];
if($period<=0)
  return;
$ip=IPToInteger($REMOTE_ADDR);
sql("insert into counters_ip(counter_id,ip,expires)
     values($id,$ip,now()+interval $period hour)",
    'storeCounterIP');
}

function hasCounterIP($id,$mode)
{
global $counterModes,$REMOTE_ADDR;

$period=$counterModes[$mode]['period'];
if($period<=0)
  return false;
$ip=IPToInteger($REMOTE_ADDR);
$result=sql("select counter_id
	     from counters_ip
	     where counter_id=$id and ip=$ip",
	    'hasCounterIP');
return mysql_num_rows($result)>0;
}

function incCounter($message_id,$mode)
{
$result=sql("select id
	     from counters
	     where message_id=$message_id and mode=$mode and serial=0",
	    'incCounter','select');
if(mysql_num_rows($result)<=0)
  return;
$id=mysql_result($result,0,0);
if(hasCounterIP($id,$mode))
  return;
sql("update counters
     set value=value+1
     where id=$id",
    'incCounter','increment');
journal('update counters
         set value=value+1
	 where id='.journalVar('counters',$id));
storeCounterIP($id,$mode);
}

function rotateCounter($message_id,$mode)
{
global $counterModes;

sql("update counters
     set serial=serial+1
     where message_id=$message_id and mode=$mode",
    'rotateCounter','rotate');
journal('update counters
         set serial=serial+1
         where message_id='.journalVar('messages',$message_id).
	     " and mode=$mode");

$max_serial=$counterModes[$mode]['max_serial'];
if($max_serial>=0)
  {
  sql("delete from counters
       where message_id=$message_id and mode=$mode
	     and serial>$max_serial",
      'rotateCounter','delete');
  journal('delete from counters
	   where message_id='.journalVar('messages',$message_id).
	       " and mode=$mode and serial>$max_serial");
  }

$started=date("Y-m-d H:i:s");
$ttl=$counterModes[$mode]['ttl'];
if($ttl!=0)
  $finished=date("Y-m-d H:i:s",time()+$ttl*60*60);
else
  $finished="2100-01-01 00:00:00";
  // Leave solution of this problem to next generations of programmers ;)
sql("insert into counters(message_id,mode,started,finished)
     values($message_id,$mode,'$started','$finished')",
    'rotateCounter','create');
journal('insert into counters(message_id,mode,started,finished)
         values('.journalVar('messages',$message_id).",$mode,
	         '$started','$finished')");
}

function createCounters($message_id,$grp)
{
global $counterModes;

foreach($counterModes as $mode => $info)
       if(($info['grp'] & $grp)!=0)
         rotateCounter($message_id,$mode);
}

function getCounterValue($message_id,$mode,$serial=0)
{
$result=sql("select value
	     from counters
	     where message_id=$message_id and mode=$mode
		   and serial=$serial",
	    'getCounterValue');
return mysql_num_rows($result)>0 ? mysql_result($result,0,0) : 0;
}
?>
