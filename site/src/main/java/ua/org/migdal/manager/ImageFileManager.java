package ua.org.migdal.manager;

import javax.inject.Inject;

import org.springframework.stereotype.Service;
import ua.org.migdal.data.ImageFile;
import ua.org.migdal.data.ImageFileRepository;

@Service
public class ImageFileManager {

    @Inject
    private ImageFileRepository imageFileRepository;

    public ImageFile get(Long id) {
        return imageFileRepository.findById(id).orElse(null);
    }

    public void save(ImageFile imageFile) {
        imageFileRepository.save(imageFile);
    }

}