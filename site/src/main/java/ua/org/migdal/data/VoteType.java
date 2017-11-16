package ua.org.migdal.data;

public enum VoteType {

    VOTE(3, 720) {

        private static final int VOTE_ZERO = 3;
        private static final int MODERATOR_VOTE_WEIGHT = 3;
        private static final int USER_VOTE_WEIGHT = 2;
        private static final int GUEST_VOTE_WEIGHT = 1;

        @Override
        public void castVote(Entry entry, int voteAmount, User user) {
            int weight;
            if (voteAmount > VOTE_ZERO) {
                weight = user == null
                        ? GUEST_VOTE_WEIGHT
                        : user.isModerator() ? MODERATOR_VOTE_WEIGHT : USER_VOTE_WEIGHT;
            } else {
                weight = 1;
            }

            entry.setVote(entry.getVote() + weight * voteAmount);
            entry.setVoteCount(entry.getVoteCount() + weight);
            entry.setRating(entry.getVote() - VOTE_ZERO * entry.getVoteCount());
        }

    },

    CLICK(0, 0) {

        @Override
        public void castVote(Entry entry, int voteAmount, User user) {
            entry.setCounter3(entry.getCounter3() + voteAmount);
        }

    },

    VIEW(0, 0) {

        @Override
        public void castVote(Entry entry, int voteAmount, User user) {
            entry.setCounter2(entry.getCounter2() + voteAmount);
        }

    };

    private int guestExpirationPeriod;
    private int userExpirationPeriod;

    VoteType(int guestExpirationPeriod, int userExpirationPeriod) {
        this.guestExpirationPeriod = guestExpirationPeriod;
        this.userExpirationPeriod = userExpirationPeriod;
    }

    public int getGuestExpirationPeriod() {
        return guestExpirationPeriod;
    }

    public int getUserExpirationPeriod() {
        return userExpirationPeriod;
    }

    public int getExpirationPeriod(User user) {
        return user != null ? getUserExpirationPeriod() : getGuestExpirationPeriod();
    }

    public abstract void castVote(Entry entry, int voteAmount, User user);

}