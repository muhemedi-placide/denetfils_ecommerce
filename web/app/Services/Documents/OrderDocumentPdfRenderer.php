<?php

namespace App\Services\Documents;

use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class OrderDocumentPdfRenderer
{
    private const PAGE_WIDTH = 595.0;
    private const PAGE_HEIGHT = 842.0;
    private const MARGIN = 42.0;
    private const CONTENT_TOP = 662.0;
    private const FOOTER_TOP = 82.0;

    private array $pages = [];

    private float $y = self::CONTENT_TOP;

    private string $type = 'invoice';

    private string $title = 'FACTURE';

    private array $order = [];

    private array $shop = [];

    public function render(string $type, array $order): string
    {
        $this->type = $type === 'delivery-note' ? 'delivery-note' : 'invoice';
        $this->title = $this->type === 'invoice' ? 'FACTURE' : 'BON DE LIVRAISON';
        $this->order = $order;
        $this->shop = config('documents.shop', []);
        $this->pages = [];

        $this->addPage();
        $this->drawOrderSummary();
        $this->drawAddresses();
        $this->drawItemsTable();
        $this->drawTotalsAndNotes();

        return $this->buildPdf();
    }

    private function addPage(): void
    {
        $this->pages[] = [];
        $this->drawPageHeader(count($this->pages));
        $this->y = self::CONTENT_TOP;
    }

    private function drawPageHeader(int $page): void
    {
        $this->rect(0, 810, self::PAGE_WIDTH, 32, [0.12, 0.39, 0.25]);
        $this->rect(self::MARGIN, 724, self::PAGE_WIDTH - (self::MARGIN * 2), 1, [0.87, 0.91, 0.86]);
        $this->rect(self::MARGIN, 692, self::PAGE_WIDTH - (self::MARGIN * 2), 1, [0.87, 0.91, 0.86]);

        $this->text(self::MARGIN, 821, (string) ($this->shop['tagline'] ?? 'Boutique alimentaire premium'), 9, 'F2', [1, 1, 1]);
        $this->text(self::MARGIN, 780, (string) ($this->shop['brand'] ?? 'DEN & FILS'), 24, 'F2', [0.08, 0.10, 0.08]);
        $this->text(self::MARGIN, 760, (string) ($this->shop['trade_name'] ?? 'Denetfils'), 10, 'F1', [0.33, 0.30, 0.25]);

        $contactLines = array_filter([
            $this->shop['website'] ?? null,
            $this->shop['email'] ?? null,
            $this->shop['phone'] ?? null,
        ]);
        $contactY = 780;

        foreach ($contactLines as $line) {
            $this->text(172, $contactY, (string) $line, 8.5, 'F1', [0.33, 0.30, 0.25]);
            $contactY -= 13;
        }

        $documentRef = $this->documentReference();
        $this->textRight(self::PAGE_WIDTH - self::MARGIN, 782, $this->title, 20, 'F2', [0.08, 0.10, 0.08]);
        $this->textRight(self::PAGE_WIDTH - self::MARGIN, 760, $documentRef, 10, 'F2', [0.12, 0.39, 0.25]);
        $this->textRight(self::PAGE_WIDTH - self::MARGIN, 744, 'Date: '.$this->date($this->order['placed_at'] ?? $this->order['created_at'] ?? null), 8.5, 'F1', [0.33, 0.30, 0.25]);

        if ($page > 1) {
            $this->textRight(self::PAGE_WIDTH - self::MARGIN, 708, 'Suite du document', 8.5, 'F2', [0.33, 0.30, 0.25]);
        }
    }

    private function drawOrderSummary(): void
    {
        $this->sectionTitle('Commande');

        $status = $this->labelFromMap($this->order['status'] ?? null, [
            'pending_payment' => 'Paiement en attente',
            'confirmed' => 'Confirmee',
            'processing' => 'En traitement',
            'completed' => 'Terminee',
            'cancelled' => 'Annulee',
            'refunded' => 'Remboursee',
        ]);

        $this->infoGrid([
            ['Reference', (string) ($this->order['order_number'] ?? '-')],
            ['ID commande', (string) ($this->order['id'] ?? '-')],
            ['Date commande', $this->date($this->order['placed_at'] ?? $this->order['created_at'] ?? null)],
            ['Etat', $status],
            ['Paiement', (string) (data_get($this->order, 'payment_method') ?: data_get($this->order, 'metadata.payment.method') ?: '-')],
            ['Transporteur', (string) ($this->order['carrier'] ?? '-')],
        ]);
    }

    private function drawAddresses(): void
    {
        $this->ensureSpace(150);
        $shipping = collect($this->order['addresses'] ?? [])->firstWhere('type', 'shipping');
        $billing = collect($this->order['addresses'] ?? [])->firstWhere('type', 'billing') ?: $shipping;

        $left = self::MARGIN;
        $right = 313;
        $top = $this->y;
        $height = 122;

        $this->box($left, $top - $height, 240, $height);
        $this->box($right, $top - $height, 240, $height);
        $this->text($left + 14, $top - 22, 'Adresse de livraison', 11, 'F2', [0.08, 0.10, 0.08]);
        $this->text($right + 14, $top - 22, 'Adresse de facturation', 11, 'F2', [0.08, 0.10, 0.08]);

        $this->addressLines($left + 14, $top - 42, $shipping);
        $this->addressLines($right + 14, $top - 42, $billing);
        $this->y = $top - $height - 24;
    }

    private function drawItemsTable(): void
    {
        $this->sectionTitle('Articles');

        $columns = $this->type === 'invoice'
            ? [
                ['label' => 'Ref.', 'x' => self::MARGIN + 8, 'w' => 70],
                ['label' => 'Produit', 'x' => self::MARGIN + 84, 'w' => 202],
                ['label' => 'PU TTC', 'x' => self::MARGIN + 300, 'w' => 70, 'align' => 'right'],
                ['label' => 'Qt.', 'x' => self::MARGIN + 384, 'w' => 42, 'align' => 'right'],
                ['label' => 'Total TTC', 'x' => self::MARGIN + 440, 'w' => 70, 'align' => 'right'],
            ]
            : [
                ['label' => 'Ref.', 'x' => self::MARGIN + 8, 'w' => 80],
                ['label' => 'Produit', 'x' => self::MARGIN + 100, 'w' => 300],
                ['label' => 'Qt.', 'x' => self::MARGIN + 420, 'w' => 50, 'align' => 'right'],
            ];

        $this->drawTableHeader($columns);

        foreach (($this->order['items'] ?? []) as $item) {
            $name = (string) data_get($item, 'product.name', 'Produit');
            $nameLines = $this->wrap($name, $this->type === 'invoice' ? 42 : 62);
            $rowHeight = max(34, count($nameLines) * 12 + 16);
            $this->ensureTableSpace($rowHeight, $columns);
            $rowTop = $this->y;

            $this->line(self::MARGIN, $rowTop, self::PAGE_WIDTH - self::MARGIN, $rowTop, [0.90, 0.90, 0.87]);
            $this->text($columns[0]['x'], $rowTop - 20, (string) (data_get($item, 'product.sku') ?: data_get($item, 'product_id') ?: '-'), 8.5, 'F1', [0.25, 0.24, 0.21]);
            $lineY = $rowTop - 20;

            foreach ($nameLines as $line) {
                $this->text($columns[1]['x'], $lineY, $line, 9, 'F2', [0.08, 0.10, 0.08]);
                $lineY -= 12;
            }

            if ($this->type === 'invoice') {
                $this->textRight($columns[2]['x'] + $columns[2]['w'], $rowTop - 20, (string) ($item['formatted_unit_price'] ?? '-'), 8.5, 'F1', [0.25, 0.24, 0.21]);
                $this->textRight($columns[3]['x'] + $columns[3]['w'], $rowTop - 20, (string) ($item['quantity'] ?? 0), 8.5, 'F1', [0.25, 0.24, 0.21]);
                $this->textRight($columns[4]['x'] + $columns[4]['w'], $rowTop - 20, (string) ($item['formatted_line_total'] ?? '-'), 8.5, 'F2', [0.08, 0.10, 0.08]);
            } else {
                $this->textRight($columns[2]['x'] + $columns[2]['w'], $rowTop - 20, (string) ($item['quantity'] ?? 0), 8.5, 'F2', [0.08, 0.10, 0.08]);
            }

            $this->y -= $rowHeight;
        }

        if (empty($this->order['items'])) {
            $this->ensureTableSpace(38, $columns);
            $this->text(self::MARGIN + 8, $this->y - 22, 'Aucun article dans cette commande.', 9, 'F1', [0.33, 0.30, 0.25]);
            $this->y -= 38;
        }

        $this->line(self::MARGIN, $this->y, self::PAGE_WIDTH - self::MARGIN, $this->y, [0.90, 0.90, 0.87]);
        $this->y -= 26;
    }

    private function drawTotalsAndNotes(): void
    {
        $this->ensureSpace($this->type === 'invoice' ? 158 : 132);

        if ($this->type === 'invoice') {
            $x = 340;
            $w = 213;
            $top = $this->y;
            $this->box($x, $top - 116, $w, 116);
            $this->totalLine($x, $top - 24, 'Produits', (string) ($this->order['formatted_subtotal'] ?? '-'));
            $this->totalLine($x, $top - 44, 'Livraison', (string) ($this->order['formatted_shipping'] ?? '-'));
            $this->totalLine($x, $top - 64, 'TVA', (string) ($this->order['formatted_tax'] ?? '-'));
            $this->line($x + 14, $top - 78, $x + $w - 14, $top - 78, [0.87, 0.91, 0.86]);
            $this->totalLine($x, $top - 98, 'Total TTC', (string) ($this->order['formatted_total'] ?? '-'), true);
            $this->drawFooterNote($top - 140);
            $this->y = $top - 158;

            return;
        }

        $tracking = data_get($this->order, 'tracking.number');
        $pickup = data_get($this->order, 'metadata.pickup_point');
        $this->box(self::MARGIN, $this->y - 102, self::PAGE_WIDTH - (self::MARGIN * 2), 102);
        $this->text(self::MARGIN + 14, $this->y - 24, 'Instructions logistiques', 11, 'F2', [0.08, 0.10, 0.08]);
        $this->text(self::MARGIN + 14, $this->y - 45, 'Transporteur: '.(string) ($this->order['carrier'] ?? '-'), 9, 'F1', [0.25, 0.24, 0.21]);
        $this->text(self::MARGIN + 14, $this->y - 62, 'Numero de suivi: '.($tracking ?: '-'), 9, 'F1', [0.25, 0.24, 0.21]);

        if ($pickup) {
            $this->text(self::MARGIN + 14, $this->y - 79, 'Point relais: '.(string) data_get($pickup, 'name', '-').' - '.(string) data_get($pickup, 'address', '-'), 8.5, 'F1', [0.33, 0.30, 0.25]);
        }

        $this->drawFooterNote($this->y - 128);
        $this->y -= 146;
    }

    private function drawFooterNote(float $y): void
    {
        $lines = $this->shop['legal'] ?? [];
        $this->text(self::MARGIN, $y, (string) ($lines[0] ?? 'Document genere par le back-office Denetfils.'), 8.5, 'F1', [0.33, 0.30, 0.25]);
        $this->text(self::MARGIN, $y - 14, (string) ($lines[1] ?? 'Merci de verifier la commande avant expedition.'), 8.5, 'F1', [0.33, 0.30, 0.25]);
    }

    private function sectionTitle(string $title): void
    {
        $this->ensureSpace(42);
        $this->text(self::MARGIN, $this->y, $title, 14, 'F2', [0.08, 0.10, 0.08]);
        $this->rect(self::MARGIN, $this->y - 10, 42, 3, [0.12, 0.39, 0.25]);
        $this->y -= 30;
    }

    private function infoGrid(array $items): void
    {
        $this->ensureSpace(98);
        $x = self::MARGIN;
        $w = self::PAGE_WIDTH - (self::MARGIN * 2);
        $top = $this->y;
        $this->box($x, $top - 84, $w, 84);
        $columnWidth = $w / 3;

        foreach ($items as $index => [$label, $value]) {
            $column = $index % 3;
            $row = intdiv($index, 3);
            $cellX = $x + ($column * $columnWidth) + 14;
            $cellY = $top - 24 - ($row * 40);
            $this->text($cellX, $cellY, $label, 7.5, 'F2', [0.45, 0.42, 0.36]);
            $this->text($cellX, $cellY - 14, $this->shorten((string) $value, 28), 9.5, 'F2', [0.08, 0.10, 0.08]);
        }

        $this->y = $top - 106;
    }

    private function addressLines(float $x, float $y, mixed $address): void
    {
        $lines = array_filter([
            data_get($address, 'recipient_name'),
            data_get($address, 'company'),
            data_get($address, 'street_line_1'),
            data_get($address, 'street_line_2'),
            trim((string) data_get($address, 'postal_code').' '.(string) data_get($address, 'city')),
            data_get($address, 'country_code'),
            data_get($address, 'phone'),
        ]);

        if ($lines === []) {
            $lines = ['Adresse non renseignee'];
        }

        foreach ($lines as $line) {
            $this->text($x, $y, $this->shorten((string) $line, 39), 8.8, 'F1', [0.25, 0.24, 0.21]);
            $y -= 13;
        }
    }

    private function drawTableHeader(array $columns): void
    {
        $this->ensureSpace(42);
        $this->rect(self::MARGIN, $this->y - 26, self::PAGE_WIDTH - (self::MARGIN * 2), 26, [0.94, 0.96, 0.93]);

        foreach ($columns as $column) {
            $this->drawAlignedText($column, $this->y - 17, $column['label'], 8, 'F2', [0.08, 0.10, 0.08]);
        }

        $this->y -= 26;
    }

    private function ensureTableSpace(float $height, array $columns): void
    {
        if ($this->y - $height >= self::FOOTER_TOP) {
            return;
        }

        $this->addPage();
        $this->sectionTitle('Articles');
        $this->drawTableHeader($columns);
    }

    private function ensureSpace(float $height): void
    {
        if ($this->y - $height < self::FOOTER_TOP) {
            $this->addPage();
        }
    }

    private function totalLine(float $x, float $y, string $label, string $value, bool $strong = false): void
    {
        $this->text($x + 14, $y, $label, $strong ? 10.5 : 9, $strong ? 'F2' : 'F1', [0.08, 0.10, 0.08]);
        $this->textRight($x + 198, $y, $value, $strong ? 11 : 9, 'F2', [0.08, 0.10, 0.08]);
    }

    private function drawAlignedText(array $column, float $y, string $value, float $size, string $font, array $color): void
    {
        if (($column['align'] ?? 'left') === 'right') {
            $this->textRight($column['x'] + $column['w'], $y, $value, $size, $font, $color);

            return;
        }

        $this->text($column['x'], $y, $value, $size, $font, $color);
    }

    private function documentReference(): string
    {
        $prefix = $this->type === 'invoice' ? 'FAC' : 'BL';

        return $prefix.'-'.(string) ($this->order['order_number'] ?? $this->order['id'] ?? 'COMMANDE');
    }

    private function date(?string $value): string
    {
        if (! $value) {
            return now()->format('d/m/Y');
        }

        try {
            return Carbon::parse($value)->format('d/m/Y H:i');
        } catch (\Throwable) {
            return Str::of($value)->replace('T', ' ')->before('+')->before('Z')->toString();
        }
    }

    private function labelFromMap(?string $value, array $labels): string
    {
        return $labels[$value] ?? $value ?? '-';
    }

    private function wrap(string $text, int $width): array
    {
        $words = preg_split('/\s+/', trim($text)) ?: [];
        $lines = [];
        $current = '';

        foreach ($words as $word) {
            $candidate = trim($current.' '.$word);

            if (mb_strlen($candidate) > $width && $current !== '') {
                $lines[] = $current;
                $current = $word;
            } else {
                $current = $candidate;
            }
        }

        if ($current !== '') {
            $lines[] = $current;
        }

        return $lines ?: ['-'];
    }

    private function shorten(string $text, int $width): string
    {
        return mb_strlen($text) > $width ? mb_substr($text, 0, $width - 1).'...' : $text;
    }

    private function box(float $x, float $y, float $w, float $h): void
    {
        $this->rect($x, $y, $w, $h, [0.99, 0.99, 0.98]);
        $this->strokeRect($x, $y, $w, $h, [0.87, 0.91, 0.86]);
    }

    private function rect(float $x, float $y, float $w, float $h, array $color): void
    {
        $this->op(sprintf(
            "q %.3F %.3F %.3F rg %.2F %.2F %.2F %.2F re f Q\n",
            $color[0],
            $color[1],
            $color[2],
            $x,
            $y,
            $w,
            $h,
        ));
    }

    private function strokeRect(float $x, float $y, float $w, float $h, array $color): void
    {
        $this->op(sprintf(
            "q %.3F %.3F %.3F RG 0.7 w %.2F %.2F %.2F %.2F re S Q\n",
            $color[0],
            $color[1],
            $color[2],
            $x,
            $y,
            $w,
            $h,
        ));
    }

    private function line(float $x1, float $y1, float $x2, float $y2, array $color): void
    {
        $this->op(sprintf(
            "q %.3F %.3F %.3F RG 0.7 w %.2F %.2F m %.2F %.2F l S Q\n",
            $color[0],
            $color[1],
            $color[2],
            $x1,
            $y1,
            $x2,
            $y2,
        ));
    }

    private function text(float $x, float $y, string $text, float $size = 10, string $font = 'F1', array $color = [0, 0, 0]): void
    {
        $this->op(sprintf(
            "BT %.3F %.3F %.3F rg /%s %.2F Tf %.2F %.2F Td (%s) Tj ET\n",
            $color[0],
            $color[1],
            $color[2],
            $font,
            $size,
            $x,
            $y,
            $this->pdfText($text),
        ));
    }

    private function textRight(float $right, float $y, string $text, float $size = 10, string $font = 'F1', array $color = [0, 0, 0]): void
    {
        $width = mb_strlen($text) * $size * 0.48;
        $this->text($right - $width, $y, $text, $size, $font, $color);
    }

    private function op(string $operation): void
    {
        $this->pages[array_key_last($this->pages)][] = $operation;
    }

    private function buildPdf(): string
    {
        $totalPages = count($this->pages);
        $objects = [];
        $pageRefs = [];
        $nextObject = 5;

        foreach ($this->pages as $index => $operations) {
            $content = implode('', $operations).$this->footerContent($index + 1, $totalPages);
            $contentObject = $nextObject++;
            $pageObject = $nextObject++;
            $pageRefs[] = "{$pageObject} 0 R";
            $objects[$contentObject] = "<< /Length ".strlen($content)." >>\nstream\n{$content}endstream";
            $objects[$pageObject] = "<< /Type /Page /Parent 2 0 R /MediaBox [0 0 ".self::PAGE_WIDTH.' '.self::PAGE_HEIGHT."] /Resources << /Font << /F1 3 0 R /F2 4 0 R >> >> /Contents {$contentObject} 0 R >>";
        }

        $objects[1] = '<< /Type /Catalog /Pages 2 0 R >>';
        $objects[2] = '<< /Type /Pages /Kids ['.implode(' ', $pageRefs).'] /Count '.$totalPages.' >>';
        $objects[3] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>';
        $objects[4] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold >>';
        ksort($objects);

        $pdf = "%PDF-1.4\n";
        $offsets = [0];

        foreach ($objects as $number => $body) {
            $offsets[$number] = strlen($pdf);
            $pdf .= "{$number} 0 obj\n{$body}\nendobj\n";
        }

        $xref = strlen($pdf);
        $maxObject = max(array_keys($objects));
        $pdf .= "xref\n0 ".($maxObject + 1)."\n";
        $pdf .= "0000000000 65535 f \n";

        for ($i = 1; $i <= $maxObject; $i++) {
            if (isset($offsets[$i])) {
                $pdf .= str_pad((string) $offsets[$i], 10, '0', STR_PAD_LEFT)." 00000 n \n";
            } else {
                $pdf .= "0000000000 65535 f \n";
            }
        }

        $pdf .= "trailer\n<< /Size ".($maxObject + 1)." /Root 1 0 R >>\nstartxref\n{$xref}\n%%EOF";

        return $pdf;
    }

    private function footerContent(int $page, int $totalPages): string
    {
        $footer = '';
        $footer .= sprintf("q 0.870 0.910 0.860 RG 0.7 w %.2F %.2F m %.2F %.2F l S Q\n", self::MARGIN, 58, self::PAGE_WIDTH - self::MARGIN, 58);
        $footer .= sprintf(
            "BT 0.250 0.240 0.210 rg /F2 8.00 Tf %.2F %.2F Td (%s) Tj ET\n",
            self::MARGIN,
            40,
            $this->pdfText((string) ($this->shop['brand'] ?? 'DEN & FILS').' - '.(string) ($this->shop['website'] ?? 'denetfils.fr').' - '.(string) ($this->shop['email'] ?? 'support@denetfils.fr')),
        );
        $footer .= sprintf(
            "BT 0.450 0.420 0.360 rg /F1 7.50 Tf %.2F %.2F Td (%s) Tj ET\n",
            self::MARGIN,
            25,
            $this->pdfText($this->documentReference().' - Document valable sans signature manuscrite'),
        );
        $footer .= sprintf(
            "BT 0.250 0.240 0.210 rg /F2 8.00 Tf %.2F %.2F Td (%s) Tj ET\n",
            492,
            40,
            $this->pdfText('Page '.$page.' / '.$totalPages),
        );

        return $footer;
    }

    private function pdfText(string $value): string
    {
        $encoded = iconv('UTF-8', 'Windows-1252//TRANSLIT//IGNORE', $value) ?: $value;

        return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $encoded);
    }
}
