<?php
# @(#) $Id$

require_once('lib/errorreporting.php');
require_once('lib/database.php');
require_once('lib/session.php');
require_once('lib/post.php');
require_once('lib/errors.php');
require_once('lib/utils.php');
require_once('lib/random.php');
require_once('lib/postings.php');

function modifyPosting($editid,$domain)
{
global $userAdminDomain;

if(!$userAdminDomain)
  return EDM_NO_CHANGE;
if(!postingExists($editid))
  return EDM_NO_POSTING;
$result=mysql_query("update postings
                     set subdomain=$domain
		     where id=$editid")
	     or die('������ SQL ��� ��������� ��������� ���������');
return EDM_OK;
}

postInteger('editid');
postInteger('domain');

dbOpen();
session($sessionid);
$err=modifyPosting($editid,$domain);
if($err==EDM_OK)
  header('Location: '.remakeURI($okdir,array(),array('reload' => random(0,999))));
else
  header('Location: '.remakeURI($faildir,array(),array('err' => $err)).'#error');
dbClose();
?>
