<?php
require_once('lib/grps.php');
require_once('lib/array.php');
require_once('lib/grpentry.php');

class GrpIterator
      extends ArrayIterator
{

function GrpIterator()
{
global $GRP_ALL;

parent::ArrayIterator($GRP_ALL);
}

function create($value)
{
return new GrpEntry(array('grp' => $value));
}

}
?>
