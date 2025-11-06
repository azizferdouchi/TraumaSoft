package com.traumasoft.entity;

import jakarta.persistence.*;
import java.time.LocalDateTime;

@Entity
@Table(name = "rendezvous")
public class RendezVous {
    
    @Id
    @GeneratedValue(strategy = GenerationType.IDENTITY)
    private Long id;
    
    @ManyToOne
    @JoinColumn(name = "patient_id")
    private Patient patient;
    
    @Column(name = "date_heure", nullable = false)
    private LocalDateTime dateHeure;
    
    @Column(name = "type_consultation")
    private String typeConsultation;
    
    @Column(name = "notes")
    private String notes;
    
    @Column(name = "statut")
    private String statut = "PLANIFIE";
    
    @Column(name = "created_at")
    private LocalDateTime createdAt;
    
    // Constructeurs
    public RendezVous() {
        this.createdAt = LocalDateTime.now();
    }
    
    // Getters et Setters
    public Long getId() { return id; }
    public void setId(Long id) { this.id = id; }
    
    public Patient getPatient() { return patient; }
    public void setPatient(Patient patient) { this.patient = patient; }
    
    public LocalDateTime getDateHeure() { return dateHeure; }
    public void setDateHeure(LocalDateTime dateHeure) { this.dateHeure = dateHeure; }
    
    public String getTypeConsultation() { return typeConsultation; }
    public void setTypeConsultation(String typeConsultation) { this.typeConsultation = typeConsultation; }
    
    public String getNotes() { return notes; }
    public void setNotes(String notes) { this.notes = notes; }
    
    public String getStatut() { return statut; }
    public void setStatut(String statut) { this.statut = statut; }
    
    public LocalDateTime getCreatedAt() { return createdAt; }
    public void setCreatedAt(LocalDateTime createdAt) { this.createdAt = createdAt; }
}