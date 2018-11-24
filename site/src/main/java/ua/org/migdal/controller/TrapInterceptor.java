package ua.org.migdal.controller;

import javax.inject.Inject;
import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpServletResponse;

import org.springframework.stereotype.Component;
import org.springframework.web.servlet.handler.HandlerInterceptorAdapter;
import org.springframework.web.util.UriComponentsBuilder;

import ua.org.migdal.controller.exception.PageNotFoundException;
import ua.org.migdal.manager.EntryManager;
import ua.org.migdal.manager.OldIdManager;
import ua.org.migdal.manager.PostingManager;
import ua.org.migdal.manager.TopicManager;
import ua.org.migdal.manager.UserManager;
import ua.org.migdal.util.UriUtils;

@Component
public class TrapInterceptor extends HandlerInterceptorAdapter {

    @Inject
    private OldIdManager oldIdManager;

    @Inject
    private TopicManager topicManager;

    @Inject
    private PostingManager postingManager;

    @Inject
    private EntryManager entryManager;

    @Inject
    private UserManager userManager;

    @Override
    public boolean preHandle(HttpServletRequest request, HttpServletResponse response, Object handler) throws Exception {
        UriComponentsBuilder builder = UriUtils.createLocalBuilderFromRequest(request);
        if (!TrapHandler.fallsUnder(builder)) {
            return true;
        }
        TrapHandler trapHandler = new TrapHandler(builder.cloneBuilder());
        trapHandler.setOldIdManager(oldIdManager);
        trapHandler.setTopicManager(topicManager);
        trapHandler.setPostingManager(postingManager);
        trapHandler.setEntryManager(entryManager);
        trapHandler.setUserManager(userManager);
        String redirectTo = trapHandler.trap();
        if (redirectTo == null) {
            throw new PageNotFoundException();
        }
        response.sendRedirect(redirectTo);
        return false;
    }

}
