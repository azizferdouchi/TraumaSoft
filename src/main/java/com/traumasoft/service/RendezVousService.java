package com.traumasoft.service;

import com.traumasoft.entity.RendezVous;
import com.traumasoft.repository.RendezVousRepository;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.stereotype.Service;
import java.time.LocalDateTime;
import java.util.List;
import java.util.Optional;

@Service
public class RendezVousService {

    @Autowired
    private RendezVousRepository rendezVousRepository;

    public List<RendezVous> getAllRendezVous() {
        return rendezVousRepository.findAll();
    }

    public Optional<RendezVous> getRendezVousById(Long id) {
        return rendezVousRepository.findById(id);
    }

    public RendezVous saveRendezVous(RendezVous rendezVous) {
        return rendezVousRepository.save(rendezVous);
    }

    public void deleteRendezVous(Long id) {
        rendezVousRepository.deleteById(id);
    }

    public List<RendezVous> getRendezVousByPatientId(Long patientId) {
        return rendezVousRepository.findByPatientId(patientId);
    }

    public List<RendezVous> getRendezVousBetweenDates(LocalDateTime start, LocalDateTime end) {
        return rendezVousRepository.findByDateHeureBetween(start, end);
    }

    public List<RendezVous> getRendezVousByStatut(String statut) {
        return rendezVousRepository.findByStatut(statut);
    }
}