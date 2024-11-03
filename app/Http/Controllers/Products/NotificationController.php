<?php

namespace App\Http\Controllers\Products;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;
use App\Http\Resources\NotificationResource;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Exception;

class NotificationController extends Controller
{
    /*
    |----------------------------------------------------------------------
    | Display a listing of the notifications for the current user.
    |----------------------------------------------------------------------
    */
    public function index()
    {
        try {
            $userId = Auth::id();
            $notifications = Notification::where('is_global', true)
                ->orWhereHas('users', function ($query) use ($userId) {
                    $query->where('user_id', $userId);
                })->get();

            return NotificationResource::collection($notifications);
        } catch (Exception $e) {
            return response()->json(['error' => 'Failed to fetch notifications: ' . $e->getMessage()], 500);
        }
    }

    /*
    |----------------------------------------------------------------------
    | Store a newly created notification in storage.
    |----------------------------------------------------------------------
    */
    public function store(Request $request)
    {
        try {
            $validationRules = [
                'title' => 'required|string|max:255',
                'message' => 'required|string',
                'type' => 'required|string|max:50',
                'is_global' => 'required|boolean',
            ];

            $validatedData = $request->validate($validationRules);
            $notification = Notification::create($validatedData);

            if ($validatedData['is_global']) {
                $users = User::all();
                foreach ($users as $user) {
                    $user->notifications()->attach($notification->id, ['read_at' => false]);
                }
            } else {
                Auth::user()->notifications()->attach($notification->id, ['read_at' => false]);
            }

            return new NotificationResource($notification);
        } catch (Exception $e) {
            return response()->json(['error' => 'Failed to store notification: ' . $e->getMessage()], 500);
        }
    }

    /*
    |----------------------------------------------------------------------
    | Display the specified notification.
    |----------------------------------------------------------------------
    */
    public function show($id)
    {
        try {
            $notification = Notification::findOrFail($id);

            if (!Auth::user()->notifications()->where('notification_id', $id)->exists() && !$notification->is_global) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            return new NotificationResource($notification);
        } catch (Exception $e) {
            return response()->json(['error' => 'Failed to fetch notification: ' . $e->getMessage()], 500);
        }
    }

    /*
    |----------------------------------------------------------------------
    | Update the specified notification in storage.
    |----------------------------------------------------------------------
    */
    public function update(Request $request, $id)
    {
        try {
            $notification = Notification::findOrFail($id);

            if (!Auth::user()->notifications()->where('notification_id', $id)->exists() && !$notification->is_global) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            $validationRules = [
                'title' => 'sometimes|required|string|max:255',
                'message' => 'sometimes|required|string',
                'type' => 'sometimes|required|string|max:50',
                'is_global' => 'sometimes|required|boolean',
            ];

            $validatedData = $request->validate($validationRules);
            $notification->update($validatedData);

            return new NotificationResource($notification);
        } catch (Exception $e) {
            return response()->json(['error' => 'Failed to update notification: ' . $e->getMessage()], 500);
        }
    }

    /*
    |----------------------------------------------------------------------
    | Remove the specified notification from storage.
    |----------------------------------------------------------------------
    */
    public function destroy($id)
    {
        try {
            $notification = Notification::findOrFail($id);

            if ($notification->is_global) {
                return response()->json(['error' => 'Cannot delete a global notification'], 403);
            }

            if (!Auth::user()->notifications()->where('notification_id', $id)->exists()) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            Auth::user()->notifications()->detach($notification->id);

            return response()->json(['message' => 'Notification deleted successfully'], 200);
        } catch (Exception $e) {
            return response()->json(['error' => 'Failed to delete notification: ' . $e->getMessage()], 500);
        }
    }

    /*
    |----------------------------------------------------------------------
    | Clear all notifications for the current user.
    |----------------------------------------------------------------------
    */
    public function clearAll()
    {
        try {
            $userId = Auth::id();

            if (!$userId) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            Auth::user()->notifications()->detach();

            return response()->json(['message' => 'All notifications deleted successfully'], 200);
        } catch (Exception $e) {
            return response()->json(['error' => 'Failed to clear notifications: ' . $e->getMessage()], 500);
        }
    }
}
