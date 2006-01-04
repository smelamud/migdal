<?php
require_once('lib/grps.php');
require_once('lib/iterator.php');
require_once('lib/grpentry.php');

class GrpIterator
      extends Iterator
{
var $grp;

function GrpIterator()
{
parent::Iterator();
$this->grp=1;
}

function next()
{
parent::next();
$result=$this->grp<=GRP_ALL ? new GrpEntry(array('grp' => $this->grp)) : 0;
$this->grp*=2;
return $result;
}

}
?>
