<?php
# @(#) $Id$

require_once('conf/migdal.conf');

require_once('lib/errorreporting.php');
require_once('lib/database.php');
require_once('lib/mailings.php');

function send($mail)
{
global $mailingsDir,$siteDomain;

if($mail->isEmailDisabled() && !$mail->isForceSend())
  return;
$path=$mail->getMailScript();
if($path=='')
  return;
if(substr($path,0,1)!='/')
  $path="$mailingsDir/$path";
$path=urlencode($path);
$link=$mail->getLink();
$userId=$mail->getReceiverId();
$linkParam=urlencode("link=$link");
preg_match("/^(.+?\n)\n(.*)$/s",
           file_get_contents("http://$siteDomain/lib/run-script?name=$path&argv[]=$linkParam&argv[]=$userId"),
	   $mailparts);
$heads=explode("\n",$mailparts[1]);
$newheads=array();
foreach($heads as $head)
       if(preg_match('/^(\w+): (.*)$/',$head,$parts) && $parts[1]=='Subject')
         $subject=$parts[2];
       else
         if($head!='')
           $newheads[]=$head;
mail($mail->getEmail(),$subject,$mailparts[2],implode("\n",$newheads));
}

dbOpen();
$iter=new MailingsExtractIterator();
while($mail=$iter->next())
     send($mail);
dbClose();
?>
