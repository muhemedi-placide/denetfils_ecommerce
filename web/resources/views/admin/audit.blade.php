@extends('layouts.admin')

@section('title', 'Journal audit')
@section('page_title', 'Audit')
@section('page_subtitle', 'Tracer les actions sensibles et comprendre qui a fait quoi.')

@section('content')
    @php
        $rows = data_get($auditLogs, 'data', []);
    @endphp

    <section class="space-y-4">
        <form method="GET" class="rounded-2xl border border-stone-200 bg-white p-3 shadow-sm">
            <div class="grid gap-3 md:grid-cols-[minmax(0,1fr)_180px_180px_auto]">
                <label class="block">
                    <span class="mb-1 block text-xs font-bold uppercase tracking-[0.18em] text-stone-400">Action</span>
                    <input name="action" value="{{ $filters['action'] ?? '' }}" placeholder="product.updated, user.login..." class="w-full rounded-xl border border-stone-200 bg-stone-50 px-3 py-2.5 text-sm outline-none transition focus:border-[#f15b2a] focus:bg-white focus:ring-4 focus:ring-orange-100">
                </label>
                <label class="block">
                    <span class="mb-1 block text-xs font-bold uppercase tracking-[0.18em] text-stone-400">Acteur</span>
                    <input name="actor_id" value="{{ $filters['actor_id'] ?? '' }}" placeholder="ID" class="w-full rounded-xl border border-stone-200 bg-stone-50 px-3 py-2.5 text-sm outline-none transition focus:border-[#f15b2a] focus:bg-white focus:ring-4 focus:ring-orange-100">
                </label>
                <label class="block">
                    <span class="mb-1 block text-xs font-bold uppercase tracking-[0.18em] text-stone-400">Cible</span>
                    <input name="auditable_type" value="{{ $filters['auditable_type'] ?? '' }}" placeholder="Product" class="w-full rounded-xl border border-stone-200 bg-stone-50 px-3 py-2.5 text-sm outline-none transition focus:border-[#f15b2a] focus:bg-white focus:ring-4 focus:ring-orange-100">
                </label>
                <button class="self-end rounded-xl bg-[#12210f] px-4 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-[#1c3517]">Filtrer</button>
            </div>
        </form>

        @if(!data_get($auditLogs, 'ok', true))
            <div class="rounded-2xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800">{{ data_get($auditLogs, 'message', 'Impossible de charger le journal audit pour le moment.') }}</div>
        @endif

        <div class="overflow-hidden rounded-2xl border border-stone-200 bg-white shadow-sm">
            <div class="flex flex-col gap-2 border-b border-stone-100 p-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-lg font-black text-[#12210f]">Evenements recents</h2>
                    <p class="text-sm text-stone-500">{{ count($rows) }} entree(s) chargee(s) depuis l API.</p>
                </div>
                <span class="rounded-full bg-stone-100 px-3 py-1 text-xs font-bold text-stone-600">Lecture seule</span>
            </div>

            <div class="divide-y divide-stone-100">
                @forelse($rows as $log)
                    @php
                        $actor = data_get($log, 'actor.name') ?: data_get($log, 'actor.email') ?: 'Systeme';
                        $targetType = class_basename($log['auditable_type'] ?? 'Objet');
                        $metadata = $log['metadata'] ?? [];
                        $metadataPreview = is_array($metadata) ? Str::limit(json_encode($metadata, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), 160) : Str::limit((string) $metadata, 160);
                    @endphp
                    <article class="grid gap-3 p-4 transition hover:bg-stone-50 lg:grid-cols-[220px_minmax(0,1fr)_180px_150px] lg:items-start">
                        <div>
                            <p class="font-black text-[#12210f]">{{ $log['action'] ?? 'action.inconnue' }}</p>
                            <p class="mt-1 text-xs font-bold uppercase tracking-[0.16em] text-stone-400">{{ $log['created_at'] ?? 'date inconnue' }}</p>
                        </div>
                        <div class="min-w-0">
                            <p class="text-sm font-bold text-stone-700">{{ $actor }}</p>
                            <p class="mt-1 break-words rounded-xl bg-stone-50 px-3 py-2 text-xs leading-5 text-stone-500">{{ $metadataPreview ?: 'Aucune meta donnee.' }}</p>
                        </div>
                        <div class="text-sm text-stone-600">
                            <p class="font-bold text-stone-800">{{ $targetType }}</p>
                            <p>ID {{ $log['auditable_id'] ?? 'N/A' }}</p>
                        </div>
                        <div class="text-sm text-stone-500">
                            <p class="font-bold text-stone-700">IP</p>
                            <p>{{ $log['ip_address'] ?? 'Non renseignee' }}</p>
                        </div>
                    </article>
                @empty
                    <div class="p-8 text-center text-sm text-stone-500">Aucun evenement audit ne correspond aux filtres.</div>
                @endforelse
            </div>
        </div>
    </section>
@endsection
