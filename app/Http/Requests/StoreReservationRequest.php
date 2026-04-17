<?php

namespace App\Http\Requests;

use App\Rules\HalfHourTime;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class StoreReservationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = $this->user();
        if ($user && ($user->isBanned() || $user->isRestricted())) {
            return false;
        }
        return true;
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
                // function ($attribute, $value, $fail) {
                //     $this->timeValidator($value, $fail);
                // },
                new HalfHourTime
            ],

            'end_at' => [
                'required',
                'date_format:H:i',
                'after:start_at',
                // function ($attribute, $value, $fail) {
                //     $this->timeValidator($value, $fail);
                // },
                new HalfHourTime
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
