package com.traumasoft.controller;

import com.traumasoft.entity.Patient;
import com.traumasoft.service.PatientService;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.stereotype.Controller;
import org.springframework.ui.Model;
import org.springframework.web.bind.annotation.*;
import java.util.List;

@Controller
@RequestMapping("/patients")
public class PatientController {

    @Autowired
    private PatientService patientService;

    @GetMapping
    public String listPatients(Model model) {
        List<Patient> patients = patientService.getAllPatients();
        model.addAttribute("patients", patients);
        model.addAttribute("pageTitle", "Gestion des Patients");
        return "patients";
    }

    @GetMapping("/nouveau")
    public String showPatientForm(Model model) {
        model.addAttribute("patient", new Patient());
        model.addAttribute("pageTitle", "Nouveau Patient");
        return "patient-form";
    }

    @PostMapping("/enregistrer")
    public String savePatient(@ModelAttribute Patient patient) {
        patientService.savePatient(patient);
        return "redirect:/patients";
    }

    @GetMapping("/modifier/{id}")
    public String showEditForm(@PathVariable Long id, Model model) {
        Patient patient = patientService.getPatientById(id)
                .orElseThrow(() -> new IllegalArgumentException("Patient non trouvé: " + id));
        model.addAttribute("patient", patient);
        model.addAttribute("pageTitle", "Modifier Patient");
        return "patient-form";
    }

    @GetMapping("/supprimer/{id}")
    public String deletePatient(@PathVariable Long id) {
        patientService.deletePatient(id);
        return "redirect:/patients";
    }

    @GetMapping("/rechercher")
    public String searchPatients(@RequestParam String keyword, Model model) {
        List<Patient> patients = patientService.searchPatients(keyword);
        model.addAttribute("patients", patients);
        model.addAttribute("pageTitle", "Résultats de recherche: " + keyword);
        return "patients";
    }
}