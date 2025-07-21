<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Resources\CustomerResource;
use App\Http\Requests\CustomerRequest;
use App\Models\Customer;
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

        $customers = Customer::with('orders')->get();

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
        // Gate::authorize('create', Customer::class);

        // $customer = Customer::create($request->validated());

        // return ApiResponse::success(
        //     'Customer created.',
        //     new CustomerResource($customer),
        //     HttpResponse::HTTP_CREATED
        // );
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
            'Customer updated.',
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

        $customer->delete();

        return ApiResponse::success(
            'Customer deleted.',
            null,
            HttpResponse::HTTP_OK
        );
    }
}
