<?php
$pageTitle = "Vizualus žemėlapis";
include 'includes/header.php';
?>

<main class="container mt-4">
    <div class="card card-green mb-4">
        <div class="card-body">
            <h2 class="card-title">🗺️ Vizualus svetainės žemėlapis</h2>
            <p class="card-text">
                Žemiau matote svetainės struktūrą ir puslapių tarpusavio ryšius.
            </p>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card mb-4">
                <div class="card-body">
                    <h4 class="card-title">📊 Svetainės struktūros schema</h4>
                    
                    <!-- Beautiful Image Container -->
                    <div class="text-center my-4">
                        <!-- Image Frame with Shadow and Border -->
                        <div class="image-frame rounded-3 shadow-lg p-3 mb-4" style="background: linear-gradient(145deg, #f8f9fa, #e9ecef);">
                            <!-- Replace "YOUR_IMAGE_PATH" with your actual image path -->
                            <img src="C:\xampp\htdocs\PD2\includes\sitemap.png" 
                                 alt="Kino Duomenys svetainės struktūros schema" 
                                 class="img-fluid rounded-2 shadow-sm"
                                 style="max-height: 500px; width: auto; border: 1px solid #dee2e6;">
                        </div>
                        
                        <!-- Image Caption -->
                        <div class="image-caption text-muted mb-4">
                            <p class="mb-0">
                                <i class="bi bi-info-circle"></i> 
                                1 pav. „Kino Duomenys" svetainės struktūros vaizdinis žemėlapis
                            </p>
                        </div>
                        
                        <!-- Image Controls (Optional) -->
                        <div class="image-controls btn-group" role="group">
                            <button type="button" class="btn btn-outline-primary btn-sm" onclick="zoomIn()">
                                <i class="bi bi-zoom-in"></i> Padidinti
                            </button>
                            <button type="button" class="btn btn-outline-primary btn-sm" onclick="zoomOut()">
                                <i class="bi bi-zoom-out"></i> Sumažinti
                            </button>
                            <button type="button" class="btn btn-outline-primary btn-sm" onclick="resetZoom()">
                                <i class="bi bi-arrow-counterclockwise"></i> Atstatyti
                            </button>
                            <a href="PATH_TO_YOUR_IMAGE" download class="btn btn-outline-success btn-sm">
                                <i class="bi bi-download"></i> Atsisiųsti
                            </a>
                        </div>
                    </div>
                    
                    <!-- Image Description -->
                    <div class="card bg-light mt-4">
                        <div class="card-body">
                            <h5 class="card-title">📝 Diagramos paaiškinimai</h5>
                            <div class="row">
                                <div class="col-md-6">
                                    <ul class="list-unstyled">
                                        <li class="mb-2">
                                            <span class="badge bg-success me-2">●</span>
                                            <strong>Žalios kortelės:</strong> Informacinės sekcijos
                                        </li>
                                        <li class="mb-2">
                                            <span class="badge bg-danger me-2">●</span>
                                            <strong>Raudonos kortelės:</strong> Vartotojų interakcijos
                                        </li>
                                        <li class="mb-2">
                                            <span class="badge bg-primary me-2">●</span>
                                            <strong>Mėlynos nuorodos:</strong> Navigacijos keliai
                                        </li>
                                    </ul>
                                </div>
                                <div class="col-md-6">
                                    <ul class="list-unstyled">
                                        <li class="mb-2">
                                            <span class="badge bg-warning me-2">●</span>
                                            <strong>Geltoni langai:</strong> Dinaminis turinys
                                        </li>
                                        <li class="mb-2">
                                            <span class="badge bg-info me-2">●</span>
                                            <strong>Žydri elementai:</strong> Duomenų srautai
                                        </li>
                                        <li class="mb-2">
                                            <span class="badge bg-secondary me-2">●</span>
                                            <strong>Pilki blokai:</strong> Techninė infrastruktūra
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-body">
                    <h4 class="card-title">🎨 Spalvų reikšmės</h4>
                    <ul class="list-unstyled">
                        <li class="mb-2"><span class="badge bg-success me-2">🟢</span> Viešas prieinamumas</li>
                        <li class="mb-2"><span class="badge bg-primary me-2">🔵</span> Reikalauja prisijungimo</li>
                        <li class="mb-2"><span class="badge bg-info me-2">🟡</span> Dinaminis puslapis</li>
                        <li class="mb-2"><span class="badge bg-danger me-2">🔴</span> Paslaugos / AJAX</li>
                        <li class="mb-2"><span class="badge bg-warning me-2">🟠</span> Administravimas</li>
                    </ul>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-body">
                    <h4 class="card-title">⚡ Greita navigacija</h4>
                    <div class="d-grid gap-2">
                        <a href="index.php" class="btn btn-success">
                            <i class="bi bi-house-door"></i> Pagrindinis
                        </a>
                        <a href="movies.php" class="btn btn-primary">
                            <i class="bi bi-film"></i> Visi filmai
                        </a>
                        <a href="sitemap.php" class="btn btn-info">
                            <i class="bi bi-diagram-3"></i> Tekstinis žemėlapis
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<!-- Zoom Functionality Script -->
<script>
let currentZoom = 1;
const zoomStep = 0.1;
const minZoom = 0.5;
const maxZoom = 2;

function zoomIn() {
    if (currentZoom < maxZoom) {
        currentZoom += zoomStep;
        updateImageZoom();
    }
}

function zoomOut() {
    if (currentZoom > minZoom) {
        currentZoom -= zoomStep;
        updateImageZoom();
    }
}

function resetZoom() {
    currentZoom = 1;
    updateImageZoom();
}

function updateImageZoom() {
    const image = document.querySelector('.image-frame img');
    if (image) {
        image.style.transform = `scale(${currentZoom})`;
        image.style.transition = 'transform 0.3s ease';
        
        // Update zoom indicator
        const zoomIndicator = document.getElementById('zoomIndicator');
        if (zoomIndicator) {
            zoomIndicator.textContent = `(${Math.round(currentZoom * 100)}%)`;
        }
    }
}

// Add Bootstrap Icons if not already included
if (!document.querySelector('link[href*="bootstrap-icons"]')) {
    const link = document.createElement('link');
    link.rel = 'stylesheet';
    link.href = 'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css';
    document.head.appendChild(link);
}
</script>

<!-- Add some custom CSS for the image frame -->
<style>
.image-frame {
    position: relative;
    overflow: hidden;
    transition: all 0.3s ease;
}

.image-frame:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.1) !important;
}

.image-frame::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(135deg, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0) 100%);
    z-index: 1;
    pointer-events: none;
}

.image-caption {
    border-left: 4px solid #28a745;
    padding-left: 15px;
    font-style: italic;
}

.image-controls {
    margin-top: 20px;
    padding: 10px;
    background: rgba(248, 249, 250, 0.8);
    border-radius: 10px;
    display: inline-flex;
    gap: 5px;
}

.img-fluid {
    transition: transform 0.3s ease;
    cursor: zoom-in;
}

.img-fluid:hover {
    transform: scale(1.01);
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .image-frame {
        padding: 10px !important;
    }
    
    .image-controls .btn {
        font-size: 0.8rem;
        padding: 5px 8px;
    }
}
</style>

<?php include 'includes/footer.php'; ?>