@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<section class="dashboard">
    <div class="main-top-section">
        <h1 class="title-section">Dashboard</h1>
        <div class="filter-section">
            <div class="filter-wrapper dropdown">
                <button class="filter-trigger" data-bs-toggle="dropdown" data-bs-auto-close="outside"><i class="bi bi-bank"></i><span class="selected-dashboard-accounts">Contas</span><i class="bi bi-chevron-down filter-arrow"></i></button>
                <div class="dropdown-menu filter-menu"><h6 class="filter-menu-title">Contas</h6><hr class="dropdown-divider"><div class="form-check"><input class="form-check-input" type="checkbox" id="all-accounts" checked><label class="form-check-label" for="all-accounts">Todas as contas</label></div>@foreach($accounts as $account)<div class="form-check"><input class="form-check-input dashboard-account-option" type="checkbox" id="dashboard-account-{{ $account->id }}" value="{{ $account->id }}"><label class="form-check-label" for="dashboard-account-{{ $account->id }}">{{ $account->name }}</label></div>@endforeach</div>
            </div>
            <div class="filter-wrapper dropdown">
                <button class="filter-trigger" data-bs-toggle="dropdown" data-bs-auto-close="outside"><i class="bi bi-calendar-date"></i><span>Período</span><i class="bi bi-chevron-down filter-arrow"></i></button>
                <div class="dropdown-menu dropdown-menu-end filter-menu period-menu"><h6 class="filter-menu-title">Período</h6><hr class="dropdown-divider"><div class="period-fields"><div class="period-field"><label for="dashboardStartDate">Data inicial</label><input type="date" id="dashboardStartDate" class="form-control"></div><div class="period-field"><label for="dashboardEndDate">Data final</label><input type="date" id="dashboardEndDate" class="form-control"></div></div><button type="button" class="clear-filter-btn" id="clearDashboardPeriod">Limpar</button></div>
            </div>
        </div>
    </div>
    <div class="cards-section row g-4">
        <div class="col-12 col-md-6 col-xl-3"><div class="card financial-card"><div class="card-title"><i class="bi bi-wallet2"></i><span>Saldo total</span></div><div class="card-info"><span id="dashboard-total-balance">R$ 0,00</span></div><div class="card-caption"><span>Saldo atual das contas selecionadas</span></div></div></div>
        <div class="col-12 col-md-6 col-xl-3"><div class="card financial-card"><div class="card-title"><i class="bi bi-graph-up"></i><span>Total de receitas</span><span class="dot-green"></span></div><div class="card-info positive"><span id="dashboard-total-income">+ R$ 0,00</span></div><div class="card-caption"><span>Receitas do período</span></div></div></div>
        <div class="col-12 col-md-6 col-xl-3"><div class="card financial-card"><div class="card-title"><i class="bi bi-graph-down"></i><span>Total de despesas</span><span class="dot-red"></span></div><div class="card-info negative"><span id="dashboard-total-expenses">- R$ 0,00</span></div><div class="card-caption"><span>Despesas do período</span></div></div></div>
        <div class="col-12 col-md-6 col-xl-3"><div class="card financial-card"><div class="card-title"><i class="bi bi-bar-chart-fill"></i><span>Resultado do período</span><span id="dashboard-result-indicator" class="dot-green"></span></div><div class="card-info" id="dashboard-result-wrapper"><span id="dashboard-period-result">R$ 0,00</span></div><div class="card-caption"><span>Receitas menos despesas</span></div></div></div>
    </div>
    <div class="panels-section row g-4">
        <div class="col-12 col-lg-4">
            <div class="dashboard-panel">
                <div class="panel-header"><i class="bi bi-arrow-left-right"></i><span>Transações recentes</span></div>
                <div class="panel-filters">
                    <div class="dropdown transaction-filter">
                        <button type="button" class="transaction-filter-btn" data-bs-toggle="dropdown" data-bs-auto-close="outside"><span id="dashboardCategoryLabel">Categorias</span><i class="bi bi-chevron-down filter-arrow"></i></button>
                        <div class="dropdown-menu transaction-filter-menu">
                            <h6>Categorias</h6><hr class="dropdown-divider">
                            <div class="form-check"><input class="form-check-input" type="checkbox" id="dashboardAllCategories" checked><label class="form-check-label" for="dashboardAllCategories">Todas as categorias</label></div>
                            @foreach($categories as $category)
                                <div class="form-check"><input class="form-check-input dashboard-category-option" type="checkbox" id="dashboardCategory{{ $category->id }}" value="{{ $category->id }}"><label class="form-check-label" for="dashboardCategory{{ $category->id }}">{{ $category->name }}</label></div>
                            @endforeach
                        </div>
                    </div>
                    <div class="dropdown transaction-filter">
                        <button type="button" class="transaction-filter-btn" data-bs-toggle="dropdown"><span id="dashboardRecentPeriodLabel">Últimos 30 dias</span><i class="bi bi-chevron-down filter-arrow"></i></button>
                        <ul class="dropdown-menu transaction-filter-menu">
                            @foreach([7, 15, 30, 60, 90] as $days)
                                <li><button type="button" class="dropdown-item dashboard-recent-period {{ $days === 30 ? 'active' : '' }}" data-days="{{ $days }}">Últimos {{ $days }} dias</button></li>
                            @endforeach
                        </ul>
                    </div>
                </div>
                <div class="panel-content"><div class="transactions-list" id="dashboardTransactions"></div><div class="empty-state panel-empty-state d-none" id="dashboardTransactionsEmpty"><i class="bi bi-inbox"></i><p>Nenhuma transação encontrada.</p></div></div>
            </div>
        </div>
        <div class="col-12 col-lg-4">
            <div class="dashboard-panel">
                <div class="panel-header"><i class="bi bi-piggy-bank"></i><span>Resumo de metas</span></div>
                <div class="panel-filters">
                    <div class="dropdown goal-filter">
                        <button type="button" class="goal-filter-btn" data-bs-toggle="dropdown"><span id="dashboardGoalOrderLabel">Prazo mais próximo</span><i class="bi bi-chevron-down filter-arrow"></i></button>
                        <ul class="dropdown-menu goal-filter-menu"><li><button type="button" class="dropdown-item dashboard-goal-order active" data-order="asc">Prazo mais próximo</button></li><li><button type="button" class="dropdown-item dashboard-goal-order" data-order="desc">Prazo mais distante</button></li></ul>
                    </div>
                </div>
                <div class="panel-content"><div class="goals-list" id="dashboardGoals">@forelse($goals as $goal)<div class="goal-item" data-deadline="{{ $goal->deadline?->format('Y-m-d') ?? '9999-12-31' }}"><div class="goal-item-header"><div class="goal-details"><span class="goal-title">{{ $goal->title }}</span><small class="goal-deadline">{{ $goal->deadline ? 'Meta até '.$goal->deadline->translatedFormat('F \d\e Y') : 'Sem prazo definido' }}</small></div><a href="{{ route('goals') }}" class="goal-details-btn"><i class="bi bi-three-dots"></i></a></div><div class="goal-progress"><div class="goal-values"><span class="goal-current-value">R$ {{ number_format((float)$goal->current_amount, 2, ',', '.') }}</span><span class="goal-target-value">R$ {{ number_format((float)$goal->target_amount, 2, ',', '.') }}</span></div><div class="progress goal-progress-bar"><div class="progress-bar" style="width: {{ $goal->progress_percentage }}%"></div></div></div></div>@empty<div class="empty-state panel-empty-state"><i class="bi bi-inbox"></i><p>Não há metas cadastradas.</p></div>@endforelse</div></div>
            </div>
        </div>
        <div class="col-12 col-lg-4"><div class="dashboard-panel"><div class="panel-header"><i class="bi bi-bank"></i><span>Detalhes das contas</span></div><div class="panel-summary"><div class="balance-section"><span>Saldo total</span><span id="dashboard-accounts-balance">R$ 0,00</span></div></div><div class="panel-content"><div class="accounts-list" id="dashboardAccounts"></div></div></div></div>
    </div>
</section>

<script type="application/json" id="dashboardData">{!! json_encode([
    'accounts' => $accounts->map(fn($account) => ['id' => $account->id, 'name' => $account->name, 'type' => $account->type, 'balance' => (float)$account->balance]),
    'transactions' => $transactions->map(fn($transaction) => ['id' => $transaction->id, 'account_id' => $transaction->account_id, 'category_id' => $transaction->category_id, 'description' => $transaction->description, 'type' => $transaction->type, 'amount' => (float)$transaction->amount, 'date' => $transaction->transaction_date->format('Y-m-d')]),
], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
@endsection

@push('scripts')<script src="{{ asset('js/dashboard.js') }}?v={{ filemtime(public_path('js/dashboard.js')) }}"></script>@endpush
