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

function modifyMessage($editid,$hide)
{
global $userModerator;

if(!$userModerator)
  return EMH_NO_MODERATE;
if(!messageExists($editid))
  return EMH_NO_MESSAGE;
setDisabledByMessageId($editid,$hide);
return EMH_OK;
}

postInteger('editid');

dbOpen();
session();
$err=modifyMessage($editid,$hide ? 1 : 0);
if($err==EMH_OK)
  header('Location: '.remakeURI($okdir,array(),array('reload' => random(0,999))));
else
  header('Location: '.remakeURI($faildir,array(),array('err' => $err)).'#error');
dbClose();
?>
