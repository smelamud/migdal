package ua.org.migdal.manager;

import java.sql.Timestamp;
import java.time.Instant;
import java.time.temporal.ChronoUnit;
import java.util.function.Consumer;
import javax.inject.Inject;

import org.springframework.stereotype.Service;

import ua.org.migdal.Config;
import ua.org.migdal.data.EntryType;
import ua.org.migdal.data.Image;
import ua.org.migdal.data.ImageRepository;
import ua.org.migdal.session.RequestContext;
import ua.org.migdal.util.CatalogUtils;
import ua.org.migdal.util.TrackUtils;
import ua.org.migdal.util.Utils;

@Service
public class ImageManager implements EntryManagerBase<Image> {

    @Inject
    private Config config;

    @Inject
    private RequestContext requestContext;

    @Inject
    private ImageRepository imageRepository;

    @Inject
    private TrackManager trackManager;

    @Inject
    private CatalogManager catalogManager;

    public Image get(long id) {
        return imageRepository.findById(id).orElse(null);
    }

    @Override
    public Image beg(long id) {
        return get(id);
    }

    @Override
    public void save(Image image) {
        if (image.getId() <= 0) {
            image.setCreator(requestContext.getUser());
            image.setCreated(Utils.now());
        }
        image.setModifier(requestContext.getUser());
        image.setModified(Utils.now());
        imageRepository.save(image);
    }

    public void saveAndFlush(Image image) {
        if (image.getId() <= 0) {
            image.setCreator(requestContext.getUser());
            image.setCreated(Utils.now());
        }
        image.setModifier(requestContext.getUser());
        image.setModified(Utils.now());
        imageRepository.saveAndFlush(image);
    }

    public void store(
            Image image,
            Consumer<Image> applyChanges,
            boolean newForum,
            boolean trackChanged,
            boolean catalogChanged) {

        String oldTrack = image.getTrack();
        if (applyChanges != null) {
            applyChanges.accept(image);
        }
        saveAndFlush(image); /* We need to have the record in DB to know ID after this point */

        String newTrack = TrackUtils.track(image.getId(), image.getUp().getTrack());
        if (newForum) {
            trackManager.setTrackById(image.getId(), newTrack);
            String newCatalog = CatalogUtils.catalog(EntryType.FORUM, image.getId(), "", 0, image.getUp().getCatalog());
            catalogManager.setCatalogById(image.getId(), newCatalog);
        }
        if (trackChanged) {
            trackManager.replaceTracks(oldTrack, newTrack);
        }
        if (catalogChanged) {
            catalogManager.updateCatalogs(newTrack);
        }
    }

    public void delete(long id) {
        imageRepository.deleteById(id);
    }

    @Daily
    public void deleteObsolete() {
        imageRepository.deleteObsolete(
                Timestamp.from(Instant.now().minus(config.getInnerImageTimeout(), ChronoUnit.DAYS)));
    }

}
