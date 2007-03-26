<?php
# @(#) $Id$

require_once('conf/migdal.conf');

require_once('lib/errorreporting.php');
require_once('lib/database.php');
require_once('lib/session.php');
require_once('lib/redirs.php');
require_once('lib/postings.php');
require_once('lib/images.php');
require_once('lib/image-types.php');
require_once('lib/packages.php');
require_once('lib/exec.php');
require_once('lib/sql.php');

$copied=array();

function copyFile($src,$dst)
{
global $copied;

$rfd=fopen($src,'r');
$wfd=fopen($dst,'w');
do
  {
  $buf=fread($rfd,65536);
  fwrite($wfd,$buf);
  }
while(!feof($rfd) && $buf!='');
fclose($rfd);
fclose($wfd);
$copied[]=$dst;
}

function unlinkCopied()
{
global $copied;

foreach($copied as $fname)
       unlink($fname);
$copied=array();
}

function copyImage($dir,$par)
{
global $siteDomain,$thumbnailType;

$id=$par->getImageId();
if($id!=0)
  {
  $ext=getImageExtension($par->hasLargeImage()
      		   ? $thumbnailType : $par->getImageFormat());
  copyFile("http://$siteDomain/lib/image.php?id=$id&size=small",
           "$dir/pics/migdal-$id-small.$ext");
  if($par->hasLargeImage())
    {
    $ext=getImageExtension($par->getImageFormat());
    copyFile("http://$siteDomain/lib/image.php?id=$id&size=large",
             "$dir/pics/migdal-$id.$ext");
    }
  }
}

function packBook($id,$message_id,$type)
{
global $tmpDir,$siteDomain,$bookCompressCommand,$bookCompressType,
       $bookCompressDir,$bookCompressURL;

$dir="$tmpDir/book-$message_id";
unlink($dir);
mkdir($dir,0777);
mkdir("$dir/style",0777);
mkdir("$dir/pics",0777);

$list=new PostingListIterator(GRP_BOOK_CHAPTERS,'-1',false,'0','0','0',
                              SORT_INDEX0,GRP_NONE,'0','-1','0','-1',
			      $message_id,true,SELECT_GENERAL);

if($type==PT_BOOK_ONEFILE)
  copyFile("http://$siteDomain/book-static.php?bookid=$id","$dir/index.html");
else
  {
  copyFile("http://$siteDomain/book-split.php?bookid=$id","$dir/index.html");
  while($item=$list->next())
       copyFile("http://$siteDomain/book-chapter-split.php?chapid=".
                                                           $item->getId(),
                "$dir/chapter-".$item->getIndex0().'.html');
  }

copyFile("http://$siteDomain/styles/static-article.css",
         "$dir/style/static-article.css");
copyFile("http://$siteDomain/pics/up.gif","$dir/pics/up.gif");
if($type==PT_BOOK_SPLIT)
  {
  copyFile("http://$siteDomain/pics/left.gif","$dir/pics/left.gif");
  copyFile("http://$siteDomain/pics/right.gif","$dir/pics/right.gif");
  copyFile("http://$siteDomain/pics/further.gif","$dir/pics/further.gif");
  }

$list->reset();
while($item=$list->next())
     {
     // FIXME SELECT_IMAGES deprecated
     $chap=getPostingById($item->getId(),GRP_BOOK_CHAPTERS,-1,SELECT_IMAGES);
     $pars=new PostingParagraphIterator($chap); // FIXME no such class
     while($par=$pars->next())
          copyImage($dir,$par);
     }

$cmd=str_replace(array('#','%'),
                 array($tmpDir,"book-$message_id"),
		 $bookCompressCommand);
if($bookCompressDir=='')
  {
  $body=getCommand($cmd);
  $package=new Package(array('message_id' => $message_id,
			     'type'       => $type,
			     'mime_type'  => $bookCompressType,
			     'body'       => $body,
			     'size'       => strlen($body)));
  }
else
  {
  $pname=getPackageFileName($type,$message_id,$bookCompressType);
  getCommand("$cmd > $bookCompressDir/$pname");
  $package=new Package(array('message_id' => $message_id,
			     'type'       => $type,
			     'mime_type'  => $bookCompressType,
			     'size'       => filesize("$bookCompressDir/$pname"),
			     'url'        => "$bookCompressURL/$pname"));
  }
$package->store();

unlinkCopied();
rmdir("$dir/pics");
rmdir("$dir/style");
rmdir($dir);
}

function dropBook($message_id)
{
sql("delete from packages
     where message_id=$message_id and
	   (type=".PT_BOOK_ONEFILE.' or type='.PT_BOOK_SPLIT.')',
    'dropBook');
}

function arePackagesReady($message_id)
{
$result=sql("select min(created)
	     from packages
	     where message_id=$message_id and
		   (`type`=".PT_BOOK_ONEFILE.' or
		    `type`='.PT_BOOK_SPLIT.')',
	    'arePackagesReady','min_created');
if(mysql_num_rows($result)<=0)
  return false;
$packs=mysql_result($result,0,0);
if($packs=='')
  return false;
$packs=strtotime($packs);
$result=sql('select from_unixtime(max(unix_timestamp(last_updated)))
	     from messages
	     where '.subtree('messages',$message_id,true),
	    'arePackagesReady','max_last_updated');
if(mysql_num_rows($result)<=0)
  return true;
$msgs=mysql_result($result,0,0);
if($msgs=='')
  return false;
$msgs=strtotime($msgs);
return $msgs<$packs;
}

function run()
{
$iter=new PostingListIterator(GRP_BOOKS,-1,true,0,0,0,SORT_SENT,GRP_NONE,0,-1,
                              0,-1,-1,false,SELECT_GENERAL);
while($item=$iter->next())
     if(!arePackagesReady($item->getMessageId()))
       {
       dropBook($item->getMessageId());
       packBook($item->getId(),$item->getMessageId(),PT_BOOK_ONEFILE);
       packBook($item->getId(),$item->getMessageId(),PT_BOOK_SPLIT);
       }
}

dbOpen();
session(getShamesId());
redirect();
run();
dbClose();
?>
