package ua.org.migdal.manager;

import java.sql.Timestamp;
import java.util.Set;
import javax.persistence.EntityManager;
import javax.persistence.PersistenceContext;

import ua.org.migdal.Config;
import ua.org.migdal.data.IdProjection;
import ua.org.migdal.data.User;
import ua.org.migdal.data.UserRepository;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.stereotype.Service;
import org.springframework.transaction.annotation.Transactional;
import ua.org.migdal.util.CachedValue;
import ua.org.migdal.util.Utils;

@Service
public class UsersManager {

    @Autowired
    private Config config;

    @Autowired
    private UserRepository userRepository;

    @PersistenceContext
    private EntityManager entityManager;

    private final CachedValue<Long> guestId = new CachedValue<>(this::fetchGuestId);

    public User get(Long id) {
        return userRepository.findOne(id);
    }

    public User getByLogin(String login) {
        return userRepository.findByLogin(login);
    }

    public long getIdByLogin(String login) {
        IdProjection idp = userRepository.findIdByLogin(login);  // TODO add caching
        return idp != null ? idp.getId() : 0;
    }

    public long idOrLogin(String login) {
        return Utils.idOrName(login, this::getIdByLogin);
    }

    public long getGuestId() {
        if (!config.isAllowGuests()) {
            return 0;
        }
        return guestId.get();
    }

    private Long fetchGuestId() {
        IdProjection data = userRepository.findFirstIdByGuestTrueOrderByLogin();
        return data != null ? data.getId() : addGuestUser();
    }

    @Transactional
    private long addGuestUser() {
        User user = new User();
        user.setLogin(config.getGuestLogin());
        user.setEmailDisabled(true);
        user.setGuest(true);
        user.setHidden((short) 2);
        user.setNoLogin(true);
        user.setCreated(new Timestamp(System.currentTimeMillis()));
        user.setModified(new Timestamp(System.currentTimeMillis()));
        userRepository.save(user);
        return user.getId();
    }

    @Transactional
    public void registerUser(User user) {
        userRepository.save(user);
//        entityManager.flush();
    }

    @Transactional
    public void updateLastOnline(long id, Timestamp lastOnline) {
        userRepository.updateLastOnline(id, lastOnline);
    }

    public Set<Long> getGroupIdsByUserId(long id) {
        return userRepository.findGroupIdsByUserId(id);
    }

}
