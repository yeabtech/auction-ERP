<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AuctionERP Admin &mdash; {{ $title ?? 'Dashboard' }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js" defer></script>
    <style>
        :root {
            --sidebar-w: 260px;
            --accent: #6366f1;
            --accent-dark: #4f46e5;
            --bg-dark: #0f0f1a;
            --sidebar-bg: #13132b;
            --card-bg: #1a1a35;
            --card-border: rgba(99,102,241,.18);
            --text-muted: #9ca3af;
        }
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Inter', sans-serif; background: var(--bg-dark); color: #e2e8f0; min-height: 100vh; display: flex; }

        /* Sidebar */
        .sidebar {
            width: var(--sidebar-w); background: var(--sidebar-bg);
            border-right: 1px solid var(--card-border);
            display: flex; flex-direction: column;
            position: fixed; top: 0; left: 0; height: 100vh; z-index: 100;
            overflow-y: auto;
        }
        .sidebar-logo {
            padding: 1.5rem 1.25rem; display: flex; align-items: center; gap: .75rem;
            border-bottom: 1px solid var(--card-border);
        }
        .logo-icon {
            width: 36px; height: 36px; background: linear-gradient(135deg, var(--accent), #a855f7);
            border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.1rem;
        }
        .logo-text { font-weight: 800; font-size: 1.1rem; background: linear-gradient(90deg, #818cf8, #c084fc); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .logo-sub { font-size: .7rem; color: var(--text-muted); font-weight: 500; }

        .nav-section { padding: .75rem 1rem .25rem; font-size: .65rem; text-transform: uppercase; letter-spacing: .1em; color: var(--text-muted); font-weight: 600; }
        .sidebar-nav { padding: .5rem .75rem; list-style: none; }
        .sidebar-nav li a {
            display: flex; align-items: center; gap: .75rem; padding: .6rem .85rem;
            border-radius: 10px; color: #94a3b8; text-decoration: none; font-size: .875rem; font-weight: 500;
            transition: all .2s;
        }
        .sidebar-nav li a:hover, .sidebar-nav li a.active {
            background: rgba(99,102,241,.15); color: #c7d2fe;
        }
        .sidebar-nav li a.active { border-left: 3px solid var(--accent); }
        .sidebar-nav li a i { font-size: 1rem; width: 20px; text-align: center; }
        .sidebar-badge { margin-left: auto; background: #ef4444; color: #fff; font-size: .65rem; padding: 1px 7px; border-radius: 20px; font-weight: 700; }

        /* Main */
        .main-content { margin-left: var(--sidebar-w); flex: 1; display: flex; flex-direction: column; }
        .topbar {
            background: rgba(19,19,43,.8); backdrop-filter: blur(10px);
            border-bottom: 1px solid var(--card-border);
            padding: .9rem 2rem; display: flex; align-items: center; justify-content: space-between;
            position: sticky; top: 0; z-index: 50;
        }
        .topbar-title { font-size: 1rem; font-weight: 600; color: #e2e8f0; }
        .topbar-actions { display: flex; align-items: center; gap: 1rem; }
        .avatar { width: 36px; height: 36px; background: linear-gradient(135deg, var(--accent), #a855f7); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: .85rem; cursor: pointer; }

        .page-content { padding: 2rem; flex: 1; }

        /* Cards */
        .stat-card {
            background: var(--card-bg); border: 1px solid var(--card-border);
            border-radius: 16px; padding: 1.5rem; position: relative; overflow: hidden;
            transition: transform .2s, box-shadow .2s;
        }
        .stat-card:hover { transform: translateY(-2px); box-shadow: 0 12px 32px rgba(99,102,241,.15); }
        .stat-card::before {
            content: ''; position: absolute; top: 0; right: 0;
            width: 80px; height: 80px; border-radius: 0 16px 0 100%;
            opacity: .06;
        }
        .stat-icon {
            width: 48px; height: 48px; border-radius: 12px; display: flex;
            align-items: center; justify-content: center; font-size: 1.3rem; margin-bottom: 1rem;
        }
        .stat-value { font-size: 1.8rem; font-weight: 800; line-height: 1; }
        .stat-label { font-size: .8rem; color: var(--text-muted); margin-top: .4rem; font-weight: 500; }
        .stat-change { font-size: .75rem; margin-top: .5rem; }
        .stat-change.up { color: #10b981; } .stat-change.dn { color: #ef4444; }

        .card-dark { background: var(--card-bg); border: 1px solid var(--card-border); border-radius: 16px; }
        .card-dark .card-header { background: transparent; border-bottom: 1px solid var(--card-border); padding: 1.25rem 1.5rem; font-weight: 600; font-size: .95rem; display: flex; align-items: center; justify-content: space-between; }
        .card-dark .card-body { padding: 1.25rem 1.5rem; }

        /* Table */
        .table-dark-custom { --bs-table-bg: transparent; --bs-table-color: #cbd5e1; --bs-table-border-color: rgba(99,102,241,.1); font-size: .85rem; }
        .table-dark-custom thead th { color: #64748b; font-size: .75rem; text-transform: uppercase; letter-spacing: .05em; font-weight: 600; padding: .75rem 1rem; }
        .table-dark-custom tbody td { padding: .85rem 1rem; vertical-align: middle; }

        /* Badges */
        .badge-active { background: rgba(16,185,129,.15); color: #10b981; }
        .badge-pending { background: rgba(245,158,11,.15); color: #f59e0b; }
        .badge-completed { background: rgba(99,102,241,.15); color: #818cf8; }
        .badge-cancelled { background: rgba(239,68,68,.15); color: #f87171; }

        .btn-accent { background: linear-gradient(135deg, var(--accent), #a855f7); border: none; color: #fff; font-weight: 600; border-radius: 10px; padding: .5rem 1.25rem; transition: opacity .2s; }
        .btn-accent:hover { opacity: .9; color: #fff; }

        /* Notify toast */
        .toast-container { position: fixed; bottom: 1.5rem; right: 1.5rem; z-index: 9999; }

        /* Scrollbar */
        ::-webkit-scrollbar { width: 6px; } ::-webkit-scrollbar-track { background: transparent; } ::-webkit-scrollbar-thumb { background: rgba(99,102,241,.3); border-radius: 3px; }
    </style>
</head>
<body>
<!-- Sidebar -->
<aside class="sidebar">
    <div class="sidebar-logo">
        <div class="logo-icon">🏛️</div>
        <div>
            <div class="logo-text">AuctionERP</div>
            <div class="logo-sub">Admin Portal</div>
        </div>
    </div>

    <div class="nav-section">Overview</div>
    <ul class="sidebar-nav">
        <li><a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
            <i class="bi bi-grid-1x2-fill"></i> Dashboard
        </a></li>
        <li><a href="{{ route('admin.auctions') }}"><i class="bi bi-hammer"></i> Auctions</a></li>
        <li><a href="{{ route('admin.lots') }}"><i class="bi bi-collection"></i> Lots</a></li>
    </ul>

    <div class="nav-section">Users & Compliance</div>
    <ul class="sidebar-nav">
        <li><a href="{{ route('admin.users') }}"><i class="bi bi-people-fill"></i> Users</a></li>
        <li><a href="{{ route('admin.kyc') }}">
            <i class="bi bi-shield-check"></i> KYC Review
            <span class="sidebar-badge">{{ \App\Models\KycDocument::where('status','pending')->count() }}</span>
        </a></li>
    </ul>

    <div class="nav-section">Finance</div>
    <ul class="sidebar-nav">
        <li><a href="{{ route('admin.finance') }}"><i class="bi bi-cash-stack"></i> Transactions</a></li>
        <li><a href="{{ route('admin.reports') }}"><i class="bi bi-bar-chart-fill"></i> Reports</a></li>
    </ul>

    <div class="nav-section">System</div>
    <ul class="sidebar-nav">
        <li><a href="{{ route('admin.audit') }}"><i class="bi bi-journal-text"></i> Audit Logs</a></li>
        <li><a href="{{ route('admin.settings') }}"><i class="bi bi-gear-fill"></i> Settings</a></li>
    </ul>

    <div class="mt-auto p-3">
        <form action="/logout" method="POST">
            @csrf
            <button type="submit" class="btn btn-outline-secondary btn-sm w-100" style="border-color:rgba(99,102,241,.3);color:#94a3b8;">
                <i class="bi bi-box-arrow-left me-1"></i> Sign Out
            </button>
        </form>
    </div>
</aside>

<!-- Main Content -->
<div class="main-content">
    <nav class="topbar">
        <span class="topbar-title">{{ $title ?? 'Admin Dashboard' }}</span>
        <div class="topbar-actions">
            <span style="color:var(--text-muted);font-size:.85rem;">{{ auth()->user()->name }}</span>
            <div class="avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
        </div>
    </nav>

    <div class="page-content">
        {{ $slot }}
    </div>
</div>

<!-- Toast Notifications -->
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
