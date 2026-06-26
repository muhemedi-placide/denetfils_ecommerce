@extends('layouts.shop')

@php
    $isEnglish = $locale === 'en';
    $orderNumber = $order['order_number'] ?? ('#'.$order['id']);
    $placedAt = $order['placed_at'] ?? $order['created_at'] ?? null;
    $tracking = $order['tracking'] ?? [];
    $trackingNumber = $tracking['number'] ?? null;
    $trackingUrl = $tracking['url'] ?? null;
    $conversationStatus = $conversation['status'] ?? 'not_started';
    $messages = $conversation['messages'] ?? [];
    $unreadCount = (int) ($conversation['customer_unread_count'] ?? 0);
    $statusLabels = [
        'not_started' => $isEnglish ? 'Not opened' : 'A ouvrir',
        'open' => $isEnglish ? 'Open' : 'Ouverte',
        'closed' => $isEnglish ? 'Closed' : 'Closee',
    ];
    $icon = function (string $name, string $class = 'h-5 w-5') {
        $paths = [
            'back' => '<path d="M19 12H5"/><path d="m12 19-7-7 7-7"/>',
            'package' => '<path d="m21 8-9-5-9 5 9 5 9-5Z"/><path d="M3 8v8l9 5 9-5V8"/><path d="M12 13v8"/>',
            'truck' => '<path d="M14 18V6H3v12h11Z"/><path d="M14 9h4l3 4v5h-7"/><circle cx="7" cy="18" r="2"/><circle cx="17" cy="18" r="2"/>',
            'message' => '<path d="M21 15a4 4 0 0 1-4 4H8l-5 3V7a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4v8Z"/>',
            'check' => '<path d="m20 6-11 11-5-5"/>',
            'lock' => '<rect x="5" y="11" width="14" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/>',
            'send' => '<path d="m22 2-7 20-4-9-9-4 20-7Z"/><path d="M22 2 11 13"/>',
        ];

        return '<svg class="'.e($class).'" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">'.($paths[$name] ?? $paths['package']).'</svg>';
    };
@endphp

@section('title', ($isEnglish ? 'Order ' : 'Commande ') . $orderNumber . ' | DEN & FILS')
@section('description', $isEnglish ? 'Customer order detail.' : 'Detail de la commande client.')
@section('robots', 'noindex,nofollow')

@section('content')
    <section class="soft-grid px-4 pb-20 pt-8 dark:bg-ink sm:px-6 lg:px-8 lg:pb-24 lg:pt-10">
        <div class="mx-auto max-w-7xl">
            @if (session('status'))
                <div class="mb-4 rounded-xl border border-leaf/20 bg-mint px-4 py-3 text-sm font-black text-forest dark:border-white/10 dark:bg-white/10 dark:text-meadow">
                    {{ session('status') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-4 rounded-xl border border-coral/25 bg-coral/10 px-4 py-3 text-sm font-semibold text-cocoa dark:text-cream">
                    {{ $errors->first() }}
                </div>
            @endif

            <a href="{{ route('account.show', ['locale' => $locale]) }}" class="mb-5 inline-flex min-h-[42px] items-center gap-2 rounded-full bg-white px-4 text-sm font-black text-forest shadow-sm transition hover:bg-mint dark:bg-white/10 dark:text-meadow" wire:navigate>
                {!! $icon('back', 'h-4 w-4') !!}
                {{ $isEnglish ? 'Back to account' : 'Retour au compte' }}
            </a>

            <div class="overflow-hidden rounded-2xl border border-leaf/10 bg-[#f8f7f3] shadow-tropical dark:border-white/10 dark:bg-[#101b10]">
                <header class="border-b border-leaf/10 bg-white p-5 dark:border-white/10 dark:bg-white/5 sm:p-6">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                        <div>
                            <p class="text-xs font-black uppercase tracking-[0.2em] text-leaf dark:text-meadow">{{ $isEnglish ? 'Order detail' : 'Detail commande' }}</p>
                            <h1 class="mt-2 break-words text-3xl font-black text-cocoa dark:text-cream sm:text-4xl">{{ $orderNumber }}</h1>
                            @if ($placedAt)
                                <p class="mt-2 text-sm font-semibold text-cocoa/60 dark:text-cream/60">
                                    {{ \Illuminate\Support\Carbon::parse($placedAt)->locale($locale)->isoFormat('LLL') }}
                                </p>
                            @endif
                        </div>
                        <div class="grid gap-2 sm:grid-cols-3 lg:min-w-[520px]">
                            <div class="rounded-xl bg-linen p-3 dark:bg-white/5">
                                <p class="text-[11px] font-black uppercase tracking-wide text-cocoa/45 dark:text-cream/45">{{ $isEnglish ? 'Order' : 'Commande' }}</p>
                                <p class="mt-1 text-sm font-black text-forest dark:text-meadow">{{ $order['status_label'] ?? $order['status'] ?? '-' }}</p>
                            </div>
                            <div class="rounded-xl bg-linen p-3 dark:bg-white/5">
                                <p class="text-[11px] font-black uppercase tracking-wide text-cocoa/45 dark:text-cream/45">{{ $isEnglish ? 'Payment' : 'Paiement' }}</p>
                                <p class="mt-1 text-sm font-black text-forest dark:text-meadow">{{ $order['payment_status_label'] ?? $order['payment_status'] ?? '-' }}</p>
                            </div>
                            <div class="rounded-xl bg-linen p-3 dark:bg-white/5">
                                <p class="text-[11px] font-black uppercase tracking-wide text-cocoa/45 dark:text-cream/45">{{ $isEnglish ? 'Shipping' : 'Livraison' }}</p>
                                <p class="mt-1 text-sm font-black text-forest dark:text-meadow">{{ $order['fulfillment_status_label'] ?? $order['fulfillment_status'] ?? '-' }}</p>
                            </div>
                        </div>
                    </div>
                </header>

                <main class="grid gap-5 p-4 sm:p-6 xl:grid-cols-[minmax(0,1.2fr)_420px]">
                    <div class="grid gap-5">
                        <section class="rounded-xl border border-leaf/10 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-white/5">
                            <div class="flex items-start gap-3">
                                <span class="grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-mint text-forest dark:bg-white/10 dark:text-meadow">{!! $icon('truck') !!}</span>
                                <div class="min-w-0 flex-1">
                                    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                        <div>
                                            <p class="text-xs font-black uppercase tracking-[0.2em] text-leaf dark:text-meadow">{{ $isEnglish ? 'Tracking' : 'Suivi' }}</p>
                                            <h2 class="mt-2 text-2xl font-black text-cocoa dark:text-cream">{{ $isEnglish ? 'Delivery status' : 'Statut de livraison' }}</h2>
                                        </div>
                                        <span class="inline-flex min-h-[32px] items-center rounded-full bg-mint px-3 text-xs font-black uppercase tracking-wide text-forest dark:bg-white/10 dark:text-meadow">
                                            {{ $tracking['shipment_status'] ?? $order['fulfillment_status'] ?? '-' }}
                                        </span>
                                    </div>

                                    <div class="mt-5 rounded-xl bg-linen p-4 dark:bg-white/5">
                                        <p class="text-xs font-black uppercase tracking-wide text-cocoa/45 dark:text-cream/45">{{ $isEnglish ? 'Tracking code' : 'Code suivi' }}</p>
                                        @if ($trackingNumber)
                                            <p class="mt-2 break-all text-xl font-black text-cocoa dark:text-cream">{{ $trackingNumber }}</p>
                                            @if ($trackingUrl)
                                                <a href="{{ $trackingUrl }}" target="_blank" rel="noopener" class="mt-3 inline-flex min-h-[38px] items-center rounded-full bg-forest px-4 text-xs font-black uppercase tracking-wide text-cream transition hover:bg-leaf">
                                                    {{ $isEnglish ? 'Track parcel' : 'Suivre le colis' }}
                                                </a>
                                            @endif
                                        @else
                                            <p class="mt-2 text-sm font-semibold text-cocoa/60 dark:text-cream/60">{{ $isEnglish ? 'The tracking code will appear here when the shipment is prepared.' : 'Le code suivi apparaitra ici quand l expedition sera preparee.' }}</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </section>

                        <section class="rounded-xl border border-leaf/10 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-white/5">
                            <div class="flex items-center gap-3 border-b border-leaf/10 pb-4 dark:border-white/10">
                                <span class="grid h-11 w-11 place-items-center rounded-xl bg-mint text-forest dark:bg-white/10 dark:text-meadow">{!! $icon('package') !!}</span>
                                <div>
                                    <p class="text-xs font-black uppercase tracking-[0.2em] text-leaf dark:text-meadow">{{ $isEnglish ? 'Products' : 'Produits' }}</p>
                                    <h2 class="text-2xl font-black text-cocoa dark:text-cream">{{ $isEnglish ? 'Order content' : 'Contenu de la commande' }}</h2>
                                </div>
                            </div>

                            <div class="divide-y divide-leaf/10 dark:divide-white/10">
                                @foreach (($order['items'] ?? []) as $item)
                                    @php
                                        $imageUrl = data_get($item, 'product.image.url');
                                        $productName = data_get($item, 'product.name', '-');
                                    @endphp
                                    <div class="grid gap-3 py-4 sm:grid-cols-[minmax(0,1fr)_110px_120px] sm:items-center">
                                        <div class="flex min-w-0 items-center gap-3">
                                            @if ($imageUrl)
                                                <img src="{{ $imageUrl }}" alt="{{ $productName }}" class="h-14 w-14 shrink-0 rounded-xl object-cover ring-1 ring-leaf/10 dark:ring-white/10" loading="lazy" decoding="async">
                                            @else
                                                <span class="grid h-14 w-14 shrink-0 place-items-center rounded-xl bg-mint text-xs font-black text-forest dark:bg-white/10 dark:text-meadow">DF</span>
                                            @endif
                                            <div class="min-w-0">
                                                <p class="truncate text-base font-black text-cocoa dark:text-cream">{{ $productName }}</p>
                                                <p class="mt-1 text-xs font-semibold text-cocoa/55 dark:text-cream/55">{{ data_get($item, 'product.sku') ?: data_get($item, 'variant.sku') }}</p>
                                            </div>
                                        </div>
                                        <p class="text-sm font-black text-cocoa/70 dark:text-cream/70">x {{ $item['quantity'] ?? 1 }}</p>
                                        <p class="text-sm font-black text-cocoa dark:text-cream">{{ $item['formatted_line_total'] ?? $item['formatted_unit_price'] ?? '' }}</p>
                                    </div>
                                @endforeach
                            </div>

                            <div class="mt-4 flex justify-end border-t border-leaf/10 pt-4 dark:border-white/10">
                                <div class="min-w-[220px] rounded-xl bg-linen p-4 text-right dark:bg-white/5">
                                    <p class="text-xs font-black uppercase tracking-wide text-cocoa/45 dark:text-cream/45">{{ $isEnglish ? 'Total' : 'Total' }}</p>
                                    <p class="mt-1 text-2xl font-black text-forest dark:text-meadow">{{ $order['formatted_total'] ?? '' }}</p>
                                </div>
                            </div>
                        </section>
                    </div>

                    <aside class="rounded-2xl border border-leaf/10 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-white/5">
                        <div class="flex items-start justify-between gap-3 border-b border-leaf/10 pb-4 dark:border-white/10">
                            <div class="flex items-center gap-3">
                                <span class="grid h-11 w-11 place-items-center rounded-xl bg-mint text-forest dark:bg-white/10 dark:text-meadow">{!! $icon('message') !!}</span>
                                <div>
                                    <p class="text-xs font-black uppercase tracking-[0.2em] text-leaf dark:text-meadow">{{ $isEnglish ? 'Support' : 'Support' }}</p>
                                    <h2 class="text-xl font-black text-cocoa dark:text-cream">{{ $isEnglish ? 'Order discussion' : 'Discussion commande' }}</h2>
                                </div>
                            </div>
                            <span class="inline-flex min-h-[30px] items-center rounded-full px-3 text-xs font-black uppercase tracking-wide {{ $conversationStatus === 'closed' ? 'bg-coral/10 text-coral' : 'bg-mint text-forest dark:bg-white/10 dark:text-meadow' }}">
                                {{ $statusLabels[$conversationStatus] ?? $conversationStatus }}
                            </span>
                        </div>

                        @if ($unreadCount > 0)
                            <form method="POST" action="{{ route('account.orders.discussion.read', ['locale' => $locale, 'order' => $order['id']]) }}" class="mt-4">
                                @csrf
                                <button type="submit" class="inline-flex min-h-[38px] items-center gap-2 rounded-full bg-sunshine px-4 text-xs font-black uppercase tracking-wide text-forest transition hover:bg-mango">
                                    {!! $icon('check', 'h-4 w-4') !!}
                                    {{ $isEnglish ? 'Mark as read' : 'Marquer comme lu' }} ({{ $unreadCount }})
                                </button>
                            </form>
                        @endif

                        <div class="chat-thread mt-4">
                            @forelse ($messages as $message)
                                @php
                                    $isOwn = (bool) ($message['is_own'] ?? false) || ($message['sender_type'] ?? null) === 'customer';
                                    $messageStatus = $message['status'] ?? 'read';
                                @endphp
                                <article class="chat-bubble {{ $isOwn ? 'chat-bubble-own' : 'chat-bubble-other' }}">
                                    <div class="chat-meta">
                                        <p class="{{ $isOwn ? 'text-cream/70' : 'text-cocoa/45 dark:text-cream/45' }}">
                                            {{ $isOwn ? ($isEnglish ? 'You' : 'Vous') : 'DEN & FILS' }}
                                        </p>
                                        <span class="shrink-0 {{ $isOwn ? 'text-cream/70' : ($messageStatus === 'unread' ? 'text-coral' : 'text-cocoa/45 dark:text-cream/45') }}">
                                            {{ $messageStatus === 'unread' ? ($isEnglish ? 'Unread' : 'Non lu') : ($isEnglish ? 'Read' : 'Lu') }}
                                        </span>
                                    </div>
                                    <p class="mt-2 whitespace-pre-line text-sm font-semibold leading-6">{{ $message['body'] ?? '' }}</p>
                                    @if (! empty($message['created_at']))
                                        <p class="mt-2 text-[11px] font-semibold {{ $isOwn ? 'text-cream/60' : 'text-cocoa/45 dark:text-cream/45' }}">
                                            {{ \Illuminate\Support\Carbon::parse($message['created_at'])->locale($locale)->isoFormat('LLL') }}
                                        </p>
                                    @endif
                                </article>
                            @empty
                                <div class="rounded-xl border border-dashed border-leaf/20 bg-white p-5 text-sm font-semibold leading-6 text-cocoa/60 dark:border-white/10 dark:bg-white/5 dark:text-cream/60">
                                    {{ $isEnglish ? 'Open a discussion to contact support about this order.' : 'Ouvrez une discussion pour contacter le support sur cette commande.' }}
                                </div>
                            @endforelse
                        </div>

                        @if ($conversationStatus === 'open')
                            <form method="POST" action="{{ route('account.orders.discussion.messages', ['locale' => $locale, 'order' => $order['id']]) }}" class="mt-4 border-t border-leaf/10 pt-4 dark:border-white/10">
                                @csrf
                                <label for="order-message-body" class="text-sm font-black text-cocoa dark:text-cream">{{ $isEnglish ? 'Message' : 'Message' }}</label>
                                <div class="chat-composer mt-2">
                                    <textarea id="order-message-body" name="body" required rows="4" maxlength="2000" class="chat-textarea" placeholder="{{ $isEnglish ? 'Write your message...' : 'Ecrivez votre message...' }}">{{ old('body') }}</textarea>
                                    <div class="chat-toolbar">
                                        <span class="text-xs font-semibold text-cocoa/50 dark:text-cream/50">{{ $isEnglish ? 'Visible by support' : 'Visible par le support' }}</span>
                                        <div class="flex flex-wrap gap-2">
                                            <button type="submit" class="btn-primary gap-2">
                                                {!! $icon('send', 'h-4 w-4') !!}
                                                {{ $isEnglish ? 'Send' : 'Envoyer' }}
                                            </button>
                                            <button type="submit" formnovalidate formaction="{{ route('account.orders.discussion.close', ['locale' => $locale, 'order' => $order['id']]) }}" class="inline-flex min-h-[46px] items-center justify-center gap-2 rounded-full border border-leaf/20 px-5 py-3 text-sm font-black uppercase tracking-wide text-cocoa transition hover:border-coral hover:text-coral dark:border-white/10 dark:text-cream">
                                                {!! $icon('lock', 'h-4 w-4') !!}
                                                {{ $isEnglish ? 'Close' : 'Clore' }}
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        @else
                            <form method="POST" action="{{ route('account.orders.discussion.open', ['locale' => $locale, 'order' => $order['id']]) }}" class="mt-4 border-t border-leaf/10 pt-4 dark:border-white/10">
                                @csrf
                                <button type="submit" class="btn-primary gap-2">
                                    {!! $icon('message', 'h-4 w-4') !!}
                                    {{ $conversationStatus === 'closed' ? ($isEnglish ? 'Reopen discussion' : 'Rouvrir la discussion') : ($isEnglish ? 'Open discussion' : 'Ouvrir un chat') }}
                                </button>
                            </form>
                        @endif
                    </aside>
                </main>
            </div>
        </div>
    </section>
@endsection
