<section class="dashboard">
    <div class="main-top-section">
        <h1 class="title-section">Dashboard</h1>
        <div class="filter-section">
            <div class="filter-wrapper dropdown"><button class="filter-trigger" data-bs-toggle="dropdown" data-bs-auto-close="outside"><i class="bi bi-bank"></i><span class="selected-dashboard-accounts">Contas</span><i class="bi bi-chevron-down filter-arrow"></i></button>
                <div class="dropdown-menu filter-menu">
                    <h6 class="filter-menu-title">Contas</h6>
                    <hr class="dropdown-divider">
                    <div class="form-check"><input class="form-check-input" type="checkbox" id="all-accounts" checked><label class="form-check-label" for="all-accounts">Todas as contas</label></div><?php foreach ($accounts as $account): ?><div class="form-check"><input class="form-check-input dashboard-account-option" type="checkbox" id="dashboard-account-<?= (int) $account['id'] ?>" value="<?= (int) $account['id'] ?>"><label class="form-check-label" for="dashboard-account-<?= (int) $account['id'] ?>"><?= e($account['name']) ?></label></div><?php endforeach; ?>
                </div>
            </div>
            <div class="filter-wrapper dropdown"><button class="filter-trigger" data-bs-toggle="dropdown" data-bs-auto-close="outside"><i class="bi bi-calendar-date"></i><span>Período</span><i class="bi bi-chevron-down filter-arrow"></i></button>
                <div class="dropdown-menu dropdown-menu-end filter-menu period-menu">
                    <h6 class="filter-menu-title">Período</h6>
                    <hr class="dropdown-divider">
                    <div class="period-fields">
                        <div class="period-field"><label for="dashboardStartDate">Data inicial</label><input type="date" id="dashboardStartDate" class="form-control"></div>
                        <div class="period-field"><label for="dashboardEndDate">Data final</label><input type="date" id="dashboardEndDate" class="form-control"></div>
                    </div><button type="button" class="clear-filter-btn" id="clearDashboardPeriod">Limpar</button>
                </div>
            </div>
        </div>
    </div>
    <div class="cards-section row g-4">
        <div class="col-12 col-md-6 col-xl-3">
            <div class="card financial-card">
                <div class="card-title"><i class="bi bi-wallet2"></i><span>Saldo total</span></div>
                <div class="card-info"><span id="dashboard-total-balance">R$ 0,00</span></div>
                <div class="card-caption"><span>Saldo atual das contas selecionadas</span></div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-xl-3">
            <div class="card financial-card">
                <div class="card-title"><i class="bi bi-graph-up"></i><span>Total de receitas</span><span class="dot-green"></span></div>
                <div class="card-info positive"><span id="dashboard-total-income">+ R$ 0,00</span></div>
                <div class="card-caption"><span>Receitas do período</span></div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-xl-3">
            <div class="card financial-card">
                <div class="card-title"><i class="bi bi-graph-down"></i><span>Total de despesas</span><span class="dot-red"></span></div>
                <div class="card-info negative"><span id="dashboard-total-expenses">- R$ 0,00</span></div>
                <div class="card-caption"><span>Despesas do período</span></div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-xl-3">
            <div class="card financial-card">
                <div class="card-title"><i class="bi bi-bar-chart-fill"></i><span>Resultado do período</span><span id="dashboard-result-indicator" class="dot-green"></span></div>
                <div class="card-info" id="dashboard-result-wrapper"><span id="dashboard-period-result">R$ 0,00</span></div>
                <div class="card-caption"><span>Receitas menos despesas</span></div>
            </div>
        </div>
    </div>
    <div class="panels-section row g-4">
        <div class="col-12 col-lg-4">
            <div class="dashboard-panel">
                <div class="panel-header"><i class="bi bi-arrow-left-right"></i><span>Transações recentes</span></div>
                <div class="panel-filters">
                    <div class="dropdown transaction-filter"><button type="button" class="transaction-filter-btn" data-bs-toggle="dropdown" data-bs-auto-close="outside"><span id="dashboardCategoryLabel">Categorias</span><i class="bi bi-chevron-down filter-arrow"></i></button>
                        <div class="dropdown-menu transaction-filter-menu">
                            <h6>Categorias</h6>
                            <hr class="dropdown-divider">
                            <div class="form-check"><input class="form-check-input" type="checkbox" id="dashboardAllCategories" checked><label class="form-check-label" for="dashboardAllCategories">Todas as categorias</label></div><?php foreach ($categories as $category): ?><div class="form-check"><input class="form-check-input dashboard-category-option" type="checkbox" id="dashboardCategory<?= (int) $category['id'] ?>" value="<?= (int) $category['id'] ?>"><label class="form-check-label" for="dashboardCategory<?= (int) $category['id'] ?>"><?= e($category['name']) ?></label></div><?php endforeach; ?>
                        </div>
                    </div>
                    <div class="dropdown transaction-filter"><button type="button" class="transaction-filter-btn" data-bs-toggle="dropdown"><span id="dashboardRecentPeriodLabel">Últimos 30 dias</span><i class="bi bi-chevron-down filter-arrow"></i></button>
                        <ul class="dropdown-menu transaction-filter-menu"><?php foreach ([7, 15, 30, 60, 90] as $days): ?><li><button type="button" class="dropdown-item dashboard-recent-period <?= $days === 30 ? 'active' : '' ?>" data-days="<?= $days ?>">Últimos <?= $days ?> dias</button></li><?php endforeach; ?></ul>
                    </div>
                </div>
                <div class="panel-content">
                    <div class="transactions-list" id="dashboardTransactions"></div>
                    <div class="empty-state panel-empty-state d-none" id="dashboardTransactionsEmpty"><i class="bi bi-inbox"></i>
                        <p>Nenhuma transação encontrada.</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-lg-4">
            <div class="dashboard-panel">
                <div class="panel-header"><i class="bi bi-piggy-bank"></i><span>Resumo de metas</span></div>
                <div class="panel-filters">
                    <div class="dropdown goal-filter"><button type="button" class="goal-filter-btn" data-bs-toggle="dropdown"><span id="dashboardGoalOrderLabel">Prazo mais próximo</span><i class="bi bi-chevron-down filter-arrow"></i></button>
                        <ul class="dropdown-menu goal-filter-menu">
                            <li><button type="button" class="dropdown-item dashboard-goal-order active" data-order="asc">Prazo mais próximo</button></li>
                            <li><button type="button" class="dropdown-item dashboard-goal-order" data-order="desc">Prazo mais distante</button></li>
                        </ul>
                    </div>
                </div>
                <div class="panel-content">
                    <div class="goals-list" id="dashboardGoals">
                        <?php if ($goals === []): ?><div class="empty-state panel-empty-state"><i class="bi bi-inbox"></i>
                                <p>Não há metas cadastradas.</p>
                            </div><?php else: foreach ($goals as $goal): ?><div class="goal-item" data-deadline="<?= e($goal['deadline'] ?: '9999-12-31') ?>">
                                    <div class="goal-item-header">
                                        <div class="goal-details"><span class="goal-title"><?= e($goal['title']) ?></span><small class="goal-deadline"><?= $goal['deadline'] ? 'Meta até ' . e(date_br($goal['deadline'])) : 'Sem prazo definido' ?></small></div><a href="<?= e(url('/metas')) ?>" class="goal-details-btn"><i class="bi bi-three-dots"></i></a>
                                    </div>
                                    <div class="goal-progress">
                                        <div class="goal-values"><span class="goal-current-value">R$ <?= money($goal['current_amount']) ?></span><span class="goal-target-value">R$ <?= money($goal['target_amount']) ?></span></div>
                                        <div class="progress goal-progress-bar">
                                            <div class="progress-bar" style="width: <?= e((string) $goal['progress_percentage']) ?>%"></div>
                                        </div>
                                    </div>
                                </div><?php endforeach;
                                endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-lg-4">
            <div class="dashboard-panel">
                <div class="panel-header"><i class="bi bi-bank"></i><span>Detalhes das contas</span></div>
                <div class="panel-summary">
                    <div class="balance-section"><span>Saldo total</span><span id="dashboard-accounts-balance">R$ 0,00</span></div>
                </div>
                <div class="panel-content">
                    <div class="accounts-list" id="dashboardAccounts"></div>
                </div>
            </div>
        </div>
    </div>
</section>
<script type="application/json" id="dashboardData">
    <?= json_encode(['accounts' => array_map(static fn(array $a): array => ['id' => (int)$a['id'], 'name' => $a['name'], 'type' => $a['type'], 'balance' => (float)$a['balance']], $accounts), 'transactions' => array_map(static fn(array $t): array => ['id' => (int)$t['id'], 'account_id' => (int)$t['account_id'], 'category_id' => (int)$t['category_id'], 'description' => $t['description'], 'type' => $t['type'], 'amount' => (float)$t['amount'], 'date' => $t['transaction_date']], $transactions)], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) ?>
</script>