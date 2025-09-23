<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class StoreEnrollmentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            "course_id" => [
                "required",
                "string",
                "exists:courses,id",
                Rule::unique('enrollments')->where(function ($query) {
                    return $query->where('user_id', $this->user_id);
                }),
            ],
            "user_id" => "required|string|exists:users,id",
            "progress" => "nullable|integer|min:0|max:100"
        ];
    }
}
