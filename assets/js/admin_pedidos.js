const adminPedidosRefreshInterval = 15000; // 15 segundos
    let adminPedidosRefreshTimer = null;

    function scheduleAdminPedidosRefresh() {
        if (document.hidden) {
            return;
        }
        adminPedidosRefreshTimer = window.setTimeout(() => {
            window.location.reload();
        }, adminPedidosRefreshInterval);
    }

    document.addEventListener('visibilitychange', () => {
        if (!document.hidden) {
            if (adminPedidosRefreshTimer) {
                clearTimeout(adminPedidosRefreshTimer);
            }
            scheduleAdminPedidosRefresh();
        }
    });

    scheduleAdminPedidosRefresh();