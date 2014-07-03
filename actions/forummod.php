<?php
# @(#) $Id$

require_once('conf/migdal.conf');

require_once('lib/errorreporting.php');
require_once('lib/database.php');
require_once('lib/session.php');
require_once('lib/post.php');
require_once('lib/utils.php');
require_once('lib/errors.php');
require_once('lib/tmptexts.php');
require_once('lib/postings.php');
require_once('lib/forums.php');
require_once('lib/permissions.php');
require_once('lib/image-upload.php');
require_once('lib/logging.php');
require_once('lib/captcha.php');

function modifyForum($forum, $original) {
    global $captcha, $userId, $realUserId, $forumMandatoryBody,
           $forumMandatoryImage;

    if ($userId <= 0 && $realUserId <= 0)
        return EF_NO_SEND;
    if ($original->getId() != 0 && !$original->isWritable())
        return EF_NO_EDIT;
    if (!entryExists(ENT_NULL, $forum->getParentId()))
        return EF_NO_PARENT;
    $correct = validateHierarchy($forum->getParentId(), $forum->getUpValue(),
                                 ENT_FORUM, $forum->getId());
    if ($correct != EG_OK)
        return $correct;
    $parent = getPermsById($forum->getParentId());
    if (!$parent->isPostable())
        return EF_NO_SEND;
    if ($forum->getUpValue() != $forum->getParentId()) {
        $perms = getPermsById($forum->getUpValue());
        if (!$perms->isAppendable())
            return EF_UP_APPEND;
    }
    if ($forumMandatorySubject && $forum->getSubject() == '')
        return EF_SUBJECT_ABSENT;
    if ($forumMandatoryAuthor && $forum->getAuthor() == '')
        return EF_AUTHOR_ABSENT;
    if ($forumMandatoryBody && $forum->getBody() == '')
        return EF_BODY_ABSENT;
    if ($forumMandatoryImage && !$forum->hasSmallImage())
        return EF_IMAGE_ABSENT;
    if ($forum->hasSmallImage()
        && !imageFileExists($forum->getSmallImageFormat(),
                            $forum->getSmallImage()))
        return EF_NO_IMAGE;
    if ($forum->hasLargeImage()
        && !imageFileExists($forum->getLargeImageFormat(),
                            $forum->getLargeImage()))
        return EF_NO_IMAGE;
    if ($forum->getId() <= 0 && $userId <= 0) {
        if ($captcha == '')
            return EF_CAPTCHA_ABSENT;
        if (!validateCaptcha($captcha))
            return EF_CAPTCHA;
    }
    storeForum($forum);
    return EG_OK;
}

httpRequestString('okdir');
httpRequestString('faildir');

httpRequestInteger('relogin');
httpRequestString('guest_login');
httpRequestString('login');
httpRequestString('password');
httpRequestInteger('remember');

httpRequestInteger('editid');
httpRequestInteger('edittag');
httpRequestString('captcha');
httpRequestInteger('parent_id');
httpRequestInteger('up');
httpRequestString('subject');
httpRequestString('author');
httpRequestString('body');
httpRequestInteger('small_image');
httpRequestInteger('small_image_x');
httpRequestInteger('small_image_y');
httpRequestString('small_image_format');
httpRequestInteger('large_image');
httpRequestInteger('large_image_x');
httpRequestInteger('large_image_y');
httpRequestInteger('large_image_size');
httpRequestString('large_image_format');
httpRequestString('large_image_filename');
httpRequestInteger('del_image');
httpRequestInteger('hidden');
httpRequestInteger('disabled');

dbOpen();
session();
$err = EG_OK;
loginHints($login, $userMyComputerHint);
if ($editid<=0)
    $err = relogin($relogin, $login, $password, $remember, $guest_login);
$forum = getForumById($editid, $parent_id);
$original = clone $forum;
$forum->setup($Args);

$erru = uploadStandardImage('image_file', $forum, 'auto-manual',
                            0, 0, $thumbnailWidth, $thumbnailHeight,
		            0, 0, 0, 0, $del_image);
if ($err == EG_OK)
    $err = $erru;
if ($err == EG_OK)
    $err = modifyForum($forum, $original);
if ($err == EG_OK) {
    header(
        'Location: '.
        remakeURI(
            $okdir,
            array(
                'offset'
            ),
            array(
                'tid' => $forum->getId()
            ),
            't'.$forum->getId()
        )
    );
} else {
    $bodyId = tmpTextSave($body);
    $subjectId = tmpTextSave($subject);
    $authorId = tmpTextSave($author);
    $smallImageFormatId = tmpTextSave($forum->getSmallImageFormat());
    $largeImageFormatId = tmpTextSave($forum->getLargeImageFormat());
    $largeImageFilenameId = tmpTextSave($forum->getLargeImageFilename());
    header(
        'Location: '.
        remakeMakeURI(
            $faildir,
            $Args,
            array(
                'password',
                'body',
                'subject',
                'author',
                'okdir',
                'faildir'
            ),
            array(
                'body_i'        => $bodyId,
                'subject_i'     => $subjectId,
                'author_i'      => $authorId,
                'small_image'   => $forum->getSmallImage(),
                'small_image_x' => $forum->getSmallImageX(),
                'small_image_y' => $forum->getSmallImageY(),
                'small_image_format_i' => $smallImageFormatId,
                'large_image'   => $forum->getLargeImage(),
                'large_image_x' => $forum->getLargeImageX(),
                'large_image_y' => $forum->getLargeImageY(),
                'large_image_size' => $forum->getLargeImageSize(),
                'large_image_format_i' => $largeImageFormatId,
                'large_image_filename_i' => $largeImageFilenameId,
                'err'           => $err)
        ).'#error'
    );
  }
dbClose();
?>
