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

function modifyMessage($id)
{
global $userModerator,$HTTP_POST_VARS;

if(!$userModerator)
  return EMO_NO_MODERATE;
if(!messageExists($id))
  return EMO_NO_MESSAGE;
setHiddenByMessageId($id,$HTTP_POST_VARS["hidden"]);
setDisabledByMessageId($id,$HTTP_POST_VARS["disabled"]);
$bits=0;
for($bit=1;$bit<=MOD_ALL;$bit*=2)
   if($HTTP_POST_VARS["bit$bit"])
     $bits|=$bit;
assignModbitsByMessageId($id,$bits);
return EMO_OK;
}

postInteger('id');

dbOpen();
session();
$err=modifyMessage($id);
if($err==EMO_OK)
  header('Location: '.remakeURI($okdir,
				array('err')));
else
  header('Location: '.remakeURI($faildir,
				array(),
				array('err' => $err)).'#error');
dbClose();
?>
