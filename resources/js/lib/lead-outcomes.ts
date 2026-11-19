export const outcomeLabel = (outcome: string | null) =>
    outcome === 'closing' ? 'Closing' : outcome === 'loss' ? 'Loss' : null;
