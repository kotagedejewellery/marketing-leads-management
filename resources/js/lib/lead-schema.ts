import { z } from 'zod';
import { budgetOptions } from './lead-options';
export const leadSchema = z.object({
    intent_buying: z
        .enum(['Tunangan', 'Lamaran', 'Nikahan', 'Anniversary', 'Lainnya'])
        .or(z.literal('')),
    usage_date: z.string(),
    budget: z.enum(budgetOptions).or(z.literal('')),
    keterangan: z.string(),
    next_follow_up_at: z.string(),
    milestones: z.array(z.string()),
});
