<?php
# @(#) $Id$

require_once('lib/dataobject.php');
require_once('lib/bug.php');
require_once('lib/track.php');
require_once('lib/uri.php');
require_once('lib/utils.php');
require_once('lib/sql.php');
require_once('lib/post.php');

class Redir
      extends DataObject
{
var $id;
var $up;
var $track;
var $uri;
var $last_access;

function Redir($row=array())
{
parent::DataObject($row);
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
    __FUNCTION__);
}

function redirect()
{
global $LocationInfo,$redirid,$globalid,$Args;

postInteger('globalid');

if($globalid==0)
  {
  $requestURIS=addslashes($_SERVER['REQUEST_URI']);
  sql("insert into redirs(up,uri)
       values($redirid,'$requestURIS')",
      __FUNCTION__);
  $id=sql_insert_id();
  $track=track($id,trackById('redirs',$redirid));
  updateTrackById('redirs',$id,$track);
  $redir=new Redir(array('id'    => $id,
			 'up'    => $redirid,
			 'track' => $track,
			 'uri'   => $_SERVER['REQUEST_URI']));
  }
else
  {
  $redir=getRedirById($globalid);
  if($redir->getId()==0)
    {
    unset($Args['globalid']);
    postIntegerValue('globalid',0);
    redirect();
    return;
    }
  }
updateRedirectTimestamps($redir->getTrack());
$LocationInfo->setRedir($redir);
}

function getRedirById($id)
{
$result=sql("select id,up,track,uri
	     from redirs
	     where id=$id",
	    __FUNCTION__);
return new Redir(mysql_num_rows($result)>0 ? mysql_fetch_assoc($result)
                                           : array());
}

function redirExists($id)
{
$result=sql("select id
	     from redirs
	     where id=$id",
	    __FUNCTION__);
return mysql_num_rows($result)>0;
}
?>
