<?php

namespace App\Services;

use App\Models\CustomizationSettings;
use App\Models\User;
use Spatie\Browsershot\Browsershot;
use Illuminate\Http\Response;

class PdfExportService
{
    public function generate(User $user, array $itemIds = []): Response
    {
        $portfolio     = $user->portfolio()->with(['items.files', 'customization'])->firstOrFail();
        $customization = $portfolio->customization
            ?? new CustomizationSettings(CustomizationSettings::$defaults);

        $items = $portfolio->items()
            ->where('is_visible', true)
            ->when(!empty($itemIds), fn($q) => $q->whereIn('id', $itemIds))
            ->with('files')
            ->get();

        $grouped = $items->groupBy('item_type')->map(fn($g) => $g->toArray())->toArray();

        // Render the blade view to HTML
        $html = view('pdf.portfolio', compact('user', 'portfolio', 'customization', 'grouped'))->render();

        // Inject Google Fonts inline so Puppeteer can load them
        $html = $this->injectBaseUrl($html);

        try {
            $chromePath = env('BROWSERSHOT_CHROME_PATH');

            $shot = Browsershot::html($html)
                ->noSandbox()
                ->waitUntilNetworkIdle()
                ->showBackground()
                ->format('A4')
                ->margins(0, 0, 0, 0);

            if ($chromePath) {
                $shot->setChromePath($chromePath);
            }

            $pdf = $shot->pdf();

            return response($pdf, 200, [
                'Content-Type'        => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="portfolio.pdf"',
            ]);
        } catch (\Throwable $e) {
            // Fallback to DomPDF if Puppeteer/Browsershot fails
            \Illuminate\Support\Facades\Log::warning('Browsershot failed, falling back to DomPDF', ['error' => $e->getMessage()]);

            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.portfolio', compact('user', 'portfolio', 'customization', 'grouped'))
                ->setPaper('a4', 'portrait');

            return response($pdf->output(), 200, [
                'Content-Type'        => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="portfolio.pdf"',
            ]);
        }
    }

    private function injectBaseUrl(string $html): string
    {
        $baseUrl = config('app.url');
        // Add base tag so relative URLs resolve correctly
        return str_replace('<head>', '<head><base href="' . $baseUrl . '/">', $html);
    }
}
