<?php
# @(#) $Id$

require_once('lib/errorreporting.php');
require_once('lib/database.php');
require_once('lib/session.php');
require_once('lib/post.php');
require_once('lib/errors.php');
require_once('lib/utils.php');
require_once('lib/random.php');
require_once('lib/entries.php');
require_once('lib/postings.php');
require_once('lib/forums.php');
require_once('lib/modbits.php');

function modifyEntry($id,$modbits)
{
global $userModerator;

if(!$userModerator)
  return EMO_NO_MODERATE;
if(!entryExists(ENT_NULL,$id))
  return EMO_NO_ENTRY;
if(in_array(MOD_DELETE,$modbits))
  {
  $entryType=getTypeByEntryId($id);
  switch($entryType)
        {
	case ENT_FORUM:
	     deleteForum($id);
	     return EG_OK;
	case ENT_POSTING:
	     deletePosting($id);
	     return EG_OK;
	default:
	     return EMO_CANNOT_DELETE;
	}
  }
setHiddenByEntryId($id,in_array(MOD_HIDDEN,$modbits) ? 1 : 0);
setDisabledByEntryId($id,in_array(MOD_DISABLED,$modbits) ? 1 : 0);
$bits=0;
foreach($modbits as $bit)
       if($bit>MOD_NONE && $bit<MOD_ALL)
         $bits|=$bit;
assignModbitsByEntryId($id,$bits);
return EG_OK;
}

httpRequestString('okdir');
httpRequestString('faildir');

httpRequestIntegerArray('ids');
httpRequestIntegerArray2D('modbits');

dbOpen();
session();
foreach($ids as $id)
       {
       $err=modifyEntry($id,$modbits[$id]);
       if($err!=EG_OK)
         break;
       }
if($err==EG_OK)
  header('Location: '.remakeURI($okdir,
				array('err'),
			        array('reload' => random(0,999))));
else
  header('Location: '.remakeURI($faildir,
				array(),
				array('err' => $err)).'#error');
dbClose();
?>
