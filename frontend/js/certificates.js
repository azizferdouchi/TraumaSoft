class CertificateManager {
    constructor() {
        this.init();
    }

    init() {
        this.loadCertificates();
        this.setupEventListeners();
        this.loadPatientsForSelect();
    }

    setupEventListeners() {
        // Certificate form submission
        document.getElementById('certificateForm').addEventListener('submit', (e) => {
            e.preventDefault();
            this.saveCertificate();
        });
    }

    async loadCertificates() {
        try {
            showLoading();
            const response = await fetch(`${API_BASE}/controllers/CertificateController.php`);
            const data = await response.json();
            
            if (data.certificates) {
                this.renderCertificates(data.certificates);
            }
        } catch (error) {
            console.error('Error loading certificates:', error);
            showAlert('Erreur lors du chargement des certificats', 'danger');
        } finally {
            hideLoading();
        }
    }

    renderCertificates(certificates) {
        const tbody = document.querySelector('#certificatesTable tbody');
        if (!tbody) return;

        tbody.innerHTML = certificates.map(certificate => `
            <tr>
                <td>${formatDate(certificate.date_certificat)}</td>
                <td>${certificate.patient_nom} ${certificate.patient_prenom}</td>
                <td>${certificate.diagnostic}</td>
                <td>${certificate.duree_jours} jours</td>
                <td>
                    <button class="btn btn-sm btn-outline-primary" onclick="certificateManager.viewCertificate(${certificate.id})">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-success" onclick="certificateManager.editCertificate(${certificate.id})">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-danger" onclick="certificateManager.deleteCertificate(${certificate.id})">
                        <i class="fas fa-trash"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-info" onclick="certificateManager.printCertificate(${certificate.id})">
                        <i class="fas fa-print"></i>
                    </button>
                </td>
            </tr>
        `).join('');
    }

    async loadPatientsForSelect() {
        await traumaSoftApp.loadPatientsForSelect('certificatePatientId');
    }

    async saveCertificate() {
        const form = document.getElementById('certificateForm');
        const formData = new FormData(form);
        
        const certificateData = {
            patient_id: document.getElementById('certificatePatientId').value,
            date_certificat: document.getElementById('certificateDate').value,
            diagnostic: document.getElementById('certificateDiagnostic').value,
            duree_jours: document.getElementById('certificateDuree').value,
            recommandations: document.getElementById('certificateRecommandations').value
        };

        try {
            showLoading();
            const response = await fetch(`${API_BASE}/controllers/CertificateController.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(certificateData)
            });

            const result = await response.json();

            if (response.ok) {
                showAlert(result.message, 'success');
                form.reset();
                this.loadCertificates();
            } else {
                showAlert(result.message, 'danger');
            }
        } catch (error) {
            console.error('Error saving certificate:', error);
            showAlert('Erreur lors de la sauvegarde du certificat', 'danger');
        } finally {
            hideLoading();
        }
    }

    async viewCertificate(certificateId) {
        try {
            showLoading();
            const response = await fetch(`${API_BASE}/controllers/CertificateController.php?id=${certificateId}`);
            const data = await response.json();
            
            if (data.certificates && data.certificates.length > 0) {
                const certificate = data.certificates[0];
                this.showCertificatePreview(certificate);
            }
        } catch (error) {
            console.error('Error loading certificate:', error);
            showAlert('Erreur lors du chargement du certificat', 'danger');
        } finally {
            hideLoading();
        }
    }

    showCertificatePreview(certificate) {
        const preview = `
            <div class="certificate-preview">
                <div class="certificate-header">
                    <h2>CERTIFICAT MÉDICAL</h2>
                    <p>Dr. Martin - Médecin Traumatologue</p>
                    <p>15 Avenue des Champs-Élysées, 75008 Paris</p>
                    <p>Tél: 01 45 67 89 10 - RPPS: 12345678901</p>
                </div>
                <div class="certificate-body">
                    <p>Je soussigné, Dr. Martin, médecin traumatologue,</p>
                    <p>certifie avoir examiné ce jour <strong>${certificate.patient_nom} ${certificate.patient_prenom}</strong>,</p>
                    <p>né(e) le <strong>${formatDate(certificate.date_naissance)}</strong>,</p>
                    <p>demeurant <strong>${certificate.adresse || 'Non renseigné'}</strong>,</p>
                    <p>et avoir constaté :</p>
                    <p><strong>${certificate.diagnostic}</strong></p>
                    <p>Je préconise un arrêt de travail de <strong>${certificate.duree_jours} jours</strong></p>
                    <p>à compter du <strong>${formatDate(certificate.date_certificat)}</strong>.</p>
                    ${certificate.recommandations ? `<p><strong>Recommandations :</strong> ${certificate.recommandations}</p>` : ''}
                    <div style="margin-top: 60px;">
                        <p>Fait à Paris, le ${formatDate(certificate.date_certificat)}</p>
                        <p>Signature et cachet :</p>
                        <div style="margin-top: 40px; border-bottom: 1px solid #000; width: 300px;"></div>
                    </div>
                </div>
            </div>
        `;

        // Show in modal or new window for printing
        const printWindow = window.open('', '_blank');
        printWindow.document.write(`
            <!DOCTYPE html>
            <html>
            <head>
                <title>Certificat Médical - ${certificate.patient_nom} ${certificate.patient_prenom}</title>
                <style>
                    body { font-family: 'Times New Roman', serif; margin: 40px; }
                    .certificate-header { text-align: center; border-bottom: 2px solid #000; padding-bottom: 20px; margin-bottom: 30px; }
                    .certificate-body { line-height: 1.6; }
                    strong { font-weight: bold; }
                    @media print { body { margin: 0; } }
                </style>
            </head>
            <body>${preview}</body>
            </html>
        `);
        printWindow.document.close();
    }

    printCertificate(certificateId) {
        this.viewCertificate(certificateId);
    }

    editCertificate(certificateId) {
        showAlert(`Modification du certificat #${certificateId} - Fonctionnalité à implémenter`, 'info');
    }

    async deleteCertificate(certificateId) {
        if (!confirm('Êtes-vous sûr de vouloir supprimer ce certificat ?')) {
            return;
        }

        try {
            showLoading();
            const response = await fetch(`${API_BASE}/controllers/CertificateController.php`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ id: certificateId })
            });

            const result = await response.json();

            if (response.ok) {
                showAlert(result.message, 'success');
                this.loadCertificates();
            } else {
                showAlert(result.message, 'danger');
            }
        } catch (error) {
            console.error('Error deleting certificate:', error);
            showAlert('Erreur lors de la suppression du certificat', 'danger');
        } finally {
            hideLoading();
        }
    }
}

// Initialize certificate manager
const certificateManager = new CertificateManager();