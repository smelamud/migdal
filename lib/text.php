<?php
# @(#) $Id$

function is_delim($c)
{
return ctype_cntrl($c) || ctype_space($c) || ctype_punct($c);
}

function flipReplace($foo,$bar,$_bar,$s)
{
$c='';
$tag=0;
for($n=0;$n<strlen($s);$n++)
   if(!$tag && $s[$n]==$foo && ($n==0 || is_delim($s[$n-1])))
     {
     $c.=$bar;
     $tag=1;
     }
   elseif($tag && $s[$n]==$foo && ($n==strlen($s) || is_delim($s[$n+1])))
     {
     $c.=$_bar;
     $tag=0;
     }
   else
     $c.=$s[$n];
if($tag)
  $c.=$_bar;
return $c;
}

function enrichedTextToHTML($s)
{
$c=preg_replace('/\n\n/','<p>',$s);
$c=preg_replace('/\n/','<br>',$c);
$c=flipReplace('_','<u>','</u>',$c);
$c=flipReplace('~','<b>','</b>',$c);
return $c;
}
?>
