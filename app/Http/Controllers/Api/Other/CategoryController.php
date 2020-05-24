<?php

namespace App\Http\Controllers\Api\Other;

use App\Category;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\CategoryCollection;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return CategoryCollection
     */
    public function index()
    {
        return new CategoryCollection(Category::paginate());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return CategoryResource
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'          => 'required|string|max:191|unique:categories',
            'description'   => 'required|string',
        ]);

        $request->request->add(['slug' => Str::slug($request->name)]);
        $category = Category::create($request->all());
        return (new CategoryResource($category))->additional(['message' => 'Created Successfully!']);
    }

    /**
     * Display the specified resource.
     *
     * @param Category $category
     * @return CategoryResource
     */
    public function show(Category $category)
    {
        return new CategoryResource($category);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param Category $category
     * @return CategoryResource
     */
    public function update(Request $request, Category $category)
    {
        $request->validate([
            'name'          => 'string|max:191|unique:categories',
            'description'   => 'string',
            'status'        => 'boolean',
        ]);

        if ($request->has('name'))
        {
            $request->request->add(['slug' => Str::slug($request->name)]);
        }

        $category->update($request->all());
        return (new CategoryResource($category))->additional(['message' => 'Updated Successfully!']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Category $category
     * @return void
     * @throws Exception
     */
    public function destroy(Category $category)
    {
        $category->delete();
        return response()->json('', 204);
    }
}
