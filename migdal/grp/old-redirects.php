<?php
# @(#) $Id$

require_once('lib/old-ids.php');
require_once('lib/post.php');
require_once('lib/catalog.php');
require_once('lib/users.php');

function oldRedirectLinks($args)
{
$id=postProcessInteger($args['topic_id']);
if($id<=0)
  return remakeMakeURI('/links/',$args);
else
  return remakeMakeURI('/'.catalogById(getNewId('topics',$id)),$args,
                       array('topic_id'));
}

function oldRedirectRegister($args)
{
return remakeMakeURI('/register/',$args);
}

function oldRedirectUrls($args)
{
return remakeMakeURI('/links/urls/',$args,array('offset'));
}

function oldRedirectUserinfo($args)
{
$id=postProcessInteger($args['id']);
$user=getUserById($id);
if($user->getLogin()!='')
  return remakeMakeURI('/users/'.$user->getFolder().'/',$args,array('id'));
else
  return '';
}

function oldRedirectUsers($args)
{
return remakeMakeURI('/users/',$args,array('offset'));
}
?>
