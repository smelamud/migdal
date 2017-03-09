package ua.org.migdal.data;

import java.sql.Timestamp;
import java.util.Set;

import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.data.jpa.repository.Modifying;
import org.springframework.data.jpa.repository.Query;

public interface UserRepository extends JpaRepository<User, Long> {

    User findByLogin(String login);

    IdProjection findIdByLogin(String login);

    IdProjection findFirstIdByGuestTrueOrderByLogin();

    @Modifying
    @Query("update User u set u.lastOnline=?2 where id=?1")
    void updateLastOnline(long id, Timestamp lastOnline);

    @Query(value = "select group_id from groups where user_id=?1", nativeQuery = true)
    Set<Long> findGroupIdsByUserId(long id);

    @Query(value = "select * from users where (rights & ?1)<>0", nativeQuery = true)
    Set<User> findAdmins(long right);

}