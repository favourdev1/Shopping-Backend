<?php

// app/Http/Controllers/ProductController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use App\Models\Category;
use Illuminate\Support\Facades\DB;


class ProductController extends Controller
{
    public function index()
    {
        // Retrieve all products
        $products = Product::join('categories', 'products.category_id', '=', 'categories.id')
            ->select('products.*', 'categories.category_name as category')
            // ->paginate(10); 
            ->get();
        return response()->json([
            'status' => 'success',
            'data' => ['products' => $products],
        ]);
    }



    public function show($productId)
    {
        try {
            // Find the product by ID
            // Find the product by ID
            $product = DB::table('products')
                ->join('categories', 'products.category_id', '=', 'categories.id')
                ->select('products.*', 'categories.category_name as category')
                ->where('products.id', $productId)
                ->first();

            if (!$product) {
                return response()->json([
                    'status' => 'error',
                    'data' => [],
                ]);
            }

            return response()->json([
                'status' => 'success',
                'data' => $product,
            ]);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Product not found',
            ], 404);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        }
    }



    public function store(Request $request)
    {
        try {
            // Validate the request
            $validator = Validator::make($request->all(), [
                'product_name' => 'required|string|max:255',
                'category_id' => 'required|exists:categories,id',
                'description' => 'required|string',
                'regular_price' => 'required|numeric|min:0',
                'brand' => 'required|string|max:255',
                'product_img1' => 'required|string',
                'product_img2' => 'required|string',
                'product_img3' => 'required|string',
                'product_img4' => 'required|string',
                'product_img5' => 'nullable|string',
                'weight' => 'numeric|min:0',
                'quantity_in_stock' => 'required|integer|min:0',
                'tags' => 'nullable|string',
                'refundable' => 'in:true,false',
                'status' => 'required|in:active,inactive',
                'sales_price' => 'required|numeric|min:0',
                'meta_title' => 'required|string|max:255',
                'meta_description' => 'required|string',

                'sku' => 'string|max:20',
                'cash_on_delivery' => 'in:true,false',
                'free_shipping' => 'in:true,false',
                'shipping_cost' => 'numeric|min:0',
                'tax' => 'min:0'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => implode(", ", $validator->errors()->all())
                ], 422);

            }

            // Create a new product
            Product::create($request->all());

            return response()->json([
                'status' => 'success',
                'message' => 'Product added successfully'
            ], 200);
        } catch (QueryException $e) {
            return response()->json(
                [
                    'status' => 'error',
                    'message',
                    'Error creating product: ' . $e->getMessage()
                ]
            );
        }
    }


    public function update(Request $request, $id)
    {
        try {
            // Validate the request
            $validator = Validator::make($request->all(), [
                'product_name' => 'required|string|max:255',
                'category_id' => 'required|exists:categories,id',
                'description' => 'required|string',
                'regular_price' => 'required|numeric|min:0',
                'brand' => 'required|string|max:255',
                'product_img1' => 'required|string',
                'product_img2' => 'required|string',
                'product_img3' => 'required|string',
                'product_img4' => 'required|string',
                'product_img5' => 'nullable|string',
                'weight' => 'numeric|min:0',
                'quantity_in_stock' => 'required|integer|min:0',
                'tags' => 'nullable|string',
                'refundable' => 'in:true,false',
                'status' => 'required|in:active,inactive',
                'sales_price' => 'required|numeric|min:0',
                'meta_title' => 'required|string|max:255',
                'meta_description' => 'required|string',

                'sku' => 'string|max:20',
                'cash_on_delivery' => 'in:true,false',
                'free_shipping' => 'in:true,false',
                'shipping_cost' => 'numeric|min:0',
                'tax' => 'min:0'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => implode($validator->errors()->all()),
                    'errors' => implode($validator->errors()->all()),
                ], 422);

            }

            // Update the product
            $product = Product::findOrFail($id);
            $product->update($request->all());

            return response()->json([
                'status' => 'success',
                'message' => 'Product updated successfully'
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'product does not exist ',
                'errors' => $e->getMessage(),
            ], 404);
        } catch (QueryException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error updating product ',
                'errors' => $e->getMessage(),
            ], 404);
        }
    }


    public function destroy($productId)
    {
        try {
            // Find the product by ID
            $product = Product::where('id', $productId)->firstOrFail();

            // Delete the product
            $product->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Product deleted successfully',
            ]);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Product not found',
            ], 404);
        }
    }


    // for uploading image 
    public function upload(Request $request)
    {
        try {
            $request->validate([
                'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);

            if ($request->file('image')->isValid()) {
                $image = $request->file('image');
                $imageName = time() . '.' . $image->extension();
                $image->move(public_path('storage/product_img'), $imageName);

                return response()->json([
                    'data' => ['image_url' => url('storage/product_img/' . $imageName)],
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


    // TODO fix bug with serarch method






// Search with filters
    public function search(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'query' => 'required|string|min:3',
                'category' => 'sometimes|exists:categories,id',
                'free_shipping' => 'sometimes|boolean',
                'cash_on_delivery' => 'sometimes|boolean',
                'min_price' => 'sometimes|numeric|min:0',
                'max_price' => 'sometimes|numeric|min:' . ($request->input('min_price') ?? 0),
            ]);
    
            $validator->validate();
    
            $query = $request->input('query');
    
            $results = Product::where(function ($queryBuilder) use ($query) {
                $queryBuilder->where('name', 'like', "%$query%")
                             ->orWhere('description', 'like', "%$query%");
            });
    
            if ($request->has('category')) {
                $category = $request->input('category');
                $results->where('category_id', $category);
            }
    
            if ($request->has('free_shipping')) {
                $freeShipping = $request->input('free_shipping');
                $results->where('free_shipping', $freeShipping);
            }
    
            if ($request->has('cash_on_delivery')) {
                $cashOnDelivery = $request->input('cash_on_delivery');
                $results->where('cash_on_delivery', $cashOnDelivery);
            }
    
            if ($request->has('min_price') && $request->has('max_price')) {
                $minPrice = $request->input('min_price');
                $maxPrice = $request->input('max_price');
                $results->whereBetween('sales_price', [$minPrice, $maxPrice]);
            }
    
            $results = $results->get();
    
            return response()->json([
                'status' => 'success',
                'message' => 'Search results retrieved successfully',
                'data' => $results,
            ]);
    
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        }
    }
    


    public function similarProduct($productId)
    {
        // Get the details of the provided product
        $product = Product::find($productId);

        if (!$product) {
            return response()->json([
                'status' => 'error',
                'message' => 'Product Does not exist ',

            ], 422);
        }

        // Find similar products based on name, category, and tags
        $similarProducts = Product::where('category_id', $product->category_id)
            ->orWhere(function ($query) use ($product) {
                // Search for products with similar names or tags
                $query->where('product_name', 'like', '%' . $product->product_name . '%')
                    ->orWhere('tags', 'like', '%' . $product->tags . '%');
            })
            ->where('id', '!=', $productId) // Exclude the current product
            ->take(10) // Adjust the number of similar products you want to display
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => ['similarProducts'=>$similarProducts],
        ]);
    }

}