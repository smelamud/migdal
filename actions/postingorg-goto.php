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
$recursive=array();
for($n=0;$n<count($topic_id);$n++)
   {
   $rname="recursive($n)";
   postInteger($rname);
   $recursive[]=$Args[$rname]!=0 ? 1 : 0;
   $remove[]=$rname;
   }
header('Location: '.remakeMakeURI($okdir,
                                  $Args,
                                  $remove,
			          array('recursive' => $recursive)));
?>
