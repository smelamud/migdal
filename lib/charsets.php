<?php
# @(#) $Id$

function toKOI($s)
{
$c=0;
for($i=0;$i<strlen($s);$i++)
   {
   if($s[$i]>='á' && $s[$i]<='ñ')
     $c++;
   if($s[$i]>='Á' && $s[$i]<='Ñ')
     $c--;
   }
if($c>0)
  return convert_cyr_string($s,'w','k');
else
  return $s;
}
?>
