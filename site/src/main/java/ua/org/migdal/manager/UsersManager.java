package ua.org.migdal.manager;

import javax.persistence.EntityManager;
import javax.persistence.PersistenceContext;

import org.springframework.data.jpa.repository.Query;
import ua.org.migdal.data.User;
import ua.org.migdal.data.UserRepository;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.stereotype.Service;
import org.springframework.transaction.annotation.Transactional;

@Service
public class UsersManager {

    @Autowired
    private UserRepository userRepository;

    @PersistenceContext
    private EntityManager entityManager;

    public User getByLogin(String login) {
        return userRepository.findByLogin(login);
    }

    public long getGuestId() {
        return userRepository.findFirstIdByGuestTrueOrderByLogin().getId();
    }

    @Transactional
    public void registerUser(User user) {
        userRepository.save(user);
        entityManager.flush();
    }

}
