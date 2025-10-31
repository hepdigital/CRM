<?php
// modules/notifications/list.php
require_once '../../includes/header.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: /login.php');
    exit;
}

$userId = $_SESSION['user_id'];

// Filters
$filterType = $_GET['type'] ?? 'all';
$filterRead = $_GET['read'] ?? 'all';
$search = trim($_GET['search'] ?? '');
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 15;
$offset = ($page - 1) * $perPage;

$where = ['user_id = :user_id'];
$params = [':user_id' => $userId];

if ($filterType !== 'all') {
    $where[] = 'type = :type';
    $params[':type'] = $filterType;
}
if ($filterRead !== 'all') {
    $where[] = 'is_read = :is_read';
    $params[':is_read'] = $filterRead === 'unread' ? 0 : 1;
}
if ($search !== '') {
    $where[] = '(title LIKE :q OR message LIKE :q)';
    $params[':q'] = '%' . $search . '%';
}

$whereSql = 'WHERE ' . implode(' AND ', $where);

// Count total
$stmt = $db->prepare("SELECT COUNT(*) as cnt FROM notifications $whereSql");
$stmt->execute($params);
$totalRecords = (int)$stmt->fetchColumn();
$totalPages = max(1, (int)ceil($totalRecords / $perPage));

// Fetch notifications
$stmt = $db->prepare("SELECT * FROM notifications $whereSql ORDER BY created_at DESC LIMIT :offset, :per_page");
foreach ($params as $k => $v) {
    $stmt->bindValue($k, $v);
}
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->bindValue(':per_page', $perPage, PDO::PARAM_INT);
$stmt->execute();
$notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

$typeOptions = [
    'all' => 'Tümü',
    'info' => 'Bilgi',
    'success' => 'Başarılı',
    'warning' => 'Uyarı',
    'error' => 'Hata',
    'system' => 'Sistem'
];

$readOptions = [
    'all' => 'Tümü',
    'unread' => 'Okunmamış',
    'read' => 'Okunmuş'
];
?>

<!-- Page Header -->
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
    <div>
        <h1 style="font-size: 2rem; font-weight: 700; color: var(--text-primary); margin: 0;">Bildirimler</h1>
        <p style="color: var(--text-muted); margin: 0.5rem 0 0;">Sistemde size gönderilen bildirimleri görüntüleyin</p>
    </div>
    <div>
        <button class="btn btn-secondary" onclick="notificationManager.markAllAsRead()">
            <i class="fas fa-check-double"></i> Tümünü Okundu İşaretle
        </button>
    </div>
</div>

<!-- Filters -->
<div class="card" style="margin-bottom: 1.5rem;">
    <div class="card-body">
        <form method="GET" style="display: grid; grid-template-columns: 2fr 1fr 1fr auto; gap: 1rem; align-items: end;">
            <div>
                <label style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: var(--text-primary);">Ara</label>
                <input type="text" name="search" class="form-control" placeholder="Başlık veya içerikte ara" value="<?php echo htmlspecialchars($search); ?>">
            </div>
            <div>
                <label style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: var(--text-primary);">Tür</label>
                <select name="type" class="form-control">
                    <?php foreach ($typeOptions as $value => $label): ?>
                        <option value="<?php echo $value; ?>" <?php echo $filterType === $value ? 'selected' : ''; ?>><?php echo $label; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: var(--text-primary);">Durum</label>
                <select name="read" class="form-control">
                    <?php foreach ($readOptions as $value => $label): ?>
                        <option value="<?php echo $value; ?>" <?php echo $filterRead === $value ? 'selected' : ''; ?>><?php echo $label; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i> Filtrele
                </button>
                <a href="list.php" class="btn btn-secondary">
                    <i class="fas fa-sync"></i> Sıfırla
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Notifications Table -->
<div class="table-container">
    <div class="table-header">
        <div style="display: flex; align-items: center; gap: 1rem; flex: 1;">
            <div style="color: var(--text-muted); font-size: 0.9rem;">
                <i class="fas fa-bell"></i> Toplam <?php echo $totalRecords; ?> bildirim
            </div>
        </div>
    </div>

    <div class="table-responsive" style="max-height: 70vh; overflow-y: auto;">
        <table class="table">
            <thead>
                <tr>
                    <th style="width: 100px;">Tarih</th>
                    <th style="width: 100px;">Tür</th>
                    <th>Başlık & Mesaj</th>
                    <th style="width: 120px;">Durum</th>
                    <th style="width: 140px;">İşlemler</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($notifications as $n): ?>
                <tr>
                    <td>
                        <div style="font-weight: 500; color: var(--text-primary);">
                            <?php echo date('d.m.Y', strtotime($n['created_at'])); ?>
                        </div>
                        <div style="font-size: 0.8rem; color: var(--text-muted);">
                            <?php echo date('H:i', strtotime($n['created_at'])); ?>
                        </div>
                    </td>
                    <td>
                        <span class="badge badge-<?php 
                            echo $n['type'] === 'success' ? 'success' : ($n['type'] === 'error' ? 'danger' : ($n['type'] === 'warning' ? 'warning' : 'info')); 
                        ?>">
                            <?php echo ucfirst($n['type']); ?>
                        </span>
                    </td>
                    <td>
                        <div style="font-weight: 600; color: var(--text-primary); margin-bottom: 0.25rem;">
                            <?php echo htmlspecialchars($n['title']); ?>
                        </div>
                        <div style="color: var(--text-secondary);">
                            <?php echo htmlspecialchars($n['message']); ?>
                        </div>
                        <?php if (!empty($n['action_url'])): ?>
                        <div style="margin-top: 0.25rem;">
                            <a href="<?php echo htmlspecialchars($n['action_url']); ?>" class="btn btn-sm btn-info" target="_blank">
                                <i class="fas fa-external-link-alt"></i> İlgili sayfaya git
                            </a>
                        </div>
                        <?php endif; ?>
                    </td>
                    <td>
                        <span class="badge badge-<?php echo $n['is_read'] ? 'secondary' : 'primary'; ?>">
                            <?php echo $n['is_read'] ? 'Okunmuş' : 'Okunmamış'; ?>
                        </span>
                    </td>
                    <td>
                        <div style="display: flex; gap: 0.5rem; justify-content: center;">
                            <?php if (!$n['is_read']): ?>
                            <button class="btn btn-sm btn-success" title="Okundu İşaretle" onclick="markRead(<?php echo $n['id']; ?>)">
                                <i class="fas fa-check"></i>
                            </button>
                            <?php endif; ?>
                            <button class="btn btn-sm btn-danger" title="Sil" onclick="deleteNotification(<?php echo $n['id']; ?>)">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($notifications)): ?>
                <tr>
                    <td colspan="5" style="text-align: center; padding: 3rem; color: var(--text-muted);">
                        <i class="fas fa-bell-slash" style="font-size: 3rem; margin-bottom: 1rem; display: block; color: var(--text-muted);"></i>
                        <div style="font-size: 1.1rem; margin-bottom: 0.5rem;">Henüz bildirim yok</div>
                        <div style="font-size: 0.9rem;">Yeni bildirimler geldiğinde burada görünecek.</div>
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Pagination -->
<?php if ($totalPages > 1): ?>
<div style="display: flex; justify-content: center; margin-top: 2rem;">
    <div style="display: flex; align-items: center; gap: 0.5rem; background: var(--bg-primary); padding: 0.75rem 1.5rem; border-radius: var(--radius-lg); border: 1px solid var(--border-color); box-shadow: var(--shadow-sm);">
        <?php if ($page > 1): ?>
        <a href="?page=<?php echo ($page - 1); ?>&type=<?php echo urlencode($filterType); ?>&read=<?php echo urlencode($filterRead); ?>&search=<?php echo urlencode($search); ?>" 
           class="btn btn-sm btn-secondary">
            <i class="fas fa-chevron-left"></i>
        </a>
        <?php endif; ?>

        <div style="display: flex; gap: 0.25rem; margin: 0 1rem;">
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <?php if ($i == $page): ?>
                    <span style="background: var(--primary-color); color: white; padding: 0.5rem 0.75rem; border-radius: var(--radius-md); font-weight: 600;">
                        <?php echo $i; ?>
                    </span>
                <?php else: ?>
                    <a href="?page=<?php echo $i; ?>&type=<?php echo urlencode($filterType); ?>&read=<?php echo urlencode($filterRead); ?>&search=<?php echo urlencode($search); ?>" 
                       style="padding: 0.5rem 0.75rem; border-radius: var(--radius-md); color: var(--text-primary); text-decoration: none; transition: all var(--transition-fast);"
                       onmouseover="this.style.background='var(--bg-tertiary)'" 
                       onmouseout="this.style.background='transparent'">
                        <?php echo $i; ?>
                    </a>
                <?php endif; ?>
            <?php endfor; ?>
        </div>

        <?php if ($page < $totalPages): ?>
        <a href="?page=<?php echo ($page + 1); ?>&type=<?php echo urlencode($filterType); ?>&read=<?php echo urlencode($filterRead); ?>&search=<?php echo urlencode($search); ?>" 
           class="btn btn-sm btn-secondary">
            <i class="fas fa-chevron-right"></i>
        </a>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<script>
async function markRead(id) {
    try {
        const res = await fetch('/api/notifications.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'mark_read', notification_id: id })
        });
        const data = await res.json();
        if (data.success) {
            location.reload();
        } else {
            alert('İşlem başarısız.');
        }
    } catch (e) {
        alert('Sunucu hatası.');
    }
}

async function deleteNotification(id) {
    if (!confirm('Bu bildirimi silmek istediğinizden emin misiniz?')) return;
    try {
        const res = await fetch('/api/notifications.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'delete', notification_id: id })
        });
        const data = await res.json();
        if (data.success) {
            location.reload();
        } else {
            alert('Silme işlemi başarısız.');
        }
    } catch (e) {
        alert('Sunucu hatası.');
    }
}
</script>

<?php require_once '../../includes/footer.php'; ?>


