import { z } from 'zod';
export const leadSchema = z.object({
    intent_buying: z
        .enum(['Tunangan', 'Lamaran', 'Nikahan', 'Anniversary', 'Lainnya'])
        .or(z.literal('')),
    usage_date: z.string(),
    budget: z.string().max(120),
    keterangan: z.string(),
    next_follow_up_at: z.string(),
    milestones: z.array(z.string()),
});
