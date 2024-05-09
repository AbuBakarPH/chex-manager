<?php

namespace App\Http\Controllers\AdminControllers;

use App\Http\Controllers\Controller;
// use App\Models\Admin\CheckList;
use App\Services\CategoryService;
use App\Services\Interfaces\CategoryServiceInterface;
use Illuminate\Http\Request;
use App\Models\Admin\Category;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Http\Requests\CategoryRequest;
use App\Http\Resources\CategoriesWithChecklistsResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CategoryController extends Controller
{

    public function __construct(private CategoryServiceInterface $service)
    {
    }

    public function index(Request $request)
    {
        return $this->response('Category listing', $this->service->index($request), 200);
    }

    public function store(CategoryRequest $request)
    {
        return $this->response(
            'Category created successfully',
            $this->service->store($request),
            200
        );
    }

    public function show($id)
    {
        $category = Category::with('sub_categories', 'photo', 'documents')->where('id', $id)->first();
        return $this->response('Category detail', $category, 200);
    }

    public function update(CategoryRequest $request, $id)
    {
        $category = Category::where('id', $id)->first();
        return $this->response('Category updated successfully', $this->service->update($request, $category), 200);
    }

    public function destroy(Category $category)
    {
        // $category->delete();
        $this->service->destroy($category);
        return response()->noContent();
    }

    public function getFilteredCategoriesWithChecklists(Request $request)
    {
        // $categories = Category::select('id', 'name')->with(['photo', 'sub_categories'])->whereNull('parent_id')->get();
        $categories = Category::select('id', 'name')->whereNull('parent_id')->get();

        // $items = collect($categories)->map(function ($category) {    
        //     foreach ($category->sub_categories as $sub_category) {
        //         if(count($sub_category->checklists) > 0) {
        //             return $category ;
        //         }
        //     }
        // });

        return $this->response('Category listing', $categories, 200);
    }
}
