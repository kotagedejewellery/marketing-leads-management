<?php

namespace App\Http\Requests;

use App\Support\LeadBudgetRanges;
use App\Support\LeadOutcomeFactors;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

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
            'outcome' => ['nullable', Rule::in(['closing', 'loss'])],
            'outcome_factor' => ['nullable', 'string', 'max:80'],
            'outcome_note' => ['nullable', 'string', 'max:2000'],
            'assigned_user_id' => ['prohibited'],
            'crm_taken_over' => ['nullable', 'boolean'],
            'revision' => ['nullable', 'integer', 'min:0'],
            'milestones' => ['array'],
            'milestones.*' => ['in:clicked,asked,price_list,offline,online,closing_repeat'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if (! $this->has('outcome')) {
                return;
            }

            $outcome = (string) $this->input('outcome');
            $factor = (string) $this->input('outcome_factor');
            $note = trim((string) $this->input('outcome_note'));
            if ($outcome === '') {
                if ($factor !== '' || $note !== '') {
                    $validator->errors()->add('outcome_factor', 'Hapus faktor dan catatan saat hasil akhir dikosongkan.');
                }

                return;
            }
            if ($factor === '' || ! LeadOutcomeFactors::isValid($outcome, $factor)) {
                $validator->errors()->add('outcome_factor', 'Pilih faktor yang sesuai dengan hasil akhir lead.');

                return;
            }
            if ($factor === 'other' && $note === '') {
                $validator->errors()->add('outcome_note', 'Jelaskan faktor lainnya.');
            }
        });
    }
}
