<?php
# @(#) $Id$

require_once('grp/counters.php');
require_once('lib/ip.php');

function storeCounterIP($id,$mode)
{
global $counterModes,$REMOTE_ADDR;

$period=$counterModes[$mode]['period'];
if($period<=0)
  return;
$ip=IPToInteger($REMOTE_ADDR);
mysql_query("insert into counters_ip(counter_id,ip,expires)
             values($id,$ip,now()+interval $period hour)")
  or sqlbug('Ошибка SQL при сохранении IP для счетчика');
}

function hasCounterIP($id,$mode)
{
global $counterModes,$REMOTE_ADDR;

$period=$counterModes[$mode]['period'];
if($period<=0)
  return false;
$ip=IPToInteger($REMOTE_ADDR);
$result=mysql_query("select counter_id
                     from counters_ip
		     where counter_id=$id and ip=$ip")
          or sqlbug('Ошибка SQL при проверке IP для счетчика');
return mysql_num_rows($result)>0;
}

function incCounter($message_id,$mode)
{
$result=mysql_query("select id
                     from counters
		     where message_id=$message_id and mode=$mode and serial=0")
          or sqlbug('Ошибка SQL при получении текущего счетчика');
if(mysql_num_rows($result)<=0)
  return;
$id=mysql_result($result,0,0);
if(hasCounterIP($id,$mode))
  return;
mysql_query("update counters
             set value=value+1
	     where id=$id")
  or sqlbug('Ошибка SQL при увеличении счетчика');
journal('update counters
         set value=value+1
	 where id='.journalVar('counters',$id));
storeCounterIP($id,$mode);
}

function rotateCounter($message_id,$mode)
{
global $counterModes;

mysql_query("update counters
             set serial=serial+1
	     where message_id=$message_id and mode=$mode")
  or sqlbug('Ошибка SQL при ротации счетчиков');
journal('update counters
         set serial=serial+1
         where message_id='.journalVar('messages',$message_id).
	     " and mode=$mode");

$max_serial=$counterModes[$mode]['max_serial'];
if($max_serial>=0)
  {
  mysql_query("delete from counters
	       where message_id=$message_id and mode=$mode
		     and serial>$max_serial")
    or sqlbug('Ошибка SQL при удалении лишних счетчиков');
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
mysql_query("insert into counters(message_id,mode,started,finished)
             values($message_id,$mode,'$started','$finished')")
  or sqlbug('Ошибка SQL при создании нового счетчика');
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
$result=mysql_query("select value
                     from counters
		     where message_id=$message_id and mode=$mode
		           and serial=$serial")
          or sqlbug('Ошибка SQL при получении значения счетчика');
return mysql_num_rows($result)>0 ? mysql_result($result,0,0) : 0;
}
?>
