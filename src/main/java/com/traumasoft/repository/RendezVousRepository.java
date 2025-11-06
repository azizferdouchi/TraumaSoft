package com.traumasoft.repository;

import com.traumasoft.entity.RendezVous;
import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.stereotype.Repository;
import java.time.LocalDateTime;
import java.util.List;

@Repository
public interface RendezVousRepository extends JpaRepository<RendezVous, Long> {
    List<RendezVous> findByPatientId(Long patientId);
    List<RendezVous> findByDateHeureBetween(LocalDateTime start, LocalDateTime end);
    List<RendezVous> findByStatut(String statut);
}