<?php
require_once('lib/utils.php');
require_once('grp/grps.php');

function getGrpClassName($grp)
{
global $grpClassNames;

$name=$grpClassNames[$grp];
return isset($name) ? $name : 'Message';
}

function getGrpOrder($grp)
{
return round(log($grp)/M_LN2);
}

function getGrpValid($grp)
{
return round(exp(getGrpOrder($grp)*M_LN2))==$grp;
}

function getGrpWord($grp,$words)
{
return $words[getGrpValid($grp) ? getGrpOrder($grp)+1 : 0];
}

function getGrpPlural($n,$grp,$words)
{
$pl=array();
$w=getGrpValid($grp) ? (getGrpOrder($grp)+1)*3 : 0;
for($i=0;$i<3;$i++)
   $pl[$i]=$words[$w+$i];
return getPlural($n,$pl);
}
?>
