<?php

namespace App\Interfaces;

interface AdminStatisticRepositoryInterface
{
    public function getDashboardStats(): array;
}
