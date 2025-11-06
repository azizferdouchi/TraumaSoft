package com.traumasoft.controller;

import com.traumasoft.entity.User;
import com.traumasoft.service.UserService;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.stereotype.Controller;
import org.springframework.ui.Model;
import org.springframework.web.bind.annotation.GetMapping;
import org.springframework.web.bind.annotation.PostMapping;
import org.springframework.web.servlet.mvc.support.RedirectAttributes;

@Controller
public class AuthController {

    @Autowired
    private UserService userService;

    @GetMapping("/register")
    public String showRegistrationForm(Model model) {
        model.addAttribute("user", new User());
        return "register";
    }

    @PostMapping("/register")
    public String registerUser(User user, RedirectAttributes redirectAttributes) {
        try {
            if (userService.usernameExists(user.getUsername())) {
                redirectAttributes.addFlashAttribute("error", "Nom d'utilisateur déjà existant");
                return "redirect:/register";
            }
            if (userService.emailExists(user.getEmail())) {
                redirectAttributes.addFlashAttribute("error", "Email déjà existant");
                return "redirect:/register";
            }
            
            userService.createUser(user);
            redirectAttributes.addFlashAttribute("success", "Utilisateur créé avec succès!");
            return "redirect:/login";
        } catch (Exception e) {
            redirectAttributes.addFlashAttribute("error", "Erreur lors de la création: " + e.getMessage());
            return "redirect:/register";
        }
    }
}