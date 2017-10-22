package ua.org.migdal.manager;

import javax.inject.Inject;

import org.springframework.stereotype.Service;
import ua.org.migdal.data.CrossEntry;
import ua.org.migdal.data.CrossEntryRepository;
import ua.org.migdal.data.LinkType;

@Service
public class CrossEntryManager {

    @Inject
    private CrossEntryRepository crossEntryRepository;

    public void save(CrossEntry crossEntry) {
        crossEntryRepository.save(crossEntry);
    }

    public void delete(CrossEntry crossEntry) {
        crossEntryRepository.delete(crossEntry);
    }

    public CrossEntry get(LinkType linkType, long peerId) {
        return crossEntryRepository.findByLinkTypeAndPeerId(linkType, peerId);
    }

}