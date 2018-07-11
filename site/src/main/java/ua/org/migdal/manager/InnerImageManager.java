package ua.org.migdal.manager;

import java.util.List;

import javax.inject.Inject;

import org.springframework.stereotype.Service;
import ua.org.migdal.data.InnerImage;
import ua.org.migdal.data.InnerImageRepository;

@Service
public class InnerImageManager {

    @Inject
    private InnerImageRepository innerImageRepository;

    public List<InnerImage> getAll(long entryId) {
        return innerImageRepository.findByEntryId(entryId);
    }

}
