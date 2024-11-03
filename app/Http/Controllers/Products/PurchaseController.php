<?php

namespace App\Http\Controllers\Products;

use App\Http\Controllers\Controller;
use App\Models\Purchase;
use Illuminate\Http\Request;
use App\Http\Resources\PurchaseResource;
use App\Traits\CrudOperationsTrait;
use Exception;

class PurchaseController extends Controller
{
    use CrudOperationsTrait;

    /*
    |----------------------------------------------------------------------
    | Display a listing of the purchases.
    |----------------------------------------------------------------------
    */
    public function index()
    {
        try {
            $purchases = $this->getAllWithRelation(new Purchase(), ['user', 'productDetails']);
            return PurchaseResource::collection($purchases);
        } catch (Exception $e) {
            return response()->json(['error' => 'Failed to fetch purchases: ' . $e->getMessage()], 500);
        }
    }

    /*
    |----------------------------------------------------------------------
    | Store a newly created purchase in storage.
    |----------------------------------------------------------------------
    */
    public function store(Request $request)
    {
        try {
            $validationRules = [
                'quantity' => 'required|integer|min:1',
                'total_price' => 'required|numeric|min:0',
                'purchase_date' => 'nullable|date',
                'user_id' => 'required|exists:users,id',
                'product_detail_id' => 'required|exists:product_details,id',
            ];

            $validationError = $this->validateRequestData($request, $validationRules);
            if ($validationError) return $validationError;

            $data = $request->only(['quantity', 'total_price', 'purchase_date', 'user_id', 'product_detail_id']);
            $purchase = $this->createRecord(new Purchase(), $data);

            return new PurchaseResource($purchase);
        } catch (Exception $e) {
            return response()->json(['error' => 'Failed to store purchase: ' . $e->getMessage()], 500);
        }
    }

    /*
    |----------------------------------------------------------------------
    | Display the specified purchase.
    |----------------------------------------------------------------------
    */
    public function show($id)
    {
        try {
            $purchase = $this->findWithRelation(new Purchase(), ['user', 'productDetails'], $id);
            return new PurchaseResource($purchase);
        } catch (Exception $e) {
            return response()->json(['error' => 'Failed to fetch purchase: ' . $e->getMessage()], 500);
        }
    }

    /*
    |----------------------------------------------------------------------
    | Update the specified purchase in storage.
    |----------------------------------------------------------------------
    */
    public function update(Request $request, $id)
    {
        try {
            $validationRules = [
                'quantity' => 'required|integer|min:1',
                'total_price' => 'required|numeric|min:0',
                'purchase_date' => 'nullable|date',
                'user_id' => 'required|exists:users,id',
                'product_detail_id' => 'required|exists:product_details,id',
            ];

            $validationError = $this->validateRequestData($request, $validationRules);
            if ($validationError) return $validationError;

            $data = $request->only(['quantity', 'total_price', 'purchase_date', 'user_id', 'product_detail_id']);
            $purchase = $this->updateRecord(new Purchase(), $id, $data);

            return new PurchaseResource($purchase);
        } catch (Exception $e) {
            return response()->json(['error' => 'Failed to update purchase: ' . $e->getMessage()], 500);
        }
    }

    /*
    |----------------------------------------------------------------------
    | Remove the specified purchase from storage.
    |----------------------------------------------------------------------
    */
    public function destroy($id)
    {
        try {
            $this->deleteRecord(new Purchase(), $id);
            return response()->json(['message' => 'Purchase deleted successfully'], 200);
        } catch (Exception $e) {
            return response()->json(['error' => 'Failed to delete purchase: ' . $e->getMessage()], 500);
        }
    }
}
