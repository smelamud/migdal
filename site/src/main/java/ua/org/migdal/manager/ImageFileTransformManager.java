package ua.org.migdal.manager;

import javax.inject.Inject;

import org.springframework.stereotype.Service;

import ua.org.migdal.data.ImageFileTransform;
import ua.org.migdal.data.ImageFileTransformRepository;
import ua.org.migdal.data.ImageFileTransformType;

@Service
public class ImageFileTransformManager {

    @Inject
    private ImageFileTransformRepository imageFileTransformRepository;

    public ImageFileTransform getBySource(long sourceId, ImageFileTransformType transform,
                                          short transformX, short transformY) {
        return imageFileTransformRepository.findBySource(sourceId, transform, transformX, transformY);
    }

    public ImageFileTransform getByResult(long sourceId, ImageFileTransformType transform,
                                          short sizeX, short sizeY) {
        return imageFileTransformRepository.findByResult(sourceId, transform, sizeX, sizeY);
    }

    public void save(ImageFileTransform transform) {
        imageFileTransformRepository.save(transform);
    }

}