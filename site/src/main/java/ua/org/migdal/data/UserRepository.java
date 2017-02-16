package ua.org.migdal.data;

import org.springframework.data.jpa.repository.Query;
import org.springframework.data.repository.CrudRepository;

public interface UserRepository extends CrudRepository<User, Long> {

    User findByLogin(String login);

    @Query("select user.id from User user where user.guest = true order by user.login")
    long getGuestId();

}
