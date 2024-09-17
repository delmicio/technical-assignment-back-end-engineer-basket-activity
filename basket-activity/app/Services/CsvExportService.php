<?php

namespace App\Services;

use Symfony\Component\HttpFoundation\StreamedResponse;

class CsvExportService implements ExportInterface
{
    public function export(array $data): void
    {
        $response = new StreamedResponse(function () use ($data) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, ['User ID', 'Product ID', 'Product Name']);

            foreach ($data as $row) {
                fputcsv($handle, [$row['user_id'], $row['product_id'], $row['name']]);
            }

            fclose($handle);
        });

        // Set headers for CSV download
        $response->headers->set('Content-Type', 'text/csv; charset=UTF-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="removed_items.csv"');

        $response->send();
    }
}
