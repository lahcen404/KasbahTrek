<?php

namespace App\Interfaces;

interface BookingRepositoryInterface
{
    public function create(array $data);
    public function getByTraveler(int $travelerId);
    public function getByGuide(int $guideId);
    public function updateStatus(int $id, string $status);
}
