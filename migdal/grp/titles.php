<?php
# @(#) $Id$

require_once('lib/topics.php');

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

function titlePostingSubjectDesc($id)
{
$posting=getPostingById($id);
return $posting->getSubjectDesc();
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
?>
