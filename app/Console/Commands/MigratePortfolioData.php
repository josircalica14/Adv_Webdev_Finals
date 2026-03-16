<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use PDO;

class MigratePortfolioData extends Command
{
    protected $signature = 'portfolio:migrate-data
                            {--source-host=localhost}
                            {--source-db=portfolio_platform}
                            {--source-user=root}
                            {--source-password=}
                            {--dry-run : Show counts without migrating}
                            {--verify : Verify record counts after migration}';

    protected $description = 'Migrate data from the vanilla PHP portfolio database to Laravel';

    private PDO $source;

    public function handle(): int
    {
        $this->info('Connecting to source database…');

        try {
            $this->source = new PDO(
                "mysql:host={$this->option('source-host')};dbname={$this->option('source-db')};charset=utf8mb4",
                $this->option('source-user'),
                $this->option('source-password'),
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
        } catch (\PDOException $e) {
            $this->error("Cannot connect to source: {$e->getMessage()}");
            return 1;
        }

        if ($this->option('dry-run')) {
            $this->dryRun();
            return 0;
        }

        $tables = ['users', 'portfolios', 'portfolio_items', 'files', 'customization_settings'];

        foreach ($tables as $table) {
            $this->info("Migrating {$table}…");
            $this->{"migrate" . str_replace('_', '', ucwords($table, '_'))}();
        }

        $this->info('Migration complete.');

        if ($this->option('verify')) {
            $this->verify($tables);
        }

        return 0;
    }

    private function dryRun(): void
    {
        $this->info('--- DRY RUN ---');
        foreach (['users', 'portfolios', 'portfolio_items', 'files', 'customization_settings'] as $table) {
            $count = $this->source->query("SELECT COUNT(*) FROM {$table}")->fetchColumn();
            $this->line("  {$table}: {$count} records");
        }
    }

    private function migrateUsers(): void
    {
        $rows = $this->source->query("SELECT * FROM users")->fetchAll(PDO::FETCH_ASSOC);
        $bar  = $this->output->createProgressBar(count($rows));

        foreach ($rows as $row) {
            DB::table('users')->updateOrInsert(['id' => $row['id']], [
                'name'                 => $row['full_name'] ?? $row['name'] ?? '',
                'full_name'            => $row['full_name'] ?? $row['name'] ?? '',
                'email'                => $row['email'],
                'username'             => $row['username'] ?? null,
                'bio'                  => $row['bio'] ?? null,
                'program'              => $row['program'] ?? 'BSIT',
                'contact_info'         => $row['contact_info'] ?? null,
                'profile_photo_path'   => isset($row['profile_photo_path']) ? 'portfolio/' . ltrim($row['profile_photo_path'], '/') : null,
                'is_verified'          => $row['is_verified'] ?? 0,
                'is_admin'             => $row['is_admin'] ?? 0,
                'last_username_change' => $row['last_username_change'] ?? null,
                'password'             => $row['password_hash'] ?? $row['password'],  // preserve hash verbatim
                'created_at'           => $row['created_at'] ?? now(),
                'updated_at'           => $row['updated_at'] ?? now(),
            ]);
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
    }

    private function migratePortfolios(): void
    {
        $rows = $this->source->query("SELECT * FROM portfolios")->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows as $row) {
            DB::table('portfolios')->updateOrInsert(['id' => $row['id']], [
                'user_id'    => $row['user_id'],
                'is_public'  => $row['is_public'] ?? 0,
                'view_count' => $row['view_count'] ?? 0,
                'created_at' => $row['created_at'] ?? now(),
                'updated_at' => $row['updated_at'] ?? now(),
            ]);
        }
    }

    private function migratePortfolioitems(): void
    {
        $rows = $this->source->query("SELECT * FROM portfolio_items")->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows as $row) {
            DB::table('portfolio_items')->updateOrInsert(['id' => $row['id']], [
                'portfolio_id'  => $row['portfolio_id'],
                'item_type'     => $row['item_type'],
                'title'         => $row['title'],
                'description'   => $row['description'],
                'item_date'     => $row['item_date'] ?? null,
                'tags'          => $row['tags'] ?? null,
                'links'         => $row['links'] ?? null,
                'is_visible'    => $row['is_visible'] ?? 1,
                'display_order' => $row['display_order'] ?? 0,
                'created_at'    => $row['created_at'] ?? now(),
                'updated_at'    => $row['updated_at'] ?? now(),
            ]);
        }
    }

    private function migrateFiles(): void
    {
        $rows = $this->source->query("SELECT * FROM files")->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows as $row) {
            DB::table('files')->updateOrInsert(['id' => $row['id']], [
                'portfolio_item_id' => $row['portfolio_item_id'],
                'user_id'           => $row['user_id'],
                'original_filename' => $row['original_filename'],
                'stored_filename'   => $row['stored_filename'] ?? basename($row['file_path']),
                'file_path'         => 'uploads/' . basename($row['file_path']),
                'file_type'         => $row['file_type'] ?? $row['mime_type'] ?? 'application/octet-stream',
                'file_size'         => $row['file_size'] ?? 0,
                'thumbnail_path'    => isset($row['thumbnail_path']) ? 'thumbs/' . basename($row['thumbnail_path']) : null,
                'created_at'        => $row['created_at'] ?? now(),
                'updated_at'        => $row['updated_at'] ?? now(),
            ]);
        }
    }

    private function migrateCustomizationsettings(): void
    {
        $rows = $this->source->query("SELECT * FROM customization_settings")->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows as $row) {
            DB::table('customization_settings')->updateOrInsert(['portfolio_id' => $row['portfolio_id']], [
                'theme'         => $row['theme'] ?? 'default',
                'layout'        => $row['layout'] ?? 'grid',
                'primary_color' => $row['primary_color'] ?? '#3498db',
                'accent_color'  => $row['accent_color'] ?? '#e74c3c',
                'heading_font'  => $row['heading_font'] ?? 'Roboto',
                'body_font'     => $row['body_font'] ?? 'Open Sans',
                'created_at'    => $row['created_at'] ?? now(),
                'updated_at'    => $row['updated_at'] ?? now(),
            ]);
        }
    }

    private function verify(array $tables): void
    {
        $this->info('--- VERIFICATION ---');
        $allMatch = true;

        foreach ($tables as $table) {
            $srcCount  = (int) $this->source->query("SELECT COUNT(*) FROM {$table}")->fetchColumn();
            $destCount = (int) DB::table($table)->count();
            $match     = $srcCount === $destCount;
            $allMatch  = $allMatch && $match;
            $this->line(sprintf('  %-30s source: %d  dest: %d  %s', $table, $srcCount, $destCount, $match ? '✓' : '✗ MISMATCH'));
        }

        $allMatch ? $this->info('All counts match.') : $this->error('Some counts do not match.');
    }
}
