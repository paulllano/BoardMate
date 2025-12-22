<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePaymentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'contract_id' => 'required|exists:contracts,id',
            'amount' => 'required|numeric|min:0',
            'payment_date' => 'required|date',
            'payment_method' => 'required|in:Cash,GCash',
            'reference_number' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:500',
        ];
        
        // Check user type using Sanctum
        $user = $this->user();
        $isAdmin = $user && ($user instanceof \App\Models\Admin);
        $isBoarder = $user && !($user instanceof \App\Models\Admin);
        
        // Status validation - required for admins, optional for boarders (defaults to pending)
        if ($isAdmin) {
            $rules['status'] = 'required|in:pending,completed,failed,cancelled';
        } else {
            $rules['status'] = 'nullable|in:pending,completed,failed,cancelled';
        }
        
        // Only require boarder_id for admins
        if (!$isBoarder) {
            $rules['boarder_id'] = 'required|exists:boarders,id';
        }
        
        // Payment type validation - required for boarders, optional for admins
        if ($isBoarder) {
            $rules['payment_type'] = 'required|in:full,partial';
        } else {
            $rules['payment_type'] = 'nullable|in:full,partial';
        }
        
        return $rules;
    }
}
