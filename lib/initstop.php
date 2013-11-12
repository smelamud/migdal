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
header('Content-Style-Type: text/css'); 
dbOpen();
session();
postInteger('err');
set_error_handler('error_handler');
ob_start('convertOutput');
}

function initializeMeta()
{
echo "<!DOCTYPE html>\n";
}

function initializeHead()
{
global $stylesheetList,$userStyle,$jsList,$ogImageList;

foreach($stylesheetList as $sheet)
       {
       echo "<link rel='stylesheet' href='/styles/$sheet-".
             getStyle($userStyle).".css'>\n";
       include("styles/$sheet-".getStyle($userStyle).'.php');
       }
if(isset($jsList))
  foreach($jsList as $src)
         echo "<script src='$src' type='text/javascript'></script>\n";
if(isset($ogImageList) && count($ogImageList)>0)
  {
  echo "<link rel='image_src' href='{$ogImageList[0]}'>\n";
  foreach($ogImageList as $src)
         echo "<meta property='og:image' content='$src'>\n";
  }
}

function finalize()
{
dbClose(); // dbClose() ������ ���������� �����, ����� ��������� �� ��������
           // ����� ���������� �������� ��������
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
