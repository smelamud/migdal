<?php
# @(#) $Id$

require_once('conf/migdal.conf');

require_once('lib/dataobject.php');
require_once('lib/selectiterator.php');
require_once('lib/ip.php');
require_once('lib/bug.php');
require_once('lib/sql.php');

function logEvent($event,$body)
{
global $disableLogging;

if($disableLogging)
  return;
$event=addslashes($event);
$ip=IPToInteger($_SERVER['REMOTE_ADDR']);
$body=addslashes($body);
sql("insert into logs(event,ip,body)
     values('$event',$ip,'$body')",
    'logEvent','','',false);
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
