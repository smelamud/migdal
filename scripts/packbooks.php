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

function packBook($id)
{
global $tmpDir,$siteDomain,$bookCompressCommand,$bookCompressType;

$dir="$tmpDir/book-$id";
unlink($dir);
mkdir($dir,0777);
mkdir("$dir/style",0777);
mkdir("$dir/pics",0777);

copyFile("http://$siteDomain/book-static?bookid=$id","$dir/index.html");
copyFile("http://$siteDomain/styles/static-article.css",
         "$dir/style/static-article.css");
copyFile("http://$siteDomain/pics/up.gif","$dir/pics/up.gif");

$msgid=getMessageIdByPostingId($id);
$list=new PostingListIterator(GRP_BOOK_CHAPTERS,'-1',false,'0','0','0',
                              SORT_INDEX0,GRP_NONE,'0','-1','0','-1',$msgid,
			      true,SELECT_GENERAL);
while($item=$list->next())
     {
     $chap=getFullPostingById($item->getId(),GRP_BOOK_CHAPTERS);
     $pars=new PostingParagraphIterator($chap);
     while($par=$pars->next())
          copyImage($dir,$par);
     }

$cmd=str_replace(array('#','%'),
                 array($tmpDir,"book-$id"),
		 $bookCompressCommand);
$body=`$cmd`;
$package=new Package(array('posting_id' => $id,
                           'type'       => PT_BOOK_ONEFILE,
                           'mime_type'  => $bookCompressType,
			   'body'       => $body,
			   'size'       => strlen($body)));
if(!$package->store())
  echo "Book $id: ".mysql_error()."\n";

unlinkCopied();
rmdir("$dir/pics");
rmdir("$dir/style");
rmdir($dir);
}

function dropBook($id)
{
mysql_query("delete from packages
             where posting_id=$id and
	           (type=".PT_BOOK_ONEFILE.' or type='.PT_BOOK_SPLIT.')')
  or sqlbug('������ SQL ��� �������� ������ ������� ����');
}

function arePackagesReady($id)
{
$result=mysql_query("select min(created)
		     from packages
		     where posting_id=$id and
			   (`type`=".PT_BOOK_ONEFILE.' or
			    `type`='.PT_BOOK_SPLIT.')')
	  or sqlbug('������ SQL ��� ��������� ���� �������� ������� ����');
if(mysql_num_rows($result)<=0)
  return false;
$packs=mysql_result($result,0,0);
if($packs=='')
  return false;
$packs=strtotime($packs);
$msgid=getMessageIdByPostingId($id);
$result=mysql_query('select from_unixtime(max(unix_timestamp(last_updated)))
                     from messages
		     where '.subtree('messages',$msgid,true))
	  or sqlbug('������ SQL ��� ��������� ���� ���������� ���� �����');
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
     if(!arePackagesReady($item->getId()))
       {
       dropBook($item->getId());
       packBook($item->getId());
       }
}

dbOpen();
session(getShamesId());
redirect();
run();
dbClose();
?>
