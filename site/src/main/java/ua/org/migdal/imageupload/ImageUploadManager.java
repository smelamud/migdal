package ua.org.migdal.imageupload;

import static javax.transaction.Transactional.TxType.REQUIRES_NEW;

import java.awt.geom.AffineTransform;
import java.awt.image.AffineTransformOp;
import java.awt.image.BufferedImage;
import java.io.File;
import java.io.IOException;
import java.nio.file.Files;
import java.nio.file.attribute.PosixFilePermissions;
import java.util.HashMap;
import java.util.Map;
import java.util.UUID;

import javax.annotation.PostConstruct;
import javax.imageio.ImageIO;
import javax.inject.Inject;
import javax.transaction.Transactional;

import org.springframework.stereotype.Service;
import org.springframework.util.StringUtils;
import org.springframework.web.multipart.MultipartFile;

import ua.org.migdal.Config;
import ua.org.migdal.data.Entry;
import ua.org.migdal.data.ImageFile;
import ua.org.migdal.data.ImageFileTransform;
import ua.org.migdal.data.ImageFileTransformType;
import ua.org.migdal.grp.ImageTransformFlag;
import ua.org.migdal.grp.ThumbnailTransformFlag;
import ua.org.migdal.manager.ImageFileManager;
import ua.org.migdal.manager.ImageFileTransformManager;
import ua.org.migdal.util.MimeUtils;

@Service
public class ImageUploadManager {

    private static ImageUploadManager instance;

    @Inject
    private Config config;

    @Inject
    private ImageFileManager imageFileManager;

    @Inject
    private ImageFileTransformManager imageFileTransformManager;

    private Map<UUID, UploadedImage> uploads = new HashMap<>();

    @PostConstruct
    public void init() {
        instance = this;
    }

    public static ImageUploadManager getInstance() {
        return instance;
    }

    public UploadedImage get(String uuid) {
        if (StringUtils.isEmpty(uuid)) {
            return null;
        }

        try {
            UploadedImage uploadedImage = uploads.get(UUID.fromString(uuid));
            if (uploadedImage != null) {
                uploadedImage.access();
            }
            return uploadedImage;
        } catch (IllegalArgumentException e) {
            return null;
        }
    }

    public String extract(Entry entry) {
        if (entry.getSmallImage() == null && entry.getLargeImage() == null) {
            return "";
        }

        UUID uuid = UUID.randomUUID();
        uploads.put(uuid, new UploadedImage(entry));
        return uuid.toString();
    }

    public String uploadStandard(MultipartFile imageFile,
                                 ThumbnailTransformFlag thumbFlag, ImageTransformFlag imageFlag,
                                 short thumbExactX, short thumbExactY, short thumbMaxX, short thumbMaxY,
                                 short imageExactX, short imageExactY, short imageMaxX, short imageMaxY) {
        return uploadStandard(null, imageFile, thumbFlag, imageFlag,
                thumbExactX, thumbExactY, thumbMaxX, thumbMaxY,
                imageExactX, imageExactY, imageMaxX, imageMaxY);
    }

    @Transactional(REQUIRES_NEW)
    public String uploadStandard(String uploadedImageUuid, MultipartFile imageFile,
                                 ThumbnailTransformFlag thumbFlag, ImageTransformFlag imageFlag,
                                 short thumbExactX, short thumbExactY, short thumbMaxX, short thumbMaxY,
                                 short imageExactX, short imageExactY, short imageMaxX, short imageMaxY) {
        if ((uploadedImageUuid == null || uploadedImageUuid.isEmpty()) && (imageFile == null || imageFile.isEmpty())) {
            return "";
        }

        short exactX;
        short exactY;
        short maxX;
        short maxY;
        ImageFileTransformType transform;
        short transformX;
        short transformY;

        switch (imageFlag) {
            case RESIZE:
                exactX = 0;
                exactY = 0;
                maxX = 0;
                maxY = 0;
                transform = ImageFileTransformType.RESIZE;
                transformX = imageMaxX;
                transformY = imageMaxY;
                break;

            case MANUAL:
            default:
                exactX = imageExactX;
                exactY = imageExactY;
                maxX = imageMaxX;
                maxY = imageMaxY;
                transform = ImageFileTransformType.NULL;
                transformX = 0;
                transformY = 0;
        }
        UploadedImageFile largeImageFile;
        if (imageFile != null && !imageFile.isEmpty()) {
            largeImageFile = uploadImageFile(imageFile, exactX, exactY, maxX, maxY, transform, transformX, transformY);
            largeImageFile.setFileSize(imageFile.getSize());
            largeImageFile.setOriginalFilename(imageFile.getOriginalFilename());
        } else {
            largeImageFile = get(uploadedImageUuid).getLarge();
        }
        if (largeImageFile == null) {
            return "";
        }

        UploadedImageFile smallImageFile = null;

        switch (thumbFlag) {
            case AUTO:
                smallImageFile = thumbnailImageFile(largeImageFile, ImageFileTransformType.RESIZE,
                        thumbMaxX, thumbMaxY);
                break;

            case CLIP:
                smallImageFile = thumbnailImageFile(largeImageFile, ImageFileTransformType.CLIP,
                        thumbExactX, thumbExactY);
                break;

            case NONE:
            default:
                break;
        }

        if (smallImageFile == null || smallImageFile.getId() == 0) {
            smallImageFile = largeImageFile.clone();
        }

        UUID uuid = UUID.randomUUID();
        uploads.put(uuid, new UploadedImage(smallImageFile, largeImageFile));
        return uuid.toString();
    }

    private UploadedImageFile uploadImageFile(MultipartFile uploadedFile, short exactX, short exactY,
                                              short maxX, short maxY, ImageFileTransformType transform,
                                              short transformX, short transformY) {
        if (uploadedFile.getSize() > config.getMaxImageSize()) {
            throw new ImageUploadException("imageFileLarge");
        }
        BufferedImage image;
        try {
            image = ImageIO.read(uploadedFile.getInputStream());
        } catch (IOException e) {
            throw new ImageUploadException("imageFileReadError", e);
        }
        short sizeX = (short) image.getWidth();
        short sizeY = (short) image.getHeight();
        if (!MimeUtils.isImage(uploadedFile.getContentType())) {
            throw new ImageUploadException("imageFileWrongType");
        }
        if (exactX > 0 && (sizeX - exactX) > 1 || exactY > 0 && (sizeY - exactY) > 1) {
            throw new ImageUploadException("imageFileWrongImageSize");
        }
        if (maxX > 0 && sizeX > maxX || maxY > 0 && sizeY > maxY) {
            throw new ImageUploadException("imageFileImageLarge");
        }

        image = transformImage(image, transform, transformX, transformY);

        // FIXME what if we transformed GIF to JPG?
        return writeImageFile(image, uploadedFile.getContentType());
    }

    private UploadedImageFile writeImageFile(BufferedImage image, String mimeType) {
        UploadedImageFile imageInfo = new UploadedImageFile();
        imageInfo.setFormat(mimeType);
        imageInfo.setSizeX((short) image.getWidth());
        imageInfo.setSizeY((short) image.getHeight());
        ImageFile imageFile = new ImageFile(imageInfo);
        imageFileManager.save(imageFile);
        imageInfo.setId(imageFile.getId());

        File imageFilePath = new File(imageFile.getPath());
        try {
            ImageIO.write(image, MimeUtils.imageFormat(imageInfo.getFormat()), imageFilePath);
            imageInfo.setFileSize(Files.size(imageFilePath.toPath()));
            Files.setPosixFilePermissions(imageFilePath.toPath(), PosixFilePermissions.fromString("rw-r--r--"));
        } catch (IOException e) {
            throw new ImageUploadException("imageFileWriteError", e);
        }
        imageFile.setFileSize(imageInfo.getFileSize());
        imageFileManager.save(imageFile);
        return imageInfo;
    }

    private UploadedImageFile thumbnailImageFile(UploadedImageFile imageFile, ImageFileTransformType transform,
                                                 short transformX, short transformY) {
        if (imageFile.getId() == 0 || isTransformedImage(imageFile, transform, transformX, transformY)) {
            return imageFile.clone();
        }

        ImageFileTransform readyTransform = imageFileTransformManager.getBySource(
                imageFile.getId(), transform, transformX, transformY);
        if (readyTransform != null) {
            return new UploadedImageFile(readyTransform.getDestination());
        }

        BufferedImage image;
        try {
            image = ImageIO.read(new File(imageFile.getPath()));
        } catch (IOException e) {
            throw new ImageUploadException("imageFileReadError", e);
        }

        image = transformImage(image, transform, transformX, transformY);

        readyTransform = imageFileTransformManager.getBySource(
                imageFile.getId(), transform, (short) image.getWidth(), (short) image.getHeight());
        if (readyTransform != null) {
            return new UploadedImageFile(readyTransform.getDestination());
        }

        UploadedImageFile thumbFile = writeImageFile(image, MimeUtils.JPEG);

        ImageFile destination = imageFileManager.get(thumbFile.getId());
        ImageFile source = imageFileManager.get(imageFile.getId());
        imageFileTransformManager.save(new ImageFileTransform(destination, source, transform, transformX, transformY));

        return thumbFile;
    }

    private boolean isTransformedImage(UploadedImageFile imageFile, ImageFileTransformType transform,
                                       short transformX, short transformY) {
        switch (transform) {
            case RESIZE:
                return (transformX <= 0 || imageFile.getSizeX() <= transformX)
                        && (transformY <= 0 || imageFile.getSizeY() <= transformY);

            case CLIP:
                return (transformX <= 0 || imageFile.getSizeX() == transformX)
                        && (transformY <= 0 || imageFile.getSizeY() == transformY);

            default:
                return true;
        }
    }

    private BufferedImage transformImage(BufferedImage image, ImageFileTransformType transform,
                                         short transformX, short transformY) {
        switch (transform) {
            case RESIZE:
                return resizeImage(image, transformX, transformY);

            case CLIP:
                return clipImage(image, transformX, transformY);

            default:
                return image;
        }
    }

    private BufferedImage resizeImage(BufferedImage image, short maxX, short maxY) {
        // Calculate the dimensions
        short largeSizeX = (short) image.getWidth();
        short largeSizeY = (short) image.getHeight();

        double aspect = (double) largeSizeX / largeSizeY;

        if (maxX == 0) {
            maxX = (short) 32767;
        }
        if (maxY == 0) {
            maxY = (short) 32767;
        }

        short smallSizeX;
        short smallSizeY;
        if (largeSizeX > maxX || largeSizeY > maxY) {
            smallSizeX = maxX;
            smallSizeY = (short) (smallSizeX / aspect);
            if (smallSizeY > maxY) {
                smallSizeY = maxY;
                smallSizeX = (short) (smallSizeY * aspect);
            }
        } else {
            smallSizeX = largeSizeX;
            smallSizeY = largeSizeY;
        }
        double scale = (double) smallSizeX / largeSizeX;

        // Resize the image
        BufferedImage smallImage = new BufferedImage(smallSizeX, smallSizeY, BufferedImage.TYPE_INT_RGB);
        AffineTransform affine = new AffineTransform();
        affine.scale(scale, scale);
        smallImage.createGraphics().drawImage(
                image, new AffineTransformOp(affine, AffineTransformOp.TYPE_BICUBIC), 0, 0);

        return smallImage;
    }

    private BufferedImage clipImage(BufferedImage image, short clipX, short clipY) {
        // Calculate the dimensions
        short largeSizeX = (short) image.getWidth();
        short largeSizeY = (short) image.getHeight();

        double aspect = (double) largeSizeX / largeSizeY;

        short smallSizeX;
        short smallSizeY;
        if (clipX > 0 || clipY > 0) {
            smallSizeX = clipX;
            smallSizeY = (short) (clipX / aspect);
            if (smallSizeY < clipY) {
                smallSizeY = clipY;
                smallSizeX = (short) (clipY * aspect);
            }
        } else {
            smallSizeX = largeSizeX;
            smallSizeY = largeSizeY;
        }

        short smallX = (short) ((smallSizeX - clipX) / 2);
        short smallY = (short) ((smallSizeY - clipY) / 2);
        double scale = (double) largeSizeX / smallSizeX;
        short largeX = (short) (smallX * scale);
        short largeY = (short) (smallY * scale);
        short largeClipX = (short) (clipX * scale);
        short largeClipY = (short) (clipY * scale);

        // Resize the image
        BufferedImage smallImage = new BufferedImage(smallSizeX, smallSizeY, BufferedImage.TYPE_INT_RGB);
        smallImage.createGraphics().drawImage(image,
                0, 0, clipX, clipY,
                largeX, largeY, largeClipX, largeClipY, null);

        return smallImage;
    }

}