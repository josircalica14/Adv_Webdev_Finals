<?php

namespace App\Services;

use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;

class PdfExportService
{
    public function generate(User $user, array $itemIds = []): \Barryvdh\DomPDF\PDF
    {
        $portfolio     = $user->portfolio()->with(['items.files', 'customization'])->firstOrFail();
        $customization = $portfolio->customization;

        $items = $portfolio->items()
            ->where('is_visible', true)
            ->when(!empty($itemIds), fn($q) => $q->whereIn('id', $itemIds))
            ->with('files')
            ->get();

        // Group by type
        $grouped = $items->groupBy('item_type')->toArray();

        $data = compact('user', 'portfolio', 'customization', 'grouped');

        return Pdf::loadView('pdf.portfolio', $data)
            ->setPaper('a4', 'portrait');
    }
}
