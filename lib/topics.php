<?php
# @(#) $Id$

require_once('conf/migdal.conf');

require_once('lib/array.php');
require_once('lib/bug.php');
require_once('lib/cache.php');
require_once('lib/charsets.php');
require_once('lib/entries.php');
require_once('lib/grpentry.php');
require_once('lib/grpiterator.php');
require_once('lib/grps.php');
require_once('lib/ident.php');
require_once('lib/permissions.php');
require_once('lib/postings-info.php');
require_once('lib/select.php');
require_once('lib/selectiterator.php');
require_once('lib/sql.php');
require_once('lib/tmptexts.php');
require_once('lib/track.php');
require_once('lib/modbits.php');
require_once('lib/utils.php');

class Topic
      extends GrpEntry
{
var $full_name;
var $perm_string;
var $postings_info;
var $sub_count;

function Topic($row)
{
global $rootTopicModbits;

$this->entry=ENT_TOPIC;
$this->modbits=$rootTopicModbits;
parent::GrpEntry($row);
}

function setup($vars)
{
if(!isset($vars['edittag']) || !$vars['edittag'])
  return;
$this->body_format=TF_PLAIN;
$this->body=$vars['body'];
$this->body_xml=wikiToXML($this->body,$this->body_format,MTEXT_SHORT);
$this->up=$vars['up'];
$this->subject=$vars['subject'];
$this->subject_sort=convertSort($this->subject);
$this->comment0=$vars['comment0'];
$this->comment0_xml=wikiToXML($this->comment0,$this->body_format,MTEXT_LINE);
$this->comment1=$vars['comment1'];
$this->comment1_xml=wikiToXML($this->comment1,$this->body_format,MTEXT_LINE);
$this->ident=$vars['ident']!='' ? $vars['ident'] : null;
$this->login=$vars['login'];
if($vars['user_name']!='')
  $this->login=$vars['user_name'];
$this->group_login=$vars['group_login'];
if($vars['group_name']!='')
  $this->group_login=$vars['group_name'];
$this->perm_string=$vars['perm_string'];
if($this->perm_string!='')
  $this->perms=permString($this->perm_string,strPerms($this->perms));
$this->modbits=disjunct($vars['modbits']);
$this->index2=$vars['index2'];
$this->grps=array();
foreach($vars['grps'] as $grp)
       if(isGrpValid($grp))
	 $this->grps[]=$grp;
}

function getNbSubject()
{
return str_replace(' ','&nbsp;',$this->getSubject());
}

function getFullName()
{
return $this->full_name;
}

function getFullNameShort()
{
global $fullNameShortSize;

$s=$this->getFullName();
return strlen($s)>$fullNameShortSize
       ? '...'.substr($s,-($fullNameShortSize-3))
       : $s;
}

function getPermString()
{
return $this->perm_string!='' ? $this->perm_string
                              : strPerms($this->getPerms());
}

function getPermHTML()
{
return $this->perm_string!='' ? str_replace('-','-&nil;',$this->perm_string)
                              : strPerms($this->getPerms(),true);
}

function isPermitted($right)
{
global $userAdminTopics,$userModerator;

return $userAdminTopics && $right!=PERM_POST
       ||
       $userModerator && $right==PERM_POST
       ||
       perm($this->getUserId(),$this->getGroupId(),$this->getPerms(),$right);
}

function getPostingsInfo()
{
return $this->postings_info;
}

function setPostingsInfo($postings_info)
{
$this->postings_info=$postings_info;
}

function getAnswers()
{
$info=$this->getPostingsInfo();
return $info ? $info->getTotal() : parent::getAnswers();
}

function getLastAnswer()
{
$info=$this->getPostingsInfo();
return $info ? $info->getMaxSent() : parent::getLastAnswer();
}

function getSubCount()
{
return $this->sub_count;
}

function setSubCount($sub_count)
{
$this->sub_count=$sub_count;
}

}

function topicsPermFilter($right,$prefix='')
{
global $userAdminTopics,$userModerator;

if($userAdminTopics && $right!=PERM_POST)
  return '1';
if($userModerator && $right==PERM_POST)
  return '1';
return permFilter($right,$prefix);
}

class TopicIterator
      extends LimitSelectIterator
{

function getWhere($grp,$up=0,$prefix='',$recursive=false,$level=1,$index2=-1)
{
$hide='and '.topicsPermFilter(PERM_READ,$prefix);
$userFilter=$up>=0 ? 'and '.subtree('entries',$up,$recursive,'up') : '';
$grpFilter=$grp!=GRP_ALL ? 'and '.grpFilter($grp,'grp','entry_grps') : '';
// TODO: Levels > 2 are not implemented. strlen(topics.track) must be checked.
$levelFilter=$level<=1 || $up<0 ? '' : "and entries.id<>$up and up<>$up";
$index2Filter=$index2<0 ? '' : "and index2=$index2";
return " where entry=".ENT_TOPIC." $hide $userFilter $grpFilter $levelFilter
         $index2Filter ";
}

function TopicIterator($query,$limit=0,$offset=0)
{
$this->LimitSelectIterator('Topic',$query,$limit,$offset);
}

}

class TopicListIterator
      extends TopicIterator
{
var $fields;
var $grp;

function TopicListIterator($grp,$up=0,$sort=SORT_SUBJECT,$recursive=false,
                           $level=1,$fields=SELECT_GENERAL,$index2=-1,
			   $limit=0,$offset=0)
{
$this->fields=$fields;
$this->grp=$grp;
/* Select */
$distinct=$grp!=GRP_ALL ? 'distinct' : '';
$Select="$distinct entries.id as id,entries.ident as ident,entries.up as up,
         entries.subject as subject,entries.comment0 as comment0,
         entries.comment0_xml as comment0_xml,entries.comment1 as comment1,
	 entries.comment1_xml as comment1_xml,entries.body as body,
	 entries.body_xml as body_xml,entries.user_id as user_id,
	 entries.group_id as group_id,users.login as login,
	 gusers.login as group_login,entries.perms as perms,entries.grp as grp,
	 entries.index2 as index2,entries.answers as answers,
	 entries.last_answer as last_answer";
/* From */
$grpTable=$grp!=GRP_ALL ? 'left join entry_grps
                                on entry_grps.entry_id=entries.id'
			: '';
$From="entries
       left join users
	    on entries.user_id=users.id
       left join users as gusers
	    on entries.group_id=gusers.id
       $grpTable";
/* Where */
$Where=$this->getWhere($grp,$up,'entries.',$recursive,$level,$index2);
/* Order */
$Order=getOrderBy($sort,
       array(SORT_SUBJECT         => 'subject_sort',
	     SORT_INDEX0          => 'index0',
	     SORT_RINDEX0         => 'index0 desc',
	     SORT_INDEX1          => 'index1',
	     SORT_RINDEX1         => 'index1 desc',
	     SORT_RINDEX2_RINDEX0 => 'index2 desc,index0 desc'));
/* Query */
$this->TopicIterator(
      "select $Select
       from $From
       $Where
       $Order",$limit,$offset);
}

function create($row)
{
$topic=parent::create($row);
if(($this->fields & SELECT_GRPS)!=0)
  $topic->setGrps(getGrpsByEntryId($row['id']));
if(($this->fields & SELECT_INFO)!=0)
  $topic->setPostingsInfo(getPostingsInfo($this->grp,$row['id']));
return $topic;
}

}

class TopicNamesIterator
      extends TopicIterator
{
var $names;
var $up;
var $delimiter;

function TopicNamesIterator($grp,$up=-1,$recursive=false,$delimiter=' :: ')
{
$this->up=$up;
$this->delimiter=$delimiter;

$distinct=$grp!=GRP_ALL ? 'distinct' : '';
$grpTable=$grp!=GRP_ALL ? 'left join entry_grps
                                on entry_grps.entry_id=entries.id'
			: '';
parent::TopicIterator("select $distinct id,up,track,subject
		       from entries
		            $grpTable"
		      .$this->getWhere($grp,$this->up,'',$recursive)
		    .' order by track');
}

function create($row)
{
if($row['id']!=$this->up)
  {
  if($row['up']!=0 && $row['up']!=$this->up)
    $row['full_name']=getTopicFullNameById($row['up'],$this->up,
                                           $this->delimiter)
                      .$this->delimiter.$row['subject'];
  else
    $row['full_name']=$row['subject'];
  }
$topic=parent::create($row);
setCachedValue('name','entries',$row['id'],$topic);
return $topic;
}

}

class SortedTopicNamesIterator
      extends ArrayIterator
{

function SortedTopicNamesIterator($grp,$up=-1,$recursive=false,
                                  $delimiter=' :: ')
{
$iterator=new TopicNamesIterator($grp,$up,$recursive,$delimiter);
$topics=array();
while($item=$iterator->next())
     $topics[convertSort($item->getFullName())]=$item;
ksort($topics);
parent::ArrayIterator($topics);
}

}

class TopicHierarchyIterator
      extends ArrayIterator
{

function TopicHierarchyIterator($topic_id,$root=-1,$reverse=false)
{
$topics=array();
for($id=idByIdent($topic_id);$id>0 && $id!=$root;)
   {
   $topic=getTopicById($id);
   $topics[]=$topic;
   $id=$topic->getUpValue();
   }
if(!$reverse)
  $topics=array_reverse($topics);
parent::ArrayIterator($topics);
}

}

function storeTopic(&$topic)
{
$jencoded=array('ident' => '','up' => 'entries','subject' => '',
                'subject_sort' => '','comment0' => '','comment0_xml' => '',
		'comment1' => '','comment1_xml' => '','user_id' => 'users',
		'group_id' => 'users','body' => '','body_xml' => '');
$vars=array('entry' => $topic->entry,
            'ident' => $topic->ident,
            'up' => $topic->up,
	    'track' => $topic->track,
	    'subject' => $topic->subject,
	    'subject_sort' => $topic->subject_sort,
	    'comment0' => $topic->comment0,
	    'comment0_xml' => $topic->comment0_xml,
	    'comment1' => $topic->comment0,
	    'comment1_xml' => $topic->comment0_xml,
	    'user_id' => $topic->user_id,
	    'group_id' => $topic->group_id,
	    'perms' => $topic->perms,
	    'grp' => $topic->grp,
	    'modbits' => $topic->modbits,
	    'index2' => $topic->index2,
	    'body' => $topic->body,
	    'body_xml' => $topic->body_xml,
	    'modified' => sqlNow());
if($topic->id)
  {
  $result=sql(makeUpdate('entries',
                         $vars,
			 array('id' => $topic->id)),
	      __FUNCTION__,'update');
  journal(makeUpdate('entries',
                     jencodeVars($vars,$jencoded),
		     array('id' => journalVar('entries',$topic->id))));
  }
else
  {
  $vars['created']=sqlNow();
  $result=sql(makeInsert('entries',
                         $vars),
	      __FUNCTION__,'insert');
  $topic->id=sql_insert_id();
  journal(makeInsert('entries',
                     jencodeVars($vars,$jencoded)),
	  'entries',$topic->id);
  }
return $result;
}

function getModbitsByTopicId($id)
{
global $rootTopicModbits;

$hide=topicsPermFilter(PERM_READ);
$result=sql("select modbits
	     from entries
	     where id=$id and $hide",
	    __FUNCTION__);
return mysql_num_rows($result)>0 ? mysql_result($result,0,0)
                                 : $rootTopicModbits;
}

function getPremoderateByTopicId($id)
{
return (getModbitsByTopicId($id) & MODT_PREMODERATE)!=0;
}

function getModerateByTopicId($id)
{
return (getModbitsByTopicId($id) & MODT_MODERATE)!=0;
}

function getEditByTopicId($id)
{
return (getModbitsByTopicId($id) & MODT_EDIT)!=0;
}

function getTopicById($id,$up=0,$fields=SELECT_GENERAL)
{
global $userId,$userLogin,$userModerator,$rootTopicModbits,$rootTopicGroupName,
       $rootTopicPerms;

if(hasCachedValue('obj','entries',$id))
  return getCachedValue('obj','entries',$id);
$mhide=$userModerator ? 2 : 1;
$hide=topicsPermFilter(PERM_READ,'entries');
$result=sql(
       "select entries.id as id,entries.up as up,entries.subject as subject,
               entries.comment0 as comment0,
	       entries.comment0_xml as comment0_xml,
	       entries.comment1 as comment1,
               entries.comment1_xml as comment1_xml,entries.body as body,
	       entries.body_xml as body_xml,entries.grp as grp,
	       entries.modbits as modbits,entries.ident as ident,
	       entries.user_id as user_id,users.login as login,
	       users.gender as gender,users.email as email,
	       users.hide_email as hide_email,entries.group_id as group_id,
	       gusers.login as group_login,entries.perms as perms,
	       entries.index2 as index2
	from entries
	     left join users
	          on entries.user_id=users.id
	     left join users as gusers
	          on entries.group_id=gusers.id
	where entries.id=$id and $hide",
       __FUNCTION__);
if(mysql_num_rows($result)>0)
  {
  $row=mysql_fetch_assoc($result);
  if(!is_null($row['ident']))
    setCachedValue('ident','entries',$row['ident'],$row['id']);
  $topic=new Topic($row); 
  if(($fields & SELECT_TOPICS)!=0)
    $topic->setSubCount(getSubtopicsCountById($id));
  if(($fields & SELECT_GRPS)!=0)
    $topic->setGrps(getGrpsByEntryId($id));
  if(($fields & SELECT_INFO)!=0)
    $topic->setPostingsInfo(getPostingsInfo(GRP_ALL,$id));
  setCachedValue('obj','entries',$id,$topic);
  }
else
  if($up>0)
    {
    $topic=getTopicById($up);
    $topic=new Topic(array('up'          => $topic->getId(),
			   'grp'         => $topic->getGrp(),
			   'modbits'     => $topic->getModbits(),
			   'user_id'     => $userId,
			   'login'       => $userLogin,
			   'group_id'    => $topic->getGroupId(),
			   'group_login' => $topic->getGroupLogin(),
			   'perms'       => $topic->getPerms()));
    }
  else
    $topic=new Topic(array('grp'         => GRP_ALL,
			   'modbits'     => $rootTopicModbits,
			   'user_id'     => $userId,
			   'login'       => $userLogin,
			   'group_id'    => getUserIdByLogin($rootTopicGroupName),
			   'group_login' => $rootTopicGroupName,
			   'perms'       => $rootTopicPerms));
return $topic;
}

function getTopicNameById($id)
{
if(hasCachedValue('name','entries',$id))
  return getCachedValue('name','entries',$id);
$hide=topicsPermFilter(PERM_READ);
$result=sql("select id,up,subject
	     from entries
	     where id=$id and $hide",
	    __FUNCTION__);
$topic=new Topic(mysql_num_rows($result)>0 ? mysql_fetch_assoc($result)
					   : array());
setCachedValue('name','entries',$id,$topic);
return $topic;
}

function getTopicFullNameById($id,$root=0,$delimiter=' :: ')
{
if($id==$root)
  return '';
$topic=getTopicNameById($id);
if($topic->getUpValue()!=0 && $topic->getUpValue()!=$root)
  return getTopicFullNameById($topic->getUpValue(),$root,$delimiter).$delimiter
	 .$topic->getSubject();
else
  return $topic->getSubject();
}

function getSubtopicsCountById($id,$recursive=false)
{
$id=idByIdent($id);
$result=sql('select count(*)
	     from entries
	     where entry='.ENT_TOPIC.' '.subtree('entries',$id,$recursive,'up'),
	    __FUNCTION__);
return mysql_num_rows($result)>0
       ? mysql_result($result,0,0)-($recursive ? 1 : 0) : 0;
}

function topicExists($id)
{
$hide=topicsPermFilter(PERM_READ);
$result=sql("select id
	     from entries
	     where id=$id and $hide",
	    __FUNCTION__);
return mysql_num_rows($result)>0;
}
?>
