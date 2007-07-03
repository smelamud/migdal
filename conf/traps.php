<?php
# @(#) $Id$

require_once('lib/old-ids.php');
require_once('lib/post.php');
require_once('lib/catalog.php');
require_once('lib/users.php');
require_once('lib/postings.php');
require_once('lib/complains.php');

function trapActions_userconfirm($args)
{
return remakeMakeURI('/actions/user/confirm/',$args);
}

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

function trapBook_print($args)
{
$id=postProcessInteger($args['bookid']);
$posting=getPostingById(getNewId('postings',$id));
if($posting->getId()>0)
  return remakeMakeURI($posting->getGrpDetailsHref().'print/',$args,
                       array('bookid'));
else
  return '';
}

function trapBook_split($args)
{
$id=postProcessInteger($args['bookid']);
$posting=getPostingById(getNewId('postings',$id));
if($posting->getId()>0)
  return remakeMakeURI($posting->getGrpDetailsHref(),$args,array('bookid'));
else
  return '';
}

function trapBook_static($args)
{
$id=postProcessInteger($args['bookid']);
$posting=getPostingById(getNewId('postings',$id));
if($posting->getId()>0)
  return remakeMakeURI($posting->getGrpDetailsHref(),$args,array('bookid'));
else
  return '';
}

function trapChat($args)
{
return remakeMakeURI('/chat-archive/',$args);
}

function trapChatboard($args)
{
return remakeMakeURI('/chat-archive/',$args);
}

function trapChatconsole($args)
{
return remakeMakeURI('/chat-archive/',$args);
}

function trapComplainforum($args)
{
$id=postProcessInteger($args['compid']);
$id=getNewId('complains',$id);
if(complainExists($id))
  return remakeMakeURI("/complains/$id/",$args,array('compid'));
else
  return '';
}

function trapComplains($args)
{
return remakeMakeURI('/complains/',$args);
}

function trapEvent($args)
{
$id=postProcessInteger($args['topic_id']);
$topic=getTopicById(getNewId('topics',$id));
if($topic->getId()>0)
  return remakeMakeURI('/'.$topic->getCatalog(),$args,array('topic_id'));
else
  return '';
}

function trapEvents_english($args)
{
return remakeMakeURI('/events/',$args);
}

function trapEvents($args)
{
return remakeMakeURI('/migdal/events/',$args);
}

function trapForumcatalog($args)
{
return remakeMakeURI('/forum/',$args,array('topic_id'));
}

function trapForum($args)
{
$id=postProcessInteger($args['msgid']);
$id=getNewId('postings',$id);
if(postingExists($id))
  return remakeMakeURI("/forum/$id/",$args,array('msgid'));
else
  return '';
}

function trapGallery($args)
{
$topic_id=postProcessInteger($args['topic_id']);
$user_id=postProcessInteger($args['user_id']);
$general=postProcessInteger($args['general']);
$rm=array('topic_id','user_id','general','grp');
if($topic_id==143) /* Галерея музея */
  $topic_id=175;
if($user_id<=0)
  if($topic_id<=0)
    return remakeMakeURI('/gallery/',$args,$rm);
  else
    {
    $topic=getTopicById(getNewId('topics',$topic_id));
    if($topic->getId()>0)
      if($general<=0)
        return remakeMakeURI('/gallery/'.$topic->getCatalog(),$args,$rm);
      else
        return remakeMakeURI('/'.$topic->getCatalog().'gallery/',$args,$rm);
    else
      return '';
    }
else
  {
  $topic=getTopicById(getNewId('topics',$topic_id));
  if($topic->getId()<=0)
    return '';
  $user=getUserById($user_id);
  if($user->getId()<=0)
    return '';
  return remakeMakeURI('/gallery/'.$topic->getCatalog().$user->getFolder().'/',
                       $args,$rm);
  }
}

function trapHalom_main($args)
{
return remakeMakeURI('/migdal/events/',$args);
}

function trapHalom($args)
{
$postid=postProcessInteger($args['postid']);
$topic_id=postProcessInteger($args['topic_id']);
$day=postProcessInteger($args['day']);
$rm=array('postid','topic_id','day');
if($day<=0)
  $day=1;
if($postid>0)
  {
  $posting=getPostingById(getNewId('postings',$postid));
  if($posting->getId()>0)
    return remakeMakeURI($posting->getGrpDetailsHref(),$args,$rm);
  else
    return '';
  }
else
  {
  $topic=getTopicById(getNewId('topics',$topic_id));
  if($topic->getId()>0)
    return remakeMakeURI('/'.$topic->getCatalog()."day-$day/",$args,$rm);
  else
    return '';
  }
}

function trapHelp($args)
{
$id=postProcessInteger($args['artid']);
$posting=getPostingById(getNewId('postings',$id));
if($posting->getId()>0)
  return remakeMakeURI($posting->getGrpDetailsHref(),$args,array('artid'));
else
  return '';
}

function trapIndex($args)
{
$id=postProcessInteger($args['topic_id']);
if($id==5) /* Еврейский Интернет */
  return remakeMakeURI('/links/',$args,array('topic_id'));
if($id==13) /* КЕС */
  return remakeMakeURI('/migdal/jcc/student/',$args,array('topic_id'));
if($id==24) /* Ту би-Шват */
  return remakeMakeURI('/judaism/',$args,array('topic_id'));
if($id==146) /* Методический центр */
  return remakeMakeURI('/migdal/methodology/books/',$args,array('topic_id'));
if($id<=0)
  return remakeMakeURI('/',$args);
else
  return remakeMakeURI('/'.catalogById(getNewId('topics',$id)),$args,
                       array('topic_id'));
}

function trapJcc($args)
{
return remakeMakeURI('/migdal/jcc/',$args);
}

function trapKaitana_main($args)
{
return remakeMakeURI('/migdal/events/',$args);
}

function trapKaitana($args)
{
$postid=postProcessInteger($args['postid']);
$topic_id=postProcessInteger($args['topic_id']);
$day=postProcessInteger($args['day']);
$rm=array('postid','topic_id','day');
if($day<=0)
  $day=1;
if($postid>0)
  {
  $posting=getPostingById(getNewId('postings',$postid));
  if($posting->getId()>0)
    return remakeMakeURI($posting->getGrpDetailsHref(),$args,$rm);
  else
    return '';
  }
else
  {
  if($topic_id<=0)
    return remakeMakeURI("/migdal/events/kaitanot/5762/summer/day-$day/",
                         $args,$rm);;
  $topic=getTopicById(getNewId('topics',$topic_id));
  if($topic->getId()>0)
    return remakeMakeURI('/'.$topic->getCatalog()."day-$day/",$args,$rm);
  else
    return '';
  }
}

function trapLib_earview($args)
{
$id=postProcessInteger($args['image_id']);
$image=getImageById(getNewId('images',$id));
if($image->getId()<=0)
  return '';
return remakeMakeURI($image->getLargeImageURL(),$args,
                     array('message_id','image_id'));
}

function trapLib_image($args)
{
$id=postProcessInteger($args['id']);
$size=postProcessString($args['size']);
if($size!='small' && $size!='large')
  $size='small';
$image=getImageById(getNewId('images',$id));
if($image->getId()<=0)
  return '';
return remakeMakeURI($size=='small' ? $image->getSmallImageURL()
                                    : $image->getLargeImageURL(),
		     $args,array('id','size'));
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

function trapMethodic_center($args)
{
return remakeMakeURI('/migdal/methodology/',$args);
}

function trapMethodics($args)
{
return remakeMakeURI('/migdal/methodology/books/',$args);
}

function trapMigdal($args)
{
$id=postProcessInteger($args['artid']);
if($id<=0)
  return remakeMakeURI('/migdal/',$args);
echo "$id<br>";
$posting=getPostingById(getNewId('postings',$id));
echo getNewId('postings',$id),'<br>';
exit;
if($posting->getId()>0)
  return remakeMakeURI($posting->getGrpDetailsHref(),$args,array('artid'));
else
  return '';
}

function trapMigdal_library_news($args)
{
return remakeMakeURI('/migdal/library/novelties/',$args);
}

function trapMigdal_library($args)
{
return remakeMakeURI('/migdal/library/',$args);
}

function trapMigdal_news($args)
{
$dirs=array(177 => 'museum',
            174 => 'mazltov',
	    259 => 'beitenu');
$id=postProcessInteger($args['topic_id']);
if($id<=0)
  return remakeMakeURI('/migdal/news/',$args,array('topic_id'));
else
  if(isset($dirs[$id]))
    return remakeMakeURI("/migdal/{$dirs[$id]}/news/",$args,array('topic_id'));
  else
    return '';
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

function trapPrintings($args)
{
return remakeMakeURI('/migdal/printings/',$args);
}

function trapRegister($args)
{
return remakeMakeURI('/register/',$args);
}

function trapSearch($args)
{
return remakeMakeURI('/search/',$args);
}

function trapTaglit($args)
{
return remakeMakeURI('/taglit/',$args);
}

function trapTaglit_user($args)
{
$user_id=postProcessInteger($args['user_id']);
$user=getUserById($user_id);
if($user->getId()<=0)
  return '';
return remakeMakeURI('/taglit/'.$user->getFolder().'/',$args,array('user_id'));
}

function trapThumbnail($args)
{
$id=postProcessInteger($args['id']);
$image=getImageById(getNewId('images',$id));
if($image->getId()<=0)
  return '';
return remakeMakeURI($image->getSmallImageURL(),$args,array('id','size'));
}

function trapTimes($args)
{
$issue=postProcessInteger($args['issue']);
if($issue<=0)
  return remakeMakeURI('/times/',$args);
else
  return remakeMakeURI("/times/$issue/",$args,array('issue'),
                       array('issue' => $issue));
}

function trapTips($args)
{
return remakeMakeURI('/tips/',$args);
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

function trapUserinfo_panel($args)
{
return trapUserinfo($args);
}

function trapUserlost($args)
{
return remakeMakeURI('/remember-password/',$args);
}

function trapUsers($args)
{
return remakeMakeURI('/users/',$args,array('offset'));
}

function trapVeterans($args)
{
return remakeMakeURI('/veterans/',$args);
}

function trapVeterans_user($args)
{
$user_id=postProcessInteger($args['user_id']);
$user=getUserById($user_id);
if($user->getId()<=0)
  return '';
return remakeMakeURI('/veterans/'.$user->getFolder().'/',$args,array('user_id'));
}
?>
