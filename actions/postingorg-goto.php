<?php
# @(#) $Id$

require_once('lib/errorreporting.php');
require_once('lib/uri.php');
require_once('lib/post.php');

postInteger('recursive');
postInteger('redirid');
postIntegerArray('grp');

$grpf=0;
if(is_array($grp))
  foreach($grp as $value)
         $grpf|=$value;
else
  $grpf=-1;
header('Location: '.remakeMakeURI($okdir,
                                  $HTTP_POST_VARS,
                                  array('okdir',
				        'faildir',
					'grp[]'),
			          array('recursive' => $recursive ? 1 : -1,
				        'grp' => $grpf)));
?>
