package ua.org.migdal.data;

import org.springframework.data.jpa.repository.JpaRepository;

public interface PrisonerRepository extends JpaRepository<Prisoner, Long> {
}
