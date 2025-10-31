<?php
// setup_interactions_notes.php - Customer Interactions ve Notes tablolarını oluştur

require_once 'config/database.php';

try {
    // SQL dosyasını oku
    $sql = file_get_contents('database/customer_interactions_notes.sql');
    
    // SQL'i çalıştır
    $db->exec($sql);
    
    echo "✅ Customer Interactions ve Notes tabloları başarıyla oluşturuldu!\n";
    echo "📝 Örnek veriler eklendi.\n";
    echo "🔗 Artık müşteri detay sayfasından not ve iletişim ekleyebilirsiniz.\n";
    
} catch (Exception $e) {
    echo "❌ Hata: " . $e->getMessage() . "\n";
}
?>