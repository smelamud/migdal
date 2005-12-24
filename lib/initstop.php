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

function initialize()
{
noCacheHeaders();
dbOpen();
session();
postInteger('err');
set_error_handler('error_handler');
ob_start();
}

function initializeMeta()
{
echo "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.0 Transitional//EN\">\n";
noCacheMeta();
}

function initializeHead()
{
global $stylesheetList,$userStyle;

redirect();
foreach($stylesheetList as $sheet)
       {
       echo "<link rel='stylesheet' href='/styles/$sheet-".
             getStyle($userStyle).".css'>";
       include("styles/$sheet-".getStyle($userStyle).'.php');
       }
}

function finalize()
{
global $userReadKOI;

dbClose(); // dbClose() должен находиться здесь, чтобы профайлер не учитывал
           // время скачивания страницы клиентом
$Output=ob_get_contents();
ob_end_clean();
echo convertOutput($Output,$userReadKOI>0);
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
