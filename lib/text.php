<?php
# @(#) $Id$

define('TF_PLAIN',0);
define('TF_TEX',1);
define('TF_HTML',2);
define('TF_PRE',3);
define('TF_MAIL',4);
define('TF_MAX',4);

function is_delim($c)
{
return ctype_cntrl($c) || ctype_space($c) || ctype_punct($c);
}

function flipReplace($foo,$bar,$_bar,$s)
{
$c='';
$tag=0;
$intag=0;
for($n=0;$n<strlen($s);$n++)
   {
   if($s[$n]=='<')
     $intag++;
   if($s[$n]=='>')
     $intag--;
   if(!$intag && !$tag && $s[$n]==$foo
      && ($n==0 || is_delim($s[$n-1])))
     {
     $c.=$bar;
     $tag=1;
     }
   elseif(!$intag && $tag && $s[$n]==$foo
          && ($n==strlen($s) || is_delim($s[$n+1])))
     {
     $c.=$_bar;
     $tag=0;
     }
   else
     $c.=$s[$n];
   }
if($tag)
  $c.=$_bar;
return $c;
}

function replaceURLs($s)
{
$c=preg_replace('/\S+:\/\/\S+/','<a href="\\0" target=_blank>\\0</a>',$s);
$c=preg_replace('/[A-Za-z-]+(\.[A-Za-z-]+)*@[A-Za-z-]+(\.[A-Za-z-]+)*/',
                '<a href="mailto:\\0">\\0</a>',$c);
return $c;
}

function replaceQuoting($s)
{
return preg_replace('/(&gt;.*)\n/','<i>\\1</i>'."\n",$s);
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
      case TF_MAIL:
	   $c=replaceQuoting($c);
      case TF_PLAIN:
	   $c=replaceURLs($c);
	   $c=str_replace("\n\n",'<p>',$c);
	   $c=str_replace("\n",'<br>',$c);
	   $c=flipReplace('_','<u>','</u>',$c);
	   $c=flipReplace('~','<b>','</b>',$c);
	   break;
      case TF_TEX:
	   $c=str_replace("\n\n",'<p>',$c);
	   $c=str_replace('\\\\','<br>',$c);
	   $c=flipReplace('_','<u>','</u>',$c);
	   $c=flipReplace('~','<b>','</b>',$c);
	   $c=replaceURLs($c);
	   break;
      case TF_HTML:
	   $c=str_replace("\n\n",'<p>',$c);
	   break;
      case TF_PRE:
	   $c=replaceURLs($c);
	   $c=flipReplace('_','<u>','</u>',$c);
	   $c=flipReplace('~','<b>','</b>',$c);
	   break;
      }
return $c;
}
?>
