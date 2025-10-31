<?php
// modules/quotes/gazebo_configurator.php
// Bu dosya sadece modal HTML'ini içerir, veriler gazebo-data.js'den gelir
?>

<!-- Gazebo Konfiguratör Modal -->
<div class="modal fade" id="gazeboModal" tabindex="-1" aria-labelledby="gazeboModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="gazeboModalLabel">
                    <i class="fas fa-home"></i> Gazebo Tente Konfiguratörü
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="max-height: 80vh; overflow-y: auto;">
                <!-- Gazebo konfiguratör içeriği -->
                <div id="gazebo-configurator" class="gazebo-configurator">
                    <div class="container-fluid">
                        <div class="config-section" id="step-boyut">
                            <h2><i class="fas fa-ruler-combined"></i> 1. Gazebo Ölçüsü Seçimi (Zorunlu)</h2>
                            <div class="options-grid" id="size-options"></div>
                        </div>

                        <div class="config-section disabled" id="step-kumas">
                            <h2><i class="fas fa-palette"></i> 2. Kumaş Türü Seçimi (Zorunlu)</h2>
                            <div class="options-grid" id="fabric-options"></div>
                        </div>

                        <div class="config-section disabled" id="step-iskelet">
                            <h2><i class="fas fa-tools"></i> 3. İskelet Tipi Seçimi (Opsiyonel)</h2>
                            <div class="options-grid" id="frame-options"></div>
                            <div class="clear-selection-box">
                                <input type="radio" name="frame_type" id="frame_none" value="none" class="hidden-radio" checked>
                                <label for="frame_none" class="clear-selection-label">
                                    <i class="fas fa-times-circle"></i> İskelet Almak İstemiyorum
                                </label>
                            </div>
                        </div>

                        <div class="config-section disabled" id="step-duvar">
                            <h2><i class="fas fa-border-all"></i> 4. Duvar Konfigürasyonu (Opsiyonel)</h2>
                            <p class="info-text"><i class="fas fa-info-circle"></i> Lütfen her bir kenar için duvar tipini seçiniz.</p>
                            <div id="wall-configuration"></div>
                        </div>

                        <div class="config-section summary-section" id="price-summary">
                            <h2><i class="fas fa-receipt"></i> Sipariş Özeti ve Toplam Fiyat</h2>
                            <div id="summary-details"></div>
                            <div id="total-price-box">
                                Toplam Tutar: <span id="total-price">0,00 TL</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times"></i> İptal
                </button>
                <button type="button" class="btn btn-primary" id="addGazeboToQuote">
                    <i class="fas fa-plus"></i> Teklife Ekle
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Gazebo konfiguratör stilleri - Sistem temasıyla uyumlu -->
<style>
/* Gazebo Konfigüratör Stilleri */
.gazebo-configurator {
    font-family: 'Inter', sans-serif;
}

.config-section {
    margin-bottom: 2rem;
    padding: 1.5rem;
    background: #2d3748;
    border: 1px solid #4a5568;
    border-radius: 12px;
    transition: all 0.3s ease;
}

.config-section.disabled {
    opacity: 0.4;
    pointer-events: none;
    background: #1a202c;
}

.config-section.disabled h2 {
    color: #718096;
}

.config-section h2 {
    color: #e2e8f0;
    font-size: 1.25rem;
    font-weight: 600;
    margin-bottom: 1.5rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.config-section h2 i {
    color: #4299e1;
}

.options-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 1rem;
    margin-bottom: 1rem;
}

.option-card {
    background: #4a5568;
    border: 2px solid #718096;
    border-radius: 8px;
    padding: 1rem;
    cursor: pointer;
    transition: all 0.2s ease;
    text-align: center;
    position: relative;
    min-height: 120px;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
}

.option-card:hover {
    border-color: #4299e1;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(66, 153, 225, 0.3);
    background: #5a6578;
}

.option-card.selected {
    border-color: #48bb78;
    background: #48bb78;
    color: white;
    box-shadow: 0 4px 16px rgba(72, 187, 120, 0.4);
}

.option-card.selected::before {
    content: '✓';
    position: absolute;
    top: 8px;
    right: 12px;
    font-weight: bold;
    font-size: 1.4rem;
    color: white;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 50%;
    width: 24px;
    height: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.option-card.disabled {
    background: #2d3748;
    border-color: #4a5568;
    opacity: 0.5;
    cursor: not-allowed;
    color: #a0aec0;
    pointer-events: none;
}

.option-card.disabled::after {
    content: 'Mevcut Değil';
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background: #e53e3e;
    color: white;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 0.75rem;
    font-weight: 600;
    z-index: 2;
}

.option-card.disabled:hover {
    border-color: #4a5568;
    transform: none;
    box-shadow: none;
    background: #2d3748;
}

.disabled-card {
    background: #2d3748 !important;
    border-color: #4a5568 !important;
    opacity: 0.5 !important;
    cursor: not-allowed !important;
    color: #a0aec0 !important;
    pointer-events: none !important;
    position: relative;
}

.disabled-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(45, 55, 72, 0.8);
    border-radius: 8px;
    z-index: 1;
}

.disabled-card::after {
    content: 'Mevcut Değil';
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background: #e53e3e;
    color: white;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 0.75rem;
    font-weight: 600;
    z-index: 2;
}

.disabled-card:hover {
    border-color: #4a5568 !important;
    transform: none !important;
    box-shadow: none !important;
    background: #2d3748 !important;
}

.option-card h4 {
    font-size: 1rem;
    font-weight: 600;
    margin-bottom: 0.5rem;
    color: inherit;
}

.option-card p {
    font-size: 0.85rem;
    margin-bottom: 0.5rem;
    opacity: 0.9;
    color: inherit;
}

.option-card .price {
    font-weight: 700;
    font-size: 1rem;
    color: #68d391;
}

.option-card.selected .price {
    color: white;
}

.option-card.disabled .price {
    color: #a0aec0;
}

.clear-selection-box {
    background: #4a5568;
    border: 2px dashed #718096;
    border-radius: 8px;
    padding: 1rem;
    text-align: center;
    margin-top: 1rem;
}

.clear-selection-label {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    cursor: pointer;
    color: #cbd5e0;
    font-weight: 500;
    margin: 0;
}

.clear-selection-label:hover {
    color: #4299e1;
}

.hidden-radio {
    display: none;
}

.wall-config-item {
    background: #4a5568;
    border: 1px solid #718096;
    border-radius: 8px;
    padding: 1.5rem;
    margin-bottom: 1rem;
}

.wall-config-item h5 {
    color: #e2e8f0;
    font-size: 1.1rem;
    font-weight: 600;
    margin-bottom: 1rem;
}

.wall-options {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
    gap: 0.75rem;
}

.wall-option {
    background: #2d3748;
    border: 2px solid #4a5568;
    border-radius: 6px;
    padding: 0.75rem;
    cursor: pointer;
    transition: all 0.2s ease;
    text-align: center;
    font-size: 0.85rem;
    color: #e2e8f0;
    min-height: 80px;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    position: relative;
}

.wall-option:hover {
    border-color: #4299e1;
    background: #4a5568;
    transform: translateY(-1px);
}

.wall-option.selected {
    border-color: #48bb78;
    background: #48bb78;
    color: white;
}

.wall-option.selected::before {
    content: '✓';
    position: absolute;
    top: 4px;
    right: 6px;
    font-weight: bold;
    font-size: 1rem;
    color: white;
}

.wall-option.disabled {
    background: #2d3748;
    border-color: #4a5568;
    opacity: 0.4;
    cursor: not-allowed;
}

.wall-option .wall-price {
    font-size: 0.8rem;
    font-weight: 600;
    color: #68d391;
    margin-top: 0.25rem;
}

.wall-option.selected .wall-price {
    color: white;
}

.wall-option.disabled .wall-price {
    color: #a0aec0;
}

.summary-section {
    background: #2d3748;
    border: 2px solid #4299e1;
}

.summary-section h2 {
    color: #4299e1;
}

#summary-details {
    background: #4a5568;
    border-radius: 8px;
    padding: 1rem;
    margin-bottom: 1rem;
    max-height: 300px;
    overflow-y: auto;
}

.summary-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.75rem 0;
    border-bottom: 1px solid #718096;
    font-size: 0.9rem;
}

.summary-item:last-child {
    border-bottom: none;
    font-weight: 600;
    font-size: 1rem;
    margin-top: 0.5rem;
    padding-top: 1rem;
    border-top: 2px solid #4299e1;
}

.summary-item .item-name {
    color: #e2e8f0;
    flex: 1;
    text-align: left;
}

.summary-item .item-quantity {
    color: #cbd5e0;
    margin: 0 0.5rem;
    font-size: 0.8rem;
}

.summary-item .item-price {
    color: #68d391;
    font-weight: 600;
    text-align: right;
}

.summary-item .item-image {
    width: 40px;
    height: 40px;
    object-fit: cover;
    border-radius: 4px;
    margin-right: 0.75rem;
    border: 1px solid #718096;
}

#total-price-box {
    background: linear-gradient(135deg, #48bb78, #38a169);
    color: white;
    padding: 1.25rem;
    border-radius: 8px;
    text-align: center;
    font-size: 1.4rem;
    font-weight: 700;
    box-shadow: 0 4px 16px rgba(72, 187, 120, 0.3);
}

.info-text {
    background: #4299e1;
    color: white;
    padding: 0.75rem 1rem;
    border-radius: 6px;
    font-size: 0.9rem;
    margin-bottom: 1rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

/* Modal özelleştirmeleri */
.modal-xl .modal-dialog {
    max-width: 1200px;
}

.modal-content {
    background: #1a202c;
    border: none;
    border-radius: 12px;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
}

.modal-header {
    background: linear-gradient(135deg, #4299e1, #3182ce);
    color: white;
    border-bottom: none;
    border-radius: 12px 12px 0 0;
    padding: 1.25rem 1.5rem;
}

.modal-title {
    font-weight: 600;
    font-size: 1.25rem;
}

.btn-close {
    filter: invert(1);
    opacity: 0.8;
}

.btn-close:hover {
    opacity: 1;
}

.modal-body {
    padding: 1.5rem;
    background: #1a202c;
}

.modal-footer {
    border-top: 1px solid #4a5568;
    padding: 1rem 1.5rem;
    background: #2d3748;
    border-radius: 0 0 12px 12px;
}

/* Responsive */
@media (max-width: 768px) {
    .options-grid {
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 0.75rem;
    }
    
    .wall-options {
        grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
    }
    
    .modal-body {
        padding: 1rem;
    }
    
    .config-section {
        padding: 1rem;
        margin-bottom: 1.5rem;
    }
    
    .option-card {
        min-height: 100px;
        padding: 0.75rem;
    }
    
    .option-card h4 {
        font-size: 0.9rem;
    }
    
    .option-card .price {
        font-size: 0.9rem;
    }
}

@media (max-width: 576px) {
    .options-grid {
        grid-template-columns: 1fr 1fr;
    }
    
    .wall-options {
        grid-template-columns: 1fr 1fr;
    }
}

/* Light theme uyumluluğu */
[data-theme="light"] .config-section {
    background: #f7fafc;
    border-color: #e2e8f0;
}

[data-theme="light"] .config-section h2 {
    color: #2d3748;
}

[data-theme="light"] .option-card {
    background: white;
    border-color: #e2e8f0;
    color: #2d3748;
}

[data-theme="light"] .option-card:hover {
    background: #f7fafc;
}

[data-theme="light"] .wall-config-item {
    background: white;
    border-color: #e2e8f0;
}

[data-theme="light"] .wall-option {
    background: #f7fafc;
    border-color: #e2e8f0;
    color: #2d3748;
}

[data-theme="light"] .modal-content {
    background: white;
}

[data-theme="light"] .modal-body {
    background: #f7fafc;
}

[data-theme="light"] .modal-footer {
    background: white;
    border-color: #e2e8f0;
}

/* Light theme disabled styles */
[data-theme="light"] .option-card.disabled,
[data-theme="light"] .disabled-card {
    background: #f7fafc !important;
    border-color: #e2e8f0 !important;
    opacity: 0.5 !important;
    color: #a0aec0 !important;
}

[data-theme="light"] .disabled-card::before {
    background: rgba(247, 250, 252, 0.8);
}

[data-theme="light"] .disabled-card::after {
    background: #e53e3e;
    color: white;
}

[data-theme="light"] .wall-option.disabled {
    background: #f7fafc !important;
    border-color: #e2e8f0 !important;
    opacity: 0.4 !important;
}
</style>

<!-- Bootstrap JS (eğer yüklü değilse) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- Gazebo veri dosyası -->
<script src="../../assets/js/gazebo-data.js"></script>
<!-- Gazebo konfiguratör script'i -->
<script src="../../assets/js/gazebo-configurator.js"></script>

<script>
// Modal açılma testi
document.addEventListener('DOMContentLoaded', function() {
    console.log('Gazebo konfiguratör yüklendi');
    
    // Modal açılma eventi
    const gazeboModal = document.getElementById('gazeboModal');
    if (gazeboModal) {
        gazeboModal.addEventListener('shown.bs.modal', function () {
            console.log('Gazebo modal açıldı');
            // Modal her açıldığında konfigüratörü sıfırla
            resetGazeboConfigurator();
        });
    }
});

// CRM entegrasyonu için özel fonksiyonlar
document.addEventListener('DOMContentLoaded', function() {
    const addButton = document.getElementById('addGazeboToQuote');
    if (addButton) {
        addButton.addEventListener('click', function() {
    if (!selectedConfig.size || !selectedConfig.fabric) {
        alert('Lütfen en az gazebo boyutu ve kumaş türünü seçin.');
        return;
    }

    // Seçilen ürünlerin mevcut olup olmadığını kontrol et
    const sizeData = globalFiyatData.fiyat_tablolari[selectedConfig.size];
    const fabricData = globalFiyatData.genislik_bazli_fiyatlar[sizeData.genislik][selectedConfig.fabric];
    
    // Çatı fiyatı kontrolü
    if (!fabricData.cati_sacak || fabricData.cati_sacak <= 0) {
        alert('Seçilen gazebo boyutu ve kumaş kombinasyonu mevcut değil.');
        return;
    }
    
    // İskelet kontrolü
    if (selectedConfig.frame !== 'none') {
        const framePrice = sizeData.iskelet_aksesuar[selectedConfig.frame];
        if (!framePrice || framePrice <= 0) {
            alert('Seçilen iskelet tipi mevcut değil.');
            return;
        }
    }
    
    // Duvar kontrolü
    for (const wallId in selectedConfig.walls) {
        const wall = selectedConfig.walls[wallId];
        if (wall.type !== 'none' && wall.price <= 0) {
            alert('Seçilen duvar tiplerinden biri mevcut değil.');
            return;
        }
    }

    const productIds = [];
    
    // Ana gazebo ürünü ID'si
    productIds.push(getGazeboProductId(selectedConfig.size, selectedConfig.fabric));

    // İskelet ID'si (varsa)
    if (selectedConfig.frame !== 'none') {
        productIds.push(getFrameProductId(selectedConfig.size, selectedConfig.frame));
    }

    // Duvar ID'leri ve adetlerini hesapla
    const wallCounts = {};
    Object.keys(selectedConfig.walls).forEach(wallId => {
        const wall = selectedConfig.walls[wallId];
        if (wall.type !== 'none' && wall.price > 0) {
            const wallProductId = getWallProductId(wall.width, wall.type, selectedConfig.fabric);
            if (wallCounts[wallProductId]) {
                wallCounts[wallProductId]++;
            } else {
                wallCounts[wallProductId] = 1;
                productIds.push(wallProductId);
            }
        }
    });

    // Veritabanından gerçek ürün bilgilerini çek ve forma ekle
    
    fetch('get_products.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ 
            product_ids: productIds,
            wall_counts: wallCounts 
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.products) {
            // Veritabanından gelen gerçek ürün bilgilerini kullan
            data.products.forEach(product => {
                if (typeof addProductToQuote === 'function') {
                    // Duvar ürünü ise adet bilgisini kontrol et
                    const wallCount = wallCounts[product.id];
                    if (wallCount && wallCount > 1) {
                        // Aynı duvardan birden fazla varsa, önce ürünü ekle sonra adetini artır
                        addProductToQuote(product);
                        const rowId = 'product-' + product.id;
                        const row = document.getElementById(rowId);
                        if (row) {
                            const quantityInput = row.querySelector('.quantity-input');
                            quantityInput.value = wallCount;
                            updateQuantity(quantityInput);
                        }
                    } else {
                        addProductToQuote(product);
                    }
                }
            });
        } else {
            console.error('Ürün bilgileri alınamadı:', data.error);
            alert('Ürün bilgileri alınırken hata oluştu.');
        }
    })
    .catch(error => {
        console.error('AJAX hatası:', error);
        alert('Ürünler eklenirken hata oluştu.');
    });

    // Konfigüratörü sıfırla
    resetGazeboConfigurator();
    
    // Modal'ı kapat
    const modal = bootstrap.Modal.getInstance(document.getElementById('gazeboModal'));
    if (modal) {
        modal.hide();
    }
        });
    }
});

// Yardımcı fonksiyonlar
function getGazeboProductId(size, fabric) {
    // Gerçek veritabanı ID'leri - CSV'den import edilen ürünlerin ID'leri
    const productMap = {
        "2x2_690_denye": 10001,
        "2x2_200_oxford": 10002,
        "2x2_dijital_baski": 10003,
        "2x3_690_denye": 10004,
        "2x3_200_oxford": 10005,
        "2x3_dijital_baski": 10006,
        "3x3_690_denye": 10007,
        "3x3_200_oxford": 10008,
        "3x3_dijital_baski": 10009,
        "3x4.5_690_denye": 10010,
        "3x4.5_200_oxford": 10011,
        "3x4.5_dijital_baski": 10012,
        "3x6_690_denye": 10013,
        "3x6_200_oxford": 10014,
        "3x6_dijital_baski": 10015,
        "4x4_690_denye": 10016,
        "4x4_200_oxford": 10017,
        "4x4_dijital_baski": 10018,
        "4x6_690_denye": 10019,
        "4x6_200_oxford": 10020,
        "4x6_dijital_baski": 10021,
        "4x8_690_denye": 10022,
        "4x8_200_oxford": 10023,
        "4x8_dijital_baski": 10024
    };
    
    const key = `${size}_${fabric}`;
    return productMap[key] || 10001; // Varsayılan olarak ilk gazebo
}

function getFabricName(fabricKey) {
    const names = {
        "690_denye": "690 Denye",
        "200_oxford": "200 Oxford",
        "dijital_baski": "Dijital Baskı"
    };
    return names[fabricKey] || fabricKey;
}

function getFrameName(frameKey) {
    const names = {
        "30luk_celik": "30luk Çelik",
        "40luk_celik": "40luk Çelik", 
        "40lik_aluminyum": "40lik Alüminyum",
        "52lik_aluminyum": "52lik Alüminyum"
    };
    return names[frameKey] || frameKey;
}

function getFabricPrice(size, fabric) {
    if (!globalFiyatData || !globalFiyatData.fiyat_tablolari[size]) {
        return 0;
    }
    
    const sizeData = globalFiyatData.fiyat_tablolari[size];
    const fabricData = globalFiyatData.genislik_bazli_fiyatlar[sizeData.genislik];
    
    if (fabricData && fabricData[fabric]) {
        return fabricData[fabric].cati_sacak || 0;
    }
    return 0;
}

function getFramePrice(size, frame) {
    if (!globalFiyatData || !globalFiyatData.fiyat_tablolari[size]) {
        return 0;
    }
    
    const sizeData = globalFiyatData.fiyat_tablolari[size];
    return sizeData.iskelet_aksesuar[frame] || 0;
}

function getSizeImage(size) {
    const sizeImages = {
        "2x2": "https://skygorselmarket.com/wp-content/uploads/2025/03/1-4.jpg",
        "2x3": "https://skygorselmarket.com/wp-content/uploads/2025/03/10-1-scaled.jpg",
        "3x3": "https://skygorselmarket.com/wp-content/uploads/2024/09/300x300-beyaz-768x768.webp",
        "3x4.5": "https://skygorselmarket.com/wp-content/uploads/2025/01/1-2-768x686.jpg",
        "3x6": "https://skygorselmarket.com/wp-content/uploads/2025/01/1-3-768x619.jpg",
        "4x4": "https://skygorselmarket.com/wp-content/uploads/2025/01/3-4-768x768.jpg",
        "4x6": "https://skygorselmarket.com/wp-content/uploads/2025/01/11-4-768x619.jpg",
        "4x8": "https://skygorselmarket.com/wp-content/uploads/2025/01/11-4-768x619.jpg"
    };
    return sizeImages[size] || '';
}

function getFrameImage(frame) {
    const frameImages = {
        "30luk_celik": "https://skygorselmarket.com/wp-content/uploads/2024/09/30luk-celik-iskelet-768x768.webp",
        "40luk_celik": "https://skygorselmarket.com/wp-content/uploads/2024/09/40lik-celik-iskelet-768x768.webp",
        "40lik_aluminyum": "https://skygorselmarket.com/wp-content/uploads/2024/09/40lik-aluminyum-iskelet-768x768.webp",
        "52lik_aluminyum": "https://skygorselmarket.com/wp-content/uploads/2024/09/52lik-aluminyum-iskelet-768x768.webp"
    };
    return frameImages[frame] || '';
}

function getWallImage(wallType) {
    const wallImages = {
        "tam_duvar": "https://skygorselmarket.com/wp-content/uploads/2024/08/tam-duvar.webp",
        "yarim_duvar": "https://skygorselmarket.com/wp-content/uploads/2024/08/yarim-duvar.webp",
        "seffaf_duvar": "https://skygorselmarket.com/wp-content/uploads/2024/09/seffaf-duvar.webp",
        "fermuarli_duvar": "https://skygorselmarket.com/wp-content/uploads/2024/09/tek-fermuarli-duvar.webp",
        "sineklik_duvar": "https://skygorselmarket.com/wp-content/uploads/2024/09/sineklik-duvar.webp",
        "kapili_duvar": "https://skygorselmarket.com/wp-content/uploads/2024/09/kapili-duvar.webp",
        "sineklikli_kapi": "https://skygorselmarket.com/wp-content/uploads/2024/09/sineklikli-kapi.webp",
        "kapi_seffaf_pencere": "https://skygorselmarket.com/wp-content/uploads/2024/09/kapiseffaf-pencere.webp",
        "sineklikli_kapi_seffaf_perde": "https://skygorselmarket.com/wp-content/uploads/2024/09/sineklikli-kapiseffaf-pencere.webp",
        "seffaf_sineklik_perde_kapi": "https://skygorselmarket.com/wp-content/uploads/2024/09/seffaf-sineklikli-perdeseffaf-sineklikli-kapi.webp",
        "seffaf_camli_duvar": "https://skygorselmarket.com/wp-content/uploads/2024/09/seffaf-camli-duvar.webp",
        "seffaf_camli_perdeli_duvar": "https://skygorselmarket.com/wp-content/uploads/2024/09/seffaf-camli-perdeli-duvar.webp",
        "seffaf_sineklik_perdeli_camli_duvar": "https://skygorselmarket.com/wp-content/uploads/2024/09/seffaf-sineklik-perdeli-camli-duvar.webp"
    };
    return wallImages[wallType] || '';
}

function getFrameProductId(size, frame) {
    // İskelet ürünlerinin gerçek ID'leri - boyut ve iskelet tipine göre
    const frameMap = {
        "2x2_30luk_celik": 11001,
        "2x3_30luk_celik": 11002,
        "3x3_30luk_celik": 11003,
        "3x3_40luk_celik": 11004,
        "3x3_40lik_aluminyum": 11005,
        "3x3_52lik_aluminyum": 11006,
        "3x4.5_40luk_celik": 11007,
        "3x4.5_40lik_aluminyum": 11008,
        "3x4.5_52lik_aluminyum": 11009,
        "3x6_40luk_celik": 11010,
        "3x6_40lik_aluminyum": 11011,
        "3x6_52lik_aluminyum": 11012,
        "4x4_40luk_celik": 11013,
        "4x4_52lik_aluminyum": 11014,
        "4x6_40luk_celik": 11015,
        "4x6_52lik_aluminyum": 11016,
        "4x8_40luk_celik": 11017,
        "4x8_52lik_aluminyum": 11018
    };
    
    const key = `${size}_${frame}`;
    return frameMap[key] || 11001; // Varsayılan olarak ilk iskelet
}

function getWallProductId(width, wallType, fabric) {
    // Basitleştirilmiş duvar ID hesaplama
    // Her boyut için yaklaşık 29 duvar ürünü var
    const sizeOffsets = {
        "2m": 0,    // 12001-12027 (27 ürün)
        "3m": 27,   // 12028-12056 (29 ürün)  
        "4m": 56,   // 12057-12086 (30 ürün)
        "4.5m": 86, // 12087-12116 (30 ürün)
        "6m": 116,  // 12117-12146 (30 ürün)
        "8m": 146   // 12147-12177 (31 ürün)
    };
    
    // Duvar tipi sıralaması (CSV'deki gerçek sırayla)
    const wallTypeOffsets = {
        "tam_duvar": 0,           // 3 varyant (690, oxford, dijital) -> 12001-12003
        "yarim_duvar": 3,         // 3 varyant -> 12004-12006
        "seffaf_duvar": 6,        // 1 varyant (kumaşsız) -> 12007
        "fermuarli_duvar": 7,     // 3 varyant -> 12008-12010
        "sineklik_duvar": 10,     // 1 varyant (kumaşsız) -> 12011
        "kapili_duvar": 11,       // 3 varyant -> 12012-12014
        "sineklikli_kapi": 14,    // 1 varyant (kumaşsız) -> 12015
        "kapi_seffaf_pencere": 15, // 2 varyant (690, oxford) -> 12016-12017
        "sineklikli_kapi_seffaf_perde": 17, // 2 varyant -> 12018-12019
        "seffaf_sineklik_perde_kapi": 19,   // 2 varyant -> 12020-12021
        "seffaf_camli_duvar": 21,           // 2 varyant -> 12022-12023
        "seffaf_camli_perdeli_duvar": 23,   // 2 varyant -> 12024-12025
        "seffaf_sineklik_perdeli_camli_duvar": 25 // 2 varyant -> 12026-12027
    };
    
    const fabricOffsets = {
        "690_denye": 0,
        "200_oxford": 1, 
        "dijital_baski": 2
    };
    
    const baseId = 12001;
    const sizeOffset = sizeOffsets[width] || 0;
    const wallOffset = wallTypeOffsets[wallType] || 0;
    
    // Kumaşsız duvarlar için fabric offset'i 0
    let fabricOffset = 0;
    if (fabric && (wallType === "tam_duvar" || wallType === "yarim_duvar" || 
                   wallType === "fermuarli_duvar" || wallType === "kapili_duvar")) {
        fabricOffset = fabricOffsets[fabric] || 0;
    } else if (fabric && (wallType === "kapi_seffaf_pencere" || 
                         wallType === "sineklikli_kapi_seffaf_perde" ||
                         wallType === "seffaf_sineklik_perde_kapi" ||
                         wallType === "seffaf_camli_duvar" ||
                         wallType === "seffaf_camli_perdeli_duvar" ||
                         wallType === "seffaf_sineklik_perdeli_camli_duvar")) {
        fabricOffset = fabric === "200_oxford" ? 1 : 0; // Sadece 690 ve oxford var
    }
    
    return baseId + sizeOffset + wallOffset + fabricOffset;
}

// Konfigüratörü sıfırlama fonksiyonu
function resetGazeboConfigurator() {
    // Seçim durumunu sıfırla
    if (typeof selectedConfig !== 'undefined') {
        selectedConfig.size = null;
        selectedConfig.fabric = null;
        selectedConfig.frame = 'none';
        selectedConfig.walls = {};
    }
    
    // Tüm seçimleri görsel olarak sıfırla
    document.querySelectorAll('.option-card.selected').forEach(card => {
        card.classList.remove('selected');
    });
    
    // Adımları devre dışı bırak
    document.querySelectorAll('.config-section').forEach(section => {
        if (section.id !== 'step-boyut') {
            section.classList.add('disabled');
        }
    });
    
    // İskelet seçimini sıfırla
    const frameNoneRadio = document.getElementById('frame_none');
    if (frameNoneRadio) {
        frameNoneRadio.checked = true;
    }
    
    // Duvar konfigürasyonunu temizle
    const wallConfig = document.getElementById('wall-configuration');
    if (wallConfig) {
        wallConfig.innerHTML = '';
    }
    
    // Özet ve fiyatı sıfırla
    const summaryDetails = document.getElementById('summary-details');
    const totalPrice = document.getElementById('total-price');
    if (summaryDetails) summaryDetails.innerHTML = '';
    if (totalPrice) totalPrice.textContent = '0,00 TL';
    
    console.log('Gazebo konfigüratörü sıfırlandı');
}
</script>