package ua.org.migdal.manager;

import java.sql.Timestamp;
import java.util.ArrayList;
import java.util.Collections;
import java.util.List;

import org.springframework.data.domain.Sort;
import org.springframework.data.util.Pair;

import ua.org.migdal.data.PostingModbit;
import ua.org.migdal.grp.GrpEnum;

public class Postings {

    List<Pair<Long, Boolean>> topicRoots;
    long[] grps;
    Long upId;
    Long index0;
    boolean afterIndex0;
    Long index1;
    boolean atIndex1 = true;
    boolean afterIndex1;
    Long userId;
    PostingModbit modbit;
    boolean asGuest;
    Timestamp earlierThan;
    Timestamp laterThan;
    boolean withAnswers;
    int offset;
    int limit = Integer.MAX_VALUE;
    Sort.Direction sortDirection = Sort.Direction.DESC;
    String[] sortFields = new String[] { "sent" };

    public static Postings all() {
        return new Postings();
    }

    public Postings topics(List<Pair<Long, Boolean>> topicRoots) {
        if (topicRoots != null) {
            this.topicRoots = topicRoots;
        }
        return this;
    }

    public Postings topic(Long topicId, boolean recursive) {
        if (topicId == null) {
            return this;
        }
        if (topicRoots == null) {
            topicRoots = Collections.singletonList(Pair.of(topicId, recursive));
            return this;
        }
        if (topicRoots.size() == 1) {
            List<Pair<Long, Boolean>> roots = new ArrayList<>(topicRoots);
            roots.add(Pair.of(topicId, recursive));
            topicRoots = roots;
            return this;
        }
        topicRoots.add(Pair.of(topicId, recursive));
        return this;
    }

    public Postings topic(Long topicId) {
        return topic(topicId, false);
    }

    public Postings grp(long[] grps) {
        if (grps != null) {
            this.grps = grps;
        }
        return this;
    }

    public Postings grp(String grpName) {
        return grp(GrpEnum.getInstance().group(grpName));
    }

    public Postings up(Long upId) {
        if (upId != null) {
            this.upId = upId;
        }
        return this;
    }

    public Postings index0(Long index0, boolean afterIndex0) {
        if (index0 != null) {
            this.index0 = index0;
            this.afterIndex0 = afterIndex0;
        }
        return this;
    }

    public Postings beforeIndex0(Long index0) {
        return index0(index0, false);
    }

    public Postings afterIndex0(Long index0) {
        return index0(index0, true);
    }

    public Postings index1(Long index1) {
        if (index1 != null) {
            this.index1 = index1;
        }
        return this;
    }

    public Postings index1(Long index1, boolean afterIndex1) {
        if (index1 != null) {
            this.index1 = index1;
            atIndex1 = false;
            this.afterIndex1 = afterIndex1;
        }
        return this;
    }

    public Postings user(Long userId) {
        if (userId != null) {
            this.userId = userId;
        }
        return this;
    }

    public Postings modbit(PostingModbit modbit) {
        if (modbit != null) {
            this.modbit = modbit;
        }
        return this;
    }

    public Postings asGuest(Boolean asGuest) {
        if (asGuest != null) {
            this.asGuest = asGuest;
        }
        return this;
    }

    public Postings asGuest() {
        return asGuest(true);
    }

    public Postings earlierThan(Timestamp earlierThan) {
        if (earlierThan != null) {
            this.earlierThan = earlierThan;
        }
        return this;
    }

    public Postings laterThan(Timestamp laterThan) {
        if (laterThan != null) {
            this.laterThan = laterThan;
        }
        return this;
    }

    public Postings withAnswers(Boolean withAnswers) {
        if (withAnswers != null) {
            this.withAnswers = withAnswers;
        }
        return this;
    }

    public Postings withAnswers() {
        return withAnswers(true);
    }

    public Postings offset(Integer offset) {
        if (offset != null) {
            this.offset = offset;
        }
        return this;
    }

    public Postings limit(Integer limit) {
        if (limit != null) {
            this.limit = limit;
        }
        return this;
    }

    public Postings page(Integer offset, Integer limit) {
        return offset(offset).limit(limit);
    }

    public Postings sort(Sort.Direction sortDirection, String... sortFields) {
        this.sortDirection = sortDirection;
        this.sortFields = sortFields;
        return this;
    }

    public Postings sort(boolean asc, String... sortFields) {
        return sort(asc ? Sort.Direction.ASC : Sort.Direction.DESC, sortFields);
    }

}
