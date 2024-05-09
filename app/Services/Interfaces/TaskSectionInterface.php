<?php

namespace App\Services\Interfaces;

/**
 * Interface CategoryServiceInterface
 * @package App\Services\Interfaces
 */
interface TaskSectionInterface
{

    public function store($section, $questions);

    public function show($id);

    public function update($request, $model);

    public function destroy($id);
}
