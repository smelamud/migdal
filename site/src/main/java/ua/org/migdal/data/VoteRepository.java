package ua.org.migdal.data;

import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.data.jpa.repository.Modifying;
import org.springframework.data.jpa.repository.Query;

public interface VoteRepository extends JpaRepository<Vote, Long> {

    Vote findByVoteTypeAndEntryIdAndIp(VoteType voteType, long entryId, String ip);

    Vote findByVoteTypeAndEntryIdAndUserId(VoteType voteType, long entryId, long userId);

    @Modifying
    @Query("delete from Vote v where v.expires < now()")
    void deleteExpiredVotes();

}
