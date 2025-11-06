<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TraumaSoft - Gestion de Cabinet de Traumatologie</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary: #2c3e50;
            --secondary: #3498db;
            --accent: #e74c3c;
            --light: #ecf0f1;
            --dark: #2c3e50;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
        }
        
        .sidebar {
            background-color: var(--primary);
            color: white;
            min-height: 100vh;
            padding: 0;
        }
        
        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.8);
            padding: 15px 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            transition: all 0.3s;
        }
        
        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            background-color: rgba(255, 255, 255, 0.1);
            color: white;
        }
        
        .sidebar .nav-link i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }
        
        .main-content {
            padding: 20px;
        }
        
        .header {
            background-color: white;
            padding: 15px 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }
        
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }
        
        .card-header {
            background-color: white;
            border-bottom: 1px solid #eee;
            font-weight: 600;
            padding: 15px 20px;
        }
        
        .patient-card {
            transition: transform 0.2s;
        }
        
        .patient-card:hover {
            transform: translateY(-5px);
        }
        
        .badge-waiting {
            background-color: #f39c12;
        }
        
        .badge-in-consultation {
            background-color: #e74c3c;
        }
        
        .badge-finished {
            background-color: #27ae60;
        }
        
        .btn-primary {
            background-color: var(--secondary);
            border: none;
        }
        
        .btn-primary:hover {
            background-color: #2980b9;
        }
        
        .calendar-day {
            border: 1px solid #eee;
            height: 120px;
            padding: 5px;
            overflow-y: auto;
        }
        
        .calendar-day.today {
            background-color: rgba(52, 152, 219, 0.1);
        }
        
        .appointment-badge {
            font-size: 0.7rem;
            margin-bottom: 2px;
            display: block;
        }
        
        .dicom-viewer {
            background-color: #000;
            height: 400px;
            border-radius: 5px;
            overflow: hidden;
        }
        
        .dicom-tools {
            background-color: #2c3e50;
            padding: 10px;
            border-radius: 5px;
        }
        
        .tool-btn {
            background-color: #34495e;
            color: white;
            border: none;
            border-radius: 5px;
            padding: 5px 10px;
            margin-right: 5px;
            margin-bottom: 5px;
        }
        
        .certificate {
            border: 2px solid #000;
            padding: 20px;
            background-color: white;
            min-height: 400px;
        }
        
        .certificate-header {
            text-align: center;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        
        .prescription-item {
            border-bottom: 1px dashed #ddd;
            padding: 10px 0;
        }
        
        .waiting-room-item {
            border-left: 4px solid var(--secondary);
            padding: 10px 15px;
            margin-bottom: 10px;
            background-color: white;
            border-radius: 5px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        
        .waiting-room-item.urgent {
            border-left-color: var(--accent);
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-2 sidebar">
                <div class="d-flex flex-column flex-shrink-0 p-3">
                    <div class="text-center mb-4 mt-3">
                        <h4>TraumaSoft</h4>
                        <p class="text-muted">Cabinet de Traumatologie</p>
                    </div>
                    <ul class="nav nav-pills flex-column mb-auto">
                        <li class="nav-item">
                            <a href="#" class="nav-link active" data-section="dashboard">
                                <i class="fas fa-tachometer-alt"></i> Tableau de bord
                            </a>
                        </li>
                        <li>
                            <a href="#" class="nav-link" data-section="patients">
                                <i class="fas fa-user-injured"></i> Patients
                            </a>
                        </li>
                        <li>
                            <a href="#" class="nav-link" data-section="waiting-room">
                                <i class="fas fa-clock"></i> Salle d'attente
                            </a>
                        </li>
                        <li>
                            <a href="#" class="nav-link" data-section="agenda">
                                <i class="fas fa-calendar-alt"></i> Agenda
                            </a>
                        </li>
                        <li>
                            <a href="#" class="nav-link" data-section="operations">
                                <i class="fas fa-procedures"></i> Opérations
                            </a>
                        </li>
                        <li>
                            <a href="#" class="nav-link" data-section="certificates">
                                <i class="fas fa-file-medical"></i> Certificats
                            </a>
                        </li>
                        <li>
                            <a href="#" class="nav-link" data-section="reports">
                                <i class="fas fa-chart-bar"></i> Rapports
                            </a>
                        </li>
                    </ul>
                    <hr>
                    <div class="dropdown">
                        <a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle" id="dropdownUser1" data-bs-toggle="dropdown" aria-expanded="false">
                            <img src="https://github.com/mdo.png" alt="" width="32" height="32" class="rounded-circle me-2">
                            <strong>Dr. Dupont</strong>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-dark text-small shadow" aria-labelledby="dropdownUser1">
                            <li><a class="dropdown-item" href="#">Profil</a></li>
                            <li><a class="dropdown-item" href="#">Paramètres</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="#">Déconnexion</a></li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-10 main-content">
                <!-- Header -->
                <div class="header d-flex justify-content-between align-items-center">
                    <h3 id="section-title">Tableau de bord</h3>
                    <div>
                        <button class="btn btn-primary">
                            <i class="fas fa-plus"></i> Nouveau patient
                        </button>
                    </div>
                </div>

                <!-- Dashboard Section -->
                <div id="dashboard-section" class="section-content">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="card text-center">
                                <div class="card-body">
                                    <h1 class="display-4 text-primary">12</h1>
                                    <p class="card-text">Patients aujourd'hui</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card text-center">
                                <div class="card-body">
                                    <h1 class="display-4 text-warning">5</h1>
                                    <p class="card-text">En salle d'attente</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card text-center">
                                <div class="card-body">
                                    <h1 class="display-4 text-success">3</h1>
                                    <p class="card-text">Rendez-vous restants</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card text-center">
                                <div class="card-body">
                                    <h1 class="display-4 text-info">2</h1>
                                    <p class="card-text">Opérations cette semaine</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-8">
                            <div class="card">
                                <div class="card-header">
                                    <i class="fas fa-calendar-day"></i> Agenda du jour
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Heure</th>
                                                    <th>Patient</th>
                                                    <th>Motif</th>
                                                    <th>Statut</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td>09:00</td>
                                                    <td>Martin Jean</td>
                                                    <td>Fracture radius contrôle</td>
                                                    <td><span class="badge bg-success">Terminé</span></td>
                                                </tr>
                                                <tr>
                                                    <td>09:30</td>
                                                    <td>Dubois Marie</td>
                                                    <td>Entorse cheville</td>
                                                    <td><span class="badge bg-success">Terminé</span></td>
                                                </tr>
                                                <tr>
                                                    <td>10:00</td>
                                                    <td>Leroy Pierre</td>
                                                    <td>Lombalgie aiguë</td>
                                                    <td><span class="badge bg-warning">En cours</span></td>
                                                </tr>
                                                <tr>
                                                    <td>10:30</td>
                                                    <td>Moreau Sophie</td>
                                                    <td>Fracture clavicule</td>
                                                    <td><span class="badge bg-secondary">À venir</span></td>
                                                </tr>
                                                <tr>
                                                    <td>11:00</td>
                                                    <td>Petit Luc</td>
                                                    <td>Tendinite épaule</td>
                                                    <td><span class="badge bg-secondary">À venir</span></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-header">
                                    <i class="fas fa-user-injured"></i> Salle d'attente
                                </div>
                                <div class="card-body">
                                    <div class="waiting-room-item">
                                        <div class="d-flex justify-content-between">
                                            <h6>Leroy Pierre</h6>
                                            <span class="badge bg-warning">10:00</span>
                                        </div>
                                        <p class="mb-1">Lombalgie aiguë</p>
                                        <small class="text-muted">Arrivé il y a 15 min</small>
                                    </div>
                                    <div class="waiting-room-item">
                                        <div class="d-flex justify-content-between">
                                            <h6>Moreau Sophie</h6>
                                            <span class="badge bg-secondary">10:30</span>
                                        </div>
                                        <p class="mb-1">Fracture clavicule</p>
                                        <small class="text-muted">Arrivé il y a 5 min</small>
                                    </div>
                                    <div class="waiting-room-item">
                                        <div class="d-flex justify-content-between">
                                            <h6>Petit Luc</h6>
                                            <span class="badge bg-secondary">11:00</span>
                                        </div>
                                        <p class="mb-1">Tendinite épaule</p>
                                        <small class="text-muted">En attente</small>
                                    </div>
                                    <div class="waiting-room-item urgent">
                                        <div class="d-flex justify-content-between">
                                            <h6>Durand Alice</h6>
                                            <span class="badge bg-danger">URGENT</span>
                                        </div>
                                        <p class="mb-1">Entorse genou</p>
                                        <small class="text-muted">Sans rendez-vous</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Patients Section -->
                <div id="patients-section" class="section-content d-none">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <span><i class="fas fa-user-injured"></i> Liste des patients</span>
                            <div>
                                <input type="text" class="form-control" placeholder="Rechercher un patient...">
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Nom</th>
                                            <th>Prénom</th>
                                            <th>Date de naissance</th>
                                            <th>Assurance</th>
                                            <th>Dernière visite</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>P-001</td>
                                            <td>Martin</td>
                                            <td>Jean</td>
                                            <td>15/03/1975</td>
                                            <td>CPAM</td>
                                            <td>10/05/2023</td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button class="btn btn-sm btn-outline-success">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>P-002</td>
                                            <td>Dubois</td>
                                            <td>Marie</td>
                                            <td>22/08/1982</td>
                                            <td>MGEN</td>
                                            <td>15/05/2023</td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button class="btn btn-sm btn-outline-success">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>P-003</td>
                                            <td>Leroy</td>
                                            <td>Pierre</td>
                                            <td>03/11/1968</td>
                                            <td>CPAM</td>
                                            <td>Aujourd'hui</td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button class="btn btn-sm btn-outline-success">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Patient Detail Section -->
                <div id="patient-detail-section" class="section-content d-none">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <span><i class="fas fa-user-injured"></i> Fiche patient : Martin Jean</span>
                            <div>
                                <button class="btn btn-sm btn-outline-secondary">
                                    <i class="fas fa-print"></i> Imprimer
                                </button>
                                <button class="btn btn-sm btn-primary">
                                    <i class="fas fa-save"></i> Enregistrer
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <ul class="nav nav-tabs" id="patientTabs" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" id="info-tab" data-bs-toggle="tab" data-bs-target="#info" type="button" role="tab">Informations</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="diagnosis-tab" data-bs-toggle="tab" data-bs-target="#diagnosis" type="button" role="tab">Diagnostics</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="prescriptions-tab" data-bs-toggle="tab" data-bs-target="#prescriptions" type="button" role="tab">Ordonnances</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="imaging-tab" data-bs-toggle="tab" data-bs-target="#imaging" type="button" role="tab">Imagerie</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="history-tab" data-bs-toggle="tab" data-bs-target="#history" type="button" role="tab">Historique</button>
                                </li>
                            </ul>
                            <div class="tab-content p-3" id="patientTabsContent">
                                <div class="tab-pane fade show active" id="info" role="tabpanel">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <h5>Informations personnelles</h5>
                                            <div class="mb-3">
                                                <label class="form-label">Nom</label>
                                                <input type="text" class="form-control" value="Martin">
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Prénom</label>
                                                <input type="text" class="form-control" value="Jean">
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Date de naissance</label>
                                                <input type="date" class="form-control" value="1975-03-15">
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Adresse</label>
                                                <textarea class="form-control">15 Rue de la République, 75001 Paris</textarea>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <h5>Assurance maladie</h5>
                                            <div class="mb-3">
                                                <label class="form-label">Caisse d'assurance</label>
                                                <input type="text" class="form-control" value="CPAM">
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Numéro de sécurité sociale</label>
                                                <input type="text" class="form-control" value="1 75 03 15 123 456 78">
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Mutuelle</label>
                                                <input type="text" class="form-control" value="Harmonie Mutuelle">
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Numéro de mutuelle</label>
                                                <input type="text" class="form-control" value="HM123456789">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="tab-pane fade" id="diagnosis" role="tabpanel">
                                    <h5>Diagnostics</h5>
                                    <div class="mb-3">
                                        <button class="btn btn-sm btn-primary">
                                            <i class="fas fa-plus"></i> Nouveau diagnostic
                                        </button>
                                    </div>
                                    <div class="card mb-3">
                                        <div class="card-header">
                                            <strong>Fracture du radius distal</strong> - 10/05/2023
                                        </div>
                                        <div class="card-body">
                                            <p>Fracture fermée du radius distal gauche avec déplacement minime. Traitement par réduction et plâtre brachio-antébrachio-palmaire.</p>
                                            <div class="d-flex">
                                                <span class="badge bg-secondary me-2">S52.5</span>
                                                <span class="badge bg-info">Fracture</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card">
                                        <div class="card-header">
                                            <strong>Entorse de la cheville</strong> - 15/03/2022
                                        </div>
                                        <div class="card-body">
                                            <p>Entorse bénigne de la cheville droite. Traitement symptomatique.</p>
                                            <div class="d-flex">
                                                <span class="badge bg-secondary me-2">S93.4</span>
                                                <span class="badge bg-info">Entorse</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="tab-pane fade" id="prescriptions" role="tabpanel">
                                    <h5>Ordonnances</h5>
                                    <div class="mb-3">
                                        <button class="btn btn-sm btn-primary">
                                            <i class="fas fa-plus"></i> Nouvelle ordonnance
                                        </button>
                                    </div>
                                    <div class="card mb-3">
                                        <div class="card-header d-flex justify-content-between">
                                            <span><strong>Ordonnance #ORD-001</strong> - 10/05/2023</span>
                                            <button class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-print"></i> Imprimer
                                            </button>
                                        </div>
                                        <div class="card-body">
                                            <div class="prescription-item">
                                                <div class="d-flex justify-content-between">
                                                    <strong>Paracétamol 1000 mg</strong>
                                                    <span>3 boîtes</span>
                                                </div>
                                                <p>1 comprimé 3 fois par jour pendant 5 jours</p>
                                            </div>
                                            <div class="prescription-item">
                                                <div class="d-flex justify-content-between">
                                                    <strong>Ibuprofène 400 mg</strong>
                                                    <span>2 boîtes</span>
                                                </div>
                                                <p>1 comprimé 2 fois par jour pendant 3 jours</p>
                                            </div>
                                            <div class="prescription-item">
                                                <div class="d-flex justify-content-between">
                                                    <strong>Vitamine D3 1000 UI</strong>
                                                    <span>1 boîte</span>
                                                </div>
                                                <p>1 comprimé par jour</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="tab-pane fade" id="imaging" role="tabpanel">
                                    <h5>Imagerie médicale</h5>
                                    <div class="mb-3">
                                        <button class="btn btn-sm btn-primary">
                                            <i class="fas fa-upload"></i> Importer un examen
                                        </button>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-8">
                                            <div class="dicom-viewer d-flex justify-content-center align-items-center">
                                                <div class="text-center text-white">
                                                    <i class="fas fa-image fa-5x mb-3"></i>
                                                    <p>Visualiseur DICOM</p>
                                                    <p>Chargement de l'image...</p>
                                                </div>
                                            </div>
                                            <div class="dicom-tools mt-3">
                                                <button class="tool-btn"><i class="fas fa-search-plus"></i> Zoom</button>
                                                <button class="tool-btn"><i class="fas fa-search-minus"></i> Dézoomer</button>
                                                <button class="tool-btn"><i class="fas fa-arrows-alt"></i> Pan</button>
                                                <button class="tool-btn"><i class="fas fa-ruler"></i> Mesurer</button>
                                                <button class="tool-btn"><i class="fas fa-adjust"></i> Contraste</button>
                                                <button class="tool-btn"><i class="fas fa-sun"></i> Luminosité</button>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="card">
                                                <div class="card-header">
                                                    Examens disponibles
                                                </div>
                                                <div class="card-body">
                                                    <div class="list-group">
                                                        <a href="#" class="list-group-item list-group-item-action active">
                                                            <div class="d-flex w-100 justify-content-between">
                                                                <h6 class="mb-1">Scanner poignet gauche</h6>
                                                                <small>10/05/2023</small>
                                                            </div>
                                                            <p class="mb-1">Fracture radius distal</p>
                                                            <small>DICOM - 120 images</small>
                                                        </a>
                                                        <a href="#" class="list-group-item list-group-item-action">
                                                            <div class="d-flex w-100 justify-content-between">
                                                                <h6 class="mb-1">Radiographie cheville droite</h6>
                                                                <small>15/03/2022</small>
                                                            </div>
                                                            <p class="mb-1">Entorse cheville</p>
                                                            <small>DICOM - 4 images</small>
                                                        </a>
                                                        <a href="#" class="list-group-item list-group-item-action">
                                                            <div class="d-flex w-100 justify-content-between">
                                                                <h6 class="mb-1">IRM genou droit</h6>
                                                                <small>10/01/2021</small>
                                                            </div>
                                                            <p class="mb-1">Douleur chronique</p>
                                                            <small>DICOM - 250 images</small>
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="tab-pane fade" id="history" role="tabpanel">
                                    <h5>Historique des consultations</h5>
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Date</th>
                                                    <th>Motif</th>
                                                    <th>Diagnostic</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td>10/05/2023</td>
                                                    <td>Chute avec douleur poignet</td>
                                                    <td>Fracture radius distal</td>
                                                    <td>
                                                        <button class="btn btn-sm btn-outline-primary">
                                                            <i class="fas fa-eye"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>15/03/2022</td>
                                                    <td>Entorse cheville</td>
                                                    <td>Entorse bénigne</td>
                                                    <td>
                                                        <button class="btn btn-sm btn-outline-primary">
                                                            <i class="fas fa-eye"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>10/01/2021</td>
                                                    <td>Douleur genou chronique</td>
                                                    <td>Arthrose débutante</td>
                                                    <td>
                                                        <button class="btn btn-sm btn-outline-primary">
                                                            <i class="fas fa-eye"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Waiting Room Section -->
                <div id="waiting-room-section" class="section-content d-none">
                    <div class="card">
                        <div class="card-header">
                            <i class="fas fa-clock"></i> Salle d'attente
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="card">
                                        <div class="card-header">
                                            Patients en attente
                                        </div>
                                        <div class="card-body">
                                            <div class="waiting-room-item">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <h5>Leroy Pierre</h5>
                                                        <p class="mb-1">Lombalgie aiguë - Arrivé il y a 25 min</p>
                                                        <div class="d-flex">
                                                            <span class="badge bg-warning me-2">10:00</span>
                                                            <span class="badge bg-info">Consultation de contrôle</span>
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <button class="btn btn-success me-2">
                                                            <i class="fas fa-user-md"></i> Appeler
                                                        </button>
                                                        <button class="btn btn-outline-secondary">
                                                            <i class="fas fa-times"></i> Annuler
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="waiting-room-item">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <h5>Moreau Sophie</h5>
                                                        <p class="mb-1">Fracture clavicule - Arrivé il y a 15 min</p>
                                                        <div class="d-flex">
                                                            <span class="badge bg-secondary me-2">10:30</span>
                                                            <span class="badge bg-danger">Nouveau patient</span>
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <button class="btn btn-success me-2">
                                                            <i class="fas fa-user-md"></i> Appeler
                                                        </button>
                                                        <button class="btn btn-outline-secondary">
                                                            <i class="fas fa-times"></i> Annuler
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="waiting-room-item">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <h5>Petit Luc</h5>
                                                        <p class="mb-1">Tendinite épaule - En attente</p>
                                                        <div class="d-flex">
                                                            <span class="badge bg-secondary me-2">11:00</span>
                                                            <span class="badge bg-info">Consultation de contrôle</span>
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <button class="btn btn-success me-2">
                                                            <i class="fas fa-user-md"></i> Appeler
                                                        </button>
                                                        <button class="btn btn-outline-secondary">
                                                            <i class="fas fa-times"></i> Annuler
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="waiting-room-item urgent">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <h5>Durand Alice</h5>
                                                        <p class="mb-1">Entorse genou - Sans rendez-vous</p>
                                                        <div class="d-flex">
                                                            <span class="badge bg-danger me-2">URGENT</span>
                                                            <span class="badge bg-warning">Douleur intense</span>
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <button class="btn btn-success me-2">
                                                            <i class="fas fa-user-md"></i> Appeler
                                                        </button>
                                                        <button class="btn btn-outline-secondary">
                                                            <i class="fas fa-times"></i> Refuser
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card">
                                        <div class="card-header">
                                            Statistiques d'attente
                                        </div>
                                        <div class="card-body">
                                            <div class="mb-3">
                                                <h6>Temps d'attente moyen</h6>
                                                <div class="progress">
                                                    <div class="progress-bar bg-warning" role="progressbar" style="width: 65%">18 min</div>
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <h6>Patients vus aujourd'hui</h6>
                                                <h3 class="text-primary">7/12</h3>
                                            </div>
                                            <div class="mb-3">
                                                <h6>Retards</h6>
                                                <h3 class="text-warning">25 min</h3>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card mt-3">
                                        <div class="card-header">
                                            Ajouter un patient sans RDV
                                        </div>
                                        <div class="card-body">
                                            <div class="mb-3">
                                                <label class="form-label">Nom</label>
                                                <input type="text" class="form-control">
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Prénom</label>
                                                <input type="text" class="form-control">
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Motif</label>
                                                <textarea class="form-control" rows="2"></textarea>
                                            </div>
                                            <div class="form-check mb-3">
                                                <input class="form-check-input" type="checkbox" id="urgentCheck">
                                                <label class="form-check-label" for="urgentCheck">
                                                    Cas urgent
                                                </label>
                                            </div>
                                            <button class="btn btn-primary w-100">Ajouter à la salle d'attente</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Agenda Section -->
                <div id="agenda-section" class="section-content d-none">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <span><i class="fas fa-calendar-alt"></i> Agenda professionnel</span>
                            <div>
                                <button class="btn btn-sm btn-outline-secondary me-2">
                                    <i class="fas fa-chevron-left"></i>
                                </button>
                                <span class="fw-bold">Mai 2023</span>
                                <button class="btn btn-sm btn-outline-secondary ms-2">
                                    <i class="fas fa-chevron-right"></i>
                                </button>
                                <button class="btn btn-sm btn-primary ms-3">
                                    <i class="fas fa-plus"></i> Nouveau rendez-vous
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th width="14%">Lundi<br>15</th>
                                            <th width="14%">Mardi<br>16</th>
                                            <th width="14%">Mercredi<br>17</th>
                                            <th width="14%">Jeudi<br>18</th>
                                            <th width="14%">Vendredi<br>19</th>
                                            <th width="14%">Samedi<br>20</th>
                                            <th width="14%">Dimanche<br>21</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td class="calendar-day">
                                                <span class="badge appointment-badge bg-primary">09:00 - Martin Jean</span>
                                                <span class="badge appointment-badge bg-primary">10:30 - Dubois Marie</span>
                                                <span class="badge appointment-badge bg-primary">14:00 - Leroy Pierre</span>
                                                <span class="badge appointment-badge bg-primary">16:30 - Moreau Sophie</span>
                                            </td>
                                            <td class="calendar-day">
                                                <span class="badge appointment-badge bg-primary">08:30 - Petit Luc</span>
                                                <span class="badge appointment-badge bg-warning">10:00 - Opération</span>
                                                <span class="badge appointment-badge bg-primary">14:30 - Bernard Alain</span>
                                            </td>
                                            <td class="calendar-day">
                                                <span class="badge appointment-badge bg-primary">09:15 - Robert Nathalie</span>
                                                <span class="badge appointment-badge bg-primary">11:00 - Richard Eric</span>
                                                <span class="badge appointment-badge bg-primary">15:00 - Durand Alice</span>
                                            </td>
                                            <td class="calendar-day today">
                                                <span class="badge appointment-badge bg-success">09:00 - Martin Jean</span>
                                                <span class="badge appointment-badge bg-success">09:30 - Dubois Marie</span>
                                                <span class="badge appointment-badge bg-warning">10:00 - Leroy Pierre</span>
                                                <span class="badge appointment-badge bg-secondary">10:30 - Moreau Sophie</span>
                                                <span class="badge appointment-badge bg-secondary">11:00 - Petit Luc</span>
                                            </td>
                                            <td class="calendar-day">
                                                <span class="badge appointment-badge bg-primary">08:00 - Thomas Laurent</span>
                                                <span class="badge appointment-badge bg-warning">10:30 - Opération</span>
                                                <span class="badge appointment-badge bg-primary">14:00 - Simon Claire</span>
                                            </td>
                                            <td class="calendar-day text-muted">
                                                <!-- Weekend - no appointments -->
                                            </td>
                                            <td class="calendar-day text-muted">
                                                <!-- Weekend - no appointments -->
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div class="mt-4">
                                <h5>Rendez-vous du jour</h5>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Heure</th>
                                                <th>Patient</th>
                                                <th>Motif</th>
                                                <th>Statut</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>09:00</td>
                                                <td>Martin Jean</td>
                                                <td>Fracture radius contrôle</td>
                                                <td><span class="badge bg-success">Terminé</span></td>
                                                <td>
                                                    <button class="btn btn-sm btn-outline-primary">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>09:30</td>
                                                <td>Dubois Marie</td>
                                                <td>Entorse cheville</td>
                                                <td><span class="badge bg-success">Terminé</span></td>
                                                <td>
                                                    <button class="btn btn-sm btn-outline-primary">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>10:00</td>
                                                <td>Leroy Pierre</td>
                                                <td>Lombalgie aiguë</td>
                                                <td><span class="badge bg-warning">En cours</span></td>
                                                <td>
                                                    <button class="btn btn-sm btn-outline-primary">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>10:30</td>
                                                <td>Moreau Sophie</td>
                                                <td>Fracture clavicule</td>
                                                <td><span class="badge bg-secondary">À venir</span></td>
                                                <td>
                                                    <button class="btn btn-sm btn-outline-primary">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>11:00</td>
                                                <td>Petit Luc</td>
                                                <td>Tendinite épaule</td>
                                                <td><span class="badge bg-secondary">À venir</span></td>
                                                <td>
                                                    <button class="btn btn-sm btn-outline-primary">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Operations Section -->
                <div id="operations-section" class="section-content d-none">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <span><i class="fas fa-procedures"></i> Suivi des opérations</span>
                            <button class="btn btn-sm btn-primary">
                                <i class="fas fa-plus"></i> Nouvelle opération
                            </button>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Patient</th>
                                            <th>Type d'opération</th>
                                            <th>Lieu</th>
                                            <th>Durée</th>
                                            <th>Statut</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>16/05/2023</td>
                                            <td>Dubois Marie</td>
                                            <td>Ostéosynthèse radius</td>
                                            <td>Clinique Saint-Louis</td>
                                            <td>1h 30min</td>
                                            <td><span class="badge bg-warning">Planifiée</span></td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button class="btn btn-sm btn-outline-success">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>19/05/2023</td>
                                            <td>Thomas Laurent</td>
                                            <td>Arthroscopie genou</td>
                                            <td>Clinique Saint-Louis</td>
                                            <td>45min</td>
                                            <td><span class="badge bg-warning">Planifiée</span></td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button class="btn btn-sm btn-outline-success">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>10/05/2023</td>
                                            <td>Martin Jean</td>
                                            <td>Réduction fracture radius</td>
                                            <td>Cabinet</td>
                                            <td>30min</td>
                                            <td><span class="badge bg-success">Réalisée</span></td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button class="btn btn-sm btn-outline-success">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>05/05/2023</td>
                                            <td>Robert Nathalie</td>
                                            <td>Suture tendon main</td>
                                            <td>Clinique Saint-Louis</td>
                                            <td>1h 15min</td>
                                            <td><span class="badge bg-success">Réalisée</span></td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button class="btn btn-sm btn-outline-success">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Certificates Section -->
                <div id="certificates-section" class="section-content d-none">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <span><i class="fas fa-file-medical"></i> Certificats médicaux</span>
                            <button class="btn btn-sm btn-primary">
                                <i class="fas fa-plus"></i> Nouveau certificat
                            </button>
                        </div>
                        <div class="card-body">
                            <div class="row mb-4">
                                <div class="col-md-8">
                                    <h5>Modèle de certificat médical</h5>
                                    <div class="certificate">
                                        <div class="certificate-header">
                                            <h3>CERTIFICAT MÉDICAL</h3>
                                            <p>Dr. Jean Dupont - Médecin Traumatologue</p>
                                            <p>15 Avenue des Champs-Élysées, 75008 Paris</p>
                                            <p>Tél: 01 45 67 89 10 - RPPS: 12345678901</p>
                                        </div>
                                        <div class="certificate-body">
                                            <p>Je soussigné, Dr. Jean Dupont, médecin traumatologue,</p>
                                            <p>certifie avoir examiné ce jour <strong>M. Jean Martin</strong>, né le <strong>15/03/1975</strong>,</p>
                                            <p>et avoir constaté une <strong>fracture du radius distal gauche</strong>.</p>
                                            <p>Je préconise un <strong>arrêt de travail de 15 jours</strong> à compter du <strong>10/05/2023</strong>.</p>
                                            <p>Le patient est invité à se présenter à une consultation de contrôle le <strong>25/05/2023</strong>.</p>
                                            <div class="mt-5">
                                                <p>Fait à Paris, le 10/05/2023</p>
                                                <p>Signature et cachet :</p>
                                                <div class="mt-4" style="height: 80px; border-bottom: 1px solid #000;"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <h5>Paramètres du certificat</h5>
                                    <div class="card">
                                        <div class="card-body">
                                            <div class="mb-3">
                                                <label class="form-label">Patient</label>
                                                <select class="form-select">
                                                    <option selected>Martin Jean</option>
                                                    <option>Dubois Marie</option>
                                                    <option>Leroy Pierre</option>
                                                    <option>Moreau Sophie</option>
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Diagnostic</label>
                                                <textarea class="form-control" rows="3">Fracture du radius distal gauche</textarea>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Recommandations</label>
                                                <textarea class="form-control" rows="3">Arrêt de travail de 15 jours. Consultation de contrôle dans 15 jours.</textarea>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Date de début</label>
                                                <input type="date" class="form-control" value="2023-05-10">
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Date de fin</label>
                                                <input type="date" class="form-control" value="2023-05-25">
                                            </div>
                                            <div class="d-grid gap-2">
                                                <button class="btn btn-primary">
                                                    <i class="fas fa-print"></i> Imprimer le certificat
                                                </button>
                                                <button class="btn btn-outline-primary">
                                                    <i class="fas fa-save"></i> Enregistrer le modèle
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <h5>Certificats récents</h5>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Patient</th>
                                            <th>Diagnostic</th>
                                            <th>Durée</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>10/05/2023</td>
                                            <td>Martin Jean</td>
                                            <td>Fracture radius distal</td>
                                            <td>15 jours</td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button class="btn btn-sm btn-outline-success">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="btn btn-sm btn-outline-info">
                                                    <i class="fas fa-print"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>05/05/2023</td>
                                            <td>Robert Nathalie</td>
                                            <td>Suture tendon main</td>
                                            <td>21 jours</td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button class="btn btn-sm btn-outline-success">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="btn btn-sm btn-outline-info">
                                                    <i class="fas fa-print"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>28/04/2023</td>
                                            <td>Simon Claire</td>
                                            <td>Entorse cheville sévère</td>
                                            <td>10 jours</td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button class="btn btn-sm btn-outline-success">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="btn btn-sm btn-outline-info">
                                                    <i class="fas fa-print"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Reports Section -->
                <div id="reports-section" class="section-content d-none">
                    <div class="card">
                        <div class="card-header">
                            <i class="fas fa-chart-bar"></i> Rapports et statistiques
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="card mb-4">
                                        <div class="card-header">
                                            Activité mensuelle
                                        </div>
                                        <div class="card-body">
                                            <div class="text-center p-4">
                                                <p>Graphique d'activité mensuelle</p>
                                                <div style="height: 300px; background-color: #f8f9fa; display: flex; justify-content: center; align-items: center;">
                                                    <p>Zone de graphique - Consultations, opérations, etc.</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card mb-4">
                                        <div class="card-header">
                                            Statistiques du mois
                                        </div>
                                        <div class="card-body">
                                            <div class="mb-3">
                                                <h6>Consultations</h6>
                                                <h3 class="text-primary">142</h3>
                                            </div>
                                            <div class="mb-3">
                                                <h6>Nouveaux patients</h6>
                                                <h3 class="text-success">28</h3>
                                            </div>
                                            <div class="mb-3">
                                                <h6>Opérations</h6>
                                                <h3 class="text-info">12</h3>
                                            </div>
                                            <div class="mb-3">
                                                <h6>Certificats délivrés</h6>
                                                <h3 class="text-warning">45</h3>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-header">
                                            Diagnostics les plus fréquents
                                        </div>
                                        <div class="card-body">
                                            <div class="mb-3">
                                                <div class="d-flex justify-content-between">
                                                    <span>Fractures</span>
                                                    <span>32%</span>
                                                </div>
                                                <div class="progress">
                                                    <div class="progress-bar" role="progressbar" style="width: 32%"></div>
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <div class="d-flex justify-content-between">
                                                    <span>Entorses</span>
                                                    <span>25%</span>
                                                </div>
                                                <div class="progress">
                                                    <div class="progress-bar" role="progressbar" style="width: 25%"></div>
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <div class="d-flex justify-content-between">
                                                    <span>Tendinites</span>
                                                    <span>18%</span>
                                                </div>
                                                <div class="progress">
                                                    <div class="progress-bar" role="progressbar" style="width: 18%"></div>
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <div class="d-flex justify-content-between">
                                                    <span>Lombalgies</span>
                                                    <span>15%</span>
                                                </div>
                                                <div class="progress">
                                                    <div class="progress-bar" role="progressbar" style="width: 15%"></div>
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <div class="d-flex justify-content-between">
                                                    <span>Autres</span>
                                                    <span>10%</span>
                                                </div>
                                                <div class="progress">
                                                    <div class="progress-bar" role="progressbar" style="width: 10%"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-header">
                                            Génération de rapports
                                        </div>
                                        <div class="card-body">
                                            <div class="mb-3">
                                                <label class="form-label">Type de rapport</label>
                                                <select class="form-select">
                                                    <option selected>Activité mensuelle</option>
                                                    <option>Activité trimestrielle</option>
                                                    <option>Activité annuelle</option>
                                                    <option>Patients par pathologie</option>
                                                    <option>Opérations par type</option>
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Période</label>
                                                <div class="row">
                                                    <div class="col">
                                                        <input type="date" class="form-control" value="2023-05-01">
                                                    </div>
                                                    <div class="col">
                                                        <input type="date" class="form-control" value="2023-05-31">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Format</label>
                                                <select class="form-select">
                                                    <option selected>PDF</option>
                                                    <option>Excel</option>
                                                    <option>HTML</option>
                                                </select>
                                            </div>
                                            <div class="d-grid">
                                                <button class="btn btn-primary">
                                                    <i class="fas fa-download"></i> Générer le rapport
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Navigation between sections
        document.querySelectorAll('.sidebar .nav-link').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                
                // Update active link
                document.querySelectorAll('.sidebar .nav-link').forEach(item => {
                    item.classList.remove('active');
                });
                this.classList.add('active');
                
                // Hide all sections
                document.querySelectorAll('.section-content').forEach(section => {
                    section.classList.add('d-none');
                });
                
                // Show selected section
                const sectionId = this.getAttribute('data-section') + '-section';
                document.getElementById(sectionId).classList.remove('d-none');
                
                // Update section title
                const sectionTitle = this.textContent.trim();
                document.getElementById('section-title').textContent = sectionTitle;
            });
        });

        // Patient detail view (simulated)
        document.querySelectorAll('#patients-section .btn-outline-primary').forEach(button => {
            button.addEventListener('click', function() {
                // Hide patients list
                document.getElementById('patients-section').classList.add('d-none');
                // Show patient detail
                document.getElementById('patient-detail-section').classList.remove('d-none');
                // Update section title
                document.getElementById('section-title').textContent = 'Fiche patient';
            });
        });

        // Back to patients list from patient detail
        document.querySelectorAll('#patient-detail-section .btn-outline-secondary').forEach(button => {
            button.addEventListener('click', function() {
                // Hide patient detail
                document.getElementById('patient-detail-section').classList.add('d-none');
                // Show patients list
                document.getElementById('patients-section').classList.remove('d-none');
                // Update section title
                document.getElementById('section-title').textContent = 'Patients';
            });
        });

        // Initialize tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    </script>
</body>
</html>