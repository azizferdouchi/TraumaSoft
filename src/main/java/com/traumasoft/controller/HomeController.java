package com.traumasoft.controller;

import org.springframework.stereotype.Controller;
import org.springframework.ui.Model;
import org.springframework.web.bind.annotation.GetMapping;

@Controller
public class HomeController {

    @GetMapping("/")
    public String home(Model model) {
        model.addAttribute("message", "Bienvenue dans TraumaSoft");
        return "home";
    }
    
    @GetMapping("/patients")
    public String patientsRedirect() {
        return "redirect:/patients/list";
    }
    
    @GetMapping("/rendezvous")
    public String rendezvousRedirect() {
        return "redirect:/rendezvous/list";
    }
}