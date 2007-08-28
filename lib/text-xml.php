<?php
# @(#) $Id$

$mtextEmptyTags=array('A'        => false,
		      'EMAIL'    => true,
		      'B'        => false,
		      'I'        => false,
		      'U'        => false,
		      'TT'       => false,
		      'SUP'      => false,
		      'P'        => false,
		      'QUOTE'    => false,
		      'CENTER'   => false,
		      'H2'       => false,
		      'H3'       => false,
		      'H4'       => false,
		      'BR'       => true,
		      'UL'       => false,
		      'OL'       => false,
		      'DL'       => false,
		      'LI'       => false,
		      'FOOTNOTE' => false,
		      'IMG'      => true,
		      'TABLE'    => false,
		      'TR'       => false,
		      'TD'       => false,
		      'TH'       => false,
		      'OBJECT'   => false,
		      'PARAM'    => true,
		      'EMBED'    => false);

function replaceParagraphs($s)
{
$s=preg_replace('/\n\s*\n/','</p><p>',$s);
$s=preg_replace('/<p>&lt;-/','<p clear="left">',$s);
if(substr($s,0,5)=='&lt;-')
  return '<p clear="left">'.substr($s,5).'</p>';
else
  return "<p>$s</p>";
}

class MTText
{
var $text;

function MTText($text)
{
$this->text=$text;
}

function toString()
{
return $this->text;
}

}

class MTTag
      extends MTText
{
var $name;

function MTTag($name,$text)
{
parent::MTText($text);
$this->name=$name;
}

function getName()
{
return $this->name;
}

function getProperName()
{
$name=$this->name;
if($name{0}=='/')
  $name=substr($name,1);
return $name;
}

function isClosing()
{
return $this->name{0}=='/';
}

}

function isTagValid($name)
{
global $mtextEmptyTags;

if($name=='')
  return false;
$name=strtoupper($name);
if($name{0}=='/')
  $name=substr($name,1);
return isset($mtextEmptyTags[$name]);
}

function joinQueue($queue)
{
while(count($queue)>0)
     {
     if(get_class($queue[0])=='MTTag' && $queue[0]->getProperName()=='BR')
       array_shift($queue);
     elseif(count($queue)>=2
	    && get_class($queue[0])=='MTTag' && $queue[0]->getName()=='P'
	    && get_class($queue[1])=='MTTag' && $queue[1]->getName()=='/P')
       {
       array_shift($queue);
       array_shift($queue);
       }
     else
       break;
     }
while(count($queue)>0)
     {
     if(get_class($queue[count($queue)-1])=='MTTag'
        && $queue[count($queue)-1]->getProperName()=='BR')
       array_pop($queue);
     elseif(count($queue)>=2
	    && get_class($queue[count($queue)-1])=='MTTag'
	    && $queue[count($queue)-1]->getName()=='/P'
	    && get_class($queue[count($queue)-2])=='MTTag'
	    && $queue[count($queue)-2]->getName()=='P')
       {
       array_pop($queue);
       array_pop($queue);
       }
     else
       break;
     }
$out='';
foreach($queue as $obj)
       $out.=$obj->toString();
return $out;
}

function closeTag($tagName,&$tagStack,&$xmlQueue,$tagObject)
{
$rstack=array();
while(count($tagStack)>0 && $tagStack[count($tagStack)-1]!=$tagName)
     {
     $tag=array_pop($tagStack);
     $xmlQueue[]=new MTTag($tag,'</'.strtolower($tag).'>');
     $rstack[]=$tag;
     }
if($tagName=='')
  return;
if(count($tagStack)==0)
  $xmlQueue[]=new MTTag($tagName,'<'.strtolower($tagName).'>');
else
  array_pop($tagStack);
$xmlQueue[]=$tagObject;
while(count($rstack)>0)
     {
     $tag=array_pop($rstack);
     $xmlQueue[]=new MTTag($tag,'<'.strtolower($tag).'>');
     $tagStack[]=$tag;
     }
}

function processTag($tag,&$tagStack,&$xmlQueue)
{
global $mtextEmptyTags;

//preg_match('/^<\s*(\/?\w+)\s*(.*)>$/',$tag,$m);
preg_match('/^<\s*(\/?\w+)\s*([^<]*)>$/',$tag,$m); // UNCHECKED CHANGE
if(!isset($m[1]) || !isTagValid($m[1]))
  {
  $xmlQueue[]=new MTText(delicateSpecialChars($tag));
  return;
  }
$out='<'.$m[1];
$tagName=strtoupper($m[1]);
$tail=isset($m[2]) ? $m[2] : '';
debugLog(LL_DEBUG,'processing tag: tagName=(%) tail=(%)',array($tagName,$tail));
while($tail!='')
     {
     if(preg_match('/^(\w+)\s*=\s*("[^"]*"|\'[^\']*\')\s*(.*)$/',$tail,
                   $m))
       {
       $out.=' '.$m[1].'='.$m[2];
       $tail=isset($m[3]) ? $m[3] : '';
       }
     elseif(preg_match('/^(\w+)\s*=\s*(\S+)\s*(.*)$/',$tail,$m))
       {
       $out.=' '.$m[1].'="'.$m[2].'"';
       $tail=isset($m[3]) ? $m[3] : '';
       }
     elseif(preg_match('/^(\w+)\s*(.*)$/',$tail,$m))
       {
       $out.=' '.$m[1].'="'.$m[1].'"';
       $tail=isset($m[2]) ? $m[2] : '';
       }
     else
       {
       $tail=substr($tail,1);
       }
     }
$tagObject=new MTTag($tagName,$out);
if($tagName{0}=='/')
  {
  $tagObject->text.='>';
  closeTag(substr($tagName,1),$tagStack,$xmlQueue,$tagObject);
  }
else
  if(!$mtextEmptyTags[$tagName])
    {
    $tagObject->text.='>';
    $tagStack[]=$tagName;
    $xmlQueue[]=$tagObject;
    }
  else
    {
    $tagObject->text.=' />';
    $xmlQueue[]=$tagObject;
    }
}

function cleanupXML($s)
{
global $mtextEmptyTags;

$tagStack=array();
$xmlQueue=array();
$i=0;
while($i<strlen($s))
     {
     if($s{$i}!='<')
       {
       $pos=strpos($s,'<',$i);
       if($pos===false)
         $pos=strlen($s);
       $xmlQueue[]=new MTText(delicateSpecialChars(substr($s,$i,$pos-$i)));
       $i=$pos;
       }
     else
       {
       $pos=strpos($s,'>',$i);
       if($pos===false)
         {
         $xmlQueue[]=new MTText(delicateSpecialChars(substr($s,$i)));
	 $i=strlen($s);
	 continue;
	 }
       $tag=substr($s,$i,$pos-$i+1);
       $i=$pos+1;
       processTag($tag,$tagStack,$xmlQueue);
       }
     }
closeTag('',$tagStack,$xmlQueue,null);
$return=joinQueue($xmlQueue);
return $return;
}

function xmlFormatToXML($s,$format,$dformat)
{
$c=$s;
if($dformat>=MTEXT_SHORT)
  $c=replaceParagraphs($c);
return delicateAmps(cleanupXML($c));
}
?>
