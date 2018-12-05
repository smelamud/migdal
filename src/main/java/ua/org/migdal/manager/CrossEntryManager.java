package ua.org.migdal.manager;

import java.util.List;
import java.util.stream.Collectors;
import javax.inject.Inject;

import org.springframework.stereotype.Service;
import ua.org.migdal.data.CrossEntry;
import ua.org.migdal.data.CrossEntryRepository;
import ua.org.migdal.data.LinkType;
import ua.org.migdal.data.Posting;

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

    public CrossEntry get(long id) {
        return crossEntryRepository.getOne(id);
    }

    public CrossEntry get(LinkType linkType, long peerId) {
        return crossEntryRepository.findByLinkTypeAndPeerId(linkType, peerId);
    }

    public List<CrossEntry> getAll(LinkType linkType, String sourceName) {
        return crossEntryRepository.findAllByLinkTypeAndSourceNameOrderByPeerSubject(linkType, sourceName);
    }

    public List<CrossEntry> getAll(LinkType linkType, long sourceId) {
        return crossEntryRepository.findAllByLinkTypeAndSourceIdOrderByPeerSubject(linkType, sourceId);
    }

    public void fetchPublishedEntries(Posting posting) {
        if (posting.isGrpPublisher()) {
            posting.setPublishedEntries(
                    getAll(LinkType.PUBLISH, posting.getId()).stream()
                            .map(CrossEntry::getPeer)
                            .collect(Collectors.toList()));
        }
    }

}