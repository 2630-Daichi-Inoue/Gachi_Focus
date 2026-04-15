<?php

namespace App\Http\Requests;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class StoreReservationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function timeValidator($datetime, $fail): void
    {
        $time_collection = explode(':', $datetime);

        if ($time_collection[1] !== '00' && $time_collection[1] !== '30') {
            $fail('The time must be in 30-minute increments.');
        }

    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $date = $this->input('date');
            $startAt = $this->input('start_at');

            if (!$date || !$startAt) {
                return;
            }

            $start = Carbon::parse($date . ' ' . $startAt);

            if (Carbon::parse($date)->isToday() && $start->lte(now())) {
                $validator->errors()->add('start_at', 'Start time must be in the future.');
            }
        });
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $space = $this->route('space');

        return [
            'date' => [
                'required',
                'date_format:Y-m-d',
                'after_or_equal:today',
            ],

            'start_at' => [
                'required',
                'date_format:H:i',
                function ($attribute, $value, $fail) {
                    $this->timeValidator($value, $fail);
                },
            ],

            'end_at' => [
                'required',
                'date_format:H:i',
                'after:start_at',
                function ($attribute, $value, $fail) {
                    $this->timeValidator($value, $fail);
                },
            ],

            'quantity' => [
                'required',
                'integer',
                'min:1',
                'max:' . $space->capacity,
            ],

        ];
    }
}
