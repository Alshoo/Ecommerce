<?php

namespace App\Http\Controllers\Products;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use App\Http\Resources\CategoryResource;
use App\Traits\CrudOperationsTrait;
use Exception;

class CategoryController extends Controller
{
    use CrudOperationsTrait;

    /*
    |----------------------------------------------------------------------
    | Display a listing of the categories.
    |----------------------------------------------------------------------
    */
    public function index()
    {
        try {
            $categories = $this->getAllWithRelation(new Category(), ['products']);
            return CategoryResource::collection($categories);
        } catch (Exception $e) {
            return response()->json(['error' => 'Failed to fetch categories: ' . $e->getMessage()], 500);
        }
    }

    /*
    |----------------------------------------------------------------------
    | Store a newly created category in storage.
    |----------------------------------------------------------------------
    */
    public function store(Request $request)
    {
        try {
            $validationRules = [
                'category_name' => 'required|string|max:255|unique:categories',
            ];

            $validationError = $this->validateRequestData($request, $validationRules);
            if ($validationError) return $validationError;

            $data = $request->only(['category_name']);
            $category = $this->createRecord(new Category(), $data);

            return new CategoryResource($category);
        } catch (Exception $e) {
            return response()->json(['error' => 'Failed to store category: ' . $e->getMessage()], 500);
        }
    }

    /*
    |----------------------------------------------------------------------
    | Display the specified category.
    |----------------------------------------------------------------------
    */
    public function show($id)
    {
        try {
            $category = $this->findWithRelation(new Category(), ['products'], $id);
            return new CategoryResource($category);
        } catch (Exception $e) {
            return response()->json(['error' => 'Failed to fetch category: ' . $e->getMessage()], 500);
        }
    }

    /*
    |----------------------------------------------------------------------
    | Update the specified category in storage.
    |----------------------------------------------------------------------
    */
    public function update(Request $request, $id)
    {
        try {
            $validationRules = [
                'category_name' => 'required|string|max:255|unique:categories,category_name,' . $id,
            ];

            $validationError = $this->validateRequestData($request, $validationRules);
            if ($validationError) return $validationError;

            $data = $request->only(['category_name']);
            $category = $this->updateRecord(new Category(), $id, $data);

            return new CategoryResource($category);
        } catch (Exception $e) {
            return response()->json(['error' => 'Failed to update category: ' . $e->getMessage()], 500);
        }
    }

    /*
    |----------------------------------------------------------------------
    | Remove the specified category from storage.
    |----------------------------------------------------------------------
    */
    public function destroy($id)
    {
        try {
            $this->deleteRecord(new Category(), $id);
            return response()->json(['message' => 'Category deleted successfully'], 200);
        } catch (Exception $e) {
            return response()->json(['error' => 'Failed to delete category: ' . $e->getMessage()], 500);
        }
    }
}
