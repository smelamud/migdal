package ua.org.migdal.data;

import org.springframework.data.repository.CrudRepository;

public interface UserRepository extends CrudRepository<User, Long> {

    User findByLogin(String login);

    IdProjection findFirstIdByGuestTrueOrderByLogin();

}
