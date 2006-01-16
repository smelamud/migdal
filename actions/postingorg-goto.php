<?php
# @(#) $Id$

require_once('lib/errorreporting.php');
require_once('lib/uri.php');
require_once('lib/post.php');

postString('okdir');
postString('faildir');
postIntegerArray('topic_id');
postInteger('offset');
postIntegerArray('grp');
postInteger('index1');
postInteger('use_index1');

$remove=array('okdir','faildir','offset');
$n=0;
foreach($topic_id as $id)
       {
       $rname="recursive($n)";
       postInteger($rname);
       $topics[]=$Args[$rname]!=0 ? -abs($id) : abs($id);
       $remove[]=$rname;
       $n++;
       }
header('Location: '.remakeMakeURI($okdir,
                                  $Args,
                                  $remove,
			          array('topic_id' => $topics)));
?>
