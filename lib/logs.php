<?php
# @(#) $Id$

require_once('lib/dataobject.php');
require_once('lib/selectiterator.php');

function IPToInteger($addr)
{
$octets=explode('.',$addr);
$ip=0;
foreach($octets as $oct)
       $ip=$ip*256+$oct;
return $ip;
}

function logEvent($event,$body)
{
global $REMOTE_ADDR;

$event=addslashes($event);
$ip=IPToInteger($REMOTE_ADDR);
$body=addslashes($body);
mysql_query("insert into logs(event,ip,body)
             values('$event',$ip,'$body')")
     or die("Ошибка SQL при добавлении в лог");
}

class LogLine
      extends DataObject
{
var $id;
var $event;
var $sent;
var $ip;
var $body;

function LogLine($row)
{
$this->DataObject($row);
}

function getId()
{
return $this->id;
}

function getEvent()
{
return $this->event;
}

function getSent()
{
return $this->sent;
}

function getIP()
{
return $this->ip;
}

function getBody()
{
return $this->body;
}

}

class LogIterator
      extends SelectIterator
{

function LogIterator($from=0)
{
$this->SelectIterator('LogLine',
                      "select id,event,unix_timestamp(sent) as sent,ip,body
		       from logs
		       where unix_timestamp(sent)>$from");
}

}
?>
