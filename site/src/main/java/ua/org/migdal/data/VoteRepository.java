package ua.org.migdal.data;

import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.data.jpa.repository.Query;

public interface VoteRepository extends JpaRepository<Vote, Long> {

    @Query("from Vote v where v.voteType=?1 and v.entry.id=?2 and (v.ip=?3 or v.user.id=?4)")
    Vote findVote(VoteType voteType, long entryId, String ip, long userId);

}