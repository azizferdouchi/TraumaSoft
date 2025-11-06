package com.traumasoft.controller;

import com.traumasoft.entity.RendezVous;
import com.traumasoft.entity.Patient;
import com.traumasoft.service.RendezVousService;
import com.traumasoft.service.PatientService;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.stereotype.Controller;
import org.springframework.ui.Model;
import org.springframework.web.bind.annotation.*;

import java.util.List;

@Controller
@RequestMapping("/rendezvous")
public class RendezVousController {

    @Autowired
    private RendezVousService rendezVousService;

    @Autowired
    private PatientService patientService;

    @GetMapping("/list")
    public String listRendezVous(Model model) {
        List<RendezVous> rendezVous = rendezVousService.getAllRendezVous();
        model.addAttribute("rendezVous", rendezVous);
        return "listRendezVous";
    }

    @GetMapping("/add")
    public String showAddRendezVousForm(Model model) {
        List<Patient> patients = patientService.getAllPatients();
        model.addAttribute("rendezVous", new RendezVous());
        model.addAttribute("patients", patients);
        return "addRendezVous";
    }

    @PostMapping("/add")
    public String addRendezVous(@ModelAttribute RendezVous rendezVous, 
                               @RequestParam("patientId") Long patientId) {
        Patient patient = patientService.getPatientById(patientId)
                .orElseThrow(() -> new IllegalArgumentException("Invalid patient Id:" + patientId));
        rendezVous.setPatient(patient);
        rendezVousService.saveRendezVous(rendezVous);
        return "redirect:/rendezvous/list";
    }

    @GetMapping("/edit/{id}")
    public String showEditRendezVousForm(@PathVariable("id") Long id, Model model) {
        RendezVous rendezVous = rendezVousService.getRendezVousById(id)
                .orElseThrow(() -> new IllegalArgumentException("Invalid rendez-vous Id:" + id));
        List<Patient> patients = patientService.getAllPatients();
        model.addAttribute("rendezVous", rendezVous);
        model.addAttribute("patients", patients);
        return "addRendezVous";
    }

    @GetMapping("/delete/{id}")
    public String deleteRendezVous(@PathVariable("id") Long id) {
        rendezVousService.deleteRendezVous(id);
        return "redirect:/rendezvous/list";
    }
}