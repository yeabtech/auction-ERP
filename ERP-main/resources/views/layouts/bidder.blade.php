<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AuctionERP &mdash; Live Online Auction</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --accent: #6366f1;
            --accent-hover: #4f46e5;
            --bg: #0a0a14;
            --card: #111126;
            --border: rgba(99,102,241,.15);
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
            background: linear-gradient(90deg, #818cf8, #c084fc);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .navbar-dark-custom {
            background: rgba(10, 10, 20, 0.8);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid var(--border);
        }
        .btn-accent {
            background: linear-gradient(135deg, var(--accent), #a855f7);
            border: none;
            color: #fff;
            font-weight: 600;
            border-radius: 10px;
            transition: transform .2s, opacity .2s;
        }
        .btn-accent:hover {
            opacity: .95;
            color: #fff;
            transform: translateY(-1px);
        }
        /* Toast style */
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
    <div class="container">
        <a class="navbar-brand" href="/">🏛️ AuctionERP</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMain">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navMain">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-3">
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}" href="/">Browse Auctions</a>
                </li>
            </ul>
            <div class="d-flex align-items-center gap-3">
                @auth
                    <!-- Wallet Balance -->
                    <span class="badge px-3 py-2 fw-semibold" style="background: rgba(16, 185, 129, 0.12); color: #10b981; border: 1px solid rgba(16, 185, 129, 0.2);">
                        <i class="bi bi-wallet2 me-1"></i> Balance: ${{ number_format(auth()->user()->walletBalance(), 2) }}
                    </span>
                    <a href="{{ route('bidder.dashboard') }}" class="btn btn-sm text-decoration-none" style="background: rgba(99, 102, 241, 0.15); color: #818cf8; border-radius: 8px;">
                        <i class="bi bi-grid me-1"></i> Dashboard
                    </a>
                    
                    <form action="/logout" method="POST" class="m-0">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-outline-danger" style="border-radius: 8px;">
                            <i class="bi bi-box-arrow-left"></i>
                        </button>
                    </form>
                @else
                    <a href="/login" class="btn btn-sm text-decoration-none" style="color: var(--text-muted);">Login</a>
                    <a href="/register" class="btn btn-sm btn-accent px-3 py-1.5">Register</a>
                @endauth
            </div>
        </div>
    </div>
</nav>

<main>
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
