<?php
# @(#) $Id$

require_once('lib/messages.php');
require_once('lib/stotext-images.php');
require_once('lib/selectiterator.php');
require_once('lib/limitselect.php');
require_once('lib/grps.php');
require_once('lib/topics.php');
require_once('lib/text.php');
require_once('lib/paragraphs.php');

class Posting
      extends Message
{
var $ident;
var $message_id;
var $topic_id;
var $topic_name;
var $personal_id;
var $grp;
var $priority;

function Posting($row)
{
$this->Message($row);
$this->grp=GRP_ALL;
}

function getCorrespondentVars()
{
$list=Message::getCorrespondentVars();
array_push($list,'ident','topic_id','grp','personal_id','priority');
return $list;
}

function getWorldPostingVars()
{
return array('message_id','topic_id','grp','personal_id');
}

function getAdminPostingVars()
{
return array('ident','priority');
}

function getNormalPosting($isAdmin=false)
{
$normal=$this->collectVars($this->getWorldPostingVars());
if($isAdmin)
  $normal=array_merge($normal,$this->collectVars($this->getAdminPostingVars()));
return $normal;
}

function store()
{
global $userModerator;

$result=Message::store('message_id');
if(!$result)
  return $result;
$normal=$this->getNormalPosting($userModerator);
if($this->id)
  $result=mysql_query(makeUpdate('postings',$normal,array('id' => $this->id)));
else
  {
  $result=mysql_query(makeInsert('postings',$normal));
  $this->id=mysql_insert_id();
  }
return $result;
}

function hasTopic()
{
return $this->getLocalConf('HasTopic');
}

function mandatoryTopic()
{
return $this->hasTopic() && $this->getLocalConf('MandatoryTopic');
}

function hasIdent()
{
return $this->getLocalConf('HasIdent');
}

function mandatoryIdent()
{
return $this->hasIdent() && $this->getLocalConf('MandatoryIdent');
}

function getIdent()
{
return $this->ident;
}

function getMessageId()
{
return $this->message_id;
}

function getGrp()
{
return $this->grp;
}

function getPriority()
{
return $this->priority;
}

function getTopicId()
{
return $this->topic_id;
}

function getTopicName()
{
return $this->topic_name;
}

function getPersonalId()
{
return $this->personal_id;
}

}

class Forum
      extends Posting
{

function Forum($row)
{
$this->Posting($row);
$this->grp=GRP_FORUMS;
}

}

class News
      extends Posting
{

function News($row)
{
$this->Posting($row);
$this->grp=GRP_NEWS;
}

}

class Gallery
      extends Posting
{

function Gallery($row)
{
$this->Posting($row);
$this->grp=GRP_GALLERY;
}

}

class Article
      extends Posting
{

function Article($row)
{
$this->large_format=TF_TEX;
$this->Posting($row);
$this->grp=GRP_ARTICLES;
}

}

function newPosting($row)
{
$name=getGrpClassName($row['grp']);
return new $name($row);
}

function newGrpPosting($grp,$row=array())
{
$name=getGrpClassName($grp);
return new $name($row);
}

class PostingListIterator
      extends LimitSelectIterator
{

function PostingListIterator($grp,$topic=-1,$limit=10,$offset=0,$personal=0)
{
global $userId,$userModerator;

$hide=$userModerator ? 2 : 1;
$topicFilter=$topic<0 ? '' 
                      : ' and '.byIdent($topic,'topic_id','topics.ident').' ';
$this->LimitSelectIterator(
       'Message',
       "select postings.id as id,postings.message_id as message_id,
               messages.stotext_id as stotext_id,body,subject,grp,sent,
	       topic_id,sender_id,messages.hidden as hidden,
	       messages.disabled as disabled,users.hidden as sender_hidden,
	       images.image_set as image_set,images.id as image_id,
	       images.has_large as has_large_image,images.title as title,
	       topics.name as topic_name,
	       login,gender,email,hide_email,rebe,
	       count(forums.up) as answer_count
	from postings
	     left join messages
	          on postings.message_id=messages.id
	     left join stotexts
	          on stotexts.id=messages.stotext_id
	     left join images
		  on stotexts.image_set=images.image_set
	     left join topics
		  on postings.topic_id=topics.id
	     left join users
		  on messages.sender_id=users.id
	     left join forums
		  on messages.id=forums.up
	where (messages.hidden<$hide or sender_id=$userId) and
	      (messages.disabled<$hide or sender_id=$userId) and
	      personal_id=$personal and (grp & $grp)<>0 $topicFilter
	group by messages.id
	order by sent desc",$limit,$offset,
       "select count(*)
	from postings
	     left join messages
	          on postings.message_id=messages.id
	where (messages.hidden<$hide or sender_id=$userId) and
	      (messages.disabled<$hide or sender_id=$userId) and
	      personal_id=$personal and (grp & $grp)<>0 $topicFilter");
      /* здесь нужно поменять, если будут другие ограничения на
	 просмотр TODO */
}

function create($row)
{
return newPosting($row);
}

}

class PostingParagraphIterator
      extends ParagraphIterator
{
var $images;

function PostingParagraphIterator($posting)
{
$this->ParagraphIterator($posting->getLargeFormat(),$posting->getLargeBody());
$this->loadImages($posting);
}

function loadImages($posting)
{
$this->images=array();
$sid=$posting->getStotextId();
settype($sid,'integer');
$result=mysql_query("select stotext_id,par,image_id,placement,
                            has_large as has_large_image,title
		     from stotext_images
		          left join images
			       on images.id=stotext_images.image_id
		     where stotext_id=$sid");
if(!$result)
  die('Ошибка SQL при выборке иллюстраций к постингу');
while($row=mysql_fetch_assoc($result))
     {
     $image=new StotextImage($row);
     $this->images[$image->getPar()]=$image;
     }
}

function next()
{
$paragraph=ParagraphIterator::next();
if($paragraph)
  $paragraph->setImage($this->images[$paragraph->getNumber()]);
return $paragraph;
}

}

function getPostingById($id,$grp=GRP_ALL,$topic=0)
{
global $userId,$userModerator;

$hide=$userModerator ? 2 : 1;
$result=mysql_query('select postings.id as id,ident,message_id,stotext_id,body,
                            large_filename,large_format,large_body,
			    large_imageset,subject,topic_id,personal_id,
			    sender_id,grp,priority,image_set,hidden,disabled
		     from postings
		          left join messages
			       on postings.message_id=messages.id
	                  left join stotexts
	                       on stotexts.id=messages.stotext_id
		     where postings.'.byIdent($id).
		         " and (hidden<$hide or sender_id=$userId)
			   and (disabled<$hide or sender_id=$userId)")
		    /* здесь нужно поменять, если будут другие ограничения на
		       просмотр TODO */
	     or die('Ошибка SQL при выборке постинга');
return mysql_num_rows($result)>0 ? newPosting(mysql_fetch_assoc($result))
                                 : newGrpPosting($grp,
				                 array('topic_id' => $topic));
}

function getFullPostingById($id,$grp=GRP_ALL)
{
global $userId,$userModerator;

$hide=$userModerator ? 2 : 1;
$result=mysql_query(
	"select postings.id as id,postings.ident as ident,
	        postings.message_id as message_id,
		messages.stotext_id as stotext_id,body,large_format,large_body,
		subject,grp,sent,topic_id,sender_id,messages.hidden as hidden,
		disabled,users.hidden as sender_hidden,
		images.image_set as image_set,images.id as image_id,
		topics.name as topic_name,images.has_large as has_large_image,
		images.title as title,login,gender,email,hide_email,rebe,
	        count(forums.up) as answer_count
	 from postings
	      left join messages
	           on postings.message_id=messages.id
	      left join stotexts
	           on stotexts.id=messages.stotext_id
	      left join images
		   on stotexts.image_set=images.image_set
	      left join topics
		   on postings.topic_id=topics.id
	      left join users
		   on messages.sender_id=users.id
	      left join forums
	 	   on messages.id=forums.up
	 where (messages.hidden<$hide or sender_id=$userId) and
	       (messages.disabled<$hide or sender_id=$userId) and
	       postings.".byIdent($id).
       ' group by messages.id')
      /* здесь нужно поменять, если будут другие ограничения на
	 просмотр TODO */
 or die('Ошибка SQL при выборке постинга');
return mysql_num_rows($result)>0 ? newPosting(mysql_fetch_assoc($result))
                                 : newGrpPosting($grp);
}
?>
