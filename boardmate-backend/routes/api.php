<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\BoardingHouseController;
use App\Http\Controllers\BoarderController;
use App\Http\Controllers\ContractController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ApplicationController;


// ============================================
// PUBLIC ROUTES (No Authentication Required)
// ============================================

// Browse boarding houses (public - for boarders to search)
Route::get('/boarding-houses/browse', [BoardingHouseController::class, 'browse']); // Public browse/search
    // curl -X GET http://127.0.0.1:8000/api/boarding-houses/browse?search=house&page=1 \
    //     -H "Content-Type: application/json"

Route::get('/boarding-houses/search/suggestions', [BoardingHouseController::class, 'getSuggestions']); // Public search suggestions
    // curl -X GET http://127.0.0.1:8000/api/boarding-houses/search/suggestions?q=house \
    //     -H "Content-Type: application/json"

Route::get('/boarding-houses/{boardingHouse}/view', [BoardingHouseController::class, 'view']); // Public view for boarders
    // curl -X GET http://127.0.0.1:8000/api/boarding-houses/1/view \
    //     -H "Content-Type: application/json"

// Reviews for a boarding house (public - for boarders to see reviews)
Route::get('/boarding-houses/{id}/reviews', [ReviewController::class, 'houseReviews']); // Show reviews of a house (public)
    // curl -X GET http://127.0.0.1:8000/api/boarding-houses/1/reviews \
    //     -H "Content-Type: application/json"

// ============================================
// AUTHENTICATION ROUTES (Public - No Auth Required)
// ============================================

// Admin Authentication
Route::post('/admin/login', [AuthController::class, 'apiAdminLogin']);
    // curl -X POST http://127.0.0.1:8000/api/admin/login \
    //     -H "Content-Type: application/json" \
    //     -d '{
    //         "email": "admin@example.com",
    //         "password": "password123"
    //     }'

Route::post('/admin/register', [AuthController::class, 'apiAdminRegister']);
    // curl -X POST http://127.0.0.1:8000/api/admin/register \
    //     -H "Content-Type: application/json" \
    //     -d '{
    //         "name": "Admin User",
    //         "email": "admin@example.com",
    //         "phone": "09123456789",
    //         "password": "password123",
    //         "password_confirmation": "password123",
    //         "role": "owner"
    //     }'

// Boarder Authentication
Route::post('/boarder/login', [AuthController::class, 'apiBoarderLogin']);
    // curl -X POST http://127.0.0.1:8000/api/boarder/login \
    //     -H "Content-Type: application/json" \
    //     -d '{
    //         "email": "boarder@example.com",
    //         "password": "password123"
    //     }'

Route::post('/boarder/register', [AuthController::class, 'apiBoarderRegister']);
    // curl -X POST http://127.0.0.1:8000/api/boarder/register \
    //     -H "Content-Type: application/json" \
    //     -d '{
    //         "name": "Boarder User",
    //         "email": "boarder@example.com",
    //         "phone": "09123456789",
    //         "age": 25,
    //         "date_of_birth": "1998-01-01",
    //         "address": "123 Street, City",
    //         "password": "password123",
    //         "password_confirmation": "password123"
    //     }'

// ============================================
// PROTECTED ROUTES (Require Authentication)
// ============================================

Route::middleware('auth:sanctum')->group(function () {
    // 1️ All Admins
    Route::get('/admins', [AdminController::class, 'index']); // List all admins (filtered by auth)
    // curl -X GET http://127.0.0.1:8000/api/admins \
    //     -H "Content-Type: application/json" \
    //     -H "Authorization: Bearer {token}"

    Route::get('/admins/{id}', [AdminController::class, 'show']); // Show one admin by ID
    // curl -X GET http://127.0.0.1:8000/api/admins/1 \
    //     -H "Content-Type: application/json" \
    //     -H "Authorization: Bearer {token}"

    // 2️ All Boarding Houses (filtered by admin)
    Route::get('/boarding-houses', [BoardingHouseController::class, 'index']); // List all houses (filtered by admin)
    // curl -X GET http://127.0.0.1:8000/api/boarding-houses?status=pending&page=1 \
    //     -H "Content-Type: application/json" \
    //     -H "Authorization: Bearer {token}"

    Route::get('/boarding-houses/{boardingHouse}', [BoardingHouseController::class, 'show']); // Show single house
    // curl -X GET http://127.0.0.1:8000/api/boarding-houses/1 \
    //     -H "Content-Type: application/json" \
    //     -H "Authorization: Bearer {token}"

    Route::get('/boarding-houses/{id}/boarders', [BoardingHouseController::class, 'boarders']); // Show boarders of a house
    // curl -X GET http://127.0.0.1:8000/api/boarding-houses/1/boarders \
    //     -H "Content-Type: application/json" \
    //     -H "Authorization: Bearer {token}"

    // 3️ Boarders API (filtered by admin)
    Route::get('/boarders', [BoarderController::class, 'index']); // List all boarders (filtered by admin)
    // curl -X GET http://127.0.0.1:8000/api/boarders?page=1 \
    //     -H "Content-Type: application/json" \
    //     -H "Authorization: Bearer {token}"

    Route::get('/boarders/{boarder}', [BoarderController::class, 'show']); // Show single boarder
    // curl -X GET http://127.0.0.1:8000/api/boarders/1 \
    //     -H "Content-Type: application/json" \
    //     -H "Authorization: Bearer {token}"

    Route::get('/boarders/{id}/contracts', [BoarderController::class, 'contracts']); // Show contracts of a boarder
    // curl -X GET http://127.0.0.1:8000/api/boarders/1/contracts \
    //     -H "Content-Type: application/json" \
    //     -H "Authorization: Bearer {token}"

    // 4️ Contracts (filtered by admin)
    Route::get('/contracts', [ContractController::class, 'index']); // Show all contracts (filtered by admin)
    // curl -X GET http://127.0.0.1:8000/api/contracts?status=Active&page=1 \
    //     -H "Content-Type: application/json" \
    //     -H "Authorization: Bearer {token}"

    // IMPORTANT: Routes with specific paths (like /deleted, /create) must come BEFORE routes with parameters (like /{id})
    Route::get('/contracts/deleted', [ContractController::class, 'deleted']);
    // curl -X GET http://127.0.0.1:8000/api/contracts/deleted \
    //     -H "Content-Type: application/json" \
    //     -H "Authorization: Bearer {token}"

    Route::post('/contracts/deleted/{id}/restore', [ContractController::class, 'restore']);
    // curl -X POST http://127.0.0.1:8000/api/contracts/deleted/1/restore \
    //     -H "Content-Type: application/json" \
    //     -H "Authorization: Bearer {token}"

    Route::get('/contracts/create', [ContractController::class, 'create']); // Get form data for creating contract (must be before /contracts/{id})
    // curl -X GET http://127.0.0.1:8000/api/contracts/create \
    //     -H "Content-Type: application/json" \
    //     -H "Authorization: Bearer {token}"

    Route::get('/contracts/{id}/payments', [ContractController::class, 'payments']); // Show payments for a contract (must be before /contracts/{id})
    // curl -X GET http://127.0.0.1:8000/api/contracts/1/payments \
    //     -H "Content-Type: application/json" \
    //     -H "Authorization: Bearer {token}"

    Route::get('/contracts/{id}', [ContractController::class, 'show']); // Show single contract
    // curl -X GET http://127.0.0.1:8000/api/contracts/1 \
    //     -H "Content-Type: application/json" \
    //     -H "Authorization: Bearer {token}"

    // 5️ Payments (filtered by admin)
    Route::get('/payments', [PaymentController::class, 'index']); // Show all payments (filtered by admin)
    // curl -X GET http://127.0.0.1:8000/api/payments?status=completed&page=1 \
    //     -H "Content-Type: application/json" \
    //     -H "Authorization: Bearer {token}"

    // IMPORTANT: Routes with specific paths (like /deleted, /create) must come BEFORE routes with parameters (like /{id})
    Route::get('/payments/deleted', [PaymentController::class, 'deleted']);
    // curl -X GET http://127.0.0.1:8000/api/payments/deleted \
    //     -H "Content-Type: application/json" \
    //     -H "Authorization: Bearer {token}"

    Route::post('/payments/deleted/{id}/restore', [PaymentController::class, 'restore']);
    // curl -X POST http://127.0.0.1:8000/api/payments/deleted/1/restore \
    //     -H "Content-Type: application/json" \
    //     -H "Authorization: Bearer {token}"

    Route::get('/payments/create', [PaymentController::class, 'create']); // Get form data for creating payment (must be before /payments/{id})
    // curl -X GET http://127.0.0.1:8000/api/payments/create \
    //     -H "Content-Type: application/json" \
    //     -H "Authorization: Bearer {token}"

    Route::get('/payments/{id}', [PaymentController::class, 'show']); // Show single payment
    // curl -X GET http://127.0.0.1:8000/api/payments/1 \
    //     -H "Content-Type: application/json" \
    //     -H "Authorization: Bearer {token}"

    // 6️ Reviews (filtered by admin)
    Route::get('/reviews', [ReviewController::class, 'index']); // List all reviews (filtered by admin)
    // curl -X GET http://127.0.0.1:8000/api/reviews?page=1 \
    //     -H "Content-Type: application/json" \
    //     -H "Authorization: Bearer {token}"

    Route::get('/reviews/{id}', [ReviewController::class, 'show']); // Show single review
    // curl -X GET http://127.0.0.1:8000/api/reviews/1 \
    //     -H "Content-Type: application/json" \
    //     -H "Authorization: Bearer {token}"

    // 7️ Applications (filtered by admin/boarder)
    Route::get('/applications', [ApplicationController::class, 'index']); // List all applications (filtered by user)
    // curl -X GET http://127.0.0.1:8000/api/applications?status=pending&page=1 \
    //     -H "Content-Type: application/json" \
    //     -H "Authorization: Bearer {token}"

    Route::get('/applications/{id}', [ApplicationController::class, 'show']); // Show single application
    // curl -X GET http://127.0.0.1:8000/api/applications/1 \
    //     -H "Content-Type: application/json" \
    //     -H "Authorization: Bearer {token}"

    // 8️ Services (filtered by admin)
    Route::get('/services', [ServiceController::class, 'index']); // List all services (filtered by admin)
    // curl -X GET http://127.0.0.1:8000/api/services?page=1 \
    //     -H "Content-Type: application/json" \
    //     -H "Authorization: Bearer {token}"

    Route::get('/services/{id}', [ServiceController::class, 'show']); // Show one service
    // curl -X GET http://127.0.0.1:8000/api/services/1 \
    //     -H "Content-Type: application/json" \
    //     -H "Authorization: Bearer {token}"

// Get current user (requires authentication)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'apiUser']);
    // curl -X GET http://127.0.0.1:8000/api/user \
    //     -H "Content-Type: application/json" \
    //     -H "Authorization: Bearer {token}"

    Route::post('/logout', [AuthController::class, 'apiLogout']);
    // curl -X POST http://127.0.0.1:8000/api/logout \
    //     -H "Content-Type: application/json" \
    //     -H "Authorization: Bearer {token}"

    Route::put('/settings/profile', [AuthController::class, 'apiUpdateProfile']);
    // curl -X PUT http://127.0.0.1:8000/api/settings/profile \
    //     -H "Content-Type: application/json" \
    //     -H "Authorization: Bearer {token}" \
    //     -d '{
    //         "name": "Updated Name",
    //         "email": "updated@example.com",
    //         "phone": "09123456789"
    //     }'
});

    // ============================================
    // CRUD OPERATIONS (Create, Update, Delete)
    // ============================================
    
    // Boarding Houses CRUD
    Route::post('/boarding-houses', [BoardingHouseController::class, 'store']);
    // curl -X POST http://127.0.0.1:8000/api/boarding-houses \
    //     -H "Content-Type: application/json" \
    //     -H "Authorization: Bearer {token}" \
    //     -d '{
    //         "name": "New Boarding House",
    //         "address": "123 Main Street",
    //         "description": "A nice boarding house"
    //     }'

    Route::put('/boarding-houses/{boardingHouse}', [BoardingHouseController::class, 'update']);
    // curl -X PUT http://127.0.0.1:8000/api/boarding-houses/1 \
    //     -H "Content-Type: application/json" \
    //     -H "Authorization: Bearer {token}" \
    //     -d '{
    //         "name": "Updated Boarding House",
    //         "address": "456 New Street",
    //         "description": "Updated description"
    //     }'

    Route::delete('/boarding-houses/{boardingHouse}', [BoardingHouseController::class, 'destroy']);
    // curl -X DELETE http://127.0.0.1:8000/api/boarding-houses/1 \
    //     -H "Content-Type: application/json" \
    //     -H "Authorization: Bearer {token}"
    
    // Contracts CRUD
    Route::post('/contracts', [ContractController::class, 'store']);
    // curl -X POST http://127.0.0.1:8000/api/contracts \
    //     -H "Content-Type: application/json" \
    //     -H "Authorization: Bearer {token}" \
    //     -d '{
    //         "boarder_id": 1,
    //         "boarding_house_id": 1,
    //         "start_date": "2025-01-01",
    //         "end_date": "2025-12-31",
    //         "rent_amount": 5000.00
    //     }'

    Route::put('/contracts/{contract}', [ContractController::class, 'update']);
    // curl -X PUT http://127.0.0.1:8000/api/contracts/1 \
    //     -H "Content-Type: application/json" \
    //     -H "Authorization: Bearer {token}" \
    //     -d '{
    //         "status": "Active",
    //         "rent_amount": 5500.00
    //     }'

    Route::delete('/contracts/{contract}', [ContractController::class, 'destroy']);
    // curl -X DELETE http://127.0.0.1:8000/api/contracts/1 \
    //     -H "Content-Type: application/json" \
    //     -H "Authorization: Bearer {token}"
    
    // Payments CRUD
    Route::post('/payments', [PaymentController::class, 'store']);
    // curl -X POST http://127.0.0.1:8000/api/payments \
    //     -H "Content-Type: application/json" \
    //     -H "Authorization: Bearer {token}" \
    //     -d '{
    //         "contract_id": 1,
    //         "boarder_id": 1,
    //         "amount": 5000.00,
    //         "payment_date": "2025-01-01",
    //         "method": "Cash",
    //         "payment_type": "full",
    //         "status": "pending"
    //     }'

    Route::put('/payments/{payment}', [PaymentController::class, 'update']);
    // curl -X PUT http://127.0.0.1:8000/api/payments/1 \
    //     -H "Content-Type: application/json" \
    //     -H "Authorization: Bearer {token}" \
    //     -d '{
    //         "status": "completed",
    //         "amount": 5000.00
    //     }'

    Route::delete('/payments/{payment}', [PaymentController::class, 'destroy']);
    // curl -X DELETE http://127.0.0.1:8000/api/payments/1 \
    //     -H "Content-Type: application/json" \
    //     -H "Authorization: Bearer {token}"

    Route::post('/payments/{payment}/approve', [PaymentController::class, 'approve']);
    // curl -X POST http://127.0.0.1:8000/api/payments/1/approve \
    //     -H "Content-Type: application/json" \
    //     -H "Authorization: Bearer {token}"

    Route::post('/payments/{payment}/reject', [PaymentController::class, 'reject']);
    // curl -X POST http://127.0.0.1:8000/api/payments/1/reject \
    //     -H "Content-Type: application/json" \
    //     -H "Authorization: Bearer {token}" \
    //     -d '{
    //         "admin_notes": "Payment rejected due to insufficient funds"
    //     }'
    
    // Applications CRUD
    Route::post('/applications', [ApplicationController::class, 'store']);
    // curl -X POST http://127.0.0.1:8000/api/applications \
    //     -H "Content-Type: application/json" \
    //     -H "Authorization: Bearer {token}" \
    //     -d '{
    //         "boarding_house_id": 1,
    //         "message": "I would like to apply for this boarding house"
    //     }'

    Route::post('/applications/{application}/approve', [ApplicationController::class, 'approve']);
    // curl -X POST http://127.0.0.1:8000/api/applications/1/approve \
    //     -H "Content-Type: application/json" \
    //     -H "Authorization: Bearer {token}"

    Route::post('/applications/{application}/reject', [ApplicationController::class, 'reject']);
    // curl -X POST http://127.0.0.1:8000/api/applications/1/reject \
    //     -H "Content-Type: application/json" \
    //     -H "Authorization: Bearer {token}" \
    //     -d '{
    //         "admin_notes": "Application rejected due to incomplete requirements"
    //     }'

    Route::post('/applications/{application}/approve-transfer', [ApplicationController::class, 'approveTransfer']);
    // curl -X POST http://127.0.0.1:8000/api/applications/1/approve-transfer \
    //     -H "Content-Type: application/json" \
    //     -H "Authorization: Bearer {token}"

    Route::post('/applications/{application}/reject-transfer', [ApplicationController::class, 'rejectTransfer']);
    // curl -X POST http://127.0.0.1:8000/api/applications/1/reject-transfer \
    //     -H "Content-Type: application/json" \
    //     -H "Authorization: Bearer {token}" \
    //     -d '{
    //         "admin_notes": "Transfer request rejected"
    //     }'
    
    // Reviews CRUD
    Route::post('/reviews', [ReviewController::class, 'store']);
    // curl -X POST http://127.0.0.1:8000/api/reviews \
    //     -H "Content-Type: application/json" \
    //     -H "Authorization: Bearer {token}" \
    //     -d '{
    //         "boarding_house_id": 1,
    //         "rating": 5,
    //         "comment": "Great boarding house!",
    //         "is_anonymous": false
    //     }'

    Route::put('/reviews/{review}', [ReviewController::class, 'update']);
    // curl -X PUT http://127.0.0.1:8000/api/reviews/1 \
    //     -H "Content-Type: application/json" \
    //     -H "Authorization: Bearer {token}" \
    //     -d '{
    //         "rating": 4,
    //         "comment": "Updated review comment"
    //     }'

    Route::delete('/reviews/{review}', [ReviewController::class, 'destroy']);
    // curl -X DELETE http://127.0.0.1:8000/api/reviews/1 \
    //     -H "Content-Type: application/json" \
    //     -H "Authorization: Bearer {token}"
    
    // Services CRUD
    Route::post('/services', [ServiceController::class, 'store']);
    // curl -X POST http://127.0.0.1:8000/api/services \
    //     -H "Content-Type: application/json" \
    //     -H "Authorization: Bearer {token}" \
    //     -d '{
    //         "boarding_house_id": 1,
    //         "name": "Laundry Service",
    //         "description": "Weekly laundry service",
    //         "price": 500.00,
    //         "category": "cleaning",
    //         "availability": "available",
    //         "is_recurring": true,
    //         "notes": "Available every Monday"
    //     }'

    Route::put('/services/{service}', [ServiceController::class, 'update']);
    // curl -X PUT http://127.0.0.1:8000/api/services/1 \
    //     -H "Content-Type: application/json" \
    //     -H "Authorization: Bearer {token}" \
    //     -d '{
    //         "name": "Updated Laundry Service",
    //         "price": 600.00,
    //         "availability": "available"
    //     }'

    Route::delete('/services/{service}', [ServiceController::class, 'destroy']);
    // curl -X DELETE http://127.0.0.1:8000/api/services/1 \
    //     -H "Content-Type: application/json" \
    //     -H "Authorization: Bearer {token}"
    
    // Boarders CRUD
    Route::post('/boarders', [BoarderController::class, 'store']);
    // curl -X POST http://127.0.0.1:8000/api/boarders \
    //     -H "Content-Type: application/json" \
    //     -H "Authorization: Bearer {token}" \
    //     -d '{
    //         "name": "New Boarder",
    //         "email": "boarder@example.com",
    //         "phone": "09123456789",
    //         "age": 25,
    //         "date_of_birth": "1998-01-01",
    //         "address": "123 Street, City",
    //         "password": "password123",
    //         "password_confirmation": "password123",
    //         "boarding_house_id": 1
    //     }'

    Route::put('/boarders/{boarder}', [BoarderController::class, 'update']);
    // curl -X PUT http://127.0.0.1:8000/api/boarders/1 \
    //     -H "Content-Type: application/json" \
    //     -H "Authorization: Bearer {token}" \
    //     -d '{
    //         "name": "Updated Boarder Name",
    //         "phone": "09987654321",
    //         "age": 26
    //     }'

    Route::delete('/boarders/{boarder}', [BoarderController::class, 'destroy']);
    // curl -X DELETE http://127.0.0.1:8000/api/boarders/1 \
    //     -H "Content-Type: application/json" \
    //     -H "Authorization: Bearer {token}"
});









