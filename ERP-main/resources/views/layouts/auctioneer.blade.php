<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Auctioneer Control Panel &mdash; AuctionERP</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --bg: #0b0b18;
            --card: #12122c;
            --accent: #d97706; /* Amber style for auctioneer */
            --accent-hover: #b45309;
            --border: rgba(217, 119, 6, 0.15);
            --text-muted: #94a3b8;
        }
        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg);
            color: #e2e8f0;
            min-height: 100vh;
        }
        .navbar-brand {
            font-weight: 800;
            font-size: 1.25rem;
            background: linear-gradient(90deg, #fbbf24, #f59e0b);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .navbar-dark-custom {
            background: rgba(18, 18, 44, 0.8);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid var(--border);
        }
        .toast-container {
            position: fixed;
            bottom: 1.5rem;
            right: 1.5rem;
            z-index: 9999;
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark navbar-dark-custom sticky-top">
    <div class="container-fluid px-4">
        <a class="navbar-brand" href="#">🔨 Auctioneer Control Desk</a>
        <div class="d-flex align-items-center gap-3 ms-auto">
            <span class="badge px-3 py-2 fw-semibold" style="background: rgba(217, 119, 6, 0.12); color: #fbbf24; border: 1px solid rgba(217, 119, 6, 0.2);">
                <i class="bi bi-person-fill-lock me-1"></i> Auctioneer Mode
            </span>
            <span style="color: var(--text-muted); font-size: 0.85rem;">{{ auth()->user()->name }}</span>
            <form action="/logout" method="POST" class="m-0">
                @csrf
                <button type="submit" class="btn btn-sm btn-outline-danger" style="border-radius: 8px;">
                    <i class="bi bi-box-arrow-left me-1"></i> Sign Out
                </button>
            </form>
        </div>
    </div>
</nav>

<main class="py-4">
    {{ $slot }}
</main>

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
