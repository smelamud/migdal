<?php
# @(#) $Id$

require_once('lib/utils.php');

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
       ? str_replace(array('&lt;&lt;','&gt;&gt;','&LT;&LT;','&GT;&GT;','---',
                           '``','&#039;&#039;','&sp;','&nil;'),
                     array('"','"','"','"','--',
		           '"','"',' ',''),$s)
       : str_replace(array('&lt;&lt;','&gt;&gt;','&LT;&LT;','&GT;&GT;','---',
                           '``','&#039;&#039;','(c)','(C)','(r)',
			   '(R)','(tm)','(TM)','No.','&sp;','&nil;'),
                     array('&laquo;','&raquo;','&laquo;','&raquo;','&mdash;',
		           '&ldquo;','&rdquo;','&copy;','&copy;','&reg;',
			   '&reg;','&trade;','&trade;','&#8470;',' ',''),$s);
$s=preg_replace('/\$(-?)(\S+)\$/e',
                "'\\1'=='-' ? convertHebrew(strrev('\\2'))
		            : convertHebrew('\\2')",$s);
return $koiCharset ? $s : convert_cyr_string($s,'k','w');
}

function convertSort($s)
{
$c=convert_cyr_string(
   str_replace(array('&lt;&lt;','&gt;&gt;','&LT;&LT;','&GT;&GT;','---','``',
                     "&#039;&#039;",'(c)','(C)','(r)','(R)','(tm)',
		     '(TM)','No.'),
	       array("\xAB","\xBB","\xAB","\xBB","\x96","\x93",
	             "\x94","\xA9","\xA9","\xAE","\xAE","\x99",
		     "\x99","\xB9"),$s),'k','w');
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
