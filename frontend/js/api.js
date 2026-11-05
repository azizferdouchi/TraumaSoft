/**
 * Module API pour TraumaSoft
 * Gère toutes les communications avec le backend
 */

class TraumaSoftAPI {
    constructor() {
        this.baseUrl = window.location.origin + '/traumasoft/backend';
        this.token = localStorage.getItem('traumasoft_token');
        this.user = JSON.parse(localStorage.getItem('traumasoft_user'));
    }

    /**
     * Configurer l'URL de base
     */
    setBaseUrl(url) {
        this.baseUrl = url;
    }

    /**
     * Définir le token d'authentification
     */
    setToken(token) {
        this.token = token;
        localStorage.setItem('traumasoft_token', token);
    }

    /**
     * Définir l'utilisateur actuel
     */
    setUser(user) {
        this.user = user;
        localStorage.setItem('traumasoft_user', JSON.stringify(user));
    }

    /**
     * Effacer les informations d'authentification
     */
    clearAuth() {
        this.token = null;
        this.user = null;
        localStorage.removeItem('traumasoft_token');
        localStorage.removeItem('traumasoft_user');
    }

    /**
     * Vérifier si l'utilisateur est connecté
     */
    isAuthenticated() {
        return !!this.token && !!this.user;
    }

    /**
     * Obtenir les headers par défaut
     */
    getDefaultHeaders() {
        const headers = {
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        };

        if (this.token) {
            headers['Authorization'] = `Bearer ${this.token}`;
        }

        return headers;
    }

    /**
     * Effectuer une requête HTTP
     */
    async request(method, endpoint, data = null, headers = {}) {
        const url = `${this.baseUrl}${endpoint}`;
        const options = {
            method: method.toUpperCase(),
            headers: {
                ...this.getDefaultHeaders(),
                ...headers
            }
        };

        if (data && (method.toUpperCase() === 'POST' || method.toUpperCase() === 'PUT')) {
            options.body = JSON.stringify(data);
        }

        try {
            const response = await fetch(url, options);
            
            let responseData;
            try {
                responseData = await response.json();
            } catch (e) {
                // Si la réponse n'est pas du JSON
                responseData = { message: await response.text() };
            }

            if (!response.ok) {
                // Gérer les erreurs d'authentification
                if (response.status === 401) {
                    this.clearAuth();
                    window.location.href = '/login.html';
                }

                throw new Error(responseData.message || 'Erreur inconnue');
            }

            return responseData;
        } catch (error) {
            console.error('Erreur API:', error);
            throw error;
        }
    }

    // ====================
    // Méthodes d'authentification
    // ====================

    /**
     * Connexion
     */
    async login(email, password) {
        const response = await this.request('POST', '/auth/login', { email, mot_de_passe: password });
        
        if (response.success) {
            this.setToken(response.data.tokens.access_token);
            this.setUser(response.data.user);
        }

        return response;
    }

    /**
     * Enregistrement
     */
    async register(userData) {
        const response = await this.request('POST', '/auth/register', userData);
        
        if (response.success) {
            this.setToken(response.data.access_token);
            this.setUser(response.data.user);
        }

        return response;
    }

    /**
     * Déconnexion
     */
    async logout() {
        try {
            await this.request('POST', '/auth/logout');
        } catch (e) {
            // Ignorer les erreurs de déconnexion
        } finally {
            this.clearAuth();
        }
    }

    /**
     * Rafraîchir le token
     */
    async refreshToken(refreshToken) {
        const response = await this.request('POST', '/auth/refresh', { refresh_token: refreshToken });
        
        if (response.success) {
            this.setToken(response.data.access_token);
        }

        return response;
    }

    // ====================
    // Méthodes pour les patients
    // ====================

    /**
     * Obtenir tous les patients
     */
    async getPatients(params = {}) {
        const queryString = new URLSearchParams(params).toString();
        const endpoint = queryString ? `/patients?${queryString}` : '/patients';
        return this.request('GET', endpoint);
    }

    /**
     * Obtenir un patient spécifique
     */
    async getPatient(id) {
        return this.request('GET', `/patients/${id}`);
    }

    /**
     * Créer un patient
     */
    async createPatient(patientData) {
        return this.request('POST', '/patients', patientData);
    }

    /**
     * Mettre à jour un patient
     */
    async updatePatient(id, patientData) {
        return this.request('PUT', `/patients/${id}`, patientData);
    }

    /**
     * Supprimer un patient
     */
    async deletePatient(id) {
        return this.request('DELETE', `/patients/${id}`);
    }

    /**
     * Rechercher des patients
     */
    async searchPatients(term) {
        return this.request('GET', `/patients/search/${term}`);
    }

    // ====================
    // Méthodes pour les rendez-vous
    // ====================

    /**
     * Obtenir tous les rendez-vous
     */
    async getAppointments(params = {}) {
        const queryString = new URLSearchParams(params).toString();
        const endpoint = queryString ? `/appointments?${queryString}` : '/appointments';
        return this.request('GET', endpoint);
    }

    /**
     * Obtenir un rendez-vous spécifique
     */
    async getAppointment(id) {
        return this.request('GET', `/appointments/${id}`);
    }

    /**
     * Créer un rendez-vous
     */
    async createAppointment(appointmentData) {
        return this.request('POST', '/appointments', appointmentData);
    }

    /**
     * Mettre à jour un rendez-vous
     */
    async updateAppointment(id, appointmentData) {
        return this.request('PUT', `/appointments/${id}`, appointmentData);
    }

    /**
     * Supprimer un rendez-vous
     */
    async deleteAppointment(id) {
        return this.request('DELETE', `/appointments/${id}`);
    }

    /**
     * Obtenir les rendez-vous du jour
     */
    async getTodayAppointments() {
        return this.request('GET', '/appointments/today');
    }

    /**
     * Obtenir les rendez-vous à venir
     */
    async getUpcomingAppointments() {
        return this.request('GET', '/appointments/upcoming');
    }

    // ====================
    // Méthodes pour les diagnostics
    // ====================

    /**
     * Obtenir tous les diagnostics
     */
    async getDiagnoses(params = {}) {
        const queryString = new URLSearchParams(params).toString();
        const endpoint = queryString ? `/diagnoses?${queryString}` : '/diagnoses';
        return this.request('GET', endpoint);
    }

    /**
     * Obtenir un diagnostic spécifique
     */
    async getDiagnosis(id) {
        return this.request('GET', `/diagnoses/${id}`);
    }

    /**
     * Créer un diagnostic
     */
    async createDiagnosis(diagnosisData) {
        return this.request('POST', '/diagnoses', diagnosisData);
    }

    /**
     * Mettre à jour un diagnostic
     */
    async updateDiagnosis(id, diagnosisData) {
        return this.request('PUT', `/diagnoses/${id}`, diagnosisData);
    }

    /**
     * Supprimer un diagnostic
     */
    async deleteDiagnosis(id) {
        return this.request('DELETE', `/diagnoses/${id}`);
    }

    /**
     * Obtenir les diagnostics d'un patient
     */
    async getDiagnosesByPatient(patientId) {
        return this.request('GET', `/diagnoses/patient/${patientId}`);
    }

    // ====================
    // Méthodes pour les prescriptions
    // ====================

    /**
     * Obtenir toutes les prescriptions
     */
    async getPrescriptions(params = {}) {
        const queryString = new URLSearchParams(params).toString();
        const endpoint = queryString ? `/prescriptions?${queryString}` : '/prescriptions';
        return this.request('GET', endpoint);
    }

    /**
     * Obtenir une prescription spécifique
     */
    async getPrescription(id) {
        return this.request('GET', `/prescriptions/${id}`);
    }

    /**
     * Créer une prescription
     */
    async createPrescription(prescriptionData) {
        return this.request('POST', '/prescriptions', prescriptionData);
    }

    /**
     * Mettre à jour une prescription
     */
    async updatePrescription(id, prescriptionData) {
        return this.request('PUT', `/prescriptions/${id}`, prescriptionData);
    }

    /**
     * Supprimer une prescription
     */
    async deletePrescription(id) {
        return this.request('DELETE', `/prescriptions/${id}`);
    }

    /**
     * Obtenir les prescriptions d'un patient
     */
    async getPrescriptionsByPatient(patientId) {
        return this.request('GET', `/prescriptions/patient/${patientId}`);
    }

    // ====================
    // Méthodes pour les opérations
    // ====================

    /**
     * Obtenir toutes les opérations
     */
    async getOperations(params = {}) {
        const queryString = new URLSearchParams(params).toString();
        const endpoint = queryString ? `/operations?${queryString}` : '/operations';
        return this.request('GET', endpoint);
    }

    /**
     * Obtenir une opération spécifique
     */
    async getOperation(id) {
        return this.request('GET', `/operations/${id}`);
    }

    /**
     * Créer une opération
     */
    async createOperation(operationData) {
        return this.request('POST', '/operations', operationData);
    }

    /**
     * Mettre à jour une opération
     */
    async updateOperation(id, operationData) {
        return this.request('PUT', `/operations/${id}`, operationData);
    }

    /**
     * Supprimer une opération
     */
    async deleteOperation(id) {
        return this.request('DELETE', `/operations/${id}`);
    }

    /**
     * Obtenir les opérations à venir
     */
    async getUpcomingOperations() {
        return this.request('GET', '/operations/upcoming');
    }

    // ====================
    // Méthodes pour les certificats
    // ====================

    /**
     * Obtenir tous les certificats
     */
    async getCertificates(params = {}) {
        const queryString = new URLSearchParams(params).toString();
        const endpoint = queryString ? `/certificates?${queryString}` : '/certificates';
        return this.request('GET', endpoint);
    }

    /**
     * Obtenir un certificat spécifique
     */
    async getCertificate(id) {
        return this.request('GET', `/certificates/${id}`);
    }

    /**
     * Créer un certificat
     */
    async createCertificate(certificateData) {
        return this.request('POST', '/certificates', certificateData);
    }

    /**
     * Mettre à jour un certificat
     */
    async updateCertificate(id, certificateData) {
        return this.request('PUT', `/certificates/${id}`, certificateData);
    }

    /**
     * Supprimer un certificat
     */
    async deleteCertificate(id) {
        return this.request('DELETE', `/certificates/${id}`);
    }

    /**
     * Obtenir les certificats d'un patient
     */
    async getCertificatesByPatient(patientId) {
        return this.request('GET', `/certificates/patient/${patientId}`);
    }

    // ====================
    // Méthodes pour la finance
    // ====================

    /**
     * Obtenir tous les paiements
     */
    async getPayments(params = {}) {
        const queryString = new URLSearchParams(params).toString();
        const endpoint = queryString ? `/finance/payments?${queryString}` : '/finance/payments';
        return this.request('GET', endpoint);
    }

    /**
     * Obtenir un paiement spécifique
     */
    async getPayment(id) {
        return this.request('GET', `/finance/payments/${id}`);
    }

    /**
     * Créer un paiement
     */
    async createPayment(paymentData) {
        return this.request('POST', '/finance/payments', paymentData);
    }

    /**
     * Mettre à jour un paiement
     */
    async updatePayment(id, paymentData) {
        return this.request('PUT', `/finance/payments/${id}`, paymentData);
    }

    /**
     * Supprimer un paiement
     */
    async deletePayment(id) {
        return this.request('DELETE', `/finance/payments/${id}`);
    }

    /**
     * Obtenir le rapport de revenus
     */
    async getRevenueReport(params = {}) {
        const queryString = new URLSearchParams(params).toString();
        const endpoint = queryString ? `/finance/revenue?${queryString}` : '/finance/revenue';
        return this.request('GET', endpoint);
    }

    /**
     * Obtenir les paiements en attente
     */
    async getOutstandingPayments() {
        return this.request('GET', '/finance/outstanding');
    }

    // ====================
    // Méthodes pour le tableau de bord
    // ====================

    /**
     * Obtenir les statistiques
     */
    async getStatistics() {
        return this.request('GET', '/dashboard/stats');
    }

    /**
     * Obtenir la salle d'attente
     */
    async getWaitingRoom() {
        return this.request('GET', '/dashboard/waiting-room');
    }

    /**
     * Obtenir le résumé du jour
     */
    async getTodaySummary() {
        return this.request('GET', '/dashboard/today');
    }

    /**
     * Obtenir l'activité récente
     */
    async getRecentActivity() {
        return this.request('GET', '/dashboard/activity');
    }

    // ====================
    // Méthodes pour l'IA
    // ====================

    /**
     * Analyser un diagnostic avec l'IA
     */
    async analyzeDiagnostic(diagnosticId) {
        return this.request('GET', `/ai/analyze/diagnostic/${diagnosticId}`);
    }

    /**
     * Analyser un patient avec l'IA
     */
    async analyzePatient(patientId) {
        return this.request('GET', `/ai/analyze/patient/${patientId}`);
    }

    /**
     * Prédire la durée de récupération
     */
    async predictRecovery(diagnosticId) {
        return this.request('GET', `/ai/predict/recovery/${diagnosticId}`);
    }

    /**
     * Obtenir l'analyse des performances
     */
    async getPerformanceAnalysis(params = {}) {
        const queryString = new URLSearchParams(params).toString();
        const endpoint = queryString ? `/ai/performance?${queryString}` : '/ai/performance';
        return this.request('GET', endpoint);
    }

    /**
     * Optimiser le planning
     */
    async optimizeSchedule() {
        return this.request('GET', '/ai/schedule/optimize');
    }

    /**
     * Obtenir toutes les analyses IA
     */
    async getAllAnalyses(params = {}) {
        const queryString = new URLSearchParams(params).toString();
        const endpoint = queryString ? `/ai/analyses?${queryString}` : '/ai/analyses';
        return this.request('GET', endpoint);
    }

    /**
     * Analyser une image DICOM
     */
    async analyzeDicomImage(imageData, patientId, diagnosticId = null) {
        return this.request('POST', '/ai/analyze/dicom', {
            patient_id: patientId,
            diagnostic_id: diagnosticId,
            image_data: imageData
        });
    }

    /**
     * Créer une analyse IA
     */
    async createAnalysis(diagnosticId) {
        return this.request('POST', '/ai/analyze', { diagnostic_id: diagnosticId });
    }

    /**
     * Obtenir des suggestions IA
     */
    async getAISuggestions(type, data = {}) {
        return this.request('POST', '/ai/suggestions', { type, data });
    }

    // ====================
    // Méthodes utilitaires
    // ====================

    /**
     * Obtenir les informations de l'API
     */
    async getApiInfo() {
        return this.request('GET', '/api');
    }

    /**
     * Ping pour vérifier la connexion
     */
    async ping() {
        return this.request('GET', '/api');
    }
}

// Créer une instance globale
const api = new TraumaSoftAPI();

// Exporter pour les modules
if (typeof module !== 'undefined' && module.exports) {
    module.exports = TraumaSoftAPI;
}
