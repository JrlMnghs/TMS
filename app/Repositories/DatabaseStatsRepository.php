<?php

namespace App\Repositories;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class DatabaseStatsRepository
{
    /**
     * Get all table statistics.
     */
    public function getTableStatistics(): Collection
    {
        $tableDefinitions = $this->getTableDefinitions();
        $stats = collect();
        
        foreach ($tableDefinitions as $table => $definition) {
            $stats->push($this->getTableStat($table, $definition));
        }
        
        $this->addTotalStatistic($stats);
        
        return $stats;
    }
    
    /**
     * Get table definitions with display names.
     */
    private function getTableDefinitions(): array
    {
        return [
            'users' => [
                'display_name' => 'Users',
                'description' => 'System users and authentication'
            ],
            'translation_keys' => [
                'display_name' => 'Translation Keys',
                'description' => 'Translation key definitions'
            ],
            'translations' => [
                'display_name' => 'Translations',
                'description' => 'Translation values per locale'
            ],
            'locales' => [
                'display_name' => 'Locales',
                'description' => 'Supported languages'
            ],
            'tags' => [
                'display_name' => 'Tags',
                'description' => 'Translation categorization tags'
            ],
            'translation_key_tags' => [
                'display_name' => 'Key-Tag Relations',
                'description' => 'Many-to-many relationships'
            ],
            'personal_access_tokens' => [
                'display_name' => 'API Tokens',
                'description' => 'User authentication tokens'
            ],
            'failed_jobs' => [
                'display_name' => 'Failed Jobs',
                'description' => 'Queue job failures'
            ],
        ];
    }
    
    /**
     * Get statistics for a single table.
     */
    private function getTableStat(string $table, array $definition): array
    {
        try {
            $count = $this->getTableRecordCount($table);
            
            return [
                'table' => $definition['display_name'],
                'table_name' => $table,
                'count' => $this->formatNumber($count),
                'raw_count' => $count,
                'status' => 'active',
                'description' => $definition['description']
            ];
        } catch (\Exception $e) {
            return [
                'table' => $definition['display_name'],
                'table_name' => $table,
                'count' => 'N/A',
                'raw_count' => 0,
                'status' => 'error',
                'description' => $definition['description'],
                'error_message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Get record count for a specific table.
     */
    private function getTableRecordCount(string $table): int
    {
        return DB::table($table)->count();
    }
    
    /**
     * Format number with commas for display.
     */
    private function formatNumber(int $number): string
    {
        return number_format($number);
    }
    
    /**
     * Add total statistics to the collection.
     */
    private function addTotalStatistic(Collection $stats): void
    {
        $totalCount = $stats->where('status', 'active')->sum('raw_count');
        
        $stats->push([
            'table' => 'Total Records',
            'table_name' => 'total',
            'count' => $this->formatNumber($totalCount),
            'raw_count' => $totalCount,
            'status' => 'total',
            'description' => 'Sum of all table records'
        ]);
    }
}
