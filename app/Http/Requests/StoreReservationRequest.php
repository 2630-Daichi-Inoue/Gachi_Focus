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
            $space     = $this->route('space');
            $date      = $this->input('date');
            $startedAt = $this->input('started_at');
            $endedAt   = $this->input('ended_at');

            if (!$date || !$startedAt || !$endedAt || !$space) {
                return;
            }

            $start      = Carbon::parse($date . ' ' . $startedAt);
            $end        = Carbon::parse($date . ' ' . $endedAt);
            $openTime   = Carbon::parse($date . ' ' . $space->open_time);
            $closeTime  = Carbon::parse($date . ' ' . $space->close_time);

            if (Carbon::parse($date)->isToday() && $start->lte(now())) {
                $validator->errors()->add('started_at', 'Start time must be in the future.');
            }

            if ($start->lt($openTime)) {
                $validator->errors()->add('started_at', 'Start time must be within the space opening hours.');
            }

            if ($end->gt($closeTime)) {
                $validator->errors()->add('ended_at', 'End time must be within the space closing hours.');
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

            'started_at' => [
                'required',
                'date_format:H:i',
                // function ($attribute, $value, $fail) {
                //     $this->timeValidator($value, $fail);
                // },
                new HalfHourTime
            ],

            'ended_at' => [
                'required',
                'date_format:H:i',
                'after:started_at',
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
