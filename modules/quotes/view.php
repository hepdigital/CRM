<?php
// modules/quotes/view.php
require_once '../../includes/header.php';

if (!isset($_GET['id'])) {
    header('Location: list.php');
    exit;
}

$id = (int)$_GET['id'];

// Teklif ve müşteri bilgilerini getir
$stmt = $db->prepare("
    SELECT q.*, c.*, u.full_name as created_by_name,
           q.id as quote_id,
           c.id as customer_id,
           q.created_at as quote_date
    FROM quotes q
    JOIN customers c ON q.customer_id = c.id
    LEFT JOIN users u ON q.created_by = u.id
    WHERE q.id = ?
");
$stmt->execute([$id]);
$quote = $stmt->fetch();

if (!$quote) {
    header('Location: list.php');
    exit;
}

//  Teklif kalemlerini getir
$stmt = $db->prepare("
    SELECT qi.*, p.name as product_name, p.image_url,
           (qi.unit_price * qi.quantity) as subtotal,
           (qi.unit_price * qi.quantity * qi.vat_rate / 100) as vat_amount,
           ((qi.unit_price * qi.quantity) + (qi.unit_price * qi.quantity * qi.vat_rate / 100)) as row_total
    FROM quote_items qi
    JOIN products p ON qi.product_id = p.id
    WHERE qi.quote_id = ?
    ORDER BY qi.id
");
$stmt->execute([$id]);
$items = $stmt->fetchAll();

// Durum güncellemesi yapıldı mı?
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    try {
        $oldStatus = $quote['status'];
        $newStatus = $_POST['status'];

        $stmt = $db->prepare("UPDATE quotes SET status = ? WHERE id = ?");
        $stmt->execute([$newStatus, $id]);

        // Bildirim oluştur (durum değiştiyse)
        if ($oldStatus !== $newStatus) {
            $notificationManager->createQuoteStatusNotification(
                $quote['quote_number'],
                $quote['company_name'],
                $oldStatus,
                $newStatus
            );
        }

        $quote['status'] = $newStatus;

        $message = 'Teklif durumu güncellendi.';
        $messageType = 'success';
    } catch (Exception $e) {
        $message = 'Hata: ' . $e->getMessage();
        $messageType = 'danger';
    }
}

// Durum açıklamaları
$statusLabels = [
    'draft' => ['text' => 'Taslak', 'class' => 'secondary'],
    'sent' => ['text' => 'Gönderildi', 'class' => 'primary'],
    'accepted' => ['text' => 'Kabul Edildi', 'class' => 'success'],
    'rejected' => ['text' => 'Reddedildi', 'class' => 'danger']
];
?>

<!-- Başarı mesajı -->
<?php if (isset($_GET['success'])): ?>
<div class="alert alert-success alert-dismissible fade show" role="alert">
    Teklif başarıyla oluşturuldu.
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<?php if (isset($message)): ?>
<div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
    <?php echo $message; ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">
            Teklif Detayı 
            <span class="badge bg-<?php echo $statusLabels[$quote['status']]['class']; ?>">
                <?php echo $statusLabels[$quote['status']]['text']; ?>
            </span>
        </h5>
        <div>
            <?php if ($quote['status'] === 'draft'): ?>
            <a href="edit.php?id=<?php echo $quote['quote_id']; ?>" class="btn btn-warning btn-sm">
                <i class="fas fa-edit"></i> Düzenle
            </a>
            <?php endif; ?>
            <a href="generate_pdf.php?id=<?php echo $quote['quote_id']; ?>" 
               class="btn btn-primary btn-sm" target="_blank">
                <i class="fas fa-file-pdf"></i> PDF İndir
            </a>
            <a href="list.php" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Listeye Dön
            </a>
        </div>
    </div>
    <div class="card-body">
        <div class="row">
            <!-- Sol Kolon: Müşteri ve Teklif Bilgileri -->
            <div class="col-md-4">
                <!-- Müşteri Bilgileri -->
                <div class="card mb-3">
                    <div class="card-body">
                        <h6 class="card-title">Müşteri Bilgileri</h6>
                        <dl class="mb-0">
                            <dt>Firma</dt>
                            <dd><?php echo htmlspecialchars($quote['company_name']); ?></dd>
                            
                            <?php if ($quote['contact_person']): ?>
                            <dt>İletişim Kişisi</dt>
                            <dd><?php echo htmlspecialchars($quote['contact_person']); ?></dd>
                            <?php endif; ?>
                            
                            <?php if ($quote['email']): ?>
                            <dt>E-posta</dt>
                            <dd><?php echo htmlspecialchars($quote['email']); ?></dd>
                            <?php endif; ?>
                            
                            <?php if ($quote['phone']): ?>
                            <dt>Telefon</dt>
                            <dd><?php echo htmlspecialchars($quote['phone']); ?></dd>
                            <?php endif; ?>
                            
                            <?php if ($quote['address']): ?>
                            <dt>Adres</dt>
                            <dd><?php echo nl2br(htmlspecialchars($quote['address'])); ?></dd>
                            <?php endif; ?>
                        </dl>
                    </div>
                </div>

                <!-- Teklif Bilgileri -->
                <div class="card mb-3">
                    <div class="card-body">
                        <h6 class="card-title">Teklif Bilgileri</h6>
                        <dl class="mb-0">
                            <dt>Teklif No</dt>
                            <dd><?php echo htmlspecialchars($quote['quote_number']); ?></dd>
                            
                            <dt>Oluşturma Tarihi</dt>
                            <dd><?php echo date('d.m.Y H:i', strtotime($quote['quote_date'])); ?></dd>
                            <dt>Teklifi Veren</dt>
                            <dd><?php echo htmlspecialchars($quote['created_by_name']); ?></dd>
                            <?php if ($quote['valid_until']): ?>
                            <dt>Geçerlilik Tarihi</dt>
                            <dd><?php echo date('d.m.Y', strtotime($quote['valid_until'])); ?></dd>
                            <?php endif; ?>
                        </dl>
                    </div>
                </div>

                <!-- Durum Güncelleme -->
                <div class="card mb-3">
                    <div class="card-body">
                        <h6 class="card-title">Teklif Durumu</h6>
                        <form method="POST" class="mb-0">
                            <div class="mb-3">
                                <select name="status" class="form-select">
                                    <?php foreach ($statusLabels as $value => $label): ?>
                                        <option value="<?php echo $value; ?>"
                                                <?php echo $quote['status'] === $value ? 'selected' : ''; ?>>
                                            <?php echo $label['text']; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <button type="submit" name="update_status" class="btn btn-primary btn-sm">
                                <i class="fas fa-save"></i> Durumu Güncelle
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Sağ Kolon: Ürünler ve Tutarlar -->
            <div class="col-md-8">
                <!-- Ürünler Tablosu -->
                <div class="table-responsive mb-3">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Ürün</th>
                                <th style="width: 100px;">Adet</th>
                                <th style="width: 150px;">Birim Fiyat</th>
                                <th style="width: 100px;">KDV</th>
                                <th style="width: 150px;">Toplam</th>
                            </tr>
                        </thead>
                        <tbody>
    <?php 
    $total_subtotal = 0;
    $total_vat = 0;
    foreach ($items as $item): 
        $total_subtotal += $item['subtotal'];
        $total_vat += $item['vat_amount'];
    ?>
    <tr>
        <td>
            <div class="d-flex align-items-center">
                <?php if ($item['image_url']): ?>
                <img src="<?php echo htmlspecialchars(getImageUrl($item['image_url'])); ?>" 
                     alt="<?php echo htmlspecialchars($item['product_name']); ?>"
                     class="me-2" style="width: 50px; height: 50px; object-fit: contain;"
                     onerror="this.style.display='none';">
                <?php endif; ?>
                <div>
                    <?php echo htmlspecialchars($item['product_name']); ?>
                </div>
            </div>
        </td>
        <td class="text-end">
            <?php echo number_format($item['quantity']); ?>
        </td>
        <td class="text-end">
            <?php echo number_format($item['unit_price'], 2, ',', '.'); ?> TL
        </td>
        <td class="text-end">
            %<?php echo number_format($item['vat_rate'], 0); ?>
        </td>
        <td class="text-end">
            <?php echo number_format($item['row_total'], 2, ',', '.'); ?> TL
        </td>
    </tr>
    <?php endforeach; ?>
</tbody>
<tfoot>
    <tr>
        <td colspan="4" class="text-end">Ara Toplam:</td>
        <td class="text-end">
            <?php echo number_format($total_subtotal, 2, ',', '.'); ?> TL
        </td>
    </tr>
    <tr>
        <td colspan="4" class="text-end">KDV Toplam:</td>
        <td class="text-end">
            <?php echo number_format($total_vat, 2, ',', '.'); ?> TL
        </td>
    </tr>
    <tr>
        <td colspan="4" class="text-end"><strong>Genel Toplam:</strong></td>
        <td class="text-end">
            <strong><?php echo number_format($total_subtotal + $total_vat, 2, ',', '.'); ?> TL</strong>
        </td>
    </tr>
</tfoot>
                    </table>
                </div>

                <!-- Notlar -->
                <?php if ($quote['notes']): ?>
                <div class="card">
                    <div class="card-body">
                        <h6 class="card-title">Teklif Notları</h6>
                        <ul class="mb-0">
                            <?php foreach (explode("\n", $quote['notes']) as $note): ?>
                                <?php if (trim($note)): ?>
                                <li><?php echo htmlspecialchars($note); ?></li>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>