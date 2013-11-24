<?php
require_once('lib/grps.php');
require_once('lib/array.php');
require_once('lib/grpentry.php');

class GrpIterator
      extends MArrayIterator
{

function __construct()
{
parent::__construct(grpArray(GRP_ALL));
}

function create($key,$value)
{
return new GrpEntry(array('grp' => $value));
}

}
?>
