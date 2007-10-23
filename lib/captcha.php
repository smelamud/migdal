<?php
# @(#) $Id$

require_once('lib/bug.php');
require_once('lib/sql.php');

function storeCaptcha($keystring)
{
global $sessionid;

sql(sqlInsert('captcha_keys',
              array('keystring' => $keystring,
                    'sid'       => $sessionid,
                    'created'   => sqlNow())),
    __FUNCTION__);
}

function captchaExists($keystring)
{
global $sessionid;

$keystringS=addslashes($keystring);
$result=sql("select id
             from captcha_keys
             where keystring='$keystringS' and sid='$sessionid'",
            __FUNCTION__);
return mysql_num_rows($result)>0;
}

function deleteCaptcha($keystring)
{
global $sessionid;

$keystringS=addslashes($keystring);
sql("delete from captcha_keys
     where keystring='$keystringS' and sid='$sessionid'",
    __FUNCTION__);
}

function validateCaptcha($keystring)
{
$isValid=captchaExists($keystring);
if($isValid)
  deleteCaptcha($keystring);
return $isValid;
}

function deleteObsoleteCaptchas()
{
global $captchaTimeout;

$now=sqlNow();
sql("delete from captcha_keys
     where created+interval $captchaTimeout hour<'$now'",
    __FUNCTION__);
sql('optimize table captcha_keys',
    __FUNCTION__,'optimize');
}
?>
