<?php
# @(#) $Id$

require_once('lib/topics.php');

function titleTopicName($id)
{
$topic=getTopicNameById($id);
return $topic->getSubject();
}
?>
