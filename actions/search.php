<?php
# @(#) $Id$

require_once('lib/errorreporting.php');
require_once('lib/database.php');
require_once('lib/session.php');
require_once('lib/post.php');
require_once('lib/logs.php');

postString('okdir');
postString('faildir');

postString('q');
postInteger('offset');

dbOpen();
session();
logEvent('search',"offset=$offset query=".urlencode($q));
header('Location: '.remakeMakeURI($okdir,
                                  $Args,
				  array('okdir',
				        'faildir',
					'offset')));
dbClose();
?>
