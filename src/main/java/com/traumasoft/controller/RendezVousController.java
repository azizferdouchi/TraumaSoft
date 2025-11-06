package com.traumasoft.controller;

import com.traumasoft.entity.RendezVous;
import com.traumasoft.entity.Patient;
import com.traumasoft.service.RendezVousService;
import com.traumasoft.service.PatientService;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.stereotype.Controller;
import org.springframework.ui.Model;
import org.springframework.web.bind.annotation.*;
import java.time.LocalDateTime;
import java.util.List;

@Controller
@RequestMapping("/rendezvous")
public class RendezVousController {

    @Autowired
    private RendezVousService rendezVousService;

    @Autowired
    private PatientService patientService;

    @GetMapping
    public String listRendezVous(Model model) {
        List<RendezVous> rendezVous = rendezVousService.getAllRendezVous();
        model.addAttribute("rendezVous", rendezVous);
        model.addAttribute("pageTitle", "Gestion des Rendez-vous");
        return "rendezvous";
    }

    @GetMapping("/nouveau")
    public String showRendezVousForm(Model model) {
        List<Patient> patients = patientService.getAllPatients();
        model.addAttribute("rendezVous", new RendezVous());
        model.addAttribute("patients", patients);
        model.addAttribute("pageTitle", "Nouveau Rendez-vous");
        return "rendezvous-form";
    }

    @PostMapping("/enregistrer")
    public String saveRendezVous(@ModelAttribute RendezVous rendezVous, 
                                @RequestParam Long patientId) {
        Patient patient = patientService.getPatientById(patientId)
                .orElseThrow(() -> new IllegalArgumentException("Patient non trouvé: " + patientId));
        rendezVous.setPatient(patient);
        rendezVousService.saveRendezVous(rendezVous);
        return "redirect:/rendezvous";
    }

    @GetMapping("/modifier/{id}")
    public String showEditForm(@PathVariable Long id, Model model) {
        RendezVous rendezVous = rendezVousService.getRendezVousById(id)
                .orElseThrow(() -> new IllegalArgumentException("Rendez-vous non trouvé: " + id));
        List<Patient> patients = patientService.getAllPatients();
        model.addAttribute("rendezVous", rendezVous);
        model.addAttribute("patients", patients);
        model.addAttribute("pageTitle", "Modifier Rendez-vous");
        return "rendezvous-form";
    }

    @GetMapping("/supprimer/{id}")
    public String deleteRendezVous(@PathVariable Long id) {
        rendezVousService.deleteRendezVous(id);
        return "redirect:/rendezvous";
    }
}