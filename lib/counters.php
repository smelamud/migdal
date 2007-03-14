<?php
# @(#) $Id$

require_once('conf/counters.php');
require_once('lib/ip.php');
require_once('lib/sql.php');

function storeCounterIP($id,$mode)
{
global $counterModes;

$period=$counterModes[$mode]['period'];
if($period<=0)
  return;
$ip=IPToInteger($_SERVER['REMOTE_ADDR']);
sql("insert into counters_ip(counter_id,ip,expires)
     values($id,$ip,now()+interval $period hour)",
    __FUNCTION__);
}

function hasCounterIP($id,$mode)
{
global $counterModes;

$period=$counterModes[$mode]['period'];
if($period<=0)
  return false;
$ip=IPToInteger($_SERVER['REMOTE_ADDR']);
$result=sql("select counter_id
	     from counters_ip
	     where counter_id=$id and ip=$ip",
	    __FUNCTION__);
return mysql_num_rows($result)>0;
}

function incCounter($entry_id,$mode)
{
$result=sql("select id
	     from counters
	     where entry_id=$entry_id and mode=$mode and serial=0",
	    __FUNCTION__,'select');
if(mysql_num_rows($result)<=0)
  return;
$id=mysql_result($result,0,0);
if(hasCounterIP($id,$mode))
  return;
sql("update counters
     set value=value+1
     where id=$id",
    __FUNCTION__,'increment');
journal('update counters
         set value=value+1
	 where id='.journalVar('counters',$id));
storeCounterIP($id,$mode);
}

function rotateCounter($entry_id,$mode)
{
global $counterModes;

sql("update counters
     set serial=serial+1
     where entry_id=$entry_id and mode=$mode",
    __FUNCTION__,'rotate');
journal('update counters
         set serial=serial+1
         where entry_id='.journalVar('entries',$entry_id).
	     " and mode=$mode");

$max_serial=$counterModes[$mode]['max_serial'];
if($max_serial>=0)
  {
  sql("delete from counters
       where entry_id=$entry_id and mode=$mode and serial>$max_serial",
      __FUNCTION__,'delete');
  journal('delete from counters
	   where entry_id='.journalVar('entries',$entry_id).
	       " and mode=$mode and serial>$max_serial");
  }

$started=date("Y-m-d H:i:s");
$ttl=$counterModes[$mode]['ttl'];
if($ttl!=0)
  $finished=date("Y-m-d H:i:s",time()+$ttl*60*60);
else
  $finished="2100-01-01 00:00:00";
  // Leave solution of this problem to the next generations of programmers ;)
sql("insert into counters(entry_id,mode,started,finished)
     values($entry_id,$mode,'$started','$finished')",
    __FUNCTION__,'create');
journal('insert into counters(entry_id,mode,started,finished)
         values('.journalVar('entries',$entry_id).",$mode,
	         '$started','$finished')");
}

function createCounters($entry_id,$grp)
{
global $counterModes;

foreach($counterModes as $mode => $info)
       if(($info['grp'] & $grp)!=0)
         rotateCounter($entry_id,$mode);
}

function getCounterValue($entry_id,$mode,$serial=0)
{
$result=sql("select value
	     from counters
	     where entry_id=$entry_id and mode=$mode and serial=$serial",
	    __FUNCTION__);
return mysql_num_rows($result)>0 ? mysql_result($result,0,0) : 0;
}
?>
