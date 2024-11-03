<?php

namespace App\Http\Controllers\Products;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductDetails;
use App\Http\Resources\ProductResource;
use Illuminate\Http\Request;
use App\Traits\CrudOperationsTrait;
use App\Traits\HandleFile;
use Exception;

class ProductController extends Controller
{
    use CrudOperationsTrait, HandleFile;

    /*
    |--------------------------------------------------------------------------
    | Display a listing of the products.
    |--------------------------------------------------------------------------
    */
public function index(Request $request)
{
    try {
        $query = Product::with(['vendor', 'categories', 'details', 'comments', 'favorites']);

        if ($request->has('search') && !empty($request->search)) {
            $query->where('product_name', 'like', '%' . $request->search . '%')
                ->orWhere('description', 'like', '%' . $request->search . '%');
        }

        if ($request->has('category_id') && !empty($request->category_id)) {
            $query->whereHas('categories', function($q) use ($request) {
                $q->where('category_id', $request->category_id);
            });
        }

        if ($request->has('brand') && !empty($request->brand)) {
            $query->where('brand', $request->brand);
        }

        $products = $query->get();

        return ProductResource::collection($products);
    } catch (Exception $e) {
        return response()->json(['error' => 'Failed to retrieve products', 'message' => $e->getMessage()], 500);
    }
}

    /*
    |--------------------------------------------------------------------------
    | Store a newly created product in storage.
    |--------------------------------------------------------------------------
    */
    public function store(Request $request)
    {
        try {
            $validationRules = [
                'product_name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'brand' =>  'nullable|string',
                'vendor_id' => 'required|exists:vendors,id',
                'details' => 'required|array', 
                'details.*.size' => 'required|string|max:50',
                'details.*.color' => 'required|string|max:50',
                'details.*.price' => 'required|numeric|min:0',
                'details.*.discount' => 'nullable|numeric|min:0',
                'details.*.stock' => 'required|integer|min:0',
                'details.*.image' => 'required',
            ];

            $validationError = $this->validateRequestData($request, $validationRules);
            if ($validationError) return $validationError;

            $productData = $request->only(['product_name', 'description', 'vendor_id', 'brand']);
            $product = $this->createRecord(new Product(), $productData);

            foreach ($request->details as $detail) {
                $detailData = array_merge($detail, ['product_id' => $product->id]);

                if (isset($detail['image'])) {
                    $detailData['image'] = $this->UploadFiles($detail['image'], null, 'image');
                }

                $this->createRecord(new ProductDetails(), $detailData);
            }

            $product->load(['details', 'vendor', 'categories']);

            return response()->json([
                'data' => new ProductResource($product)
            ], 201);
        } catch (Exception $e) {
            return response()->json(['error' => 'Failed to create product', 'message' => $e->getMessage()], 500);
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Display the specified product.                                                             
    |--------------------------------------------------------------------------
    */
    public function show($id)
    {
        try {
            $product = $this->findWithRelation(new Product(), ['vendor', 'categories', 'details', 'comments', 'favorites'], $id);
            return new ProductResource($product);
        } catch (Exception $e) {
            return response()->json(['error' => 'Failed to retrieve product', 'message' => $e->getMessage()], 404);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Display a limited listing of products (limit 30).
    |--------------------------------------------------------------------------
    */
    public function limitedProducts()
    {
        try {
            $products = Product::with(['vendor', 'categories', 'details', 'comments', 'favorites'])
                                ->paginate(30);

            return response()->json([
                'data' => ProductResource::collection($products),
                'meta' => [
                    'current_page' => $products->currentPage(),
                    'last_page' => $products->lastPage(),
                    'per_page' => $products->perPage(),
                    'total' => $products->total(),
                    'next_page_url' => $products->nextPageUrl(),
                    'prev_page_url' => $products->previousPageUrl(),
                ]
            ]);
        } catch (Exception $e) {
            return response()->json(['error' => 'Failed to retrieve limited products', 'message' => $e->getMessage()], 500);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Get related products by category or brand.
    |--------------------------------------------------------------------------
    */
    public function relatedProducts($id)
    {
        try {
            $product = Product::findOrFail($id);

            $relatedProducts = Product::with(['vendor', 'categories', 'details', 'comments', 'favorites'])
                ->where(function($query) use ($product) {
                    $query->where('brand', $product->brand)
                        ->orWhereHas('categories', function($q) use ($product) {
                            $q->whereIn('categories.id', $product->categories->pluck('id'));
                        });
                })
                ->where('id', '!=', $product->id)  
                ->limit(7)  
                ->get();

            return ProductResource::collection($relatedProducts);
        } catch (Exception $e) {
            return response()->json(['error' => 'Failed to retrieve related products', 'message' => $e->getMessage()], 500);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Update the specified product in storage.
    |--------------------------------------------------------------------------
    */
    public function update(Request $request, $id)
    {
        try {
            $validationRules = [
                'product_name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'brand' => 'nullable|string',
                'vendor_id' => 'required|exists:vendors,id',
                'details' => 'nullable|array', 
                'details.*.id' => 'nullable|exists:product_details,id', 
                'details.*.size' => 'required|string|max:50',
                'details.*.color' => 'required|string|max:50',
                'details.*.price' => 'required|numeric|min:0',
                'details.*.discount' => 'nullable|numeric|min:0',
                'details.*.stock' => 'required|integer|min:0',
                'details.*.image' => 'required',
            ];

            $validationError = $this->validateRequestData($request, $validationRules);
            if ($validationError) return $validationError;

            $productData = $request->only(['product_name', 'description', 'vendor_id']);
            $product = $this->updateRecord(new Product(), $id, $productData);

            if ($request->has('details')) {
                foreach ($request->details as $detail) {
                    if (isset($detail['id']) && $detail['id']) {
                        $detailData = array_merge($detail, ['product_id' => $product->id]);
                        if (isset($detail['image'])) {
                            $oldDetail = ProductDetails::find($detail['id']);
                            $detailData['image'] = $this->updateFile($request, 'image', $oldDetail->image ?? null, null, 'image');
                        }
                        $this->updateRecord(new ProductDetails(), $detail['id'], $detailData);
                    } else {
                        $detailData = array_merge($detail, ['product_id' => $product->id]);
                        if (isset($detail['image'])) {
                            $detailData['image'] = $this->UploadFiles($detail['image'], null, 'image');
                        }
                        $this->createRecord(new ProductDetails(), $detailData);
                    }
                }
            }

            $product->load(['details', 'vendor', 'categories']);

            return response()->json([
                'data' => new ProductResource($product)
            ]);
        } catch (Exception $e) {
            return response()->json(['error' => 'Failed to update product', 'message' => $e->getMessage()], 500);
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Remove the specified product from storage.
    |--------------------------------------------------------------------------
    */
    public function destroy($id)
    {
        try {
            $product = Product::with('details')->findOrFail($id);

            foreach ($product->details as $detail) {
                $this->deleteFile($detail->image); 
            }

            $this->deleteRecord(new Product(), $id);
            return response()->json(['message' => 'Product deleted successfully'], 200);
        } catch (Exception $e) {
            return response()->json(['error' => 'Failed to delete product', 'message' => $e->getMessage()], 500);
        }
    }
}
