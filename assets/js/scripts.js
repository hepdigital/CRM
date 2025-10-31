// assets/js/scripts.js

// Loader göster/gizle
const loader = {
    show: function() {
        document.querySelector('.loader').style.display = 'block';
    },
    hide: function() {
        document.querySelector('.loader').style.display = 'none';
    }
};

// AJAX isteği için yardımcı fonksiyon
async function makeRequest(url, method = 'GET', data = null) {
    try {
        loader.show();
        
        const options = {
            method: method,
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]')?.content
            }
        };

        if (data) {
            options.body = JSON.stringify(data);
        }

        const response = await fetch(url, options);
        const result = await response.json();

        if (!response.ok) {
            throw new Error(result.error || 'Bir hata oluştu');
        }

        return result;
    } catch (error) {
        console.error('Request error:', error);
        throw error;
    } finally {
        loader.hide();
    }
}

// Para formatı
function formatMoney(amount) {
    return new Intl.NumberFormat('tr-TR', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(amount);
}

// Form verilerini JSON'a dönüştür
function formToJSON(form) {
    const data = new FormData(form);
    const json = {};
    
    for (const [key, value] of data.entries()) {
        if (key.endsWith('[]')) {
            const k = key.slice(0, -2);
            if (!json[k]) json[k] = [];
            json[k].push(value);
        } else {
            json[key] = value;
        }
    }
    
    return json;
}

// Dosya yükleme önizleme
function previewImage(input, previewElement) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            const preview = document.querySelector(previewElement);
            preview.src = e.target.result;
            preview.style.display = 'block';
        }
        
        reader.readAsDataURL(input.files[0]);
    }
}

// Tablo sıralama
function sortTable(table, column, type = 'string') {
    const tbody = table.querySelector('tbody');
    const rows = Array.from(tbody.querySelectorAll('tr'));
    const direction = table.dataset.sortDir === 'asc' ? -1 : 1;
    
    rows.sort((a, b) => {
        let x = a.cells[column].textContent.trim();
        let y = b.cells[column].textContent.trim();
        
        if (type === 'number') {
            x = parseFloat(x.replace(/[^\d.-]/g, ''));
            y = parseFloat(y.replace(/[^\d.-]/g, ''));
        }
        
        return x > y ? direction : -direction;
    });
    
    rows.forEach(row => tbody.appendChild(row));
    table.dataset.sortDir = direction === 1 ? 'asc' : 'desc';
}

// Tablo filtreleme
function filterTable(input, table) {
    const term = input.value.toLowerCase();
    const rows = table.querySelectorAll('tbody tr');
    
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(term) ? '' : 'none';
    });
}

// Ondalık sayı kontrolü
function validateDecimal(input) {
    input.value = input.value.replace(/[^0-9.,]/g, '');
    input.value = input.value.replace(/,/g, '.');
    
    if (input.value.split('.').length > 2) {
        input.value = input.value.replace(/\.+$/, '');
    }
}

// Form doğrulama
function validateQuoteForm(form) {  // validateForm yerine validateQuoteForm olarak değiştirdik
    const inputs = form.querySelectorAll('input[required], select[required], textarea[required]');
    let isValid = true;
    
    // Form verilerini kontrol et
    console.log('Form verileri:', new FormData(form));
    
    inputs.forEach(input => {
        if (!input.value.trim()) {
            input.classList.add('is-invalid');
            isValid = false;
        } else {
            input.classList.remove('is-invalid');
        }
    });

    // Ürün verilerini kontrol et
    const products = document.querySelectorAll('#selectedProducts tbody tr:not(#noProductRow)');
    products.forEach(tr => {
        const id = tr.querySelector('input[name="products[][id]"]')?.value;
        console.log('Ürün ID:', id);
    });

    if (products.length === 0) {
        alert('Lütfen en az bir ürün ekleyin.');
        return false;
    }
    
    return isValid;
}


// Bildirimi okundu olarak işaretle
window.markAsRead = async function(notificationId) {
    try {
        const response = await fetch('/api/notifications.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'mark_read',
                notification_id: notificationId
            })
        });
        
        if (response.ok) {
            // Sayfayı yenile
            location.reload();
        }
    } catch (error) {
        console.error('Bildirim okundu işaretlenemedi:', error);
    }
};

// Tüm bildirimleri okundu olarak işaretle
window.markAllAsRead = async function() {
    try {
        const response = await fetch('/api/notifications.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'mark_all_read'
            })
        });
        
        if (response.ok) {
            // Sayfayı yenile
            location.reload();
        }
    } catch (error) {
        console.error('Tüm bildirimler okundu işaretlenemedi:', error);
    }
};

// Sayfa yüklendiğinde
document.addEventListener('DOMContentLoaded', function() {
    // Bootstrap tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Otomatik kaybolma özellikli alertler
    const alerts = document.querySelectorAll('.alert-dismissible:not(.alert-persistent)');
    alerts.forEach(alert => {
        setTimeout(() => {
            const closeButton = alert.querySelector('.btn-close');
            if (closeButton) {
                closeButton.click();
            }
        }, 5000);
    });

    // Dropdown menüleri - Değişkenleri önce tanımla
    const notificationIcon = document.getElementById('notificationIcon');
    const notificationDropdown = document.getElementById('notificationDropdown');
    const userAvatar = document.getElementById('userAvatar');
    const userDropdown = document.getElementById('userDropdown');

    // Bildirim dropdown
    if (notificationIcon && notificationDropdown) {
        console.log('Notification dropdown initialized'); // Debug için
        // Icon'a tıklayınca aç/kapat
        notificationIcon.addEventListener('click', function(e) {
            e.stopPropagation();
            console.log('Notification icon clicked'); // Debug için
            // Kullanıcı dropdown'unu kapat
            if (userDropdown) {
                userDropdown.classList.remove('show');
            }
            // Bildirim dropdown'unu toggle et
            notificationDropdown.classList.toggle('show');
            console.log('Notification dropdown classes:', notificationDropdown.className); // Debug için
        });

        // Dropdown içine tıklayınca kapanmasın
        notificationDropdown.addEventListener('click', function(e) {
            e.stopPropagation();
        });
    }

    // Kullanıcı dropdown
    if (userAvatar && userDropdown) {
        console.log('User dropdown initialized'); // Debug için
        // Avatar'a tıklayınca aç/kapat
        userAvatar.addEventListener('click', function(e) {
            e.stopPropagation();
            console.log('User avatar clicked'); // Debug için
            // Bildirim dropdown'unu kapat
            if (notificationDropdown) {
                notificationDropdown.classList.remove('show');
            }
            // Kullanıcı dropdown'unu toggle et
            userDropdown.classList.toggle('show');
            console.log('User dropdown classes:', userDropdown.className); // Debug için
        });

        // Dropdown içine tıklayınca kapanmasın
        userDropdown.addEventListener('click', function(e) {
            e.stopPropagation();
        });
    }

    // Dışarı tıklayınca her ikisini de kapat
    document.addEventListener('click', function() {
        if (notificationDropdown) {
            notificationDropdown.classList.remove('show');
        }
        if (userDropdown) {
            userDropdown.classList.remove('show');
        }
    });
});