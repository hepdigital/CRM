<?php
// modules/products/image_manager.php
require_once '../../includes/header.php';

// Sadece admin erişebilir
if ($_SESSION['role'] !== 'admin') {
    header('Location: ../../index.php');
    exit;
}

$message = '';
$messageType = '';

// AJAX istekleri için
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    // Hata raporlamayı kapat
    error_reporting(0);
    ini_set('display_errors', 0);
    
    // Output buffering'i temizle
    if (ob_get_level()) {
        ob_end_clean();
    }
    
    header('Content-Type: application/json');
    
    try {
        switch ($_POST['action']) {
            case 'download_image':
                $productId = (int)$_POST['product_id'];
                $imageUrl = $_POST['image_url'];
                
                $result = downloadImageFromUrl($imageUrl);
                if ($result) {
                    // Veritabanını güncelle
                    $stmt = $db->prepare("UPDATE products SET image_url = ? WHERE id = ?");
                    $stmt->execute([$result, $productId]);
                    echo json_encode(['success' => true, 'new_url' => $result]);
                } else {
                    echo json_encode(['success' => false, 'error' => 'Görsel indirilemedi']);
                }
                exit;
                
            case 'download_all':
                $stmt = $db->query("SELECT id, name, image_url FROM products WHERE image_url IS NOT NULL AND image_url != '' AND image_url LIKE 'http%'");
                $products = $stmt->fetchAll();
                
                $successCount = 0;
                $errorCount = 0;
                $errors = [];
                
                foreach ($products as $product) {
                    try {
                        $newUrl = downloadImageFromUrl($product['image_url']);
                        if ($newUrl) {
                            $updateStmt = $db->prepare("UPDATE products SET image_url = ? WHERE id = ?");
                            $updateStmt->execute([$newUrl, $product['id']]);
                            $successCount++;
                        } else {
                            $errorCount++;
                            $errors[] = "Ürün ID {$product['id']}: Görsel indirilemedi";
                        }
                    } catch (Exception $e) {
                        $errorCount++;
                        $errors[] = "Ürün ID {$product['id']}: " . $e->getMessage();
                    }
                }
                
                echo json_encode([
                    'success' => true,
                    'success_count' => $successCount,
                    'error_count' => $errorCount,
                    'errors' => $errors
                ]);
                exit;
                
            case 'download_progress':
                // Anlık indirme için yeni endpoint
                $productId = (int)$_POST['product_id'];
                $imageUrl = $_POST['image_url'];
                
                $result = downloadImageFromUrl($imageUrl);
                if ($result) {
                    // Veritabanını güncelle
                    $stmt = $db->prepare("UPDATE products SET image_url = ? WHERE id = ?");
                    $stmt->execute([$result, $productId]);
                    echo json_encode([
                        'success' => true, 
                        'product_id' => $productId,
                        'new_url' => $result,
                        'message' => 'Başarıyla indirildi'
                    ]);
                } else {
                    echo json_encode([
                        'success' => false, 
                        'product_id' => $productId,
                        'error' => 'Görsel indirilemedi'
                    ]);
                }
                exit;
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        exit;
    }
}

// Dış URL'den görsel indirme fonksiyonu
function downloadImageFromUrl($url) {
    if (empty($url)) return false;
    
    // Zaten yerel dosya mı kontrol et
    if (strpos($url, 'http') !== 0) {
        return $url;
    }
    
    try {
        // Hedef klasörü kontrol et
        $uploadDir = '../../assets/uploads/products/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        // Dosya uzantısını belirle
        $pathInfo = pathinfo(parse_url($url, PHP_URL_PATH));
        $extension = isset($pathInfo['extension']) ? strtolower($pathInfo['extension']) : 'jpg';
        
        // Geçerli uzantı kontrolü
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (!in_array($extension, $allowedExtensions)) {
            $extension = 'jpg';
        }
        
        // Benzersiz dosya adı oluştur
        $fileName = 'product_' . uniqid() . '.' . $extension;
        $filePath = $uploadDir . $fileName;
        
        // Dosyayı indir (daha güvenli yöntem)
        $context = stream_context_create([
            'http' => [
                'timeout' => 30,
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                'follow_location' => true,
                'max_redirects' => 5
            ]
        ]);
        
        $imageData = file_get_contents($url, false, $context);
        if ($imageData === false) {
            return false;
        }
        
        // Dosya boyutunu kontrol et (5MB limit)
        if (strlen($imageData) > 5 * 1024 * 1024) {
            return false;
        }
        
        // Dosyayı kaydet
        if (file_put_contents($filePath, $imageData) === false) {
            return false;
        }
        
        // Yerel URL döndür
        return 'assets/uploads/products/' . $fileName;
        
    } catch (Exception $e) {
        return false;
    }
}

// İstatistikleri getir
$stats = [];

// Toplam ürün sayısı
$stmt = $db->query("SELECT COUNT(*) as total FROM products");
$stats['total_products'] = $stmt->fetch()['total'];

// Dış URL'li ürün sayısı
$stmt = $db->query("SELECT COUNT(*) as count FROM products WHERE image_url IS NOT NULL AND image_url != '' AND image_url LIKE 'http%'");
$stats['external_urls'] = $stmt->fetch()['count'];

// Yerel görseli olan ürün sayısı
$stmt = $db->query("SELECT COUNT(*) as count FROM products WHERE image_url IS NOT NULL AND image_url != '' AND image_url NOT LIKE 'http%'");
$stats['local_images'] = $stmt->fetch()['count'];

// Görseli olmayan ürün sayısı
$stats['no_images'] = $stats['total_products'] - $stats['external_urls'] - $stats['local_images'];

// Dış URL'li ürünleri getir
$stmt = $db->query("
    SELECT id, name, image_url, created_at 
    FROM products 
    WHERE image_url IS NOT NULL AND image_url != '' AND image_url LIKE 'http%'
    ORDER BY created_at DESC
");
$externalProducts = $stmt->fetchAll();
?>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Görsel Yöneticisi</h5>
        <a href="list.php" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Ürün Listesi
        </a>
    </div>
    <div class="card-body">
        <!-- İstatistikler -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body text-center">
                        <h3><?php echo $stats['total_products']; ?></h3>
                        <p class="mb-0">Toplam Ürün</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning text-white">
                    <div class="card-body text-center">
                        <h3><?php echo $stats['external_urls']; ?></h3>
                        <p class="mb-0">Dış URL Görsel</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body text-center">
                        <h3><?php echo $stats['local_images']; ?></h3>
                        <p class="mb-0">Yerel Görsel</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-secondary text-white">
                    <div class="card-body text-center">
                        <h3><?php echo $stats['no_images']; ?></h3>
                        <p class="mb-0">Görsel Yok</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Toplu İşlemler -->
        <?php if ($stats['external_urls'] > 0): ?>
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h6 class="card-title">Toplu İşlemler</h6>
                        <p class="text-muted">Tüm dış URL görsellerini yerel klasöre indirin.</p>
                        <button type="button" class="btn btn-primary" id="downloadAllBtn">
                            <i class="fas fa-download"></i> Tümünü İndir (<?php echo $stats['external_urls']; ?> adet)
                        </button>
                        <button type="button" class="btn btn-success" id="downloadProgressBtn" style="display: none;">
                            <i class="fas fa-pause"></i> Durdur
                        </button>
                        
                        <div id="downloadProgress" class="mt-3" style="display: none;">
                            <div class="progress mb-2">
                                <div class="progress-bar" role="progressbar" style="width: 0%"></div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <small class="text-muted" id="progressText">İşlem başlatılıyor...</small>
                                </div>
                                <div class="col-md-6 text-end">
                                    <small class="text-muted" id="progressStats">0 / 0</small>
                                </div>
                            </div>
                            
                            <!-- Anlık İşlem Listesi -->
                            <div class="mt-3" id="progressList" style="max-height: 300px; overflow-y: auto;">
                                <div class="list-group" id="progressItems">
                                    <!-- Anlık işlemler buraya eklenecek -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Dış URL'li Ürünler Listesi -->
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">Dış URL'li Ürünler (<?php echo count($externalProducts); ?> adet)</h6>
            </div>
            <div class="card-body">
                <?php if (empty($externalProducts)): ?>
                    <div class="text-center text-muted py-4">
                        <i class="fas fa-check-circle fa-3x mb-3"></i>
                        <p>Tüm görseller yerel olarak saklanıyor!</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Ürün</th>
                                    <th>Görsel URL</th>
                                    <th>Durum</th>
                                    <th>İşlemler</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($externalProducts as $product): ?>
                                <tr data-product-id="<?php echo $product['id']; ?>">
                                    <td>
                                        <strong><?php echo htmlspecialchars($product['name']); ?></strong>
                                        <br>
                                        <small class="text-muted">ID: <?php echo $product['id']; ?></small>
                                    </td>
                                    <td>
                                        <a href="<?php echo htmlspecialchars($product['image_url']); ?>" 
                                           target="_blank" class="text-truncate d-block" style="max-width: 200px;">
                                            <?php echo htmlspecialchars($product['image_url']); ?>
                                        </a>
                                    </td>
                                    <td>
                                        <span class="badge bg-warning">Dış URL</span>
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-primary download-single-btn" 
                                                data-product-id="<?php echo $product['id']; ?>"
                                                data-image-url="<?php echo htmlspecialchars($product['image_url']); ?>">
                                            <i class="fas fa-download"></i> İndir
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
// Tekil görsel indirme
document.querySelectorAll('.download-single-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const productId = this.dataset.productId;
        const imageUrl = this.dataset.imageUrl;
        const row = this.closest('tr');
        
        this.disabled = true;
        this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> İndiriliyor...';
        
        fetch('image_manager.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=download_image&product_id=${productId}&image_url=${encodeURIComponent(imageUrl)}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                row.querySelector('.badge').className = 'badge bg-success';
                row.querySelector('.badge').textContent = 'Yerel';
                this.innerHTML = '<i class="fas fa-check"></i> İndirildi';
                this.className = 'btn btn-sm btn-success';
            } else {
                this.innerHTML = '<i class="fas fa-times"></i> Hata';
                this.className = 'btn btn-sm btn-danger';
                alert('Hata: ' + (data.error || 'Bilinmeyen hata'));
            }
        })
        .catch(error => {
            this.innerHTML = '<i class="fas fa-times"></i> Hata';
            this.className = 'btn btn-sm btn-danger';
            console.error('Error:', error);
        });
    });
});

// Toplu indirme (Anlık görsel indirme)
let isDownloading = false;
let downloadQueue = [];
let currentIndex = 0;
let successCount = 0;
let errorCount = 0;

document.getElementById('downloadAllBtn')?.addEventListener('click', function() {
    if (!confirm('Tüm dış URL görsellerini indirmek istediğinizden emin misiniz?')) {
        return;
    }
    
    // Dış URL'li ürünleri al
    const externalRows = document.querySelectorAll('tr[data-product-id]');
    downloadQueue = [];
    
    externalRows.forEach(row => {
        const productId = row.dataset.productId;
        const imageUrl = row.querySelector('.download-single-btn').dataset.imageUrl;
        const productName = row.querySelector('strong').textContent;
        
        downloadQueue.push({
            id: productId,
            url: imageUrl,
            name: productName,
            row: row
        });
    });
    
    if (downloadQueue.length === 0) {
        alert('İndirilecek görsel bulunamadı!');
        return;
    }
    
    // UI'yi güncelle
    this.style.display = 'none';
    document.getElementById('downloadProgressBtn').style.display = 'inline-block';
    
    const progressDiv = document.getElementById('downloadProgress');
    const progressBar = progressDiv.querySelector('.progress-bar');
    const progressText = document.getElementById('progressText');
    const progressStats = document.getElementById('progressStats');
    const progressItems = document.getElementById('progressItems');
    
    progressDiv.style.display = 'block';
    progressItems.innerHTML = '';
    
    // İndirme işlemini başlat
    isDownloading = true;
    currentIndex = 0;
    successCount = 0;
    errorCount = 0;
    
    downloadNext();
});

// Durdur butonu
document.getElementById('downloadProgressBtn')?.addEventListener('click', function() {
    isDownloading = false;
    this.innerHTML = '<i class="fas fa-stop"></i> Durduruldu';
    this.className = 'btn btn-danger';
});

// Sıradaki görseli indir
function downloadNext() {
    if (!isDownloading || currentIndex >= downloadQueue.length) {
        // İşlem tamamlandı
        finishDownload();
        return;
    }
    
    const item = downloadQueue[currentIndex];
    const progressBar = document.querySelector('.progress-bar');
    const progressText = document.getElementById('progressText');
    const progressStats = document.getElementById('progressStats');
    const progressItems = document.getElementById('progressItems');
    
    // Progress bar güncelle
    const progress = ((currentIndex + 1) / downloadQueue.length) * 100;
    progressBar.style.width = progress + '%';
    progressStats.textContent = `${currentIndex + 1} / ${downloadQueue.length}`;
    progressText.textContent = `İndiriliyor: ${item.name}`;
    
    // Anlık işlem öğesi ekle
    const progressItem = document.createElement('div');
    progressItem.className = 'list-group-item d-flex justify-content-between align-items-center';
    progressItem.id = `progress-item-${item.id}`;
    progressItem.innerHTML = `
        <div>
            <strong>${item.name}</strong>
            <br>
            <small class="text-muted">ID: ${item.id}</small>
        </div>
        <div>
            <span class="badge bg-warning" id="status-${item.id}">
                <i class="fas fa-spinner fa-spin"></i> İndiriliyor...
            </span>
        </div>
    `;
    progressItems.appendChild(progressItem);
    
    // Scroll to bottom
    progressItems.scrollTop = progressItems.scrollHeight;
    
    // AJAX isteği gönder
    fetch('image_manager.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=download_progress&product_id=${item.id}&image_url=${encodeURIComponent(item.url)}`
    })
    .then(response => response.json())
    .then(data => {
        const statusBadge = document.getElementById(`status-${item.id}`);
        
        if (data.success) {
            // Başarılı
            successCount++;
            statusBadge.className = 'badge bg-success';
            statusBadge.innerHTML = '<i class="fas fa-check"></i> Başarılı';
            
            // Tablodaki satırı güncelle
            item.row.querySelector('.badge').className = 'badge bg-success';
            item.row.querySelector('.badge').textContent = 'Yerel';
            item.row.querySelector('.download-single-btn').innerHTML = '<i class="fas fa-check"></i> İndirildi';
            item.row.querySelector('.download-single-btn').className = 'btn btn-sm btn-success';
        } else {
            // Hatalı
            errorCount++;
            statusBadge.className = 'badge bg-danger';
            statusBadge.innerHTML = '<i class="fas fa-times"></i> Hata';
        }
        
        currentIndex++;
        
        // Kısa bir bekleme sonra devam et
        setTimeout(() => {
            downloadNext();
        }, 500);
    })
    .catch(error => {
        errorCount++;
        const statusBadge = document.getElementById(`status-${item.id}`);
        statusBadge.className = 'badge bg-danger';
        statusBadge.innerHTML = '<i class="fas fa-times"></i> Hata';
        
        currentIndex++;
        setTimeout(() => {
            downloadNext();
        }, 500);
    });
}

// İndirme işlemini bitir
function finishDownload() {
    isDownloading = false;
    
    const progressText = document.getElementById('progressText');
    const downloadBtn = document.getElementById('downloadAllBtn');
    const stopBtn = document.getElementById('downloadProgressBtn');
    
    progressText.textContent = `Tamamlandı! ${successCount} başarılı, ${errorCount} hatalı`;
    
    downloadBtn.style.display = 'inline-block';
    stopBtn.style.display = 'none';
    stopBtn.innerHTML = '<i class="fas fa-pause"></i> Durdur';
    stopBtn.className = 'btn btn-success';
    
    // 3 saniye sonra sayfayı yenile
    setTimeout(() => {
        window.location.reload();
    }, 3000);
}
</script>

<?php require_once '../../includes/footer.php'; ?>
