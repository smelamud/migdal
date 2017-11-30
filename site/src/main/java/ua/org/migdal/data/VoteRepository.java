package ua.org.migdal.data;

import org.springframework.data.jpa.repository.JpaRepository;

public interface VoteRepository extends JpaRepository<Vote, Long> {

    Vote findByVoteTypeAndEntryIdAndIp(VoteType voteType, long entryId, String ip);

    Vote findByVoteTypeAndEntryIdAndUserId(VoteType voteType, long entryId, long userId);

}
