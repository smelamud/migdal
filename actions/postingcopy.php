<?php
# @(#) $Id$

# FIXME копирование large_imageset неработоспособно

require_once('lib/errorreporting.php');
require_once('lib/database.php');
require_once('lib/session.php');
require_once('lib/post.php');
require_once('lib/errors.php');
require_once('lib/postings.php');
require_once('lib/postings-info.php');
require_once('lib/images.php');
require_once('lib/sql.php');

$stotextImages=array();

function copyImageSet($image_set)
{
global $stotextImages;

if($image_set==0)
  return 0;
$new_set=0;
$result=sql("select *
	     from images
	     where image_set=$image_set",
	    'copyImageSet');
if(mysql_num_rows($result)==0)
  return 0;
while($row=mysql_fetch_assoc($result))
     {
     $image=new Image($row);
     $image->id=0;
     if($new_set==0)
       {
       $image->setImageSet(0);
       $image->store();
       $new_set=$image->getImageSet();
       }
     else
       {
       $image->setImageSet($new_set);
       $image->store();
       }
     $stimage=getStotextImageByImageId($row['id']);
     if($stimage->getStotextId()!=0)
       {
       $stimage->image_id=$image->getId();
       $stotextImages[]=$stimage;
       }
     }
return $new_set;
}

function storeStotextImages($stotext_id)
{
global $stotextImages;

foreach($stotextImages as $stimage)
       {
       $stimage->stotext_id=$stotext_id;
       $stimage->store();
       }
}

function copyPosting($postid)
{
global $userModerator;

if(!$userModerator)
  return EPC_NO_COPY;
$posting=getPostingById($postid,GRP_ALL,-1,-1,SELECT_TOPICS);
if($posting->getId()<=0)
  return EPC_NO_POSTING;
$posting->id=0;
$posting->ident='';
$posting->message_id=0;
$posting->setStotextId(0);
$posting->setImageSet(copyImageSet($posting->getImageSet()));
$posting->setLargeImageSet(copyImageSet($posting->getLargeImageSet()));
$posting->store();
storeStotextImages($posting->getStotextId());
return EG_OK;
}

postInteger('postid');

dbOpen();
session();
$err=copyPosting($postid);
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
