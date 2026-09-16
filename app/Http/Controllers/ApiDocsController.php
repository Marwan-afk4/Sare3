<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\View\View;

class ApiDocsController extends Controller
{
    public function ui(): View
    {
        abort_unless((bool) config('api-docs.enabled'), 404);

        return view('docs.swagger', [
            'title' => config('api-docs.title'),
            'specUrl' => '/docs/openapi.yaml',
        ]);
    }

    public function spec(): Response
    {
        abort_unless((bool) config('api-docs.enabled'), 404);

        $path = resource_path('openapi/openapi.yaml');

        abort_unless(is_file($path), 404);

        $contents = file_get_contents($path);

        abort_if($contents === false, 404);

        $contents = str_replace('{{API_BASE_URL}}', $this->apiBaseUrl(), $contents);

        return response($contents, 200, [
            'Content-Type' => 'application/yaml; charset=UTF-8',
        ]);
    }

    private function apiBaseUrl(): string
    {
        return rtrim((string) config('app.url'), '/').'/api';
    }
}
