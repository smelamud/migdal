<?php
# @(#) $Id$

require_once('conf/migdal.conf');

require_once('lib/errorreporting.php');
require_once('lib/no-cache.php');
require_once('lib/database.php');
require_once('lib/session.php');
require_once('lib/logs.php');
require_once('lib/postings.php');
require_once('lib/redirs.php');
require_once('lib/exec.php');
require_once('lib/time.php');

function removeControlChars($s)
{
return preg_replace('/\s+/',' ',$s);
}

postInteger('from');

dbOpen();
session(getUserIdByLogin($rebeLogin));

$dir=tempnam($tmpDir,'mig-stat-');
unlink($dir);
mkdir($dir,0777);

$timestamp=0;
$fd=fopen("$dir/log",'w');
$iter=new LogIterator($from,$statisticsQuota);
foreach($iter as $line)
       {
       fputs($fd,$line->getEvent()."\t".$line->getSent()."\t".$line->getIP().
                 "\t".$line->getBody()."\n");
       $timestamp=$line->getSent();
       }
fclose($fd);

$fd=fopen("$dir/timestamp",'w');
fputs($fd,"$from\n");
fputs($fd,ourtime()."\n");
fclose($fd);

$fd=fopen("$dir/postings",'w');
$iter=new PostingListIterator(GRP_ALL,-1,false,0);
foreach($iter as $post)
       fputs($fd,$post->getId()."\t".$post->getParentId().
             "\t".removeControlChars($post->getHeading(true))."\n");
fclose($fd);

$fd=fopen("$dir/topics",'w');
$iter=new TopicNamesIterator(GRP_ALL);
foreach($iter as $topic)
       fputs($fd,$topic->getId()."\t".$topic->getIdent().
                 "\t".$topic->getFullName()."\n");
fclose($fd);

$fd=fopen("$dir/users",'w');
$iter=new UserListIterator('');
foreach($iter as $user)
       fputs($fd,$user->getId()."\t".$user->getLogin()."\n");
fclose($fd);

noCacheHeaders();
header("Content-Type: $compressType");
header("Content-Encoding: $compressEncoding");
$cmd=str_replace(array('#','%'),
                 array($tmpDir,substr($dir,strlen($tmpDir)+
		                           ($dir[strlen($tmpDir)]=='/' ? 1 : 0))),
		 $compressCommand);
echoCommand($cmd);

unlink("$dir/users");
unlink("$dir/topics");
unlink("$dir/postings");
unlink("$dir/log");
unlink("$dir/timestamp");
rmdir($dir);

dbClose();
?>
