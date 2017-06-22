package ua.org.migdal.manager;

import java.util.Collections;
import java.util.List;
import java.util.Set;

import javax.inject.Inject;

import org.springframework.cache.annotation.Cacheable;
import org.springframework.stereotype.Service;

import com.querydsl.core.BooleanBuilder;
import com.querydsl.core.types.Predicate;
import com.querydsl.core.types.dsl.NumberPath;

import ua.org.migdal.data.EntryRepository;
import ua.org.migdal.session.RequestContext;
import ua.org.migdal.util.Perm;

@Service
public class PermManager {

    @Inject
    private RequestContext requestContext;

    @Inject
    private EntryRepository entryRepository;

    // We need a separate class here, because @Cacheable doesn't work for internal calls
    private class Internal {

        @Cacheable("entries-permsall")
        private List<Long> getPermsVariety() {
            return entryRepository.permsVariety();
        }

    }

    private Internal internal = new Internal();

    private Predicate getMask(NumberPath<Long> field, long right) {
        BooleanBuilder builder = new BooleanBuilder();
        for (Long perms : internal.getPermsVariety()) {
            if ((perms & right) != 0) {
                builder.or(field.eq(perms));
            }
        }
        return builder;
    }

    public Predicate getFilter(NumberPath<Long> userIdField, NumberPath<Long> groupIdField, NumberPath<Long> permsField,
                               long right) {
        return getFilter(userIdField, groupIdField, permsField, right, false);
    }

    public Predicate getFilter(NumberPath<Long> userIdField, NumberPath<Long> groupIdField, NumberPath<Long> permsField,
                               long right, boolean asGuest) {
        long eUserId = !asGuest ? requestContext.getUserId() : 0;
        Set<Long> eUserGroups = !asGuest ? requestContext.getUserGroups() : Collections.emptySet();

        if (eUserId <= 0) {
            return getMask(permsField, right << Perm.GUEST);
        }
        BooleanBuilder builder = new BooleanBuilder();
        builder.orAllOf(userIdField.eq(eUserId), getMask(permsField, right << Perm.USER));
        BooleanBuilder groups = new BooleanBuilder();
        for (Long group : eUserGroups) {
            groups.or(groupIdField.eq(group));
        }
        builder.orAllOf(groups, getMask(permsField, right << Perm.GROUP));
        builder.or(getMask(permsField, right << Perm.OTHER));
        builder.or(getMask(permsField, right << Perm.GUEST));
        return builder;
    }

}