<?php
# @(#) $Id$

require_once('lib/ctypes.php');

define('TF_PLAIN',0);
define('TF_TEX',1);
define('TF_HTML',2);
define('TF_PRE',3);
define('TF_MAIL',4);
define('TF_MAX',4);

function is_delim($c)
{
return c_cntrl($c) || c_space($c) || c_punct($c);
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
      && ($n==0 || is_delim($s[$n-1]) && $s[$n-1]!='&'))
                                         # &#entity; combinations are
					 # not replaced
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

function replaceParagraphs($s)
{
return preg_replace('/\n\s*\n/','<p>',$s);
}

function replaceURLs($s)
{
$c=preg_replace('/(^|\s)(?:&#039;((?:[^&]*(?:&[^#;]+;)?)+)&#039;\s)?(\S+:\/)?(\/\S*[^\s.,:;])/e',
                "('\\3'!='' ? '\\1<a href=\"\\3\\4\" target=_blank>'".
		          " : '\\1<a href=\"\\4\">').".
		"('\\2'=='' ? '\\3\\4' : '\\2').'</a>'",$s);
$c=preg_replace('/[A-Za-z-]+(\.[A-Za-z-]+)*@[A-Za-z-]+(\.[A-Za-z-]+)*/',
                '<a href="mailto:\\0">\\0</a>',$c);
return $c;
}

function replaceQuoting($s)
{
return preg_replace('/(^|\n)((&gt;\s*)+.*)(\n|$)/','<i>\\1</i>'."\n",$s);
}

function replaceCenter($s)
{
return preg_replace('/(^|\n)[^\S\n]{10}[^\S\n]*([^\n]+)(\n|$)/',
                    '\\1<center>\\2</center>\\3',$s);
}

function replaceBSD($s)
{
return preg_replace('/(^|\n)\s*ву&quot;д\s*(\n|$)/',
                    '\\1<div align=right><img src="pics/bsd.gif"></div>\\2',$s);
}

function replaceHeading($s,$n,$c)
{
return preg_replace('/(^\s*|\n\s*)([^\n]*)\n\s*'.$c.'{3}'.$c.'*(\n|$)/',
                    '\\1<h'.$n.'>\\2</h'.$n.'>\\3',$s);
}

function replaceHeadings($s)
{
return replaceHeading(replaceHeading(replaceHeading($s,2,'='),3,'-'),4,'~');
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
 	   $c=replaceBSD($c);
	   $c=replaceHeadings($c);
 	   $c=replaceCenter($c);
 	   $c=replaceParagraphs($c);
 	   $c=str_replace("\n",'<br>',$c);
 	   $c=flipReplace('_','<u>','</u>',$c);
 	   $c=flipReplace('~','<b>','</b>',$c);
 	   $c=flipReplace('=','<i>','</i>',$c);
 	   $c=flipReplace('#','<tt>','</tt>',$c);
	   break;
      case TF_TEX:
	   $c=replaceURLs($c);
	   $c=replaceBSD($c);
	   $c=replaceHeadings($c);
	   $c=replaceCenter($c);
	   $c=replaceParagraphs($c);
	   $c=str_replace('\\\\','<br>',$c);
	   $c=flipReplace('_','<u>','</u>',$c);
	   $c=flipReplace('~','<b>','</b>',$c);
	   $c=flipReplace('=','<i>','</i>',$c);
	   $c=flipReplace('#','<tt>','</tt>',$c);
	   break;
      case TF_HTML:
	   $c=replaceParagraphs($c);
	   break;
      case TF_PRE:
	   $c=replaceURLs($c);
	   $c=flipReplace('_','<u>','</u>',$c);
	   $c=flipReplace('~','<b>','</b>',$c);
	   $c=flipReplace('=','<i>','</i>',$c);
	   break;
      }
return $c;
}
?>
