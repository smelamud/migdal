<?php
# @(#) $Id$

class CURLSession
{
var $curl;
var $data;
var $pos;

function __construct($curl,$transfer)
{
$this->curl=$curl;
$this->data=explode("\n",$transfer);
$this->pos=0;
}

function eof()
{
return $this->pos>=count($this->data);
}

function gets()
{
return $this->data[$this->pos++];
}

}

function url_fopen($url)
{
if(ini_get("allow_url_fopen"))
  return fopen($url,'r');
else
  {
  $curl=curl_init();
  curl_setopt($curl,CURLOPT_RETURNTRANSFER,1);
  curl_setopt($curl,CURLOPT_URL,$url);
  return new CURLSession($curl,curl_exec($curl));
  }
}

function url_feof($fd)
{
if(ini_get("allow_url_fopen"))
  return feof($fd);
else
  return $fd->eof();
}

function url_fclose($fd)
{
if(ini_get("allow_url_fopen"))
  return fclose($fd);
else
  curl_close($fd->curl);
}

function url_fgets(&$fd)
{
if(ini_get("allow_url_fopen"))
  return fgets($fd,65535);
else
  return $fd->gets();
}
?>
