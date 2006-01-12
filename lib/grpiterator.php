<?php
require_once('lib/grps.php');
require_once('lib/array.php');
require_once('lib/grpentry.php');

class GrpIterator
      extends ArrayIterator
{

function GrpIterator()
{
global $grpGroups;

parent::ArrayIterator($grpGroups[GRP_ALL]);
}

function create($value)
{
return new GrpEntry(array('grp' => $value));
}

}
?>
