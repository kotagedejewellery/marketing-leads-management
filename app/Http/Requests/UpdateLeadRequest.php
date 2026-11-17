<?php

namespace App\Http\Requests;

use App\Support\LeadBudgetRanges;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateLeadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, list<string|Rule>> */
    public function rules(): array
    {
        return [
            'intent_buying' => ['nullable', 'in:Tunangan,Lamaran,Nikahan,Anniversary,Lainnya'],
            'usage_date' => ['nullable', 'date'],
            'budget' => ['nullable', Rule::in(LeadBudgetRanges::OPTIONS)],
            'keterangan' => ['nullable', 'string'],
            'next_follow_up_at' => ['nullable', 'date'],
            'assigned_user_id' => ['nullable', 'integer', 'exists:users,id'],
            'crm_taken_over' => ['nullable', 'boolean'],
            'revision' => ['nullable', 'integer', 'min:0'],
            'milestones' => ['array'],
            'milestones.*' => ['in:clicked,asked,price_list,offline,online,closing_repeat'],
        ];
    }
}
