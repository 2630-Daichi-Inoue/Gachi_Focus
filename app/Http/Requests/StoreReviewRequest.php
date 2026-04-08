<?php

namespace App\Http\Requests;

use Illuminate\Container\Attributes\Auth;
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

    public function ratingValidator($rating, $fail): void
    {
        if ($rating < 1 || $rating > 5) {
            $fail('The rating must be between 1 and 5.');
        }
    }

    public function commentValidator($comment, $fail): void
    {
        if ($comment && strlen($comment) > 500) {
            $fail('The comment must not exceed 500 characters.');
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {

        return [
            'rating' => [
                'required',
                'numeric',
                'min:1',
                'max:5',
                function ($attribute, $value, $fail) {
                    $this->ratingValidator($value, $fail);
                },
            ],

            'comment' => [
                'nullable',
                'string',
                'max:500',
                function ($attribute, $value, $fail) {
                    $this->commentValidator($value, $fail);
                },
            ],

            'is_public' => [
                'required',
                'boolean',
            ],

        ];
    }
}
