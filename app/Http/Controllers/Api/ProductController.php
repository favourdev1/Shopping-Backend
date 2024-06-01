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
            ->get()
            ->take(10);
        return response()->json([
            'status' => 'success',
            'data' => ['products' => $products],
        ]);
    }

    public function getDiscountProduct()
    {
        try {
            $products = Product::join('categories', 'products.category_id', '=', 'categories.id')->where('regular_price', '!=', '0')->orWhere('regular_price', '!=', '')->select('products.*', 'categories.category_name as category')->get()->take(10);

            return response()->json(
                [
                    'status' => 'success',
                    'data' => ['products' => $products],
                ],
                200,
            );
        } catch (\Exception $e) {
            return response()->json(
                [
                    'status' => 'error1',
                    'mesasage' => 'Unable to fetch resource. ' . $e->getMessage(),
                ],
                422,
            );
        }
    }
    public function show($productId)
    {
        try {
            // Find the product by ID
            // Find the product by ID
            $product = DB::table('products')->join('categories', 'products.category_id', '=', 'categories.id')->select('products.*', 'categories.category_name as category')->where('products.id', $productId)->first();

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
            return response()->json(
                [
                    'status' => 'error',
                    'message' => 'Product not found',
                ],
                404,
            );
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(
                [
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $e->errors(),
                ],
                422,
            );
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
                'tax' => 'min:0',
            ]);

            if ($validator->fails()) {
                return response()->json(
                    [
                        'status' => 'error',
                        'message' => implode(', ', $validator->errors()->all()),
                    ],
                    422,
                );
            }

            // Create a new product
            Product::create($request->all());

            return response()->json(
                [
                    'status' => 'success',
                    'message' => 'Product added successfully',
                ],
                200,
            );
        } catch (QueryException $e) {
            return response()->json([
                'status' => 'error',
                'message',
                'Error creating product: ' . $e->getMessage(),
            ]);
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
                'tax' => 'min:0',
            ]);

            if ($validator->fails()) {
                return response()->json(
                    [
                        'status' => 'error',
                        'message' => implode($validator->errors()->all()),
                        'errors' => implode($validator->errors()->all()),
                    ],
                    422,
                );
            }

            // Update the product
            $product = Product::findOrFail($id);
            $product->update($request->all());

            return response()->json(
                [
                    'status' => 'success',
                    'message' => 'Product updated successfully',
                ],
                200,
            );
        } catch (ModelNotFoundException $e) {
            return response()->json(
                [
                    'status' => 'error',
                    'message' => 'product does not exist ',
                    'errors' => $e->getMessage(),
                ],
                404,
            );
        } catch (QueryException $e) {
            return response()->json(
                [
                    'status' => 'error',
                    'message' => 'Error updating product ',
                    'errors' => $e->getMessage(),
                ],
                404,
            );
        }
    }

    public function destroy($productId)
    {
        try {
            // Find the product by ID
            $product = Product::where('id', $productId)->firstOrFail();

            // Delete the produc
            $product->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Product deleted successfully',
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json(
                [
                    'status' => 'error',
                    'message' => 'Product not found',
                ],
                404,
            );
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

                return response()->json(
                    [
                        'data' => ['image_url' => url('storage/product_img/' . $imageName)],
                        'message' => 'Image uploaded successfully',
                        'status' => 'success',
                    ],
                    200,
                );
            } else {
                return response()->json(
                    [
                        'data' => null,
                        'message' => 'Invalid image file',
                        'status' => 'error',
                    ],
                    400,
                );
            }
        } catch (\Exception $e) {
            return response()->json(
                [
                    'data' => null,
                    'message' => 'Error uploading image: ' . $e->getMessage(),
                    'status' => 'error',
                ],
                500,
            );
        }
    }

    // TODO fix bug with serarch method

    private function fetchCategories($categoryName)
    {
        $categories = Category::where('category_name', 'like', '%' . $categoryName . '%');
        if (!$categories->count()) {
            return false;
        }

        return $categories->get(['id', 'category_name']);
    }

    // Search with filters
    public function search(Request $request)
    {
        $categoryId = null;

        $price_max = 0;
        $price_min = 0;
        $brands = [];
        $categories = [];

        $validator = Validator::make($request->all(), [
            'query' => 'sometimes|required|string|min:3',
            'group' => 'sometimes|required|string|min:3',
            'category' => 'sometimes',
            'free_shipping' => 'sometimes|boolean',
            'cash_on_delivery' => 'sometimes|in:true,false',
            'min_price' => 'sometimes|numeric|min:0',
            'max_price' => 'sometimes|numeric|min:' . ($request->input('min_price') ?? 0),
            'brands' => [
                'sometimes',
                'string',
                function ($attribute, $value, $fail) {
                    $brandsValidate = explode(',', $value);

                    // Add additional validation for each brand if necessary
                    foreach ($brandsValidate as $brand) {
                        // Check if each brand meets your validation criteria
                        if (!ctype_alnum(str_replace(['_', ' ', '-'], '', html_entity_decode($brand)))) {
                            $fail($attribute . ' contains invalid characters.');
                        }
                    }
                },
            ],
        ]);
        try {
            $validator->validate();

            if (!$request->has('group') || empty($request->group)) {
                if (empty(trim($request->input('query')))) {
                    $results = Product::inRandomOrder()->join('categories', 'products.category_id', '=', 'categories.id')->select('products.*', 'categories.category_name as category');
                    $results1 = Product::inRandomOrder()->join('categories', 'products.category_id', '=', 'categories.id')->select('products.*', 'categories.category_name as category');
                } else {
                    $query = $request->input('query');

                    $results = Product::where(function ($queryBuilder) use ($query) {
                        $queryBuilder
                            ->where('products.product_name', 'like', "%$query%")
                            ->orWhere('products.tags', 'like', "%$query%")
                            ->orWhere('products.brand', 'like', "%$query%")
                            ->orWhere('products.description', 'like', "%$query%");
                    })
                        ->join('categories', 'products.category_id', '=', 'categories.id')
                        ->select('products.*', 'categories.category_name as category');

                    $results1 = Product::where(function ($queryBuilder) use ($query) {
                        $queryBuilder
                            ->where('products.product_name', 'like', "%$query%")
                            ->orWhere('products.tags', 'like', "%$query%")
                            ->orWhere('products.brand', 'like', "%$query%")
                            ->orWhere('products.description', 'like', "%$query%");
                    })
                        ->join('categories', 'products.category_id', '=', 'categories.id')
                        ->select('products.*', 'categories.category_name as category');
                }
            } else {
                if ($request->group == 'new_arrivals') {
                    $results = $this->getNewArrivalsAutomatically();
                    $results1 = $this->getNewArrivalsAutomatically();
                } else {
                    if ($request->group == 'top-deals') {
                        $results = $this->getTopDeals();
                        // return $results;
                        $results1 = $this->getTopDeals();
                    }
                }
            }

            if ($request->has('category')) {
                $categories = $this->fetchCategories($request->input('category'));
                if ($categories) {
                    $categoryId = $categories->first()['id'];
                    $results->where('category_id', $categoryId);
                }
            }

            if ($request->has('brands')) {
                $brandsearch = explode(',', $request->query('brands'));

                $results->where(function ($query) use ($brandsearch) {
                    foreach ($brandsearch as $brand) {
                        $query->orWhere('products.brand', 'like', "%$brand%");
                    }
                });
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
            // }

            if ($request->has('group') && $request->group == 'top-deals') {
                $resultVal = $results;
                $totalresult = count($results);
            } else {
                $resultVal = $results->get();
                $totalresult = count($results->get());
            }

            if ($request->has('group') && $request->group == 'top-deals') {
                if ($totalresult > 0) {
                    $price_min = $resultVal->min('sales_price');
                    $price_max = $resultVal->max('sales_price');
                    // Extract unique brands from the filtered products
                    $brands = $resultVal
                        ->pluck('brand')
                        ->unique()
                        ->map(function ($brand) {
                            return ['brand' => $brand];
                        })
                        ->values();
                    $categories = $resultVal
                        ->pluck('category_id', 'category')
                        ->unique()
                        ->map(function ($categoryId, $categoryName) {
                            return [
                                'category_id' => $categoryId,
                                'category_name' => $categoryName,
                            ];
                        })
                        ->values();
                }
            } else {
                if ($totalresult > 0) {
                    $price_min = $this->getPrice($results)->min('sales_price');
                    $price_max = $this->getPrice($results)->max('sales_price');

                    $brands = $this->fetchProductBrands($results1);
                    $categories = $this->getProductCategories($results1);
                }
            }
            return response()->json([
                'status' => 'success',
                'message' => 'Search results retrieved successfully',
                'data' => [
                    'products' => $resultVal,
                    'brands' => $brands,
                    'price_min' => round($price_min),
                    'price_max' => round($price_max),
                    'products_found' => $totalresult,
                    'categories' => $categories,
                ],
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(
                [
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $e->errors(),
                ],
                422,
            );
        }
    }

    private function fetchProductBrands($result)
    {
        return $result->select('brand')->distinct()->get();
    }

    private function getPrice($products)
    {
        return $products->select('products.id', 'products.sales_price')->orderBy('products.id', 'asc')->get();
    }

    private function getProductCategories($products)
    {
        return $products->select('products.category_id', 'categories.category_name')->distinct()->get();
    }

    public function similarProduct($productId)
    {
        // Get the details of the provided product
        $product = Product::find($productId);

        if (!$product) {
            return response()->json(
                [
                    'status' => 'error',
                    'message' => 'Product Does not exist ',
                ],
                422,
            );
        }

        // Find similar products based on name, category, and tags
        $similarProducts = Product::where('category_id', $product->category_id)
            ->orWhere(function ($query) use ($product) {
                // Search for products with similar names or tags
                $query->where('product_name', 'like', '%' . $product->product_name . '%')->orWhere('tags', 'like', '%' . $product->tags . '%');
            })
            ->where('id', '!=', $productId) // Exclude the current product
            ->take(10) // Adjust the number of similar products you want to display
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => ['similarProducts' => $similarProducts],
        ]);
    }

    public function getNewArrivalsAutomatically()
    {
        $newArrivals = Product::join('categories', 'products.category_id', '=', 'categories.id')->select('products.*', 'categories.category_name as category')->orderBy('products.created_at', 'desc')->take(10);

        return $newArrivals;
    }

    private function filterProducts($query)
    {
        return $query
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->where('regular_price', '!=', '0')
            ->orWhere('regular_price', '!=', '')
            ->selectRaw(
                'products.*, categories.category_name as category,
            IF(sales_price >= regular_price, 0, ((regular_price - sales_price) / regular_price) * 100) as discount_percentage',
            )
            ->get()
            ->filter(function ($product) {
                return $product->discount_percentage > 50;
            })
            ->values();
    }

    public function getTopDeals()
    {
        $products = $this->filterProducts(Product::query());

        return $products;
    }

    private function calculatePercentageDiscount($originalPrice, $salesPrice)
    {
        if (!is_numeric($originalPrice) || !is_numeric($salesPrice)) {
            $originalPrice = floatval($originalPrice);
            $salesPrice = floatval($salesPrice);
        }

        // Check if the sales price is greater than or equal to the original price
        if ($salesPrice >= $originalPrice) {
            return 0; // Return 0 discount if the sales price is not less than the original price
        }

        $discount = $originalPrice - $salesPrice;
        $percentageDiscount = ($discount / $originalPrice) * 100;
        return $percentageDiscount;
    }
}
