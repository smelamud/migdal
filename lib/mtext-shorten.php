<?php
# @(#) $Id$

require_once('lib/xml.php');
require_once('lib/charsets.php');
require_once('lib/text.php');

class MTextToLineXML
      extends XMLParser
{
var $line='';

function xmlError($message)
{
$this->line.=$message;
}

function parse($body)
{
parent::parse('mtext-short',$body);
}

function getLine()
{
return $this->line;
}

function endElement($parser,$name)
{
switch($name)
      {
      case 'P':
      case 'LI':
      case 'CENTER':
      case 'QUOTE':
      case 'H2':
      case 'H3':
      case 'H4':
           $this->line.="\x1F";
      }
}

function characterData($parser,$data)
{
$this->line.=unhtmlentities(convertFromXMLText($data));
}

}

class MTextShortenXML
      extends XMLParser
{
var $short='';
var $len;
var $clearTags;
var $open=array();
var $clen=0;
var $stop=false;

function MTextShortenXML($len,$clearTags=false)
{
parent::XMLParser();
$this->len=$len;
$this->clearTags=$clearTags;
}

function xmlError($message)
{
$this->short.="<b>$message</b>";
}

function parse($body)
{
parent::parse('mtext-short',$body);
}

function getShort()
{
return $this->short;
}

function startElement($parser,$name,$attrs)
{
if($this->stop)
  return;
switch($name)
      {
      case 'MTEXT-LINE':
      case 'MTEXT-SHORT':
      case 'MTEXT-LONG':
           break;
      case 'EMAIL':
      case 'BR':
	   if(!$this->clearTags)
             $this->short.=makeTag($name,$attrs,true);
           break;
      default:
	   if(!$this->clearTags)
             $this->short.=makeTag($name,$attrs);
	   array_unshift($this->open,$name);
      }
}

function endElement($parser,$name)
{
switch($name)
      {
      case 'MTEXT-LINE':
      case 'MTEXT-SHORT':
      case 'MTEXT-LONG':
           if(!$this->clearTags)
	     foreach($this->open as $tag)
		    $this->short.=makeTag("/$tag");
           return;
      }
if($this->stop)
  return;
switch($name)
      {
      case 'EMAIL':
      case 'BR':
           break;
      case 'P':
      case 'LI':
      case 'CENTER':
      case 'QUOTE':
      case 'H2':
      case 'H3':
      case 'H4':
           $this->clen++;
      default:
           if(!$this->clearTags)
             $this->short.=makeTag("/$name");
	   array_shift($this->open);
      }
if($this->clen>=$this->len)
  $this->stop=true;
}

function characterData($parser,$data)
{
if($this->stop)
  return;
$n=strlen(unhtmlentities(utf8_decode($data)));
$this->clen+=$n;
$text=convertFromXMLText($data,$n-($this->clen-$this->len));
if(!$this->clearTags)
  $text=delicateSpecialChars($text);
$this->short.=$text;
if($this->clen>=$this->len)
  $this->stop=true;
}

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

function getShortenLength($s,$len,$mdlen,$pdlen)
{
debugLog(LL_FUNCTIONS,'getShortenLength(s=%,len=%,mdlen=%,pdlen=%)',
	 array($s,$len,$mdlen,$pdlen));
if(strlen($s)<=$len+$pdlen)
  {
  $return=strlen($s);
  debugLog(LL_FUNCTIONS,'getShortenLength() returned %',array($return));
  return $return;
  }
$st=$len-$mdlen;
$st=$st<0 ? 0 : $st;
$c=substr($s,$st,$mdlen+$pdlen);
$patterns=array("\x1F",
                array('. ','! ','? ',".\n","!\n","?\n"),
                array(': ',', ','; ',') ',":\n",",\n",";\n",")\n"));
foreach($patterns as $pat)
       {
       $matches=array();
       if(!strpos_all($c,$pat,$matches))
         continue;
       $bestpos=-1;
       foreach($matches as $pos)
              {
              if($bestpos<0 || abs($bestpos-$mdlen)>abs($pos-$mdlen))
	        $bestpos=$pos;
	      }
       $matchLen=is_array($pat) ? 2 : 1;
       $return=$bestpos+$st+$matchLen;
       debugLog(LL_FUNCTIONS,'getShortenLength() returned %',array($return));
       return $return;
       }
debugLog(LL_FUNCTIONS,'getShortenLength() returned %',array($len));
return $len;
}

function hasMarkup($s)
{
for($i=0;$i<strlen($s);$i++)
   {
   if(strpos("<>&=~_^[]{}'",$s[$i])!==false)
     return true;
   if($s[$i]=='/' && ($i==0 || $s[$i-1]==' ' || $s[$i-1]==':'))
     return true;
   }
return false;
}

function cleanLength($s)
{
if(!hasMarkup($s))
  return strlen($s);
$xml=new MTextToLineXML();
$xml->parse(convertToXMLText($s));
$xml->free();
return strlen($xml->getLine());
}

function shortenUniversal($s,$len,$mdlen,$pdlen,$clearTags=false,$suffix='')
{
debugLog(LL_FUNCTIONS,
         'shortenUniversal(s=%,len=%,mdlen=%,pdlen=%,clearTags=%,suffix=%)',
	 array($s,$len,$mdlen,$pdlen,$clearTags,$suffix));
$hasMarkup=hasMarkup($s);
if($hasMarkup)
  {
  $xml=new MTextToLineXML();
  $xmlText=convertToXMLText($s);
  debugLog(LL_DETAILS,'convertToXMLText(s)=%',array($xmlText));
  $xml->parse($xmlText);
  $xml->free();
  $line=$xml->getLine();
  }
else
  $line=$s;
$n=getShortenLength($line,$len,$mdlen,$pdlen);
$c=$n>=strlen($line) ? '' : $suffix;
if($hasMarkup)
  {
  $xml1=new MTextShortenXML($n,$clearTags);
  $xml1->parse(convertToXMLText($s));
  $xml1->free();
  $return=$xml1->getShort().$c;
  }
else
  $return=substr($s,0,$n).$c;
debugLog(LL_FUNCTIONS,'shortenUniversal() returned %',array($return));
return $return;
}

function shorten($s,$len,$mdlen,$pdlen)
{
return shortenUniversal($s,$len,$mdlen,$pdlen);
}

function shortenNote($s,$len,$mdlen,$pdlen)
{
return shortenUniversal($s,$len,$mdlen,$pdlen,true,'...');
}
?>
