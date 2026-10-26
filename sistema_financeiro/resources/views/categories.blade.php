@extends('layouts.app')

@section('title', 'Categorias')

@section('content')
<section class="categories" data-filter-page="categories">
    <div class="main-top-section"><h1 class="title-section">Categorias</h1></div>
    <div class="categories-toolbar toolbar">
        <div class="categories-search search-bar"><i class="bi bi-search"></i><input type="search" class="js-table-search" placeholder="Pesquisar" aria-label="Pesquisar categorias"></div>
        <div class="dropdown categories-date-filter filter"><button type="button" class="date-filter-btn filter-btn" data-bs-toggle="dropdown" data-bs-auto-close="outside"><span>Data</span><i class="bi bi-chevron-down filter-arrow"></i></button><div class="dropdown-menu date-filter-menu filter-menu-section"><h6 class="filter-menu-title">Selecionar data</h6><hr class="dropdown-divider"><input type="date" class="form-control js-date-filter"><button type="button" class="clear-filter-btn js-clear-filter">Limpar</button></div></div>
        <div class="filter"><select class="form-select filter-btn js-type-filter"><option value="">Tipo</option><option value="income">Receita</option><option value="expense">Despesa</option></select></div>
        <div class="filter"><select class="form-select filter-btn js-status-filter"><option value="">Status</option><option value="active">Ativo</option><option value="inactive">Inativo</option></select></div>
        <button type="button" class="add-category-btn add-btn js-create-record" data-form-mode="create" data-bs-toggle="modal" data-bs-target="#categoryModal"><span>+ Adicionar categoria</span></button>
    </div>
    <div class="categories-table-section table-section">
        <div class="table-responsive {{ $categories->isEmpty() ? 'd-none' : '' }}">
            <table class="table data-table categories-table"><thead><tr class="table-header"><th>Data</th><th>Nome</th><th>Tipo</th><th>Status</th><th>Ações</th></tr></thead><tbody>
            @foreach ($categories as $category)
                <tr class="table-row" data-search="{{ Str::lower($category->name) }}" data-date="{{ $category->created_at->toDateString() }}" data-type="{{ $category->type }}" data-status="{{ $category->status }}">
                    <td>{{ $category->created_at->format('d/m/Y') }}</td><td>{{ $category->name }}</td><td>{{ $category->type === 'income' ? 'Receita' : 'Despesa' }}</td><td>{{ $category->status === 'active' ? 'Ativo' : 'Inativo' }}</td>
                    <td><button type="button" class="edit-btn js-edit-category" data-form-mode="edit" data-record="{{ $category->toJson() }}" data-bs-toggle="modal" data-bs-target="#categoryModal"><i class="bi bi-pencil"></i></button><button type="button" class="delete-btn js-delete-record" data-url="/api/categories/{{ $category->id }}" data-name="{{ $category->name }}"><i class="bi bi-trash3"></i></button></td>
                </tr>
            @endforeach
            </tbody></table>
        </div>
        <div class="empty-state js-filter-empty {{ $categories->isEmpty() ? '' : 'd-none' }}"><i class="bi bi-inbox"></i><p>{{ $categories->isEmpty() ? 'Não há categorias cadastradas' : 'Nenhuma categoria encontrada' }}</p></div>
    </div>
</section>

<div class="modal fade" id="categoryModal" tabindex="-1" aria-hidden="true"><div class="modal-dialog modal-dialog-centered"><div class="modal-content category-modal system-modal">
    <div class="modal-header"><h2 class="modal-title"><i class="bi bi-file-earmark-text"></i> <span class="js-modal-title">Adicionar uma categoria</span></h2><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <form id="categoryForm" class="system-form" data-resource="categories"><input type="hidden" name="id"><div class="modal-body">
        <div class="system-form-field"><label for="categoryName">Nome</label><input type="text" id="categoryName" name="name" class="form-control" minlength="3" maxlength="100" required></div>
        <div class="system-form-field"><label for="categoryType">Tipo</label><select id="categoryType" name="type" class="form-select" required><option value="" disabled selected>Selecione um tipo</option><option value="income">Receita</option><option value="expense">Despesa</option></select></div>
        <div class="system-form-field"><label for="categoryStatus">Status</label><select id="categoryStatus" name="status" class="form-select" required><option value="active">Ativo</option><option value="inactive">Inativo</option></select></div>
        <p class="form-error text-danger d-none"></p>
    </div><div class="modal-footer"><button type="button" class="cancel-btn" data-bs-dismiss="modal">Cancelar</button><button type="submit" class="save-btn"><span class="js-submit-label">+ Adicionar</span></button></div></form>
</div></div></div>
@endsection

@push('scripts')<script src="{{ asset('js/resources.js') }}?v={{ filemtime(public_path('js/resources.js')) }}"></script>@endpush
