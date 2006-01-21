<?php
require_once('lib/grps.php');
require_once('lib/array.php');
require_once('lib/grpentry.php');

class GrpIterator
      extends ArrayIterator
{

function GrpIterator()
{
parent::ArrayIterator(grpArray(GRP_ALL));
}

function create($value)
{
return new GrpEntry(array('grp' => $value));
}

}
?>
