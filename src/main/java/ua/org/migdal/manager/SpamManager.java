package ua.org.migdal.manager;

import java.io.BufferedReader;
import java.io.IOException;
import java.io.InputStreamReader;
import java.util.ArrayList;
import java.util.List;
import java.util.regex.Pattern;

import javax.annotation.PostConstruct;
import javax.inject.Inject;

import org.springframework.context.ApplicationContext;
import org.springframework.stereotype.Service;
import org.springframework.util.StringUtils;
import ua.org.migdal.data.Posting;
import ua.org.migdal.session.RequestContext;

@Service
public class SpamManager {

    @Inject
    private ApplicationContext applicationContext;

    @Inject
    private RequestContext requestContext;

    private List<Pattern> spamWords = new ArrayList<>();
    
    @PostConstruct
    public void init() throws IOException {
        BufferedReader in = new BufferedReader(new InputStreamReader(
                applicationContext.getResource("classpath:spam-words.txt").getInputStream()));
        String line;
        while ((line = in.readLine()) != null) {
            if (StringUtils.isEmpty(line)) {
                continue;
            }
            spamWords.add(Pattern.compile(line, Pattern.CASE_INSENSITIVE | Pattern.UNICODE_CASE));
        }
    }

    public boolean isSpam(String text) {
        for (Pattern spamWord : spamWords) {
            if (spamWord.matcher(text).find()) {
                return true;
            }
        }
        return false;
    }

    public boolean containsLinks(String text) {
        return text.contains("<a")
                || text.contains("http:")
                || text.contains("https:")
                || text.contains("bit.ly/");
    }

    public boolean needsAttention(Posting posting) {
        return !requestContext.isUserModerator()
                && (containsLinks(posting.getBody())
                        || containsLinks(posting.getSource())
                        || !StringUtils.isEmpty(posting.getUrl()));
    }

}