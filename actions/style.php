<?php
# @(#) $Id$

require_once('lib/errorreporting.php');
require_once('lib/database.php');
require_once('lib/session.php');
require_once('lib/post.php');

dbOpen();
session();
header('Location: '.remakeURI($okdir,array(),array('st'  => $style,
                                                   'bt'  => $biff,
						   'rk'  => empty($koi)
						            ? -1 : 1,
						   'mp'  => $mp,
						   'fp'  => $fp,
						   'fcp' => $fcp,
						   'prp' => $prp,
						   'pcp' => $pcp,
						   'up'  => $up
						   )));
dbClose();
?>
