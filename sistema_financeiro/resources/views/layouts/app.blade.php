@php
    $currentUser = request()->user();
    $darkMode = $currentUser->preference?->theme === 'dark';
@endphp
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title') | Finaxis</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    @foreach (['style', 'sidebar', 'header', 'dropdown', 'dashboard', 'goals', 'settings', 'filters', 'tables', 'modal'] as $stylesheet)
        <link rel="stylesheet" href="{{ asset("css/{$stylesheet}.css") }}?v={{ filemtime(public_path("css/{$stylesheet}.css")) }}">
    @endforeach
    <link rel="stylesheet" href="{{ asset('css/integration.css') }}?v={{ filemtime(public_path('css/integration.css')) }}">
</head>
<body class="{{ $darkMode ? 'dark-mode' : '' }}" data-theme="{{ $darkMode ? 'dark' : 'light' }}">
<section class="app-layout">
    <aside class="sidebar">
        <div class="sidebar-top">
            <picture class="logo">
                <source media="(max-width: 1200px)" srcset="{{ asset('assets/images/icone_finaxis.png') }}">
                <img src="{{ asset('assets/images/logo_finaxis.png') }}" alt="Finaxis">
            </picture>
        </div>
        <nav class="sidebar-navigation">
            <p class="text-section">Principal</p>
            <a href="{{ route('dashboard') }}" class="page-btn {{ request()->routeIs('dashboard') ? 'active' : '' }}" title="Dashboard"><i class="bi bi-grid-1x2"></i><span>Dashboard</span></a>
            <a href="{{ route('accounts') }}" class="page-btn {{ request()->routeIs('accounts') ? 'active' : '' }}" title="Contas"><i class="bi bi-bank"></i><span>Contas</span></a>
            <a href="{{ route('transactions') }}" class="page-btn {{ request()->routeIs('transactions') ? 'active' : '' }}" title="Transações"><i class="bi bi-arrow-left-right"></i><span>Transações</span></a>
            <a href="{{ route('categories') }}" class="page-btn {{ request()->routeIs('categories') ? 'active' : '' }}" title="Categorias"><i class="bi bi-file-earmark-text"></i><span>Categorias</span></a>
            <a href="{{ route('goals') }}" class="page-btn {{ request()->routeIs('goals') ? 'active' : '' }}" title="Metas"><i class="bi bi-piggy-bank"></i><span>Metas</span></a>
        </nav>
        <div class="sidebar-bottom">
            <p class="text-section">Conta</p>
            <div class="account-section">
                <div class="account-info"><div class="account-profile"><i class="bi bi-person"></i></div><p class="profile-name">{{ $currentUser->name }}</p></div>
                <button type="button" class="config-btn" data-bs-toggle="modal" data-bs-target="#settingsModal" title="Configurações"><i class="bi bi-gear"></i></button>
            </div>
            <div class="exit-section"><button type="button" class="exit-btn js-logout"><i class="bi bi-box-arrow-right"></i><span>Sair</span></button></div>
        </div>
    </aside>

    <div class="app-content">
        <header class="app-header">
            <div class="header-content">
                <p class="system-title">Sistema de Gestão Financeira Pessoal</p>
                <div class="user-section">
                    <button type="button" class="notify-btn" aria-label="Notificações" disabled title="Funcionalidade futura"><i class="bi bi-bell"></i></button>
                    <div class="dropdown">
                        <button type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" class="account-btn" aria-label="Abrir menu do perfil"><i class="bi bi-person"></i></button>
                        <div class="dropdown-menu dropdown-menu-end profile-dropdown">
                            <div class="profile-dropdown-header">
                                <div class="profile-avatar"><i class="bi bi-person"></i></div>
                                <div class="profile-details"><p class="profile-title">{{ $currentUser->name }}</p><div class="profile-description"><small>Usuário Padrão</small><span class="profile-separator" aria-hidden="true">•</span><small class="user-email">{{ $currentUser->email }}</small></div></div>
                            </div>
                            <hr class="dropdown-divider">
                            <button type="button" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#settingsModal"><i class="bi bi-gear"></i><span>Configurações</span></button>
                            <button type="button" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#changePasswordModal"><i class="bi bi-key"></i><span>Alterar senha</span></button>
                            <label class="dropdown-item theme-option" for="profileDarkMode"><i class="bi bi-palette"></i><span>Tema do sistema</span><input class="theme-switch" type="checkbox" role="switch" id="profileDarkMode" {{ $darkMode ? 'checked' : '' }}></label>
                            <hr class="dropdown-divider">
                            <button type="button" class="dropdown-item js-logout"><i class="bi bi-box-arrow-right"></i><span>Sair</span></button>
                        </div>
                    </div>
                </div>
            </div>
        </header>
        <main class="app-main" id="page-content">
            <div id="appFeedback" class="alert app-feedback d-none" role="status" aria-live="polite"></div>
            @yield('content')
        </main>
    </div>
</section>

<div class="modal fade" id="changePasswordModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered"><div class="modal-content system-modal password-modal">
        <div class="modal-header"><h2 class="modal-title"><i class="bi bi-key"></i> Alteração de senha</h2><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <form id="changePasswordForm" class="system-form">
            <div class="modal-body">
                <div class="system-form-field"><label for="currentPassword">Senha atual</label><div class="password-input-wrapper"><input type="password" id="currentPassword" name="current_password" class="form-control" required><button type="button" class="password-toggle" data-target="currentPassword"><i class="bi bi-eye-slash"></i></button></div></div>
                <div class="system-form-field"><label for="newPassword">Nova senha</label><div class="password-input-wrapper"><input type="password" id="newPassword" name="password" class="form-control" minlength="8" required><button type="button" class="password-toggle" data-target="newPassword"><i class="bi bi-eye-slash"></i></button></div></div>
                <div class="system-form-field"><label for="confirmNewPassword">Confirmar nova senha</label><div class="password-input-wrapper"><input type="password" id="confirmNewPassword" name="password_confirmation" class="form-control" minlength="8" required><button type="button" class="password-toggle" data-target="confirmNewPassword"><i class="bi bi-eye-slash"></i></button></div></div>
                <p class="form-error text-danger d-none" role="alert"></p>
            </div>
            <div class="modal-footer"><button type="button" class="cancel-btn" data-bs-dismiss="modal">Cancelar</button><button type="submit" class="save-btn">Salvar alterações</button></div>
        </form>
    </div></div>
</div>

<div class="modal fade" id="settingsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable"><div class="modal-content system-modal settings-modal">
        <div class="modal-header"><h2 class="modal-title"><i class="bi bi-gear"></i> Configurações</h2><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <form id="settingsForm" class="settings-form">
            <div class="modal-body">
                <section class="settings-section">
                    <h3 class="settings-section-title">Dados do usuário</h3>
                    <div class="settings-profile-row"><div class="settings-avatar"><i class="bi bi-person"></i></div><span class="settings-profile-label">Foto de perfil</span><span class="settings-profile-hint">Alteração indisponível</span><button type="button" class="settings-icon-btn" disabled><i class="bi bi-camera"></i></button></div>
                    <div class="settings-row"><label for="settingsName">Nome do usuário</label><div class="settings-row-value"><input type="text" id="settingsName" name="name" value="{{ $currentUser->name }}" minlength="3" maxlength="100" readonly required><button type="button" class="settings-icon-btn" id="editSettingsName"><i class="bi bi-pencil"></i></button></div></div>
                    <div class="settings-row"><span>E-mail</span><span class="settings-muted">{{ $currentUser->email }}</span></div>
                    <div class="settings-row"><span>Senha</span><div class="settings-row-value"><span class="settings-muted">••••••••</span><button type="button" class="settings-icon-btn" id="openPasswordModal"><i class="bi bi-pencil"></i></button></div></div>
                </section>
                <section class="settings-section">
                    <h3 class="settings-section-title">Preferências do usuário</h3>
                    <div class="settings-row"><span>Notificações</span><div class="settings-row-value"><span class="settings-muted">Indisponível</span><div class="form-check form-switch"><input class="form-check-input" type="checkbox" disabled></div></div></div>
                    <div class="settings-row"><label for="settingsLanguage">Idioma</label><select id="settingsLanguage" class="settings-select" disabled><option>Português (Brasil)</option></select></div>
                    <div class="settings-row"><label for="settingsDarkMode">Tema escuro</label><div class="settings-row-value"><span class="settings-muted" id="darkModeStatus">{{ $darkMode ? 'Ativado' : 'Desativado' }}</span><div class="form-check form-switch"><input class="form-check-input" type="checkbox" id="settingsDarkMode" {{ $darkMode ? 'checked' : '' }}></div></div></div>
                    <div class="settings-row"><label for="settingsCurrency">Tipo de moeda</label><select id="settingsCurrency" class="settings-select" disabled><option>Real brasileiro (BRL)</option></select></div>
                </section>
                <section class="settings-section"><h3 class="settings-section-title">Gerenciamento da conta</h3><div class="settings-danger-section"><div><strong>Excluir conta</strong><p>Excluir permanentemente sua conta e seus dados financeiros.</p></div><button type="button" class="settings-delete-btn" data-bs-toggle="modal" data-bs-target="#deleteProfileModal">Excluir conta</button></div></section>
                <p class="form-error text-danger d-none" role="alert"></p>
            </div>
            <div class="modal-footer"><button type="button" class="cancel-btn" data-bs-dismiss="modal">Cancelar</button><button type="submit" class="save-btn">Salvar alterações</button></div>
        </form>
    </div></div>
</div>

<div class="modal fade" id="deleteProfileModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered"><div class="modal-content system-modal">
        <div class="modal-header"><h2 class="modal-title"><i class="bi bi-exclamation-triangle"></i> Excluir conta</h2><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <form id="deleteProfileForm" class="system-form">
            <div class="modal-body"><p>Esta ação é permanente. Digite sua senha para confirmar.</p><div class="system-form-field"><label for="deleteProfilePassword">Senha atual</label><input type="password" id="deleteProfilePassword" name="password" class="form-control" required></div><p class="form-error text-danger d-none"></p></div>
            <div class="modal-footer"><button type="button" class="cancel-btn" data-bs-dismiss="modal">Cancelar</button><button type="submit" class="settings-delete-btn">Excluir permanentemente</button></div>
        </form>
    </div></div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.6/Sortable.min.js"></script>
<script src="{{ asset('js/app.js') }}"></script>
@stack('scripts')
</body>
</html>
