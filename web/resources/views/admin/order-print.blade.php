@php
    $shop = $shopDocument ?? config('documents.shop', []);
    $shipping = collect($order['addresses'] ?? [])->firstWhere('type', 'shipping');
    $billing = collect($order['addresses'] ?? [])->firstWhere('type', 'billing') ?: $shipping;
    $items = $order['items'] ?? [];
    $dateValue = fn (?string $value) => $value ? Str::of($value)->replace('T', ' ')->before('+')->before('Z') : '-';
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', $locale ?? app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Impression commande {{ $order['order_number'] ?? $order['id'] ?? '' }} | Denetfils</title>
        <style>
            @page {
                size: A4;
                margin: 18mm 16mm 20mm;
            }

            * {
                box-sizing: border-box;
            }

            body {
                margin: 0;
                background: #f5f2ea;
                color: #111811;
                font-family: Arial, Helvetica, sans-serif;
                font-size: 13px;
                line-height: 1.45;
            }

            .no-print {
                padding: 18px;
                text-align: right;
            }

            .print-button {
                border: 1px solid #111811;
                background: #111811;
                color: #fff;
                cursor: pointer;
                font-weight: 800;
                padding: 12px 18px;
            }

            .document {
                max-width: 210mm;
                min-height: 297mm;
                margin: 0 auto 24px;
                background: #fff;
                padding: 34mm 16mm 24mm;
                position: relative;
                box-shadow: 0 18px 60px rgba(17, 24, 17, .14);
            }

            .document-header,
            .document-footer {
                left: 16mm;
                right: 16mm;
                position: absolute;
            }

            .document-header {
                top: 12mm;
                border-bottom: 1px solid #d9e4d7;
                padding-bottom: 12px;
            }

            .document-footer {
                bottom: 10mm;
                border-top: 1px solid #d9e4d7;
                color: #6e675d;
                display: flex;
                font-size: 11px;
                justify-content: space-between;
                padding-top: 9px;
            }

            .top-line {
                background: #1f643f;
                color: #fff;
                font-size: 11px;
                font-weight: 800;
                letter-spacing: .08em;
                margin: -12mm -16mm 18px;
                padding: 8px 16mm;
                text-transform: uppercase;
            }

            .header-grid,
            .address-grid,
            .summary-grid {
                display: grid;
                gap: 18px;
                grid-template-columns: 1fr 1fr;
            }

            h1,
            h2,
            h3,
            p {
                margin: 0;
            }

            h1 {
                font-size: 28px;
                letter-spacing: .04em;
            }

            h2 {
                font-size: 22px;
                text-align: right;
            }

            h3 {
                font-size: 15px;
                margin-bottom: 10px;
            }

            .muted {
                color: #6e675d;
            }

            .panel {
                border: 1px solid #d9e4d7;
                background: #fbfbf7;
                padding: 14px;
            }

            .section {
                margin-top: 24px;
            }

            .summary-grid {
                grid-template-columns: repeat(3, 1fr);
            }

            .summary-grid div {
                border: 1px solid #d9e4d7;
                padding: 10px;
            }

            .summary-grid span {
                color: #6e675d;
                display: block;
                font-size: 10px;
                font-weight: 800;
                text-transform: uppercase;
            }

            .summary-grid strong {
                display: block;
                margin-top: 4px;
            }

            table {
                border-collapse: collapse;
                width: 100%;
            }

            th {
                background: #eef4ec;
                color: #111811;
                font-size: 11px;
                text-align: left;
                text-transform: uppercase;
            }

            th,
            td {
                border-bottom: 1px solid #d9e4d7;
                padding: 10px 8px;
                vertical-align: top;
            }

            .product-cell {
                align-items: center;
                display: flex;
                gap: 10px;
            }

            .product-thumb {
                background: #eef4ec;
                border: 1px solid #d9e4d7;
                border-radius: 10px;
                height: 44px;
                object-fit: cover;
                width: 44px;
            }

            .right {
                text-align: right;
            }

            .totals {
                margin-left: auto;
                margin-top: 18px;
                width: 260px;
            }

            .totals div {
                display: flex;
                justify-content: space-between;
                padding: 7px 0;
            }

            .totals .grand-total {
                border-top: 2px solid #111811;
                font-size: 16px;
                font-weight: 900;
                margin-top: 4px;
                padding-top: 10px;
            }

            @media print {
                body {
                    background: #fff;
                }

                .no-print {
                    display: none;
                }

                .document {
                    box-shadow: none;
                    margin: 0;
                    max-width: none;
                    min-height: auto;
                    padding: 34mm 0 22mm;
                }

                .document-header,
                .document-footer {
                    left: 0;
                    position: fixed;
                    right: 0;
                }

                .document-header {
                    top: 0;
                }

                .document-footer {
                    bottom: 0;
                }

                .top-line {
                    margin-left: 0;
                    margin-right: 0;
                    padding-left: 0;
                    padding-right: 0;
                }
            }
        </style>
    </head>
    <body>
        <div class="no-print">
            <button type="button" class="print-button" onclick="window.print()">Imprimer</button>
        </div>

        <main class="document">
            <header class="document-header">
                <div class="top-line">{{ $shop['tagline'] ?? 'Boutique alimentaire premium' }}</div>
                <div class="header-grid">
                    <div>
                        <h1>{{ $shop['brand'] ?? 'DEN & FILS' }}</h1>
                        <p class="muted">{{ $shop['trade_name'] ?? 'Denetfils' }}</p>
                        <p class="muted">{{ $shop['website'] ?? 'denetfils.fr' }} - {{ $shop['email'] ?? 'support@denetfils.fr' }}</p>
                    </div>
                    <div>
                        <h2>COMMANDE</h2>
                        <p class="right"><strong>{{ $order['order_number'] ?? '-' }}</strong></p>
                        <p class="right muted">Date: {{ $dateValue($order['placed_at'] ?? $order['created_at'] ?? null) }}</p>
                    </div>
                </div>
            </header>

            <section class="summary-grid">
                <div><span>ID commande</span><strong>{{ $order['id'] ?? '-' }}</strong></div>
                <div><span>Paiement</span><strong>{{ data_get($order, 'payment_method', '-') }}</strong></div>
                <div><span>Transporteur</span><strong>{{ $order['carrier'] ?? '-' }}</strong></div>
            </section>

            <section class="section address-grid">
                @foreach ([['Adresse de livraison', $shipping], ['Adresse de facturation', $billing]] as [$title, $address])
                    <div class="panel">
                        <h3>{{ $title }}</h3>
                        <p>{{ data_get($address, 'recipient_name', '-') }}</p>
                        @if (data_get($address, 'company'))
                            <p>{{ data_get($address, 'company') }}</p>
                        @endif
                        <p>{{ data_get($address, 'street_line_1', '-') }}</p>
                        @if (data_get($address, 'street_line_2'))
                            <p>{{ data_get($address, 'street_line_2') }}</p>
                        @endif
                        <p>{{ data_get($address, 'postal_code', '') }} {{ data_get($address, 'city', '') }}</p>
                        <p>{{ data_get($address, 'country_code', '') }}</p>
                        @if (data_get($address, 'phone'))
                            <p>{{ data_get($address, 'phone') }}</p>
                        @endif
                    </div>
                @endforeach
            </section>

            <section class="section">
                <h3>Articles</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Ref.</th>
                            <th>Produit</th>
                            <th class="right">PU TTC</th>
                            <th class="right">Quantite</th>
                            <th class="right">Total TTC</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($items as $item)
                            @php
                                $imageUrl = data_get($item, 'product.image.url');
                                $productName = data_get($item, 'product.name', '-');
                            @endphp
                            <tr>
                                <td>{{ data_get($item, 'product.sku', '-') }}</td>
                                <td>
                                    <div class="product-cell">
                                        @if ($imageUrl)
                                            <img src="{{ $imageUrl }}" alt="{{ $productName }}" class="product-thumb">
                                        @endif
                                        <span>{{ $productName }}</span>
                                    </div>
                                </td>
                                <td class="right">{{ $item['formatted_unit_price'] ?? '-' }}</td>
                                <td class="right">{{ $item['quantity'] ?? 0 }}</td>
                                <td class="right"><strong>{{ $item['formatted_line_total'] ?? '-' }}</strong></td>
                            </tr>
                        @empty
                            <tr><td colspan="5">Aucun article dans cette commande.</td></tr>
                        @endforelse
                    </tbody>
                </table>

                <div class="totals">
                    <div><span>Produits</span><strong>{{ $order['formatted_subtotal'] ?? '-' }}</strong></div>
                    <div><span>Livraison</span><strong>{{ $order['formatted_shipping'] ?? '-' }}</strong></div>
                    <div><span>TVA</span><strong>{{ $order['formatted_tax'] ?? '-' }}</strong></div>
                    <div class="grand-total"><span>Total TTC</span><strong>{{ $order['formatted_total'] ?? '-' }}</strong></div>
                </div>
            </section>

            <footer class="document-footer">
                <span>{{ $shop['brand'] ?? 'DEN & FILS' }} - {{ $shop['website'] ?? 'denetfils.fr' }} - {{ $shop['email'] ?? 'support@denetfils.fr' }}</span>
                <span>Document genere par le back-office Denetfils</span>
            </footer>
        </main>
    </body>
</html>
