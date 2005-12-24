<?php
# @(#) $Id$

require_once('lib/errorreporting.php');
require_once('lib/database.php');
require_once('lib/session.php');
require_once('lib/post.php');
require_once('lib/errors.php');
require_once('lib/utils.php');
require_once('lib/random.php');
/* Required to prevent inclusion of Posting class before Message */
require_once('lib/postings.php');
require_once('lib/messages.php');
require_once('lib/modbits.php');

function modifyMessage($editid)
{
global $userModerator,$HTTP_POST_VARS;

if(!$userModerator)
  return EMM_NO_MODERATE;
if(!messageExists($editid))
  return EMM_NO_MESSAGE;
setHiddenByMessageId($editid,$HTTP_POST_VARS["hidden$editid"]);
setDisabledByMessageId($editid,$HTTP_POST_VARS["disabled$editid"]);
$bits=0;
for($bit=1;$bit<=MOD_ALL;$bit*=2)
   if($HTTP_POST_VARS["bit$editid-$bit"])
     $bits|=$bit;
assignModbitsByMessageId($editid,$bits);
return EG_OK;
}

postIntegerArray('id');

dbOpen();
session();
foreach($id as $editid)
       {
       $err=modifyMessage($editid);
       if($err!=EG_OK)
         {
         header('Location: '.remakeURI($faildir,
	                               array(),
				       array('err' => $err)).'#error');
	 exit;
	 }
       }
header('Location: '.remakeURI($okdir,
                              array('err'),
			      array('reload' => random(0,999))));
dbClose();
?>
