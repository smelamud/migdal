<?php
require_once('lib/grps.php');
require_once('lib/iterator.php');
require_once('lib/postings.php');

class GrpIterator
      extends Iterator
{
var $grp;

function GrpIterator()
{
$this->Iterator();
$this->grp=1;
}

function next()
{
Iterator::next();
$result=$this->grp<GRP_ALL ? newGrpPosting($this->grp) : 0;
$this->grp*=2;
return $result;
}

}
?>
