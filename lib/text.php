<?php
# @(#) $Id$

define('TF_PLAIN',0);
define('TF_TEX',1);
define('TF_HTML',2);
define('TF_PRE',3);
define('TF_MAX',3);

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

function textToStotext($format,$s)
{
return $format==TF_HTML ? $s : htmlspecialchars($s,ENT_QUOTES);
}

function stotextToHTML($format,$s)
{
$c=$s;
switch($format)
      {
      default:
      case TF_PLAIN:
	   $c=str_replace("\n\n",'<p>',$c);
	   $c=str_replace("\n",'<br>',$c);
      case TF_PRE:
	   $c=flipReplace('_','<u>','</u>',$c);
	   $c=flipReplace('~','<b>','</b>',$c);
	   break;
      case TF_TEX:
	   $c=str_replace("\n\n",'<p>',$c);
	   $c=str_replace('\\\\','<br>',$c);
	   $c=flipReplace('_','<u>','</u>',$c);
	   $c=flipReplace('~','<b>','</b>',$c);
	   break;
      case TF_HTML:
	   $c=str_replace("\n\n",'<p>',$c);
	   break;
      }
return $c;
}
?>
