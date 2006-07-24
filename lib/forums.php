<?php
# @(#) $Id$

require_once('lib/limitselect.php');
require_once('lib/entries.php');
require_once('lib/permissions.php');
require_once('lib/utils.php');
require_once('lib/bug.php');
require_once('lib/answers.php');
require_once('lib/sql.php');
require_once('lib/images.php');

class Forum
      extends Entry
{

function Forum($row)
{
$this->entry=ENT_FORUM;
$this->body_format=TF_MAIL;
parent::Entry($row);
}

function setup($vars)
{
if(!isset($vars['edittag']) || !$vars['edittag'])
  return;
// from Stotext FIXME
$this->body_format=TF_MAIL;
$this->body=$vars['body'];
$this->body_xml=wikiToXML($this->body,$this->body_format,MTEXT_SHORT);
$this->large_body_format=$vars['large_body_format'];
if(!c_digit($this->large_body_format) || $this->large_body_format>TF_MAX)
  $this->large_body_format=TF_PLAIN;
$this->has_large_body=0;
$this->large_body='';
$this->large_body_xml='';
if($vars['large_body']!='')
  {
  $this->has_large_body=1;
  $this->large_body=$vars['large_body'];
  $this->large_body_xml=wikiToXML($this->large_body,$this->large_body_format,
                                  MTEXT_LONG);
  }
if($vars['large_body_filename']!='')
  $this->large_body_filename=$vars['large_body_filename'];
$this->small_image=$vars['small_image'];
$this->small_image_x=$vars['small_image_x'];
$this->small_image_y=$vars['small_image_y'];
$this->large_image=$vars['large_image'];
$this->large_image_x=$vars['large_image_x'];
$this->large_image_y=$vars['large_image_y'];
$this->large_image_size=$vars['large_image_size'];
$this->large_image_format=$vars['large_image_format'];
$this->large_image_filename=$vars['large_image_filename'];
// from Message FIXME
$this->up=$vars['up'];
$this->subject=$vars['subject'];
$this->subject_sort=convertSort($this->subject);
$this->comment0=$vars['comment0'];
$this->comment0_xml=wikiToXML($this->comment0,$this->body_format,MTEXT_LINE);
$this->comment1=$vars['comment1'];
$this->comment1_xml=wikiToXML($this->comment1,$this->body_format,MTEXT_LINE);
$this->author=$vars['author'];
$this->author_xml=wikiToXML($this->author,$this->body_format,MTEXT_LINE);
$this->source=$vars['source'];
$this->source_xml=wikiToXML($this->source,$this->body_format,MTEXT_LINE);
$this->title=$vars['title'];
$this->title_xml=wikiToXML($this->title,$this->body_format,MTEXT_LINE);
$this->login=$vars['login'];
if($vars['user_name']!='')
  $this->login=$vars['user_name'];
$this->group_login=$vars['group_login'];
if($vars['group_name']!='')
  $this->group_login=$vars['group_name'];
$this->perm_string=$vars['perm_string'];
if($this->perm_string!='')
  $this->perms=permString($this->perm_string,strPerms($this->perms));
else
  if($vars['hidden'])
    $this->perms&=~0x1100;
  else
    $this->perms|=0x1100;
$this->lang=$vars['lang'];
$this->disabled=$vars['disabled'];
$this->url=$vars['url'];
$this->url_domain=getURLDomain($this->url);
// from Posting FIXME
$this->parent_id=$vars['parent_id'];
if($this->up<=0)
  $this->up=$this->parent_id;
else
  if(getTypeByEntryId($this->up)==ENT_FORUM)
    $this->parent_id=getParentIdByEntryId($this->up);
}

function isPermitted($right)
{
global $userModerator,$userId;

return $userModerator
       ||
       (!$this->isDisabled() || $this->getUserId()==$userId) &&
       perm($this->getUserId(),$this->getGroupId(),$this->getPerms(),$right);
}

}

function forumPermFilter($right,$prefix='')
{
global $userModerator,$userId;

if($userModerator)
  return '1';
$filter=permFilter($right,$prefix);
if($prefix!='' && substr($prefix,-1)!='.')
  $prefix.='.';
return "$filter and (${prefix}disabled=0".
       ($userId>0 ? " or ${prefix}user_id=$userId)" : ')');
}

function forumListFilter($parent_id)
{
$Filter='entry='.ENT_FORUM;
$Filter.=' and '.forumPermFilter(PERM_READ);
$Filter.=" and parent_id=$parent_id";
return $Filter;
}

class ForumListIterator
      extends LimitSelectIterator
{

function ForumListIterator($parent_id,$limit=10,$offset=0,$sort=SORT_SENT)
{
$Filter=forumListFilter($parent_id);
$Order=getOrderBy($sort,
       array(SORT_SENT  => 'entries.sent desc',
             SORT_RSENT => 'entries.sent asc'));
parent::LimitSelectIterator(
        'Forum',
	"select entries.id as id,body,body_xml,sent,created,modified,user_id,
	        group_id,perms,disabled,parent_id,users.login as login,
		users.gender as gender,users.email as email,
		users.hide_email as hide_email,users.hidden as user_hidden
	 from entries
	      left join users
		   on entries.user_id=users.id
	 where $Filter
	 $Order",
	 $limit,$offset,
	"select count(*)
	 from entries
	 where $Filter");
}

}

function storeForum(&$forum)
{
$jencoded=array('subject' => '','body' => '','body_xml' => '',
                'small_image' => 'images','large_image' => 'images',
		'large_image_filename' => '','user_id' => 'users',
		'group_id' => 'users','subject_sort' => '','up' => 'entries',
		'parent_id' => 'entries');
$vars=array('entry' => $forum->entry,
            'modified' => sqlNow(),
            'subject' => $forum->subject,
	    'subject_sort' => $forum->subject_sort,
	    'body' => $forum->body,
	    'body_xml' => $forum->body_xml,
	    'body_format' => $forum->body_format,
	    'small_image' => $forum->small_image,
	    'small_image_x' => $forum->small_image_x,
	    'small_image_y' => $forum->small_image_y,
	    'large_image' => $forum->large_image,
	    'large_image_x' => $forum->large_image_x,
	    'large_image_y' => $forum->large_image_y,
	    'large_image_size' => $forum->large_image_size,
	    'large_image_format' => $forum->large_image_format,
	    'large_image_filename' => $forum->large_image_filename,
	    'user_id' => $forum->user_id,
	    'group_id' => $forum->group_id,
	    'perms' => $forum->perms,
            'up' => $forum->up,
	    'track' => $forum->track,
	    'catalog' => $forum->catalog,
	    'parent_id' => $forum->parent_id);
if($userModerator)
  $vars=array_merge($vars,
		    array('disabled' => $forum->disabled,
			  'priority' => $forum->priority));
if($forum->id)
  {
  $result=sql(makeUpdate('entries',
                         $vars,
			 array('id' => $forum->id)),
	      __FUNCTION__,'update');
  journal(makeUpdate('entries',
                     jencodeVars($vars,$jencoded),
		     array('id' => journalVar('entries',$forum->id))));
  }
else
  {
  $vars['sent']=sqlNow();
  $vars['created']=sqlNow();
  $result=sql(makeInsert('entries',
                         $vars),
	      __FUNCTION__,'insert');
  $forum->id=sql_insert_id();
  journal(makeInsert('entries',
                     jencodeVars($vars,$jencoded)),
	  'entries',$forum->id);

  }
return $result;
}

function forumExists($id)
{
$hide=forumPermFilter(PERM_READ);
$result=sql("select id
	     from entries
	     where id=$id and entry=".ENT_FORUM." and $hide",
	    __FUNCTION__);
return mysql_num_rows($result)>0;
}

function getForumById($id,$parent_id=0,$quote='',$quoteWidth=75)
{
global $userId,$realUserId;

$hide=forumPermFilter(PERM_READ);
$result=sql("select entries.id as id,body,body_xml,user_id,group_id,perms,
		    small_image,small_image_x,small_image_y,
		    large_image,large_image_x,large_image_y,large_image_size,
		    large_image_format,large_image_filename,
		    up,parent_id,disabled,sent,created,modified,
		    users.login as login,users.gender as gender,
		    users.email as email,users.hide_email as hide_email,
		    users.hidden as user_hidden
	     from entries
		  left join users
		       on entries.user_id=users.id
	     where entries.id=$id and $hide",
	    __FUNCTION__);
if(mysql_num_rows($result)>0)
  return new Forum(mysql_fetch_assoc($result));
else
  {
  global $rootForumPerms;

  if($parent_id>0)
    {
    $perms=getPermsById($parent_id);
    $group_id=$perms->getGroupId();
    }
  else
    $group_id=0;
  return new Forum(array('parent_id' => $parent_id,
			 'body'      => $quote!=''
					 ? getQuote($quote,$quoteWidth)
					 : '',
			 'user_id'   => $userId>0 ? $userId : $realUserId,
			 'group_id'  => $group_id,
			 'perms'     => $rootForumPerms));
  }
}

// remake
function postForumAnswer($message_id,$body,$sender_id=0)
{
global $rootForumPerms;

if($parent_id>0)
  {
  $perms=getPermsById('messages',$message_id);
  $group_id=$perms->getGroupId();
  }
else
  $group_id=0;
$forum=new Forum(array('body'      => $body,
                             'parent_id' => $message_id,
    			     'sender_id' => $sender_id,
			     'group_id'  => $group_id,
			     'perms'     => $rootForumPerms));
return $forum->store();
}

// remake
function getForumAnswersInfoByMessageId($message_id)
{
global $userId;

if($userId<=0)
  return answerGet($message_id);
else
  {
  $hide=messagesPermFilter(PERM_READ,'messages');
  $result=sql("select count(*) as answers,max(sent) as last_answer
	       from forums
		    left join messages
			 on forums.message_id=messages.id
	       where parent_id=$message_id and $hide",
	      'getForumAnswersInfoByMessageId');
  return mysql_num_rows($result)>0 ? mysql_fetch_assoc($result) : array();
  }
}

// remake
function getForumAnswerIdByMessageId($message_id)
{
$result=sql("select id
	     from forums
	     where message_id=$message_id",
	    'getForumAnswerIdByMessageId');
return mysql_num_rows($result)>0 ? mysql_result($result,0,0) : 0;
}

// remake
function isForumAnswer($message_id)
{
return getForumAnswerIdByMessageId($message_id)>0;
}

function getForumListOffset($parent_id,$id,$sort=SORT_SENT)
{
$Filter=forumListFilter($parent_id);
$conds=array(SORT_SENT  => array('field' => 'sent',
                                 'condition' => "sent > '%s'"),
             SORT_RSENT => array('field' => 'sent',
                                 'condition' => "sent < '%s'"));
$field=$conds[$sort]['field'];
$result=sql("select $field
             from entries
	     where id=$id",
	    __FUNCTION__,'find');
$value=mysql_num_rows($result)>0 ? mysql_result($result,0,0) : 0;
$Filter.=' and '.sprintf($conds[$sort]['condition'],$value);
$result=sql("select count(*)
	     from entries
	     where $Filter",
	    __FUNCTION__,'count');
return mysql_num_rows($result)>0 ? mysql_result($result,0,0) : 0;
}

function deleteForum($id)
{
$forum=getForumById($id);
$up=$forum->getUpValue();
sql("update entries
     set up=$up,track='',catalog=''
     where up=$id",
    __FUNCTION__,'children');
journal('update entries
         set up='.journalVar('entries',$up).",track='',catalog=''
         where up=".journalVar('entries',$id));
deleteImageFiles($id,$forum->getSmallImage(),$forum->getLargeImage(),
                 $forum->getLargeImageFormat());
sql("delete from entries
     where id=$id",
    __FUNCTION__,'delete');
journal('delete from entries
         where id='.journalVar('entries',$id));
updateTracks('entries',$forum->getParentId());
updateCatalogs($forum->getParentId());
answerUpdate($forum->getParentId());
}
?>
