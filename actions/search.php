<?php
# @(#) $Id$

require_once('lib/errorreporting.php');
require_once('lib/database.php');
require_once('lib/session.php');
require_once('lib/post.php');

postString('q');
postInteger('offset');

dbOpen();
session();
logEvent('search',"offset=$offset query=".urlencode($q));
header('Location: '.remakeMakeURI($okdir,
                                  $HTTP_POST_VARS,
				  array('okdir',
				        'faildir',
					'offset')));
dbClose();
?>
