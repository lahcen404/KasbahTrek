<?php

namespace App\Interfaces;

use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Collection;

interface TourRepositoryInterface
{

    public function getAll(array $filters = [], ?int $perPage = null): Collection|Paginator;

    public function findById(int $id);

    public function create(array $data);

    public function update(int $id, array $data);

    public function delete(int $id);

    public function getByGuide(int $guideId);

    public function addImage(int $tourId, string $path);

    public function deleteImage(int $tourId, int $imageId): bool;
}
