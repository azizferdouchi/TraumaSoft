class FinanceManager {
    constructor() {
        this.init();
    }

    init() {
        this.loadPayments();
        this.setupEventListeners();
        this.loadPatientsForSelect();
    }

    setupEventListeners() {
        // Payment form submission
        document.getElementById('paymentForm').addEventListener('submit', (e) => {
            e.preventDefault();
            this.savePayment();
        });
    }

    async loadPayments() {
        try {
            showLoading();
            const response = await fetch(`${API_BASE}/controllers/FinanceController.php`);
            const data = await response.json();
            
            if (data.payments) {
                this.renderPayments(data.payments);
                this.updateFinanceStats(data.payments);
            }

            // Load stats
            const statsResponse = await fetch(`${API_BASE}/controllers/FinanceController.php?stats=1`);
            const statsData = await statsResponse.json();
            if (statsData.stats) {
                this.renderPaymentStats(statsData.stats);
            }
        } catch (error) {
            console.error('Error loading payments:', error);
            showAlert('Erreur lors du chargement des données financières', 'danger');
        } finally {
            hideLoading();
        }
    }

    renderPayments(payments) {
        const tbody = document.querySelector('#paymentsTable tbody');
        if (!tbody) return;

        tbody.innerHTML = payments.map(payment => `
            <tr>
                <td>${payment.id}</td>
                <td>${payment.patient_nom} ${payment.patient_prenom}</td>
                <td>${formatDate(payment.date_paiement)}</td>
                <td>${formatCurrency(payment.montant)}</td>
                <td><span class="badge bg-${this.getPaymentTypeBadge(payment.type_paiement)}">${this.getPaymentTypeText(payment.type_paiement)}</span></td>
                <td><span class="badge bg-${payment.mode_reglement === 'caisse' ? 'warning' : 'info'}">${payment.mode_reglement === 'caisse' ? 'Caisse' : 'Banque'}</span></td>
                <td>${payment.reference || '-'}</td>
                <td>
                    <button class="btn btn-sm btn-outline-primary" onclick="financeManager.viewPayment(${payment.id})">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-danger" onclick="financeManager.deletePayment(${payment.id})">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>
        `).join('');
    }

    updateFinanceStats(payments) {
        const totalCaisse = payments
            .filter(p => p.mode_reglement === 'caisse')
            .reduce((sum, p) => sum + parseFloat(p.montant), 0);
            
        const totalBanque = payments
            .filter(p => p.mode_reglement === 'banque')
            .reduce((sum, p) => sum + parseFloat(p.montant), 0);

        const totalGeneral = totalCaisse + totalBanque;

        // Stats du mois en cours
        const currentMonth = new Date().toISOString().slice(0, 7);
        const monthlyPayments = payments.filter(p => p.date_paiement.startsWith(currentMonth));
        const totalMois = monthlyPayments.reduce((sum, p) => sum + parseFloat(p.montant), 0);

        document.getElementById('totalCaisse').textContent = formatCurrency(totalCaisse);
        document.getElementById('totalBanque').textContent = formatCurrency(totalBanque);
        document.getElementById('totalGeneral').textContent = formatCurrency(totalGeneral);
        document.getElementById('totalMois').textContent = formatCurrency(totalMois);
    }

    renderPaymentStats(stats) {
        const container = document.getElementById('paymentMethodsBreakdown');
        if (!container) return;

        container.innerHTML = stats.map(stat => `
            <div class="col-md-6 mb-3">
                <div class="payment-method-card ${this.getPaymentMethodClass(stat.type_paiement)}">
                    <h5>${this.getPaymentTypeText(stat.type_paiement)}</h5>
                    <h3>${formatCurrency(parseFloat(stat.total))}</h3>
                    <p>${stat.nombre} opération(s)</p>
                    <small>${stat.mode_reglement === 'caisse' ? 'Caisse' : 'Banque'}</small>
                </div>
            </div>
        `).join('');
    }

    async loadPatientsForSelect() {
        await traumaSoftApp.loadPatientsForSelect('paymentPatientId');
    }

    async savePayment() {
        const form = document.getElementById('paymentForm');
        const formData = new FormData(form);
        
        const paymentData = {
            patient_id: document.getElementById('paymentPatientId').value,
            date_paiement: document.getElementById('paymentDate').value,
            montant: document.getElementById('paymentMontant').value,
            type_paiement: document.getElementById('paymentType').value,
            mode_reglement: document.getElementById('paymentMode').value,
            reference: document.getElementById('paymentReference').value,
            statut: 'réglé'
        };

        try {
            showLoading();
            const response = await fetch(`${API_BASE}/controllers/FinanceController.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(paymentData)
            });

            const result = await response.json();

            if (response.ok) {
                showAlert(result.message, 'success');
                form.reset();
                document.getElementById('paymentDate').value = new Date().toISOString().split('T')[0];
                this.loadPayments();
            } else {
                showAlert(result.message, 'danger');
            }
        } catch (error) {
            console.error('Error saving payment:', error);
            showAlert('Erreur lors de la sauvegarde du paiement', 'danger');
        } finally {
            hideLoading();
        }
    }

    viewPayment(paymentId) {
        showAlert(`Visualisation du paiement #${paymentId}`, 'info');
    }

    async deletePayment(paymentId) {
        if (!confirm('Êtes-vous sûr de vouloir supprimer ce paiement ?')) {
            return;
        }

        try {
            showLoading();
            const response = await fetch(`${API_BASE}/controllers/FinanceController.php`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ id: paymentId })
            });

            const result = await response.json();

            if (response.ok) {
                showAlert(result.message, 'success');
                this.loadPayments();
            } else {
                showAlert(result.message, 'danger');
            }
        } catch (error) {
            console.error('Error deleting payment:', error);
            showAlert('Erreur lors de la suppression du paiement', 'danger');
        } finally {
            hideLoading();
        }
    }

    getPaymentTypeBadge(type) {
        const badges = {
            'espèces': 'success',
            'chèque': 'primary',
            'virement': 'info',
            'carte': 'warning'
        };
        return badges[type] || 'secondary';
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

    getPaymentMethodClass(type) {
        const classes = {
            'espèces': 'cash',
            'chèque': 'check',
            'virement': 'transfer',
            'carte': 'card'
        };
        return classes[type] || '';
    }
}

// Initialize finance manager
const financeManager = new FinanceManager();