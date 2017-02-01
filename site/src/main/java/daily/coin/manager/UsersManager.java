package daily.coin.manager;

import javax.persistence.EntityManager;
import javax.persistence.PersistenceContext;

import daily.coin.data.User;
import daily.coin.data.UserRepository;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.stereotype.Service;
import org.springframework.transaction.annotation.Transactional;

@Service
public class UsersManager {

    @Autowired
    private UserRepository userRepository;

    @PersistenceContext
    private EntityManager entityManager;

    @Transactional
    public void registerUser(User user) {
        userRepository.save(user);
        entityManager.flush();
    }

}
