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
?>
