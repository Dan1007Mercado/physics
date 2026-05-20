<aside class="sidebar" aria-label="Main sidebar">
    <a class="brand" href="dashboard.php" aria-label="Go to dashboard">
        <span class="brand-icon">🥚</span>
        <span>
            <strong>Egg Incubator</strong>
            <small>Farm Monitoring System</small>
        </span>
    </a>

    <nav class="side-nav" aria-label="Main navigation">
        <a href="dashboard.php" class="<?= active_nav_class($activePage, 'dashboard') ?>">
            <span>🏠</span>
            <b>Dashboard</b>
            <small>Current status</small>
        </a>

        <a href="trays.php" class="<?= active_nav_class($activePage, 'trays') ?>">
            <span>🥚</span>
            <b>Egg Trays</b>
            <small>Tray 1 and 2</small>
        </a>

        <a href="egg_batches.php" class="<?= active_nav_class($activePage, 'batches') ?>">
            <span>📋</span>
            <b>Egg Batches</b>
            <small>Manage records</small>
        </a>

        <a href="readings.php" class="<?= active_nav_class($activePage, 'readings') ?>">
            <span>🌡️</span>
            <b>Readings</b>
            <small>Sensor history</small>
        </a>

        <a href="settings.php" class="<?= active_nav_class($activePage, 'settings') ?>">
            <span>⚙️</span>
            <b>Settings</b>
            <small>Safe ranges</small>
        </a>
    </nav>

    <div class="sidebar-help">
        <strong>Reminder</strong>
        <p>The ESP32 only sends readings. The HX-W3001 controls the bulb and fan.</p>
    </div>
</aside>
