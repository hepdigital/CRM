<?php
// index.php
require_once 'includes/header.php';

// İstatistikleri getir
try {
    // Son 30 gündeki teklif sayısı ve tutarı
    $stmt = $db->query("
        SELECT 
            COUNT(*) as total_quotes,
            SUM(CASE WHEN status = 'accepted' THEN 1 ELSE 0 END) as accepted_quotes,
            SUM(total_amount) as total_amount,
            AVG(CASE WHEN status = 'accepted' THEN 1 ELSE 0 END) * 100 as success_rate
        FROM quotes 
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
    ");
    $quoteStats = $stmt->fetch();

    // Toplam müşteri sayısı ve bu ay eklenen müşteri sayısı
    $stmt = $db->query("
        SELECT 
            COUNT(*) as total_customers,
            SUM(CASE WHEN created_at >= DATE_FORMAT(NOW(), '%Y-%m-01') THEN 1 ELSE 0 END) as new_customers
        FROM customers
    ");
    $customerStats = $stmt->fetch();

    // Toplam ürün sayısı ve stok değeri
    $stmt = $db->query("
        SELECT 
            COUNT(*) as total_products,
            SUM(stock_quantity) as total_stock,
            SUM(stock_quantity * price) as stock_value
        FROM products
    ");
    $productStats = $stmt->fetch();

    // Grafik verileri
    // Son 6 ayın satış verileri
    $stmt = $db->query("
        SELECT 
            DATE_FORMAT(q.created_at, '%Y-%m') as month,
            COUNT(*) as quote_count,
            SUM(
                (SELECT COALESCE(SUM((qi.unit_price * qi.quantity) + (qi.unit_price * qi.quantity * qi.vat_rate / 100)), 0)
                 FROM quote_items qi WHERE qi.quote_id = q.id)
            ) as total_amount
        FROM quotes q
        WHERE q.created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
        GROUP BY DATE_FORMAT(q.created_at, '%Y-%m')
        ORDER BY month
    ");
    $salesData = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Teklif durumları
    $stmt = $db->query("
        SELECT 
            status,
            COUNT(*) as count
        FROM quotes 
        GROUP BY status
    ");
    $quoteStatusData = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Son teklifler
    $stmt = $db->query("
    SELECT 
        q.*,
        c.company_name,
        (
            SELECT SUM(
                (qi.unit_price * qi.quantity) + 
                (qi.unit_price * qi.quantity * qi.vat_rate / 100)
            )
            FROM quote_items qi 
            WHERE qi.quote_id = q.id
        ) as calculated_total
    FROM quotes q
    JOIN customers c ON q.customer_id = c.id
    ORDER BY q.created_at DESC
    LIMIT 5
");
$recentQuotes = $stmt->fetchAll();

    // Stok durumu kritik olan ürünler (stok < 10)
    $stmt = $db->query("
        SELECT *
        FROM products
        WHERE stock_quantity < 10
        ORDER BY stock_quantity ASC
        LIMIT 5
    ");
    $lowStockProducts = $stmt->fetchAll();

} catch (Exception $e) {
    die("İstatistikler alınırken bir hata oluştu: " . $e->getMessage());
}

// Durum açıklamaları
$statusLabels = [
    'draft' => ['text' => 'Taslak', 'class' => 'secondary'],
    'sent' => ['text' => 'Gönderildi', 'class' => 'primary'],
    'accepted' => ['text' => 'Kabul Edildi', 'class' => 'success'],
    'rejected' => ['text' => 'Reddedildi', 'class' => 'danger']
];
?>

<!-- Modern Stats Cards -->
<div class="stats-grid">
    <div class="stat-card primary">
        <div class="stat-icon">
            <i class="fas fa-file-invoice"></i>
        </div>
        <div class="stat-value"><?php echo number_format($quoteStats['total_quotes']); ?></div>
        <div class="stat-label">Teklifler (Son 30 Gün)</div>
        <div class="stat-change positive">
            <i class="fas fa-arrow-up"></i>
            <span><?php echo number_format($quoteStats['success_rate'], 1); ?>% başarı oranı</span>
        </div>
    </div>
    
    <div class="stat-card success">
        <div class="stat-icon">
            <i class="fas fa-chart-line"></i>
        </div>
        <div class="stat-value"><?php echo number_format($quoteStats['total_amount'], 0, ',', '.'); ?> ₺</div>
        <div class="stat-label">Toplam Satış</div>
        <div class="stat-change positive">
            <i class="fas fa-arrow-up"></i>
            <span><?php echo number_format($quoteStats['accepted_quotes']); ?> kabul edilen</span>
        </div>
    </div>
    
    <div class="stat-card warning">
        <div class="stat-icon">
            <i class="fas fa-users"></i>
        </div>
        <div class="stat-value"><?php echo number_format($customerStats['total_customers']); ?></div>
        <div class="stat-label">Toplam Müşteri</div>
        <div class="stat-change positive">
            <i class="fas fa-arrow-up"></i>
            <span>Bu ay +<?php echo $customerStats['new_customers']; ?> yeni</span>
        </div>
    </div>
    
    <div class="stat-card danger">
        <div class="stat-icon">
            <i class="fas fa-warehouse"></i>
        </div>
        <div class="stat-value"><?php echo number_format($productStats['stock_value'], 0, ',', '.'); ?> ₺</div>
        <div class="stat-label">Stok Değeri</div>
        <div class="stat-change">
            <i class="fas fa-box"></i>
            <span><?php echo number_format($productStats['total_stock']); ?> adet ürün</span>
        </div>
    </div>
</div>

       <!-- Charts Section -->
       <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-top: 2rem;">
           <!-- Sales Chart -->
           <div class="card">
               <div class="card-header">
                   <h5 class="card-title">
                       <i class="fas fa-chart-line"></i> Satış Grafiği (Son 6 Ay)
                   </h5>
               </div>
               <div class="card-body">
                   <canvas id="salesChart" style="height: 300px;"></canvas>
               </div>
           </div>
           
           <!-- Quote Status Chart -->
           <div class="card">
               <div class="card-header">
                   <h5 class="card-title">
                       <i class="fas fa-chart-pie"></i> Teklif Durumları
                   </h5>
               </div>
               <div class="card-body">
                   <canvas id="quoteStatusChart" style="height: 300px;"></canvas>
               </div>
           </div>
       </div>

       <!-- Main Content Grid -->
       <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 2rem; margin-top: 2rem;">
    <!-- Son Teklifler -->
    <div class="table-container">
        <div class="table-header">
            <h3 class="table-title">Son Teklifler</h3>
            <a href="modules/quotes/list.php" class="btn btn-primary btn-sm">
                <i class="fas fa-external-link-alt"></i> Tümünü Gör
            </a>
        </div>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Teklif No</th>
                        <th>Müşteri</th>
                        <th>Tarih</th>
                        <th>Tutar</th>
                        <th>Durum</th>
                        <th>İşlemler</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentQuotes as $quote): ?>
                    <tr>
                        <td>
                            <strong><?php echo htmlspecialchars($quote['quote_number']); ?></strong>
                        </td>
                        <td><?php echo htmlspecialchars($quote['company_name']); ?></td>
                        <td><?php echo date('d.m.Y', strtotime($quote['created_at'])); ?></td>
                        <td>
                            <strong><?php echo number_format($quote['calculated_total'] ?? 0, 0, ',', '.'); ?> ₺</strong>
                        </td>
                        <td>
                            <span class="badge badge-<?php echo $statusLabels[$quote['status']]['class']; ?>">
                                <?php echo $statusLabels[$quote['status']]['text']; ?>
                            </span>
                        </td>
                        <td>
                            <a href="modules/quotes/view.php?id=<?php echo $quote['id']; ?>" 
                               class="btn btn-sm btn-secondary">
                                <i class="fas fa-eye"></i> Görüntüle
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($recentQuotes)): ?>
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 2rem; color: var(--text-muted);">
                            <i class="fas fa-inbox" style="font-size: 2rem; margin-bottom: 1rem; display: block;"></i>
                            Henüz teklif bulunmuyor.
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Sidebar Content -->
    <div>
        <!-- Kritik Stok Uyarıları -->
        <div class="card" style="margin-bottom: 1.5rem;">
            <div class="card-header">
                <h5 class="card-title">Kritik Stok Uyarıları</h5>
            </div>
            <div class="card-body" style="padding: 0;">
                <?php if (!empty($lowStockProducts)): ?>
                    <?php foreach ($lowStockProducts as $product): ?>
                    <div style="padding: 1rem; border-bottom: 1px solid var(--border-light); display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <h6 style="margin: 0 0 0.25rem 0; color: var(--text-primary);"><?php echo htmlspecialchars($product['name']); ?></h6>
                            <small style="color: var(--text-muted);">
                                <?php echo number_format($product['price'], 0, ',', '.'); ?> ₺
                            </small>
                        </div>
                        <div style="text-align: right;">
                            <div style="font-size: 1.25rem; font-weight: 700; color: <?php echo $product['stock_quantity'] <= 5 ? 'var(--danger-color)' : 'var(--warning-color)'; ?>;">
                                <?php echo $product['stock_quantity']; ?>
                            </div>
                            <small style="color: var(--text-muted);">stokta</small>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div style="padding: 2rem; text-align: center; color: var(--text-muted);">
                        <i class="fas fa-check-circle" style="font-size: 2rem; margin-bottom: 1rem; display: block; color: var(--success-color);"></i>
                        Kritik stok seviyesinde ürün bulunmuyor.
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Hızlı İşlemler -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">Hızlı İşlemler</h5>
            </div>
            <div class="card-body">
                <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                    <a href="modules/quotes/create.php" class="btn btn-primary">
                        <i class="fas fa-file-invoice"></i> Yeni Teklif Oluştur
                    </a>
                    <a href="modules/customers/add.php" class="btn btn-secondary">
                        <i class="fas fa-user-plus"></i> Yeni Müşteri Ekle
                    </a>
                    <a href="modules/products/add.php" class="btn btn-secondary">
                        <i class="fas fa-box-open"></i> Yeni Ürün Ekle
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Dashboard Charts
document.addEventListener('DOMContentLoaded', function() {
    // Sales Chart
    const salesCtx = document.getElementById('salesChart');
    if (salesCtx) {
        const salesData = <?php echo json_encode($salesData); ?>;
        
        // Son 6 ayın etiketlerini oluştur
        const months = [];
        const salesAmounts = [];
        const quoteCounts = [];
        
        for (let i = 5; i >= 0; i--) {
            const date = new Date();
            date.setMonth(date.getMonth() - i);
            const monthLabel = date.toLocaleDateString('tr-TR', { year: 'numeric', month: 'short' });
            months.push(monthLabel);
            
            const monthKey = date.toISOString().substr(0, 7);
            const monthData = salesData.find(d => d.month === monthKey);
            salesAmounts.push(monthData ? parseFloat(monthData.total_amount) : 0);
            quoteCounts.push(monthData ? parseInt(monthData.quote_count) : 0);
        }
        
        new Chart(salesCtx, {
            type: 'line',
            data: {
                labels: months,
                datasets: [{
                    label: 'Satış Tutarı (₺)',
                    data: salesAmounts,
                    borderColor: 'rgb(99, 102, 241)',
                    backgroundColor: 'rgba(99, 102, 241, 0.1)',
                    tension: 0.4,
                    yAxisID: 'y'
                }, {
                    label: 'Teklif Sayısı',
                    data: quoteCounts,
                    borderColor: 'rgb(16, 185, 129)',
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    tension: 0.4,
                    yAxisID: 'y1'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                    }
                },
                scales: {
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        title: {
                            display: true,
                            text: 'Satış Tutarı (₺)'
                        }
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        title: {
                            display: true,
                            text: 'Teklif Sayısı'
                        },
                        grid: {
                            drawOnChartArea: false,
                        },
                    }
                }
            }
        });
    }
    
    // Quote Status Chart
    const statusCtx = document.getElementById('quoteStatusChart');
    if (statusCtx) {
        const statusData = <?php echo json_encode($quoteStatusData); ?>;
        
        const statusLabels = {
            'draft': 'Taslak',
            'sent': 'Gönderildi',
            'accepted': 'Kabul Edildi',
            'rejected': 'Reddedildi'
        };
        
        const colors = {
            'draft': 'rgb(107, 114, 128)',
            'sent': 'rgb(59, 130, 246)',
            'accepted': 'rgb(16, 185, 129)',
            'rejected': 'rgb(239, 68, 68)'
        };
        
        const labels = statusData.map(item => statusLabels[item.status] || item.status);
        const data = statusData.map(item => parseInt(item.count));
        const backgroundColor = statusData.map(item => colors[item.status] || 'rgb(107, 114, 128)');
        
        new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{
                    data: data,
                    backgroundColor: backgroundColor,
                    borderWidth: 2,
                    borderColor: 'var(--bg-primary)'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                    }
                }
            }
        });
    }
});
</script>

<?php require_once 'includes/footer.php'; ?>