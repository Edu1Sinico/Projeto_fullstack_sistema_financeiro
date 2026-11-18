<section class="categories" data-filter-page="categories">
    <div class="main-top-section">
        <h1 class="title-section">Categorias</h1>
    </div>
    <div class="categories-toolbar toolbar">
        <div class="categories-search search-bar"><i class="bi bi-search"></i><input type="search" class="js-table-search" placeholder="Pesquisar"></div>
        <div class="dropdown categories-date-filter filter"><button type="button" class="date-filter-btn filter-btn" data-bs-toggle="dropdown"><span>Data</span><i class="bi bi-chevron-down filter-arrow"></i></button>
            <div class="dropdown-menu date-filter-menu filter-menu-section"><input type="date" class="form-control js-date-filter"><button type="button" class="clear-filter-btn js-clear-filter">Limpar</button></div>
        </div>
        <div class="filter"><select class="form-select filter-btn js-type-filter">
                <option value="">Tipo</option>
                <option value="income">Receita</option>
                <option value="expense">Despesa</option>
            </select></div>
        <div class="filter"><select class="form-select filter-btn js-status-filter">
                <option value="">Status</option>
                <option value="active">Ativo</option>
                <option value="inactive">Inativo</option>
            </select></div><button type="button" class="add-category-btn add-btn js-create-record" data-form-mode="create" data-bs-toggle="modal" data-bs-target="#categoryModal">+ Adicionar categoria</button>
    </div>
    <div class="categories-table-section table-section">
        <div class="table-responsive <?= $categories === [] ? 'd-none' : '' ?>">
            <table class="table data-table categories-table">
                <thead>
                    <tr class="table-header">
                        <th>Data</th>
                        <th>Nome</th>
                        <th>Tipo</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody><?php foreach ($categories as $category): ?><tr class="table-row" data-search="<?= e(lower($category['name'])) ?>" data-date="<?= e(substr($category['created_at'], 0, 10)) ?>" data-type="<?= e($category['type']) ?>" data-status="<?= e($category['status']) ?>">
                            <td><?= e(date_br($category['created_at'])) ?></td>
                            <td><?= e($category['name']) ?></td>
                            <td><?= $category['type'] === 'income' ? 'Receita' : 'Despesa' ?></td>
                            <td><?= $category['status'] === 'active' ? 'Ativo' : 'Inativo' ?></td>
                            <td><button type="button" class="edit-btn js-edit-category" data-form-mode="edit" data-record="<?= json_attr($category) ?>" data-bs-toggle="modal" data-bs-target="#categoryModal"><i class="bi bi-pencil"></i></button><button type="button" class="delete-btn js-delete-record" data-url="/api/categories/<?= (int)$category['id'] ?>" data-name="<?= e($category['name']) ?>"><i class="bi bi-trash3"></i></button></td>
                        </tr><?php endforeach; ?></tbody>
            </table>
        </div>
        <div class="empty-state js-filter-empty <?= $categories === [] ? '' : 'd-none' ?>"><i class="bi bi-inbox"></i>
            <p><?= $categories === [] ? 'Não há categorias cadastradas' : 'Nenhuma categoria encontrada' ?></p>
        </div>
    </div>
</section>
<div class="modal fade" id="categoryModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content category-modal system-modal">
            <div class="modal-header">
                <h2 class="modal-title"><i class="bi bi-file-earmark-text"></i> <span class="js-modal-title">Adicionar uma categoria</span></h2><button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="categoryForm" class="system-form" data-resource="categories"><input type="hidden" name="id">
                <div class="modal-body">
                    <div class="system-form-field"><label>Nome</label><input type="text" name="name" class="form-control" minlength="3" maxlength="100" required></div>
                    <div class="system-form-field"><label>Tipo</label><select name="type" class="form-select" required>
                            <option value="" disabled selected>Selecione um tipo</option>
                            <option value="income">Receita</option>
                            <option value="expense">Despesa</option>
                        </select></div>
                    <div class="system-form-field"><label>Status</label><select name="status" class="form-select">
                            <option value="active">Ativo</option>
                            <option value="inactive">Inativo</option>
                        </select></div>
                    <p class="form-error text-danger d-none"></p>
                </div>
                <div class="modal-footer"><button type="button" class="cancel-btn" data-bs-dismiss="modal">Cancelar</button><button type="submit" class="save-btn"><span class="js-submit-label">+ Adicionar</span></button></div>
            </form>
        </div>
    </div>
</div>
