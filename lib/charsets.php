<?php
# @(#) $Id$

function isKOI($s)
{
$c=0;
for($i=0;$i<strlen($s);$i++)
   {
   if($s[$i]>='á' && $s[$i]<='ñ')
     $c++;
   if($s[$i]>='Á' && $s[$i]<='Ñ')
     $c--;
   }
return $c<=0;
}

function convertInput($s)
{
return convert_cyr_string(str_replace(array("\xAB","\xBB","\x97","\x84","\x93",
                                            "\xAE","\x99","\xB9"),
                                      array('<<','>>','---','``',"''",
				            '(r)','(tm)','No.'),$s),'w','k');
}

function convertOutput($s)
{
global $userReadKOI;

return $userReadKOI
       ? str_replace(array('&lt;&lt;','&gt;&gt;','---','``',"&#039;&#039;"),
                     array('"','"','--','"','"'),$s)
       : str_replace(array('&lt;&lt;','&gt;&gt;','---','``',"&#039;&#039;",
                           '(c)','(C)','(r)','(R)','(tm)',
			   '(TM)','No.'),
                     array('&laquo;','&raquo;','&#151;','&#132;','&#147;',
		           '&#169;','&#169;','&#174;','&#174;','&#153;',
			   '&#153;','&#8470;'),$s);
}
?>
