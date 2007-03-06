<?php
# @(#) $Id$

require_once('lib/topics.php');
require_once('lib/users.php');
require_once('lib/complains.php');

function titleUserLogin($id)
{
return getUserLoginById($id);
}

function titleUserFullName($id)
{
$user=getUserById($id);
return $user->getFullName();
}

function titleTopicName($id)
{
$topic=getTopicNameById($id);
return $topic->getSubject();
}

function titleTopicBodyOrName($id)
{
$topic=getTopicById($id);
return $topic->getBody()=='' ? $topic->getSubject() : $topic->getBody();
}

function titlePostingHeading($id)
{
$posting=getPostingById($id);
return $posting->getHeading();
}

function titlePostingGrpWhatV($id)
{
$posting=getPostingById($id);
return $posting->getGrpWhatV();
}

function titleArchiveDate($id)
{
$posting=getPostingById($id);
return formatAnyDate('Rj',$posting->getSent()).' '
       .formatAnyDate('Rk',$posting->getSent()).' '
       .formatAnyDate('RY',$posting->getSent());
}

function titlePostingIssues($id)
{
$posting=getPostingById($id);
return $posting->getIssues();
}

function titleComplainSubject($id)
{
$complain=getComplainById($id);
return $complain->getSubject();
}
?>
