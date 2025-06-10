<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'CommercialiZe - Gestion Commerciale')</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom CSS CommercialiZe -->
    <style>
        :root {
            --primary-color: #E91E63;
            --secondary-color: #8BC34A;
            --accent-blue: #2196F3;
            --accent-orange: #FF9800;
            --accent-yellow: #FFEB3B;
            --accent-red: #F44336;
            --accent-teal: #009688;
            --neutral-gray: #6C757D;
            --light-gray: #F8F9FA;
            --dark-gray: #343A40;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--light-gray);
        }

        .navbar-brand {
            font-weight: bold;
            font-size: 1.5rem;
        }

        .navbar-brand .brand-c {
            color: var(--primary-color);
        }

        .navbar-brand .brand-z {
            color: var(--secondary-color);
        }

        .navbar {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--accent-blue) 100%);
            box-shadow: 0 2px 10px rgba(0,0,0,.1);
        }

        .sidebar {
            background: white;
            min-height: calc(100vh - 76px);
            box-shadow: 2px 0 10px rgba(0,0,0,.1);
            border-radius: 0 15px 15px 0;
        }

        .sidebar .nav-link {
            color: var(--dark-gray);
            padding: 12px 20px;
            margin: 5px 10px;
            border-radius: 10px;
            transition: all 0.3s ease;
        }

        .sidebar .nav-link:hover {
            background-color: var(--light-gray);
            color: var(--primary-color);
            transform: translateX(5px);
        }

        .sidebar .nav-link.active {
            background: linear-gradient(135deg, var(--primary-color), var(--accent-blue));
            color: white;
        }

        .main-content {
            padding: 30px;
        }

        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,.1);
            transition: transform 0.3s ease;
        }

        .card:hover {
            transform: translateY(-5px);
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color), var(--accent-blue));
            border: none;
            border-radius: 10px;
            padding: 10px 25px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(233, 30, 99, 0.4);
        }

        .btn-success {
            background: linear-gradient(135deg, var(--secondary-color), var(--accent-teal));
            border: none;
            border-radius: 10px;
        }

        .btn-warning {
            background: linear-gradient(135deg, var(--accent-orange), var(--accent-yellow));
            border: none;
            border-radius: 10px;
        }

        .alert {
            border: none;
            border-radius: 10px;
            border-left: 4px solid;
        }

        .alert-success {
            border-left-color: var(--secondary-color);
            background-color: rgba(139, 195, 74, 0.1);
        }

        .alert-danger {
            border-left-color: var(--accent-red);
            background-color: rgba(244, 67, 54, 0.1);
        }

        .form-control {
            border-radius: 10px;
            border: 2px solid #e9ecef;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(233, 30, 99, 0.25);
        }

        .logo-icon {
            width: 40px;
            height: 40px;
            background: white;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-right: 10px;
        }

        .version-badge {
            background: linear-gradient(135deg, var(--accent-orange), var(--accent-yellow));
            color: white;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 500;
            margin-left: 10px;
        }

        @media (max-width: 768px) {
            .sidebar {
                position: fixed;
                left: -250px;
                width: 250px;
                height: 100vh;
                z-index: 1000;
                transition: left 0.3s ease;
            }

            .sidebar.show {
                left: 0;
            }

            .main-content {
                padding: 15px;
            }
        }
    </style>
    
    @stack('styles')
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid">
            <a class="navbar-brand d-flex align-items-center" href="{{ route('dashboard') }}">
                <div class="logo-icon">
                    <i class="fas fa-shopping-cart" style="color: var(--primary-color);"></i>
                </div>
                <span class="brand-c">Commerciali</span><span class="brand-z">Ze</span>
                @auth
                    <span class="version-badge">{{ strtoupper(auth()->user()->version) }}</span>
                @endauth
            </a>

            <button class="navbar-toggler d-lg-none" type="button" data-bs-toggle="collapse" data-bs-target="#sidebarMenu">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="navbar-nav ms-auto">
                @auth
                    <div class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle text-white" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user-circle me-2"></i>{{ auth()->user()->name }}
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('profile.show') }}"><i class="fas fa-user me-2"></i>Profil</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="dropdown-item">
                                        <i class="fas fa-sign-out-alt me-2"></i>Déconnexion
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                @endauth
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            @auth
            <!-- Sidebar -->
            <div class="col-lg-3 col-xl-2 p-0">
                <div class="sidebar" id="sidebarMenu">
                    <nav class="nav flex-column pt-3">
                        <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                            <i class="fas fa-tachometer-alt me-2"></i>Tableau de bord
                        </a>
                        
                        <hr class="mx-3">
                        
                        <a class="nav-link {{ request()->routeIs('taxes.*') ? 'active' : '' }}" href="{{ route('taxes.index') }}">
                            <i class="fas fa-percentage me-2"></i>Gestion des taxes
                        </a>
                        
                        <hr class="mx-3">
                        
                        <a class="nav-link {{ request()->routeIs('quotes.*') ? 'active' : '' }}" href="{{ route('quotes.create') }}">
                            <i class="fas fa-file-alt me-2"></i>Créer un devis
                        </a>
                        
                        <a class="nav-link {{ request()->routeIs('quotes.payments') ? 'active' : '' }}" href="{{ route('quotes.payments') }}">
                            <i class="fas fa-credit-card me-2"></i>Gestion paiements
                        </a>
                        
                        <a class="nav-link {{ request()->routeIs('invoices.*') ? 'active' : '' }}" href="{{ route('invoices.create') }}">
                            <i class="fas fa-file-invoice me-2"></i>Créer une facture
                        </a>
                        
                        <a class="nav-link {{ request()->routeIs('deliveries.*') ? 'active' : '' }}" href="{{ route('deliveries.create') }}">
                            <i class="fas fa-truck me-2"></i>Bon de livraison
                        </a>
                    </nav>
                </div>
            </div>

            <!-- Main content -->
            <div class="col-lg-9 col-xl-10">
                <div class="main-content">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @yield('content')
                </div>
            </div>
            @else
            <!-- Full width for guests -->
            <div class="col-12">
                @yield('content')
            </div>
            @endauth
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Scripts personnalisés -->
    @stack('scripts')
</body>
</html>