<?php
# @(#) $Id$

require_once('lib/errorreporting.php');
require_once('lib/post.php');

httpRequestString('okdir');

header("Location: $okdir");
?>
