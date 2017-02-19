package ua.org.migdal.data;

import org.springframework.data.jpa.repository.JpaRepository;

public interface UserRepository extends JpaRepository<User, Long> {

    User findByLogin(String login);

    IdProjection findFirstIdByGuestTrueOrderByLogin();

}
