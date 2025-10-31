<?php
// Test dosyası - AJAX çalışıyor mu kontrol et
session_start();

// Test için session oluştur
$_SESSION['user_id'] = 4; // Admin user ID

require_once 'config/database.php';

echo "<h2>AJAX Test Sayfası</h2>";

// Test 1: Veritabanı bağlantısı
echo "<h3>1. Veritabanı Bağlantısı</h3>";
try {
    $stmt = $db->query("SELECT COUNT(*) as count FROM customers");
    $result = $stmt->fetch();
    echo "✅ Veritabanı bağlantısı başarılı. Toplam müşteri: " . $result['count'] . "<br>";
} catch (Exception $e) {
    echo "❌ Veritabanı hatası: " . $e->getMessage() . "<br>";
}

// Test 2: Customer Interactions tablosu
echo "<h3>2. Customer Interactions Tablosu</h3>";
try {
    $stmt = $db->query("DESCRIBE customer_interactions");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "✅ Tablo sütunları: " . implode(", ", $columns) . "<br>";
} catch (Exception $e) {
    echo "❌ Tablo hatası: " . $e->getMessage() . "<br>";
}

// Test 3: Customer Notes tablosu
echo "<h3>3. Customer Notes Tablosu</h3>";
try {
    $stmt = $db->query("DESCRIBE customer_notes");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "✅ Tablo sütunları: " . implode(", ", $columns) . "<br>";
} catch (Exception $e) {
    echo "❌ Tablo hatası: " . $e->getMessage() . "<br>";
}

// Test 4: İletişim kaydı ekleme testi
echo "<h3>4. İletişim Kaydı Ekleme Testi</h3>";
try {
    $stmt = $db->prepare("
        INSERT INTO customer_interactions (
            customer_id, user_id, type, subject, description, 
            interaction_date, status, created_at, updated_at
        ) VALUES (
            ?, ?, ?, ?, ?, NOW(), 'completed', NOW(), NOW()
        )
    ");
    
    $stmt->execute([
        75, // customer_id
        4,  // user_id
        'call',
        'Test İletişim',
        'Bu bir test kaydıdır'
    ]);
    
    echo "✅ İletişim kaydı eklendi. ID: " . $db->lastInsertId() . "<br>";
} catch (Exception $e) {
    echo "❌ İletişim ekleme hatası: " . $e->getMessage() . "<br>";
}

// Test 5: Not ekleme testi
echo "<h3>5. Not Ekleme Testi</h3>";
try {
    $stmt = $db->prepare("
        INSERT INTO customer_notes (
            customer_id, user_id, note, color, is_private, created_at, updated_at
        ) VALUES (
            ?, ?, ?, ?, ?, NOW(), NOW()
        )
    ");
    
    $stmt->execute([
        75, // customer_id
        4,  // user_id
        'Test notu - bu bir test kaydıdır',
        '#fbbf24',
        0
    ]);
    
    echo "✅ Not eklendi. ID: " . $db->lastInsertId() . "<br>";
} catch (Exception $e) {
    echo "❌ Not ekleme hatası: " . $e->getMessage() . "<br>";
}

// Test 6: AJAX dosyalarının varlığını kontrol et
echo "<h3>6. AJAX Dosyaları Kontrolü</h3>";
$files = [
    'modules/customers/add_interaction_ajax.php',
    'modules/customers/add_note_ajax.php'
];

foreach ($files as $file) {
    if (file_exists($file)) {
        echo "✅ $file mevcut<br>";
    } else {
        echo "❌ $file BULUNAMADI!<br>";
    }
}

echo "<hr>";
echo "<h3>AJAX Test Formu</h3>";
?>

<form id="testInteractionForm">
    <h4>İletişim Testi</h4>
    <input type="hidden" name="customer_id" value="75">
    <select name="type" required>
        <option value="call">Telefon</option>
        <option value="email">E-posta</option>
    </select>
    <input type="text" name="subject" placeholder="Konu" required>
    <textarea name="description" placeholder="Açıklama"></textarea>
    <button type="submit">Test Et</button>
</form>

<div id="result"></div>

<script>
document.getElementById('testInteractionForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch('modules/customers/add_interaction_ajax.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(text => {
        console.log('Raw response:', text);
        document.getElementById('result').innerHTML = '<pre>' + text + '</pre>';
        try {
            const data = JSON.parse(text);
            if (data.success) {
                alert('✅ Başarılı: ' + data.message);
            } else {
                alert('❌ Hata: ' + data.error);
            }
        } catch (e) {
            alert('❌ JSON Parse Hatası: ' + e.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('❌ Network Hatası: ' + error.message);
    });
});
</script>
