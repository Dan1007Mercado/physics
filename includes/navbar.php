<header class="topbar">
    <div>
        <p class="eyebrow mb-1">Smart Egg Incubator</p>
        <h1><?= e($pageTitle) ?></h1>
        <p class="topbar-subtitle mb-0">
            Monitor temperature, humidity, and egg hatch progress clearly.
        </p>
    </div>

    <div class="topbar-note" aria-label="Current date and time">
        <span>Today</span>
        <strong><?= date('M j, Y') ?></strong>
        <small><?= date('g:i A') ?></small>
    </div>
</header>
