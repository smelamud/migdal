<?php
require_once('lib/utils.php');
require_once('grp/grps.php');

function getGrpClassName($grp)
{
global $grpClassNames;

$name=$grpClassNames[$grp];
return isset($name) ? $name : 'Posting';
}

function getGrpOrder($grp)
{
return round(log($grp)/M_LN2);
}

function getGrpValid($grp)
{
global $grpClassNames;

return round(exp(getGrpOrder($grp)*M_LN2))==$grp
       && isset($grpClassNames[$grp]);
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

function grpFilter($grp,$field='grp',$prefix='')
{
global $GRP_ALL;

if($grp==GRP_NONE)
  return 0;
if(count($grp)==count($GRP_ALL))
  return 1;
if(!is_array($grp))
  $grp=array($grp);
if($prefix!='' && substr($prefix,-1)!='.')
  $prefix.='.';
$conds=array();
foreach($grp as $i)
       $conds[]="${prefix}$field=$i";
return '('.join(' or ',$conds).')';
}

function grpSample($grp)
{
if($grp==GRP_NONE)
  return 0;
for($i=1;$i<=GRP_ALL;$i*=2)
   if(($i & $grp)!=0)
     return $i;
return 0;
}
?>
