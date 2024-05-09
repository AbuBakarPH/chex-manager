<?php

namespace App\Services\Interfaces;

/**
 * Interface FormFieldServiceInterface
 * @package App\Services\Interfaces
 */
interface DashboardInterface
{
    public function getStats();
    public function getRiskStats();
    public function getCurrentLevelRiskStats($request);
    public function getRisksData($request);
}
