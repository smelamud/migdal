package ua.org.migdal.manager;

import java.sql.Timestamp;
import java.util.Set;

import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.data.domain.Page;
import org.springframework.data.domain.PageRequest;
import org.springframework.data.domain.Sort;
import org.springframework.stereotype.Service;
import org.springframework.transaction.annotation.Transactional;
import org.springframework.util.StringUtils;

import com.querydsl.core.BooleanBuilder;

import ua.org.migdal.Config;
import ua.org.migdal.data.IdProjection;
import ua.org.migdal.data.QUser;
import ua.org.migdal.data.User;
import ua.org.migdal.data.UserRepository;
import ua.org.migdal.data.UserRight;
import ua.org.migdal.session.RequestContext;
import ua.org.migdal.util.CachedValue;
import ua.org.migdal.util.LikeUtils;
import ua.org.migdal.util.Utils;

@Service
public class UserManager {

    @Autowired
    private Config config;

    @Autowired
    private UserRepository userRepository;

    @Autowired
    private RequestContext requestContext;

    private final CachedValue<Long> guestId = new CachedValue<>(this::fetchGuestId);

    public boolean exists(long id) {
        return userRepository.exists(id);
    }

    public long count() {
        return userRepository.count();
    }

    public User get(long id) {
        return userRepository.findOne(id);
    }

    public User beg(long id) {
        short hide = requestContext.isUserAdminUsers() ? (short) 2 : 1;
        return userRepository.findByIdAndHiddenLessThan(id, hide);
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

    public int countNotConfirmed() {
        return userRepository.countByConfirmDeadlineNotNull();
    }

    @Transactional
    public void confirm(User user) {
        user.setNoLogin(false);
        user.setHidden((short) 0);
        user.setConfirmDeadline(null);
        user.setLastOnline(Utils.now());
        userRepository.save(user);
    }

    public Page<User> begAll(String prefix, String sortField, int offset, int limit) {
        short hide = requestContext.isUserAdminUsers() ? (short) 2 : 1;
        sortField = !StringUtils.isEmpty(sortField) ? sortField : "login";

        QUser user = QUser.user;
        BooleanBuilder builder = new BooleanBuilder();
        builder.and(user.hidden.lt(hide));
        if (!StringUtils.isEmpty(prefix)) {
            switch (sortField) {
                case "login":
                    builder.and(user.login.likeIgnoreCase(LikeUtils.startsWith(prefix), LikeUtils.ESCAPE_CHAR));
                    break;

                case "name":
                    builder.andAnyOf(
                            user.name.likeIgnoreCase(LikeUtils.startsWith(prefix)),
                            user.jewishName.likeIgnoreCase(LikeUtils.startsWith(prefix)));
                    break;

                case "surname":
                    builder.and(user.surname.likeIgnoreCase(LikeUtils.startsWith(prefix), LikeUtils.ESCAPE_CHAR));
                    break;
            }
        }
        return userRepository.findAll(builder,
                new PageRequest(offset / limit, limit, Sort.Direction.ASC, sortField));
    }

}