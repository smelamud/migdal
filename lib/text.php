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
$c=preg_replace('/(^|[\s.,:;\(\)])(?:&#039;((?:[^&]*(?:&[^#;]+;)?)+)&#039;\s)?(\S+:\/)?(\/\S*[^\s.,:;\(\)])/e',
                "('\\3'!='' ? '\\1<a href=\"\\3\\4\" target=_blank>'".
		          " : '\\1<a href=\"\\4\">').".
		"('\\2'=='' ? '\\3\\4' : '\\2').'</a>'",$s);
$c=preg_replace('/[A-Za-z0-9-_]+(\.[A-Za-z0-9-_]+)*@[A-Za-z0-9-]+(\.[A-Za-z0-9-]+)*/',
                '<a href="mailto:\\0">\\0</a>',$c);
return $c;
}

function getProperQuoting($s)
{
$matches=array();
$n=preg_match_all('/&gt;/',$s,$matches);
$c='';
for($i=0;$i<$n;$i++)
   $c.='&gt; ';
return $c;
}

function replaceQuoting($s)
{
return preg_replace('/(^|\n)((?:&gt;\s*)+)(.*)(?=\n|$)/e',
                    "'\n<i>'.getProperQuoting('\\2').'\\3</i>'",$s);
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

function cropHTML($s)
{
$s=preg_replace('/^(\s|<[pP]>|<[bB][rR]>)*/','',$s);
return preg_replace('/(\s|<[pP]>|<[bB][rR]>)*$/','',$s);
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
return cropHTML($c);
}

function clearStotext($format,$s)
{
return preg_replace('/<[^>]+>/','',stotextToHTML($format,$s));
}

function strpos_all($haystack,$needle,&$found)
{
if(is_array($needle))
  foreach($needle as $s)
         strpos_all($haystack,$s,$found);
else
  {
  $pos=strpos($haystack,$needle);
  while($pos!==false)
       {
       $found[]=$pos;
       $pos=strpos($haystack,$needle,$pos+1);
       }
  }
return count($found);
}

function shorten($s,$len,$mdlen,$pdlen)
{
if(strlen($s)<=$len+$pdlen)
  return $s;
$st=$len-$mdlen;
$st=$st<0 ? 0 : $st;
$c=substr($s,$st,$mdlen+$pdlen);
$c=preg_replace('/\n\s*\n/',"\n\n",$c);
$patterns=array("\n\n",
                array('. ','! ','? '),
                array(': ',', ','; ',') '));
foreach($patterns as $pat)
       {
       $matches=array();
       if(!strpos_all($c,$pat,$matches))
         continue;
       $bestpos=-1;
       foreach($matches as $pos)
              if($bestpos<0 || abs($bestpos-$mdlen)>abs($pos-$mdlen))
	        $bestpos=$pos;
       return substr($s,0,$bestpos+$len-$mdlen+2);
       }
return substr($s,0,$len);
}
?>
