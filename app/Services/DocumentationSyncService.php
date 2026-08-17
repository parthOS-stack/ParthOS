<?php

namespace App\Services;

use App\Http\Controllers\AdminAuthController;
use App\Services\ForgotPasswordService;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

class DocumentationSyncService
{
    public function sync(): array
    {
        $pagesPath = base_path('docs/PAGES.md');
        $flowsPath = base_path('docs/FLOWS.md');
        $readmePath = base_path('README.md');

        $pagesUpdated = $this->syncPages($pagesPath);
        $flowsUpdated = $this->syncFlows($flowsPath);
        $readmeUpdated = $this->syncReadmeTimestamp($readmePath);

        return [
            'pages' => $pagesUpdated,
            'flows' => $flowsUpdated,
            'readme' => $readmeUpdated,
            'synced_at' => now()->toIso8601String(),
        ];
    }

    private function syncPages(string $path): bool
    {
        if (!is_file($path)) {
            return false;
        }

        $content = file_get_contents($path);
        $generated = $this->buildPagesSection();

        $updated = $this->replaceBetweenMarkers(
            $content,
            '<!-- docs:pages:start -->',
            '<!-- docs:pages:end -->',
            $generated
        );

        file_put_contents($path, $updated);

        return true;
    }

    private function syncFlows(string $path): bool
    {
        if (!is_file($path)) {
            return false;
        }

        $content = file_get_contents($path);

        $lockout = implode("\n", [
            '- Reset password prompt after: **' . AdminAuthController::RESET_PROMPT_AFTER . '** failed login attempts',
            '- Account lock after: **' . AdminAuthController::LOCK_AFTER . '** failed login attempts',
            '- Lock duration: **' . AdminAuthController::LOCK_HOURS . ' hours**',
            '- Successful login or password reset clears lock and attempt counter',
        ]);

        $otp = implode("\n", [
            '- OTP expiry: **' . ForgotPasswordService::OTP_EXPIRY_MINUTES . ' minutes**',
            '- Max wrong OTP tries: **' . ForgotPasswordService::MAX_OTP_ATTEMPTS . '**',
            '- Post-verify session: **' . ForgotPasswordService::VERIFY_SESSION_MINUTES . ' minutes**',
            '- OTP stored **hashed** in `password_reset_otps` (not plain text)',
        ]);

        $content = $this->replaceBetweenMarkers(
            $content,
            '<!-- docs:lockout:start -->',
            '<!-- docs:lockout:end -->',
            $lockout
        );

        $content = $this->replaceBetweenMarkers(
            $content,
            '<!-- docs:otp:start -->',
            '<!-- docs:otp:end -->',
            $otp
        );

        $content = $this->replaceBetweenMarkers(
            $content,
            '<!-- docs:sync-time:start -->',
            '<!-- docs:sync-time:end -->',
            'Last auto-sync: **' . now()->format('M j, Y g:i A T') . '**'
        );

        file_put_contents($path, $content);

        return true;
    }

    private function syncReadmeTimestamp(string $path): bool
    {
        if (!is_file($path)) {
            return false;
        }

        $content = file_get_contents($path);
        $stamp = 'Docs last synced: **' . now()->format('M j, Y g:i A T') . '** — run `php artisan docs:sync` after route or flow changes.';

        $updated = $this->replaceBetweenMarkers(
            $content,
            '<!-- docs:sync-time:start -->',
            '<!-- docs:sync-time:end -->',
            $stamp
        );

        file_put_contents($path, $updated);

        return true;
    }

    private function buildPagesSection(): string
    {
        $routes = collect(Route::getRoutes())
            ->filter(function ($route) {
                $uri = $route->uri();

                if (str_starts_with($uri, 'storage/')) {
                    return false;
                }

                if ($uri === '' || $uri === '/') {
                    return false;
                }

                return !str_starts_with($uri, '_')
                    && $uri !== 'up'
                    && !str_starts_with($uri, 'sanctum');
            })
            ->sortBy(fn ($route) => $route->uri())
            ->values();

        $rows = [];

        foreach ($routes as $route) {
            $methods = collect($route->methods())
                ->reject(fn ($method) => $method === 'HEAD')
                ->implode(', ');

            $uri = '/' . ltrim($route->uri(), '/');
            $name = $route->getName() ?? '—';
            $auth = $this->routeRequiresAdminAuth($route) ? 'Yes' : 'No';
            $meta = $this->resolveRouteMeta($name, $uri);

            $rows[] = [
                'module' => $meta['module'],
                'methods' => $methods,
                'uri' => $uri,
                'name' => $name,
                'page' => $meta['page'],
                'auth' => $auth,
                'status' => $meta['status'],
                'description' => $meta['description'],
            ];
        }

        $grouped = collect($rows)->groupBy('module')->sortKeys();

        $lines = [
            '### Route index (auto-generated)',
            '',
            '| Module | Methods | URI | Route name | Page | Auth | Status | Description |',
            '|--------|---------|-----|------------|------|------|--------|-------------|',
        ];

        foreach ($grouped as $module => $moduleRows) {
            foreach ($moduleRows as $row) {
                $lines[] = sprintf(
                    '| %s | %s | `%s` | `%s` | %s | %s | %s | %s |',
                    $this->escapeCell($row['module']),
                    $this->escapeCell($row['methods']),
                    $this->escapeCell($row['uri']),
                    $this->escapeCell($row['name']),
                    $this->escapeCell($row['page']),
                    $row['auth'],
                    $this->escapeCell($row['status']),
                    $this->escapeCell($row['description'])
                );
            }
        }

        $undocumented = collect($rows)
            ->filter(fn ($row) => str_contains($row['description'], 'Add description in'))
            ->pluck('name')
            ->filter(fn ($name) => $name !== '—')
            ->unique()
            ->values();

        if ($undocumented->isNotEmpty()) {
            $lines[] = '';
            $lines[] = '#### Undocumented routes';
            $lines[] = '';
            $lines[] = 'Add entries to `config/docs.php` for: `' . $undocumented->implode('`, `') . '`.';
        }

        return implode("\n", $lines);
    }

    /**
     * @return array{module: string, page: string, description: string, status: string}
     */
    private function resolveRouteMeta(string $name, string $uri): array
    {
        $configured = config('docs.routes', []);
        $fallbacks = config('docs.uri_fallbacks', []);

        if ($name !== '—' && isset($configured[$name])) {
            return [
                'module' => $configured[$name]['module'] ?? 'Other',
                'page' => $configured[$name]['page'] ?? Str::title(str_replace('.', ' ', $name)),
                'description' => $configured[$name]['description'] ?? '—',
                'status' => $configured[$name]['status'] ?? 'active',
            ];
        }

        foreach ($fallbacks as $prefix => $meta) {
            if ($uri === $prefix || str_starts_with($uri, rtrim($prefix, '/') . '/')) {
                return [
                    'module' => $meta['module'] ?? 'Other',
                    'page' => $meta['page'] ?? '—',
                    'description' => $meta['description'] ?? '—',
                    'status' => $meta['status'] ?? 'active',
                ];
            }
        }

        return [
            'module' => 'Other',
            'page' => '—',
            'description' => 'Add description in `config/docs.php` (route: `' . ($name !== '—' ? $name : $uri) . '`).',
            'status' => 'new',
        ];
    }

    private function routeRequiresAdminAuth($route): bool
    {
        $middleware = collect($route->gatherMiddleware());

        return $middleware->contains('admin.auth')
            || $middleware->contains(\App\Http\Middleware\AdminAuth::class);
    }

    private function escapeCell(string $value): string
    {
        return str_replace('|', '\\|', $value);
    }

    private function replaceBetweenMarkers(string $content, string $start, string $end, string $replacement): string
    {
        $pattern = '/' . preg_quote($start, '/') . '.*?' . preg_quote($end, '/') . '/s';

        if (!preg_match($pattern, $content)) {
            return $content;
        }

        return preg_replace(
            $pattern,
            $start . "\n" . trim($replacement) . "\n" . $end,
            $content,
            1
        );
    }
}
