<?php
# @(#) $Id$

function makeQuery($vars,$remove=array(),$subs=array())
{
$s='';
foreach($vars as $key=>$value)
       if(!in_array($key,$remove))
         $s.=($s!='' ? '&' : '')."$key=".
	     (isset($subs[$key]) ? $subs[$key] : $vars[$key]);
return $s;
}
?>
