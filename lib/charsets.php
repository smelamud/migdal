<?php
# @(#) $Id$

function toKOI($s)
{
$c=0;
for($i=0;$i<strlen($s);$i++)
   {
   if($s[$i]>='�' && $s[$i]<='�')
     $c++;
   if($s[$i]>='�' && $s[$i]<='�')
     $c--;
   }
if($c>0)
  return convert_cyr_string($s,'w','k');
else
  return $s;
}
?>
