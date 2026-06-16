@extends('layouts.admin')

@section('title', $module['title'])
@section('page_title', $module['title'])
@section('page_subtitle', $module['description'])

@section('content')
    <section class="grid gap-5 xl:grid-cols-[minmax(0,1fr)_360px]">
        <div class="space-y-5">
            <article class="admin-card overflow-hidden">
                <div class="border-b border-leaf/10 p-5 dark:border-white/10 sm:p-6">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                        <div>
                            <p class="admin-kicker">{{ $module['section'] }}</p>
                            <h2 class="mt-2 text-3xl font-black text-ink dark:text-cream">{{ $module['title'] }}</h2>
                            <p class="mt-3 max-w-3xl text-sm leading-7 text-cocoa/65 dark:text-cream/65">{{ $module['description'] }}</p>
                        </div>
                        <span class="admin-pill">{{ $module['status'] }}</span>
                    </div>
                </div>

                <div class="grid gap-4 p-5 sm:grid-cols-2 xl:grid-cols-4 sm:p-6">
                    @foreach ($module['metrics'] as $metric)
                        <div class="admin-panel p-4">
                            <p class="text-xs font-black uppercase tracking-[0.16em] text-cocoa/45 dark:text-cream/45">{{ $metric }}</p>
                            <p class="mt-4 text-3xl font-black text-ink dark:text-cream">--</p>
                            <p class="mt-2 text-xs font-semibold text-cocoa/45 dark:text-cream/45">En attente de source API</p>
                        </div>
                    @endforeach
                </div>
            </article>

            <article class="admin-card p-5 sm:p-6">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p class="admin-kicker">Workflow</p>
                        <h2 class="mt-2 admin-heading">Parcours operationnel</h2>
                    </div>
                    <span class="admin-pill">Page dediee</span>
                </div>

                <div class="mt-5 grid gap-3 md:grid-cols-2">
                    @foreach ($module['workflows'] as $step)
                        <div class="rounded-xl border border-leaf/10 bg-linen p-4 dark:border-white/10 dark:bg-white/5">
                            <div class="flex items-start gap-3">
                                <span class="grid h-8 w-8 shrink-0 place-items-center rounded-full bg-white text-xs font-black text-leaf ring-1 ring-leaf/10 dark:bg-white/10 dark:text-meadow dark:ring-white/10">{{ $loop->iteration }}</span>
                                <div>
                                    <p class="font-black text-ink dark:text-cream">{{ $step }}</p>
                                    <p class="mt-1 text-sm leading-6 text-cocoa/55 dark:text-cream/55">Ecran prepare pour recevoir les endpoints, validations et actions modales du module.</p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </article>
        </div>

        <aside class="space-y-5">
            <div class="rounded-2xl border border-leaf/10 bg-forest p-5 text-white shadow-sm dark:border-white/10 dark:bg-white/5">
                <p class="text-xs font-black uppercase tracking-[0.18em] text-meadow">ERP / CRM</p>
                <h2 class="mt-2 text-xl font-black">Module separe</h2>
                <p class="mt-3 text-sm leading-7 text-white/70">Cette page isole le module pour garder le back-office lisible. Les actions seront ajoutees ici, pas dans une page surchargee.</p>
            </div>

            <div class="admin-card p-5">
                <p class="admin-kicker">Prochaine integration</p>
                <div class="mt-4 space-y-3">
                    <div class="rounded-xl bg-linen p-3 dark:bg-white/5">
                        <p class="font-black text-ink dark:text-cream">Endpoint API</p>
                        <p class="mt-1 text-sm text-cocoa/55 dark:text-cream/55">Brancher lecture et mutations du domaine.</p>
                    </div>
                    <div class="rounded-xl bg-linen p-3 dark:bg-white/5">
                        <p class="font-black text-ink dark:text-cream">Actions modales</p>
                        <p class="mt-1 text-sm text-cocoa/55 dark:text-cream/55">Creation, edition, validation et confirmation.</p>
                    </div>
                    <div class="rounded-xl bg-linen p-3 dark:bg-white/5">
                        <p class="font-black text-ink dark:text-cream">Audit</p>
                        <p class="mt-1 text-sm text-cocoa/55 dark:text-cream/55">Tracer les actions sensibles du module.</p>
                    </div>
                </div>
            </div>
        </aside>
    </section>
@endsection
