<?php
# @(#) $Id$

require_once('lib/errorreporting.php');
require_once('lib/database.php');
require_once('lib/session.php');
require_once('lib/post.php');
require_once('lib/errors.php');
require_once('lib/sql.php');
require_once('lib/entries.php');
require_once('lib/charsets.php');
require_once('lib/cross-entries.php');
require_once('lib/tmptexts.php');

function addCrossEntry($source_name,$source_id,$link_type,$peer_path)
{
global $userModerator;

if(!$userModerator)
  return ECEA_NO_ADD;
if($source_id>0 && !entryExists(ENT_NULL,$source_id))
  return ECEA_ENTRY_ABSENT;
if($source_name=='' && $source_id<=0)
  return ECEA_NO_SOURCE;
$peer_path=getURLPath($peer_path);
if($peer_path=='')
  return ECEA_NO_PATH;
while(true)
     {
     $info=&getLocationInfo($peer_path);
     if($info->getScript()!='')
       break;
     if($info->getPath()==$peer_path)
       return ECEA_PATH_ABSENT;
     $peer_path=$info->getPath();
     }
if($info->getLinkId()<=0 && $info->getLinkName()=='')
  return ECEA_NO_LINKING;
$cross=new CrossEntry();
$cross->source_name=$source_name!='' ? $source_name : null;
$cross->source_id=$source_id>0 ? $source_id : null;
$cross->link_type=$link_type;
$cross->peer_name=$info->getLinkName()!='' ? $info->getLinkName() : null;
$cross->peer_id=$info->getLinkId()>0 ? $info->getLinkId() : null;
$cross->peer_path=$peer_path;
$cross->peer_subject=$info->getLinkTitle();
$cross->peer_icon=$info->getLinkIcon();
storeCrossEntry($cross);
return EG_OK;
}

postString('okdir');
postString('faildir');

postString('source_name');
postInteger('source_id');
postInteger('link_type');
postString('peer_path');

dbOpen();
session();
$err=addCrossEntry($source_name,$source_id,$link_type,$peer_path);
if($err==EG_OK)
  header('Location: '.remakeURI($okdir,
                                array('err')));
else
  {
  $peerPathId=tmpTextSave($peer_path);
  header('Location: '.remakeURI($faildir,
                                array('okdir',
				      'faildir'),
				array('peer_path_i' => $peerPathId,
				      'err' => $err)).'#error');
  }
dbClose();
?>
