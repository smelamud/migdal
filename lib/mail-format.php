<?php
# @(#) $Id$

require_once('conf/migdal.conf');

require_once('lib/xml.php');

define('FMS_GLOBAL',0);
define('FMS_SUBJECT',1);
define('FMS_BODY',2);
define('FMS_P',3);

class FormatMailXML
      extends XMLParser
{
var $subject='';
var $headers='';
var $body='';

var $state=FMS_GLOBAL;
var $current='';

function __construct()
{
parent::__construct();
}

function getResult()
{
return array($this->subject,$this->headers,$this->body);
}

function xmlError($message)
{
$this->body.="*** $message ***";
}

function flushCurrent()
{
$this->body.="{$this->current}\n";
$this->current='';
}

function parse($body)
{
debugLog(LL_DETAILS,'parse(body=%)',array($body));
parent::parse('mail',$body);
}

function startElement($parser,$name,$attrs)
{
debugLog(LL_DETAILS,'startElement(parser,name=%,attrs=%)',array($name,$attrs));
debugLog(LL_DEBUG,'state=%',array($this));
if($this->state==FMS_GLOBAL && $name=='SUBJECT')
  $this->state=FMS_SUBJECT;
elseif($this->state==FMS_GLOBAL && $name=='CONTENT')
  $this->state=FMS_BODY;
elseif($this->state==FMS_BODY && $name=='P')
  $this->state=FMS_P;
elseif($this->state==FMS_P && $name=='BR')
  $this->flushCurrent();
elseif($this->state==FMS_SUBJECT || $this->state==FMS_BODY
       || $this->state==FMS_P)
  $this->body.=makeTag($name,$attrs);
debugLog(LL_DEBUG,'state=%',array($this));
}

function endElement($parser,$name)
{
if($this->state==FMS_SUBJECT && $name=='SUBJECT')
  {
  $this->subject=$this->current;
  $this->current='';
  $this->state=FMS_GLOBAL;
  }
elseif($this->state==FMS_BODY && $name=='CONTENT')
  $this->state=FMS_GLOBAL;
elseif($this->state==FMS_P && $name=='P')
  {
  $this->flushCurrent();
  $this->body.="\n";
  $this->state=FMS_BODY;
  }
elseif($this->state==FMS_P && $name=='BR')
  ;
elseif($this->state==FMS_SUBJECT || $this->state==FMS_BODY
       || $this->state==FMS_P)
  $this->body.=makeTag("/$name",$attrs);
}

function characterData($parser,$data)
{
if($this->state!=FMS_SUBJECT && $this->state!=FMS_P)
  return;
$data=strtr($data,"\r\n",'  ');
$data=str_replace('&nbsp;',' ',$data);
$data=preg_replace('/\s+/',' ',$data);
if($data=='')
  return;
if($this->current=='' && $data[0]==' ')
  $this->current.=substr($data,1);
else
  $this->current.=$data;
}

}

function getMailScriptName($template)
{
global $mailingsDir;

return $mailingsDir.'/'.strtr($template,'_','-').'.php';
}

function getMailFunctionName($template)
{
return "displaymailing_$template";
}

function formatMail($template,$params)
{
global $mailSubjectPrefix,$charsetExternal,$mailFromAddress,$mailReplyToAddress,
       $mailHeadersDelimiter;

debugLog(LL_FUNCTIONS,'formatMail(template=%,params=%)',
         array($template,$params));
include(getMailScriptName($template));
$func=getMailFunctionName($template);
if(function_exists($func))
  {
  $mail=call_user_func_array($func,$params);
  $xml=new FormatMailXML();
  $xml->parse($mail);
  $xml->free();
  $result=$xml->getResult();
  // Subject
  $result[0]=convertOutput($mailSubjectPrefix).$result[0];
  $result[0]="=?$charsetExternal?B?".base64_encode($result[0]).'?=';
  // Headers
  $result[1]=join($mailHeadersDelimiter,
                  array("From: $mailFromAddress",
                        "Reply-To: $mailReplyToAddress",
                        "Content-Type: text/plain; charset=$charsetExternal",
			"Content-Transfer-Encoding: base64",
			$result[1]));
  $result[2]=wordwrap(base64_encode($result[2]),70,"\n",true);
  debugLog(LL_DEBUG,'result=%',array($result));
  return $result;
  }
else
  return array('','','');
}
?>
