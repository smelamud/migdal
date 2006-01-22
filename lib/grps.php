<?php
require_once('lib/utils.php');
require_once('grp/grps.php');

function grpArray($grp)
{
global $grpGroups;

if(is_array($grp))
  return $grp;
if($grp===GRP_NONE)
  return array();
if(is_numeric($grp))
  return array($grp);
return $grpGroups[$grp];
}

// remake
function getGrpOrder($grp)
{
return round(log($grp)/M_LN2);
}

function isGrpValid($grp)
{
return in_array($grp,grpArray(GRP_ALL));
}

// remake
function getGrpWord($grp,$words)
{
return $words[getGrpValid($grp) ? getGrpOrder($grp)+1 : 0];
}

// remake
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
if($grp==GRP_NONE)
  return 0;
if($grp==GRP_ALL)
  return 1;
$grp=grpArray($grp);
if($prefix!='' && substr($prefix,-1)!='.')
  $prefix.='.';
$conds=array();
foreach($grp as $i)
       $conds[]="${prefix}$field=$i";
return '('.join(' or ',$conds).')';
}

// remake
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
