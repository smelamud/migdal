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

$fd=fopen("$dir/timestamp",'w');
fputs($fd,"$from\n");
fputs($fd,time()."\n");
fclose($fd);

$fd=fopen("$dir/log",'w');
$iter=new LogIterator($from);
while($line=$iter->next())
     fputs($fd,$line->getEvent()."\t".$line->getSent()."\t".$line->getIP().
               "\t".$line->getBody()."\n");
fclose($fd);

$fd=fopen("$dir/postings",'w');
$iter=new PostingListIterator(GRP_ALL,-1,false,0);
while($post=$iter->next())
     fputs($fd,$post->getId()."\t".$post->getParentId().
	       "\t".removeControlChars($post->getHeading(true))."\n");
fclose($fd);

$fd=fopen("$dir/topics",'w');
$iter=new TopicNamesIterator(GRP_ALL);
while($topic=$iter->next())
     fputs($fd,$topic->getId()."\t".$topic->getFullName()."\n");
fclose($fd);

$fd=fopen("$dir/users",'w');
$iter=new UserListIterator('');
while($user=$iter->next())
     fputs($fd,$user->getId()."\t".$user->getLogin()."\n");
fclose($fd);

noCacheHeaders();
header("Content-Type: $compressType");
header("Content-Encoding: $compressEncoding");
$cmd=str_replace(array('#','%'),
                 array($tmpDir,substr($dir,strlen($tmpDir)+
		                           ($dir[strlen($tmpDir)]=='/' ? 1 : 0))),
		 $compressCommand);
$fd=fopen('/tmp/xxx','w');
fputs($fd,$cmd);
fclose($fd);
echoCommand($cmd);

/*unlink("$dir/users");
unlink("$dir/topics");
unlink("$dir/postings");
unlink("$dir/log");
unlink("$dir/timestamp");
rmdir($dir);*/

dbClose();
?>