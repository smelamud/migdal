<?php
# @(#) $Id$

function isKOI($s)
{
$c=0;
for($i=0;$i<strlen($s);$i++)
   {
   if($s[$i]>='à' && $s[$i]<='ÿ')
     $c++;
   if($s[$i]>='À' && $s[$i]<='ß')
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

function convertOutput($s)
{
global $userReadKOI;

return convert_cyr_string($userReadKOI>0
       ? str_replace(array('&lt;&lt;','&gt;&gt;','---','``',"&#039;&#039;"),
                     array('"','"','--','"','"'),$s)
       : str_replace(array('&lt;&lt;','&gt;&gt;','---','``',"&#039;&#039;",
                           '(c)','(C)','(r)','(R)','(tm)',
			   '(TM)','No.'),
                     array('&laquo;','&raquo;','&mdash;','&ldquo;','&rdquo;',
		           '&copy;','&copy;','&reg;','&reg;','&trade;',
			   '&trade;','&#8470;'),$s),'k','w');
}

function convertSort($s)
{
$c=convert_cyr_string(
   str_replace(array('&lt;&lt;','&gt;&gt;','---','``',"&#039;&#039;",
		     '(c)','(C)','(r)','(R)','(tm)',
		     '(TM)','No.'),
	       array("\xAB","\xBB","\x96","\x93","\x94",
		     "\x94","\x94","\xAE","\xAE","\x99",
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
