<?php

namespace App\Interfaces;

interface TripReportRepositoryInterface
{
    public function create(array $data);
    public function getAll();
    public function updateStatus(int $id, string $status);
}
