<?php
require_once('lib/grps.php');
require_once('lib/iterator.php');

class GrpData
{
var $grp;
var $ignore;

function GrpData($grp,$ignore=0)
{
$this->grp=$grp;
$this->ignore=$ignore;
}

function getZero()
{
return $this->grp==0;
}

function getValid()
{
return getGrpValid($this->grp);
}

function getGrp()
{
return $this->grp;
}

function getEffective()
{
return $this->ignore ? GRP_ALL : $this->grp;
}

function getIgnore()
{
return $this->ignore ? 1 : 0;
}

function getInvIgnore()
{
return $this->ignore ? 0 : 1;
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
