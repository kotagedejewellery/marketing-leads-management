import { z } from 'zod';
import { budgetOptions } from './lead-options';

export const leadSchema = z
    .object({
        intent_buying: z
            .enum(['Tunangan', 'Lamaran', 'Nikahan', 'Anniversary', 'Lainnya'])
            .or(z.literal('')),
        usage_date: z.string(),
        budget: z.enum(budgetOptions).or(z.literal('')),
        keterangan: z.string(),
        next_follow_up_at: z.string(),
        outcome: z.enum(['closing', 'loss']).or(z.literal('')),
        outcome_factor: z.string(),
        outcome_note: z.string(),
        milestones: z.array(z.string()),
    })
    .superRefine((value, context) => {
        if (!value.outcome) {
            if (value.outcome_factor || value.outcome_note) {
                context.addIssue({
                    code: z.ZodIssueCode.custom,
                    path: ['outcome_factor'],
                    message:
                        'Hapus faktor dan catatan saat hasil akhir kosong.',
                });
            }

            return;
        }
        if (!value.outcome_factor) {
            context.addIssue({
                code: z.ZodIssueCode.custom,
                path: ['outcome_factor'],
                message: 'Pilih faktor hasil akhir lead.',
            });
        }
        if (value.outcome_factor === 'other' && !value.outcome_note.trim()) {
            context.addIssue({
                code: z.ZodIssueCode.custom,
                path: ['outcome_note'],
                message: 'Jelaskan faktor lainnya.',
            });
        }
    });
