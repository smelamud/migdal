<?php
# @(#) $Id$

require_once('lib/errorreporting.php');
require_once('lib/database.php');
require_once('lib/session.php');
require_once('lib/post.php');
require_once('lib/errors.php');
require_once('lib/random.php');
/* Required to prevent inclusion of Posting class before Message */
require_once('lib/postings.php');
require_once('lib/messages.php');
require_once('lib/postings-info.php');
require_once('lib/forums.php');
require_once('lib/answers.php');
require_once('lib/sql.php');

function renewMessage($id)
{
global $userModerator;

if(!$userModerator)
  return EMR_NO_RENEW;
if(!messageExists($id))
  return EMR_NO_MESSAGE;
sql("update messages
     set sent=now()
     where id=$id",
    'renewMessage');
journal('update messages
         set sent=now()
	 where id='.journalVar('messages',$id));
if(isForumAnswer($id))
  answerUpdate($id);
return EG_OK;
}

postInteger('id');

dbOpen();
session();
$err=renewMessage($id);
if($err==EG_OK)
  {
  header('Location: '.remakeURI($okdir,
                                array(),
				array('reload' => random(0,999))));
  dropPostingsInfoCache(DPIC_BOTH);
  }
else
  header('Location: '.remakeURI($faildir,
                                array(),
				array('err' => $err)).'#error');
dbClose();
?>
