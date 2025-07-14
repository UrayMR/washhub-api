<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Resources\CustomerResource;
use App\Http\Requests\CustomerRequest;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        Gate::authorize('viewAny', Customer::class);

        $user = Auth::user();

        // Super-admin: get all information, Admin: only get informations that they serves
        $customers = $user->role === User::ROLE_SUPER_ADMIN
            ? Customer::with('orders')->get()
            : Customer::whereHas('orders', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })->with('orders')->get();

        return ApiResponse::success(
            'Customers retrieved successfully.',
            CustomerResource::collection($customers),
            HttpResponse::HTTP_OK
        );
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CustomerRequest $request)
    {
        Gate::authorize('create', Customer::class);

        $customer = Customer::create($request->validated());

        return ApiResponse::success(
            'Customer created.',
            new CustomerResource($customer),
            HttpResponse::HTTP_CREATED
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(Customer $customer)
    {
        Gate::authorize('view', $customer);

        return ApiResponse::success(
            'Customer retrieved successfully.',
            new CustomerResource($customer),
            HttpResponse::HTTP_OK
        );
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Customer $customer)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(CustomerRequest $request, Customer $customer)
    {
        Gate::authorize('update', $customer);

        $customer->update($request->validated());

        return ApiResponse::success(
            'Customer updated successfully.',
            new CustomerResource($customer),
            HttpResponse::HTTP_OK
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Customer $customer)
    {
        Gate::authorize('delete', $customer);

        $customerDeletedData = [
            'id' => $customer->id,
            'name' => $customer->name,
            'phone_number' => $customer->phone_number,
            'address' => $customer->address,
        ];

        $customer->delete();

        return ApiResponse::success(
            'Customer deleted successfully.',
            $customerDeletedData,
            HttpResponse::HTTP_OK
        );
    }
}
