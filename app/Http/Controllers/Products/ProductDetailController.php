<?php

namespace App\Http\Controllers\Products;

use App\Http\Controllers\Controller;
use App\Models\ProductDetails;
use Illuminate\Http\Request;
use App\Http\Resources\ProductDetailResource;
use App\Traits\CrudOperationsTrait;
use App\Traits\HandleFile;
use Exception;

class ProductDetailController extends Controller
{
    use CrudOperationsTrait, HandleFile;

    /*
    |--------------------------------------------------------------------------
    | Display a listing of the product details.
    |--------------------------------------------------------------------------
    */
    public function index()
    {
        try {
            $details = $this->getAllWithRelation(new ProductDetails(), ['product', 'cart', 'purchases']);
            return ProductDetailResource::collection($details);
        } catch (Exception $e) {
            return response()->json(['error' => 'Failed to fetch product details: ' . $e->getMessage()], 500);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Store a newly created product detail in storage.
    |--------------------------------------------------------------------------
    */
    public function store(Request $request)
    {
        try {
            $validationRules = [
                'size' => 'required|string|max:50',
                'color' => 'required|string|max:50',
                'image'=> 'required|image|mimes:jpeg,png,jpg|max:2048',
                'price' => 'required|numeric|min:0',
                'discount' => 'nullable|numeric|min:0',
                'stock' => 'required|integer|min:0',
                'product_id' => 'required|exists:products,id',
            ];

            $validationError = $this->validateRequestData($request, $validationRules);
            if ($validationError) return $validationError;

            $data = $request->only(['size', 'color', 'price', 'discount', 'stock', 'product_id']);

            if ($request->hasFile('image')) {
                $data['image'] = $this->UploadFiles($request->file('image'), null, 'image');
            }

            $detail = $this->createRecord(new ProductDetails(), $data);

            return new ProductDetailResource($detail);
        } catch (Exception $e) {
            return response()->json(['error' => 'Failed to store product detail: ' . $e->getMessage()], 500);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Display the specified product detail.
    |--------------------------------------------------------------------------
    */
    public function show($id)
    {
        try {
            $detail = $this->findWithRelation(new ProductDetails(), ['product', 'cart', 'image', 'purchases'], $id);
            return new ProductDetailResource($detail);
        } catch (Exception $e) {
            return response()->json(['error' => 'Failed to fetch product detail: ' . $e->getMessage()], 500);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Update the specified product detail in storage.
    |--------------------------------------------------------------------------
    */
    public function update(Request $request, $id)
    {
        try {
            $validationRules = [
                'size' => 'required|string|max:50',
                'color' => 'required|string|max:50',
                'price' => 'required|numeric|min:0',
                'image'=> 'required|image|mimes:jpeg,png,jpg|max:2048',
                'discount' => 'nullable|numeric|min:0',
                'stock' => 'required|integer|min:0',
                'product_id' => 'required|exists:products,id',
            ];

            $validationError = $this->validateRequestData($request, $validationRules);
            if ($validationError) return $validationError;

            $data = $request->only(['size', 'color', 'price', 'discount', 'stock', 'product_id']);

            $oldDetails = $this->findById(new ProductDetails(), $id);

            if ($request->hasFile('image')) {
                $data['image'] = $this->updateFile($request, 'image', $oldDetails->image, null, 'image');
            }

            $detail = $this->updateRecord(new ProductDetails(), $id, $data);

            return new ProductDetailResource($detail);
        } catch (Exception $e) {
            return response()->json(['error' => 'Failed to update product detail: ' . $e->getMessage()], 500);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Remove the specified product detail from storage.
    |--------------------------------------------------------------------------
    */
    public function destroy($id)
    {
        try {
            $this->deleteRecord(new ProductDetails(), $id, ['image']);
            return response()->json(['message' => 'Product detail deleted successfully'], 200);
        } catch (Exception $e) {
            return response()->json(['error' => 'Failed to delete product detail: ' . $e->getMessage()], 500);
        }
    }
}
