<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBoarderRequest extends FormRequest
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
        return [
            'boarding_house_id' => 'required|exists:boarding_houses,id',
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:boarders,email,' . $this->route('boarder')->id,
            'phone' => 'required|string|max:20',
            'age' => 'required|integer|min:18|max:100',
            'address' => 'nullable|string|max:500',
            'date_of_birth' => 'nullable|date',
        ];
    }
}
