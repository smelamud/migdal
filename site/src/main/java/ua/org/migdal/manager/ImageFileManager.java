package ua.org.migdal.manager;

import java.io.IOException;
import java.nio.file.DirectoryStream;
import java.nio.file.FileSystems;
import java.nio.file.Files;
import java.nio.file.Path;
import java.time.Instant;
import java.time.temporal.ChronoUnit;
import java.util.Set;

import javax.inject.Inject;

import org.apache.commons.logging.Log;
import org.apache.commons.logging.LogFactory;
import org.springframework.stereotype.Service;

import ua.org.migdal.Config;
import ua.org.migdal.data.ImageFile;
import ua.org.migdal.data.ImageFileRepository;
import ua.org.migdal.util.ImageFileInfo;
import ua.org.migdal.util.ImageFileUtils;

@Service
public class ImageFileManager {

    private static Log log = LogFactory.getLog(ImageFileManager.class.getName());

    @Inject
    private Config config;

    @Inject
    private ImageFileRepository imageFileRepository;

    public ImageFile get(Long id) {
        return imageFileRepository.findById(id).orElse(null);
    }

    public void save(ImageFile imageFile) {
        imageFileRepository.save(imageFile);
    }

    @Daily
    public void deleteObsoleteImageFiles() {
        imageFileRepository.deleteObsoleteImageFiles();
        Set<Long> ids = imageFileRepository.findAllIds();
        Path imageDir = FileSystems.getDefault().getPath(config.getImageDir());
        try (DirectoryStream<Path> stream = Files.newDirectoryStream(imageDir)) {
            for (Path path : stream) {
                ImageFileInfo info = ImageFileUtils.parseFilename(path.toString());
                if (info == null) {
                    continue;
                }
                if (!ids.contains(info.getFileId())
                        && Files.getLastModifiedTime(path)
                                .toInstant()
                                .plus(config.getImageFileTimeout(), ChronoUnit.HOURS)
                                .isBefore(Instant.now())) {
                    log.info("Deleting obsolete image file " + path);
                    Files.delete(path);
                }
            }
        } catch (IOException e) {
            e.printStackTrace();
        }
    }

}