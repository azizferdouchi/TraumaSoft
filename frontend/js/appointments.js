class AppointmentsManager {
    constructor(app) {
        this.app = app;
        this.init();
    }

    init() {
        this.loadAppointments();
        this.setupEventListeners();
    }

    setupEventListeners() {
        // Filtres des rendez-vous
        document.getElementById('appointment-filter-date').addEventListener('change', (e) => {
            this.filterAppointmentsByDate(e.target.value);
        });

        document.getElementById('appointment-filter-status').addEventListener('change', (e) => {
            this.filterAppointmentsByStatus(e.target.value);
        });
    }

    async loadAppointments() {
        try {
            const response = await fetch(`${this.app.apiBase}/controllers/AppointmentController.php`);
            const data = await response.json();
            
            this.displayAppointments(data.records);
            this.updateAppointmentStats(data.records);

        } catch (error) {
            console.error('Error loading appointments:', error);
        }
    }

    displayAppointments(appointments) {
        const container = document.getElementById('appointments-list');
        
        if (!appointments || appointments.length === 0) {
            container.innerHTML = '<p class="no-data">Aucun rendez-vous trouvé</p>';
            return;
        }

        container.innerHTML = appointments.map(apt => `
            <div class="appointment-card card" data-status="${apt.statut}">
                <div class="appointment-header">
                    <div class="appointment-patient">
                        <strong>${apt.patient_nom} ${apt.patient_prenom}</strong>
                        <span class="appointment-time">${apt.heure}</span>
                    </div>
                    <div class="appointment-actions">
                        <span class="status-badge status-${apt.statut}">${apt.statut}</span>
                        <button class="btn btn-primary btn-sm" onclick="appointmentsManager.editAppointment(${apt.id})">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-danger btn-sm" onclick="appointmentsManager.deleteAppointment(${apt.id})">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
                <div class="appointment-body">
                    <p><strong>Type:</strong> ${apt.type_consultation}</p>
                    <p><strong>Date:</strong> ${this.app.formatDate(apt.date_rdv)}</p>
                    ${apt.notes ? `<p><strong>Notes:</strong> ${apt.notes}</p>` : ''}
                </div>
            </div>
        `).join('');
    }

    updateAppointmentStats(appointments) {
        const today = new Date().toISOString().split('T')[0];
        const todayAppointments = appointments.filter(apt => apt.date_rdv === today);
        const plannedAppointments = appointments.filter(apt => apt.statut === 'planifié');
        
        document.getElementById('total-appointments').textContent = appointments.length;
        document.getElementById('today-appointments-count').textContent = todayAppointments.length;
        document.getElementById('planned-appointments').textContent = plannedAppointments.length;
    }

    showAppointmentForm() {
        const modal = document.getElementById('appointment-modal');
        document.getElementById('appointment-modal-title').textContent = 'Nouveau Rendez-vous';
        document.getElementById('appointment-form').reset();
        modal.style.display = 'block';

        // Charger la liste des patients
        this.loadPatientsForAppointment();
    }

    async loadPatientsForAppointment() {
        try {
            const response = await fetch(`${this.app.apiBase}/controllers/PatientController.php`);
            const data = await response.json();
            
            const select = document.getElementById('appointment-patient');
            select.innerHTML = '<option value="">Sélectionner un patient</option>' +
                data.records.map(patient => 
                    `<option value="${patient.id}">${patient.nom} ${patient.prenom}</option>`
                ).join('');

        } catch (error) {
            console.error('Error loading patients:', error);
        }
    }

    async saveAppointment() {
        const formData = {
            patient_id: document.getElementById('appointment-patient').value,
            date_rdv: document.getElementById('appointment-date').value,
            heure: document.getElementById('appointment-time').value,
            type_consultation: document.getElementById('appointment-type').value,
            statut: document.getElementById('appointment-status').value,
            notes: document.getElementById('appointment-notes').value
        };

        try {
            const response = await fetch(`${this.app.apiBase}/controllers/AppointmentController.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(formData)
            });

            const result = await response.json();
            
            if (response.ok) {
                alert('Rendez-vous créé avec succès');
                this.closeAppointmentModal();
                this.loadAppointments();
            } else {
                alert('Erreur: ' + result.message);
            }
        } catch (error) {
            console.error('Error saving appointment:', error);
            alert('Erreur lors de la création du rendez-vous');
        }
    }

    closeAppointmentModal() {
        document.getElementById('appointment-modal').style.display = 'none';
    }

    async addToWaitingRoom(patientId) {
        try {
            const response = await fetch(`${this.app.apiBase}/controllers/WaitingRoomController.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    patient_id: patientId,
                    heure_arrivee: new Date().toTimeString().split(' ')[0].substring(0, 5)
                })
            });

            const result = await response.json();
            
            if (response.ok) {
                alert('Patient ajouté à la salle d\'attente');
                this.app.loadWaitingRoomList();
            } else {
                alert('Erreur: ' + result.message);
            }
        } catch (error) {
            console.error('Error adding to waiting room:', error);
        }
    }
}

// Initialisation
document.addEventListener('DOMContentLoaded', () => {
    window.appointmentsManager = new AppointmentsManager(window.app);
});