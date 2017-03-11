package ua.org.migdal.manager;

import java.sql.Timestamp;
import java.util.Set;

import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.stereotype.Service;
import org.springframework.transaction.annotation.Transactional;

import ua.org.migdal.Config;
import ua.org.migdal.data.IdProjection;
import ua.org.migdal.data.User;
import ua.org.migdal.data.UserRepository;
import ua.org.migdal.data.UserRight;
import ua.org.migdal.util.CachedValue;
import ua.org.migdal.util.Utils;

@Service
public class UserManager {

    @Autowired
    private Config config;

    @Autowired
    private UserRepository userRepository;

    private final CachedValue<Long> guestId = new CachedValue<>(this::fetchGuestId);

    public boolean exists(Long id) {
        return userRepository.exists(id);
    }

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

    public boolean loginExists(String login) {
        return userRepository.countByLogin(login) > 0;
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
        return data != null ? data.getId() : addGuest();
    }

    @Transactional
    private long addGuest() {
        User user = new User();
        user.setLogin(config.getGuestLogin());
        user.setEmailDisabled((short) 1);
        user.setGuest(true);
        user.setHidden((short) 2);
        user.setNoLogin(true);
        user.setCreated(Utils.now());
        user.setModified(Utils.now());
        userRepository.save(user);
        return user.getId();
    }

    @Transactional
    public void save(User user) {
        userRepository.save(user);
    }

    @Transactional
    public void updateLastOnline(long id, Timestamp lastOnline) {
        userRepository.updateLastOnline(id, lastOnline);
    }

    public Set<Long> getGroupIdsByUserId(long id) {
        return userRepository.findGroupIdsByUserId(id);
    }

    public Set<User> getAdmins(UserRight right) {
        return userRepository.findAdmins(right.getValue());
    }

    public User begByConfirmCode(String confirmCode) {
        return userRepository.findByConfirmCodeAndHiddenLessThan(confirmCode, (short) 2);
    }

    @Transactional
    public void confirm(User user) {
        user.setNoLogin(false);
        user.setHidden((short) 0);
        user.setConfirmDeadline(null);
        user.setLastOnline(Utils.now());
        userRepository.save(user);
    }

}
