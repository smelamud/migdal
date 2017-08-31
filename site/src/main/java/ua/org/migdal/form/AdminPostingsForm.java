package ua.org.migdal.form;

import java.io.Serializable;
import java.util.ArrayList;
import java.util.Arrays;
import java.util.List;
import java.util.stream.Collectors;

import org.springframework.data.util.Pair;
import ua.org.migdal.grp.GrpEnum;

public class AdminPostingsForm implements Serializable {

    private static final long serialVersionUID = 6852165340260083018L;

    public static class TopicChoice {

        private int n;
        private long topicId;
        private boolean recursive;

        public TopicChoice(int n, long topicId, boolean recursive) {
            this.n = n;
            this.topicId = topicId;
            this.recursive = recursive;
        }

        public int getN() {
            return n;
        }

        public void setN(int n) {
            this.n = n;
        }

        public long getTopicId() {
            return topicId;
        }

        public void setTopicId(long topicId) {
            this.topicId = topicId;
        }

        public boolean isRecursive() {
            return recursive;
        }

        public void setRecursive(boolean recursive) {
            this.recursive = recursive;
        }

    }

    private long[] topicIds = new long[0];
    private int[] recursive = new int[0];
    private long[] grps = new long[0];
    private boolean useIndex1;
    private long index1;

    public AdminPostingsForm() {
        grps = GrpEnum.getInstance().group("TAPE");
    }

    public long[] getTopicIds() {
        return topicIds;
    }

    public void setTopicIds(long[] topicIds) {
        this.topicIds = topicIds;
    }

    public int[] getRecursive() {
        return recursive;
    }

    public void setRecursive(int[] recursive) {
        this.recursive = recursive;
        Arrays.sort(this.recursive);
    }

    public List<TopicChoice> getTopicChoices() {
        List<TopicChoice> choices = new ArrayList<>();

        if (topicIds != null) {
            for (int i = 0; i < topicIds.length; i++) {
                if (topicIds[i] > 0) {
                    boolean rcv = Arrays.binarySearch(recursive, i) >= 0;
                    choices.add(new TopicChoice(i, topicIds[i], rcv));
                }
            }
        }
        choices.add(new TopicChoice(topicIds != null ? topicIds.length : 0, 0, true));

        return choices;
    }

    public List<Pair<Long, Boolean>> getTopicRoots() {
        return getTopicChoices().stream()
                .map(choice -> Pair.of(choice.getTopicId(), choice.isRecursive()))
                .collect(Collectors.toList());
    }

    public long[] getGrps() {
        return grps;
    }

    public void setGrps(long[] grps) {
        this.grps = grps;
    }

    public boolean isUseIndex1() {
        return useIndex1;
    }

    public void setUseIndex1(boolean useIndex1) {
        this.useIndex1 = useIndex1;
    }

    public long getIndex1() {
        return index1;
    }

    public void setIndex1(long index1) {
        this.index1 = index1;
    }

}