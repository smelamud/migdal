<?php
# @(#) $Id$

require_once('lib/dataobject.php');
require_once('lib/selectiterator.php');
require_once('lib/bug.php');
require_once('lib/track.php');
require_once('lib/uri.php');
require_once('lib/utils.php');
require_once('lib/logs.php');
require_once('lib/sql.php');

class Redir
      extends DataObject
{
var $id;
var $up;
var $track;
var $name;
var $uri;
var $last_access;

function Redir($row=array())
{
$this->DataObject($row);
}

function getId()
{
return $this->id;
}

function getUp()
{
return $this->up;
}

function getTrack()
{
return $this->track;
}

function getName()
{
return $this->name;
}

function getURI()
{
return $this->uri;
}

}

function updateRedirectTimestamps($track)
{
sql("update redirs
     set last_access=null
     where '$track' like concat(track,'%')",
    'updateRedirectTimestamps');
}

function redirect()
{
global $redir,$lastRedir,$redirid,$globalid,$pageTitle,$REQUEST_URI,
       $HTTP_REFERER;

settype($redirid,'integer');
settype($globalid,'integer');

if($redirid!=0 && !redirExists($redirid))
  reload(remakeURI($REQUEST_URI,array('redirid')));
if($globalid==0)
  {
  sql("insert into redirs(up,name,uri)
       values($redirid,'".addslashes($pageTitle)."','".
	      addslashes($REQUEST_URI)."')",
      'redirect');
  $id=sql_insert_id();
  $track=track($id,trackById('redirs',$redirid));
  updateTrackById('redirs',$id,$track);
  $redir=new Redir(array('id'    => $id,
			 'up'    => $redirid,
			 'track' => $track,
			 'name'  => $pageTitle,
			 'uri'   => $REQUEST_URI));
  }
else
  {
  $redir=getRedirById($globalid);
  if($redir->getId()==0)
    {
    $globalid=0;
    redirect();
    return;
    }
  }
if($redir->getUp()!=0)
  $lastRedir=getRedirById($redir->getUp());
else
  $lastRedir=new Redir();
updateRedirectTimestamps($redir->getTrack());
}

class RedirIterator
      extends SelectIterator
{

function RedirIterator()
{
global $redir;

$track=$redir->getTrack();
$this->SelectIterator('Redir',
                      "select id,name,uri
		       from redirs
		       where '$track' like concat(track,'%')
		             and track<>''
		       order by length(track)");
}

}

function getRedirById($id)
{
$result=sql("select id,up,track,name,uri
	     from redirs
	     where id=$id",
	    'getRedirById');
return new Redir(mysql_num_rows($result)>0 ? mysql_fetch_assoc($result)
                                           : array());
}

function redirExists($id)
{
$result=sql("select id
	     from redirs
	     where id=$id",
	    'redirExists');
return mysql_num_rows($result)>0;
}
?>
