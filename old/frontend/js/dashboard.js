// js/dashboard.js
class DashboardManager {
    constructor() {
        this.init();
    }

    init() {
        this.loadDashboardData();
        this.setupEventListeners();
    }

    setupEventListeners() {
        // Refresh button
        document.getElementById('refreshDashboardBtn').addEventListener('click', () => {
            this.loadDashboardData();
        });
    }

    async loadDashboardData() {
        try {
            showLoading();
            
            // Load dashboard stats
            const statsData = await apiCall('controllers/DashboardController.php');
            
            // Load today's appointments
            const appointmentsData = await apiCall('controllers/AppointmentController.php?today=1');
            
            // Load upcoming operations
            const operationsData = await apiCall('controllers/OperationController.php');

            this.updateDashboardStats(statsData);
            this.renderTodayAppointments(appointmentsData.appointments || []);
            this.renderUpcomingOperations(operationsData.operations || []);

        } catch (error) {
            console.error('Error loading dashboard data:', error);
            showAlert('Erreur lors du chargement du tableau de bord', 'danger');
        } finally {
            hideLoading();
        }
    }

    updateDashboardStats(stats) {
        document.getElementById('stats-patients').textContent = stats.total_patients || '0';
        document.getElementById('stats-appointments-today').textContent = stats.today_appointments || '0';
        document.getElementById('stats-waiting').textContent = stats.waiting_appointments || '0';
        document.getElementById('stats-revenue').textContent = formatCurrency(stats.chiffre_affaires || 0);
    }

    renderTodayAppointments(appointments) {
        const tbody = document.querySelector('#todayAppointmentsTable tbody');
        if (!tbody) return;

        if (appointments.length === 0) {
            tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted">Aucun rendez-vous aujourd\'hui</td></tr>';
            return;
        }

        tbody.innerHTML = appointments.map(appointment => `
            <tr>
                <td>${new Date(appointment.date_rdv).toLocaleTimeString('fr-FR', {hour: '2-digit', minute:'2-digit'})}</td>
                <td>${appointment.patient_nom} ${appointment.patient_prenom}</td>
                <td>${appointment.motif}</td>
                <td><span class="badge bg-${this.getAppointmentStatusBadge(appointment.statut)}">${this.getAppointmentStatusText(appointment.statut)}</span></td>
                <td>
                    ${appointment.statut === 'planifié' ? `
                        <button class="btn btn-sm btn-outline-warning" onclick="dashboardManager.startAppointment(${appointment.id})">
                            <i class="fas fa-play"></i> Démarrer
                        </button>
                    ` : ''}
                    ${appointment.statut === 'en_cours' ? `
                        <button class="btn btn-sm btn-outline-success" onclick="dashboardManager.completeAppointment(${appointment.id})">
                            <i class="fas fa-check"></i> Terminer
                        </button>
                    ` : ''}
                </td>
            </tr>
        `).join('');
    }

    renderUpcomingOperations(operations) {
        const container = document.getElementById('upcomingOperationsList');
        if (!container) return;

        const upcomingOperations = operations.filter(op => 
            op.statut === 'planifiée' && new Date(op.date_operation) >= new Date()
        ).slice(0, 5);

        if (upcomingOperations.length === 0) {
            container.innerHTML = '<p class="text-muted">Aucune opération planifiée</p>';
            return;
        }

        container.innerHTML = upcomingOperations.map(operation => `
            <div class="waiting-room-item">
                <div class="d-flex justify-content-between">
                    <h6>${operation.patient_nom} ${operation.patient_prenom}</h6>
                    <span class="badge bg-secondary">${formatDate(operation.date_operation)}</span>
                </div>
                <p class="mb-1">${operation.type_operation}</p>
                <small class="text-muted">${operation.lieu} - ${operation.duree_minutes} min</small>
            </div>
        `).join('');
    }

    async startAppointment(appointmentId) {
        try {
            showLoading();
            // Simuler le démarrage
            showAlert('Rendez-vous démarré', 'success');
            this.loadDashboardData();
        } catch (error) {
            console.error('Error starting appointment:', error);
            showAlert('Erreur lors du démarrage du rendez-vous', 'danger');
        } finally {
            hideLoading();
        }
    }

    async completeAppointment(appointmentId) {
        try {
            showLoading();
            // Simuler la finalisation
            showAlert('Rendez-vous terminé', 'success');
            this.loadDashboardData();
        } catch (error) {
            console.error('Error completing appointment:', error);
            showAlert('Erreur lors de la finalisation du rendez-vous', 'danger');
        } finally {
            hideLoading();
        }
    }

    getAppointmentStatusBadge(status) {
        const badges = {
            'planifié': 'secondary',
            'confirmé': 'info',
            'en_cours': 'warning',
            'terminé': 'success',
            'annulé': 'danger'
        };
        return badges[status] || 'secondary';
    }

    getAppointmentStatusText(status) {
        const texts = {
            'planifié': 'Planifié',
            'confirmé': 'Confirmé',
            'en_cours': 'En cours',
            'terminé': 'Terminé',
            'annulé': 'Annulé'
        };
        return texts[status] || status;
    }
}

// Initialize dashboard manager
const dashboardManager = new DashboardManager();