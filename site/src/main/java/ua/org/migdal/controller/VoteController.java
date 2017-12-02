package ua.org.migdal.controller;

import java.util.List;

import javax.inject.Inject;

import org.springframework.stereotype.Controller;
import org.springframework.transaction.PlatformTransactionManager;
import org.springframework.validation.Errors;
import org.springframework.web.bind.annotation.PathVariable;
import org.springframework.web.bind.annotation.PostMapping;
import org.springframework.web.bind.annotation.RequestParam;
import org.springframework.web.bind.annotation.ResponseBody;
import ua.org.migdal.data.Posting;
import ua.org.migdal.data.VoteSettings;
import ua.org.migdal.data.VoteType;
import ua.org.migdal.data.api.PostingVote;
import ua.org.migdal.manager.ErrorMessageResolver;
import ua.org.migdal.manager.PostingManager;
import ua.org.migdal.manager.VoteManager;
import ua.org.migdal.session.RequestContext;
import ua.org.migdal.util.NoObjectErrors;

@Controller
public class VoteController {

    @Inject
    private PlatformTransactionManager txManager;

    @Inject
    private RequestContext requestContext;

    @Inject
    private PostingManager postingManager;

    @Inject
    private VoteManager voteManager;

    @Inject
    private ErrorMessageResolver errorMessageResolver;

    @PostMapping("/api/posting/{id}/vote")
    @ResponseBody
    public PostingVote actionPostingVote(@PathVariable long id, @RequestParam int vote) {
        PostingVote postingVote = new PostingVote();
        Errors errors = new NoObjectErrors("postingVoteApi");
        new ControllerAction(EntryController.class, "actionPostingVote", errors)
                .transactional(txManager)
                .execute(() -> {
                    Posting posting = postingManager.beg(id);
                    if (posting == null) {
                        return "noPosting";
                    }
                    if (vote < VoteSettings.VOTE_MIN || vote > VoteSettings.VOTE_MAX) {
                        return "voteOutOfRange";
                    }
                    if (vote < VoteSettings.VOTE_ZERO && !requestContext.isLogged()) {
                        return "noLogin";
                    }

                    if (!voteManager.vote(posting, VoteType.VOTE, vote)) {
                        return "alreadyVoted";
                    }
                    postingVote.setId(posting.getId());
                    postingVote.setVote(vote);
                    postingVote.setRating((long) posting.getRating());
                    return null;
                });
        List<String> messages = errorMessageResolver.createContext().getMessages(errors);
        if (messages.size() > 0) {
            postingVote.setError(messages.get(0));
        }
        return postingVote;
    }

}
