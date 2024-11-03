<?php

namespace App\Http\Controllers\Products;

use App\Http\Controllers\Controller;
use App\Models\Favorite;
use Illuminate\Http\Request;
use App\Http\Resources\FavoriteResource;
use App\Traits\CrudOperationsTrait;
use Illuminate\Support\Facades\Auth;
use Exception;

class FavoriteController extends Controller
{
    use CrudOperationsTrait;

    /*
    |----------------------------------------------------------------------
    | Display a listing of the favorites for the current user.
    |----------------------------------------------------------------------
    */
    public function index()
    {
        try {
            $user = Auth::user();

            if ($user) {
                $favorites = $this->getAllWithRelation(new Favorite(), ['user', 'product', 'productDetail'])
                    ->where('user_id', $user->id);

                return FavoriteResource::collection($favorites);
            } else {
                return response()->json(['message' => 'Unauthorized'], 403);
            }
        } catch (Exception $e) {
            return response()->json(['error' => 'Failed to fetch favorites: ' . $e->getMessage()], 500);
        }
    }

    /*
    |----------------------------------------------------------------------
    | Store a newly created favorite in storage.
    |----------------------------------------------------------------------
    */
    public function store(Request $request)
    {
        try {
            $validationRules = [
                'product_id' => 'required|exists:products,id',
                'product_detail_id' => 'required|exists:product_details,id',
            ];

            $validationError = $this->validateRequestData($request, $validationRules);
            if ($validationError) return $validationError;

            $userId = Auth::id();
            if ($userId) {
                $data = $request->only(['product_id', 'product_detail_id']);
                $data['user_id'] = $userId;
                $favorite = $this->createRecord(new Favorite(), $data);

                return new FavoriteResource($favorite);
            } else {
                return response()->json(['message' => 'Unauthorized'], 403);
            }
        } catch (Exception $e) {
            return response()->json(['error' => 'Failed to store favorite: ' . $e->getMessage()], 500);
        }
    }

    /*
    |----------------------------------------------------------------------
    | Display the specified favorite.
    |----------------------------------------------------------------------
    */
    public function show($id)
    {
        try {
            $favorite = $this->findWithRelation(new Favorite(), ['user', 'product', 'productDetail'], $id);

            if ($favorite->user_id !== Auth::id()) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            return new FavoriteResource($favorite);
        } catch (Exception $e) {
            return response()->json(['error' => 'Failed to fetch favorite: ' . $e->getMessage()], 500);
        }
    }

    /*
    |----------------------------------------------------------------------
    | Remove the specified favorite from storage.
    |----------------------------------------------------------------------
    */
    public function destroy($id)
    {
        try {
            $favorite = $this->findWithRelation(new Favorite(), ['user', 'product', 'productDetail'], $id);

            if ($favorite->user_id !== Auth::id()) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            $this->deleteRecord(new Favorite(), $id);
            return response()->json(['message' => 'Favorite deleted successfully'], 200);
        } catch (Exception $e) {
            return response()->json(['error' => 'Failed to delete favorite: ' . $e->getMessage()], 500);
        }
    }
}
