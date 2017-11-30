package ua.org.migdal.data;

public enum VoteType {

    VOTE(3, 720) {

        @Override
        public void castVote(Entry entry, int voteAmount, User user) {
            int weight;
            if (voteAmount > VoteSettings.VOTE_ZERO) {
                weight = user == null
                        ? VoteSettings.GUEST_VOTE_WEIGHT
                        : user.isModerator() ? VoteSettings.MODERATOR_VOTE_WEIGHT : VoteSettings.USER_VOTE_WEIGHT;
            } else {
                weight = 1;
            }

            entry.setVote(entry.getVote() + weight * voteAmount);
            entry.setVoteCount(entry.getVoteCount() + weight);
            entry.setRating(entry.getVote() - VoteSettings.VOTE_ZERO * entry.getVoteCount());
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