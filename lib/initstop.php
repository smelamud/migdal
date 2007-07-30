<?php
# @(#) $Id$

require_once('lib/errorreporting.php');
require_once('lib/database.php');
require_once('lib/session.php');
require_once('lib/no-cache.php');
require_once('lib/charsets.php');
require_once('lib/style.php');
require_once('lib/redirs.php');
require_once('lib/post.php');
require_once('lib/logs.php');

function initialize()
{
noCacheHeaders();
dbOpen();
session();
postInteger('err');
set_error_handler('error_handler');
ob_start('convertOutput');
}

function initializeMeta()
{
echo "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.0 Transitional//EN\">\n";
noCacheMeta();
}

function initializeHead()
{
global $stylesheetList,$userStyle,$jsList;

foreach($stylesheetList as $sheet)
       {
       echo "<link rel='stylesheet' href='/styles/$sheet-".
             getStyle($userStyle).".css'>\n";
       include("styles/$sheet-".getStyle($userStyle).'.php');
       }
if(isset($jsList))
  foreach($jsList as $src)
         echo "<script src='$src' language='JavaScript'></script>\n";
}

function finalize()
{
dbClose(); // dbClose() должен находиться здесь, чтобы профайлер не учитывал
           // время скачивания страницы клиентом
ob_end_flush();
}

function error_handler($errno,$errstr,$errfile,$errline)
{
if(($errno & error_reporting())==0)
  return;
logEvent('bug',"$errstr file($errfile) line($errline)");
echo "<b>Fatal error</b>: $errstr in <b>$errfile</b> on line <b>$errline</b>";
finalize();
die();
}
?>
