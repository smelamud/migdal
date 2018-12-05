package ua.org.migdal.manager;

import javax.inject.Inject;

import com.querydsl.core.BooleanBuilder;
import org.springframework.data.domain.Page;
import org.springframework.data.domain.PageRequest;
import org.springframework.data.domain.Sort;
import org.springframework.stereotype.Service;
import org.springframework.util.StringUtils;
import ua.org.migdal.data.Prisoner;
import ua.org.migdal.data.PrisonerRepository;
import ua.org.migdal.data.QPrisoner;
import ua.org.migdal.util.LikeUtils;

@Service
public class PrisonerManager {

    @Inject
    private PrisonerRepository prisonerRepository;

    public Page<Prisoner> begAll(String searchField, String prefix, String sortField, int offset, int limit) {
        searchField = !StringUtils.isEmpty(searchField) ? searchField : "name";
        sortField = !StringUtils.isEmpty(sortField) ? sortField : searchField;

        QPrisoner prisoner = QPrisoner.prisoner;
        BooleanBuilder builder = new BooleanBuilder();
        if (!StringUtils.isEmpty(prefix)) {
            switch (searchField) {
                case "name":
                    builder.and(prisoner.name.likeIgnoreCase(LikeUtils.startsWith(prefix), LikeUtils.ESCAPE_CHAR));
                    break;

                case "nameRussian":
                    builder.and(prisoner.nameRussian.likeIgnoreCase(LikeUtils.startsWith(prefix), LikeUtils.ESCAPE_CHAR));
                    break;

                case "location":
                    builder.and(prisoner.location.likeIgnoreCase(LikeUtils.startsWith(prefix), LikeUtils.ESCAPE_CHAR));
                    break;

                case "ghettoName":
                    builder.and(prisoner.ghettoName.likeIgnoreCase(LikeUtils.startsWith(prefix), LikeUtils.ESCAPE_CHAR));
                    break;

                case "senderName":
                    builder.and(prisoner.senderName.likeIgnoreCase(LikeUtils.startsWith(prefix), LikeUtils.ESCAPE_CHAR));
                    break;
            }
        }
        return prisonerRepository.findAll(builder,
                PageRequest.of(offset / limit, limit, Sort.Direction.ASC, sortField));
    }

}
