<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\Address;
use illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;

class AddressController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    private $defaultTotalAddress = 5;
    public function index()
    {
        $user = auth()->user();

        if ($user) {
            $userAddresses = Address::where('user_id', '=', $user->id)->get();

            $userAddresses->each(function ($address) use ($user) {
                $address->is_default = $address->id === $user->default_address;
            });

            return response()->json([
                'status' => 'success',
                'data' => ['addresses' => $userAddresses],
            ]);
        } else {
            return response()->json(
                [
                    'status' => 'error',
                    'message' => 'User not authenticated.',
                ],
                401,
            );
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if ($this->getTotalAddressForUser() > $this->defaultTotalAddress) {
            return response()->json(
                [
                    'status' => 'errror',
                    'message' => 'You can only add a total of 5 addresses!',
                ],
                422,
            );
        }

        try {
            $user = auth()->user();

            $addressData = $request->validate([
                'firstname' => 'string|required|max:30',
                'lastname' => 'string|required|max:30',
                'delivery_address' => 'required|string|min:3',
                'city' => 'required',
                'state' => 'required',
                'postal_code' => 'required',
                // 'country' => 'required',
                'defaut_address' => 'string',
                'phone_number_1' => 'required',
                'phone_number_2' => 'sometimes',
            ]);

            $address = $user->Address()->create($addressData);

            //check if the address is the first one for this user , if yess make it the default
            if ($this->getTotalAddressForUser() === 1) {
                $this->setDefaultAddress($address->id);
            } else {
                if ($request->has('default_address')) {
                    // $address->id contains the ID of the newly created address
                    $newlyCreatedAddressId = $address->id;
                    $this->setDefaultAddress($newlyCreatedAddressId);
                }
            }

            return response()->json(
                [
                    'status' => 'success',
                    'message' => 'Address added successfully',
                    'address' => $address,
                ],
                201,
            );
        } catch (\Exception $e) {
            return response()->json(
                [
                    'status' => 'error',
                    'message' => $e->getMessage(),
                ],
                409,
            );
        }
    }

    public function getTotalAddressForUser()
    {
        return Auth::user()->address()->count();
    }
    /**
     * Update the specified address.
     *
     * @param Request $request
     * @param Address $address
     * @return JsonResponse
     */
    public function update(Request $request, Address $address)
    {
        try {
            $user = auth()->user();

            // Validate the incoming data based on your requirements
            $updatedData = $request->validate([
                'firstname' => 'string|required|max:30',
                'lastname' => 'string|required|max:30',
                'delivery_address' => 'required',
                'city' => 'required',
                'state' => 'required',
                'postal_code' => 'required',
                // 'country' => 'required',
                'phone_number_1' => 'required',
                'phone_number_2' => 'required',
            ]);

            // Ensure the address belongs to the authenticated user
            if ($address->user_id !== $user->id) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 401);
            }

            // Update the address
            $address->update($updatedData);

            return response()->json(['status' => 'success', 'message' => 'Address updated successfully', 'address' => $address], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 409);
        }
    }

    public function setDefaultAddress($addressId)
    {
        $user = Auth()->user();

        // Validate that the address belongs to the user
        $address = $user->address()->find($addressId);

        if (!$address) {
            return response()->json(
                [
                    'status' => 'error',
                    'message' => 'Address not found or does not belong to the user.',
                ],
                404,
            );
        }

        // Update the default address in the users table
        $user->update(['default_address_id' => $addressId]);

        return response()->json([
            'status' => 'success',
            'message' => 'Default address updated successfully.',
        ]);
    }

    /**
     * Remove the specified address from storage.
     *
     * @param Address $address
     * @return JsonResponse
     */
    public function destroy(Address $address)
    {
        try {
            $user = auth()->user();

            // Ensure the address belongs to the authenticated user
            if ($address->user_id !== $user->id) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 401);
            }

            // Delete the address
            $address->delete();

            // check if there is any address left for the user and set the first one as the default
            if ($this->getTotalAddressForUser() > 0) {
                $user->update(['default_address_id' => $user->address()->first()->id]);
            }
            

            return response()->json(['status' => 'success', 'message' => 'Address deleted successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 409);
        }
    }
}
