<?php
# @(#) $Id$

require_once('lib/dataobject.php');
require_once('lib/selectiterator.php');
require_once('lib/tmptexts.php');
require_once('lib/grps.php');

class Message
      extends DataObject
{
var $id;

function Message($row)
{
$this->DataObject($row);
}

}
?>
