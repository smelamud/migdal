<?php
# @(#) $Id$

$calendarAvailableCache=-1;

function isCalendarAvailable()
{
global $calendarAvailableCache;

if($calendarAvailableCache<0)
  $calendarAvailableCache=array_search('calendar',get_loaded_extensions(),
                                       false);
return $calendarAvailableCache;
}

if(!isCalendarAvailable())
  {
  include('lib/jewish-calendar.php');
  include('lib/gregor-calendar.php');
  include('lib/unix-calendar.php');
  }
  
$jmonths=array(1 => 'тишрей','хешвана','кислева','тевета','швата','адара I',
               'адара II','нисана','ияра','сивана','тамуза','ава','элула');

function getJewishFromDate($month,$day,$year)
{
global $jmonths;

if(isCalendarAvailable())
  $dt=explode('/',JDToJewish(GregorianToJD($month,$day,$year)));
else
  {
  $dt=array();
  SDNToJewish(GregorianToSDN($month,$day,$year),$dt[0],$dt[1],$dt[2]);
  }
return $dt[1].' '.$jmonths[$dt[0]].' '.$dt[2];
}

function getJewishFromUNIX($time=0)
{
global $jmonths;

if(isCalendarAvailable())
  $dt=explode('/',JDToJewish($time!=0 ? UNIXToJD($time) : UNIXToJD()));
else
  {
  $dt=array();
  SDNToJewish(UNIXToSDN($time),$dt[0],$dt[1],$dt[2]);
  }
return $dt[1].' '.$jmonths[$dt[0]].' '.$dt[2];
}

function getCalendarAge($bMonth,$bDay,$bYear,$month,$day,$year)
{
if(isCalendarAvailable())
  {
  $jbt=GregorianToJD($bMonth,$bDay,$bYear);
  $jt=GregorianToJD($month,$day,$year);
  }
else
  {
  $jbt=GregorianToSDN($bMonth,$bDay,$bYear);
  $jt=GregorianToSDN($month,$day,$year);
  }
return (int)(($jt-$jbt)/365.25);
}
?>
