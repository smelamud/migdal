<?php
# @(#) $Id$

require_once('lib/errorreporting.php');
require_once('lib/database.php');
require_once('lib/session.php');
require_once('lib/post.php');
require_once('lib/errors.php');
require_once('lib/postings.php');
require_once('lib/utils.php');

function shadowPosting($postid)
{
global $userModerator;

if(!$userModerator)
  return ESP_NO_SHADOW;
$result=mysql_query("select *
                     from postings
		     where id=$postid");
if(!$result || mysql_num_rows($result)==0)
  return ESP_POSTING_ABSENT;
$values=mysql_fetch_assoc($result);
unset($values['id']);
unset($values['ident']);
unset($values['read_count']);
unset($values['last_read']);
unset($values['vote']);
unset($values['vote_count']);
$values['shadow']=1;
$result=mysql_query(makeInsert('postings',$values));
if(!$result)
  return ESP_COPY_SQL;
return ESP_OK;
}

postInteger('postid');

dbOpen();
session();
$err=shadowPosting($postid);
if($err==ESP_OK)
  header('Location: '.remakeURI($okdir,
                                array('err'),
				array('reload' => random(0,999))));
else
  header('Location: '.remakeURI($faildir,
                                array(),
				array('err' => $err)).'#error');
dbClose();
?>
