<?php

namespace App\Http\Controllers\Api;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Response;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::all();

        return response()->json([
            'data' => ['categories' => $categories],
            'message' => 'Categories retrieved successfully',
            'status' => 'success'
        ], 200);
    }

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'category_name' => 'required|string|max:255',
                'description' => 'required|string',
                'slug' => 'required|string|unique:categories',
                'category_image' => 'nullable|string',
                'status' => 'required|in:active,inactive',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'data' => null,
                    'message' => 'Validation error',
                    'status' => 'error',
                    'errors' => $validator->errors()
                ], 400);
            }

            Category::create($request->all());

            return response()->json([
                'data' => null,
                'message' => 'Category created successfully',
                'status' => 'success'
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'data' => null,
                'message' => 'Error creating category: ' . $e->getMessage(),
                'status' => 'error'
            ], 500);
        }
    }

    public function show($id)
    {
        $category = Category::findOrFail($id);

        return response()->json([
            'data' => ['category' => $category],
            'message' => 'Category retrieved successfully',
            'status' => 'success'
        ], 200);
    }


    public function upload(Request $request)
    {
        try {
            $request->validate([
                'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);

            if ($request->file('image')->isValid()) {
                $image = $request->file('image');
                $imageName = time() . '.' . $image->extension();
                $image->move(public_path('category_img'), $imageName);

                return response()->json([
                    'data' => ['image_url' => url('category_img/' . $imageName)],
                    'message' => 'Image uploaded successfully',
                    'status' => 'success'
                ], 200);
            } else {
                return response()->json([
                    'data' => null,
                    'message' => 'Invalid image file',
                    'status' => 'error'
                ], 400);
            }
        } catch (\Exception $e) {
            return response()->json([
                'data' => null,
                'message' => 'Error uploading image: ' . $e->getMessage(),
                'status' => 'error'
            ], 500);
        }
    }
    public function update($id, Request $request)
    {
        try {
            $request->validate([
                'category_name' => 'required|string|max:255',
                'description' => 'required|string',
                'slug' => 'required|string|unique:categories,slug,' . $id,
                'category_image' => 'nullable|string',
                'status' => 'required|in:active,inactive',
            ]);
    
            $category = Category::findOrFail($id);
            $category->update($request->post());
    
            return response()->json([
                'data' => null,
                'message' => 'Category updated successfully',
                'status' => 'success'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'data' => null,
                'message' => 'Error updating category: ' . $e->getMessage(),
                'status' => 'error'
            ], 500);
        }
    }
    

    public function destroy($id)
    {
        try {
            $category = Category::findOrFail($id);
            $category->delete();

            return response()->json([
                'data' => null,
                'message' => 'Category deleted successfully',
                'status' => 'success'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'data' => null,
                'message' => 'Error deleting category: ' . $e->getMessage(),
                'status' => 'error'
            ], 500);
        }
    }
}
