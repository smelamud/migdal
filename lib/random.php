<?php
# @(#) $Id$

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
?>
