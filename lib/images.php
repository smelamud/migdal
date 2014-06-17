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
      extends Entry
{

function __construct($row)
{
global $tfRegular;

$this->entry=ENT_IMAGE;
$this->body_format=$tfRegular;
parent::__construct($row);
}

function setup($vars)
{
global $tfRegular;

if(!isset($vars['edittag']) || !$vars['edittag'])
  return;
$this->up=$vars['postid'];
$this->body_format=$tfRegular;
$this->title=$vars['title'];
$this->title_xml=anyToXML($this->title,$this->body_format,MTEXT_LINE);
$this->small_image=$vars['small_image'];
$this->small_image_x=$vars['small_image_x'];
$this->small_image_y=$vars['small_image_y'];
$this->large_image=$vars['large_image'];
$this->large_image_x=$vars['large_image_x'];
$this->large_image_y=$vars['large_image_y'];
$this->large_image_size=$vars['large_image_size'];
$this->large_image_format=$vars['large_image_format'];
$this->large_image_filename=$vars['large_image_filename'];
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
                    large_image,large_image_x,large_image_y,
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
        'ident' => $image->ident,
        'up' => $image->up,
        'parent_id' => $image->parent_id,
        'user_id' => $image->user_id,
        'group_id' => $image->group_id,
        'perms' => $image->perms,
        'title' => $image->title,
        'title_xml' => $image->title_xml,
        'body_format' => $image->body_format,
        'small_image' => $image->small_image,
        'small_image_x' => $image->small_image_x,
        'small_image_y' => $image->small_image_y,
        'large_image' => $image->large_image,
        'large_image_x' => $image->large_image_x,
        'large_image_y' => $image->large_image_y,
        'large_image_size' => $image->large_image_size,
        'large_image_format' => $image->large_image_format,
        'large_image_filename' => $image->large_image_filename,
        'modified' => sqlNow()
    );
    if ($image->id) {
        $image->track = trackById('entries', $image->id);
        $result = sql(sqlUpdate('entries',
                                $vars,
                                array('id' => $image->id)),
                      __FUNCTION__, 'update');
        updateCatalogs($image->track);
        replaceTracksToUp('entries', $image->track, $image->up, $image->id);
    } else {
        $vars['entry'] = $image->entry;
        $vars['sent'] = sqlNow();
        $vars['created'] = sqlNow();
        $vars['track'] = (string) time();
        $result = sql(sqlInsert('entries',
                                $vars),
                      __FUNCTION__, 'insert');
        $image->id = sql_insert_id();
        setOrigIdToEntryId($image);
        createTrack('entries', $image->id);
        updateCatalogs(trackById('entries', $image->id));
    }
    return $result;
}

function getImageById($id) {
    $result = sql("select id,ident,entry,up,track,catalog,parent_id,orig_id,
                          user_id,group_id,perms,disabled,title,title_xml,
                          body_format,sent,created,modified,accessed,
                          small_image,small_image_x,small_image_y,
                          large_image,large_image_x,large_image_y,
                          large_image_size,large_image_format,
                          large_image_filename
                   from entries
                   where id=$id",
                  __FUNCTION__);
    return new Image(mysql_num_rows($result) > 0 ? mysql_fetch_assoc($result)
                                                 : array());
}

function deleteImage($id, $small_image, $large_image, $large_image_format) {
    sql("delete
         from inner_images
         where image_id=$id",
        __FUNCTION__, 'inner');
    sql("delete
         from entries
         where id=$id",
        __FUNCTION__, 'entry');
    deleteImageFiles($small_image, $large_image, $large_image_format);
}
?>
