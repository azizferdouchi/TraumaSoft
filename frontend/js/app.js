class TraumaSoftApp {
    constructor() {
        this.apiBase = 'http://localhost/traumasoft/backend';
        this.currentPatientId = null;
        this.init();
    }

    init() {
        this.setupEventListeners();
        this.loadNavigation();
        this.loadDashboard();
    }

    setupEventListeners() {
        // Navigation
        document.querySelectorAll('.sidebar a').forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                const target = link.getAttribute('href').substring(1);
                this.showSection(target);
            });
        });

        // Patient search
        const patientSearch = document.getElementById('patient-search');
        if (patientSearch) {
            patientSearch.addEventListener('input', (e) => {
                this.searchPatients(e.target.value);
            });
        }
    }

    loadNavigation() {
        this.showSection('dashboard');
    }

    showSection(sectionId) {
        console.log('Chargement de la section:', sectionId);
        
        // Hide all sections
        document.querySelectorAll('.content-section').forEach(section => {
            section.classList.remove('active');
        });

        // Show target section
        const targetSection = document.getElementById(sectionId);
        if (targetSection) {
            targetSection.classList.add('active');
        }

        // Update active nav item
        document.querySelectorAll('.sidebar li').forEach(item => {
            item.classList.remove('active');
        });
        
        const activeNav = document.querySelector(`.sidebar a[href="#${sectionId}"]`);
        if (activeNav) {
            activeNav.parentElement.classList.add('active');
        }

        // Load section data
        switch(sectionId) {
            case 'dashboard':
                this.loadDashboard();
                break;
            case 'patients':
                this.loadPatients();
                break;
            case 'appointments':
                this.loadAppointments();
                break;
            case 'waiting-room':
                this.loadWaitingRoom();
                break;
            case 'operations':
                this.loadOperations();
                break;
            case 'certificates':
                this.loadCertificates();
                break;
            case 'finance':
                this.loadFinance();
                break;
            case 'reports':
                this.loadReports();
                break;
        }
    }

    async loadDashboard() {
        try {
            // Stats
            const statsResponse = await fetch(`${this.apiBase}/dashboard?action=stats`);
            if (statsResponse.ok) {
                const stats = await statsResponse.json();
                this.updateDashboardStats(stats);
            }
            
            this.loadTodayAppointments();
            this.loadWaitingRoomList();

        } catch (error) {
            console.error('Error loading dashboard:', error);
            this.simulateDashboardData();
        }
    }

    updateDashboardStats(stats) {
        document.getElementById('total-patients').textContent = stats.totalPatients || '0';
        document.getElementById('today-appointments').textContent = stats.todayAppointments || '0';
        document.getElementById('waiting-patients').textContent = stats.waitingPatients || '0';
        document.getElementById('month-revenue').textContent = `${stats.monthRevenue || '0'} €`;
    }

    simulateDashboardData() {
        document.getElementById('total-patients').textContent = '24';
        document.getElementById('today-appointments').textContent = '8';
        document.getElementById('waiting-patients').textContent = '3';
        document.getElementById('month-revenue').textContent = '2,450 €';
    }

    async loadTodayAppointments() {
        try {
            const response = await fetch(`${this.apiBase}/appointments?action=today`);
            
            if (response.ok) {
                const data = await response.json();
                const container = document.getElementById('today-appointments-list');
                
                if (data.records && data.records.length > 0) {
                    container.innerHTML = data.records.map(apt => `
                        <div class="appointment-item">
                            <div class="appointment-header">
                                <strong>${apt.patient_nom} ${apt.patient_prenom}</strong>
                                <span class="appointment-time">${apt.heure}</span>
                            </div>
                            <div class="appointment-type">${apt.type_consultation}</div>
                        </div>
                    `).join('');
                } else {
                    container.innerHTML = '<p class="no-data">Aucun rendez-vous aujourd\'hui</p>';
                }
            }
        } catch (error) {
            console.error('Error loading appointments:', error);
            document.getElementById('today-appointments-list').innerHTML = 
                '<p class="no-data">Erreur de chargement</p>';
        }
    }

    async loadWaitingRoomList() {
        try {
            const response = await fetch(`${this.apiBase}/appointments?action=waiting`);
            
            if (response.ok) {
                const data = await response.json();
                const container = document.getElementById('waiting-room-list');
                
                if (data.records && data.records.length > 0) {
                    container.innerHTML = data.records.map(patient => `
                        <div class="waiting-patient">
                            <div class="waiting-header">
                                <strong>${patient.nom} ${patient.prenom}</strong>
                                <span class="arrival-time">Arrivé à ${patient.heure_arrivee}</span>
                            </div>
                            <div class="waiting-order">Ordre: ${patient.ordre_passage}</div>
                        </div>
                    `).join('');
                } else {
                    container.innerHTML = '<p class="no-data">Aucun patient en attente</p>';
                }
            }
        } catch (error) {
            console.error('Error loading waiting room:', error);
            document.getElementById('waiting-room-list').innerHTML = 
                '<p class="no-data">Erreur de chargement</p>';
        }
    }

    async loadPatients() {
        try {
            const response = await fetch(`${this.apiBase}/patients`);
            
            if (!response.ok) throw new Error('Erreur réseau');
            
            const data = await response.json();
            this.displayPatients(data.records);

        } catch (error) {
            console.error('Error loading patients:', error);
            this.displayPatientsError();
        }
    }

    displayPatients(patients) {
        const tbody = document.getElementById('patients-tbody');
        if (patients && patients.length > 0) {
            tbody.innerHTML = patients.map(patient => `
                <tr>
                    <td>${patient.id}</td>
                    <td>${patient.nom} ${patient.prenom}</td>
                    <td>${this.formatDate(patient.date_naissance)}</td>
                    <td>${patient.telephone}</td>
                    <td>${patient.assurance_compagnie}</td>
                    <td class="actions">
                        <button class="btn btn-primary btn-sm" onclick="app.showPatientDetail(${patient.id})">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button class="btn btn-secondary btn-sm" onclick="app.editPatient(${patient.id})">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-danger btn-sm" onclick="app.deletePatient(${patient.id})">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
            `).join('');
        } else {
            tbody.innerHTML = '<tr><td colspan="6" class="no-data">Aucun patient trouvé</td></tr>';
        }
    }

    displayPatientsError() {
        const tbody = document.getElementById('patients-tbody');
        tbody.innerHTML = '<tr><td colspan="6" class="no-data">Erreur de chargement des patients</td></tr>';
    }

    async searchPatients(query) {
        if (query.length < 2) {
            this.loadPatients();
            return;
        }

        try {
            const response = await fetch(`${this.apiBase}/patients?search=${encodeURIComponent(query)}`);
            if (response.ok) {
                const data = await response.json();
                this.displayPatients(data.records);
            }
        } catch (error) {
            console.error('Error searching patients:', error);
        }
    }

    formatDate(dateString) {
        if (!dateString) return 'Non renseigné';
        const date = new Date(dateString);
        return date.toLocaleDateString('fr-FR');
    }

    showPatientForm() {
        document.getElementById('patient-modal-title').textContent = 'Nouveau Patient';
        document.getElementById('patient-form').reset();
        document.getElementById('patient-id').value = '';
        document.getElementById('patient-modal').style.display = 'block';
    }

    closePatientModal() {
        document.getElementById('patient-modal').style.display = 'none';
    }

    async savePatient() {
        const formData = {
            nom: document.getElementById('nom').value,
            prenom: document.getElementById('prenom').value,
            date_naissance: document.getElementById('date_naissance').value,
            sexe: document.getElementById('sexe').value,
            adresse: document.getElementById('adresse').value,
            telephone: document.getElementById('telephone').value,
            email: document.getElementById('email').value,
            assurance_compagnie: document.getElementById('assurance_compagnie').value,
            assurance_numero: document.getElementById('assurance_numero').value,
            assurance_type: document.getElementById('assurance_type').value
        };

        if (!formData.nom || !formData.prenom || !formData.date_naissance || !formData.telephone) {
            alert('Veuillez remplir tous les champs obligatoires (*)');
            return;
        }

        const patientId = document.getElementById('patient-id').value;
        const method = patientId ? 'PUT' : 'POST';
        const url = `${this.apiBase}/patients`;

        if (patientId) {
            formData.id = patientId;
        }

        try {
            const response = await fetch(url, {
                method: method,
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(formData)
            });

            const result = await response.json();
            
            if (response.ok) {
                alert(result.message);
                this.closePatientModal();
                this.loadPatients();
            } else {
                alert('Erreur: ' + result.message);
            }
        } catch (error) {
            console.error('Error saving patient:', error);
            alert('Erreur lors de la sauvegarde du patient');
        }
    }

    async showPatientDetail(patientId) {
        this.currentPatientId = patientId;
        
        try {
            const response = await fetch(`${this.apiBase}/patients?id=${patientId}`);
            
            if (!response.ok) throw new Error('Erreur de chargement');
            
            const data = await response.json();
            
            if (data.patient) {
                document.getElementById('patient-detail-title').textContent = 
                    `Détails - ${data.patient.nom} ${data.patient.prenom}`;
                
                this.loadPatientInfo(data.patient);
                document.getElementById('patient-detail-modal').style.display = 'block';
                this.openPatientTab(event, 'info');
            }
        } catch (error) {
            console.error('Error loading patient details:', error);
            alert('Erreur lors du chargement des détails du patient');
        }
    }

    loadPatientInfo(patient) {
        const container = document.getElementById('patient-info-tab');
        container.innerHTML = `
            <div class="patient-info-grid">
                <div class="info-group">
                    <label>Nom Complet:</label>
                    <span>${patient.nom} ${patient.prenom}</span>
                </div>
                <div class="info-group">
                    <label>Date de Naissance:</label>
                    <span>${this.formatDate(patient.date_naissance)}</span>
                </div>
                <div class="info-group">
                    <label>Sexe:</label>
                    <span>${patient.sexe === 'M' ? 'Masculin' : 'Féminin'}</span>
                </div>
                <div class="info-group">
                    <label>Téléphone:</label>
                    <span>${patient.telephone}</span>
                </div>
                <div class="info-group">
                    <label>Email:</label>
                    <span>${patient.email || 'Non renseigné'}</span>
                </div>
                <div class="info-group">
                    <label>Adresse:</label>
                    <span>${patient.adresse || 'Non renseignée'}</span>
                </div>
                <div class="info-group">
                    <label>Compagnie d'Assurance:</label>
                    <span>${patient.assurance_compagnie || 'Non renseignée'}</span>
                </div>
                <div class="info-group">
                    <label>Numéro d'Assurance:</label>
                    <span>${patient.assurance_numero || 'Non renseigné'}</span>
                </div>
                <div class="info-group">
                    <label>Type d'Assurance:</label>
                    <span>${patient.assurance_type || 'Non renseigné'}</span>
                </div>
            </div>
            <div class="patient-actions">
                <button class="btn btn-primary" onclick="app.editPatient(${patient.id})">
                    <i class="fas fa-edit"></i> Modifier
                </button>
                <button class="btn btn-secondary" onclick="patientsManager.showDiagnosisForm(${patient.id})">
                    <i class="fas fa-stethoscope"></i> Diagnostic
                </button>
                <button class="btn btn-secondary" onclick="patientsManager.showPrescriptionForm(${patient.id})">
                    <i class="fas fa-prescription"></i> Ordonnance
                </button>
            </div>
        `;
    }

    openPatientTab(event, tabName) {
        document.querySelectorAll('.tab-content').forEach(tab => {
            tab.classList.remove('active');
        });
        
        document.querySelectorAll('.tab-button').forEach(button => {
            button.classList.remove('active');
        });
        
        document.getElementById(`patient-${tabName}-tab`).classList.add('active');
        event.target.classList.add('active');

        switch(tabName) {
            case 'diagnosis':
                if (window.patientsManager) {
                    patientsManager.loadPatientDiagnoses(this.currentPatientId);
                }
                break;
            case 'prescriptions':
                if (window.patientsManager) {
                    patientsManager.loadPatientPrescriptions(this.currentPatientId);
                }
                break;
        }
    }

    closePatientDetailModal() {
        document.getElementById('patient-detail-modal').style.display = 'none';
        this.currentPatientId = null;
    }

    async editPatient(patientId) {
        try {
            const response = await fetch(`${this.apiBase}/patients?id=${patientId}`);
            
            if (!response.ok) throw new Error('Erreur de chargement');
            
            const data = await response.json();
            
            if (data.patient) {
                const patient = data.patient;
                document.getElementById('patient-modal-title').textContent = 'Modifier Patient';
                document.getElementById('patient-id').value = patient.id;
                document.getElementById('nom').value = patient.nom;
                document.getElementById('prenom').value = patient.prenom;
                document.getElementById('date_naissance').value = patient.date_naissance;
                document.getElementById('sexe').value = patient.sexe;
                document.getElementById('adresse').value = patient.adresse || '';
                document.getElementById('telephone').value = patient.telephone;
                document.getElementById('email').value = patient.email || '';
                document.getElementById('assurance_compagnie').value = patient.assurance_compagnie || '';
                document.getElementById('assurance_numero').value = patient.assurance_numero || '';
                document.getElementById('assurance_type').value = patient.assurance_type || '';
                
                document.getElementById('patient-modal').style.display = 'block';
            }
        } catch (error) {
            console.error('Error loading patient for edit:', error);
            alert('Erreur lors du chargement du patient');
        }
    }

    async deletePatient(patientId) {
        if (!confirm('Êtes-vous sûr de vouloir supprimer ce patient ? Cette action est irréversible.')) {
            return;
        }

        try {
            const response = await fetch(`${this.apiBase}/patients`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    id: patientId
                })
            });

            const result = await response.json();
            
            if (response.ok) {
                alert(result.message);
                this.loadPatients();
            } else {
                alert('Erreur: ' + result.message);
            }
        } catch (error) {
            console.error('Error deleting patient:', error);
            alert('Erreur lors de la suppression du patient');
        }
    }

    // Other section loaders
    async loadAppointments() {
        console.log('Loading appointments...');
        // Implementation
    }

    async loadWaitingRoom() {
        console.log('Loading waiting room...');
        // Implementation
    }

    async loadOperations() {
        console.log('Loading operations...');
        // Implementation
    }

    async loadCertificates() {
        console.log('Loading certificates...');
        // Implementation
    }

    async loadFinance() {
        console.log('Loading finance...');
        // Implementation
    }

    async loadReports() {
        console.log('Loading reports...');
        // Implementation
    }

    showError(message) {
        alert('Erreur: ' + message);
    }

    showSuccess(message) {
        alert('Succès: ' + message);
    }
}

// Global functions
function showPatientForm() {
    app.showPatientForm();
}

function closePatientModal() {
    app.closePatientModal();
}

function savePatient() {
    app.savePatient();
}

function openPatientTab(event, tabName) {
    app.openPatientTab(event, tabName);
}

function closePatientDetailModal() {
    app.closePatientDetailModal();
}

// Initialize app
document.addEventListener('DOMContentLoaded', () => {
    window.app = new TraumaSoftApp();
});