<?php
# @(#) $Id$

require_once('lib/errorreporting.php');
require_once('lib/database.php');
require_once('lib/session.php');
require_once('lib/post.php');
require_once('lib/errors.php');
require_once('lib/postings.php');
require_once('lib/postings-info.php');
require_once('lib/utils.php');
require_once('lib/sql.php');

function shadowPosting($postid)
{
global $userModerator;

if(!$userModerator)
  return ESP_NO_SHADOW;
$result=sql("select *
	     from postings
	     where id=$postid",
	    'shadowPosting','get');
if(mysql_num_rows($result)==0)
  return ESP_POSTING_ABSENT;
$values=mysql_fetch_assoc($result);
unset($values['id']);
unset($values['ident']);
unset($values['last_read']);
unset($values['vote']);
unset($values['vote_count']);
$values['shadow']=1;
sql(makeInsert('postings',
               $values),
    'shadowPosting','insert');
journal(makeInsert('postings',
                   jencodeVars($values,array('message_id' => 'messages',
		                             'topic_id' => 'topics',
                                             'personal_id' => 'users'))),
        'postings',sql_insert_id());
return EG_OK;
}

postInteger('postid');

dbOpen();
session();
$err=shadowPosting($postid);
if($err==EG_OK)
  {
  header('Location: '.remakeURI($okdir,
                                array('err'),
				array('reload' => random(0,999))));
  dropPostingsInfoCache(DPIC_POSTINGS);
  }
else
  header('Location: '.remakeURI($faildir,
                                array(),
				array('err' => $err)).'#error');
dbClose();
?>
