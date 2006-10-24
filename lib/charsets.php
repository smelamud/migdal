<?php
# @(#) $Id$

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
     $pos+=strlen($chunk)/$icsize;
     $c.=$chunk;
     $c.=charToEntity(substr($s,$pos*$icsize,$icsize),$icharset);
     $pos++;
     }
return $c;
}

function isKOI($s)
{
$c=0;
for($i=0;$i<strlen($s);$i++)
   {
   if($s[$i]>='�' && $s[$i]<='�')
     $c++;
   if($s[$i]>='�' && $s[$i]<='�')
     $c--;
   }
return $c<=0;
}

function convertInput($s)
{
return convert_cyr_string(
       str_replace(array("\xAB","\xBB","\x96","\x97","\x93",
                         "\x94","\xAE","\x99","\xB9","\x85"),
                   array('<<','>>','---','---','``',
	                 "''",'(r)','(tm)','No.','...'),$s),'w','k');
}

$hebrewCodes=array(
                   '�' => '05D0',
                   '�' => 'FB2E',
                   '�' => 'FB2F',
                   '�' => 'FB31',
                   '�' => '05D1',
                   '�' => '05D1',
                   '�' => '05D2',
                   '�' => '05D3',
                   'h' => '05D4',
                   '�' => '05D5',
                   '�' => 'FB35',
                   '�' => '05F0',
                   '�' => '05F1',
                   '�' => '05D6',
                   '�' => '05D7',
                   '�' => '05D8',
                   '�' => '05D9',
                   '�' => 'FB1D',
                   '�' => '05F2',
                   '�' => 'FB1F',
                   '�' => 'FB3B',
                   '�' => '05DB',
                   '�' => '05DA',
                   '�' => '05DC',
                   '�' => '05DE',
                   '�' => '05DD',
                   '�' => '05E0',
                   '�' => '05DF',
                   '�' => '05E1',
                   '�' => '05E2',
                   '�' => 'FB44',
                   '�' => '05E4',
                   '�' => '05E3',
                   '�' => '05E6',
                   '�' => '05E5',
                   '�' => '05E7',
                   '�' => '05E8',
                   '�' => '05E9',
                   '�' => 'FB2A',
                   '�' => 'FB2B',
                   '�' => 'FB4A',
                   '�' => '05EA',
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
