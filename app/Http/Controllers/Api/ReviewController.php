<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

use App\Models\Review;

class ReviewController extends Controller
{
    public function show($productId)
    {
        $validator = Validator::make(
            ['product_id' => $productId],
            [
                'product_id' => 'required:exists:product_id',
            ],
        );

        if ($validator->fails()) {
            return response()->json(
                [
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ],
                422,
            );
        }


        $review = Review::where('product_id', $productId)->get();
        return response()->json([
            'status' => 'success',
            'message' => 'review fetched successfully',
            'data' => [
                'review' => $review,
            ],
        ]);
    }

    public function createReview(Request $request)
    {
        $user = auth::user();

        if (!$user) {
            return response()->json(
                [
                    'status' => 'error',
                    'message' => 'user not authenticated',
                ],
                403,
            );
            
        }
        $request->validate([
            'product_id' => 'requiredexists:product_id',
            'order_number' => 'required|exists:orders,order_number',
            'heading' => 'required',
            'stars' => 'is_numeric|min:1,max:5',
            'description' => 'required',
        ]);

        $user_id = $user->id;

        $Review = Review::create([
            'product_id' => $request->product_id,
            'order_number' => $request->order_number,
            'heading' => $request->heading,
            'stars' => $request->heading,
            'user_id' => $user_id,
            'description' => $request->description,
        ]);
        $Review->save();

        return response()->json([
            'status' => 'success',
            'message' => 'review saved successfully',
        ]);
    }

    public function delete($id){

        $validator = Validator::make(
            ['id' => $id],
            [
                'id' => 'required|exists:reviews',
            ],
        );
        
        $review = Review::find($id);
        $review->delete();
        return response()->json([
            'status' => 'success',
            'message' => 'review deleted successfully',
        ]);
    }
}
