<?php
# @(#) $Id$

require_once('conf/migdal.conf');

require_once('lib/utils.php');

function charsetName($name)
{
$pname='';
for($i=0;$i<strlen($name);$i++)
   if($name{$i}!='-' && $name{$i}!='_')
     $pname.=strtoupper($name{$i});
return $pname;
}

function charToEntity($c,$charset='UTF16')
{
$charset=charsetName($charset);
if($charset!='UTF16')
  $c=substr(iconv($charset,'UTF-16',$c),2);
return '&#x'.sprintf('%02x',ord($c{1})).sprintf('%02x',ord($c{0})).';';
}

function entityToChar($c,$charset='UTF16')
{
if(preg_match('/^&#(\d+);$/',$c,$matches))
  $code=(int)$matches[1];
elseif(preg_match('/^&#x([\dA-Fa-f]+);$/',$c,$matches))
  $code=hexdec($matches[1]);
else
  return $c;
$s=chr($code%0x100).chr((int)($code/0x100));
$charset=charsetName($charset);
if($charset!='UTF16')
  $s=iconv('UTF-16',$charset,"\xff\xfe".$s);
return $s;
}

// Converts from $icharset to $ocharset and decodes all character entities.
// Does not create character entities for unknown characters, so use this
// function only for conversions into UTF-8 or UTF-16 encoding.
function convertToUTF($s,$icharset,$ocharset)
{
$icharset=charsetName($icharset);
$ocharset=charsetName($ocharset);
if($icharset!='UTF8')
  {
  $s=iconv($icharset,'UTF-8',$s);
  $icharset='UTF8';
  }
$s=preg_replace('/&[^;]+;/e',"entityToChar('\\0','$icharset')",$s);
return $icharset==$ocharset ? $s : iconv($icharset,$ocharset,$s);
}

function convertCharset($s,$icharset,$ocharset)
{
$icharset=charsetName($icharset);
$ocharset=charsetName($ocharset);
if(substr($ocharset,0,3)=='UTF')
  {
  $s=convertToUTF($s,$icharset,$ocharset);
  return $s;
  }
if($icharset==$ocharset)
  return $s;
if($icharset=='UTF8')
  {
  $s=iconv($icharset,'UTF-16',$s);
  $s=substr($s,2);
  $icharset='UTF16';
  $icsize=2;
  }
else
  $icsize=1;
$c='';
$pos=0;
while(true)
     {
     $chunk=substr($s,$pos*$icsize);
     if($icsize==2)
       $chunk="\xff\xfe$chunk";
     $chunk=iconv($icharset,$ocharset,$chunk);
     if(strlen($chunk)>=strlen($s)/$icsize-$pos)
       {
       $c.=$chunk;
       break;
       }
     $pos+=strlen($chunk);
     $c.=$chunk;
     $c.=charToEntity(substr($s,$pos*$icsize,$icsize),$icharset);
     $pos++;
     }
return $c;
}

function convertFromXMLText($s)
{
global $charsetInternal;

return convertCharset($s,'UTF-8',$charsetInternal);
}

function convertToXMLText($s)
{
global $charsetInternal;

return iconv($charsetInternal,'UTF-8',$s);
}

function isKOI($s)
{
$c=0;
for($i=0;$i<strlen($s);$i++)
   {
   if($s{$i}>='à' && $s{$i}<='ÿ')
     $c++;
   if($s{$i}>='À' && $s{$i}<='ß')
     $c--;
   }
return $c<=0;
}

function isUTF8($s)
{
$errc=0;
$cc=0;
for($i=0;$i<strlen($s);$i++)
   {
   $byte=ord($s{$i});
   if(($byte & 0xc0)==0x80)
     if($cc>0)
       $cc--;
     else
       $errc++;
   elseif(($byte & 0x80)==0)
     $cc=0;
   elseif(($byte & 0xe0)==0xc0)
     $cc=1;
   elseif(($byte & 0xf0)==0xe0)
     $cc=2;
   elseif(($byte & 0xf8)==0xf0)
     $cc=3;
   }
return $errc/strlen($s)<=0.01;
}

function convertLigatures($s)
{
return str_replace(array("\xAB","\xBB","\x96","\x97","\x93",
                         "\x94","\xAE","\x99","\xB9","\x85"),
                   array('<<','>>','---','---','``',
	                 "''",'(r)','(tm)','No.','...'),$s);
}

function convertInput($s)
{
return convert_cyr_string(convertLigatures($s),'w','k');
}

function convertUploadedText($s)
{
global $charsetInternal;

if(isUTF8($s))
  $icharset='UTF-8';
elseif(isKOI($s))
  $icharset='KOI8-R';
else
  $icharset='CP1251';
$out=convertCharset($s,$icharset,$charsetInternal);
return $out;
}

$hebrewCodes=array(
                   'Á' => '05D0',
                   'á' => 'FB2E',
                   'Ï' => 'FB2F',
                   'Â' => 'FB31',
                   '×' => '05D1',
                   'â' => '05D1',
                   'Ç' => '05D2',
                   'Ä' => '05D3',
                   'h' => '05D4',
                   'Õ' => '05D5',
                   'õ' => 'FB35',
                   '÷' => '05F0',
                   'ï' => '05F1',
                   'Ú' => '05D6',
                   'Þ' => '05D7',
                   'Ô' => '05D8',
                   'É' => '05D9',
                   'ê' => 'FB1D',
                   'Ù' => '05F2',
                   'ù' => 'FB1F',
                   'Ë' => 'FB3B',
                   'È' => '05DB',
                   'è' => '05DA',
                   'Ì' => '05DC',
                   'Í' => '05DE',
                   'í' => '05DD',
                   'Î' => '05E0',
                   'î' => '05DF',
                   'Ó' => '05E1',
                   'Ü' => '05E2',
                   'Ð' => 'FB44',
                   'Æ' => '05E4',
                   'æ' => '05E3',
                   'Ã' => '05E6',
                   'ã' => '05E5',
                   'ë' => '05E7',
                   'Ò' => '05E8',
                   'Û' => '05E9',
                   'û' => 'FB2A',
                   'ý' => 'FB2B',
                   'ô' => 'FB4A',
                   'ó' => '05EA',
                   '+' => 'FB29',
                   '-' => '05BE',
		   "'" => '05F3',
		   '"' => '05F4'
                  );

function convertHebrew($s,$htmlEntities=true)
{
global $hebrewCodes;

if($htmlEntities)
  $s=unhtmlentities($s);
$c='';
for($i=0;$i<strlen($s);$i++)
   $c.='&#x'.$hebrewCodes[$s[$i]].';';
return "$c&lrm;";
}

function convertOutput($s,$koiChars=false,$koiCharset=false)
{
$s=$koiChars
       ? str_replace(array('&lt;&lt;','>>','&gt;&gt;','&LT;&LT;','&GT;&GT;',
                           '---','``','&#039;&#039;','&sp;','&nil;'),
                     array('"','"','"','"','"',
		           '--','"','"',' ',''),
		     $s)
       : str_replace(array('&lt;&lt;','>>','&gt;&gt;','&LT;&LT;','&GT;&GT;',
                           '---','``','&#039;&#039;','(c)','(C)',
			   '(r)','(R)','(tm)','(TM)','No.','&sp;','&nil;'),
                     array('&laquo;','&raquo;','&raquo;','&laquo;','&raquo;',
		           '&mdash;','&ldquo;','&rdquo;','&copy;','&copy;',
			   '&reg;','&reg;','&trade;','&trade;','&#8470;',' ',''),
		     $s);
$s=preg_replace('/\$(-?)(\S+)\$/e',
                "'\\1'=='-' ? convertHebrew(strrev('\\2'))
		            : convertHebrew('\\2')",$s);
return $koiCharset ? $s : convert_cyr_string($s,'k','w');
}

function convertSort($s)
{
$c=convert_cyr_string(
   str_replace(array('<<','>>','---','``',"''",
                     '(c)','(C)','(r)','(R)',
		     '(tm)','(TM)','No.'),
	       array("\xAB","\xBB","\x96","\x93","\x94",
	             "\xA9","\xA9","\xAE","\xAE",
		     "\x99","\x99","\xB9"),$s),'k','w');
$cc='';
for($i=0;$i<strlen($c);$i++)
   {
   $code=ord($c[$i]);
   $cc.=($code<0x7D || $code>=0x80 && $code<0xC0)
         ? $c[$i]
	 : (($code>=0xC0 && $code<0xE0)
	    ? "\x7E".chr($code-0x90)
	    : (($code>=0xE0 && $code<=0xFF)
	       ? "\x7E".chr($code-0xB0)
	       : "\x7D".$c[$i]));
   }
return $cc;
}
?>
