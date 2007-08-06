<?php
# @(#) $Id: debug-log.php,v 1.2 2007-01-31 22:33:49 balu Exp $

require_once('conf/migdal.conf');

require_once('lib/bug.php');

// Все включено
define('LL_ALL',0);
// Информация о вызываемых библиотечных функциях и их параметрах
define('LL_FUNCTIONS',1);
// Пошаговая информация о работе функций
define('LL_DETAILS',2);
// Избыточная отладочная информация
define('LL_DEBUG',3);
// Все выключено
define('LL_OFF',4);

if($debugLogLevel<LL_OFF)
  ini_set('track_errors',true);

$debugLogFD=null;

function debugLog($level,$message,$args=array())
{
global $debugLogLevel,$debugLogFile,$debugLogFD;

if(!isDebugLogging($level))
  return;
if(!isset($debugLogFD) || is_null($debugLogFD))
  {
  $debugLogFD=fopen($debugLogFile,'a');
  if(!$debugLogFD)
    bug("Cannot open '$debugLogFile' for appending");
  }
fputs($debugLogFD,date('M d H:i:s ').$_SERVER['REMOTE_ADDR'].' '
      .debugLogSubArgs($message,$args)."\n");
fflush($debugLogFD);
}

function debugLogSubArgs($message,$args)
{
if(!is_array($args))
  return '* debugLog() error: third parameter should be an array *';
$s='';
$j=0;
for($i=0;$i<strlen($message);$i++)
   if($message{$i}!='%')
     $s.=$message{$i};
   else
     if($i<strlen($message)-1 && $message{$i+1}=='%')
       {
       $s.='%';
       $i++;
       }
     else
       {
       $s.=debugLogData($args[$j]);
       $j++;
       }
return $s;
}

function debugLogData($data)
{
global $debugLogEllipSize;

if(is_null($data))
  return 'null';
elseif(is_bool($data))
  return $data ? 'true' : 'false';
elseif(is_float($data) || is_int($data))
  return (string)$data;
elseif(is_array($data))
  {
  $s='array(';
  foreach($data as $key => $value)
         $s.="$key => ".debugLogData($value).', ';
  $s.=')';
  return $s;
  }
elseif(is_object($data))
  {
  $s=get_class($data).'{';
  foreach(get_object_vars($data) as $key => $value)
         $s.="$key => ".debugLogData($value).', ';
  $s.='}';
  return $s;
  }
elseif(is_string($data))
  return "'".ellipsize($data,$debugLogEllipSize)."'";
else
  return '"'.$data.'"';
}

function isDebugLogging($level)
{
global $debugLogLevel;

return $level>=$debugLogLevel;
}

function debugLogClose()
{
global $debugLogFD;

if(isset($debugLogFD) && !is_null($debugLogFD))
  fclose($debugLogFD);
}
?>
