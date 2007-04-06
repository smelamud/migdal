<?php
# @(#) $Id$

require_once('lib/old-ids.php');
require_once('lib/post.php');
require_once('lib/catalog.php');
require_once('lib/users.php');
require_once('lib/postings.php');

function trapArchive($args)
{
return remakeMakeURI('/archive/',$args);
}

function trapArticle($args)
{
$id=postProcessInteger($args['artid']);
$posting=getPostingById(getNewId('postings',$id));
if($posting->getId()>0)
  return remakeMakeURI($posting->getGrpDetailsHref(),$args,array('artid'));
else
  return '';
}

function trapArticle_times($args)
{
$id=postProcessInteger($args['artid']);
$posting=getPostingById(getNewId('postings',$id));
if($posting->getId()>0)
  return remakeMakeURI($posting->getGrpDetailsHref(),$args,array('artid'));
else
  return '';
}

function trapBook_chapter($args)
{
$id=postProcessInteger($args['chapid']);
$posting=getPostingById(getNewId('postings',$id));
if($posting->getId()>0)
  return remakeMakeURI($posting->getGrpDetailsHref(),$args,array('chapid'));
else
  return '';
}

function trapBook_chapter_split($args)
{
$id=postProcessInteger($args['chapid']);
$posting=getPostingById(getNewId('postings',$id));
if($posting->getId()>0)
  return remakeMakeURI($posting->getGrpDetailsHref(),$args,array('chapid'));
else
  return '';
}

function trapBook($args)
{
$id=postProcessInteger($args['bookid']);
$posting=getPostingById(getNewId('postings',$id));
if($posting->getId()>0)
  return remakeMakeURI($posting->getGrpDetailsHref(),$args,array('bookid'));
else
  return '';
}

function trapLinks($args)
{
$id=postProcessInteger($args['topic_id']);
if($id<=0)
  return remakeMakeURI('/links/',$args);
else
  return remakeMakeURI('/'.catalogById(getNewId('topics',$id)),$args,
                       array('topic_id'));
}

function trapPosting($args)
{
$id=postProcessInteger($args['postid']);
$posting=getPostingById(getNewId('postings',$id));
if($posting->getId()>0)
  return remakeMakeURI($posting->getGrpDetailsHref(),$args,array('postid'));
else
  return '';
}

function trapRegister($args)
{
return remakeMakeURI('/register/',$args);
}

function trapUrls($args)
{
return remakeMakeURI('/links/urls/',$args,array('offset'));
}

function trapUserinfo($args)
{
$id=postProcessInteger($args['id']);
$user=getUserById($id);
if($user->getLogin()!='')
  return remakeMakeURI('/users/'.$user->getFolder().'/',$args,array('id'));
else
  return '';
}

function trapUsers($args)
{
return remakeMakeURI('/users/',$args,array('offset'));
}
?>
