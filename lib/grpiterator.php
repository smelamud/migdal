<?php
require_once('lib/grps.php');
require_once('lib/array.php');
require_once('lib/grpentry.php');

class GrpIterator
      extends MArrayIterator
{

function GrpIterator()
{
parent::MArrayIterator(grpArray(GRP_ALL));
}

function create($key,$value)
{
return new GrpEntry(array('grp' => $value));
}

}
?>
