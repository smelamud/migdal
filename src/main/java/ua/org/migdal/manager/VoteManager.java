package ua.org.migdal.manager;

import javax.inject.Inject;

import org.springframework.stereotype.Service;

import ua.org.migdal.data.Entry;
import ua.org.migdal.data.Vote;
import ua.org.migdal.data.VoteRepository;
import ua.org.migdal.data.VoteType;
import ua.org.migdal.session.RequestContext;

@Service
public class VoteManager {

    @Inject
    private RequestContext requestContext;

    @Inject
    private VoteRepository voteRepository;

    public Vote findVote(VoteType voteType, long entryId) {
        if (requestContext.isLogged()) {
            return voteRepository.findByVoteTypeAndEntryIdAndUserId(voteType, entryId, requestContext.getUserId());
        } else {
            return voteRepository.findByVoteTypeAndEntryIdAndIp(voteType, entryId, requestContext.getIp());
        }
    }

    public boolean vote(Entry entry, VoteType voteType, int voteAmount) {
        Entry unique = voteType.isParentUnique() ? entry.getParent() : entry;

        Vote vote = findVote(voteType, unique.getId());
        if (vote != null) {
            return false;
        }
        if (voteType.getExpirationPeriod(requestContext.getUser()) > 0) {
            if (requestContext.isLogged()) {
                // Do not store IP for registered users, because we don't want to prevent unregistered
                // users from the same IP from voting.
                vote = new Vote(voteType, unique, null, requestContext.getUser(), voteAmount);
            } else {
                vote = new Vote(voteType, unique, requestContext.getIp(), null, voteAmount);
            }
            voteRepository.save(vote);
        }
        voteType.castVote(entry, voteAmount, requestContext.getUser());
        return true;
    }

    @Hourly
    public void deleteExpired() {
        voteRepository.deleteExpired();
    }

}