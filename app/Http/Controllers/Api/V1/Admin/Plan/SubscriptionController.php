<?php

namespace App\Http\Controllers\Api\V1\Admin\Plan;

use App\Http\Controllers\Controller;
use App\Http\Requests\Plan\SubscriptionRequest;
use App\Http\Resources\SubscriptionResource;
use App\Models\SubscriptionPlan;
use App\Models\UserSubscription;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SubscriptionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
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
    public function store(SubscriptionRequest $request)
    {
        $hasSub = SubscriptionPlan::query()->where([
            ['duration', $request->get('duration')],
            ['name', $request->get('name')]
        ])->exists();

        if ($hasSub) {
            return response()->json([
                'error' => "{$request->get('name')} plan already exists for {$request->get('duration')}",
                'status_code' => Response::HTTP_UNPROCESSABLE_ENTITY
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        DB::beginTransaction();
        try {
            $sub = SubscriptionPlan::create($request->validated());
            foreach ($request->get('features') as $feature) {
                $sub->features()->attach($feature['id'], ['status' => $feature['status']]);
            }
            DB::commit();
            return response()->json([
                'status_code' => Response::HTTP_CREATED,
                'data' => new SubscriptionResource($sub->load('features')),
                'message' => 'subscription plan created successfully'
            ], Response::HTTP_CREATED);
        } catch (\Exception $exception) {
            DB::rollBack();
            Log::error($exception->getMessage());
            return \response()->json([
                'error' => 'creation of subscription plan failed',
                'status_code' => Response::HTTP_BAD_REQUEST
            ],  Response::HTTP_BAD_REQUEST);
        }

    }

    public function userPlan() {
        $userPlan = UserSubscription::where('user_id', auth()->user()->id)->first()->subscription_plan_id;
        try {
            // Retrieve the billing plan by ID
            $billingPlan = SubscriptionPlan::find($userPlan);

            if (!$billingPlan) {
                return response()->json([
                    'status' => 404,
                    'message' => 'Billing plan not found'
                ], 404);
            }

            // Return the billing plan details
            return response()->json([
                'status' => 200,
                'message' => 'Billing plans retrieved successfully',
                'data' => [
                    'id' => $billingPlan->id,
                    'name' => $billingPlan->name,
                    'price' => $billingPlan->price,
                ]
            ], 200);
        } catch (\Exception $e) {
            // Handle unexpected errors
            return response()->json([
                'status' => 500,
                'message' => 'Internal server error'
            ], 500);
        }

    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
