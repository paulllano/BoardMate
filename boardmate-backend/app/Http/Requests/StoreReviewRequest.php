<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreReviewRequest extends FormRequest
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
            'boarding_house_id' => 'required|exists:boarding_houses,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ];

        // Only require boarder_id if not logged in as boarder
        // Check using request()->user() for Sanctum compatibility
        $user = $this->user();
        if (!$user || ($user instanceof \App\Models\Admin)) {
            $rules['boarder_id'] = 'required|exists:boarders,id';
        }

        return $rules;
    }
}
