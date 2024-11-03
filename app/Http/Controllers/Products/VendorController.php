<?php

namespace App\Http\Controllers\Products;

use App\Http\Controllers\Controller;
use App\Models\Vendor;
use App\Http\Resources\VendorResource;
use App\Traits\HandleFile;
use App\Traits\CrudOperationsTrait;
use Illuminate\Http\Request;
use Exception;

class VendorController extends Controller
{
    use HandleFile, CrudOperationsTrait;

    /*
    |----------------------------------------------------------------------
    | Display a listing of the vendors.
    |----------------------------------------------------------------------
    */
    public function index()
    {
        try {
            $vendors = $this->getAllRecords(new Vendor());
            return VendorResource::collection($vendors);
        } catch (Exception $e) {
            return response()->json(['error' => 'Failed to fetch vendors: ' . $e->getMessage()], 500);
        }
    }

    /*
    |----------------------------------------------------------------------
    | Display the specified vendor.
    |----------------------------------------------------------------------
    */
    public function show($id)
    {
        try {
            $vendor = $this->findById(new Vendor(), $id);
            return new VendorResource($vendor);
        } catch (Exception $e) {
            return response()->json(['error' => 'Failed to fetch vendor: ' . $e->getMessage()], 500);
        }
    }

    /*
    |----------------------------------------------------------------------
    | Store a newly created vendor in storage.
    |----------------------------------------------------------------------
    */
    public function store(Request $request)
    {
        try {
            $validationRules = [
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:vendors,email',
                'password' => 'required|string|min:6',
                'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            ];

            $validationError = $this->validateRequestData($request, $validationRules);
            if ($validationError) {
                return $validationError;
            }

            $data = $request->only(['name', 'email', 'password']);
            $data['password'] = bcrypt($data['password']);

            if ($request->hasFile('image')) {
                $data['image'] = $this->UploadFiles($request->file('image'), null, 'image');
            }

            $vendor = $this->createRecord(new Vendor(), $data);

            return new VendorResource($vendor);
        } catch (Exception $e) {
            return response()->json(['error' => 'Failed to store vendor: ' . $e->getMessage()], 500);
        }
    }

    /*
    |----------------------------------------------------------------------
    | Update the specified vendor in storage.
    |----------------------------------------------------------------------
    */
    public function update(Request $request, $id)
    {
        try {
            $validationRules = [
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:vendors,email,' . $id,
                'password' => 'nullable|string|min:6',
                'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            ];

            $validationError = $this->validateRequestData($request, $validationRules);
            if ($validationError) {
                return $validationError;
            }

            $data = $request->only(['name', 'email']);
            if ($request->has('password')) {
                $data['password'] = bcrypt($request->password);
            }

            $vendor = $this->findById(new Vendor(), $id);

            if ($request->hasFile('image')) {
                $data['image'] = $this->updateFile($request, 'image', $vendor->image, null, 'image');
            }

            $updatedVendor = $this->updateRecord(new Vendor(), $id, $data);

            return new VendorResource($updatedVendor);
        } catch (Exception $e) {
            return response()->json(['error' => 'Failed to update vendor: ' . $e->getMessage()], 500);
        }
    }

    /*
    |----------------------------------------------------------------------
    | Remove the specified vendor from storage.
    |----------------------------------------------------------------------
    */
    public function destroy($id)
    {
        try {
            $this->deleteRecord(new Vendor(), $id, ['image']);
            return response()->json(['message' => 'Vendor deleted successfully.'], 200);
        } catch (Exception $e) {
            return response()->json(['error' => 'Failed to delete vendor: ' . $e->getMessage()], 500);
        }
    }
}
