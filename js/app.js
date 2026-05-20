(function () {
    function byId(id) {
        return document.getElementById(id);
    }

    function setText(id, value) {
        var element = byId(id);
        if (element) {
            element.textContent = value;
        }
    }

    function formatNumber(value, suffix) {
        var number = Number(value);
        if (!Number.isFinite(number)) {
            return '--';
        }

        return number.toFixed(1) + suffix;
    }

    function formatDateTime(value) {
        if (!value) {
            return '--';
        }

        var date = new Date(String(value).replace(' ', 'T'));
        if (Number.isNaN(date.getTime())) {
            return value;
        }

        return date.toLocaleString(undefined, {
            month: 'short',
            day: 'numeric',
            year: 'numeric',
            hour: 'numeric',
            minute: '2-digit'
        });
    }

    function updateStatus(condition) {
        var status = byId('incubator-status');
        if (!status || !condition) {
            return;
        }

        status.textContent = condition.message || 'No sensor reading available';
        status.className = 'status-message status-' + (condition.level || 'secondary');
    }

    function updateAlerts(alerts) {
        var list = byId('alerts-list');
        if (!list) {
            return;
        }

        list.innerHTML = '';
        var items = Array.isArray(alerts) && alerts.length ? alerts : ['No action needed right now.'];

        items.forEach(function (message) {
            var item = document.createElement('li');
            item.textContent = message;
            list.appendChild(item);
        });
    }

    function refreshDashboard() {
        fetch('api/get_latest_reading.php', { cache: 'no-store' })
            .then(function (response) {
                return response.json();
            })
            .then(function (payload) {
                if (!payload || !payload.success) {
                    throw new Error('Invalid response');
                }

                if (!payload.reading) {
                    setText('current-temperature', '--');
                    setText('current-humidity', '--');
                    setText('latest-update', '--');
                } else {
                    setText('current-temperature', formatNumber(payload.reading.temperature, ' C'));
                    setText('current-humidity', formatNumber(payload.reading.humidity, '%'));
                    setText('latest-update', formatDateTime(payload.reading.created_at));
                }

                updateStatus(payload.condition);
                updateAlerts(payload.condition ? payload.condition.alerts : []);
            })
            .catch(function () {
                updateStatus({
                    level: 'warning',
                    message: 'No recent sensor reading'
                });
                updateAlerts(['No recent sensor reading']);
            });
    }

    document.addEventListener('DOMContentLoaded', function () {
        if (!document.querySelector('[data-dashboard="true"]')) {
            return;
        }

        refreshDashboard();
        window.setInterval(refreshDashboard, 10000);
    });
}());
