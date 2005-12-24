<?php
# @(#) $Id$

require_once('lib/errorreporting.php');
require_once('lib/database.php');
require_once('lib/session.php');
require_once('lib/post.php');
require_once('lib/errors.php');
require_once('lib/topics.php');
require_once('lib/sql.php');

function reorderTopics($topic)
{
$uniq=array_unique($topic);
if(count($uniq)!=count($topic))
  return EO_DUPS;
$index=0;
foreach($topic as $id)
       {
       $tp=getTopicById($id);
       if($tp->getId()<=0)
         return ETO_NO_TOPIC;
       if(!$tp->isWritable())
         return ETO_NO_REORDER;
       sql("update topics
	    set index0=$index
	    where id=$id",
	   'reorderTopics');
       journal("update topics
                set index0=$index
		where id=".journalVar('topics',$id));
       $index++;
       }
return EG_OK;
}

postIntegerArray('topic');

dbOpen();
session();
$err=reorderTopics($topic);
if($err==EG_OK)
  header("Location: $okdir");
else
  header('Location: '.remakeMakeURI($faildir,
                                    $HTTP_POST_VARS,
				    array('okdir',
				          'faildir'),
				    array('err' => $err)).'#error');
dbClose();
?>
