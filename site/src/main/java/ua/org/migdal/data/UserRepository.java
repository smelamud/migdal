package ua.org.migdal.data;

import java.math.BigInteger;
import java.sql.Timestamp;
import java.util.List;
import java.util.Set;

import org.springframework.cache.annotation.CacheEvict;
import org.springframework.cache.annotation.Cacheable;
import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.data.jpa.repository.Modifying;
import org.springframework.data.jpa.repository.Query;
import org.springframework.data.querydsl.QueryDslPredicateExecutor;

public interface UserRepository extends JpaRepository<User, Long>, QueryDslPredicateExecutor<User> {

    @CacheEvict(cacheNames="users-id", allEntries=true)
    @Override
    <S extends User> S save(S s);

    User findByIdAndHiddenLessThan(long id, short hidden);

    User findByLogin(String login);

    @Cacheable(value="users-id", unless="#result == null")
    IdProjection findIdByLogin(String login);

    int countByLogin(String login);

    @Cacheable(value="users-guestid", unless="#result == null")
    IdProjection findFirstIdByGuestTrueOrderByLogin();

    @Modifying
    @Query("update User u set u.lastOnline=?2 where id=?1")
    void updateLastOnline(long id, Timestamp lastOnline);

    @Query(value = "select group_id from groups where user_id=?1", nativeQuery = true)
    Set<BigInteger> findGroupIdsByUserId(long id);

    @Query(value = "select * from users where (rights & ?1)<>0", nativeQuery = true)
    Set<User> findAdmins(long right);

    User findByConfirmCodeAndHiddenLessThan(String confirmCode, short hidden);

    int countByConfirmDeadlineNotNull();

    @Query("select distinct g.id, g.login, u.id, u.login from User g inner join g.users u order by g.login, u.login")
    List<Object[]> findGroupsAndUsersOrderByLogin();

}