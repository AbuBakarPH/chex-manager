<?php

namespace App\Services;

use App\Models\Admin\Category;
use App\Models\Admin\Media;
use App\Services\Interfaces\CategoryServiceInterface;
use App\Services\GeneralService;
use Illuminate\Support\Facades\Notification;
use App\Notifications\GeneralNotification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Class CategoryService
 * @package App\Services
 */
class CategoryService implements CategoryServiceInterface
{
    public function __construct(private Category $model, private MeidaService $meidaService, private GeneralService $generalService)
    {
        // parent::__construct($model);
        $this->generalService = $generalService;
    }
    
     public function index($request)
    {
    
        $data = Category::with('sub_categories','photo','sub_categories.photo');
        if ($request['category_id']) {
            $data = $data->where('parent_id', $request['category_id']); 
        } else {
            $data = $data->whereNull('parent_id');
        }
        
        $data = $this->generalService->handleSearch($request['searchText'], $data, ['name']);
        if ($request['perpage'] == 'All') {
            return $this->generalService->handleAllData($request, $data);
        } else {
            return $this->generalService->handlePagination($request, $data);
        }
    }

    public function store($request)
    {
        $category = $request->validated();
        $category['slug'] = Str::slug($category['name'], '-');
        $category = Category::create($category);
        $request->merge(['docs_ids' => array_merge($request->docs_ids, [$request->image_id])]);
        $this->meidaService->storeMultiple($request->docs_ids, $category->id, "App\\Models\\Admin\\Category");
        return $category;
    }


    public function update($request, $model)
    {
        $category = $request->validated();
        if ($request->icon && $request->hasFile('icon')) {
            Storage::disk('public')->delete('category-icon/' . $category->icon);
        }

        if ($request->hasFile('icon')) {
            $iconPath = $request->file('icon')->store('public/category-icon');
            $category->icon = basename($iconPath);
        }
        
        $model->update($category);
        $this->meidaService->update($request['image_id'], $model->id, "App\\Models\\Admin\\Category" );
        // $request->merge(['docs_ids' => array_merge($request->docs_ids, [$request->image_id])]);
        // $this->meidaService->updateMultiple($request->docs_ids, $model->id, "App\\Models\\Admin\\Category");
        // Notification::send(auth()->user(), new GeneralNotification($model, 'Category', 'Update', "Category Updated Successfully"));
        return $model->refresh();
    }

    public function destroy($model)
    {
        if (!$model) {
            return $this->error('Category not found', 404);
        }
        // Notification::send(auth()->user(), new GeneralNotification($model, 'Category', 'Delete', "Category Delete Successfully"));
        $subcategories = Category::where('parent_id', $model->id)->get();
        foreach ($subcategories as $subcategory) {
            $subcategory->delete();
        }
        $model->delete();
    }
}
