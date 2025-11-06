class PatientsManager {
    constructor(app) {
        this.app = app;
        this.currentPatientId = null;
    }

    async loadPatientDiagnoses(patientId) {
        try {
            const response = await fetch(`${this.app.apiBase}/controllers/DiagnosisController.php?patient_id=${patientId}`);
            const data = await response.json();
            
            const container = document.getElementById('diagnosis-list');
            if (data.records && data.records.length > 0) {
                container.innerHTML = data.records.map(diagnosis => `
                    <div class="diagnosis-item card">
                        <div class="diagnosis-header">
                            <strong>${this.app.formatDate(diagnosis.date_diagnostic)}</strong>
                            <span class="diagnosis-doctor">${diagnosis.medecin || 'Dr. Traumatologue'}</span>
                        </div>
                        <div class="diagnosis-content">
                            <p><strong>Diagnostic:</strong> ${diagnosis.diagnostic}</p>
                            ${diagnosis.observations ? `<p><strong>Observations:</strong> ${diagnosis.observations}</p>` : ''}
                            ${diagnosis.traitement_propose ? `<p><strong>Traitement:</strong> ${diagnosis.traitement_propose}</p>` : ''}
                        </div>
                        <div class="diagnosis-actions">
                            <button class="btn btn-secondary btn-sm" onclick="patientsManager.editDiagnosis(${diagnosis.id})">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-danger btn-sm" onclick="patientsManager.deleteDiagnosis(${diagnosis.id}, ${patientId})">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                `).join('');
            } else {
                container.innerHTML = '<p class="no-data">Aucun diagnostic enregistré</p>';
            }

        } catch (error) {
            console.error('Error loading diagnoses:', error);
            document.getElementById('diagnosis-list').innerHTML = 
                '<p class="no-data">Erreur de chargement des diagnostics</p>';
        }
    }

    async loadPatientPrescriptions(patientId) {
        try {
            const response = await fetch(`${this.app.apiBase}/controllers/PrescriptionController.php?patient_id=${patientId}`);
            const data = await response.json();
            
            const container = document.getElementById('prescriptions-list');
            if (data.records && data.records.length > 0) {
                container.innerHTML = data.records.map(prescription => `
                    <div class="prescription-item card">
                        <div class="prescription-header">
                            <strong>${this.app.formatDate(prescription.date_prescription)}</strong>
                            <span class="prescription-doctor">${prescription.medecin_prescripteur || 'Dr. Traumatologue'}</span>
                        </div>
                        <div class="prescription-content">
                            <div class="medicaments-list">
                                ${this.formatMedicaments(prescription.medicaments)}
                            </div>
                            ${prescription.instructions ? `<p><strong>Instructions:</strong> ${prescription.instructions}</p>` : ''}
                            ${prescription.duree_traitement ? `<p><strong>Durée:</strong> ${prescription.duree_traitement}</p>` : ''}
                        </div>
                        <div class="prescription-actions">
                            <button class="btn btn-secondary btn-sm" onclick="patientsManager.editPrescription(${prescription.id})">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-danger btn-sm" onclick="patientsManager.deletePrescription(${prescription.id}, ${patientId})">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                `).join('');
            } else {
                container.innerHTML = '<p class="no-data">Aucune ordonnance enregistrée</p>';
            }

        } catch (error) {
            console.error('Error loading prescriptions:', error);
            document.getElementById('prescriptions-list').innerHTML = 
                '<p class="no-data">Erreur de chargement des ordonnances</p>';
        }
    }

    formatMedicaments(medicamentsJson) {
        try {
            const medicaments = JSON.parse(medicamentsJson);
            return medicaments.map(med => `
                <div class="medicament-item">
                    <strong>${med.nom}</strong> - ${med.dose} - ${med.frequence}
                    ${med.duree ? ` pendant ${med.duree}` : ''}
                </div>
            `).join('');
        } catch (e) {
            return '<div class="medicament-item">Format des médicaments invalide</div>';
        }
    }

    showDiagnosisForm(patientId = null) {
        const patientIdToUse = patientId || this.app.currentPatientId;
        if (!patientIdToUse) {
            alert('Veuillez sélectionner un patient d\'abord');
            return;
        }

        const modal = this.createModal('Nouveau Diagnostic');
        modal.innerHTML = `
            <div class="modal-content">
                <div class="modal-header">
                    <h3>Nouveau Diagnostic</h3>
                    <span class="close" onclick="this.parentElement.parentElement.remove()">&times;</span>
                </div>
                <div class="modal-body">
                    <form id="diagnosis-form">
                        <input type="hidden" id="diagnosis-patient-id" value="${patientIdToUse}">
                        <div class="form-group">
                            <label for="diagnosis-date">Date du Diagnostic *</label>
                            <input type="date" id="diagnosis-date" required value="${new Date().toISOString().split('T')[0]}">
                        </div>
                        <div class="form-group">
                            <label for="diagnosis-text">Diagnostic *</label>
                            <textarea id="diagnosis-text" rows="4" required placeholder="Décrire le diagnostic..."></textarea>
                        </div>
                        <div class="form-group">
                            <label for="diagnosis-observations">Observations</label>
                            <textarea id="diagnosis-observations" rows="3" placeholder="Observations complémentaires..."></textarea>
                        </div>
                        <div class="form-group">
                            <label for="diagnosis-traitement">Traitement Proposé</label>
                            <textarea id="diagnosis-traitement" rows="3" placeholder="Traitement proposé..."></textarea>
                        </div>
                        <div class="form-group">
                            <label for="diagnosis-medecin">Médecin</label>
                            <input type="text" id="diagnosis-medecin" value="Dr. Traumatologue">
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="this.parentElement.parentElement.remove()">Annuler</button>
                    <button type="button" class="btn btn-primary" onclick="patientsManager.saveDiagnosis()">Enregistrer</button>
                </div>
            </div>
        `;
        document.body.appendChild(modal);
    }

    async saveDiagnosis() {
        const diagnosisData = {
            patient_id: document.getElementById('diagnosis-patient-id').value,
            date_diagnostic: document.getElementById('diagnosis-date').value,
            diagnostic: document.getElementById('diagnosis-text').value,
            observations: document.getElementById('diagnosis-observations').value,
            traitement_propose: document.getElementById('diagnosis-traitement').value,
            medecin: document.getElementById('diagnosis-medecin').value
        };

        // Validation
        if (!diagnosisData.date_diagnostic || !diagnosisData.diagnostic) {
            alert('Veuillez remplir les champs obligatoires');
            return;
        }

        try {
            const response = await fetch(`${this.app.apiBase}/controllers/DiagnosisController.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(diagnosisData)
            });

            const result = await response.json();
            
            if (response.ok) {
                alert('Diagnostic enregistré avec succès');
                this.closeCurrentModal();
                this.loadPatientDiagnoses(diagnosisData.patient_id);
            } else {
                alert('Erreur: ' + result.message);
            }
        } catch (error) {
            console.error('Error saving diagnosis:', error);
            alert('Erreur lors de l\'enregistrement du diagnostic');
        }
    }

    showPrescriptionForm(patientId = null) {
        const patientIdToUse = patientId || this.app.currentPatientId;
        if (!patientIdToUse) {
            alert('Veuillez sélectionner un patient d\'abord');
            return;
        }

        const modal = this.createModal('Nouvelle Ordonnance');
        modal.innerHTML = `
            <div class="modal-content">
                <div class="modal-header">
                    <h3>Nouvelle Ordonnance</h3>
                    <span class="close" onclick="this.parentElement.parentElement.remove()">&times;</span>
                </div>
                <div class="modal-body">
                    <form id="prescription-form">
                        <input type="hidden" id="prescription-patient-id" value="${patientIdToUse}">
                        <div class="form-group">
                            <label for="prescription-date">Date de l'Ordonnance *</label>
                            <input type="date" id="prescription-date" required value="${new Date().toISOString().split('T')[0]}">
                        </div>
                        
                        <div class="form-group">
                            <label>Médicaments</label>
                            <div id="medicaments-container">
                                <div class="medicament-form">
                                    <div class="form-row">
                                        <div class="form-group">
                                            <input type="text" class="medicament-nom" placeholder="Nom du médicament" required>
                                        </div>
                                        <div class="form-group">
                                            <input type="text" class="medicament-dose" placeholder="Dose" required>
                                        </div>
                                        <div class="form-group">
                                            <input type="text" class="medicament-frequence" placeholder="Fréquence" required>
                                        </div>
                                        <div class="form-group">
                                            <input type="text" class="medicament-duree" placeholder="Durée">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <button type="button" class="btn btn-secondary btn-sm" onclick="patientsManager.addMedicamentField()">
                                <i class="fas fa-plus"></i> Ajouter un médicament
                            </button>
                        </div>

                        <div class="form-group">
                            <label for="prescription-instructions">Instructions</label>
                            <textarea id="prescription-instructions" rows="3" placeholder="Instructions particulières..."></textarea>
                        </div>
                        <div class="form-group">
                            <label for="prescription-duree">Durée du Traitement</label>
                            <input type="text" id="prescription-duree" placeholder="Ex: 7 jours">
                        </div>
                        <div class="form-group">
                            <label for="prescription-medecin">Médecin Prescripteur</label>
                            <input type="text" id="prescription-medecin" value="Dr. Traumatologue">
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="this.parentElement.parentElement.remove()">Annuler</button>
                    <button type="button" class="btn btn-primary" onclick="patientsManager.savePrescription()">Enregistrer</button>
                </div>
            </div>
        `;
        document.body.appendChild(modal);
    }

    addMedicamentField() {
        const container = document.getElementById('medicaments-container');
        const newField = document.createElement('div');
        newField.className = 'medicament-form';
        newField.innerHTML = `
            <div class="form-row">
                <div class="form-group">
                    <input type="text" class="medicament-nom" placeholder="Nom du médicament" required>
                </div>
                <div class="form-group">
                    <input type="text" class="medicament-dose" placeholder="Dose" required>
                </div>
                <div class="form-group">
                    <input type="text" class="medicament-frequence" placeholder="Fréquence" required>
                </div>
                <div class="form-group">
                    <input type="text" class="medicament-duree" placeholder="Durée">
                </div>
                <div class="form-group">
                    <button type="button" class="btn btn-danger btn-sm" onclick="this.parentElement.parentElement.parentElement.remove()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        `;
        container.appendChild(newField);
    }

    async savePrescription() {
        const medicaments = [];
        const medicamentForms = document.querySelectorAll('.medicament-form');
        
        medicamentForms.forEach(form => {
            const nom = form.querySelector('.medicament-nom').value;
            const dose = form.querySelector('.medicament-dose').value;
            const frequence = form.querySelector('.medicament-frequence').value;
            const duree = form.querySelector('.medicament-duree').value;
            
            if (nom && dose && frequence) {
                medicaments.push({
                    nom: nom,
                    dose: dose,
                    frequence: frequence,
                    duree: duree
                });
            }
        });

        if (medicaments.length === 0) {
            alert('Veuillez ajouter au moins un médicament');
            return;
        }

        const prescriptionData = {
            patient_id: document.getElementById('prescription-patient-id').value,
            date_prescription: document.getElementById('prescription-date').value,
            medicaments: medicaments,
            instructions: document.getElementById('prescription-instructions').value,
            duree_traitement: document.getElementById('prescription-duree').value,
            medecin_prescripteur: document.getElementById('prescription-medecin').value
        };

        try {
            const response = await fetch(`${this.app.apiBase}/controllers/PrescriptionController.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(prescriptionData)
            });

            const result = await response.json();
            
            if (response.ok) {
                alert('Ordonnance enregistrée avec succès');
                this.closeCurrentModal();
                this.loadPatientPrescriptions(prescriptionData.patient_id);
            } else {
                alert('Erreur: ' + result.message);
            }
        } catch (error) {
            console.error('Error saving prescription:', error);
            alert('Erreur lors de l\'enregistrement de l\'ordonnance');
        }
    }

    createModal(title) {
        const modal = document.createElement('div');
        modal.className = 'modal';
        modal.style.display = 'block';
        return modal;
    }

    closeCurrentModal() {
        const modal = document.querySelector('.modal:last-child');
        if (modal) {
            modal.remove();
        }
    }

    async deleteDiagnosis(diagnosisId, patientId) {
        if (!confirm('Êtes-vous sûr de vouloir supprimer ce diagnostic ?')) {
            return;
        }

        try {
            const response = await fetch(`${this.app.apiBase}/controllers/DiagnosisController.php`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    id: diagnosisId,
                    patient_id: patientId
                })
            });

            const result = await response.json();
            
            if (response.ok) {
                alert('Diagnostic supprimé avec succès');
                this.loadPatientDiagnoses(patientId);
            } else {
                alert('Erreur: ' + result.message);
            }
        } catch (error) {
            console.error('Error deleting diagnosis:', error);
            alert('Erreur lors de la suppression du diagnostic');
        }
    }

    async deletePrescription(prescriptionId, patientId) {
        if (!confirm('Êtes-vous sûr de vouloir supprimer cette ordonnance ?')) {
            return;
        }

        try {
            const response = await fetch(`${this.app.apiBase}/controllers/PrescriptionController.php`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    id: prescriptionId,
                    patient_id: patientId
                })
            });

            const result = await response.json();
            
            if (response.ok) {
                alert('Ordonnance supprimée avec succès');
                this.loadPatientPrescriptions(patientId);
            } else {
                alert('Erreur: ' + result.message);
            }
        } catch (error) {
            console.error('Error deleting prescription:', error);
            alert('Erreur lors de la suppression de l\'ordonnance');
        }
    }
}

// Initialisation
document.addEventListener('DOMContentLoaded', () => {
    window.patientsManager = new PatientsManager(window.app);
});