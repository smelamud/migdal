<?php
# @(#) $Id$

require_once('lib/entry-types.php');
require_once('lib/permissions.php');

/*
 * ��� ������� ���������� ��� ����, ����� ��������� ����� ������� �� entry, ��
 * �������� ���������������� ���������� ������ Entry. ��� �����������,
 * ��������, ��� ������ �������������� ��� �������� permissions ����� �����
 * Perms.
 */

$permSchemes=array(ENT_NULL     => '',
                   ENT_POSTING  => 'isPermittedPosting',
		   ENT_FORUM    => 'isPermittedForum',
		   ENT_TOPIC    => 'isPermittedTopic',
		   ENT_IMAGE    => '',
		   ENT_COMPLAIN => 'isPermittedComplain',
		   ENT_VERSION  => '');

function isPermittedEntry($entry,$right)
{
global $permSchemes;

if(isset($permSchemes[$entry->entry]))
  {
  $func=$permSchemes[$entry->entry];
  if($func!='' && function_exists($func))
    return $func($entry,$right);
  }
return true;
}

function isPermittedPosting($posting,$right)
{
global $userModerator,$userId;

return $userModerator
       ||
       (!$posting->isDisabled() || $posting->getUserId()==$userId)
       && perm($posting->getUserId(),$posting->getGroupId(),
               $posting->getPerms(),$right);
}

function isPermittedForum($forum,$right)
{
global $userModerator,$userId;

return $userModerator
       ||
       (!$forum->isDisabled() || $forum->getUserId()==$userId)
       && perm($forum->getUserId(),$forum->getGroupId(),
               $forum->getPerms(),$right);
}

function isPermittedTopic($topic,$right)
{
global $userAdminTopics,$userModerator;

return $userAdminTopics && $right!=PERM_POST
       ||
       $userModerator && $right==PERM_POST
       ||
       perm($topic->getUserId(),$topic->getGroupId(),
            $topic->getPerms(),$right);
}

function isPermittedComplain($complain,$right)
{
global $userId,$userModerator;

switch($right)
      {
      case PERM_READ:
           return true;
      case PERM_WRITE:
           return $complain->getUserId()==$userId || $userModerator;
      case PERM_APPEND:
           return true; // for abstract root complain
      case PERM_POST:
           return true;
      default:
           return false;
      }
}
?>