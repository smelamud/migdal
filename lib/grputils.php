<?php
require_once('lib/grps.php');
require_once('lib/iterator.php');

class GrpData
{
var $grp;

function GrpData($grp)
{
$this->grp=$grp;
}

function getValid()
{
return getGrpValid($this->grp);
}

function getGrp()
{
return $this->grp;
}

function getClassName()
{
return getGrpClassName($this->grp);
}

}

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
$result=($this->grp & GRP_ALL)!=0 ? new GrpData($this->grp) : 0;
$this->grp*=2;
return $result;
}

}
?>
