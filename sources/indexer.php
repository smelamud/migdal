<?php
# @(#) $Id$

require_once('lib/errorreporting.php');
require_once('lib/database.php');
require_once('lib/session.php');
require_once('lib/postings.php');
require_once('lib/topics.php');
require_once('lib/charsets.php');

function getPortions($posting,$scheme)
{
$ports=array();
$s='';
for($i=0;$i<strlen($scheme);$i++)
   switch($scheme{$i})
	 {
	 case 'J':
	      $s.=' '.$posting->getSubject();
	      break;
	 case 'D':
	      $s.=' '.$posting->getSubjectDesc(); # FIXME deprecated
	      break;
	 case 'A':
	      $s.=' '.$posting->getHTMLAuthor();
	      break;
	 case 'S':
	      $s.=' '.$posting->getHTMLSource();
	      break;
	 case 'B':
	      $s.='<p>'.$posting->getHTMLBody();
	      break;
	 case 'L':
	      $s.='<p>'.$posting->getHTMLLargeBody();
	      break;
	 case 'p':
	      $s.=' Картинка';
	      break;
	 case 't':
	      $s.=' Мигдаль Times N '.$posting->getIndex1();
	      break;
	 case '-':
	      $ports[]=$s;
	      $s='';
	 }
$ports[]=$s;
return $ports;
}

function getTopic($posting)
{
$s='';
$iterator=new TopicHierarchyIterator($posting->getTopicId());
foreach($iterator as $item)
       $s.=(!$iterator->isFirst() ? ' :: ' : '').$item->getName();
return $s;
}

dbOpen();
session(getShamesId());
settype($postid,'integer');
settype($topic_id,'integer');
header('Content-Language: ru');
if($postid==0 && $topic_id==0)
  {
  $output='<html>'.
	   '<head><title></title></head>'.
	   '<body>';
  $iterator=new TopicListIterator(GRP_ALL,-1);
  foreach($iterator as $item)
         if(($item->getPerms() & PERM_UP)!=0)
           $output.="<a href='indexer.php?topic_id=".$item->getId()."'>@</a>";
  $iterator=new PostingListIterator(GRP_ALL,-1,false,0,0,0,SORT_SENT,GRP_NONE,0,
                                    -1,0,-1,-1,true,SELECT_GENERAL);
  foreach($iterator as $item)
         $output.="<a href='indexer.php?postid=".$item->getId()."'>@</a>";
  $output.='</body>'.
	  '</html>';
  }
elseif($postid==0)
  {
  $topic=getTopicById($topic_id);
  if($topic->getId()<=0)
    {
    header('HTTP/1.1 404 Topic not found');
    exit();
    }
  $output='<html>'.
	   '<head>'.
	    '<title>'.$topic->getName().'</title>'.
	   '</head>'.
	   '<body>'.
	    '<h1>'.$topic->getName().'</h1>'.
	    $topic->getDescription().
	   '</body>'.
	  '</html>';
  }
else
  {
  $posting=getPostingById($postid);
  if($posting->getId()<=0)
    {
    header('HTTP/1.1 404 Posting not found');
    exit();
    }
  $ports=getPortions($posting,$posting->getIndexScheme());
  $topic=getTopic($posting);
  $output='<html>'.
	   '<head>'.
	    '<title>'.$ports[0].'</title>'.
	    (count($ports)>2 ?
	     '<meta name="source" content="'.htmlspecialchars($ports[2]).'">' :
	     '').
	   '</head>'.
	   '<body>'.
	    '<h1>'.$ports[0].'</h1>'.
	    '['.$topic.']<br>'.
	    $ports[1].
	   '</body>'.
	  '</html>';
  }
echo convertOutput($output);
dbClose();
?>
