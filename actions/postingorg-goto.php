<?php
# @(#) $Id$

require_once('lib/errorreporting.php');
require_once('lib/uri.php');
require_once('lib/post.php');

postIntegerArray('topic_id');
postInteger('redirid');
postIntegerArray('grp');
postInteger('index1');
postInteger('use_index1');

$grpf=0;
foreach($grp as $value)
       $grpf|=$value;
if($grpf==0)
  $grpf=-1;
$remove=array('okdir','faildir','offset');
$n=0;
foreach($topic_id as $id)
       {
       $topics[]=$HTTP_POST_VARS["recursive($n)"]!=0 ? -abs($id) : abs($id);
       $remove[]="recursive($n)";
       $n++;
       next($recursive);
       }
header('Location: '.remakeMakeURI($okdir,
                                  $HTTP_POST_VARS,
                                  $remove,
			          array('grp'      => $grpf,
				        'topic_id' => $topics)));
?>
