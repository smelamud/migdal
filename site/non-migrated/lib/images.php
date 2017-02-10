<?php
# @(#) $Id$

require_once('conf/migdal.conf');

require_once('lib/entries.php');
require_once('lib/selectiterator.php');
require_once('lib/bug.php');
require_once('lib/debug-log.php');
require_once('lib/text.php');
require_once('lib/image-types.php');
require_once('lib/sql.php');
require_once('lib/text-any.php');
require_once('lib/image-files.php');

class Image
        extends Entry {

    public function __construct(array $row = array()) {
        global $tfRegular;

        $this->entry = ENT_IMAGE;
        $this->body_format = $tfRegular;
        parent::__construct($row);
    }

    public function setup($vars) {
        global $tfRegular;

        if (!isset($vars['edittag']) || !$vars['edittag'])
            return;
        $this->up = $vars['postid'];
        $this->body_format = $tfRegular;
        $this->title = $vars['title'];
        $this->title_xml = anyToXML($this->title, $this->body_format,
                                    MTEXT_LINE);
        $this->small_image = $vars['small_image'];
        $this->small_image_x = $vars['small_image_x'];
        $this->small_image_y = $vars['small_image_y'];
        $this->small_image_format = $vars['small_image_format'];
        $this->large_image = $vars['large_image'];
        $this->large_image_x = $vars['large_image_x'];
        $this->large_image_y = $vars['large_image_y'];
        $this->large_image_size = $vars['large_image_size'];
        $this->large_image_format = $vars['large_image_format'];
        $this->large_image_filename = $vars['large_image_filename'];
    }

}

class ImagesIterator
        extends SelectIterator {

    public function __construct($postid) {
        // Показываем все нижестоящие entries, в которых есть картинка, не обязательно
        // типа ENT_IMAGE
        parent::__construct(
            'Entry',
            "select id,ident,entry,up,track,catalog,parent_id,
                    orig_id,user_id,group_id,perms,disabled,title,
                    title_xml,body_format,sent,created,modified,
                    accessed,small_image,small_image_x,small_image_y,
                    small_image_format,large_image,large_image_x,large_image_y,
                    large_image_size,large_image_format,
                    large_image_filename,count(entry_id) as inserted
             from entries
                  left join inner_images
                       on entries.id=image_id
             where up=$postid and small_image<>0
             group by id");
    }

}

function storeImage(&$image) {
    global $userId;
    
    $vars = array(
        'ident' => $image->getIdent(),
        'up' => $image->getUpValue(),
        'parent_id' => $image->getParentId(),
        'user_id' => $image->getUserId(),
        'group_id' => $image->getGroupId(),
        'perms' => $image->getPerms(),
        'title' => $image->getTitle(),
        'title_xml' => $image->getTitleXML(),
        'body_format' => $image->getBodyFormat(),
        'small_image' => $image->getSmallImage(),
        'small_image_x' => $image->getSmallImageX(),
        'small_image_y' => $image->getSmallImageY(),
        'small_image_format' => $image->getSmallImageFormat(),
        'large_image' => $image->getLargeImage(),
        'large_image_x' => $image->getLargeImageX(),
        'large_image_y' => $image->getLargeImageY(),
        'large_image_size' => $image->getLargeImageSize(),
        'large_image_format' => $image->getLargeImageFormat(),
        'large_image_filename' => $image->getLargeImageFilename(),
        'modified' => sqlNow()
    );
    if ($image->getId()) {
        $image->setTrack(trackById('entries', $image->getId()));
        $result = sql(sqlUpdate('entries',
                                $vars,
                                array('id' => $image->getId())),
                      __FUNCTION__, 'update');
        updateCatalogs($image->getTrack());
        replaceTracksToUp('entries', $image->getTrack(), $image->getUpValue(),
                          $image->getId());
    } else {
        $vars['entry'] = $image->getEntry();
        $vars['sent'] = sqlNow();
        $vars['created'] = sqlNow();
        $vars['track'] = (string) time();
        $result = sql(sqlInsert('entries',
                                $vars),
                      __FUNCTION__, 'insert');
        $image->setId(sql_insert_id());
        setOrigIdToEntryId($image);
        createTrack('entries', $image->getId());
        updateCatalogs(trackById('entries', $image->getId()));
    }
    return $result;
}

function getImageById($id) {
    $result = sql("select id,ident,entry,up,track,catalog,parent_id,orig_id,
                          user_id,group_id,perms,disabled,title,title_xml,
                          body_format,sent,created,modified,accessed,
                          small_image,small_image_x,small_image_y,
                          small_image_format,large_image,large_image_x,
                          large_image_y,large_image_size,large_image_format,
                          large_image_filename
                   from entries
                   where id=$id",
                  __FUNCTION__);
    return new Image(mysql_num_rows($result) > 0 ? mysql_fetch_assoc($result)
                                                 : array());
}

function deleteImage($id) {
    sql("delete
         from inner_images
         where image_id=$id",
        __FUNCTION__, 'inner');
    sql("delete
         from entries
         where id=$id",
        __FUNCTION__, 'entry');
}
?>
