<section class="transactions" data-filter-page="transactions">
    <div class="main-top-section">
        <h1 class="title-section">Transações</h1>
    </div>
    <div class="transactions-toolbar toolbar">
        <div class="transactions-search search-bar"><i class="bi bi-search"></i><input type="search" class="js-table-search" placeholder="Pesquisar" aria-label="Pesquisar transações"></div>
        <div class="dropdown transactions-period-filter filter"><button type="button" class="period-filter-btn filter-btn" data-bs-toggle="dropdown" data-bs-auto-close="outside"><span>Período</span><i class="bi bi-chevron-down filter-arrow"></i></button>
            <div class="dropdown-menu period-filter-menu filter-menu-section">
                <h6 class="filter-menu-title">Selecione um período</h6>
                <hr class="dropdown-divider">
                <div class="period-fields">
                    <div class="period-field"><label>Data inicial</label><input type="date" class="form-control js-start-date"></div>
                    <div class="period-field"><label>Data final</label><input type="date" class="form-control js-end-date"></div>
                </div><button type="button" class="clear-filter-btn js-clear-filter">Limpar</button>
            </div>
        </div>
        <div class="filter"><select class="form-select filter-btn js-type-filter">
                <option value="">Tipo</option>
                <option value="income">Receita</option>
                <option value="expense">Despesa</option>
            </select></div>
        <div class="filter"><select class="form-select filter-btn js-category-filter">
                <option value="">Categoria</option><?php foreach ($categories as $category): ?><option value="<?= (int) $category['id'] ?>"><?= e($category['name']) ?></option><?php endforeach; ?>
            </select></div>
        <div class="filter"><select class="form-select filter-btn js-account-filter">
                <option value="">Conta</option><?php foreach ($accounts as $account): ?><option value="<?= (int) $account['id'] ?>"><?= e($account['name']) ?></option><?php endforeach; ?>
            </select></div>
        <button type="button" class="add-transaction-btn add-btn js-create-record" data-form-mode="create" data-bs-toggle="modal" data-bs-target="#transactionModal" <?= $accounts === [] || $categories === [] ? 'disabled' : '' ?>><span>+ Adicionar transação</span></button>
    </div>
    <?php if ($accounts === [] || $categories === []): ?><div class="alert alert-warning page-notice" role="status"><i class="bi bi-info-circle"></i><span>Cadastre ao menos uma conta e uma categoria ativas para criar transações.</span></div><?php endif; ?>
    <div class="transactions-table-section table-section">
        <div class="table-responsive <?= $transactions === [] ? 'd-none' : '' ?>">
            <table class="table data-table transactions-table">
                <thead>
                    <tr class="table-header">
                        <th>Data</th>
                        <th>Descrição</th>
                        <th>Categoria</th>
                        <th>Conta</th>
                        <th>Tipo</th>
                        <th>Valor</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($transactions as $transaction): ?><tr class="table-row" data-search="<?= e(lower($transaction['description'])) ?>" data-date="<?= e($transaction['transaction_date']) ?>" data-type="<?= e($transaction['type']) ?>" data-category="<?= (int) $transaction['category_id'] ?>" data-account="<?= (int) $transaction['account_id'] ?>">
                            <td><?= e(date_br($transaction['transaction_date'])) ?></td>
                            <td><?= e($transaction['description']) ?></td>
                            <td><?= e($transaction['category_name']) ?></td>
                            <td><?= e($transaction['account_name']) ?></td>
                            <td><span class="<?= $transaction['type'] === 'income' ? 'positive' : 'negative' ?>"><?= $transaction['type'] === 'income' ? 'Receita' : 'Despesa' ?></span></td>
                            <td class="<?= $transaction['type'] === 'income' ? 'positive' : 'negative' ?>"><?= $transaction['type'] === 'income' ? '+' : '-' ?> R$ <?= money($transaction['amount']) ?></td>
                            <td><?php if ((int) $transaction['goals_count'] === 0): ?><button type="button" class="edit-btn js-edit-transaction" data-form-mode="edit" data-record="<?= json_attr($transaction) ?>" data-bs-toggle="modal" data-bs-target="#transactionModal"><i class="bi bi-pencil"></i></button><button type="button" class="delete-btn js-delete-record" data-url="/api/transactions/<?= (int) $transaction['id'] ?>" data-name="<?= e($transaction['description']) ?>"><i class="bi bi-trash3"></i></button><?php else: ?><span class="text-muted" title="Gerencie esta contribuição pela tela de metas"><i class="bi bi-piggy-bank"></i></span><?php endif; ?></td>
                        </tr><?php endforeach; ?></tbody>
            </table>
        </div>
        <div class="empty-state js-filter-empty <?= $transactions === [] ? '' : 'd-none' ?>"><i class="bi bi-inbox"></i>
            <p><?= $transactions === [] ? 'Não há transações cadastradas' : 'Nenhuma transação encontrada' ?></p>
        </div>
    </div>
</section>

<div class="modal fade" id="transactionModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content transaction-modal system-modal">
            <div class="modal-header">
                <h2 class="modal-title"><i class="bi bi-arrow-left-right"></i> <span class="js-modal-title">Adicionar transação</span></h2><button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="transactionForm" class="system-form" data-resource="transactions"><input type="hidden" name="id">
                <div class="modal-body">
                    <div class="system-form-field"><label for="transactionDate">Data</label><input type="date" id="transactionDate" name="transaction_date" class="form-control" required></div>
                    <div class="system-form-field"><label for="transactionType">Tipo</label><select id="transactionType" name="type" class="form-select" required>
                            <option value="income">Receita</option>
                            <option value="expense">Despesa</option>
                        </select></div>
                    <div class="system-form-field"><label for="transactionAccount">Conta</label><select id="transactionAccount" name="account_id" class="form-select" required>
                            <option value="" disabled selected>Selecione uma conta</option><?php foreach ($accounts as $account): ?><option value="<?= (int) $account['id'] ?>"><?= e($account['name']) ?></option><?php endforeach; ?>
                        </select></div>
                    <div class="system-form-field"><label for="transactionCategory">Categoria</label><select id="transactionCategory" name="category_id" class="form-select" required>
                            <option value="" disabled selected>Selecione uma categoria</option><?php foreach ($categories as $category): ?><option value="<?= (int) $category['id'] ?>" data-type="<?= e($category['type']) ?>"><?= e($category['name']) ?></option><?php endforeach; ?>
                        </select></div>
                    <div class="system-form-field"><label for="transactionDescription">Descrição</label><input type="text" id="transactionDescription" name="description" class="form-control" minlength="3" maxlength="255" required></div>
                    <div class="system-form-field"><label for="transactionAmount">Valor</label><input type="number" id="transactionAmount" name="amount" class="form-control" min="0.01" step="0.01" required></div>
                    <p class="form-error text-danger d-none"></p>
                </div>
                <div class="modal-footer"><button type="button" class="cancel-btn" data-bs-dismiss="modal">Cancelar</button><button type="submit" class="save-btn"><span class="js-submit-label">+ Adicionar</span></button></div>
            </form>
        </div>
    </div>
</div>