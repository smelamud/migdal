<?php
require_once('lib/utils.php');

define('GRP_FORUMS',1);
define('GRP_NEWS',2);
define('GRP_GALLERY',4);
define('GRP_EVENTS',7);
define('GRP_ARTICLES',8);
define('GRP_ALL',15);

$grpClassNames=array(GRP_FORUMS   => 'Forum',
                     GRP_NEWS     => 'News',
                     GRP_GALLERY  => 'Gallery',
		     GRP_ARTICLES => 'Article',
		     GRP_ALL      => 'Posting');

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
