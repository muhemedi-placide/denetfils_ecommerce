@extends('layouts.admin')

@section('title', 'Journal audit')
@section('page_title', 'Audit')
@section('page_subtitle', 'Tracer les actions sensibles et comprendre qui a fait quoi.')

@php
    $rows = data_get($auditLogs, 'data', []);
@endphp

@section('content')
    <section class="space-y-5">
        <form method="GET" class="admin-card p-4">
            <div class="grid gap-3 md:grid-cols-[minmax(0,1fr)_180px_180px_auto]">
                <label class="block">
                    <span class="admin-kicker mb-2 block">Action</span>
                    <input name="action" value="{{ $filters['action'] ?? '' }}" placeholder="product.updated, user.login..." class="admin-input">
                </label>
                <label class="block">
                    <span class="admin-kicker mb-2 block">Acteur</span>
                    <input name="actor_id" value="{{ $filters['actor_id'] ?? '' }}" placeholder="ID" class="admin-input">
                </label>
                <label class="block">
                    <span class="admin-kicker mb-2 block">Cible</span>
                    <input name="auditable_type" value="{{ $filters['auditable_type'] ?? '' }}" placeholder="Product" class="admin-input">
                </label>
                <button class="admin-btn self-end">Filtrer</button>
            </div>
        </form>

        @if(!data_get($auditLogs, 'ok', true))
            <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800">{{ data_get($auditLogs, 'message', 'Impossible de charger le journal audit pour le moment.') }}</div>
        @endif

        <div class="admin-card overflow-hidden">
            <div class="flex flex-col gap-2 border-b border-leaf/10 p-4 dark:border-white/10 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-xl font-black text-ink dark:text-cream">Evenements recents</h2>
                    <p class="text-sm text-cocoa/55 dark:text-cream/55">{{ count($rows) }} entree(s) chargee(s) depuis l API.</p>
                </div>
                <span class="admin-pill">Lecture seule</span>
            </div>

            <div class="divide-y divide-leaf/10 dark:divide-white/10">
                @forelse($rows as $log)
                    @php
                        $actor = data_get($log, 'actor.name') ?: data_get($log, 'actor.email') ?: 'Systeme';
                        $targetType = class_basename($log['auditable_type'] ?? 'Objet');
                        $metadata = $log['metadata'] ?? [];
                        $metadataPreview = is_array($metadata) ? Str::limit(json_encode($metadata, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), 160) : Str::limit((string) $metadata, 160);
                    @endphp
                    <article class="grid gap-3 p-4 transition hover:bg-linen dark:hover:bg-white/5 lg:grid-cols-[220px_minmax(0,1fr)_180px_150px_90px] lg:items-start">
                        <div>
                            <p class="font-black text-ink dark:text-cream">{{ $log['action'] ?? 'action.inconnue' }}</p>
                            <p class="mt-1 text-xs font-bold uppercase tracking-[0.16em] text-cocoa/45 dark:text-cream/45">{{ $log['created_at'] ?? 'date inconnue' }}</p>
                        </div>
                        <div class="min-w-0">
                            <p class="text-sm font-bold text-cocoa/75 dark:text-cream/75">{{ $actor }}</p>
                            <p class="mt-1 break-words rounded-xl bg-linen px-3 py-2 text-xs leading-5 text-cocoa/55 dark:bg-white/5 dark:text-cream/55">{{ $metadataPreview ?: 'Aucune meta donnee.' }}</p>
                        </div>
                        <div class="text-sm text-cocoa/65 dark:text-cream/65">
                            <p class="font-bold text-cocoa dark:text-cream">{{ $targetType }}</p>
                            <p>ID {{ $log['auditable_id'] ?? 'N/A' }}</p>
                        </div>
                        <div class="text-sm text-cocoa/55 dark:text-cream/55">
                            <p class="font-bold text-cocoa/70 dark:text-cream/70">IP</p>
                            <p>{{ $log['ip_address'] ?? 'Non renseignee' }}</p>
                        </div>
                        <div class="flex justify-end">
                            <button type="button" data-dialog-target="audit-show-{{ $loop->index }}" class="admin-btn-secondary min-h-0 px-3 py-2 text-xs">Voir</button>
                        </div>
                    </article>
                @empty
                    <div class="p-8 text-center text-sm text-cocoa/55 dark:text-cream/55">Aucun evenement audit ne correspond aux filtres.</div>
                @endforelse
            </div>
        </div>
    </section>
@endsection

@push('admin_modals')
    @foreach($rows as $log)
        @php
            $actor = data_get($log, 'actor.name') ?: data_get($log, 'actor.email') ?: 'Systeme';
            $targetType = class_basename($log['auditable_type'] ?? 'Objet');
            $metadata = $log['metadata'] ?? [];
            $metadataFull = is_array($metadata) ? json_encode($metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : (string) $metadata;
        @endphp
        <dialog id="audit-show-{{ $loop->index }}" class="admin-dialog admin-dialog-wide">
            <div class="admin-modal-card p-5 sm:p-6">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="admin-kicker">Audit</p>
                        <h2 class="mt-2 text-2xl font-black text-cocoa dark:text-cream">{{ $log['action'] ?? 'action.inconnue' }}</h2>
                        <p class="mt-1 text-sm text-cocoa/55 dark:text-cream/55">{{ $log['created_at'] ?? 'date inconnue' }}</p>
                    </div>
                    <button type="button" data-dialog-close class="admin-icon-btn" aria-label="Fermer">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18" /><path d="m6 6 12 12" /></svg>
                    </button>
                </div>
                <div class="mt-5 grid gap-3 sm:grid-cols-3">
                    <div class="admin-panel p-4"><p class="admin-kicker">Acteur</p><p class="mt-2 text-sm font-black">{{ $actor }}</p></div>
                    <div class="admin-panel p-4"><p class="admin-kicker">Cible</p><p class="mt-2 text-sm font-black">{{ $targetType }} #{{ $log['auditable_id'] ?? 'N/A' }}</p></div>
                    <div class="admin-panel p-4"><p class="admin-kicker">IP</p><p class="mt-2 text-sm font-black">{{ $log['ip_address'] ?? 'Non renseignee' }}</p></div>
                </div>
                <div class="mt-5 admin-panel p-4">
                    <p class="admin-kicker">Meta donnees</p>
                    <pre class="mt-3 max-h-96 overflow-auto whitespace-pre-wrap rounded-xl bg-white p-4 text-xs leading-5 text-cocoa ring-1 ring-leaf/10 dark:bg-ink dark:text-cream dark:ring-white/10">{{ $metadataFull ?: 'Aucune meta donnee.' }}</pre>
                </div>
            </div>
        </dialog>
    @endforeach
@endpush
