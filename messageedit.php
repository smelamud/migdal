<?php
# @(#) $Id$

require_once('lib/errorreporting.php');
require_once('lib/database.php');
require_once('lib/grps.php');
require_once('lib/messages.php');
require_once('lib/topics.php');

require_once('parts/top.php');
require_once('parts/grps.php');

function elementEdit($title,$value,$name,$size,$length)
{
return "<tr>
         <td>$title </td>
         <td><input type=text name='$name' value='$value' size=$size
	            maxlength=$length></td>
	</tr>";
}

function elementCheckBox($name,$value,$title)
{
return "<tr><td>
         <input type=checkbox name=$name value=1 ".($value ? 'checked' : '')
                                                 ."> $title
	</td></tr>";
}

function elementOption($label,$value,$peervalue)
{
return "<option label='$label' value=$value".
       ($value==$peervalue ? ' selected ' : '').
       '>';
}

function perror($code,$message,$color='red')
{
global $err;

if($err==$code)
  echo "<tr><td><a name='error'>
         <font color='$color'>$message</font>
	</td></tr>";
}

$title=getGrpOneTitle($grp);
$ident=getGrpIdent($grp);
$requestURI=urlencode($REQUEST_URI);

if(!isset($ident))
  {
  header('Location: '.remakeURI($REQUEST_URI,
                                array(),
				array('grp' => GRP_FORUMS)));
  exit;
  }

settype($editid,'integer');
settype($up,'integer');
?>
<html>
<head>
 <title>���� ���������� �������� - <?php echo $title ?></title>
</head>
<body bgcolor=white>
 <?php
 dbOpen();
 displayTop($ident);
 ?>
 <p>
 <a href='<?php echo $redir ?>'>&lt;&lt; ���������</a>
 <p>
 <?php
 $message=getMessageById($editid,$grp);
 $message->setUpValue($up);
 $message->setup($HTTP_GET_VARS);
 ?>
 <form method=post enctype='multipart/form-data'
       action='actions/messagemod.php'>
 <input type=hidden name='edittag' value=1>
 <input type=hidden name='redir' value='<?php echo $redir ?>'>
 <input type=hidden name='editid' value='<?php echo $editid ?>'>
 <input type=hidden name='grp' value='<?php echo $message->getGrp() ?>'>
 <input type=hidden name='up' value='<?php echo $message->getUpValue() ?>'>
 <input type=hidden name='personal_id' value='<?php
  echo $message->getPersonalId();
 ?>'>
 <table>
 <?php
 perror(EM_NO_SEND,'�������� ��������� ����� ������ ������������������
                    ������������');
 perror(EM_NO_EDIT,'� ��� ��� ����� ������������� ��� ���������');
 perror(EM_STORE_SQL,'������ ���� ������ ��� ���������� ������','magenta');
 perror(EM_FORUM_ANSWER,'�������� ����� ������ � ������');
 perror(EM_NO_UP,'���������, �� ������� �� ��������� ��������, ��� �� ��������');
 perror(EM_NO_PERSONAL,'� ����� ������������ ��� ������������ ��������');
 perror(EM_TOPIC_ABSENT,'���� ������ ���� �������');
 perror(EM_NO_TOPIC,'����� ���� �� ����������');
 ?>
 <tr><td><table>
  <tr>
   <td>����</td>
   <td>
    <select name='topic_id'>
     <?php
     echo elementOption('-- �� ������� --',0,$message->getTopicId());
     $list=new TopicNamesIterator($grp);
     while($item=$list->next())
	  echo elementOption($item->getName(),$item->getId(),
	                     $message->getTopicId());
     ?>
    </select>
   </td>
  </tr>
  <?php
  if($message->hasSubject())
    {
    perror(EM_SUBJECT_ABSENT,'��������� ����');
    echo elementEdit('���������',$message->getSubject(),'subject',42,250);
    }
  if($message->hasImage())
    {
    perror(EM_IMAGE_ABSENT,'����� ������� �������� ��� �����������');
    perror(EM_NO_IMAGE,'��������� �������� �� ����������');
    perror(EM_UNKNOWN_IMAGE,'����������� ������ ��������');
    perror(EM_IMAGE_LARGE,'�������� ������� ������ (������ 2M)');
    perror(EM_IMAGE_SQL,'������ ���� ������ ��� �������� ��������','magenta');
    if($message->getImageSet()==0)
      {
      ?>
      <tr>
       <td>��������</td>
       <td><input type=file name='image' size=35></td>
      </tr>
      <?php
      }
    else
      {
      $image=getImageNameBySet($message->getImageSet());
      ?>
      <tr><td colspan=2><table>
       <tr><td>��������</td></tr>
       <tr>
	<td>
	 <input type=radio name='image_set' value=<?php
	  echo $message->getImageSet();
	 ?> checked >
	 &nbsp;<?php echo $image->getFilename() ?>&nbsp;<i>(��� ���������)</i>
	</td>
       </tr>
       <tr><td>
	<input type=radio name='image_set' value=0>&nbsp;������&nbsp;
	<input type=file name='image' size=35>
       </td></tr>
      </table></td></tr>
      <?php
      }
    }
  ?>
 </table></td></tr>
 <?php
 perror(EM_BODY_ABSENT,'�������� ���� �� ���� ������� � ���� ���������');
 ?>
 <tr><td>�����</td></tr>
 <tr><td>
  <textarea name='body' rows=10 cols=50 wrap='virtual'><?php
   echo $message->getBody();
  ?></textarea>
 </td></tr>
 <?php
 echo elementCheckBox('hidden',$message->isHidden(),'�� ����������');
 if($userModerator)
   echo elementCheckBox('disabled',$message->isDisabled(),'��������� �����');
 ?>
 </table>
 <input type=submit value='<?php echo $editid ? '��������' : '��������' ?>'>
 <input type=reset value='��������'>
 </form>
 <?php
 dbClose();
 ?>
</body>
</html>
