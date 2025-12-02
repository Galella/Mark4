<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class ImportProgressService
{
    private $cacheKeyPrefix = 'import_progress_';

    /**
     * Store import progress for a specific job
     * 
     * @param string $jobId Unique identifier for the import job
     * @param array $progressData Progress data including total, completed, errors, etc.
     */
    public function setProgress(string $jobId, array $progressData): void
    {
        $cacheKey = $this->cacheKeyPrefix . $jobId;
        $progressData['updated_at'] = now()->toISOString();
        Cache::put($cacheKey, $progressData, now()->addMinutes(30)); // Store for 30 minutes
    }

    /**
     * Get import progress for a specific job
     * 
     * @param string $jobId Unique identifier for the import job
     * @return array Progress data or default values if not found
     */
    public function getProgress(string $jobId): array
    {
        $cacheKey = $this->cacheKeyPrefix . $jobId;
        $progress = Cache::get($cacheKey);
        
        return $progress ?: [
            'total_rows' => 0,
            'processed_rows' => 0,
            'successful_imports' => 0,
            'failed_imports' => 0,
            'status' => 'pending',
            'message' => 'Waiting to start...',
            'updated_at' => null
        ];
    }

    /**
     * Check if progress data exists for a job
     * 
     * @param string $jobId Unique identifier for the import job
     * @return bool True if progress data exists, false otherwise
     */
    public function hasProgress(string $jobId): bool
    {
        $cacheKey = $this->cacheKeyPrefix . $jobId;
        return Cache::has($cacheKey);
    }

    /**
     * Clear progress data for a specific job
     *
     * @param string $jobId Unique identifier for the import job
     */
    public function clearProgress(string $jobId): void
    {
        $cacheKey = $this->cacheKeyPrefix . $jobId;
        Cache::forget($cacheKey);
    }

    /**
     * Generate a unique job ID for import
     * 
     * @return string Unique job ID
     */
    public function generateJobId(): string
    {
        return 'import_' . uniqid() . '_' . time();
    }
}