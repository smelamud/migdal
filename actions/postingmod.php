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
require_once('lib/grps.php');
require_once('lib/topics.php');
require_once('lib/image-upload.php');
require_once('lib/postings.php');
require_once('lib/text-upload.php');
require_once('lib/track.php');
require_once('lib/catalog.php');
require_once('lib/redirs.php');
require_once('lib/modbits.php');
require_once('lib/counters.php');
require_once('lib/logging.php');
require_once('lib/sql.php');
require_once('lib/captcha.php');

function imageSizeErrorCode($err, $imageEditor) {
    if ($err == EIU_WRONG_IMAGE_SIZE) {
        if ($imageEditor['imageExactX'] > 0 && $imageEditor['imageExactY'] > 0)
            return EP_LARGE_IMAGE_EXACT;
        if ($imageEditor['imageExactX'] > 0)
            return EP_LARGE_IMAGE_EXACT_X;
        if ($imageEditor['imageExactY'] > 0)
            return EP_LARGE_IMAGE_EXACT_Y;
    }
    if ($err == EIU_LARGE_IMAGE_SIZE) {
        if ($imageEditor['imageMaxX'] > 0 && $imageEditor['imageMaxY'] > 0)
            return EP_LARGE_IMAGE_MAX;
        if ($imageEditor['imageMaxX'] > 0)
            return EP_LARGE_IMAGE_MAX_X;
        if ($imageEditor['imageMaxY'] > 0)
            return EP_LARGE_IMAGE_MAX_Y;
    }
    if ($err == EIU_WRONG_THUMBNAIL_SIZE) {
        if ($imageEditor['thumbExactX'] > 0 && $imageEditor['thumbExactY'] > 0)
            return EP_SMALL_IMAGE_EXACT;
        if ($imageEditor['thumbExactX'] > 0)
            return EP_SMALL_IMAGE_EXACT_X;
        if ($imageEditor['thumbExactY'] > 0)
            return EP_SMALL_IMAGE_EXACT_Y;
    }
    if ($err == EIU_LARGE_THUMBNAIL_SIZE) {
        if ($imageEditor['thumbMaxX'] > 0 && $imageEditor['thumbMaxY'] > 0)
            return EP_SMALL_IMAGE_MAX;
        if ($imageEditor['thumbMaxX'] > 0)
            return EP_SMALL_IMAGE_MAX_X;
        if ($imageEditor['thumbMaxY'] > 0)
            return EP_SMALL_IMAGE_MAX_Y;
    }
    return $err;
}

function isSpam($body) {
    $spams = array('clickcashmoney.com', 'porno-video-free', 'porno-exe',
                   'rem-stroi.com', 'hiphoprussia.ru', 'retrade.ru', 't35.com',
                   'viagra');
    foreach ($spams as $spam) {
        if (strpos($body,$spam) !== false) {
            logEvent('spam',$spam);
            return true;
        }
    }
    return false;
}

function modifyPosting(Posting $posting, Posting $original,
                       array $imageEditor) {
    global $captcha, $userId;

    if ($original->getId() != 0 && !$original->isWritable())
        return EP_NO_EDIT;
    if (!isGrpValid($posting->grp))
        return EP_INVALID_GRP;
    if ($posting->isMandatory('body') && $posting->body == '')
        return EP_BODY_ABSENT;
    if ($posting->body != '' && isSpam($posting->body))
        return EP_SPAM;
    if ($posting->isMandatory('lang') && $posting->lang == '')
        return EP_LANG_ABSENT;
    if ($posting->isMandatory('subject') && $posting->subject == '')
        return EP_SUBJECT_ABSENT;
    if ($posting->isMandatory('author') && $posting->author == '')
        return EP_AUTHOR_ABSENT;
    if ($posting->isMandatory('source') && $posting->source == '')
        return EP_SOURCE_ABSENT;
    if (($posting->isMandatory('large_body')
         || $posting->isMandatory('large_body_upload'))
        && $posting->large_body == '')
        return EP_LARGE_BODY_ABSENT;
    if ($posting->isMandatory('url') && $posting->url == '')
        return EP_URL_ABSENT;
    if ($posting->url != '' && strpos($posting->url, '://') === false
        && $posting->url{0} != '/')
        $posting->url = "http://{$posting->url}";
    if ($posting->isMandatory('topic') && $posting->parent_id == 0)
        return EP_TOPIC_ABSENT;
    if (getTypeByEntryId($posting->up) == ENT_TOPIC
        || $posting->up == $original->parent_id)
        $posting->up = $posting->parent_id;
    $correct = validateHierarchy($posting->parent_id, $posting->up,
                                 ENT_POSTING, $posting->id);
    if ($correct != EG_OK)
        return $correct;
    if ($original->getId() == 0
        || $original->parent_id != $posting->parent_id) {
        if ($posting->parent_id != 0)
            $perms = getPermsById($posting->parent_id);
        else
            $perms = getRootPerms('Topic');
        if (!$perms->isPostable())
            return EP_TOPIC_ACCESS;
    }
    if ($posting->up != 0 && $posting->up != $posting->parent_id) {
        $perms = getPermsById($posting->up);
        if (!$perms->isAppendable())
            return EP_UP_APPEND;
    }
    if ($posting->isMandatory('ident') && $posting->ident == '')
        return EP_IDENT_ABSENT;
    $cid = idByIdent($posting->ident);
    if ($posting->ident != '' && $cid != 0 && $posting->id != $cid)
        return EP_IDENT_UNIQUE;
    if ($posting->isMandatory('index1') && $posting->index1 == 0)
        return EP_INDEX1_ABSENT;
    if ($posting->isMandatory('image') && !$posting->hasImage())
        return EP_IMAGE_ABSENT;
    if ($posting->person_id != 0 && !personalExists($posting->person_id))
        return EP_NO_PERSON;
    if ($posting->id <= 0 && $userId <= 0) {
        if ($captcha == '')
            return EP_CAPTCHA_ABSENT;
        if (!validateCaptcha($captcha))
            return EP_CAPTCHA;
    }
    $posting->track = '';
    $posting->catalog = '';
    storePosting($posting);
    setPremoderates($posting, $original);
    if ($original->getId() == 0)
        createCounters($posting->id, $posting->grp);
    return EG_OK;
}

httpRequestString('okdir');
httpRequestString('faildir');

httpRequestInteger('full');
httpRequestInteger('relogin');
httpRequestString('guest_login');
httpRequestString('login');
httpRequestString('password');
httpRequestInteger('remember');
httpRequestInteger('noguests');

httpRequestIdent('editid');
httpRequestInteger('edittag');
httpRequestString('captcha');
httpRequestInteger('grp');
httpRequestInteger('index1');
httpRequestInteger('index2');
httpRequestInteger('up');
httpRequestString('body');
httpRequestInteger('body_format');
httpRequestString('large_body');
httpRequestInteger('del_large_body');
httpRequestInteger('large_body_format');
httpRequestString('subject');
httpRequestString('author');
httpRequestString('source');
httpRequestString('comment0');
httpRequestString('comment1');
httpRequestString('title');
httpRequestString('url');
httpRequestIdent('parent_id');
httpRequestInteger('priority');
httpRequestString('ident');
httpRequestString('lang');
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
httpRequestInteger('person_id');
httpRequestInteger('hidden');
httpRequestInteger('disabled');
httpRequestInteger('sent');
httpRequestInteger('sent_year');
httpRequestInteger('sent_month');
httpRequestInteger('sent_day');
httpRequestInteger('sent_hour');
httpRequestInteger('sent_minute');
httpRequestInteger('sent_second');

dbOpen();
session();
$err = EG_OK;
loginHints($login, $userMyComputerHint);
if ($editid <= 0)
    $err = relogin($relogin, $login, $password, $remember, $guest_login);
$posting = getPostingById($editid, $grp, $parent_id,
                          SELECT_GENERAL | SELECT_LARGE_BODY, $up);
$original = clone $posting;
$posting->setup($Args);

$imageEditor = $posting->getGrpImageEditor();
$erru = uploadStandardImage('image_file', $posting, $imageEditor['style'],
                $imageEditor['thumbExactX'], $imageEditor['thumbExactY'],
                $imageEditor['thumbMaxX'], $imageEditor['thumbMaxY'],
                $imageEditor['imageExactX'], $imageEditor['imageExactY'],
                $imageEditor['imageMaxX'], $imageEditor['imageMaxY'],
                $del_image);
if ($erru != EG_OK)
    $erru = imageSizeErrorCode($erru, $imageEditor);
if ($err == EG_OK)
    $err = $erru;

$erru = uploadLargeBody($posting,$del_large_body);
if ($err == EG_OK)
    $err = $erru;

if ($err == EG_OK)
    $err = modifyPosting($posting, $original, $imageEditor);
if ($err == EG_OK) {
    if ($posting->isDisabled() && ($userId <= 0 || $userWillBeModeratedNote))
        header(
            'Location: '.
            remakeURI(
                '/will-be-moderated/',
                array(),
                array('okdir_i' => tmpTextSave($okdir))
            )
        );
    else
        header("Location: $okdir");
} else {
    $bodyId = tmpTextSave($body);
    $largeBodyId = tmpTextSave($posting->large_body);
    $largeBodyFilenameId = tmpTextSave($posting->large_body_filename);
    $subjectId = tmpTextSave($subject);
    $authorId = tmpTextSave($author);
    $sourceId = tmpTextSave($source);
    $comment0Id = tmpTextSave($comment0);
    $comment1Id = tmpTextSave($comment1);
    $titleId = tmpTextSave($title);
    $urlId = tmpTextSave($url);
    $smallImageFormatId = tmpTextSave($posting->small_image_format);
    $largeImageFormatId = tmpTextSave($posting->large_image_format);
    $largeImageFilenameId = tmpTextSave($posting->large_image_filename);
    header(
        'Location: '.
        remakeMakeURI(
            $faildir,
            $Args,
            array(
                'password',
                'body',
                'large_body',
                'subject',
                'author',
                'source',
                'comment0',
                'comment1',
                'title',
                'url',
                'sent_year',
                'sent_month',
                'sent_day',
                'sent_hour',
                'sent_minute',
                'sent_second',
                'okdir',
                'faildir'
            ),
            array(
                'body_i'        => $bodyId,
                'large_body_i'  => $largeBodyId,
                'large_body_filename_i' => $largeBodyFilenameId,
                'subject_i'     => $subjectId,
                'author_i'      => $authorId,
                'source_i'      => $sourceId,
                'comment0_i'    => $comment0Id,
                'comment1_i'    => $comment1Id,
                'title_i'       => $titleId,
                'url_i'         => $urlId,
                'small_image'   => $posting->small_image,
                'small_image_x' => $posting->small_image_x,
                'small_image_y' => $posting->small_image_y,
                'small_image_format_i' => $smallImageFormatId,
                'large_image'   => $posting->large_image,
                'large_image_x' => $posting->large_image_x,
                'large_image_y' => $posting->large_image_y,
                'large_image_size' => $posting->large_image_size,
                'large_image_format_i' => $largeImageFormatId,
                'large_image_filename_i' => $largeImageFilenameId,
                'sent'          => $posting->getSent(),
                'err'           => $err
            )
        ).'#error'
    );
}
dbClose();
?>
