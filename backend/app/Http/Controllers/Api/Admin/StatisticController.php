<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Interfaces\AdminStatisticRepositoryInterface;

class StatisticController extends Controller
{
    protected $statisticRepository;

    public function __construct(AdminStatisticRepositoryInterface $statisticRepository)
    {
        $this->statisticRepository = $statisticRepository;
    }

    public function index()
    {
        $stats = $this->statisticRepository->getDashboardStats();

        return response()->json([
            'status' => 'success',
            'data' => $stats
        ]);
    }
}
