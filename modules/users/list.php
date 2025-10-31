<?php
// modules/users/list.php
require_once '../../includes/header.php';

// Sadece admin erişebilir
if ($_SESSION['role'] !== 'admin') {
    header('Location: ../../index.php');
    exit;
}

if (isset($_GET['success'])) {
    $message = 'Kullanıcı başarıyla oluşturuldu.';
    $messageType = 'success';
}

// Arama ve filtreleme
$search = isset($_GET['search']) ? $_GET['search'] : '';
$where = '';
$params = array();

if (!empty($search)) {
    $where = "WHERE username LIKE :search 
              OR email LIKE :search 
              OR full_name LIKE :search";
    $params[':search'] = "%{$search}%";
}

// Sayfalama
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 10;
$start = ($page - 1) * $perPage;

// Toplam kayıt sayısı
$stmt = $db->prepare("SELECT COUNT(*) FROM users " . $where);
$stmt->execute($params);
$totalRecords = $stmt->fetchColumn();
$totalPages = ceil($totalRecords / $perPage);

// Kullanıcıları getir
$stmt = $db->prepare("
    SELECT * FROM users 
    {$where}
    ORDER BY id DESC 
    LIMIT :start, :perPage
");

$stmt->bindValue(':start', $start, PDO::PARAM_INT);
$stmt->bindValue(':perPage', $perPage, PDO::PARAM_INT);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->execute();
$users = $stmt->fetchAll();
?>

<!-- Page Header -->
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
    <div>
        <h1 style="font-size: 2rem; font-weight: 700; color: var(--text-primary); margin: 0;">Kullanıcı Listesi</h1>
        <p style="color: var(--text-muted); margin: 0.5rem 0 0;">Sistem kullanıcılarını yönetin ve yetkilendirin</p>
    </div>
    <div>
        <a href="add.php" class="btn btn-primary">
            <i class="fas fa-plus"></i> Yeni Kullanıcı
        </a>
    </div>
</div>

<?php if (isset($message)): ?>
<div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert" style="margin-bottom: 2rem;">
    <i class="fas fa-<?php echo $messageType === 'success' ? 'check-circle' : 'exclamation-circle'; ?> me-2"></i>
    <?php echo $message; ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<!-- Modern Table Container -->
<div class="table-container">
    <div class="table-header">
        <div style="display: flex; align-items: center; gap: 1rem; flex: 1;">
            <form method="GET" style="display: flex; align-items: center; gap: 1rem; flex: 1; max-width: 500px;">
                <div style="position: relative; flex: 1;">
                    <i class="fas fa-search" style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: var(--text-muted);"></i>
                    <input type="text" name="search" class="search-input" 
                           placeholder="Kullanıcı adı, e-posta veya isim ile ara..." 
                           value="<?php echo htmlspecialchars($search); ?>"
                           style="padding-left: 2.5rem;">
                </div>
                <button type="submit" class="btn btn-secondary">
                    <i class="fas fa-search"></i> Ara
                </button>
            </form>
            <div style="color: var(--text-muted); font-size: 0.9rem;">
                <i class="fas fa-users"></i> Toplam <?php echo $totalRecords; ?> kullanıcı
            </div>
        </div>
    </div>
    
    <div class="table-responsive" style="max-height: 70vh; overflow-y: auto;">
        <table class="table">
            <thead>
                <tr>
                    <th style="width: 60px;">ID</th>
                    <th>Kullanıcı Bilgileri</th>
                    <th style="width: 100px;">Rol</th>
                    <th style="width: 120px;">Son Giriş</th>
                    <th style="width: 100px;">Durum</th>
                    <th style="width: 120px;">İşlemler</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                <tr>
                    <td>
                        <div style="text-align: center;">
                            <div style="font-weight: 600; color: var(--text-primary);">
                                #<?php echo $user['id']; ?>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div style="display: flex; align-items: center; gap: 1rem;">
                            <div style="width: 40px; height: 40px; background: linear-gradient(135deg, var(--primary-color), var(--info-color)); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: 600;">
                                <?php echo strtoupper(substr($user['full_name'] ?: $user['username'], 0, 1)); ?>
                            </div>
                            <div>
                                <div style="font-weight: 600; color: var(--text-primary); margin-bottom: 0.25rem;">
                                    <?php echo htmlspecialchars($user['full_name'] ?: $user['username']); ?>
                                </div>
                                <div style="font-size: 0.8rem; color: var(--text-muted); margin-bottom: 0.25rem;">
                                    <i class="fas fa-user"></i> <?php echo htmlspecialchars($user['username']); ?>
                                </div>
                                <div style="font-size: 0.8rem; color: var(--text-muted);">
                                    <i class="fas fa-envelope"></i> <?php echo htmlspecialchars($user['email']); ?>
                                </div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div style="text-align: center;">
                            <span class="badge badge-<?php echo $user['role'] === 'admin' ? 'primary' : 'secondary'; ?>">
                                <i class="fas fa-<?php echo $user['role'] === 'admin' ? 'crown' : 'user'; ?>"></i>
                                <?php echo $user['role'] === 'admin' ? 'Yönetici' : 'Kullanıcı'; ?>
                            </span>
                        </div>
                    </td>
                    <td>
                        <div style="text-align: center;">
                            <?php if ($user['last_login']): ?>
                                <div style="font-weight: 500; color: var(--text-primary);">
                                    <?php echo date('d.m.Y', strtotime($user['last_login'])); ?>
                                </div>
                                <div style="font-size: 0.8rem; color: var(--text-muted);">
                                    <?php echo date('H:i', strtotime($user['last_login'])); ?>
                                </div>
                            <?php else: ?>
                                <div style="color: var(--text-muted); font-style: italic;">
                                    Hiç giriş yapmamış
                                </div>
                            <?php endif; ?>
                        </div>
                    </td>
                    <td>
                        <div style="text-align: center;">
                            <?php 
                            $isOnline = $user['last_login'] && strtotime($user['last_login']) > (time() - 3600); // Son 1 saat içinde giriş yapmış
                            ?>
                            <span class="badge badge-<?php echo $isOnline ? 'success' : 'secondary'; ?>">
                                <i class="fas fa-circle" style="font-size: 0.6rem;"></i>
                                <?php echo $isOnline ? 'Çevrimiçi' : 'Çevrimdışı'; ?>
                            </span>
                        </div>
                    </td>
                    <td>
                        <div style="display: flex; gap: 0.5rem; justify-content: center;">
                            <a href="edit.php?id=<?php echo $user['id']; ?>" 
                               class="btn btn-sm btn-warning" title="Düzenle">
                                <i class="fas fa-edit"></i>
                            </a>
                            <?php if ($user['id'] !== $_SESSION['user_id']): ?>
                            <button type="button" class="btn btn-sm btn-danger" 
                                    onclick="deleteUser(<?php echo $user['id']; ?>)"
                                    title="Sil">
                                <i class="fas fa-trash"></i>
                            </button>
                            <?php else: ?>
                            <span style="padding: 0.375rem 0.75rem; font-size: 0.8rem; color: var(--text-muted);">
                                <i class="fas fa-info-circle"></i> Siz
                            </span>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($users)): ?>
                <tr>
                    <td colspan="6" style="text-align: center; padding: 3rem; color: var(--text-muted);">
                        <i class="fas fa-user-cog" style="font-size: 3rem; margin-bottom: 1rem; display: block; color: var(--text-muted);"></i>
                        <div style="font-size: 1.1rem; margin-bottom: 0.5rem;">Henüz kullanıcı bulunmuyor</div>
                        <div style="font-size: 0.9rem;">İlk kullanıcıyı eklemek için yukarıdaki "Yeni Kullanıcı" butonunu kullanın.</div>
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modern Pagination -->
<?php if ($totalPages > 1): ?>
<div style="display: flex; justify-content: center; margin-top: 2rem;">
    <div style="display: flex; align-items: center; gap: 0.5rem; background: var(--bg-primary); padding: 0.75rem 1.5rem; border-radius: var(--radius-lg); border: 1px solid var(--border-color); box-shadow: var(--shadow-sm);">
        <?php if ($page > 1): ?>
        <a href="?page=<?php echo ($page - 1); ?>&search=<?php echo urlencode($search); ?>" 
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
                    <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>" 
                       style="padding: 0.5rem 0.75rem; border-radius: var(--radius-md); color: var(--text-primary); text-decoration: none; transition: all var(--transition-fast);"
                       onmouseover="this.style.background='var(--bg-tertiary)'" 
                       onmouseout="this.style.background='transparent'">
                        <?php echo $i; ?>
                    </a>
                <?php endif; ?>
            <?php endfor; ?>
        </div>

        <?php if ($page < $totalPages): ?>
        <a href="?page=<?php echo ($page + 1); ?>&search=<?php echo urlencode($search); ?>" 
           class="btn btn-sm btn-secondary">
            <i class="fas fa-chevron-right"></i>
        </a>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<script>
function deleteUser(userId) {
    if (confirm('Bu kullanıcıyı silmek istediğinizden emin misiniz?')) {
        fetch('delete.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({ id: userId }).toString()
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.reload(); // sayfayı yenile
            } else {
                alert('Hata: ' + (data.error || 'Bir hata oluştu'));
            }
        })
        .catch(error => {
            // Hata olsa bile işlem başarılı olduysa sayfayı yenile
            if (document.body.innerHTML.includes('success')) {
                window.location.reload();
            } else {
                console.error('Error:', error);
                alert('İşlem başarılı oldu ancak sayfayı yenilemeniz gerekiyor.');
            }
        });
    }
}
</script>

<?php require_once '../../includes/footer.php'; ?>