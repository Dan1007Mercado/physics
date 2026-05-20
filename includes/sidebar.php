<aside class="sidebar">
    <a class="brand" href="dashboard.php">
        <span class="brand-icon">EG</span>
        <span>
            <strong>Egg Incubator</strong>
            <small>Monitoring System</small>
        </span>
    </a>

    <nav class="side-nav" aria-label="Main navigation">
        <a href="dashboard.php" class="<?= active_nav_class($activePage, 'dashboard') ?>">
            <span>D</span> Dashboard
        </a>
        <a href="trays.php" class="<?= active_nav_class($activePage, 'trays') ?>">
            <span>T</span> Egg Trays
        </a>
        <a href="egg_batches.php" class="<?= active_nav_class($activePage, 'batches') ?>">
            <span>B</span> Egg Batches
        </a>
        <a href="readings.php" class="<?= active_nav_class($activePage, 'readings') ?>">
            <span>R</span> Readings
        </a>
        <a href="settings.php" class="<?= active_nav_class($activePage, 'settings') ?>">
            <span>S</span> Warning Settings
        </a>
    </nav>
</aside>
