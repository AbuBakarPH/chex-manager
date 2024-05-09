<?php

namespace App\Services\Interfaces;

/**
 * Interface CategoryServiceInterface
 * @package App\Services\Interfaces
 */
interface SectionQuestionInterface
{

    public function store($request);

    public function show($id);

    public function update($request, $model);

    public function destroy($id);
}