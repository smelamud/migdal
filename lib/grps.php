<?php
define('GRP_ANY',0);
define('GRP_FORUMS',1);
define('GRP_NEWS',2);
define('GRP_GALLERY',4);
define('GRP_EVENTS',7);
define('GRP_ARTICLES',8);

$grpNames=array(GRP_FORUMS   => 'forums',
                GRP_NEWS     => 'news',
                GRP_GALLERY  => 'gallery',
		GRP_ARTICLES => 'articles');

function getGrpName($grp)
{
global $grpNames;

return $grpNames[$grp];
}

function getGrpNames($grp)
{
global $grpNames;

$names=array();
foreach($grpNames as $key => $value)
       if($grp & $key)
         $names[]=$value;
return $names;
}

function getGrpNumbers($grp)
{
global $grpNames;

$ns=array();
foreach(array_keys($grpNames) as $key)
       if($grp & $key)
         $ns[]=$key;
return $ns;
}

function enclose($vals,$prefix='',$postfix='')
{
$result=array();
foreach($vals as $val)
       $result[]=$prefix.$val.$postfix;
return $result;
}

function getPackedGrpFilter($grp,$prefix='')
{
return $grp==GRP_ANY ? ''
                     : 'and ('.
		       join(' or ',enclose(getGrpNumbers($grp),$prefix.'grp=')).
		       ')';
}

function getUnpackedGrpFilter($grp,$prefix='')
{
return $grp==GRP_ANY ? ''
                     : 'and ('.
		       join(' or ',enclose(getGrpNames($grp),$prefix.'no_','=0')).
		       ')';
}

$grpClassNames=array(GRP_FORUMS   => 'Forum',
                     GRP_NEWS     => 'News',
                     GRP_GALLERY  => 'Gallery',
		     GRP_ARTICLES => 'Article');

function getGrpClassName($grp)
{
global $grpClassNames;

$name=$grpClassNames[$grp];
return isset($name) ? $name : 'Message';
}
?>
