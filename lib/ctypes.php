<?php
function c_space($s)
{
for($i=0;$i<strlen($s);$i++)
   {
   $v=ord($s[$i]);
   if($v!=32 && ($v<9 || $v>13))
     return false;
   }
return true;
}

function c_digit($s)
{
for($i=0;$i<strlen($s);$i++)
   {
   $v=ord($s[$i]);
   if($v<48 || $v>57)
     return false;
   }
return true;
}

function c_punct($s)
{
for($i=0;$i<strlen($s);$i++)
   {
   $v=ord($s[$i]);
   if($v<33 || ($v>47 && $v<58) || ($v>64 && $v<91) || ($v>96 && $v<123)
      || $v>126)
     return false;
   }
return true;
}

function c_cntrl($s)
{
for($i=0;$i<strlen($s);$i++)
   {
   $v=ord($s[$i]);
   if($v>31 && $v!=127)
     return false;
   }
return true;
}
?>
