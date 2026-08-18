<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ApiTripIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'start_at' => ['required', 'date'],
            'end_at' => ['required', 'date', 'after_or_equal:start_at'],
            'vehicle_id' => ['nullable', 'integer', 'exists:vehicles,id', 'required_without:user_id', 'prohibited_with:user_id'],
            'user_id' => ['nullable', 'integer', 'exists:users,id', 'required_without:vehicle_id', 'prohibited_with:vehicle_id'],
        ];
    }
}
