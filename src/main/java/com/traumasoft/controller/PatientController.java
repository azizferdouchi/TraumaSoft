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

    @GetMapping("/list")
    public String listPatients(Model model) {
        List<Patient> patients = patientService.getAllPatients();
        model.addAttribute("patients", patients);
        return "listPatients";
    }

    @GetMapping("/add")
    public String showAddPatientForm(Model model) {
        model.addAttribute("patient", new Patient());
        return "addPatient";
    }

    @PostMapping("/add")
    public String addPatient(@ModelAttribute Patient patient) {
        patientService.savePatient(patient);
        return "redirect:/patients/list";
    }

    @GetMapping("/edit/{id}")
    public String showEditPatientForm(@PathVariable("id") Long id, Model model) {
        Patient patient = patientService.getPatientById(id)
                .orElseThrow(() -> new IllegalArgumentException("Invalid patient Id:" + id));
        model.addAttribute("patient", patient);
        return "addPatient";
    }

    @GetMapping("/delete/{id}")
    public String deletePatient(@PathVariable("id") Long id) {
        patientService.deletePatient(id);
        return "redirect:/patients/list";
    }
}