<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLeadRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'intent_buying' => ['nullable', 'in:Tunangan,Lamaran,Nikahan,Anniversary,Lainnya'],
            'usage_date' => ['nullable', 'date'],
            'budget' => ['nullable', 'string', 'max:120'],
            'keterangan' => ['nullable', 'string'],
            'next_follow_up_at' => ['nullable', 'date'],
            'assigned_user_id' => ['nullable', 'integer', 'exists:users,id'],
            'milestones' => ['array'],
            'milestones.*' => ['in:clicked,asked,price_list,offline,online,closing_pending_transfer,closing,repeat_order'],
        ];
    }
}
