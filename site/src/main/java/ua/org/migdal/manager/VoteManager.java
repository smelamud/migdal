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
        Vote vote = findVote(voteType, entry.getId());
        if (vote != null) {
            return false;
        }
        if (voteType.getExpirationPeriod(requestContext.getUser()) > 0) {
            if (requestContext.isLogged()) {
                // Do not store IP for registered users, because we don't want to prevent unregistered
                // users from the same IP from voting.
                vote = new Vote(voteType, entry, null, requestContext.getUser(), voteAmount);
            } else {
                vote = new Vote(voteType, entry, requestContext.getIp(), null, voteAmount);
            }
            voteRepository.save(vote);
        }
        voteType.castVote(entry, voteAmount, requestContext.getUser());
        return true;
    }

}