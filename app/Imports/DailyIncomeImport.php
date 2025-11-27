<?php

namespace App\Imports;

// This class is kept for potential future compatibility but not used in current implementation
// The import functionality now uses direct PhpSpreadsheet implementation in the controller
class DailyIncomeImport
{
    public $errors = [];
    public $successCount = 0;
}