<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\PdfExportService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PortfolioApiController extends Controller
{
    public function __construct(private PdfExportService $pdfExport) {}

    public function export(Request $request): Response
    {
        $itemIds = $request->input('item_ids', []);
        return $this->pdfExport->generate($request->user(), $itemIds);
    }
}
