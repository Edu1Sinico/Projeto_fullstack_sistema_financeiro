@extends('layouts.app')

@section('title', 'Contas')

@section('content')
<section class="accounts" data-filter-page="accounts">
    <div class="main-top-section"><h1 class="title-section">Contas</h1></div>
    <div class="accounts-toolbar toolbar">
        <div class="accounts-search search-bar"><i class="bi bi-search"></i><input type="search" class="js-table-search" placeholder="Pesquisar" aria-label="Pesquisar contas"></div>
        <div class="dropdown accounts-date-filter filter">
            <button type="button" class="date-filter-btn filter-btn" data-bs-toggle="dropdown" data-bs-auto-close="outside"><span>Data</span><i class="bi bi-chevron-down filter-arrow"></i></button>
            <div class="dropdown-menu date-filter-menu filter-menu-section"><h6 class="filter-menu-title">Selecionar data</h6><hr class="dropdown-divider"><input type="date" class="form-control js-date-filter"><button type="button" class="clear-filter-btn js-clear-filter">Limpar</button></div>
        </div>
        <div class="filter"><select class="form-select filter-btn js-type-filter" aria-label="Filtrar por tipo"><option value="">Tipo</option><option value="checking">Conta bancária</option><option value="savings">Poupança</option><option value="wallet">Carteira</option></select></div>
        <div class="filter"><select class="form-select filter-btn js-status-filter" aria-label="Filtrar por status"><option value="">Status</option><option value="active">Ativo</option><option value="inactive">Inativo</option></select></div>
        <button type="button" class="add-account-btn add-btn js-create-record" data-form-mode="create" data-bs-toggle="modal" data-bs-target="#accountModal"><span>+ Adicionar conta</span></button>
    </div>
    <div class="accounts-table-section table-section">
        <div class="table-responsive {{ $accounts->isEmpty() ? 'd-none' : '' }}">
            <table class="table data-table accounts-table">
                <thead><tr class="table-header"><th>Data</th><th>Nome</th><th>Tipo</th><th>Status</th><th>Saldo atual</th><th>Ações</th></tr></thead>
                <tbody>
                @foreach ($accounts as $account)
                    <tr class="table-row" data-search="{{ Str::lower($account->name) }}" data-date="{{ $account->created_at->toDateString() }}" data-type="{{ $account->type }}" data-status="{{ $account->status }}">
                        <td>{{ $account->created_at->format('d/m/Y') }}</td><td>{{ $account->name }}</td><td>{{ ['checking' => 'Conta bancária', 'savings' => 'Poupança', 'wallet' => 'Carteira'][$account->type] ?? $account->type }}</td><td>{{ $account->status === 'active' ? 'Ativo' : 'Inativo' }}</td><td>R$ {{ number_format((float) $account->balance, 2, ',', '.') }}</td>
                        <td><button type="button" class="edit-btn js-edit-account" data-form-mode="edit" data-record="{{ $account->toJson() }}" data-bs-toggle="modal" data-bs-target="#accountModal" aria-label="Editar {{ $account->name }}"><i class="bi bi-pencil"></i></button><button type="button" class="delete-btn js-delete-record" data-url="/api/accounts/{{ $account->id }}" data-name="{{ $account->name }}"><i class="bi bi-trash3"></i></button></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        <div class="empty-state js-filter-empty {{ $accounts->isEmpty() ? '' : 'd-none' }}"><i class="bi bi-inbox"></i><p>{{ $accounts->isEmpty() ? 'Não há contas cadastradas' : 'Nenhuma conta encontrada' }}</p></div>
    </div>
</section>

<div class="modal fade" id="accountModal" tabindex="-1" aria-hidden="true"><div class="modal-dialog modal-dialog-centered"><div class="modal-content account-modal system-modal">
    <div class="modal-header"><h2 class="modal-title"><i class="bi bi-bank"></i> <span class="js-modal-title">Adicionar uma conta</span></h2><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <form id="accountForm" class="system-form" data-resource="accounts"><input type="hidden" name="id">
        <div class="modal-body">
            <div class="system-form-field"><label for="accountName">Nome</label><input type="text" id="accountName" name="name" class="form-control" minlength="3" maxlength="100" required></div>
            <div class="system-form-field"><label for="accountType">Tipo</label><select id="accountType" name="type" class="form-select" required><option value="" disabled selected>Selecione um tipo</option><option value="checking">Conta bancária</option><option value="savings">Poupança</option><option value="wallet">Carteira</option></select></div>
            <div class="system-form-field"><label for="accountStatus">Status</label><select id="accountStatus" name="status" class="form-select" required><option value="active">Ativo</option><option value="inactive">Inativo</option></select></div>
            <div class="system-form-field js-create-only"><label for="accountBalance">Saldo inicial</label><input type="number" id="accountBalance" name="balance" class="form-control" min="0" step="0.01" value="0" required></div>
            <p class="form-error text-danger d-none"></p>
        </div>
        <div class="modal-footer"><button type="button" class="cancel-btn" data-bs-dismiss="modal">Cancelar</button><button type="submit" class="save-btn"><span class="js-submit-label">+ Adicionar</span></button></div>
    </form>
</div></div></div>
@endsection

@push('scripts')<script src="{{ asset('js/resources.js') }}?v={{ filemtime(public_path('js/resources.js')) }}"></script>@endpush
