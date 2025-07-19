<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Requests\OrderRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Models\User;
use App\Services\OrderService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response as HttpResponse;


class OrderController extends Controller
{
    public function __construct(protected OrderService $orderService) {}

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        Gate::authorize('viewAny', Order::class);

        $user = Auth::user();

        // Super-admin: get all information, Admin: only get informations that they serves
        $orders = $user->role === User::ROLE_SUPER_ADMIN
            ? Order::with(['customer', 'items.service'])->get()
            : Order::where('user_id', $user->id)
            ->with(['customer', 'items.service'])
            ->get();

        return ApiResponse::success(
            'Orders retrieved successfully.',
            OrderResource::collection($orders),
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
    public function store(OrderRequest $request)
    {
        Gate::authorize('create', Order::class);

        $order = $this->orderService->store($request->validated(), Auth::user());

        return ApiResponse::success(
            'Order created.',
            new OrderResource($order),
            HttpResponse::HTTP_CREATED
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(Order $order)
    {
        Gate::authorize('view', $order);

        // Eager load the relations 
        $order->load(['customer', 'items.service']);

        return ApiResponse::success(
            'Order retrieved successfully.',
            new OrderResource($order),
            HttpResponse::HTTP_OK
        );
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Order $order)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(OrderRequest $request, Order $order)
    {
        Gate::authorize('update', $order);

        $order = $this->orderService->update($order, $request->validated());

        return ApiResponse::success(
            'Order updated.',
            new OrderResource($order)
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Order $order)
    {
        Gate::authorize('delete', $order);

        DB::transaction(function () use ($order) {
            // Delete all order items
            $order->items()->delete();

            // Delete the order itself
            $order->delete();
        });

        return ApiResponse::success(
            'Order Deleted.',
            null,
            HttpResponse::HTTP_OK
        );
    }
}
