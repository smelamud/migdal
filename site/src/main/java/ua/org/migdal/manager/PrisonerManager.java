package ua.org.migdal.manager;

import javax.inject.Inject;

import org.springframework.stereotype.Service;
import ua.org.migdal.data.PrisonerRepository;

@Service
public class PrisonerManager {

    @Inject
    private PrisonerRepository prisonerRepository;

}
