<!-- Navigation Header -->
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container">
        <a class="navbar-brand" href="<?= str_contains($_SERVER['PHP_SELF'], '/admin/') ? '../index.php' : 'index.php' ?>">💧 Ofis Su Takip</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <?php $isAdmin = str_contains($_SERVER['PHP_SELF'], '/admin/'); ?>
                <li class="nav-item">
                    <a class="nav-link" href="<?= $isAdmin ? '../index.php' : 'index.php' ?>">Ana Sayfa</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= $isAdmin ? '../add_record.php' : 'add_record.php' ?>">Kayıt Ekle</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= $isAdmin ? '../view_records.php' : 'view_records.php' ?>">Kayıtları Görüntüle</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= $isAdmin ? '../reports.php' : 'reports.php' ?>">Raporlar</a>
                </li>
                <?php if (isAdmin()): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="adminDropdown" role="button" data-bs-toggle="dropdown">
                            Yönetim
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="<?= $isAdmin ? '' : 'admin/' ?>manage_users.php">Kullanıcılar</a></li>
                            <li><a class="dropdown-item" href="<?= $isAdmin ? '' : 'admin/' ?>manage_brands.php">Su Markaları</a></li>
                            <li><a class="dropdown-item" href="<?= $isAdmin ? '' : 'admin/' ?>manage_payment_methods.php">Ödeme Yöntemleri</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="<?= $isAdmin ? '' : 'admin/' ?>logs.php">Sistem Logları</a></li>
                        </ul>
                    </li>
                <?php endif; ?>
            </ul>
            <ul class="navbar-nav">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                        <?= e($_SESSION['user_name']) ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item disabled">📱 <?= e($_SESSION['user_phone'] ?? 'Bilinmiyor') ?></a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="<?= $isAdmin ? '../logout.php' : 'logout.php' ?>">Çıkış Yap</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>

<!-- Maskot CSS'i Ekle -->
<link rel="stylesheet" href="<?= $isAdmin ? '../css/mascot-helper.css' : 'css/mascot-helper.css' ?>">

