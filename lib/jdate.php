<?php
# @(#) $Id$

$jmonths=array(1 => 'тишрей','хешвана','кислева','тевета','швата','адара I',
               'адара II','нисана','ияра','сивана','тамуза','ава','элула');

function getJewishFromDate($month,$day,$year)
{
global $jmonths;

$dt=explode('/',JDToJewish(GregorianToJD($month,$day,$year)));
return $dt[1].' '.$jmonths[$dt[0]].' '.$dt[2];
}

function getJewishFromUNIX($time=0)
{
global $jmonths;

$dt=explode('/',JDToJewish($time!=0 ? UNIXToJD($time) : UNIXToJD()));
return $dt[1].' '.$jmonths[$dt[0]].' '.$dt[2];
}
?>
