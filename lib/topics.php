<?php
# @(#) $Id$

require_once('conf/migdal.conf');

require_once('lib/usertag.php');
require_once('lib/selectiterator.php');
require_once('lib/bug.php');
require_once('lib/tmptexts.php');
require_once('lib/grps.php');
require_once('lib/ident.php');
require_once('lib/stotext.php');
require_once('lib/array.php');
require_once('lib/track.php');
require_once('lib/charsets.php');
require_once('lib/grpiterator.php');
require_once('lib/cache.php');
require_once('lib/permissions.php');

class Topic
      extends UserTag
{
var $id;
var $up;
var $track;
var $name;
var $full_name;
var $user_id;
var $group_id;
var $group_login;
var $perms;
var $perm_string;
var $stotext;
var $allow;
var $premoderate;
var $ident;
var $postings_info;
var $sub_count;
var $separate;

function Topic($row)
{
global $defaultPremoderate;

$this->allow=GRP_ALL;
$this->premoderate=$defaultPremoderate;
$this->UserTag($row);
$this->stotext=new Stotext($row,'description');
}

function setup($vars)
{
if(!isset($vars['edittag']))
  return;
foreach($this->getCorrespondentVars() as $var)
       $this->$var=htmlspecialchars($vars[$var],ENT_QUOTES);
if($vars['user_name']!='')
  $this->login=$vars['user_name'];
if($vars['group_name']!='')
  $this->group_login=$vars['group_name'];
if($this->perm_string!='')
  $this->perms=permString($this->perm_string,strPerms($this->perms));
$this->allow=0;
$this->premoderate=0;
$iter=new GrpIterator();
while($group=$iter->next())
     if(($group->getGrp() & GRP_ALL)!=0)
       {
       $name=$group->getGrpIdent();
       if($vars[$name])
	 $this->allow|=$group->getGrp();
       if($vars["premoderate_$name"])
	 $this->premoderate|=$group->getGrp();
       }
$this->stotext->setup($vars,'description');
}

function getCorrespondentVars()
{
return array('up','name','ident','login','group_login','perm_string',
             'separate');
}

function getWorldVars()
{
return array('up','track','name','user_id','group_id','perms','allow',
             'premoderate','ident','separate');
}

function getJencodedVars()
{
return array('up' => 'topics','name' => '','name_sort' => '',
             'user_id' => 'users','group_id' => 'users',
	     'stotext_id' => 'stotexts');
}

function getNormal($isAdmin=false)
{
$normal=UserTag::getNormal($isAdmin);
$normal['stotext_id']=$this->stotext->getId();
$normal['name_sort']=convertSort($normal['name']);
return $normal;
}

function store()
{
$result=$this->stotext->store();
if(!$result)
  return $result;
$normal=$this->getNormal();
if($this->id)
  {
  $result=mysql_query(makeUpdate('topics',$normal,array('id' => $this->id)));
  journal(makeUpdate('topics',
                     jencodeVars($normal,$this->getJencodedVars()),
		     array('id' => journalVar('topics',$this->id))));
  }
else
  {
  $result=mysql_query(makeInsert('topics',$normal));
  $this->id=mysql_insert_id();
  journal(makeInsert('topics',
                     jencodeVars($normal,$this->getJencodedVars())),
	  'topics',$this->id);
  }
journal("track topics ".journalVar('topics',$this->id));
return $result;
}

function getId()
{
return $this->id;
}

function getUpValue()
{
return $this->up;
}

function getName()
{
return $this->name;
}

function getNbName()
{
return str_replace(' ','&nbsp;',$this->getName());
}

function getFullName()
{
return $this->full_name;
}

function getUserId()
{
return $this->user_id;
}

function setUserId($id)
{
$this->user_id=$id;
}

function getGroupId()
{
return $this->group_id;
}

function setGroupId($id)
{
$this->group_id=$id;
}

function getGroupLogin()
{
return $this->group_login;
}

function getGroupName()
{
return $this->getGroupLogin();
}

function getPerms()
{
return $this->perms;
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

function isReadable()
{
return $this->isPermitted(PERM_READ);
}

function isWritable()
{
return $this->isPermitted(PERM_WRITE);
}

function isAppendable()
{
return $this->isPermitted(PERM_APPEND);
}

function isPostable()
{
return $this->isPermitted(PERM_POST);
}

function getStotext()
{
return $this->stotext;
}

function getDescription()
{
return $this->stotext->getBody();
}

function getHTMLDescription()
{
return stotextToHTML(TF_PLAIN,$this->getDescription());
}

function getLargeFilename()
{
return $this->stotext->getLargeFilename();
}

function getLargeFormat()
{
return $this->stotext->getLargeFormat();
}

function getLargeDescription()
{
return $this->stotext->getLargeBody();
}

function getHTMLLargeDescription()
{
return stotextToHTML($this->getLargeFormat(),$this->getLargeDescription());
}

function getAllow()
{
return $this->allow;
}

function getPremoderate()
{
return $this->premoderate;
}

function getIdent()
{
return $this->ident;
}

function isSeparate()
{
return $this->separate!=0;
}

function getPostingsInfo()
{
return $this->postings_info;
}

function setPostingsInfo($postings_info)
{
$this->postings_info=$postings_info;
}

function getMessageCount()
{
$info=$this->getPostingsInfo();
return $info ? $info->getTotal() : 0;
}

function getLastMessage()
{
$info=$this->getPostingsInfo();
return $info ? $info->getMaxSent() : 0;
}

function getSubCount()
{
return $this->sub_count;
}

}

function topicsPermFilter($right,$prefix='')
{
global $userAdminTopics,$userModerator;

if($userAdminTopics && $right!=PERM_POST)
  return '1';
if($userModerator && $right==PERM_POST)
  return '1';
return permFilter('topics',$right,'user_id',$prefix);
}

class TopicIterator
      extends SelectIterator
{

function getWhere($grp,$up=0,$prefix='',$withAnswers=false,$recursive=false,
                  $withSeparate=true,$level=1)
{
$hide=topicsPermFilter(PERM_READ,$prefix);
$userFilter=$up>=0 ? 'and topics.'.subtree('topics',$up,$recursive,'up') : '';
$grpFilter="and (${prefix}allow & $grp)<>0";
$answerFilter=$withAnswers ? 'and forummesgs.id is not null' : '';
$sepFilter=!$withSeparate ? "and ${prefix}separate=0" : '';
$levelFilter=$level<=1 || $up<0 ? '' : "and topics.id<>$up and topics.up<>$up";
// TODO: Levels > 2 are not implemented. strlen(topics.track) must be checked.
return " where $hide $userFilter $grpFilter $answerFilter $sepFilter".
       " $levelFilter ";
}

function TopicIterator($query)
{
$this->SelectIterator('Topic',$query);
}

}

class TopicListIterator
      extends TopicIterator
{
var $fields;
var $grp;

function TopicListIterator($grp,$up=0,$withPostings=false,$withAnswers=false,
                           $subdomain=-1 /* deprecated */,$withSeparate=true,
			   $sort=SORT_NAME,$recursive=false,$level=1,
			   $fields=SELECT_GENERAL)
{
$this->fields=$fields;
$this->grp=$grp;
/* Select */
$Select="topics.id as id,topics.ident as ident,topics.up as up,
         topics.name as name,topics.stotext_id as stotext_id,
	 topics.user_id as user_id,topics.group_id as group_id,
	 users.login as login,gusers.login as group_login,
	 topics.perms as perms,stotexts.body as description";
/* From */
$grpFilter=grpFilter($grp,'grp','postings');
$hidePostings=messagesPermFilter(PERM_READ,'messages');
$hideAnswers=messagesPermFilter(PERM_READ,'forummesgs');

$postTables=$withPostings ?
            "left join postings
		  on topics.id=postings.topic_id and $grpFilter
	     left join messages
		  on postings.message_id=messages.id and $hidePostings" :
	    '';
$forumsTables=$withAnswers ?
	      "left join forums
		    on messages.id=forums.parent_id
	       left join messages as forummesgs
		    on forums.message_id=forummesgs.id and $hideAnswers" :
	      '';

$From="topics
       left join users
	    on topics.user_id=users.id
       left join users as gusers
	    on topics.group_id=gusers.id
       left join stotexts
	    on stotexts.id=topics.stotext_id
       $postTables
       $forumsTables";
/* Where */
$Where=$this->getWhere($grp,$up,'topics.',$withAnswers,$recursive,$withSeparate,
                       $level);
/* Group by */
$GroupBy=$withAnswers || $withPostings ? 'group by topics.id' : '';
/* Having */
$Having=$withPostings ? 'having message_count<>0' : '';
/* Order */
$Order=getOrderBy($sort,
       array(SORT_NAME       => 'topics.name_sort',
	     SORT_INDEX0     => 'topics.index0',
	     SORT_RINDEX0    => 'topics.index0 desc',
	     SORT_INDEX1     => 'topics.index1',
	     SORT_RINDEX1    => 'topics.index1 desc'));
/* Query */
$this->TopicIterator(
      "select $Select
       from $From
       $Where
       $GroupBy
       $Having
       $Order");
}

function create($row)
{
$topic=TopicIterator::create($row);
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
$this->TopicIterator('select id,track,name
		      from topics'.
		      $this->getWhere($grp,$this->up,'',false,$recursive).
		    ' order by track');
}

function create($row)
{
$this->names[(int)$row['id']]=$row['name'];
$n=strtok($row['track'],' ');
$nm=array();
$up=$this->up;
while($n)
     {
     if($up<=0)
       $nm[]=$this->names[(int)$n];
     else
       if((int)$n==$up)
	 $up=-1;
     $n=strtok(' ');
     }
$row['full_name']=join($this->delimiter,$nm);
return TopicIterator::create($row);
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
$this->ArrayIterator($topics);
}

}

class TopicHierarchyIterator
      extends ArrayIterator
{

function TopicHierarchyIterator($topic_id,$root=-1,$reverse=false)
{
$topics=array();
for($id=idByIdent('topics',$topic_id);$id>0 && $id!=$root;)
   {
   $topic=getTopicById($id);
   $topics[]=$topic;
   $id=$topic->getUpValue();
   }
if(!$reverse)
  $topics=array_reverse($topics);
$this->ArrayIterator($topics);
}

}

function getAllowByTopicId($id)
{
$hide=topicsPermFilter(PERM_READ);
$result=mysql_query("select allow
                     from topics
		     where id=$id and $hide")
          or sqlbug('Ошибка SQL при выборке маски допустимых постингов');
return mysql_num_rows($result)>0 ? mysql_result($result,0,0)
                                 : GRP_ALL;
}

function getPremoderateByTopicId($id)
{
global $defaultPremoderate;

$hide=topicsPermFilter(PERM_READ);
$result=mysql_query("select premoderate
                     from topics
		     where id=$id and $hide")
          or sqlbug('Ошибка SQL при выборке маски модерирования');
return mysql_num_rows($result)>0 ? mysql_result($result,0,0)
                                 : $defaultPremoderate;
}

function getTopicById($id,$up=0,$fields=SELECT_GENERAL)
{
global $userId,$userLogin,$userModerator,$defaultPremoderate,
       $rootTopicGroupName,$rootTopicPerms;

if(hasCachedValue('obj','topics',$id))
  return getCachedValue('obj','topics',$id);
$mhide=$userModerator ? 2 : 1;
$hide=topicsPermFilter(PERM_READ,'topics');
$subsFields=($fields & SELECT_TOPICS)!=0 ?
            ',count(distinct subtopics.id) as sub_count' :
	    '';
$subsTables=($fields & SELECT_TOPICS)!=0 ?
	    'left join topics as subtopics
	          on subtopics.up=topics.id' :
            '';
$GroupBy=($fields & SELECT_TOPICS)!=0 ?
         'group by topics.id' :
	 '' ;
$result=mysql_query(
       "select topics.id as id,topics.up as up,topics.name as name,
               topics.stotext_id as stotext_id,
               stotexts.body as description,image_set,
	       large_filename,large_format,
	       stotexts.large_body as large_description,
	       large_imageset,topics.allow as allow,
	       topics.premoderate as premoderate,topics.ident as ident,
	       topics.separate as separate,
	       topics.user_id as user_id,users.login as login,
	       users.gender as gender,users.email as email,
	       users.hide_email as hide_email,users.rebe as rebe,
	       topics.group_id as group_id,gusers.login as group_login,
	       topics.perms as perms $subsFields
	from topics
	     left join users
	          on topics.user_id=users.id
	     left join users as gusers
	          on topics.group_id=gusers.id
	     left join stotexts
		  on topics.stotext_id=stotexts.id
	     $subsTables
	where topics.id=$id and $hide
	$GroupBy")
 or sqlbug('Ошибка SQL при выборке темы'.mysql_error());
if(mysql_num_rows($result)>0)
  {
  $row=mysql_fetch_assoc($result);
  if($row['ident']!='')
    setCachedValue('ident','topics',$row['ident'],$row['id']);
  $topic=new Topic($row); 
  if(($fields & SELECT_INFO)!=0)
    $topic->setPostingsInfo(getPostingsInfo(GRP_ALL,$id));
  setCachedValue('obj','topics',$id,$topic);
  }
else
  if($up>0)
    {
    $topic=getTopicById($up);
    $topic=new Topic(array('up'          => $topic->getId(),
			   'allow'       => $topic->getAllow(),
			   'premoderate' => $topic->getPremoderate(),
			   'user_id'     => $userId,
			   'login'       => $userLogin,
			   'group_id'    => $topic->getGroupId(),
			   'group_login' => $topic->getGroupLogin(),
			   'perms'       => $topic->getPerms()));
    }
  else
    $topic=new Topic(array('allow'       => GRP_ALL,
			   'premoderate' => $defaultPremoderate,
			   'user_id'     => $userId,
			   'login'       => $userLogin,
			   'group_id'    => getUserIdByLogin($rootTopicGroupName),
			   'group_login' => $rootTopicGroupName,
			   'perms'       => $rootTopicPerms));
return $topic;
}

function getTopicNameById($id)
{
$hide=topicsPermFilter(PERM_READ,'topics');
$result=mysql_query("select id,name
		     from topics
		     where id=$id and $hide")
	  or sqlbug('Ошибка SQL при выборке названия темы');
return new Topic(mysql_num_rows($result)>0 ? mysql_fetch_assoc($result)
					   : array());
}

function getSubtopicsCountById($id,$recursive=false)
{
$id=idByIdent('topics',$id);
$result=mysql_query('select count(*)
                     from topics
		     where '.subtree('topics',$id,$recursive,'up'))
	  or sqlbug('Ошибка SQL при получении количества подтем');
return mysql_num_rows($result)>0
       ? mysql_result($result,0,0)-($recursive ? 1 : 0) : 0;
}

function topicExists($id)
{
$hide=topicsPermFilter(PERM_READ);
$result=mysql_query("select id
		     from topics
		     where id=$id and $hide")
	  or sqlbug('Ошибка SQL при проверке наличия темы');
return mysql_num_rows($result)>0;
}
?>
