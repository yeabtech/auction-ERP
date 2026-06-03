<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seller Console &mdash; AuctionERP</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --sidebar-w: 240px;
            --accent: #8b5cf6; /* Purple accent for seller */
            --accent-hover: #7c3aed;
            --bg-dark: #090915;
            --sidebar-bg: #101026;
            --card-bg: #151532;
            --card-border: rgba(139, 92, 246, 0.15);
            --text-muted: #94a3b8;
        }
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Inter', sans-serif; background: var(--bg-dark); color: #e2e8f0; min-height: 100vh; display: flex; }

        /* Sidebar */
        .sidebar {
            width: var(--sidebar-w); background: var(--sidebar-bg);
            border-right: 1px solid var(--card-border);
            display: flex; flex-direction: column;
            position: fixed; top: 0; left: 0; height: 100vh; z-index: 100;
        }
        .sidebar-logo {
            padding: 1.5rem 1.25rem; display: flex; align-items: center; gap: .75rem;
            border-bottom: 1px solid var(--card-border);
        }
        .logo-icon {
            width: 36px; height: 36px; background: linear-gradient(135deg, var(--accent), #d946ef);
            border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.1rem;
        }
        .logo-text { font-weight: 800; font-size: 1.1rem; background: linear-gradient(90deg, #a78bfa, #f472b6); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        
        .sidebar-nav { padding: 1.5rem .75rem; list-style: none; }
        .sidebar-nav li a {
            display: flex; align-items: center; gap: .75rem; padding: .65rem .85rem;
            border-radius: 10px; color: #94a3b8; text-decoration: none; font-size: .875rem; font-weight: 500;
            transition: all .2s; margin-bottom: 0.5rem;
        }
        .sidebar-nav li a:hover, .sidebar-nav li a.active {
            background: rgba(139, 92, 246, 0.15); color: #ddd6fe;
        }
        .sidebar-nav li a.active { border-left: 3px solid var(--accent); }
        .sidebar-nav li a i { font-size: 1rem; width: 20px; text-align: center; }

        /* Main */
        .main-content { margin-left: var(--sidebar-w); flex: 1; display: flex; flex-direction: column; }
        .topbar {
            background: rgba(16, 16, 38, 0.85); backdrop-filter: blur(10px);
            border-bottom: 1px solid var(--card-border);
            padding: .9rem 2rem; display: flex; align-items: center; justify-content: space-between;
            position: sticky; top: 0; z-index: 50;
        }
        .page-content { padding: 2rem; flex: 1; }
        
        .toast-container { position: fixed; bottom: 1.5rem; right: 1.5rem; z-index: 9999; }
    </style>
</head>
<body>

<!-- Sidebar -->
<aside class="sidebar">
    <div class="sidebar-logo">
        <div class="logo-icon">📦</div>
        <div>
            <div class="logo-text">AuctionERP</div>
            <div class="text-muted" style="font-size: 0.7rem; font-weight: 500;">Seller Dashboard</div>
        </div>
    </div>

    <ul class="sidebar-nav">
        <li><a href="{{ route('seller.assets') }}" class="{{ request()->routeIs('seller.assets') ? 'active' : '' }}">
            <i class="bi bi-box-seam-fill"></i> My Assets
        </a></li>
        <li><a href="{{ route('seller.auctions') }}" class="{{ request()->routeIs('seller.auctions') ? 'active' : '' }}">
            <i class="bi bi-hammer"></i> My Auctions
        </a></li>
        <li><a href="/" style="margin-top: 2rem; border: 1px dashed rgba(139, 92, 246, 0.3);">
            <i class="bi bi-arrow-left-circle"></i> Go to Bidding
        </a></li>
    </ul>

    <div class="mt-auto p-3">
        <form action="/logout" method="POST">
            @csrf
            <button type="submit" class="btn btn-outline-secondary btn-sm w-100" style="border-color: rgba(139, 92, 246, 0.3); color: #94a3b8;">
                <i class="bi bi-box-arrow-left me-1"></i> Sign Out
            </button>
        </form>
    </div>
</aside>

<!-- Main Content -->
<div class="main-content">
    <nav class="topbar">
        <span class="fw-semibold text-light">{{ $title ?? 'Seller Portal' }}</span>
        <div class="d-flex align-items-center gap-3">
            <span class="badge px-3 py-2 fw-semibold" style="background: rgba(139, 92, 246, 0.12); color: #c084fc; border: 1px solid rgba(139, 92, 246, 0.2);">
                <i class="bi bi-shop me-1"></i> Vendor Portal
            </span>
            <span style="color: var(--text-muted); font-size: 0.85rem;">{{ auth()->user()->name }}</span>
        </div>
    </nav>

    <div class="page-content">
        {{ $slot }}
    </div>
</div>

<div class="toast-container" id="toastContainer"></div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.addEventListener('livewire:initialized', () => {
        Livewire.on('notify', ({ type, message }) => {
            const el = document.createElement('div');
            el.className = `toast align-items-center text-bg-${type === 'success' ? 'success' : type === 'danger' ? 'danger' : type === 'warning' ? 'warning' : 'info'} border-0 show`;
            el.setAttribute('role', 'alert');
            el.innerHTML = `<div class="d-flex"><div class="toast-body fw-semibold">${message}</div><button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button></div>`;
            document.getElementById('toastContainer').appendChild(el);
            new bootstrap.Toast(el, { delay: 4000 }).show();
            el.addEventListener('hidden.bs.toast', () => el.remove());
        });
    });
</script>
</body>
</html>
