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
require_once('lib/sort.php');
require_once('lib/track.php');
require_once('lib/random.php');
require_once('lib/users.php');

class Posting
      extends Message
{
var $ident;
var $message_id;
var $topic_id;
var $topic_ident;
var $topic_name;
var $topic_description;
var $personal_id;
var $grp;
var $priority;
var $read_count;

function Posting($row)
{
$this->Message($row);
$this->grp=GRP_NONE;
}

function getCorrespondentVars()
{
$list=Message::getCorrespondentVars();
array_push($list,'ident','topic_id','grp','personal_id','priority','index1');
return $list;
}

function getWorldPostingVars()
{
return array('message_id','topic_id','grp','personal_id','index1');
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

function isValid()
{
return $this->grp!=GRP_NONE;
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

function getTopicIdent()
{
return $this->topic_ident;
}

function getTopicName()
{
return $this->topic_name;
}

function getTopicDescription()
{
return $this->topic_description;
}

function getPersonalId()
{
return $this->personal_id;
}

function getReadCount()
{
return $this->read_count;
}

function getIndex0()
{
return $this->index0;
}

function getIndex1()
{
return $this->index1;
}

}

require_once('grp/postings.php');

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

function newDetailedPosting($grp,$topic_id=-1,$sender_id=0)
{
return newPosting(array('grp'       => $grp,
                        'topic_id'  => $topic_id,
			'sender_id' => $sender_id));
}

class PostingListIterator
      extends LimitSelectIterator
{

function PostingListIterator($grp,$topic=-1,$recursive=false,$limit=10,
                             $offset=0,$personal=0,$sort=SORT_SENT,
			     $withAnswers=GRP_NONE,$user=0,$index1=-1,$later=0)
{
global $userId,$userModerator;

$hide=$userModerator ? 2 : 1;
$topicFilter=($topic<0 || $recursive && $topic==0) ? ''
             : ' and topics.'.byIdentRecursive('topics',$topic,$recursive);
$userFilter=$user<=0 ? '' : " and messages.sender_id=$user ";
$order=getOrderBy($sort,
       array(SORT_SENT     => 'sent desc',
             SORT_NAME     => 'subject',
             SORT_ACTIVITY => 'age desc',
	     SORT_READ     => 'read_count desc,sent desc',
	     SORT_INDEX0   => 'index0',
	     SORT_INDEX1   => 'index1',
	     SORT_RINDEX1  => 'index1 desc'));
$answerFilter=$withAnswers!=GRP_NONE
              ? $withAnswers==GRP_ALL
	        ? 'having count(forummesgs.id)<>0'
		: "having (grp & $withAnswers)=0 or count(forummesgs.id)<>0"
	      : '';
$countAnswerFilter=$withAnswers ? ' and forummesgs.id is not null' : '';
$index1Filter=$index1>=0 ? "and index1=$index1" : '';
$sentFilter=$later>0 ? "and unix_timestamp(messages.sent)>$later" : '';
$this->LimitSelectIterator(
       'Message',
       "select postings.id as id,postings.message_id as message_id,
               messages.stotext_id as stotext_id,stotexts.body as body,
	       messages.subject as subject,messages.author as author,
	       messages.source as source,grp,messages.sent as sent,topic_id,
	       messages.url as url,messages.sender_id as sender_id,
	       messages.hidden as hidden,
	       messages.disabled as disabled,users.hidden as sender_hidden,
	       index1,images.image_set as image_set,images.id as image_id,
	       images.has_large as has_large_image,images.title as title,
	       if(images.has_large,length(images.large),
	                           length(images.small)) as image_size,
 	       if(images.has_large,images.large_x,images.small_x) as image_x,
 	       if(images.has_large,images.large_y,images.small_y) as image_y,
	       topics.name as topic_name,topictexts.body as topic_description,
	       topics.ident as topic_ident,
	       login,gender,email,hide_email,rebe,
	       read_count,
	       count(forummesgs.id) as answer_count,
	       max(forummesgs.sent) as last_answer,
	       ifnull(max(forummesgs.sent),messages.sent) as age
	from postings
	     left join messages
	          on postings.message_id=messages.id
	     left join stotexts
	          on stotexts.id=messages.stotext_id
	     left join images
		  on stotexts.image_set=images.image_set
	     left join topics
		  on postings.topic_id=topics.id
	     left join stotexts as topictexts
	          on topictexts.id=topics.stotext_id
	     left join users
		  on messages.sender_id=users.id
	     left join forums
		  on messages.id=forums.up
	     left join messages as forummesgs
	          on forums.message_id=forummesgs.id and
	             (forummesgs.hidden<$hide or
		      forummesgs.sender_id=$userId) and
	             (forummesgs.disabled<$hide or
		      forummesgs.sender_id=$userId)
	where (messages.hidden<$hide or messages.sender_id=$userId) and
	      (messages.disabled<$hide or messages.sender_id=$userId) and
	      personal_id=$personal and (grp & $grp)<>0 $topicFilter
	      $userFilter $index1Filter $sentFilter
	group by messages.id
	$answerFilter
	$order",$limit,$offset,
       "select count(distinct postings.id)
	from postings
	     left join messages
	          on postings.message_id=messages.id
	     left join topics
		  on postings.topic_id=topics.id
	     left join forums
		  on messages.id=forums.up
	     left join messages as forummesgs
	          on forums.message_id=forummesgs.id and
	             (forummesgs.hidden<$hide or
		      forummesgs.sender_id=$userId) and
	             (forummesgs.disabled<$hide or
		      forummesgs.sender_id=$userId)
	where (messages.hidden<$hide or messages.sender_id=$userId) and
	      (messages.disabled<$hide or messages.sender_id=$userId) and
	      personal_id=$personal and (grp & $grp)<>0 $countAnswerFilter
	      $topicFilter $userFilter");
      /* здесь нужно поменять, если будут другие ограничения на
	 просмотр TODO */
}

function create($row)
{
return newPosting($row);
}

}

class PictureListIterator
      extends PostingListIterator
{

var $cols;

function PictureListIterator($grp,$topic=-1,$recursive=false,$rows=4,$cols=5,
                             $offset=0,$personal=0,$sort=SORT_SENT,
			     $withAnswers=GRP_NONE,$user=0)
{
$this->cols=$cols;
$this->PostingListIterator($grp,$topic,$recursive,$rows*$cols,$offset,
                           $personal,$sort,$withAnswers,$user);
}

function isEol()
{
return ($this->getPosition() % $this->cols)==$this->cols-1;
}

}

class PostingUsersIterator
      extends SelectIterator
{

function PostingUsersIterator($grp=GRP_ALL,$topic_id=-1,$recursive=false)
{
global $userId,$userModerator;

$hide=$userModerator ? 2 : 1;
$topicFilter=($topic_id<0 || $recursive && $topic_id==0) ? ''
             : ' and topics.'.byIdentRecursive('topics',$topic_id,$recursive);
$this->SelectIterator(
       'User',
       "select distinct users.id as id,login,gender,email,hide_email,rebe,
                        users.name as name,jewish_name,surname,
			max(sent) as last_message
        from users
	     left join messages
	          on messages.sender_id=users.id
	     left join postings
	          on postings.message_id=messages.id
	     left join topics
	          on postings.topic_id=topics.id
	where (messages.hidden<$hide or messages.sender_id=$userId) and
	      (messages.disabled<$hide or messages.sender_id=$userId) and
              (grp & $grp)<>0 $topicFilter
	group by users.id
	order by surname,jewish_name,name");
      /* здесь нужно поменять, если будут другие ограничения на
	 просмотр TODO */
}

}

class PostingParagraphIterator
      extends ParagraphIterator
{
var $images;

function PostingParagraphIterator($posting)
{
$this->ParagraphIterator($posting->getLargeFormat(),$posting->getLargeBody(),
                         $posting->getMessageId());
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

class ArticleCoversIterator
      extends SelectIterator
{

function ArticleCoversIterator($articleGrp,$coverGrp)
{
global $userId,$userModerator;

$hide=$userModerator ? 2 : 1;
$this->SelectIterator(
       'Posting',
       "select distinct postings.index1 as index1, 
               cover_messages.source as source
        from postings
             left join postings as covers
	          on postings.index1=covers.index1 and
		     (covers.grp & $coverGrp)<>0
	     left join messages
	          on postings.message_id=messages.id
	     left join messages as cover_messages
	          on covers.message_id=cover_messages.id
	where ((postings.grp & $articleGrp)<>0 or
	       (postings.grp & $coverGrp)<>0) and
	      (messages.hidden<$hide or messages.sender_id=$userId) and
              (messages.disabled<$hide or messages.sender_id=$userId) and
	      (covers.id is null or
	       (cover_messages.hidden<$hide or
	        cover_messages.sender_id=$userId) and
               (cover_messages.disabled<$hide or
	        cover_messages.sender_id=$userId))
	order by index1 desc");
}

function create($row)
{
return newGrpPosting(GRP_TIMES_COVER,$row);
}

}

function getPostingById($id=-1,$grp=GRP_ALL,$topic=0,$index1=-1)
{
global $userId,$userModerator;

$hide=$userModerator ? 2 : 1;
$filter=$id>=0 ? 'postings.'.byIdent($id)
               : ($index1>=0 ? "postings.index1=$index1 and (grp & $grp)<>0"
	                     : '');
$result=mysql_query("select postings.id as id,ident,message_id,stotext_id,body,
                            large_filename,large_format,large_body,
			    large_imageset,subject,author,source,url,topic_id,
			    personal_id,sender_id,grp,priority,image_set,
			    index1,hidden,disabled
		     from postings
		          left join messages
			       on postings.message_id=messages.id
	                  left join stotexts
	                       on stotexts.id=messages.stotext_id
		     where $filter
		           and (hidden<$hide or sender_id=$userId)
			   and (disabled<$hide or sender_id=$userId)")
		    /* здесь нужно поменять, если будут другие ограничения на
		       просмотр TODO */
	     or die('Ошибка SQL при выборке постинга'.mysql_error());
return mysql_num_rows($result)>0
       ? newPosting(mysql_fetch_assoc($result))
       : newGrpPosting($grp,array('topic_id' => idByIdent('topics',$topic)));
}

function getFullPostingById($id=-1,$grp=GRP_ALL,$index1=-1)
{
global $userId,$userModerator;

$hide=$userModerator ? 2 : 1;
$filter=$id>=0 ? 'postings.'.byIdent($id)
               : ($index1>=0 ? "postings.index1=$index1 and (grp & $grp)<>0"
	                     : '');
$result=mysql_query(
	"select postings.id as id,postings.ident as ident,
	        postings.message_id as message_id,
		messages.stotext_id as stotext_id,stotexts.body as body,
		stotexts.large_format as large_format,
		stotexts.large_body as large_body,messages.subject as subject,
		messages.author as author,messages.source as source,
		messages.url as url,grp,index1,
		messages.sent as sent,topic_id,messages.sender_id as sender_id,
		messages.hidden as hidden,messages.disabled as disabled,
		users.hidden as sender_hidden,images.image_set as image_set,
		images.id as image_id,length(images.large) as image_size,
		images.large_x as image_x,images.large_y as image_y,
		topics.name as topic_name,topictexts.body as topic_description,
		images.has_large as has_large_image,images.title as title,
		login,gender,email,hide_email,rebe,
	        read_count,
	        count(forummesgs.id) as answer_count,
	        max(forummesgs.sent) as last_answer
	 from postings
	      left join messages
	           on postings.message_id=messages.id
	      left join stotexts
	           on stotexts.id=messages.stotext_id
	      left join images
		   on stotexts.image_set=images.image_set
	      left join topics
		   on postings.topic_id=topics.id
	      left join stotexts as topictexts
	           on topictexts.id=topics.stotext_id
	      left join users
		   on messages.sender_id=users.id
	      left join forums
	 	   on messages.id=forums.up
	      left join messages as forummesgs
		   on forums.message_id=forummesgs.id and
		      (forummesgs.hidden<$hide or
		       forummesgs.sender_id=$userId) and
		      (forummesgs.disabled<$hide or
		       forummesgs.sender_id=$userId)
	 where (messages.hidden<$hide or messages.sender_id=$userId) and
	       (messages.disabled<$hide or messages.sender_id=$userId) and
	       $filter
         group by messages.id")
      /* здесь нужно поменять, если будут другие ограничения на
	 просмотр TODO */
 or die('Ошибка SQL при выборке постинга');
return mysql_num_rows($result)>0 ? newPosting(mysql_fetch_assoc($result))
                                 : newGrpPosting($grp);
}

function getLastPostingDate($grp=GRP_ALL,$topic_id=-1,$answers=GRP_NONE,
                            $user_id=0,$recursive=false)
{
global $userId,$userModerator;

$hide=$userModerator ? 2 : 1;
$tpf=$topic_id<0 ? '' : " and topics.".byIdentRecursive('topics',$topic_id,
                                                        $recursive);
$taf=$topic_id<0 ? '' : " and tops.".byIdentRecursive('topics',$topic_id,
                                                      $recursive);
$uf=$user_id>0 ? " and messages.sender_id=$user_id " : '';
$result=mysql_query(
        "select max(messages.sent)
         from messages
	      left join postings
	           on postings.message_id=messages.id
	      left join topics
	           on topics.id=postings.topic_id
	      left join forums
	           on forums.message_id=messages.id
	      left join messages as msgs
	           on forums.up=msgs.id
	      left join postings as posts
	           on posts.message_id=msgs.id
	      left join topics as tops
	           on tops.id=posts.topic_id
	 where (postings.id is not null or forums.id is not null) and
	       (messages.hidden<$hide or messages.sender_id=$userId) and
	       (messages.disabled<$hide or messages.sender_id=$userId) and
	       (forums.id is null or
	        (msgs.hidden<$hide or msgs.sender_id=$userId) and
	        (msgs.disabled<$hide or msgs.sender_id=$userId)) and
               (postings.id is null or (postings.grp & $grp)<>0 $tpf) and
               (forums.id is null or (posts.grp & $answers)<>0 $taf) $uf")
 or die('Ошибка SQL при определении даты последнего постинга/ответа');
$time=mysql_result($result,0,0);
return $time!='' ? strtotime($time) : 0;
}

function incPostingReadCount($id)
{
mysql_query('update postings
             set read_count=read_count+1,last_read=now()
	     where '.byIdent($id))
     or die('Ошибка SQL при обновлении счетчика прочтений постинга');
}

function getRandomPostingId($grp=GRP_ALL,$topic_id=-1,$user_id=0)
{
global $userId,$userModerator;

$hide=$userModerator ? 2 : 1;
$topicFilter=$topic_id>=0 ? " and topic_id=$topic_id " : '';
$userFilter=$user_id<=0 ? '' : " and messages.sender_id=$user_id ";
$result=mysql_query(
        "select priority,count(*)
         from postings
	      left join messages
	           on postings.message_id=messages.id
	 where (hidden<$hide or sender_id=$userId) and
	       (disabled<$hide or sender_id=$userId) and
               priority<=0 and (grp & $grp)<>0 $topicFilter $userFilter
	 group by priority
	 order by priority")
 or die('Ошибка SQL при определении количества постингов по приоритетам');
$counts=array();
$total=0;
while($row=mysql_fetch_row($result))
     {
     $row[2]=(1-$row[0])*$row[1];
     $counts[]=$row;
     $total+=$row[2];
     }
$pos=random(0,$total-1);
$realpos=0;
foreach($counts as $c)
       if($pos>=$c[2])
         {
	 $pos-=$c[2];
	 $realpos+=$c[1];
	 }
       else
         {
	 $realpos+=(int)($pos/(1-$c[0]));
	 break;
	 }
$result=mysql_query(
        "select postings.id
         from postings
	      left join messages
	           on postings.message_id=messages.id
	 where (hidden<$hide or sender_id=$userId) and
	       (disabled<$hide or sender_id=$userId) and
               priority<=0 and (grp & $grp)<>0 $topicFilter $userFilter
	 order by priority,sent desc
	 limit $realpos,1")
 or die('Ошибка SQL при получении постинга по позиции');
return mysql_num_rows($result)>0 ? mysql_result($result,0,0) : 0;
}

function getMessageIdByPostingId($id)
{
$result=mysql_query("select message_id
                     from postings
		     where id=$id")
	     or die('Ошибка SQL при получении идентификатора сообщения в постинге');
return mysql_num_rows($result)>0 ? mysql_result($result,0,0) : 0;
}

function getMaxIndexOfPosting($index,$grp)
{
$result=mysql_query("select max(index$index)
                     from postings
		     where (grp & $grp)<>0")
	     or die('Ошибка SQL при получении максимального индекса постинга');
return mysql_num_rows($result)>0 ? (int)mysql_result($result,0,0) : 0;
}
?>
