package ua.org.migdal.data;

import java.sql.Timestamp;

import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.data.jpa.repository.Modifying;
import org.springframework.data.jpa.repository.Query;

public interface UserRepository extends JpaRepository<User, Long> {

    User findByLogin(String login);

    IdProjection findFirstIdByGuestTrueOrderByLogin();

    @Modifying
    @Query("update User user set user.lastOnline=?2 where id=?1")
    void updateLastOnline(long id, Timestamp lastOnline);

}
