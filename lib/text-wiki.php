<?php
# @(#) $Id$

require_once('lib/ctypes.php');
require_once('lib/xml.php');
require_once('lib/text.php');

function is_delim($c)
{
return c_cntrl($c) || c_space($c) || c_punct($c);
}

function is_space($c)
{
return c_cntrl($c) || c_space($c);
}

function flipReplace($foo,$bar,$_bar,$s,$delim=true)
{
beginProfiling(POBJ_FUNCTION,'flipReplace');
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
      && ($n==0 || (!$delim || is_delim($s[$n-1])) && $s[$n-1]!='&'
                                         # &#entity; combinations are
					 # not replaced
                                                   && $s[$n-1]!=$foo)
      && $n!=strlen($s) && (!$delim || !is_delim($s[$n+1]) || $s[$n+1]=='&'
                            || $s[$n+1]=='=' || $s[$n+1]=='~' || $s[$n+1]=='-'
			    || $s[$n+1]=='<' || $s[$n+1]=='(' || $s[$n+1]=='[')
                                         # word may start by entity or font
					 # style markup or by dash or by tag
					 # or by parenthesis or by bracket
			&& $s[$n+1]!=$foo)
     {
     $c.=$bar;
     $tag=1;
     }
   elseif(!$intag && $tag && $s[$n]==$foo
          && ($n==strlen($s) || (!$delim || is_delim($s[$n+1])))
          && $n!=0 && (!$delim || !is_space($s[$n-1])))
	                                 # final punctuation is part
					 # of the word
     {
     $c.=$_bar;
     $tag=0;
     }
   else
     $c.=$s[$n];
   }
if($tag)
  $c.=$_bar;
endProfiling();
return $c;
}

function replaceParagraphs($s)
{
$s=preg_replace('/\n\s*\n/','</p><p>',$s);
return preg_replace('/<p>&lt;-/','<p clear="left">',$s);
}

function getURLTag($whole,$url,$protocol,$content)
{
if(strchr($whole,"'") || strchr($whole,'<') || strchr($whole,'>'))
  return $whole;
for($i=0;$i<strlen($url);$i++)
   if(ord($url{$i})>127)
     return $whole;
return "<a href=\"$url\" local=\"".($protocol=='' ? 'true' : 'false').
       "\">$content</a>";
}

function goFurther(&$out,$in,&$start,&$end,&$state,$target=0)
{
beginProfiling(POBJ_FUNCTION,'goFurther');
if($end>$start)
  {
  $out.=preg_replace('/(^|[\s.,:;\(\)])(([^\s\(\)]+:\/)?\/[^\s&;]\S*[^\s.,:;\(\)&\\\\])/e',
		     "'\\1'.getURLTag('\\0','\\2','\\3','\\2')",
		     substr($in,$start,$end-$start));
  $start=$end;
  }
$state=$target;
endProfiling();
}

function replaceURLs($s)
{
beginProfiling(POBJ_FUNCTION,'replaceURLs');
$c='';
$state=0;
$st=0;
$ed=0;
while($ed<strlen($s))
     switch($state)
           {
	   case 0:
	        $ed=strpos($s,"'",$st);
                $ed=$ed===false ? strlen($s) : $ed;
		goFurther($c,$s,$st,$ed,$state,1);
		break;
           case 1:
	        $ed=$st+1;
	        if($st!=0 && !is_delim($s[$st-1]))
		  goFurther($c,$s,$st,$ed,$state);
		else
		  $state=2;
		break;
	   case 2:
	        $ed=strpos($s,"'",$ed);
		$ed=$ed===false ? strlen($s) : $ed+1;
		$state=3;
		break;
	   case 3:
                if(!preg_match('/^\s+((\S+:\/)?\/[^\s&;]\S*[^\s.,:;\(\)&\\\\])/',
		               substr($s,$ed),$matches))
		  {
		  $ed-=6;
		  goFurther($c,$s,$st,$ed,$state);
		  }
		else
		  $state=4;
		break;
	   case 4:
	        $c.=getURLTag($matches[0],$matches[1],$matches[2],
		              substr($s,$st+1,$ed-$st-2));
	        $st=$ed+strlen($matches[0]);
		$ed=$st;
		$state=0;
		break;
	   }
if($ed>$st)
  $c.=substr($s,$st,$ed-$st);
$c=preg_replace('/[A-Za-z0-9-_]+(\.[A-Za-z0-9-_]+)*@[A-Za-z0-9-]+(\.[A-Za-z0-9-]+)*/',
                '<email addr="\\0" />',$c);
endProfiling();
return $c;
}

function getProperQuoting($s)
{
$matches=array();
$n=preg_match_all('/>/',$s,$matches);
$c='';
for($i=0;$i<$n;$i++)
   $c.='> ';
return $c;
}

function getQuoteLevel($s)
{
$n=0;
for($i=0;$i<=strlen($s)-4;$i+=5)
   if(substr($s,$i,4)=='>')
     $n++;
   else
     return $n;
return $n;
}

function replaceQuoting($s)
{
global $showQuoteChars;

beginProfiling(POBJ_FUNCTION,'replaceQuoting');
$lines=explode("\n",$s);
$level=0;
for($i=0;$i<count($lines);$i++)
   {
   $lines[$i]=preg_replace('/^((?:>\s*)+)/e',"getProperQuoting('\\1')",
                           $lines[$i]);
   $l=getQuoteLevel($lines[$i]);
   if(!$showQuoteChars)
     $lines[$i]=substr($lines[$i],5*$l);
   if($l>=$level)
     for($j=0;$j<$l-$level;$j++)
        $lines[$i]='<quote>'.$lines[$i];
   else
     for($j=0;$j<$level-$l;$j++)
        $lines[$i-1]=$lines[$i-1].'</quote>';
   $level=$l;
   }
endProfiling();
return join("\n",$lines);
}

function replaceCenter($s)
{
return preg_replace('/(^|\n)[^\S\n]{10}[^\S\n]*([^\n]+)(\n|$)/',
                    '\\1<center>\\2</center>\\3',$s);
}

function replaceHeading($s,$n,$c)
{
return preg_replace('/(^\s*|[\n\r]\s*)([^\n\r]*)[\n\r]\s*'.$c.'{3}'.$c.'*([\n\r]|$)/',
                    '\\1<h'.$n.'>\\2</h'.$n.'>\\3',$s);
}

function replaceHeadings($s)
{
return replaceHeading(replaceHeading(replaceHeading($s,2,'='),3,'-'),4,'~');
}

function cropXML($s)
{
$s=preg_replace('/^(\s|<[pP]>|<\/[pP]>|<[bB][rR] \/>)*/','',$s);
return preg_replace('/(\s|<[pP]>|<\/[pP]>|<[bB][rR] \/>)*$/','',$s);
}

function replaceFootnotes($s)
{
$pattern="/(?:^|(?<=>)|\s+)(?:'([^']+)'\s)?{{((?:[^}]|}[^}])+)}}/";
do
  {
  $matches=array();
  if(!preg_match($pattern,$s,$matches))
    break;
  if($matches[1]=='')
    $s=preg_replace($pattern,'<footnote>\\2</footnote>',$s,1);
  else
    $s=preg_replace($pattern,'<footnote title="\\1">\\2</footnote>',$s,1);
  }
while(true);
return $s;
}

function wikiToXML($s,$format,$dformat)
{
beginProfiling(POBJ_FUNCTION,'wikiToXML');
$c=delicateSpecialChars($s);
switch($format)
      {
      default:
      case TF_MAIL:
	   $c=replaceQuoting($c);
      case TF_PLAIN:
 	   $c=replaceURLs($c);
	   $c=replaceHeadings($c);
 	   $c=replaceCenter($c);
 	   $c=replaceParagraphs($c);
	   if($dformat>=MTEXT_LONG)
	     $c=replaceFootnotes($c);
 	   $c=str_replace("\n",'<br />',$c);
	   $c=str_replace('\\\\','<br />',$c);
 	   $c=flipReplace('_','<u>','</u>',$c);
 	   $c=flipReplace('~','<b>','</b>',$c);
 	   $c=flipReplace('=','<i>','</i>',$c);
 	   $c=flipReplace('^','<sup>','</sup>',$c,false);
 	   $c=flipReplace('#','<tt>','</tt>',$c);
	   break;
      case TF_TEX:
	   $c=replaceURLs($c);
	   $c=replaceHeadings($c);
	   $c=replaceCenter($c);
	   $c=replaceParagraphs($c);
	   if($dformat>=MTEXT_LONG)
	     $c=replaceFootnotes($c);
	   $c=str_replace('\\\\','<br />',$c);
	   $c=flipReplace('_','<u>','</u>',$c);
	   $c=flipReplace('~','<b>','</b>',$c);
	   $c=flipReplace('=','<i>','</i>',$c);
 	   $c=flipReplace('^','<sup>','</sup>',$c,false);
	   $c=flipReplace('#','<tt>','</tt>',$c);
	   break;
      case TF_XML:
	   $c=replaceParagraphs($c);
	   if($dformat>=MTEXT_LONG)
	     $c=replaceFootnotes($c);
	   break;
      }
endProfiling();
$c=delicateAmps(cropXML($c));
if($dformat<=MTEXT_LINE)
  return $c;
else
  return "<p>$c</p>";
}
?>
