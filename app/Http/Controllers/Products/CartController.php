<?php

namespace App\Http\Controllers\Products;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use Illuminate\Http\Request;
use App\Http\Resources\CartResource;
use App\Traits\CrudOperationsTrait;
use Illuminate\Support\Facades\Auth;
use Exception;

class CartController extends Controller
{
    use CrudOperationsTrait;

    /*
    |----------------------------------------------------------------------
    | Display a listing of the carts for the current user.
    |----------------------------------------------------------------------
    */
    public function index()
    {
        try {
            $user = Auth::user();
            if ($user) {
                $carts = $this->getAllWithRelation(new Cart(), ['user', 'product', 'productDetail'])
                              ->where('user_id', $user->id);
                return CartResource::collection($carts);
            } else {
                return response()->json(['message' => 'You are not logged in'], 401);
            }
        } catch (Exception $e) {
            return response()->json(['error' => 'Failed to fetch carts: ' . $e->getMessage()], 500);
        }
    }

    /*
    |----------------------------------------------------------------------
    | Store a newly created cart item in storage.
    |----------------------------------------------------------------------
    */
    public function store(Request $request)
    {
        try {
            $validationRules = [
                'quantity' => 'required|integer|min:1',
                'product_id' => 'required|exists:products,id',
                'product_detail_id' => 'required|exists:product_details,id',
            ];

            $validationError = $this->validateRequestData($request, $validationRules);
            if ($validationError) return $validationError;

            $userId = Auth::id();
            if ($userId) {
                $data = $request->only(['quantity', 'product_id', 'product_detail_id']);
                $data['user_id'] = $userId;
                $cart = $this->createRecord(new Cart(), $data);

                return new CartResource($cart);
            } else {
                return response()->json(['message' => 'You are not logged in'], 401);
            }
        } catch (Exception $e) {
            return response()->json(['error' => 'Failed to store cart item: ' . $e->getMessage()], 500);
        }
    }

    /*
    |----------------------------------------------------------------------
    | Display the specified cart item.
    |----------------------------------------------------------------------
    */
    public function show($id)
    {
        try {
            $cart = $this->findWithRelation(new Cart(), ['user', 'product'], $id);

            if ($cart->user_id !== Auth::id()) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            return new CartResource($cart);
        } catch (Exception $e) {
            return response()->json(['error' => 'Failed to fetch cart item: ' . $e->getMessage()], 500);
        }
    }

    /*
    |----------------------------------------------------------------------
    | Update the specified cart item in storage.
    |----------------------------------------------------------------------
    */
    public function update(Request $request, $id)
    {
        try {
            $validationRules = [
                'quantity' => 'required|integer|min:1',
                'product_id' => 'required|exists:products,id',
                'product_detail_id' => 'required|exists:product_details,id',
            ];

            $validationError = $this->validateRequestData($request, $validationRules);
            if ($validationError) return $validationError;

            $userId = Auth::id();
            $cart = $this->findWithRelation(new Cart(), ['user', 'product', 'productDetail'], $id);

            if ($cart->user_id !== $userId) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            $data = $request->only(['quantity', 'product_id', 'product_detail_id']);
            $data['user_id'] = $userId;
            $cart = $this->updateRecord(new Cart(), $id, $data);

            return new CartResource($cart);
        } catch (Exception $e) {
            return response()->json(['error' => 'Failed to update cart item: ' . $e->getMessage()], 500);
        }
    }

    /*
    |----------------------------------------------------------------------
    | Remove the specified cart item from storage.
    |----------------------------------------------------------------------
    */
    public function destroy($id)
    {
        try {
            $cart = $this->findWithRelation(new Cart(), ['user', 'product'], $id);

            if ($cart->user_id !== Auth::id()) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            $this->deleteRecord(new Cart(), $id);
            return response()->json(['message' => 'Cart item deleted successfully'], 200);
        } catch (Exception $e) {
            return response()->json(['error' => 'Failed to delete cart item: ' . $e->getMessage()], 500);
        }
    }
}
