class ReportManager {
    constructor() {
        this.init();
    }

    init() {
        this.setupEventListeners();
    }

    setupEventListeners() {
        // Generate report button
        document.getElementById('generateReportBtn').addEventListener('click', () => {
            this.generateReport();
        });
    }

    async generateReport() {
        const reportType = document.getElementById('reportType').value;
        const startDate = document.getElementById('reportStartDate').value;
        const endDate = document.getElementById('reportEndDate').value;
        const format = document.getElementById('reportFormat').value;

        try {
            showLoading();
            const response = await fetch(`${API_BASE}/controllers/ReportController.php?type=${reportType}&start=${startDate}&end=${endDate}`);
            const data = await response.json();

            this.displayReport(data, reportType);

        } catch (error) {
            console.error('Error generating report:', error);
            showAlert('Erreur lors de la génération du rapport', 'danger');
        } finally {
            hideLoading();
        }
    }

    displayReport(data, reportType) {
        const reportTitle = document.getElementById('reportTitle');
        const reportContent = document.getElementById('reportContent');

        switch(reportType) {
            case 'monthly':
                reportTitle.textContent = 'Rapport d\'activité mensuel';
                reportContent.innerHTML = this.renderMonthlyReport(data);
                break;
            case 'patients':
                reportTitle.textContent = 'Rapport des patients';
                reportContent.innerHTML = this.renderPatientsReport(data);
                break;
            case 'finance':
                reportTitle.textContent = 'Rapport financier';
                reportContent.innerHTML = this.renderFinanceReport(data);
                break;
            case 'operations':
                reportTitle.textContent = 'Rapport des opérations';
                reportContent.innerHTML = this.renderOperationsReport(data);
                break;
            default:
                reportContent.innerHTML = '<p class="text-danger">Type de rapport non supporté</p>';
        }
    }

    renderMonthlyReport(data) {
        return `
            <div class="report-chart">
                <h4>${data.periode}</h4>
                <div class="row mt-4">
                    <div class="col-md-3">
                        <div class="stat-card primary">
                            <div class="number">${data.total_patients}</div>
                            <div class="label">Patients total</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card success">
                            <div class="number">${data.rendez_vous}</div>
                            <div class="label">Rendez-vous</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card warning">
                            <div class="number">${data.operations}</div>
                            <div class="label">Opérations</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card info">
                            <div class="number">${formatCurrency(data.chiffre_affaires)}</div>
                            <div class="label">Chiffre d'affaires</div>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }

    renderPatientsReport(data) {
        if (!data.patients || data.patients.length === 0) {
            return '<p class="text-muted">Aucun patient trouvé</p>';
        }

        return `
            <div class="report-chart">
                <h4>Liste des patients (${data.total})</h4>
                <div class="table-responsive mt-3">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Nom</th>
                                <th>Prénom</th>
                                <th>Date de naissance</th>
                                <th>Téléphone</th>
                                <th>Email</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${data.patients.map(patient => `
                                <tr>
                                    <td>${patient.nom}</td>
                                    <td>${patient.prenom}</td>
                                    <td>${formatDate(patient.date_naissance)}</td>
                                    <td>${patient.telephone || '-'}</td>
                                    <td>${patient.email || '-'}</td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                </div>
            </div>
        `;
    }

    renderFinanceReport(data) {
        return `
            <div class="report-chart">
                <h4>Rapport financier</h4>
                <div class="row mt-4">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">Aperçu général</div>
                            <div class="card-body">
                                <p><strong>Total des revenus :</strong> ${formatCurrency(data.total_revenus)}</p>
                                <p><strong>Nombre de paiements :</strong> ${data.nombre_paiements}</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">Répartition par type</div>
                            <div class="card-body">
                                ${data.repartition ? data.repartition.map(stat => `
                                    <p><strong>${this.getPaymentTypeText(stat.type_paiement)} :</strong> 
                                    ${formatCurrency(stat.total)} (${stat.nombre} opérations)</p>
                                `).join('') : '<p class="text-muted">Aucune donnée</p>'}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }

    renderOperationsReport(data) {
        // Cette fonction serait similaire aux autres, mais pour les opérations
        return `
            <div class="report-chart">
                <h4>Rapport des opérations</h4>
                <p class="text-muted">Fonctionnalité à implémenter complètement</p>
            </div>
        `;
    }

    getPaymentTypeText(type) {
        const texts = {
            'espèces': 'Espèces',
            'chèque': 'Chèque',
            'virement': 'Virement',
            'carte': 'Carte'
        };
        return texts[type] || type;
    }
}

// Initialize report manager
const reportManager = new ReportManager();