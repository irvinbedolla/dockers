function usuarios() {
    $('#menu_carga').show();
}

function roles() {
    $('#menu_carga').show();
}

function poderes() {
    $('#menu_carga').show();
}

function capacitaciones() {
    $('#menu_carga').show();
}

function mis_capacitaciones() {
    $('#menu_carga').show();
}

function expedientes() {
    $('#menu_carga').show();
}

function revista() {
    $('#menu_carga').show();
}

function estadistica() {
    $('#menu_carga').show();
}

function crear_usuario() {
    $('#menu_carga').show();
}

function editar_usuario() {
    $('#menu_carga').show();
}

function crear_rol() {
    $('#menu_carga').show();
}

function editar_rol() {
    $('#menu_carga').show();
}

function turnos() {
    $('#menu_carga').show();
}

function historial() {
    $('#menu_carga').show();
}

function mis_citas() {
    $('#menu_carga').show();
}

(function () {

    try {
    if (window.__POLL_PENDIENTE_INIT) return;
    window.__POLL_PENDIENTE_INIT = true;
        const meta = (name, fallback = null) => {
            const el = document.querySelector(`meta[name="${name}"]`);
            return el ? el.getAttribute('content') : fallback;
        };

        function computePollUrl() {
            const injected = meta('poll-pendiente-url', null);
            if (injected) {
                try {
                    const u = new URL(injected, window.location.origin);
                    const needsPublic = window.location.pathname.includes('/public');
                    const hasPublic = u.pathname.includes('/public');
                    if (u.host !== window.location.host) throw new Error('host mismatch');
                    if (needsPublic !== hasPublic) throw new Error('public segment mismatch');
                    const seg1 = (window.location.pathname.split('/')[1] || '').trim();
                    if (seg1 && seg1 !== 'public' && !u.pathname.startsWith(`/${seg1}`)) throw new Error('base segment mismatch');
                    return u.href;
                } catch (e) {
                }
            }
            
        }
        const POLL_URL = computePollUrl();

    // Intervalo (5s)
    const intervalMeta = parseInt(meta('poll-interval-ms', '5000'), 10);
    const POLL_INTERVAL_MS = isNaN(intervalMeta) ? 5000 : Math.max(1000, intervalMeta);

       
        // Polling simple para consultar pendientes periódicamente
        function pollPendienteFirma() {
            try {
                if (document.hidden) return; // no consultar cuando la pestaña no esté visible
                const url = POLL_URL + (POLL_URL.includes('?') ? '&' : '?') + '_=' + Date.now();
                fetch(url, {
                    method: 'GET',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    credentials: 'same-origin',
                    cache: 'no-store'
                })
                    .then(r => {
                        if (!r.ok) {
                            return Promise.reject(r);
                        }
                        return r.json();
                    })
                    .then(data => {
                        const count = Number((data && data.count) || 0);
                        updateMenuBadge(count);
                    })
                    .catch((err) => {
                    });
            } catch (e) { /* noop */ }
        }
        setTimeout(pollPendienteFirma, 2000);
        setInterval(pollPendienteFirma, POLL_INTERVAL_MS);
    } catch (err) {
    }
})();
