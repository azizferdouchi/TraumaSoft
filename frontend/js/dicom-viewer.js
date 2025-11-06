class DICOMViewer {
    constructor() {
        this.canvas = document.getElementById('dicom-canvas');
        this.ctx = this.canvas.getContext('2d');
        this.currentImage = null;
        this.zoom = 1;
        this.panX = 0;
        this.panY = 0;
        this.isPanning = false;
        this.lastX = 0;
        this.lastY = 0;
        this.isMeasuring = false;
        this.measurePoints = [];
        this.init();
    }

    init() {
        this.setupEventListeners();
        this.setupCanvas();
    }

    setupEventListeners() {
        // Zoom avec la molette de la souris
        this.canvas.addEventListener('wheel', (e) => {
            e.preventDefault();
            const zoomFactor = e.deltaY > 0 ? 0.9 : 1.1;
            this.zoom *= zoomFactor;
            this.render();
        });

        // Pan avec drag
        this.canvas.addEventListener('mousedown', (e) => {
            if (this.isMeasuring) return;
            this.isPanning = true;
            this.lastX = e.offsetX;
            this.lastY = e.offsetY;
        });

        this.canvas.addEventListener('mousemove', (e) => {
            if (this.isPanning && !this.isMeasuring) {
                this.panX += e.offsetX - this.lastX;
                this.panY += e.offsetY - this.lastY;
                this.lastX = e.offsetX;
                this.lastY = e.offsetY;
                this.render();
            }
        });

        this.canvas.addEventListener('mouseup', () => {
            this.isPanning = false;
        });

        this.canvas.addEventListener('mouseleave', () => {
            this.isPanning = false;
        });

        // Mesure avec clic
        this.canvas.addEventListener('click', (e) => {
            if (!this.isMeasuring) return;
            
            const rect = this.canvas.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;

            this.measurePoints.push({ x, y });
            
            if (this.measurePoints.length === 2) {
                this.drawMeasurement();
                this.isMeasuring = false;
            }
        });
    }

    setupCanvas() {
        this.canvas.width = 800;
        this.canvas.height = 600;
        this.render();
    }

    async loadDICOMImage(file) {
        // Simulation du chargement DICOM
        // En production, utiliser une librairie comme Cornerstone.js
        const img = new Image();
        img.onload = () => {
            this.currentImage = img;
            this.resetView();
            this.render();
        };
        img.src = URL.createObjectURL(file);
    }

    render() {
        this.ctx.clearRect(0, 0, this.canvas.width, this.canvas.height);
        
        if (!this.currentImage) {
            this.ctx.fillStyle = '#f0f0f0';
            this.ctx.fillRect(0, 0, this.canvas.width, this.canvas.height);
            this.ctx.fillStyle = '#666';
            this.ctx.textAlign = 'center';
            this.ctx.fillText('Aucune image chargée', this.canvas.width / 2, this.canvas.height / 2);
            return;
        }

        this.ctx.save();
        this.ctx.translate(this.panX, this.panY);
        this.ctx.scale(this.zoom, this.zoom);
        
        const x = (this.canvas.width / 2 / this.zoom) - (this.currentImage.width / 2) - (this.panX / this.zoom);
        const y = (this.canvas.height / 2 / this.zoom) - (this.currentImage.height / 2) - (this.panY / this.zoom);
        
        this.ctx.drawImage(this.currentImage, x, y);
        this.ctx.restore();

        // Redessiner les mesures
        if (this.measurePoints.length > 0) {
            this.drawMeasurement();
        }
    }

    zoomIn() {
        this.zoom *= 1.2;
        this.render();
    }

    zoomOut() {
        this.zoom /= 1.2;
        this.render();
    }

    resetView() {
        this.zoom = 1;
        this.panX = 0;
        this.panY = 0;
        this.measurePoints = [];
        this.render();
    }

    measureDistance() {
        this.isMeasuring = true;
        this.measurePoints = [];
        alert('Cliquez sur deux points pour mesurer la distance');
    }

    drawMeasurement() {
        if (this.measurePoints.length < 2) return;

        const p1 = this.measurePoints[0];
        const p2 = this.measurePoints[1];

        // Calcul de la distance en pixels
        const dx = p2.x - p1.x;
        const dy = p2.y - p1.y;
        const distancePixels = Math.sqrt(dx * dx + dy * dy);

        // Conversion en mm (hypothétique - en réalité dépend de l'échelle DICOM)
        const distanceMM = (distancePixels * 0.264).toFixed(2); // 0.264 mm/pixel typique

        // Dessiner la ligne de mesure
        this.ctx.strokeStyle = '#ff0000';
        this.ctx.lineWidth = 2;
        this.ctx.beginPath();
        this.ctx.moveTo(p1.x, p1.y);
        this.ctx.lineTo(p2.x, p2.y);
        this.ctx.stroke();

        // Dessiner les points
        this.ctx.fillStyle = '#ff0000';
        this.ctx.beginPath();
        this.ctx.arc(p1.x, p1.y, 4, 0, Math.PI * 2);
        this.ctx.arc(p2.x, p2.y, 4, 0, Math.PI * 2);
        this.ctx.fill();

        // Afficher la distance
        this.ctx.fillStyle = '#ff0000';
        this.ctx.font = '14px Arial';
        this.ctx.fillText(`${distanceMM} mm`, (p1.x + p2.x) / 2, (p1.y + p2.y) / 2 - 10);
    }

    async uploadDICOM(patientId, file) {
        const formData = new FormData();
        formData.append('patient_id', patientId);
        formData.append('dicom_file', file);
        formData.append('type_examen', document.getElementById('dicom-exam-type').value);
        formData.append('date_examen', document.getElementById('dicom-exam-date').value);
        formData.append('notes', document.getElementById('dicom-notes').value);

        try {
            const response = await fetch(`${this.app.apiBase}/controllers/DICOMController.php`, {
                method: 'POST',
                body: formData
            });

            const result = await response.json();
            
            if (response.ok) {
                alert('Image DICOM uploadée avec succès');
                this.loadPatientDICOM(patientId);
            } else {
                alert('Erreur: ' + result.message);
            }
        } catch (error) {
            console.error('Error uploading DICOM:', error);
            alert('Erreur lors de l\'upload de l\'image DICOM');
        }
    }

    async loadPatientDICOM(patientId) {
        try {
            const response = await fetch(`${this.app.apiBase}/controllers/DICOMController.php?patient_id=${patientId}`);
            const data = await response.json();
            
            const container = document.getElementById('dicom-list');
            container.innerHTML = data.records && data.records.length ? 
                data.records.map(dicom => `
                    <div class="dicom-item card" onclick="dicomViewer.loadDICOMFromServer(${dicom.id})">
                        <div class="dicom-info">
                            <strong>${dicom.type_examen}</strong>
                            <span>${this.app.formatDate(dicom.date_examen)}</span>
                        </div>
                        <div class="dicom-actions">
                            <button class="btn btn-primary btn-sm">
                                <i class="fas fa-eye"></i> Voir
                            </button>
                        </div>
                    </div>
                `).join('') :
                '<p class="no-data">Aucune image DICOM</p>';

        } catch (error) {
            console.error('Error loading DICOM images:', error);
        }
    }
}

// Initialisation
document.addEventListener('DOMContentLoaded', () => {
    window.dicomViewer = new DICOMViewer();
});