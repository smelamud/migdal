<?php
# @(#) $Id$

require_once('conf/migdal.conf');

require_once('lib/errorreporting.php');
require_once('lib/database.php');
require_once('lib/ratings.php');

function scanJewishru($rating)
{
global $wgetPath;

$pos=0;

for($n=1;$n<=20;$n++)
   {
   $fd=popen("$wgetPath -qO - -T 30 'http://www.jewish.ru/topjews/index.asp?page=$n'",
             'r');
   $pl=0;
   while($fd && !feof($fd))
	{
	$s=fgets($fd);
	if(preg_match('/^<TR><TD class="TDITEM">(\d+)<\/TD>/',$s,$matches))
	  $pl=$matches[1];
	if(preg_match('/^<TD><dl><dt><A class="AITEM" HREF="\/perl\/trackrate.pl\?url='.
	              $rating->getURL().'/',$s,$matches) && $pl!=0)
          {
	  $pos=$pl;
	  $pl=0;
	  }
	}
   pclose($fd);
   if($pos!=0)
     break;
   }
addRatingPosition($rating->getId(),0,0,$pos,0);
}

function scanMailru($rating)
{
global $wgetPath;

$topicDay=0;
$topicWeek=0;
$globalDay=0;
$globalWeek=0;

$fd=popen("$wgetPath -qO - -T 30 'http://top.mail.ru/stat?id=".
           $rating->getRegId()."'",'r');
while($fd && !feof($fd))
     {
     $s=fgets($fd);
     if(preg_match('/^<td><a href="\/Rating\/MassMedia-News\/Today\/Hosts\/\d*.html#(\d+)">/',
                   $s,$matches))
       $topicDay=$matches[1];
     if(preg_match('/^<td><a href="\/Rating\/MassMedia-News\/Week\/Hosts\/\d*.html#(\d+)">/',
                   $s,$matches))
       $topicWeek=$matches[1];
     if(preg_match('/^<td><a href="\/Rating\/All\/Today\/Hosts\/\d*.html#(\d+)">/',
                   $s,$matches))
       $globalDay=$matches[1];
     if(preg_match('/^<td><a href="\/Rating\/All\/Week\/Hosts\/\d*.html#(\d+)">/',
                   $s,$matches))
       $globalWeek=$matches[1];
     }
pclose($fd);
addRatingPosition($rating->getId(),$topicDay,$topicWeek,$globalDay,$globalWeek);
}

function scanGoogleru($rating)
{
global $wgetPath;

$topicDay=0;
$topicWeek=0;

$pages=array(''       => 'topicDay' ,'24hho2' => 'topicDay',
             '24hho3' => 'topicDay' ,'who1'   => 'topicWeek',
	     'who2'   => 'topicWeek','who3'   => 'topicWeek');
reset($pages);
while(list($s,$var)=each($pages))
     {
     $fd=popen("$wgetPath -qO - -T 30 'http://www.topcto.ru/other/index$s.html'",
               'r');
     $pl=0;
     while($fd && !feof($fd))
	  {
	  $c=fgets($fd);
	  if(preg_match('/^\s*<font size="-1">&nbsp;(\d+)&nbsp;<\/font>\s*$/',
	                $c,$matches))
	    $pl=$matches[1];
	  if(preg_match('/^\s*&nbsp;<a target=_blank title=".*href="http:\/\/www.topcto.ru\/cgi-bin\/click.cgi\?uid='.
	                $rating->getId().'.*class="go">.*<\/a>&nbsp;\s*$/',
			$c,$matches) && $pl!=0)
	    {
	    $$var=$pl;
	    $pl=0;
	    }
	  }
     pclose($fd);
     }
addRatingPosition($rating->getId(),$topicDay,$topicWeek,0,0);
}

function killNbsps($s)
{
return str_replace('&nbsp;','',$s);
}

function scanRambler($rating)
{
global $wgetPath;

$topicDay=0;
$topicWeek=0;
$globalDay=0;
$globalWeek=0;

$fd=popen("$wgetPath -qO - -T 30 'http://top100.rambler.ru/cgi-bin/stats_top100.cgi?id=".
           $rating->getId()."&page=6'",'r');
$vd='topicDay';
$vw='topicWeek';
$sites=0;
while($fd && !feof($fd))
     {
     $s=fgets($fd);
     if(preg_match('/^<tr bgcolor="#e0e0e0" align=right><td colspan=4 align=center><small><i>Рейтинг сайтов<\/i><\/small><\/td><\/tr>/',
                   $s,$matches))
       $sites=1;
     if(preg_match('/^<tr bgcolor="#f2f2f2" align=right><td><small>По посетителям<\/td><td><small>([^<]+)<font size="-2" color="#999999">&gt;&gt;<\/font><\/td><td><small>([^<]+)/',
                   $s,$matches) && $sites==1)
       {
       $$vd=killNbsps($matches[1]);
       $$vw=killNbsps($matches[2]);
       $vd='globalDay';
       $vw='globalWeek';
       $sites=0;
       }
     }
pclose($fd);
addRatingPosition($rating->getId(),$topicDay,$topicWeek,$globalDay,$globalWeek);
}

dbOpen();
$iter=new RatingsListIterator();
while($rating=$iter->next())
     {
     $name='scan'.ucfirst(strtolower($rating->getIdent()));
     $name($rating);
     }
dbClose();

?>
