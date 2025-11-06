class OperationManager {
    constructor() {
        this.init();
    }

    init() {
        this.loadOperations();
        this.setupEventListeners();
        this.loadPatientsForSelect();
    }

    setupEventListeners() {
        // Add operation button
        document.getElementById('addOperationBtn').addEventListener('click', () => {
            this.showOperationModal();
        });

        // Save operation button
        document.getElementById('saveOperationBtn').addEventListener('click', () => {
            this.saveOperation();
        });
    }

    async loadOperations() {
        try {
            showLoading();
            const response = await fetch(`${API_BASE}/controllers/OperationController.php`);
            const data = await response.json();
            
            if (data.operations) {
                this.renderOperations(data.operations);
            }
        } catch (error) {
            console.error('Error loading operations:', error);
            showAlert('Erreur lors du chargement des opérations', 'danger');
        } finally {
            hideLoading();
        }
    }

    renderOperations(operations) {
        const tbody = document.querySelector('#operationsTable tbody');
        if (!tbody) return;

        tbody.innerHTML = operations.map(operation => `
            <tr>
                <td>${formatDateTime(operation.date_operation)}</td>
                <td>${operation.patient_nom} ${operation.patient_prenom}</td>
                <td>${operation.type_operation}</td>
                <td>${operation.lieu}</td>
                <td>${operation.duree_minutes} min</td>
                <td><span class="badge bg-${this.getStatusBadge(operation.statut)}">${this.getStatusText(operation.statut)}</span></td>
                <td>
                    <button class="btn btn-sm btn-outline-primary" onclick="operationManager.viewOperation(${operation.id})">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-success" onclick="operationManager.editOperation(${operation.id})">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-danger" onclick="operationManager.deleteOperation(${operation.id})">
                        <i class="fas fa-trash"></i>
                    </button>
                    ${operation.statut === 'planifiée' ? `
                        <button class="btn btn-sm btn-outline-warning" onclick="operationManager.completeOperation(${operation.id})">
                            <i class="fas fa-check"></i>
                        </button>
                    ` : ''}
                </td>
            </tr>
        `).join('');
    }

    async loadPatientsForSelect() {
        await traumaSoftApp.loadPatientsForSelect('operationPatientId');
    }

    showOperationModal(operationId = null) {
        const modal = new bootstrap.Modal(document.getElementById('operationModal'));
        
        if (operationId) {
            document.getElementById('operationModalTitle').textContent = 'Modifier l\'opération';
            this.loadOperationData(operationId);
        } else {
            document.getElementById('operationModalTitle').textContent = 'Nouvelle opération';
            document.getElementById('operationForm').reset();
            document.getElementById('operationId').value = '';
            document.getElementById('operationDateTime').value = new Date().toISOString().slice(0, 16);
        }
        
        modal.show();
    }

    async loadOperationData(operationId) {
        try {
            showLoading();
            const response = await fetch(`${API_BASE}/controllers/OperationController.php?id=${operationId}`);
            const data = await response.json();
            
            if (data.operations && data.operations.length > 0) {
                const operation = data.operations[0];
                document.getElementById('operationId').value = operation.id;
                document.getElementById('operationPatientId').value = operation.patient_id;
                document.getElementById('operationDateTime').value = operation.date_operation.replace(' ', 'T');
                document.getElementById('operationType').value = operation.type_operation;
                document.getElementById('operationLieu').value = operation.lieu;
                document.getElementById('operationDuree').value = operation.duree_minutes;
                document.getElementById('operationStatut').value = operation.statut;
                document.getElementById('operationNotes').value = operation.notes || '';
            }
        } catch (error) {
            console.error('Error loading operation data:', error);
            showAlert('Erreur lors du chargement des données de l\'opération', 'danger');
        } finally {
            hideLoading();
        }
    }

    async saveOperation() {
        const operationId = document.getElementById('operationId').value;
        const operationData = {
            patient_id: document.getElementById('operationPatientId').value,
            date_operation: document.getElementById('operationDateTime').value.replace('T', ' '),
            type_operation: document.getElementById('operationType').value,
            lieu: document.getElementById('operationLieu').value,
            duree_minutes: document.getElementById('operationDuree').value,
            statut: document.getElementById('operationStatut').value,
            notes: document.getElementById('operationNotes').value
        };

        if (operationId) {
            operationData.id = operationId;
        }

        try {
            showLoading();
            const response = await fetch(`${API_BASE}/controllers/OperationController.php`, {
                method: operationId ? 'PUT' : 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(operationData)
            });

            const result = await response.json();

            if (response.ok) {
                showAlert(result.message, 'success');
                bootstrap.Modal.getInstance(document.getElementById('operationModal')).hide();
                this.loadOperations();
            } else {
                showAlert(result.message, 'danger');
            }
        } catch (error) {
            console.error('Error saving operation:', error);
            showAlert('Erreur lors de la sauvegarde de l\'opération', 'danger');
        } finally {
            hideLoading();
        }
    }

    async completeOperation(operationId) {
        try {
            showLoading();
            const response = await fetch(`${API_BASE}/controllers/OperationController.php`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    id: operationId,
                    statut: 'réalisée'
                })
            });

            const result = await response.json();

            if (response.ok) {
                showAlert('Opération marquée comme réalisée', 'success');
                this.loadOperations();
            } else {
                showAlert(result.message, 'danger');
            }
        } catch (error) {
            console.error('Error completing operation:', error);
            showAlert('Erreur lors de la mise à jour de l\'opération', 'danger');
        } finally {
            hideLoading();
        }
    }

    viewOperation(operationId) {
        showAlert(`Visualisation de l'opération #${operationId}`, 'info');
    }

    editOperation(operationId) {
        this.showOperationModal(operationId);
    }

    async deleteOperation(operationId) {
        if (!confirm('Êtes-vous sûr de vouloir supprimer cette opération ?')) {
            return;
        }

        try {
            showLoading();
            const response = await fetch(`${API_BASE}/controllers/OperationController.php`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ id: operationId })
            });

            const result = await response.json();

            if (response.ok) {
                showAlert(result.message, 'success');
                this.loadOperations();
            } else {
                showAlert(result.message, 'danger');
            }
        } catch (error) {
            console.error('Error deleting operation:', error);
            showAlert('Erreur lors de la suppression de l\'opération', 'danger');
        } finally {
            hideLoading();
        }
    }

    getStatusBadge(status) {
        const badges = {
            'planifiée': 'warning',
            'réalisée': 'success',
            'annulée': 'danger'
        };
        return badges[status] || 'secondary';
    }

    getStatusText(status) {
        const texts = {
            'planifiée': 'Planifiée',
            'réalisée': 'Réalisée',
            'annulée': 'Annulée'
        };
        return texts[status] || status;
    }
}

// Initialize operation manager
const operationManager = new OperationManager();