<?php

namespace App\Http\Controllers\Products;

use App\Http\Controllers\Controller;
use App\Models\ProductComment;
use Illuminate\Http\Request;
use App\Http\Resources\ProductCommentResource;
use App\Traits\CrudOperationsTrait;
use Exception;

class ProductCommentController extends Controller
{
    use CrudOperationsTrait;

    /*
    |----------------------------------------------------------------------
    | Display a listing of the product comments.
    |----------------------------------------------------------------------
    */
    public function index()
    {
        try {
            $comments = $this->getAllWithRelation(new ProductComment(), ['user', 'product']);
            return ProductCommentResource::collection($comments);
        } catch (Exception $e) {
            return response()->json(['error' => 'Failed to fetch product comments: ' . $e->getMessage()], 500);
        }
    }

    /*
    |----------------------------------------------------------------------
    | Store a newly created product comment in storage.
    |----------------------------------------------------------------------
    */
    public function store(Request $request)
    {
        try {
            $validationRules = [
                'comment' => 'required|string|max:500',
                'rating' => 'required|integer|min:1|max:5',
                'product_id' => 'required|exists:products,id',
            ];

            $validationError = $this->validateRequestData($request, $validationRules);
            if ($validationError) return $validationError;

            $data = $request->only(['comment', 'rating', 'product_id']);
            $data['user_id'] = auth()->id();
            $comment = $this->createRecord(new ProductComment(), $data);

            return new ProductCommentResource($comment);
        } catch (Exception $e) {
            return response()->json(['error' => 'Failed to store product comment: ' . $e->getMessage()], 500);
        }
    }

    /*
    |----------------------------------------------------------------------
    | Display the specified product comment.
    |----------------------------------------------------------------------
    */
    public function show($id)
    {
        try {
            $comment = $this->findWithRelation(new ProductComment(), ['user', 'product'], $id);
            return new ProductCommentResource($comment);
        } catch (Exception $e) {
            return response()->json(['error' => 'Failed to fetch product comment: ' . $e->getMessage()], 500);
        }
    }

    /*
    |----------------------------------------------------------------------
    | Update the specified product comment in storage.
    |----------------------------------------------------------------------
    */
    public function update(Request $request, $id)
    {
        try {
            $validationRules = [
                'comment' => 'required|string|max:500',
                'rating' => 'required|integer|min:1|max:5',
                'product_id' => 'required|exists:products,id',
            ];

            $validationError = $this->validateRequestData($request, $validationRules);
            if ($validationError) return $validationError;

            $data = $request->only(['comment', 'rating', 'product_id']);
            $data['user_id']  = auth()->id();
            $comment = $this->updateRecord(new ProductComment(), $id, $data);

            return new ProductCommentResource($comment);
        } catch (Exception $e) {
            return response()->json(['error' => 'Failed to update product comment: ' . $e->getMessage()], 500);
        }
    }

    /*
    |----------------------------------------------------------------------
    | Remove the specified product comment from storage.
    |----------------------------------------------------------------------
    */
    public function destroy($id)
    {
        try {
            $this->deleteRecord(new ProductComment(), $id);
            return response()->json(['message' => 'Product comment deleted successfully'], 200);
        } catch (Exception $e) {
            return response()->json(['error' => 'Failed to delete product comment: ' . $e->getMessage()], 500);
        }
    }
}
