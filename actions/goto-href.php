<?php
# @(#) $Id$

require_once('lib/errorreporting.php');
require_once('lib/post.php');

postString('okdir');

header("Location: $okdir");
?>
