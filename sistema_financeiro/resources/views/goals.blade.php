@extends('layouts.app')

@section('title', 'Metas')

@section('content')
<section class="goals" data-filter-page="goals">
    <div class="main-top-section"><h1 class="title-section">Metas</h1></div>
    <div class="goals-toolbar">
        <div class="goals-search search-bar"><i class="bi bi-search"></i><input type="search" class="js-goal-search" placeholder="Pesquisar" aria-label="Pesquisar metas"></div>
        <div class="filter"><select class="form-select filter-btn js-goal-status"><option value="">Status</option><option value="not-started">Não iniciado</option><option value="in-progress">Em andamento</option><option value="completed">Concluído</option></select></div>
        <button type="button" class="add-goal-btn add-btn js-create-record" data-form-mode="create" data-bs-toggle="modal" data-bs-target="#goalModal"><span>+ Adicionar meta</span></button>
    </div>
    <div class="goals-grid {{ $goals->isEmpty() ? 'd-none' : '' }}" id="sortableGoals">
        @foreach($goals as $goal)
            @php
                $status = $goal->completed ? 'completed' : ((float)$goal->current_amount > 0 ? 'in-progress' : 'not-started');
            @endphp
            <article class="goal-card" data-goal-id="{{ $goal->id }}" data-search="{{ Str::lower($goal->title) }}" data-status="{{ $status }}">
                <div class="goal-card-header"><h3 class="goal-card-title">{{ $goal->title }}</h3><button type="button" class="goal-drag-handle" aria-label="Reorganizar meta"><i class="bi bi-grid-3x3-gap"></i></button></div>
                <div class="goal-card-info">
                    <div class="goal-info-row"><span class="goal-info-label">Objetivo</span><span class="goal-info-value">R$ {{ number_format((float)$goal->target_amount, 2, ',', '.') }}</span></div>
                    <div class="goal-info-row"><span class="goal-info-label">Valor acumulado</span><span class="goal-info-value goal-current-value">R$ {{ number_format((float)$goal->current_amount, 2, ',', '.') }}</span></div>
                    <div class="goal-info-row"><span class="goal-info-label">Prazo</span><span class="goal-info-value">{{ $goal->deadline?->format('d/m/Y') ?? 'Sem prazo' }}</span></div>
                </div>
                <div class="goal-progress-section"><div class="goal-progress-bar" role="progressbar" aria-valuenow="{{ $goal->progress_percentage }}" aria-valuemin="0" aria-valuemax="100"><div class="goal-progress-fill" style="width: {{ $goal->progress_percentage }}%"></div></div><span class="goal-progress-percentage">{{ number_format($goal->progress_percentage, 1, ',', '.') }}%</span></div>
                <div class="goal-card-actions">
                    <button type="button" class="goal-contribution-btn" data-bs-toggle="modal" data-bs-target="#contributionModal" data-goal-id="{{ $goal->id }}" data-goal-title="{{ $goal->title }}" {{ $goal->completed || $accounts->isEmpty() ? 'disabled' : '' }}>+ Adicionar contribuição</button>
                    <div class="goal-action-buttons"><button type="button" class="edit-btn js-edit-goal" data-form-mode="edit" data-record="{{ $goal->toJson() }}" data-bs-toggle="modal" data-bs-target="#goalModal"><i class="bi bi-pencil"></i></button><button type="button" class="delete-btn js-delete-record" data-url="/api/goals/{{ $goal->id }}" data-name="{{ $goal->title }}"><i class="bi bi-trash3"></i></button></div>
                </div>
            </article>
        @endforeach
    </div>
    <div class="empty-state goals-empty-state js-filter-empty {{ $goals->isEmpty() ? '' : 'd-none' }}"><i class="bi bi-inbox"></i><p>{{ $goals->isEmpty() ? 'Não há metas cadastradas.' : 'Nenhuma meta encontrada.' }}</p></div>
</section>

<div class="modal fade" id="goalModal" tabindex="-1" aria-hidden="true"><div class="modal-dialog modal-dialog-centered"><div class="modal-content goal-modal system-modal">
    <div class="modal-header"><h2 class="modal-title"><i class="bi bi-piggy-bank"></i> <span class="js-modal-title">Adicionar meta</span></h2><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <form id="goalForm" class="system-form" data-resource="goals"><input type="hidden" name="id"><div class="modal-body">
        <div class="system-form-field"><label for="goalTitle">Nome</label><input type="text" id="goalTitle" name="title" class="form-control" minlength="3" maxlength="100" required></div>
        <div class="system-form-field"><label for="goalTarget">Objetivo</label><input type="number" id="goalTarget" name="target_amount" class="form-control" min="0.01" step="0.01" required></div>
        <div class="system-form-field"><label for="goalDeadline">Prazo</label><input type="date" id="goalDeadline" name="deadline" class="form-control"></div>
        <p class="form-error text-danger d-none"></p>
    </div><div class="modal-footer"><button type="button" class="cancel-btn" data-bs-dismiss="modal">Cancelar</button><button type="submit" class="save-btn"><span class="js-submit-label">+ Adicionar</span></button></div></form>
</div></div></div>

<div class="modal fade" id="contributionModal" tabindex="-1" aria-hidden="true"><div class="modal-dialog modal-dialog-centered"><div class="modal-content contribution-modal system-modal">
    <div class="modal-header"><h2 class="modal-title"><i class="bi bi-coin"></i> Adicionar contribuição</h2><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <form id="contributionForm" class="system-form"><input type="hidden" name="goal_id"><div class="modal-body">
        <p class="js-contribution-goal fw-semibold"></p>
        <div class="system-form-field"><label for="contributionAccount">Conta</label><select id="contributionAccount" name="account_id" class="form-select" required><option value="" disabled selected>Selecione uma conta</option>@foreach($accounts as $account)<option value="{{ $account->id }}">{{ $account->name }} — R$ {{ number_format((float)$account->balance, 2, ',', '.') }}</option>@endforeach</select></div>
        <div class="system-form-field"><label for="contributionAmount">Valor</label><input type="number" id="contributionAmount" name="amount" class="form-control" min="0.01" step="0.01" required></div>
        <p class="form-error text-danger d-none"></p>
    </div><div class="modal-footer"><button type="button" class="cancel-btn" data-bs-dismiss="modal">Cancelar</button><button type="submit" class="save-btn">+ Adicionar</button></div></form>
</div></div></div>
@endsection

@push('scripts')<script src="{{ asset('js/resources.js') }}?v={{ filemtime(public_path('js/resources.js')) }}"></script>@endpush
