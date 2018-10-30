package ua.org.migdal.data;

import java.util.ArrayList;
import java.util.Arrays;
import java.util.List;
import java.util.stream.Collectors;

import javax.persistence.DiscriminatorValue;
import javax.persistence.Entity;
import javax.persistence.Transient;

import org.hibernate.Hibernate;
import org.springframework.expression.EvaluationContext;
import org.springframework.expression.spel.support.StandardEvaluationContext;
import org.springframework.util.StringUtils;

import ua.org.migdal.Config;
import ua.org.migdal.data.util.AnswersPage;
import ua.org.migdal.data.util.Selected;
import ua.org.migdal.grp.GrpDescriptor;
import ua.org.migdal.grp.GrpEnum;
import ua.org.migdal.session.RequestContext;
import ua.org.migdal.session.RequestContextImpl;
import ua.org.migdal.util.Utils;

@Entity
@DiscriminatorValue("1")
public class Posting extends Entry {

    @Transient
    private EvaluationContext spelEvaluationContext = new StandardEvaluationContext(this);

    @Transient
    private List<Topic> ancestors;

    @Transient
    private List<Entry> publishedEntries;

    public Posting() {
    }

    public Posting(Topic topic) { // used to create dummy postings
        setParent(topic);
    }

    public Posting(long grp, Topic topic, Entry up, long index1, RequestContext requestContext) {
        setGrp(grp);
        setParent(topic);
        setUp(up != null ? up : topic);
        setUser(requestContext.isLogged() ? requestContext.getUser() : requestContext.getRealUser());
        if (up != null && up.getId() != topic.getId()) {
            setGroup(up.getGroup());
            setPerms(up.getPerms());
        } else if (topic != null) {
            setGroup(topic.getGroup());
            setPerms(Config.getInstance().getDefaultPostingPerms());
        } else {
            setPerms(Config.getInstance().getDefaultPostingPerms());
        }
        setIndex1(index1);
        setSent(Utils.now());
    }

    private GrpDescriptor getGrpDescriptor() {
        return GrpEnum.getInstance().grp(getGrp()); // FIXME not null-safe
    }

    @Transient
    public String getGrpName() {
        return getGrpDescriptor().getName();
    }

    @Transient
    public String getGrpTitle() {
        return getGrpDescriptor().getTitle();
    }

    private String getGrpHeading() {
        return getGrpDescriptor().getHeading(spelEvaluationContext);
    }

    @Transient
    public String getGrpGeneralHref() {
        return getGrpDescriptor().getGeneralHref(spelEvaluationContext);
    }

    @Transient
    public String getGrpGeneralTitle() {
        return getGrpDescriptor().getGeneralTitle(spelEvaluationContext);
    }

    @Transient
    public String getGrpDetailsHref() {
        return getGrpDescriptor().getDetailsHref(spelEvaluationContext);
    }

    @Transient
    public String getGrpDetailsTemplate() {
        return getGrpDescriptor().getDetailsTemplate();
    }

    @Transient
    public String getGrpDetailsTopics() {
        return getGrpDescriptor().getDetailsTopics();
    }

    @Transient
    public String getGrpDetailsTopicsIndex() {
        return getGrpDescriptor().getDetailsTopicsIndex();
    }

    @Transient
    public String getGrpPublish() {
        return getGrpDescriptor().getPublishGrp();
    }

    @Transient
    public String getGrpWhat() {
        return getGrpDescriptor().getWhat();
    }

    @Transient
    public String getGrpWhatA() {
        return getGrpDescriptor().getWhatA();
    }

    @Transient
    public String getGrpPartial() {
        return getGrpDescriptor().getPartial();
    }

    @Transient
    public boolean isGrpInnerImages() {
        return getGrpDescriptor().isInnerImages();
    }

    @Transient
    public boolean isGrpPublisher() {
        return getGrpDescriptor().isPublisher();
    }

    @Transient
    public Topic getTopic() {
        return (Topic) getParent();
    }

    @Transient
    public long getTopicId() {
        return getParentId();
    }

    @Transient
    public String getHeading() {
        return getHeading(false);
    }

    @Transient
    public String getHeading(boolean useUrl) {
        String heading = getGrpHeading();
        if (StringUtils.isEmpty(heading)) {
            heading = getSubject();
        }
        if (StringUtils.isEmpty(heading)) {
            heading = getBodyTiny();
        }
        if (StringUtils.isEmpty(heading) && useUrl) {
            heading = getGrpDetailsHref();
        }
        if (StringUtils.isEmpty(heading)) {
            heading = "Без названия";
        }
        return heading;
    }

    public CharSequence getIssues() {
        StringBuilder buf = new StringBuilder();
        buf.append(getIndex1());
        if (getIndex2() > 0) {
            buf.append('-');
            buf.append(getIndex1() + getIndex2());
        }
        return buf;
    }

    public List<Topic> getAncestors() {
        return ancestors;
    }

    public void setAncestors(List<Topic> ancestors) {
        this.ancestors = ancestors;
    }

    public List<Entry> getPublishedEntries() {
        return publishedEntries;
    }

    public void setPublishedEntries(List<Entry> publishedEntries) {
        this.publishedEntries = publishedEntries;
    }

    @Override
    protected boolean isPermitted(long right) {
        RequestContext rc = RequestContextImpl.getInstance();
        return rc.isUserModerator()
               || (!isDisabled() || getUser().getId() == rc.getUserId()) && super.isPermitted(right);
    }

    @Transient
    public List<Selected<PostingModbit>> getModbitsSelection() {
        return Arrays.stream(PostingModbit.values())
                .filter(bit -> !bit.isSpecial())
                .map(bit -> new Selected<>(bit, (getModbits() & bit.getValue()) != 0))
                .collect(Collectors.toList());
    }

    @Transient
    public void setLastAnswerDetails(Forum forum) {
        setLastAnswer(forum);
        if (forum != null) {
            setLastAnswerTimestamp(forum.getSent());
            setLastAnswerUser(forum.getUser());
            setLastAnswerGuestLogin(forum.getGuestLogin());
        } else {
            setLastAnswerTimestamp(getSent());
            setLastAnswerUser(null);
            setLastAnswerGuestLogin("");
        }
    }

    private List<AnswersPage> getAnswersPages(int limit) {
        List<AnswersPage> pages = new ArrayList<>();
        int total = (int) getAnswers();
        int n = total / limit;
        if (total % limit > 0) {
            n++;
        }
        if (n > 1) {
            if (n > 6) {
                for (int offset = 0; offset < 3 * limit; offset += limit) {
                    pages.add(new AnswersPage(offset / limit + 1, offset));
                }
                pages.add(new AnswersPage(true));
                for (int offset = (n - 3) * limit; offset < total; offset += limit) {
                    pages.add(new AnswersPage(offset / limit + 1, offset));
                }
            } else {
                for (int offset = 0; offset < total; offset += limit) {
                    pages.add(new AnswersPage(offset / limit + 1, offset));
                }
            }
        }
        return pages;
    }

    @Transient
    public List<AnswersPage> getAnswersPages() {
        return getAnswersPages(20);
    }

    public static String validateHierarchy(Entry parent, Entry up, long id) {
        String errorCode = Entry.validateHierarchy(parent, up, id);
        if (errorCode != null) {
            return errorCode;
        }
        if (parent == null) {
            return "hierarchyNoParent";
        }
        if (Hibernate.getClass(parent) != Topic.class
                || parent.getId() != up.getId() && Hibernate.getClass(up) != Posting.class) {
            return "hierarchyIncorrect";
        }
        return null;
    }

}