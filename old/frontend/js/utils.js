// js/utils.js

// Cache le loading spinner au démarrage
function hideLoadingSpinner() {
    const loadingModal = document.getElementById('loadingModal');
    if (loadingModal) {
        const modal = bootstrap.Modal.getInstance(loadingModal);
        if (modal) {
            modal.hide();
        } else {
            // Si le modal n'est pas initialisé, on le cache manuellement
            loadingModal.style.display = 'none';
            document.querySelector('.modal-backdrop')?.remove();
            document.body.classList.remove('modal-open');
        }
    }
}

// Cache le loading spinner au chargement de la page
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(hideLoadingSpinner, 1000); // Sécurité après 1 seconde
});

// S'assure que le loading est caché même si la page est lente
window.addEventListener('load', hideLoadingSpinner);