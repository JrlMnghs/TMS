<?php

namespace App\Services;

use App\Repositories\DatabaseStatsRepository;
use Illuminate\Support\Collection;

class DashboardService
{
    public function __construct(
        private readonly DatabaseStatsRepository $databaseStatsRepository
    ) {
    }
    
    /**
     * Get dashboard statistics.
     */
    public function getDashboardStatistics(): array
    {
        $tableStats = $this->databaseStatsRepository->getTableStatistics();
        
        return [
            'tableStats' => $tableStats->toArray(),
            'summary' => $this->generateSummary($tableStats),
            'health' => $this->assessDatabaseHealth($tableStats)
        ];
    }
    
    /**
     * Generate summary statistics.
     */
    private function generateSummary(Collection $tableStats): array
    {
        $activeTables = $tableStats->where('status', 'active');
        $errorTables = $tableStats->where('status', 'error');
        
        return [
            'total_records' => $tableStats->where('status', 'total')->first()['raw_count'] ?? 0,
            'active_tables' => $activeTables->count(),
            'error_tables' => $errorTables->count(),
            'largest_table' => $this->getLargestTable($activeTables),
            'total_tables' => $tableStats->where('status', '!=', 'total')->count()
        ];
    }
    
    /**
     * Get the largest table by record count.
     */
    private function getLargestTable(Collection $activeTables): ?array
    {
        if ($activeTables->isEmpty()) {
            return null;
        }
        
        $largest = $activeTables->reduce(function ($prev, $current) {
            // Ensure both items have the required keys
            if (!isset($prev['raw_count']) || !isset($current['raw_count'])) {
                return $prev;
            }
            
            return ($prev['raw_count'] > $current['raw_count']) ? $prev : $current;
        });
        
        // Validate that the largest item has all required keys
        if (!$largest || !isset($largest['table']) || !isset($largest['count']) || !isset($largest['raw_count'])) {
            return null;
        }
        
        return [
            'name' => $largest['table'],
            'count' => $largest['count'],
            'raw_count' => $largest['raw_count']
        ];
    }
    
    /**
     * Assess overall database health.
     */
    private function assessDatabaseHealth(Collection $tableStats): array
    {
        $totalTables = $tableStats->where('status', '!=', 'total')->count();
        $activeTables = $tableStats->where('status', 'active')->count();
        $errorTables = $tableStats->where('status', 'error')->count();
        
        $healthPercentage = $totalTables > 0 ? ($activeTables / $totalTables) * 100 : 0;
        
        return [
            'percentage' => round($healthPercentage, 2),
            'status' => $this->getHealthStatus($healthPercentage),
            'active_count' => $activeTables,
            'error_count' => $errorTables,
            'total_count' => $totalTables
        ];
    }
    
    /**
     * Get health status based on percentage.
     */
    private function getHealthStatus(float $percentage): string
    {
        if ($percentage >= 90) {
            return 'excellent';
        }
        
        if ($percentage >= 75) {
            return 'good';
        }
        
        if ($percentage >= 50) {
            return 'fair';
        }
        
        return 'poor';
    }
}
