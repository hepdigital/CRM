// globalFiyatData değişkeninin gazebofiyat.json'dan yüklendiği varsayılıyor.
// JSON dosyası içeriği burada globalFiyatData olarak erişilebilir.

let selectedConfig = {
    size: null,
    fabric: null,
    frame: 'none', // Başlangıçta iskelet yok
    walls: {} // {wall_id: {type: 'tam_duvar', price: 0}, ...}
};

// ====================================================================
// 1. DOM VE VERİ YÜKLEME
// ====================================================================

// Sayfa tamamen yüklendikten sonra çalıştır
window.addEventListener('load', () => {
    console.log("Sayfa tamamen yüklendi, globalFiyatData kontrol ediliyor...");
    console.log("globalFiyatData:", typeof globalFiyatData, globalFiyatData);

    // JSON verilerinin doğru yüklendiğini kontrol et
    if (typeof globalFiyatData === 'undefined' || !globalFiyatData.fiyat_tablolari) {
        console.error("Fiyat verileri (globalFiyatData) yüklenemedi veya formatı hatalı!");
        document.querySelector('.container').innerHTML = "<h1>Hata! Fiyat verileri yüklenemedi.</h1><p>Lütfen browser console'u kontrol edin.</p>";
        return;
    }

    console.log("Veriler başarıyla yüklendi, boyut seçenekleri oluşturuluyor...");
    renderSizeOptions();
    calculateAndRenderSummary();
});

// ====================================================================
// 2. SEÇENEK OLUŞTURMA FONKSİYONLARI
// ====================================================================

function renderSizeOptions() {
    console.log("renderSizeOptions çağrıldı");
    const sizeKeys = Object.keys(globalFiyatData.fiyat_tablolari);
    console.log("Bulunan boyutlar:", sizeKeys);

    const container = document.getElementById('size-options');
    if (!container) {
        console.error("size-options container bulunamadı!");
        return;
    }

    // Gazebo boyut görselleri
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

    container.innerHTML = '';

    sizeKeys.forEach(size => {
        console.log("Boyut kartı oluşturuluyor:", size);
        const imageUrl = sizeImages[size] || '';
        const imageHtml = imageUrl ? `<img src="${imageUrl}" alt="${size} Gazebo" style="width: 80px; height: 60px; object-fit: cover; border-radius: 4px;">` : `<i class="fas fa-expand-alt fa-2x"></i>`;

        const card = createCard('size_select', size, size + ' Gazebo', imageHtml);
        container.appendChild(card);
    });

    console.log("Boyut kartları oluşturuldu, dinleyici ekleniyor...");
    addSelectionListener('size_select', handleSizeSelection);
    console.log("renderSizeOptions tamamlandı");
}

function renderFabricOptions() {
    const fabricKeys = Object.keys(globalFiyatData.genislik_bazli_fiyatlar['2m']); // Kumaş tiplerini herhangi bir genişlikten al
    const container = document.getElementById('fabric-options');
    container.innerHTML = '';

    const fabricNames = {
        "690_denye": "690 Denye Kaplamalı Kumaş",
        "200_oxford": "200 Gram Oxford Kumaş",
        "dijital_baski": "Dijital Baskılı Kumaş"
    };

    const fabricIcons = {
        "690_denye": '<i class="fas fa-shield-alt fa-2x" style="color: #2c5aa0;"></i>',
        "200_oxford": '<i class="fas fa-tshirt fa-2x" style="color: #28a745;"></i>',
        "dijital_baski": '<i class="fas fa-print fa-2x" style="color: #dc3545;"></i>'
    };

    fabricKeys.forEach(fabricKey => {
        const name = fabricNames[fabricKey] || fabricKey;
        const icon = fabricIcons[fabricKey] || '<i class="fas fa-fill-drip fa-2x"></i>';
        const card = createCard('fabric_select', fabricKey, name, icon);
        container.appendChild(card);
    });

    addSelectionListener('fabric_select', handleFabricSelection);
    updateSectionStatus('step-kumas', true);
}

function renderFrameOptions() {
    const sizeData = globalFiyatData.fiyat_tablolari[selectedConfig.size];
    const frameKeys = Object.keys(sizeData.iskelet_aksesuar).filter(k => !k.includes('oluk') && !k.includes('canta'));
    const container = document.getElementById('frame-options');
    container.innerHTML = '';

    // İskelet görselleri
    const frameImages = {
        "30luk_celik": "https://skygorselmarket.com/wp-content/uploads/2024/09/30luk-celik-iskelet-768x768.webp",
        "40luk_celik": "https://skygorselmarket.com/wp-content/uploads/2024/09/40lik-celik-iskelet-768x768.webp",
        "40lik_aluminyum": "https://skygorselmarket.com/wp-content/uploads/2024/09/40lik-aluminyum-iskelet-768x768.webp",
        "52lik_aluminyum": "https://skygorselmarket.com/wp-content/uploads/2024/09/52lik-aluminyum-iskelet-768x768.webp"
    };

    frameKeys.forEach(frameKey => {
        const price = sizeData.iskelet_aksesuar[frameKey];
        const isAvailable = price > 0;
        const formattedPrice = isAvailable ? price.toFixed(2) + ' TL' : 'Mevcut Değil';
        const name = frameKey.replace(/_/g, ' ').toUpperCase();

        const imageUrl = frameImages[frameKey] || '';
        const imageHtml = imageUrl ? `<img src="${imageUrl}" alt="${name}" style="width: 80px; height: 60px; object-fit: cover; border-radius: 4px;">` : `<i class="fas fa-ruler-combined fa-2x"></i>`;

        // Mevcut olmayan ürünler disabled olarak işaretlenir
        const card = createCard('frame_type', frameKey, name, imageHtml, formattedPrice, !isAvailable);
        container.appendChild(card);
    });

    // Opsiyonel iskelet seçimi için dinleyici
    addSelectionListener('frame_type', handleFrameSelection);
    // Seçili iskelet yoksa varsayılanı işaretle
    const frameNoneRadio = document.getElementById('frame_none');
    if (frameNoneRadio) {
        frameNoneRadio.checked = true;
        selectedConfig.frame = 'none';
    }

    updateSectionStatus('step-iskelet', true);
}

function renderWallConfiguration() {
    const size = selectedConfig.size; // Örn: '4x6'
    const [shortSide, longSide] = size.split('x').map(s => s + 'm'); // ['4m', '6m']
    const wallContainer = document.getElementById('wall-configuration');
    wallContainer.innerHTML = '';

    // Duvarlar ve referans genişlikleri
    const walls = [
        { id: 'long_wall_1', title: `Uzun Kenar 1 (${longSide})`, width: longSide, default: 'tam_duvar' },
        { id: 'long_wall_2', title: `Uzun Kenar 2 (${longSide})`, width: longSide, default: 'tam_duvar' },
        { id: 'short_wall_1', title: `Kısa Kenar 1 (${shortSide})`, width: shortSide, default: 'tam_duvar' },
        { id: 'short_wall_2', title: `Kısa Kenar 2 (${shortSide})`, width: shortSide, default: 'tam_duvar' },
    ];

    walls.forEach(wall => {
        const wallGroup = document.createElement('div');
        wallGroup.className = 'wall-group';
        wallGroup.innerHTML = `<h3>${wall.title}</h3><div class="options-grid" id="wall-${wall.id}-options"></div>`;
        wallContainer.appendChild(wallGroup);

        renderWallOptionsForSide(wall.id, wall.width, `wall_select_${wall.id}`);
    });

    updateSectionStatus('step-duvar', true);
}

function renderWallOptionsForSide(wallId, width, radioName) {
    console.log("renderWallOptionsForSide çağrıldı:", wallId, width, radioName);

    const wallOptionsContainer = document.getElementById(`wall-${wallId}-options`);
    if (!wallOptionsContainer) {
        console.error("Wall options container bulunamadı:", `wall-${wallId}-options`);
        return;
    }

    wallOptionsContainer.innerHTML = '';

    const wallTypes = globalFiyatData.genislik_bazli_fiyatlar[width][selectedConfig.fabric];

    const wallNames = {
        "tam_duvar": "Tam Duvar",
        "yarim_duvar": "Yarım Duvar",
        "seffaf_duvar": "Şeffaf Duvar",
        "fermuarli_duvar": "Fermuarlı Duvar",
        "sineklik_duvar": "Sineklik Duvar",
        "kapili_duvar": "Kapılı Duvar",
        "sineklikli_kapi": "Sineklikli Kapı",
        "kapi_seffaf_pencere": "Kapı + Şeffaf Pencere",
        "sineklikli_kapi_seffaf_perde": "Sineklikli Kapı + Şeffaf Perde",
        "seffaf_sineklik_perde_kapi": "Şeffaf Sineklik Perde + Kapı",
        "seffaf_camli_duvar": "Şeffaf Camlı Duvar",
        "seffaf_camli_perdeli_duvar": "Şeffaf Camlı Perdeli Duvar",
        "seffaf_sineklik_perdeli_camli_duvar": "Şeffaf Sineklik Perdeli Camlı Duvar"
    };
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

    // JSON'daki her duvar tipi için kart oluştur
    Object.keys(wallTypes).forEach(typeKey => {
        if (typeKey === 'cati_sacak') return; // Çatı/Saçak duvar değil

        const price = wallTypes[typeKey];
        const isAvailable = price > 0;
        const formattedPrice = isAvailable ? price.toFixed(2) + ' TL' : 'Mevcut Değil';
        const name = wallNames[typeKey] || typeKey.replace(/_/g, ' ').toUpperCase();

        const imageUrl = wallImages[typeKey] || '';
        const imageHtml = imageUrl ? `<img src="${imageUrl}" alt="${name}" style="width: 80px; height: 60px; object-fit: cover; border-radius: 4px;">` : `<i class="fas fa-border-all fa-2x"></i>`;

        const card = createCard(radioName, typeKey, name, imageHtml, formattedPrice, !isAvailable);
        wallOptionsContainer.appendChild(card);
    });

    // 'Duvar Yok' seçeneği ekle
    const noneCard = createCard(radioName, 'none', 'Duvar Yok', `<i class="fas fa-ban fa-2x"></i>`, '0,00 TL');
    wallOptionsContainer.appendChild(noneCard);

    // Duvar seçim dinleyicisini ekle - özel handler ile
    setTimeout(() => {
        const wallRadios = document.querySelectorAll(`input[name="${radioName}"]`);
        console.log(`${radioName} için bulunan radio sayısı:`, wallRadios.length);

        wallRadios.forEach(radio => {
            radio.addEventListener('change', function (e) {
                // Disabled ürün seçilmeye çalışılırsa engelle
                if (e.target.disabled || e.target.nextElementSibling?.classList.contains('disabled-card')) {
                    e.preventDefault();
                    e.stopPropagation();
                    return false;
                }

                console.log(`Duvar seçildi: ${radioName} = ${e.target.value}`);

                // Görsel güncelleme
                document.querySelectorAll(`input[name="${radioName}"]`).forEach(r => {
                    const label = r.nextElementSibling;
                    if (label && label.classList.contains('option-card')) {
                        label.classList.remove('selected');
                    }
                });

                const selectedLabel = e.target.nextElementSibling;
                if (selectedLabel && selectedLabel.classList.contains('option-card')) {
                    selectedLabel.classList.add('selected');
                }

                handleWallSelection(radioName, e.target.value, width);
            });
        });

        // Varsayılan seçimi yap
        const defaultWall = document.querySelector(`input[name="${radioName}"][value="none"]`);
        if (defaultWall) {
            defaultWall.checked = true;
            const defaultLabel = defaultWall.nextElementSibling;
            if (defaultLabel) {
                defaultLabel.classList.add('selected');
            }
            handleWallSelection(radioName, 'none', width);
        }
    }, 200);
}

// ====================================================================
// 3. SEÇİM İŞLEYİCİLERİ
// ====================================================================

function handleSizeSelection(size) {
    if (selectedConfig.size === size) return;

    selectedConfig.size = size;
    selectedConfig.fabric = null; // Kumaş seçimini sıfırla

    // Sonraki adımları tekrar yükle
    renderFabricOptions();
    // Duvar ve iskelet seçeneklerini sıfırla/devre dışı bırak
    updateSectionStatus('step-iskelet', false);
    updateSectionStatus('step-duvar', false);
    document.getElementById('frame-options').innerHTML = '';
    document.getElementById('wall-configuration').innerHTML = '';

    calculateAndRenderSummary();
}

function handleFabricSelection(fabric) {
    if (selectedConfig.fabric === fabric) return;

    selectedConfig.fabric = fabric;

    // Kumaş seçimi ile duvar ve iskelet adımları aktifleşir
    renderFrameOptions();
    renderWallConfiguration();

    calculateAndRenderSummary();
}

function handleFrameSelection(frame) {
    // Disabled ürün seçilmeye çalışılırsa engelle
    const frameRadio = document.querySelector(`input[name="frame_type"][value="${frame}"]`);
    if (frameRadio && (frameRadio.disabled || frameRadio.nextElementSibling?.classList.contains('disabled-card'))) {
        return false;
    }

    selectedConfig.frame = frame;
    calculateAndRenderSummary();
}

function handleWallSelection(radioName, wallType, width) {
    console.log("handleWallSelection çağrıldı:", radioName, wallType, width);

    // Disabled ürün seçilmeye çalışılırsa engelle
    const wallRadio = document.querySelector(`input[name="${radioName}"][value="${wallType}"]`);
    if (wallRadio && (wallRadio.disabled || wallRadio.nextElementSibling?.classList.contains('disabled-card'))) {
        return false;
    }

    const wallId = radioName.replace('wall_select_', '');
    const fabric = selectedConfig.fabric;

    let price = 0;
    let name = 'Duvar Yok';

    if (wallType !== 'none') {
        const wallData = globalFiyatData.genislik_bazli_fiyatlar[width][fabric];
        price = wallData[wallType] || 0;

        // Mevcut olmayan ürün seçilmeye çalışılırsa engelle
        if (price <= 0) {
            console.log("Mevcut olmayan ürün seçilmeye çalışıldı:", wallType);
            return false;
        }

        // İsmi radio button'dan al
        const radioElement = document.querySelector(`input[name="${radioName}"][value="${wallType}"]`);
        if (radioElement && radioElement.nextElementSibling) {
            const spans = radioElement.nextElementSibling.querySelectorAll('span');
            if (spans.length > 0) {
                name = spans[0].textContent;
            }
        }
    }

    selectedConfig.walls[wallId] = {
        type: wallType,
        width: width,
        price: price,
        name: name
    };

    console.log("Duvar konfigürasyonu güncellendi:", selectedConfig.walls[wallId]);
    calculateAndRenderSummary();
}

// ====================================================================
// 4. HESAPLAMA VE ÖZET
// ====================================================================

function calculateAndRenderSummary() {
    let totalPrice = 0;
    const summaryDetails = document.getElementById('summary-details');
    summaryDetails.innerHTML = '';

    // Seçimler tam değilse özeti göster
    if (!selectedConfig.size || !selectedConfig.fabric) {
        summaryDetails.innerHTML = '<p class="info-text"><i class="fas fa-exclamation-triangle"></i> Lütfen Gazebo Ölçüsü ve Kumaş Türünü seçiniz.</p>';
        document.getElementById('total-price').textContent = '0,00 TL';
        return;
    }

    const sizeData = globalFiyatData.fiyat_tablolari[selectedConfig.size];
    const fabricPriceData = globalFiyatData.genislik_bazli_fiyatlar[sizeData.genislik][selectedConfig.fabric];

    // Görsel URL'leri
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

    const frameImages = {
        "30luk_celik": "https://skygorselmarket.com/wp-content/uploads/2024/09/30luk-celik-iskelet-768x768.webp",
        "40luk_celik": "https://skygorselmarket.com/wp-content/uploads/2024/09/40lik-celik-iskelet-768x768.webp",
        "40lik_aluminyum": "https://skygorselmarket.com/wp-content/uploads/2024/09/40lik-aluminyum-iskelet-768x768.webp",
        "52lik_aluminyum": "https://skygorselmarket.com/wp-content/uploads/2024/09/52lik-aluminyum-iskelet-768x768.webp"
    };

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

    const fabricNames = {
        "690_denye": "690 Denye Kaplamalı Kumaş",
        "200_oxford": "200 Gram Oxford Kumaş",
        "dijital_baski": "Dijital Baskılı Kumaş"
    };

    // ANA GAZEBO BİLGİLERİ
    const mainInfoSection = document.createElement('div');
    mainInfoSection.className = 'summary-main-info';
    mainInfoSection.innerHTML = `
        <div class="main-product-card">
            <img src="${sizeImages[selectedConfig.size]}" alt="${selectedConfig.size} Gazebo" class="main-product-image">
            <div class="main-product-details">
                <h3>${selectedConfig.size} Gazebo</h3>
                <p><strong>Kumaş:</strong> ${fabricNames[selectedConfig.fabric]}</p>
                <p><strong>Genişlik:</strong> ${sizeData.genislik}</p>
            </div>
        </div>
    `;
    summaryDetails.appendChild(mainInfoSection);

    // 1. ÇATI VE SAÇAK FİYATI (Zorunlu)
    const roofPrice = fabricPriceData.cati_sacak;
    totalPrice += roofPrice;
    renderSummaryItemWithImage('Çatı ve Saçak (Zorunlu)', roofPrice, 'main', sizeImages[selectedConfig.size]);

    // 2. İSKELET FİYATI (Opsiyonel)
    if (selectedConfig.frame !== 'none' && sizeData.iskelet_aksesuar[selectedConfig.frame] > 0) {
        const framePrice = sizeData.iskelet_aksesuar[selectedConfig.frame];
        totalPrice += framePrice;
        const frameName = selectedConfig.frame.replace(/_/g, ' ').toUpperCase();
        renderSummaryItemWithImage(`İskelet (${frameName})`, framePrice, 'main', frameImages[selectedConfig.frame]);
    } else if (selectedConfig.frame === 'none') {
        renderSummaryItemWithImage('İskelet', 'Yok', 'main', null);
    }

    // 3. DUVARLAR (Opsiyonel)
    const wallCategoryTitle = document.createElement('div');
    wallCategoryTitle.className = 'category-title';
    wallCategoryTitle.innerHTML = '<h4><i class="fas fa-border-all"></i> Duvar Konfigürasyonu</h4>';
    summaryDetails.appendChild(wallCategoryTitle);

    // Gazebo'nun dört kenarı
    const [shortSide, longSide] = selectedConfig.size.split('x').map(s => s + 'm');
    const wallSides = [
        { id: 'long_wall_1', name: `Uzun Kenar 1 (${longSide})` },
        { id: 'long_wall_2', name: `Uzun Kenar 2 (${longSide})` },
        { id: 'short_wall_1', name: `Kısa Kenar 1 (${shortSide})` },
        { id: 'short_wall_2', name: `Kısa Kenar 2 (${shortSide})` },
    ];

    wallSides.forEach(wallSide => {
        const wallConfig = selectedConfig.walls[wallSide.id];
        if (wallConfig && wallConfig.type !== 'none' && wallConfig.price > 0) {
            totalPrice += wallConfig.price;
            renderSummaryItemWithImage(`${wallSide.name}: ${wallConfig.name}`, wallConfig.price, 'wall', wallImages[wallConfig.type]);
        } else {
            renderSummaryItemWithImage(`${wallSide.name}`, 'Duvar Yok', 'wall', null);
        }
    });

    // Genel Toplamı Güncelle
    document.getElementById('total-price').textContent = totalPrice.toFixed(2).replace('.', ',') + ' TL';
}

// ====================================================================
// 5. YARDIMCI FONKSİYONLAR
// ====================================================================

function createCard(radioName, value, label, iconHtml, priceText = '', isDisabled = false) {
    const id = `${radioName}_${value}`;

    // Disabled ürünler için özel işaretleme
    const disabledClass = isDisabled ? 'disabled-card' : '';
    const disabledAttr = isDisabled ? 'disabled' : '';

    // Doğrudan HTML string döndür
    const cardHTML = `
        <input type="radio" name="${radioName}" id="${id}" value="${value}" class="hidden-radio" ${disabledAttr}>
        <label for="${id}" class="option-card ${disabledClass}" ${isDisabled ? 'data-disabled="true"' : ''}>
            ${iconHtml}
            <span>${label}</span>
            <span class="price-text">${priceText}</span>
        </label>
    `;

    const wrapper = document.createElement('div');
    wrapper.innerHTML = cardHTML;

    // Disabled kartlara tıklama engelleyici ekle
    if (isDisabled) {
        const label = wrapper.querySelector('label');
        if (label) {
            label.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                return false;
            });
        }
    }

    return wrapper;
}

function addSelectionListener(radioName, handler) {
    console.log("addSelectionListener çağrıldı:", radioName);

    // Kısa bir gecikme ile elementlerin DOM'a eklenmesini bekle
    setTimeout(() => {
        const radios = document.querySelectorAll(`input[name="${radioName}"]`);
        console.log("Bulunan radio elementler:", radios.length);

        radios.forEach((radio, index) => {
            console.log(`Radio ${index}:`, radio.id, radio.value);

            radio.addEventListener('change', function (e) {
                // Disabled ürün seçilmeye çalışılırsa engelle
                if (e.target.disabled || e.target.nextElementSibling?.classList.contains('disabled-card')) {
                    e.preventDefault();
                    e.stopPropagation();
                    return false;
                }

                console.log("Radio seçildi:", e.target.value);

                // Görsel güncelleme
                document.querySelectorAll(`input[name="${radioName}"]`).forEach(r => {
                    const label = r.nextElementSibling;
                    if (label && label.classList.contains('option-card')) {
                        label.classList.remove('selected');
                    }
                });

                const selectedLabel = e.target.nextElementSibling;
                if (selectedLabel && selectedLabel.classList.contains('option-card')) {
                    selectedLabel.classList.add('selected');
                }

                handler(e.target.value);
            });
        });
    }, 100);
}

function updateSectionStatus(sectionId, enable) {
    const section = document.getElementById(sectionId);
    if (enable) {
        section.classList.remove('disabled');
    } else {
        section.classList.add('disabled');
    }
}

function renderSummaryItem(name, price, type) {
    const summaryDetails = document.getElementById('summary-details');
    const item = document.createElement('div');
    item.className = 'summary-item';

    let priceDisplay = (typeof price === 'number') ? price.toFixed(2).replace('.', ',') + ' TL' : price;
    let nameDisplay = name;

    if (type === 'main') {
        nameDisplay = `<strong>${name}</strong>`;
    }

    item.innerHTML = `
        <span>${nameDisplay}</span>
        <span>${priceDisplay}</span>
    `;
    summaryDetails.appendChild(item);
}

function renderSummaryItemWithImage(name, price, type, imageUrl) {
    const summaryDetails = document.getElementById('summary-details');
    const item = document.createElement('div');
    item.className = `summary-item-with-image ${type}`;

    let priceDisplay = (typeof price === 'number') ? price.toFixed(2).replace('.', ',') + ' TL' : price;
    let nameDisplay = name;

    if (type === 'main') {
        nameDisplay = `<strong>${name}</strong>`;
    }

    const imageHtml = imageUrl ?
        `<img src="${imageUrl}" alt="${name}" class="summary-item-image">` :
        `<div class="summary-item-no-image"><i class="fas fa-ban"></i></div>`;

    item.innerHTML = `
        <div class="summary-item-content">
            ${imageHtml}
            <div class="summary-item-info">
                <span class="summary-item-name">${nameDisplay}</span>
                <span class="summary-item-price">${priceDisplay}</span>
            </div>
        </div>
    `;
    summaryDetails.appendChild(item);
}