package com.traumasoft.service;

import com.traumasoft.entity.Patient;
import com.traumasoft.repository.PatientRepository;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.stereotype.Service;
import java.util.List;
import java.util.Optional;

@Service
public class PatientService {

    @Autowired
    private PatientRepository patientRepository;

    public List<Patient> getAllPatients() {
        return patientRepository.findAll();
    }

    public Optional<Patient> getPatientById(Long id) {
        return patientRepository.findById(id);
    }

    public Patient savePatient(Patient patient) {
        return patientRepository.save(patient);
    }

    public void deletePatient(Long id) {
        patientRepository.deleteById(id);
    }

    public List<Patient> searchPatients(String keyword) {
        return patientRepository.findByNomContainingIgnoreCaseOrPrenomContainingIgnoreCase(keyword, keyword);
    }

    public List<Patient> findByNom(String nom) {
        return patientRepository.findByNomContainingIgnoreCase(nom);
    }

    public List<Patient> findByPrenom(String prenom) {
        return patientRepository.findByPrenomContainingIgnoreCase(prenom);
    }
}