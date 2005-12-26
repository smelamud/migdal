<?php
# @(#) $Id$

require_once('lib/array.php');

function random($min,$max)
{
static $randomized=false;

if(!$randomized)
  {
  srand(time());
  $randomized=true;
  }
return rand($min,$max);
}

function rnd()
{
random(0,1);
return rand();
}

function array_random($array)
{
return $array[random(0,count($array)-1)];
}

class RandomSequenceIterator
      extends ArrayIterator
{

function RandomSequenceIterator($n,$min,$max)
{
$seq=array();
while(count($seq)<$n && count($seq)<$max-$min+1)
     {
     $k=random($min,$max);
     if(!in_array($k,$seq))
       $seq[]=$k;
     }
parent::ArrayIterator($seq);
}

}
?>
