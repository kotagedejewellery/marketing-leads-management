<script setup lang="ts">
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3';
import {
    ChevronDown,
    ChevronUp,
    Pencil,
    SlidersHorizontal,
    X,
} from '@lucide/vue';
import {
    computed,
    nextTick,
    onBeforeUnmount,
    onMounted,
    reactive,
    ref,
    watch,
} from 'vue';
import LeadSaveFeedback from '@/components/LeadSaveFeedback.vue';
import LeadHistoryPopover from '@/components/LeadHistoryPopover.vue';
import LeadQuickUpdate from '@/components/LeadQuickUpdate.vue';
import { budgetOptions } from '@/lib/lead-options';
import { outcomeLabel } from '@/lib/lead-outcomes';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import {
    Sheet,
    SheetContent,
    SheetDescription,
    SheetHeader,
    SheetTitle,
} from '@/components/ui/sheet';

type Lead = {
    id: number;
    name: string | null;
    contact_raw: string;
    phone: string | null;
    intent_buying: string | null;
    usage_date: string | null;
    budget: string | null;
    keterangan: string | null;
    imported_definition: string | null;
    current_stage: string | null;
    outcome: string | null;
    outcome_factor: string | null;
    outcome_note: string | null;
    outcome_recorded_at: string | null;
    next_follow_up_at: string | null;
    crm_taken_over_at: string | null;
    milestones: string[];
    latest_activity: string | null;
    latest_activity_detail: string | null;
    latest_activity_at: string | null;
    owner_name: string | null;
    branch_name: string | null;
    source_branch_id: number | null;
    cs_filter_match: string | null;
    handling_role: 'admin' | 'primary' | 'collaborator' | 'team';
    collaborators: { id: number; name: string }[];
    source_conflict_at: string | null;
    source_conflict_branch_name: string | null;
    lead_created_on: string | null;
    revision: number;
    created_at: string;
    updated_at: string;
};
type Page = {
    data: Lead[];
    total: number;
};
type LeadSaveState = {
    state: 'idle' | 'pending' | 'saving' | 'saved' | 'error' | 'conflict';
    message: string;
};
type QuickUpdateFields = {
    intent_buying: string;
    usage_date: string;
    budget: string;
    keterangan: string;
    outcome: string;
    outcome_factor: string;
    outcome_note: string;
    next_follow_up_at: string;
    crm_taken_over: boolean;
    revision: number;
    milestones: string[];
};
type LeadEditState = {
    current: QuickUpdateFields;
    saved: QuickUpdateFields;
    saveState: LeadSaveState;
};
const props = defineProps<{
    leads: Page;
    filters: {
        q?: string;
        branch?: string;
        source_branch_missing?: string;
        stage?: string;
        outcome?: string;
        outcome_factor?: string;
        queue?: string;
        intent?: string;
        budget?: string;
        lead_date_from?: string;
        lead_date_to?: string;
        lead_date_status?: string;
        follow_up?: string;
        handling?: string;
        crm?: string;
        cs_user_id?: string;
        archived?: string;
    };
    branches: { id: number; name: string }[];
    csAccounts: {
        id: number;
        name: string;
        branch_id: number;
        branch_name: string;
    }[];
    outcomeFactors: Record<string, Record<string, string>>;
    archiveMode: boolean;
    sheetSync: {
        status: string;
        completed_at: string | null;
        summary: { missing_source_fields?: number } | null;
    } | null;
}>();
const page = usePage();
const isAdmin = computed(() => page.props.auth.user?.role === 'admin');
const ownerLabel = (lead: Lead) => lead.owner_name || 'Belum ditugaskan';
const ownerTitle = (lead: Lead) =>
    isAdmin.value && lead.branch_name
        ? `${ownerLabel(lead)} · Cabang: ${lead.branch_name}`
        : ownerLabel(lead);
const q = ref(props.filters.q || '');
const branch = ref(props.filters.branch || '');
const sourceBranchMissing = ref(props.filters.source_branch_missing || '');
const stage = ref(props.filters.stage || '');
const outcome = ref(props.filters.outcome || '');
const queue = ref(props.filters.queue || '');
const intent = ref(props.filters.intent || '');
const budget = ref(props.filters.budget || '');
const leadDateFrom = ref(props.filters.lead_date_from || '');
const leadDateTo = ref(props.filters.lead_date_to || '');
const leadDateStatus = ref(props.filters.lead_date_status || '');
const followUp = ref(props.filters.follow_up || '');
const handling = ref(props.filters.handling || '');
const crm = ref(props.filters.crm || '');
const csUserId = ref(props.filters.cs_user_id || '');
const showAdvancedFilters = ref(
    Boolean(
        intent.value ||
        budget.value ||
        outcome.value ||
        leadDateFrom.value ||
        leadDateTo.value ||
        leadDateStatus.value ||
        followUp.value ||
        handling.value ||
        crm.value ||
        csUserId.value,
    ),
);
const isFiltering = ref(false);
const assistingLeadId = ref<number | null>(null);
const selectedLeadIds = ref<number[]>([]);
const mobileSelectionMode = ref(false);
const mobileEditorLead = ref<Lead | null>(null);
const mobileQuickUpdate = ref<InstanceType<typeof LeadQuickUpdate> | null>(
    null,
);
const mobileEditorCloseRequested = ref(false);
const selectedSourceBranchIds = computed(() =>
    Array.from(
        new Set(
            props.leads.data
                .filter((lead) => selectedLeadIds.value.includes(lead.id))
                .map((lead) => lead.source_branch_id)
                .filter((branchId): branchId is number => branchId !== null),
        ),
    ),
);
const bulkCsAccounts = computed(() => {
    const [sourceBranchId] = selectedSourceBranchIds.value;

    return selectedSourceBranchIds.value.length === 1
        ? props.csAccounts.filter(
              (account) => account.branch_id === sourceBranchId,
          )
        : selectedSourceBranchIds.value.length > 1
          ? []
          : props.csAccounts;
});
const bulkSelectionMessage = computed(() => {
    if (selectedSourceBranchIds.value.length > 1) {
        return 'Pilih lead dari satu cabang sumber untuk menugaskan CS.';
    }

    if (
        selectedLeadIds.value.some(
            (id) =>
                props.leads.data.find((lead) => lead.id === id)
                    ?.source_branch_id === null,
        )
    ) {
        return 'Lead tanpa cabang sumber dapat ditugaskan ke CS utama terlebih dahulu.';
    }

    return '';
});
const visibleLeadIds = computed(() => props.leads.data.map((lead) => lead.id));
const allVisibleSelected = computed(
    () =>
        visibleLeadIds.value.length > 0 &&
        visibleLeadIds.value.every((id) => selectedLeadIds.value.includes(id)),
);
const someVisibleSelected = computed(
    () =>
        !allVisibleSelected.value &&
        visibleLeadIds.value.some((id) => selectedLeadIds.value.includes(id)),
);
const bulkForm = useForm({ assigned_user_id: '', lead_ids: [] as number[] });
const collaboratorBulkForm = useForm({
    user_id: '',
    lead_ids: [] as number[],
});
const selectionForm = useForm({ lead_ids: [] as number[] });
const archiveDialogOpen = ref(false);
const deleteDialogOpen = ref(false);
const discardChangesDialogOpen = ref(false);
const dirtyLeadIds = ref(new Set<number>());
const leadSaveStates = ref(new Map<number, LeadSaveState>());
const leadRetryVersions = ref(new Map<number, number>());
const pendingDiscardAction = ref<(() => void) | null>(null);
const isDesktopWorkspace = ref(false);
const leadEditStates = ref(new Map<number, LeadEditState>());
const desktopLeadViewport = ref<HTMLElement | null>(null);
const desktopScrollTop = ref(0);
const desktopScrolledHorizontally = ref(false);
const desktopViewportHeight = ref(0);
const desktopRowHeights = new Map<number, number>();
const desktopRowIndexes = new WeakMap<HTMLElement, number>();
const desktopRowElements = new Map<number, HTMLElement>();
const desktopVirtualVersion = ref(0);
const desktopVirtualized = computed(() => props.leads.data.length > 100);
const desktopVirtualMetrics = computed(() => {
    void desktopVirtualVersion.value;
    const offsets = [0];

    props.leads.data.forEach((_, index) => {
        // shortcut: unmeasured rows use 128px until visited; persist indexed heights if list volume outgrows the current operational scale.
        offsets.push(offsets[index] + (desktopRowHeights.get(index) || 128));
    });

    return { offsets, total: offsets.at(-1) || 0 };
});
const desktopVirtualRange = computed(() => {
    const total = props.leads.data.length;
    if (!desktopVirtualized.value) return { start: 0, end: total };

    const { offsets } = desktopVirtualMetrics.value;
    const viewportEnd =
        desktopScrollTop.value + Math.max(desktopViewportHeight.value, 800);
    let firstVisible = 0;
    while (
        firstVisible < total &&
        offsets[firstVisible + 1] < desktopScrollTop.value
    ) {
        firstVisible++;
    }
    let lastVisible = firstVisible;
    while (lastVisible < total && offsets[lastVisible] < viewportEnd) {
        lastVisible++;
    }

    return {
        start: Math.max(0, firstVisible - 6),
        end: Math.min(total, lastVisible + 6),
    };
});
const virtualDesktopLeads = computed(() =>
    props.leads.data
        .slice(desktopVirtualRange.value.start, desktopVirtualRange.value.end)
        .map((lead, offset) => ({
            lead,
            index: desktopVirtualRange.value.start + offset,
        })),
);
const desktopVirtualTop = computed(
    () => desktopVirtualMetrics.value.offsets[desktopVirtualRange.value.start],
);
const desktopVirtualBottom = computed(
    () =>
        desktopVirtualMetrics.value.total -
        desktopVirtualMetrics.value.offsets[desktopVirtualRange.value.end],
);
const desktopVisibleLeadRange = computed(() => {
    const total = props.leads.data.length;
    if (!total) return null;

    if (!desktopVirtualized.value) return { start: 1, end: total };

    const { offsets } = desktopVirtualMetrics.value;
    const viewportEnd =
        desktopScrollTop.value + Math.max(desktopViewportHeight.value, 800);
    let firstVisible = 0;
    while (
        firstVisible < total &&
        offsets[firstVisible + 1] < desktopScrollTop.value
    ) {
        firstVisible++;
    }
    let lastVisible = firstVisible;
    while (lastVisible < total && offsets[lastVisible] < viewportEnd) {
        lastVisible++;
    }

    return {
        start: firstVisible + 1,
        end: Math.max(firstVisible + 1, lastVisible),
    };
});
const importForm = useForm<{
    csv_file: File | null;
    import_mode: 'all_leads' | 'daily_tabs';
    branch_id: string;
}>({
    csv_file: null,
    import_mode: 'all_leads',
    branch_id: '',
});
const newLead = useForm({
    inline: true,
    contact_raw: '',
    name: '',
    phone: '',
    assigned_user_id: '',
    source_date_raw: '',
    respond_time: '',
    imported_cs_name: '',
    imported_keterangan: '',
    raw_kebutuhan: '',
    greeting: '',
    chat_time: '',
    source_description: '',
    campaign_description: '',
    intent_buying: '',
    usage_date: '',
    budget: '',
    keterangan: '',
    next_follow_up_at: '',
    milestone: '',
});
const showingNewLead = ref(false);
const toQuickUpdateFields = (lead: Lead): QuickUpdateFields => ({
    intent_buying: lead.intent_buying || '',
    usage_date: lead.usage_date || '',
    budget: lead.budget || '',
    keterangan: lead.keterangan || '',
    outcome: lead.outcome || '',
    outcome_factor: lead.outcome_factor || '',
    outcome_note: lead.outcome_note || '',
    next_follow_up_at: lead.next_follow_up_at
        ? lead.next_follow_up_at.replace(' ', 'T').slice(0, 16)
        : '',
    crm_taken_over: Boolean(lead.crm_taken_over_at),
    revision: lead.revision,
    milestones: [...lead.milestones].sort(),
});
const leadEditState = (lead: Lead) => {
    const existing = leadEditStates.value.get(lead.id);
    if (existing) return existing;

    const current = toQuickUpdateFields(lead);
    const state = {
        current: reactive(current),
        saved: reactive({ ...current, milestones: [...current.milestones] }),
        saveState: reactive<LeadSaveState>({ state: 'idle', message: '' }),
    };
    leadEditStates.value = new Map(leadEditStates.value).set(lead.id, state);

    return state;
};
const quickUpdateSignature = (values: QuickUpdateFields) =>
    JSON.stringify({ ...values, milestones: [...values.milestones].sort() });
const hasUnsavedChanges = computed(
    () =>
        dirtyLeadIds.value.size > 0 ||
        Array.from(leadEditStates.value.values()).some(
            ({ current, saved }) =>
                quickUpdateSignature(current) !== quickUpdateSignature(saved),
        ),
);
const hasFilters = computed(() =>
    Boolean(
        q.value ||
        branch.value ||
        sourceBranchMissing.value ||
        stage.value ||
        outcome.value ||
        queue.value ||
        intent.value ||
        budget.value ||
        leadDateFrom.value ||
        leadDateTo.value ||
        leadDateStatus.value ||
        followUp.value ||
        handling.value ||
        crm.value ||
        csUserId.value,
    ),
);
const selectedFile = computed(() => {
    if (!importForm.csv_file) return '';

    return `${importForm.csv_file.name} · ${Math.ceil(importForm.csv_file.size / 1024)} KB`;
});
const importHint = computed(() =>
    importForm.import_mode === 'daily_tabs'
        ? 'Unggah XLSX penuh dari spreadsheet monitoring, lalu pilih cabang pemilik data tab 1–31.'
        : 'Gunakan export CSV atau XLSX dari tab All Leads.',
);
const activeFilterText = computed(() =>
    [
        q.value ? `Pencarian: “${q.value}”` : '',
        branch.value ? 'Cabang dipilih' : '',
        sourceBranchMissing.value ? 'Tanpa cabang sumber' : '',
        stage.value ? 'Tahap dipilih' : '',
        outcome.value
            ? outcome.value === 'pending'
                ? 'Hasil akhir belum ada'
                : `Hasil akhir: ${outcomeLabel(outcome.value)}`
            : '',
        queue.value ? 'Queue dipilih' : '',
        intent.value ? `Kebutuhan: ${intent.value}` : '',
        budget.value ? `Budget: ${budget.value}` : '',
        leadDateFrom.value || leadDateTo.value
            ? `Tanggal lead: ${leadDateFrom.value || 'awal'} – ${leadDateTo.value || 'akhir'}`
            : '',
        leadDateStatus.value === 'known'
            ? 'Tanggal lead tercatat'
            : leadDateStatus.value === 'missing'
              ? 'Tanggal lead belum tercatat'
              : '',
        followUp.value ? 'Follow-up dipilih' : '',
        handling.value ? 'Peran penanganan dipilih' : '',
        crm.value ? 'Status CRM dipilih' : '',
        csUserId.value
            ? `CS: ${props.csAccounts.find((account) => String(account.id) === csUserId.value)?.name || 'dipilih'}`
            : '',
    ]
        .filter(Boolean)
        .join(' · '),
);

const guardUnsavedChanges = (action: () => void) => {
    if (!hasUnsavedChanges.value) {
        action();

        return;
    }
    pendingDiscardAction.value = action;
    discardChangesDialogOpen.value = true;
};
const trackDirtyLead = (leadId: number, isDirty: boolean) => {
    const next = new Set(dirtyLeadIds.value);
    if (isDirty) {
        next.add(leadId);
    } else {
        next.delete(leadId);
    }
    dirtyLeadIds.value = next;
};
const trackLeadSaveState = (
    leadId: number,
    state: 'idle' | 'pending' | 'saving' | 'saved' | 'error' | 'conflict',
    message: string,
) => {
    const next = new Map(leadSaveStates.value);
    if (state === 'idle') {
        next.delete(leadId);
    } else {
        next.set(leadId, { state, message });
    }
    leadSaveStates.value = next;
    if (
        mobileEditorCloseRequested.value &&
        mobileEditorLead.value?.id === leadId &&
        (state === 'idle' || state === 'saved')
    ) {
        mobileEditorCloseRequested.value = false;
        mobileEditorLead.value = null;
    }
};
const leadSaveState = (leadId: number) => {
    const persistentState = leadEditStates.value.get(leadId)?.saveState;

    return persistentState?.state !== 'idle'
        ? persistentState
        : leadSaveStates.value.get(leadId);
};
const mobileSaveFeedback = computed(() => {
    const state = mobileEditorLead.value
        ? leadSaveState(mobileEditorLead.value.id)
        : undefined;

    if (state?.state === 'saving') {
        return { message: 'Menyimpan perubahan…', tone: 'text-kgj-gold-deep' };
    }
    if (state?.state === 'pending') {
        return {
            message: 'Pilih faktor Closing untuk melanjutkan.',
            tone: 'text-kgj-gold-deep',
        };
    }
    if (state?.state === 'saved') {
        return { message: 'Tersimpan otomatis', tone: 'text-[#49654e]' };
    }
    if (state?.state === 'error' || state?.state === 'conflict') {
        return {
            message: 'Gagal menyimpan. Periksa isian.',
            tone: 'text-kgj-rose',
        };
    }

    return { message: 'Perubahan tersimpan otomatis', tone: 'text-kgj-muted' };
});
const leadRetryVersion = (leadId: number) =>
    leadRetryVersions.value.get(leadId) || 0;
const retryLeadSave = (leadId: number) => {
    const next = new Map(leadRetryVersions.value);
    next.set(leadId, (next.get(leadId) || 0) + 1);
    leadRetryVersions.value = next;
};
const resolveLeadConflict = (leadId: number) => {
    trackDirtyLead(leadId, false);
    window.location.assign(`/leads/${leadId}`);
};
const discardChangesAndContinue = () => {
    const action = pendingDiscardAction.value;
    dirtyLeadIds.value = new Set();
    leadEditStates.value = new Map();
    pendingDiscardAction.value = null;
    discardChangesDialogOpen.value = false;
    action?.();
};
const applyFiltersNow = () =>
    router.get(
        '/leads',
        {
            q: q.value || undefined,
            branch: branch.value || undefined,
            source_branch_missing: sourceBranchMissing.value || undefined,
            stage: stage.value || undefined,
            outcome: outcome.value || undefined,
            queue: queue.value || undefined,
            intent: intent.value || undefined,
            budget: budget.value || undefined,
            lead_date_from: leadDateFrom.value || undefined,
            lead_date_to: leadDateTo.value || undefined,
            lead_date_status: leadDateStatus.value || undefined,
            follow_up: followUp.value || undefined,
            handling: handling.value || undefined,
            crm: crm.value || undefined,
            cs_user_id: csUserId.value || undefined,
            archived: props.archiveMode ? '1' : undefined,
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
            onStart: () => (isFiltering.value = true),
            onFinish: () => {
                isFiltering.value = false;
                selectedLeadIds.value = [];
            },
        },
    );
const applyFilters = () => guardUnsavedChanges(applyFiltersNow);
const clearFilters = () =>
    guardUnsavedChanges(() => {
        q.value = '';
        branch.value = '';
        sourceBranchMissing.value = '';
        stage.value = '';
        outcome.value = '';
        queue.value = '';
        intent.value = '';
        budget.value = '';
        leadDateFrom.value = '';
        leadDateTo.value = '';
        leadDateStatus.value = '';
        followUp.value = '';
        handling.value = '';
        crm.value = '';
        csUserId.value = '';
        showAdvancedFilters.value = false;
        applyFiltersNow();
    });
const importCsvNow = () =>
    importForm.post('/leads/import', {
        forceFormData: true,
        onSuccess: () => importForm.reset(),
    });
const importCsv = () => guardUnsavedChanges(importCsvNow);
const syncingSheet = ref(false);
const sheetSyncLabel = computed(
    () =>
        ({
            success: 'berhasil',
            partial: 'sebagian berhasil',
            failed: 'gagal',
        })[props.sheetSync?.status || ''] || 'belum diketahui',
);
const syncGoogleSheetNow = () =>
    router.post(
        '/leads/sync-google-sheet',
        {},
        {
            preserveScroll: true,
            onStart: () => (syncingSheet.value = true),
            onFinish: () => (syncingSheet.value = false),
        },
    );
const syncGoogleSheet = () => guardUnsavedChanges(syncGoogleSheetNow);
const selectCsv = (event: Event) => {
    importForm.csv_file = (event.target as HTMLInputElement).files?.[0] || null;
};
const toggleArchiveNow = () =>
    router.get('/leads', { archived: props.archiveMode ? undefined : '1' });
const toggleArchive = () => guardUnsavedChanges(toggleArchiveNow);
const createLeadNow = () =>
    newLead.post('/leads', {
        onSuccess: () => {
            newLead.reset();
            showingNewLead.value = false;
        },
    });
const createLead = () => guardUnsavedChanges(createLeadNow);
const startAssistingNow = (leadId: number) =>
    router.post(
        `/leads/${leadId}/start-assisting`,
        { return_to: `${window.location.pathname}${window.location.search}` },
        {
            preserveScroll: true,
            onStart: () => (assistingLeadId.value = leadId),
            onFinish: () => (assistingLeadId.value = null),
        },
    );
const startAssisting = (leadId: number) =>
    guardUnsavedChanges(() => startAssistingNow(leadId));
const stopAssistingNow = (leadId: number) =>
    router.delete(`/leads/${leadId}/stop-assisting`, {
        data: {
            return_to: `${window.location.pathname}${window.location.search}`,
        },
        preserveScroll: true,
        onStart: () => (assistingLeadId.value = leadId),
        onFinish: () => (assistingLeadId.value = null),
    });
const stopAssisting = (leadId: number) =>
    guardUnsavedChanges(() => stopAssistingNow(leadId));
const stageLabel = (value: string | null) =>
    ({
        clicked: 'Klik saja',
        asked: 'Tanya-Tanya',
        price_list: 'Sampai Pricelist',
        offline: 'Datang Offline',
        online: 'Diskusi Online',
        closing_repeat: 'Closing / Repeat Order',
    })[value || ''];
const handlingLabel = (role: Lead['handling_role']) =>
    ({
        admin: 'Admin',
        primary: 'Milik Anda',
        collaborator: 'Anda membantu',
        team: 'Antrean cabang',
    })[role];
const formatLeadDate = (value: string | null) =>
    value
        ? new Intl.DateTimeFormat('id-ID', {
              day: '2-digit',
              month: 'short',
              year: 'numeric',
          }).format(new Date(`${value}T12:00:00`))
        : '—';
const formatFollowUp = (value: string | null) =>
    value ? new Date(value).toLocaleString('id-ID') : '—';
const assignSelectedNow = () => {
    bulkForm.lead_ids = selectedLeadIds.value;
    bulkForm.post('/leads/assign', {
        onSuccess: () => {
            selectedLeadIds.value = [];
            bulkForm.reset();
        },
    });
};
const assignSelected = () => guardUnsavedChanges(assignSelectedNow);
const addCollaboratorSelectedNow = () => {
    collaboratorBulkForm.lead_ids = selectedLeadIds.value;
    collaboratorBulkForm.post('/leads/collaborators', {
        onSuccess: () => {
            selectedLeadIds.value = [];
            collaboratorBulkForm.reset();
        },
    });
};
const addCollaboratorSelected = () =>
    guardUnsavedChanges(addCollaboratorSelectedNow);
const clearSelection = () => {
    selectedLeadIds.value = [];
    selectionForm.reset();
    bulkForm.reset();
    collaboratorBulkForm.reset();
};
const toggleMobileSelectionMode = () => {
    mobileSelectionMode.value = !mobileSelectionMode.value;
    if (!mobileSelectionMode.value) clearSelection();
};
const openMobileEditor = (lead: Lead) => {
    mobileEditorCloseRequested.value = false;
    mobileEditorLead.value = lead;
};
const setMobileEditorOpen = (open: boolean) => {
    if (open || !mobileEditorLead.value) return;
    if (
        dirtyLeadIds.value.has(mobileEditorLead.value.id) &&
        mobileQuickUpdate.value
    ) {
        mobileEditorCloseRequested.value = true;
        mobileQuickUpdate.value.flushPendingSave();

        return;
    }
    mobileEditorLead.value = null;
};
const toggleVisibleSelection = () => {
    if (allVisibleSelected.value) {
        selectedLeadIds.value = selectedLeadIds.value.filter(
            (id) => !visibleLeadIds.value.includes(id),
        );

        return;
    }
    selectedLeadIds.value = Array.from(
        new Set([...selectedLeadIds.value, ...visibleLeadIds.value]),
    ).slice(0, 100);
};
const archiveSelected = () => {
    archiveDialogOpen.value = false;
    selectionForm.lead_ids = selectedLeadIds.value;
    selectionForm.post('/leads/archive', { onSuccess: clearSelection });
};
const openArchiveDialog = () =>
    guardUnsavedChanges(() => (archiveDialogOpen.value = true));
const restoreSelectedNow = () => {
    selectionForm.lead_ids = selectedLeadIds.value;
    selectionForm.post('/leads/restore', { onSuccess: clearSelection });
};
const restoreSelected = () => guardUnsavedChanges(restoreSelectedNow);
const destroySelected = () => {
    deleteDialogOpen.value = false;
    selectionForm.lead_ids = selectedLeadIds.value;
    selectionForm.delete('/leads', { onSuccess: clearSelection });
};
const openDeleteDialog = () =>
    guardUnsavedChanges(() => (deleteDialogOpen.value = true));

const beforeUnload = (event: BeforeUnloadEvent) => {
    if (!hasUnsavedChanges.value) {
        return;
    }
    event.preventDefault();
    event.returnValue = '';
};
let removeNavigationGuard: (() => void) | undefined;
let desktopBreakpoint: MediaQueryList | undefined;
const syncWorkspaceLayout = () => {
    if (!hasUnsavedChanges.value && desktopBreakpoint) {
        isDesktopWorkspace.value = desktopBreakpoint.matches;
    }
};
let desktopRowResizeObserver: ResizeObserver | undefined;
let desktopViewportResizeObserver: ResizeObserver | undefined;
const createDesktopRowResizeObserver = () => {
    desktopRowResizeObserver = new ResizeObserver((entries) => {
        let changed = false;
        entries.forEach((entry) => {
            const index = desktopRowIndexes.get(entry.target as HTMLElement);
            const height = Math.ceil(entry.contentRect.height);
            if (index === undefined || height <= 0) return;
            if (desktopRowHeights.get(index) === height) return;
            desktopRowHeights.set(index, height);
            changed = true;
        });
        if (changed) desktopVirtualVersion.value++;
    });
};
const updateDesktopViewport = () => {
    desktopViewportHeight.value = desktopLeadViewport.value?.clientHeight || 0;
};
const observeDesktopLeadRow = (index: number, element: unknown) => {
    const previous = desktopRowElements.get(index);
    if (previous && previous !== element) {
        desktopRowResizeObserver?.unobserve(previous);
        desktopRowElements.delete(index);
    }
    if (!(element instanceof HTMLElement)) return;

    desktopRowElements.set(index, element);
    desktopRowIndexes.set(element, index);
    desktopRowResizeObserver?.observe(element);
};
const onDesktopTableScroll = (event: Event) => {
    const viewport = event.currentTarget as HTMLElement;
    desktopScrollTop.value = viewport.scrollTop;
    desktopScrolledHorizontally.value = viewport.scrollLeft > 0;
};
const resetDesktopVirtualRows = () => {
    desktopRowResizeObserver?.disconnect();
    desktopRowResizeObserver = undefined;
    desktopRowElements.clear();
    desktopRowHeights.clear();
    desktopVirtualVersion.value++;
    desktopScrollTop.value = 0;
    desktopScrolledHorizontally.value = false;
    createDesktopRowResizeObserver();
    nextTick(() => {
        if (desktopLeadViewport.value) {
            desktopLeadViewport.value.scrollTop = 0;
        }
    });
};
const observeDesktopViewport = () => {
    desktopViewportResizeObserver?.disconnect();
    if (!desktopLeadViewport.value) return;

    desktopViewportResizeObserver = new ResizeObserver(updateDesktopViewport);
    desktopViewportResizeObserver.observe(desktopLeadViewport.value);
    updateDesktopViewport();
};
watch(hasUnsavedChanges, (isDirty) => {
    if (!isDirty) {
        syncWorkspaceLayout();
    }
});
watch(
    () => importForm.import_mode,
    (mode) => {
        if (mode === 'all_leads') importForm.branch_id = '';
    },
);
watch(
    () => props.leads.data,
    () => {
        leadEditStates.value = new Map();
        resetDesktopVirtualRows();
    },
);
watch(isDesktopWorkspace, () => {
    desktopScrollTop.value = 0;
    nextTick(observeDesktopViewport);
});
onMounted(() => {
    createDesktopRowResizeObserver();
    desktopBreakpoint = window.matchMedia('(min-width: 1280px)');
    desktopBreakpoint.addEventListener('change', syncWorkspaceLayout);
    syncWorkspaceLayout();
    window.addEventListener('beforeunload', beforeUnload);
    removeNavigationGuard = router.on('before', (event) => {
        if (event.detail.visit.method !== 'get' || !hasUnsavedChanges.value) {
            return;
        }
        const href = event.detail.visit.url.toString();
        guardUnsavedChanges(() => window.location.assign(href));

        return false;
    });
});
onBeforeUnmount(() => {
    window.removeEventListener('beforeunload', beforeUnload);
    desktopBreakpoint?.removeEventListener('change', syncWorkspaceLayout);
    desktopRowResizeObserver?.disconnect();
    desktopViewportResizeObserver?.disconnect();
    removeNavigationGuard?.();
});
</script>

<template>
    <Head title="Leads" />

    <section class="kgj-page">
        <div
            class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end"
        >
            <div>
                <p class="kgj-eyebrow">Workspace harian</p>
                <h1
                    class="mt-2 text-4xl font-semibold tracking-[-0.04em] sm:text-5xl"
                >
                    Leads.
                </h1>
                <p class="kgj-subtitle">Daftar kerja sesuai akses akun Anda.</p>
            </div>
            <div class="flex items-center gap-3 text-sm">
                <button
                    class="text-sm font-semibold text-kgj-gold-deep underline decoration-[#c4a45d] underline-offset-4"
                    type="button"
                    @click="toggleArchive"
                >
                    {{ archiveMode ? 'Kembali ke aktif' : 'Lihat arsip' }}
                </button>
                <button
                    v-if="!archiveMode"
                    class="kgj-button-primary"
                    type="button"
                    @click="showingNewLead = !showingNewLead"
                >
                    {{ showingNewLead ? 'Tutup' : 'Tambah lead' }}
                </button>
                <p class="text-[#62665b]">
                    <strong class="text-[#22231f]">{{ leads.total }}</strong>
                    lead {{ archiveMode ? 'arsip' : 'ditampilkan' }}
                </p>
            </div>
        </div>

        <form
            v-if="showingNewLead"
            class="kgj-surface mt-6 grid gap-4 p-4 sm:p-5 lg:grid-cols-2"
            @submit.prevent="createLead"
        >
            <label class="sr-only" for="new-lead-contact"
                >Nama atau nomor lead</label
            >
            <input
                id="new-lead-contact"
                v-model="newLead.contact_raw"
                class="kgj-input min-w-[220px] flex-1"
                placeholder="Nama / nomor WhatsApp"
                required
                :aria-invalid="Boolean(newLead.errors.contact_raw)"
            />
            <button
                class="kgj-button-gold shrink-0"
                :disabled="newLead.processing"
            >
                {{ newLead.processing ? 'Menyimpan…' : 'Simpan' }}
            </button>
            <p
                v-if="newLead.errors.contact_raw"
                class="basis-full text-sm text-[#a23e2b]"
                role="alert"
            >
                {{ newLead.errors.contact_raw }}
            </p>
            <details
                class="col-span-full rounded-xl border border-kgj-line bg-[#fffdf8] p-4"
            >
                <summary
                    class="cursor-pointer text-sm font-semibold text-kgj-gold-deep"
                >
                    Tambahkan data sumber dan kualifikasi
                </summary>
                <p class="mt-2 text-sm text-[#62665b]">
                    Opsional untuk lead baru; gunakan saat data berasal dari
                    chat, spreadsheet, atau kampanye.
                </p>
                <div class="mt-5 grid gap-4 lg:grid-cols-2">
                    <label class="grid gap-2 text-sm font-semibold"
                        >Nama lead<input
                            v-model="newLead.name"
                            class="kgj-input"
                    /></label>
                    <label class="grid gap-2 text-sm font-semibold"
                        >Nomor WhatsApp<input
                            v-model="newLead.phone"
                            class="kgj-input"
                    /></label>
                    <label
                        v-if="isAdmin"
                        class="grid gap-2 text-sm font-semibold"
                        >CS penanggung jawab<select
                            v-model="newLead.assigned_user_id"
                            class="kgj-input"
                        >
                            <option value="">Belum ditugaskan</option>
                            <option
                                v-for="account in csAccounts"
                                :key="account.id"
                                :value="String(account.id)"
                            >
                                {{ account.branch_name }} — {{ account.name }}
                            </option>
                        </select></label
                    >
                    <p
                        v-else
                        class="rounded-lg border border-kgj-line bg-kgj-canvas px-3 py-3 text-sm leading-5 text-[#4f514a]"
                    >
                        Lead ini otomatis menjadi tanggung jawab Anda.
                    </p>
                    <label class="grid gap-2 text-sm font-semibold"
                        >Tanggal lead<input
                            v-model="newLead.source_date_raw"
                            type="date"
                            class="kgj-input"
                    /></label>
                    <label class="grid gap-2 text-sm font-semibold"
                        >Respond time<input
                            v-model="newLead.respond_time"
                            class="kgj-input"
                            placeholder="Contoh: +-1 jam"
                    /></label>
                    <label class="grid gap-2 text-sm font-semibold"
                        >Nama CS di sumber<input
                            v-model="newLead.imported_cs_name"
                            class="kgj-input"
                    /></label>
                    <label class="grid gap-2 text-sm font-semibold"
                        >Waktu chat<input
                            v-model="newLead.chat_time"
                            class="kgj-input"
                            placeholder="Contoh: 10.38"
                    /></label>
                    <label class="grid gap-2 text-sm font-semibold"
                        >Sumber<select
                            v-model="newLead.source_description"
                            class="kgj-input"
                        >
                            <option value="">Pilih sumber</option>
                            <option>Iklan</option>
                            <option>Instagram</option>
                            <option>Google</option>
                            <option>Website</option>
                            <option>Organik</option>
                            <option>Teman</option>
                            <option>Lainnya</option>
                        </select></label
                    >
                    <label class="grid gap-2 text-sm font-semibold"
                        >Campaign<input
                            v-model="newLead.campaign_description"
                            class="kgj-input"
                            placeholder="Contoh: Bio atau DM"
                    /></label>
                    <label class="grid gap-2 text-sm font-semibold"
                        >Keterangan awal<input
                            v-model="newLead.imported_keterangan"
                            class="kgj-input"
                            placeholder="Contoh: tanya-tanya"
                    /></label>
                    <label class="grid gap-2 text-sm font-semibold"
                        >Kebutuhan awal<input
                            v-model="newLead.raw_kebutuhan"
                            class="kgj-input"
                            placeholder="Contoh: cincin lamaran"
                    /></label>
                    <label class="grid gap-2 text-sm font-semibold"
                        >Tahap awal<select
                            v-model="newLead.milestone"
                            class="kgj-input"
                        >
                            <option value="">Belum dikualifikasi</option>
                            <option value="clicked">Klik saja</option>
                            <option value="asked">Tanya-Tanya</option>
                            <option value="price_list">Sampai Pricelist</option>
                            <option value="offline">Datang Offline</option>
                            <option value="online">Diskusi Online</option>
                            <option value="closing_repeat">
                                Closing / Repeat Order
                            </option>
                        </select></label
                    >
                    <label class="grid gap-2 text-sm font-semibold"
                        >Intent Buying<select
                            v-model="newLead.intent_buying"
                            class="kgj-input"
                        >
                            <option value="">Pilih kebutuhan</option>
                            <option>Tunangan</option>
                            <option>Lamaran</option>
                            <option>Nikahan</option>
                            <option>Anniversary</option>
                            <option>Lainnya</option>
                        </select></label
                    >
                    <label class="grid gap-2 text-sm font-semibold"
                        >Waktu pemakaian<input
                            v-model="newLead.usage_date"
                            type="date"
                            class="kgj-input"
                    /></label>
                    <label class="grid gap-2 text-sm font-semibold"
                        >Budget<select
                            v-model="newLead.budget"
                            class="kgj-input"
                        >
                            <option value="">Pilih budget</option>
                            <option
                                v-for="option in budgetOptions"
                                :key="option"
                                :value="option"
                            >
                                {{ option }}
                            </option>
                        </select></label
                    >
                    <label class="grid gap-2 text-sm font-semibold"
                        >Tindak lanjut berikutnya<input
                            v-model="newLead.next_follow_up_at"
                            type="datetime-local"
                            class="kgj-input"
                    /></label>
                    <label
                        class="grid gap-2 text-sm font-semibold lg:col-span-2"
                        >Greeting<textarea
                            v-model="newLead.greeting"
                            rows="3"
                            class="kgj-input"
                        />
                    </label>
                    <label
                        class="grid gap-2 text-sm font-semibold lg:col-span-2"
                        >Catatan internal<textarea
                            v-model="newLead.keterangan"
                            rows="3"
                            class="kgj-input"
                        />
                    </label>
                </div>
            </details>
        </form>

        <div
            class="kgj-surface mt-7 grid gap-3 p-4 lg:grid-cols-[1fr_auto_auto_auto_auto]"
        >
            <form class="flex gap-2" @submit.prevent="applyFilters">
                <label class="sr-only" for="lead-search">Cari lead</label
                ><input
                    id="lead-search"
                    v-model="q"
                    class="kgj-input w-full"
                    placeholder="Cari nama atau nomor"
                /><button
                    class="kgj-button-primary shrink-0"
                    type="submit"
                    :disabled="isFiltering"
                >
                    {{ isFiltering ? 'Memuat…' : 'Cari' }}
                </button>
            </form>
            <label v-if="isAdmin" class="text-sm font-medium"
                ><span class="sr-only">Filter cabang</span
                ><select
                    v-model="branch"
                    class="kgj-input w-full"
                    :disabled="isFiltering"
                    @change="applyFilters"
                >
                    <option value="">Semua cabang</option>
                    <option
                        v-for="item in branches"
                        :key="item.id"
                        :value="String(item.id)"
                    >
                        {{ item.name }}
                    </option>
                </select></label
            >
            <label class="text-sm font-medium"
                ><span class="sr-only">Filter tahap lead</span
                ><select
                    v-model="stage"
                    class="kgj-input w-full"
                    :disabled="isFiltering"
                    @change="applyFilters"
                >
                    <option value="">Semua tahap</option>
                    <option value="clicked">Klik saja</option>
                    <option value="asked">Tanya-Tanya</option>
                    <option value="price_list">Sampai Pricelist</option>
                    <option value="offline">Datang Offline</option>
                    <option value="online">Diskusi Online</option>
                    <option value="closing_repeat">
                        Closing / Repeat Order
                    </option>
                </select></label
            >
            <button
                class="kgj-button-secondary inline-flex items-center justify-center gap-2"
                type="button"
                :aria-expanded="showAdvancedFilters"
                aria-controls="lead-advanced-filters"
                @click="showAdvancedFilters = !showAdvancedFilters"
            >
                <SlidersHorizontal class="size-4" aria-hidden="true" />
                Filter lengkap
                <ChevronUp
                    v-if="showAdvancedFilters"
                    class="size-4"
                    aria-hidden="true"
                />
                <ChevronDown v-else class="size-4" aria-hidden="true" />
            </button>
            <label v-if="isAdmin" class="text-sm font-medium"
                ><span class="sr-only">Queue kerja admin</span
                ><select
                    v-model="queue"
                    class="kgj-input w-full"
                    :disabled="isFiltering"
                    @change="applyFilters"
                >
                    <option value="">Semua queue</option>
                    <option value="unassigned">Belum ditugaskan</option>
                    <option value="needs_qualification">
                        Perlu kualifikasi
                    </option>
                    <option value="follow_up_due">Follow-up jatuh tempo</option>
                    <option value="crm_taken_over">Diambil alih CRM</option>
                    <option value="source_conflict">Nomor lintas cabang</option>
                </select></label
            >
            <form
                v-if="isAdmin"
                class="flex flex-wrap items-center gap-2"
                @submit.prevent="importCsv"
            >
                <label class="sr-only" for="import-mode">Sumber import</label
                ><select
                    id="import-mode"
                    v-model="importForm.import_mode"
                    class="kgj-input min-h-11 max-w-44 text-sm"
                    :disabled="importForm.processing"
                >
                    <option value="all_leads">All Leads</option>
                    <option value="daily_tabs">Tab harian</option>
                </select>
                <label
                    v-if="importForm.import_mode === 'daily_tabs'"
                    class="sr-only"
                    for="import-branch"
                    >Cabang sumber</label
                >
                <select
                    v-if="importForm.import_mode === 'daily_tabs'"
                    id="import-branch"
                    v-model="importForm.branch_id"
                    class="kgj-input min-h-11 max-w-44 text-sm"
                    :disabled="importForm.processing"
                >
                    <option value="">Cabang sumber</option>
                    <option
                        v-for="item in branches"
                        :key="item.id"
                        :value="String(item.id)"
                    >
                        {{ item.name }}
                    </option>
                </select>
                <label
                    class="inline-flex min-h-11 cursor-pointer items-center rounded-lg border border-[#c9aa6a] bg-white px-3 py-3 text-sm font-semibold text-kgj-gold-deep transition hover:bg-[#fbf6e8]"
                    >{{ importForm.csv_file?.name || 'Pilih CSV / XLSX'
                    }}<input
                        class="sr-only"
                        type="file"
                        accept=".csv,.xlsx,text/csv,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
                        @change="selectCsv" /></label
                ><button
                    class="kgj-button-gold"
                    :disabled="!importForm.csv_file || importForm.processing"
                >
                    {{ importForm.processing ? 'Mengimpor…' : 'Import' }}
                </button>
                <button
                    class="kgj-button-secondary"
                    type="button"
                    :disabled="syncingSheet"
                    @click="syncGoogleSheet"
                >
                    {{ syncingSheet ? 'Menyinkronkan…' : 'Sinkronkan Sheet' }}
                </button>
            </form>
        </div>
        <section
            v-if="showAdvancedFilters"
            id="lead-advanced-filters"
            class="kgj-surface mt-3 grid gap-3 p-4 sm:grid-cols-2 xl:grid-cols-4"
            aria-label="Filter lead lengkap"
        >
            <label class="grid gap-2 text-sm font-semibold"
                >Kebutuhan
                <select
                    v-model="intent"
                    class="kgj-input"
                    :disabled="isFiltering"
                    @change="applyFilters"
                >
                    <option value="">Semua kebutuhan</option>
                    <option>Tunangan</option>
                    <option>Lamaran</option>
                    <option>Nikahan</option>
                    <option>Anniversary</option>
                    <option>Lainnya</option>
                </select>
            </label>
            <label class="grid gap-2 text-sm font-semibold"
                >Budget
                <select
                    v-model="budget"
                    class="kgj-input"
                    :disabled="isFiltering"
                    @change="applyFilters"
                >
                    <option value="">Semua budget</option>
                    <option
                        v-for="option in budgetOptions"
                        :key="option"
                        :value="option"
                    >
                        {{ option }}
                    </option>
                </select>
            </label>
            <label class="grid gap-2 text-sm font-semibold"
                >Hasil akhir
                <select
                    v-model="outcome"
                    class="kgj-input"
                    :disabled="isFiltering"
                    @change="applyFilters"
                >
                    <option value="">Semua hasil akhir</option>
                    <option value="pending">Belum ada hasil akhir</option>
                    <option value="closing">Closing</option>
                    <option value="loss">Loss</option>
                </select>
            </label>
            <label class="grid gap-2 text-sm font-semibold"
                >Tanggal lead dari
                <input
                    v-model="leadDateFrom"
                    class="kgj-input"
                    type="date"
                    :disabled="isFiltering"
                    @change="applyFilters"
                />
            </label>
            <label class="grid gap-2 text-sm font-semibold"
                >Tanggal lead sampai
                <input
                    v-model="leadDateTo"
                    class="kgj-input"
                    type="date"
                    :disabled="isFiltering"
                    @change="applyFilters"
                />
            </label>
            <label class="grid gap-2 text-sm font-semibold"
                >Status tanggal lead
                <select
                    v-model="leadDateStatus"
                    class="kgj-input"
                    :disabled="isFiltering"
                    @change="applyFilters"
                >
                    <option value="">Semua tanggal</option>
                    <option value="known">Tanggal tercatat</option>
                    <option value="missing">Tanggal belum tercatat</option>
                </select>
            </label>
            <label class="grid gap-2 text-sm font-semibold"
                >Follow-up
                <select
                    v-model="followUp"
                    class="kgj-input"
                    :disabled="isFiltering"
                    @change="applyFilters"
                >
                    <option value="">Semua follow-up</option>
                    <option value="scheduled">Sudah dijadwalkan</option>
                    <option value="none">Belum dijadwalkan</option>
                    <option value="today">Hari ini</option>
                    <option value="tomorrow">Besok</option>
                    <option value="overdue">Jatuh tempo</option>
                </select>
            </label>
            <label v-if="!isAdmin" class="grid gap-2 text-sm font-semibold"
                >Peran saya
                <select
                    v-model="handling"
                    class="kgj-input"
                    :disabled="isFiltering"
                    @change="applyFilters"
                >
                    <option value="">Semua lead saya</option>
                    <option value="primary">Milik saya</option>
                    <option value="collaborator">Saya bantu</option>
                    <option value="team">Tim cabang</option>
                </select>
            </label>
            <label class="grid gap-2 text-sm font-semibold"
                >Nama CS
                <select
                    v-model="csUserId"
                    class="kgj-input"
                    :disabled="isFiltering"
                    @change="applyFilters"
                >
                    <option value="">Semua CS</option>
                    <option
                        v-for="account in csAccounts"
                        :key="account.id"
                        :value="String(account.id)"
                    >
                        {{ account.name }} — {{ account.branch_name }}
                    </option>
                </select>
            </label>
            <label class="grid gap-2 text-sm font-semibold"
                >Status CRM
                <select
                    v-model="crm"
                    class="kgj-input"
                    :disabled="isFiltering"
                    @change="applyFilters"
                >
                    <option value="">Semua status CRM</option>
                    <option value="not_taken_over">Belum diambil CRM</option>
                    <option value="taken_over">Diambil alih CRM</option>
                </select>
            </label>
        </section>
        <div
            class="mt-3 flex flex-wrap items-center gap-x-4 gap-y-2 text-sm text-[#62665b]"
            aria-live="polite"
        >
            <span v-if="hasFilters">Filter aktif: {{ activeFilterText }}</span>
            <button
                v-if="hasFilters"
                class="font-semibold text-[#6c511e] underline underline-offset-4"
                type="button"
                @click="clearFilters"
            >
                Hapus filter
            </button>
            <span v-if="selectedFile">Siap diimpor: {{ selectedFile }}</span>
            <span v-if="isAdmin && !selectedFile">{{ importHint }}</span>
            <span v-if="isAdmin && sheetSync"
                >Sinkronisasi terakhir:
                {{ sheetSyncLabel }}
                {{
                    sheetSync.completed_at
                        ? `· ${new Date(sheetSync.completed_at).toLocaleString('id-ID')}`
                        : ''
                }}</span
            >
            <span
                v-if="sheetSync?.summary?.missing_source_fields"
                class="text-[#a23e2b]"
                >{{ sheetSync.summary.missing_source_fields }} kolom sumber
                kosong</span
            >
        </div>
        <p
            v-if="importForm.errors.csv_file"
            class="mt-2 text-sm text-[#a23e2b]"
            role="alert"
        >
            {{ importForm.errors.csv_file }}
        </p>
        <p
            v-if="importForm.errors.branch_id"
            class="mt-2 text-sm text-[#a23e2b]"
            role="alert"
        >
            {{ importForm.errors.branch_id }}
        </p>
        <div
            v-if="visibleLeadIds.length && !isDesktopWorkspace"
            class="mt-5 flex flex-wrap items-center justify-between gap-3"
        >
            <p class="text-sm text-kgj-muted">
                <strong class="text-kgj-ink">{{ leads.data.length }}</strong>
                lead ditampilkan
            </p>
            <button
                class="kgj-button-secondary"
                type="button"
                @click="toggleMobileSelectionMode"
            >
                {{
                    mobileSelectionMode
                        ? 'Selesai memilih'
                        : 'Pilih beberapa lead'
                }}
            </button>
            <label
                v-if="mobileSelectionMode"
                class="flex min-h-11 basis-full items-center gap-2 text-sm font-semibold text-[#4f514a]"
            >
                <input
                    :checked="allVisibleSelected"
                    :indeterminate="someVisibleSelected"
                    class="size-4 accent-kgj-gold"
                    type="checkbox"
                    @change="toggleVisibleSelection"
                />
                Pilih hingga 100 dari {{ visibleLeadIds.length }} lead
            </label>
            <p
                v-if="mobileSelectionMode"
                class="basis-full text-sm text-kgj-muted"
                role="status"
            >
                Mode pilih aktif. Pilih lead lalu gunakan aksi massal.
            </p>
        </div>
        <div
            v-if="visibleLeadIds.length && isDesktopWorkspace"
            class="mt-4 flex flex-wrap items-center gap-3"
        >
            <label
                class="flex min-h-11 items-center gap-2 text-sm font-semibold text-[#4f514a]"
            >
                <input
                    :checked="allVisibleSelected"
                    :indeterminate="someVisibleSelected"
                    class="size-4 accent-kgj-gold"
                    type="checkbox"
                    @change="toggleVisibleSelection"
                />
                Pilih hingga 100 dari {{ visibleLeadIds.length }} lead
            </label>
        </div>
        <form
            v-if="selectedLeadIds.length"
            class="kgj-surface mt-4 flex flex-col gap-3 border border-[#dccd9f] p-4 sm:flex-row sm:items-center"
        >
            <p class="text-sm font-semibold">
                {{ selectedLeadIds.length }} lead dipilih
            </p>
            <template v-if="!archiveMode">
                <label v-if="isAdmin" class="sr-only" for="bulk-assignee"
                    >CS penanggung jawab</label
                >
                <select
                    v-if="isAdmin"
                    id="bulk-assignee"
                    v-model="bulkForm.assigned_user_id"
                    class="kgj-input sm:ml-auto sm:max-w-xs"
                    required
                >
                    <option value="">Pilih CS penanggung jawab</option>
                    <option
                        v-for="account in bulkCsAccounts"
                        :key="account.id"
                        :value="String(account.id)"
                    >
                        {{ account.branch_name }} — {{ account.name }}
                    </option>
                </select>
                <button
                    v-if="isAdmin"
                    class="kgj-button-gold"
                    type="button"
                    :disabled="
                        bulkForm.processing ||
                        !bulkForm.assigned_user_id ||
                        bulkCsAccounts.length === 0
                    "
                    @click="assignSelected"
                >
                    {{ bulkForm.processing ? 'Menugaskan…' : 'Tugaskan' }}
                </button>
                <label v-if="isAdmin" class="sr-only" for="bulk-collaborator"
                    >CS pendamping</label
                ><select
                    v-if="isAdmin"
                    id="bulk-collaborator"
                    v-model="collaboratorBulkForm.user_id"
                    class="kgj-input sm:max-w-xs"
                >
                    <option value="">Pilih CS pendamping</option>
                    <option
                        v-for="account in bulkCsAccounts"
                        :key="account.id"
                        :value="String(account.id)"
                    >
                        {{ account.branch_name }} — {{ account.name }}
                    </option>
                </select>
                <button
                    v-if="isAdmin"
                    class="kgj-button-secondary"
                    type="button"
                    :disabled="
                        collaboratorBulkForm.processing ||
                        !collaboratorBulkForm.user_id ||
                        bulkCsAccounts.length === 0 ||
                        Boolean(bulkSelectionMessage)
                    "
                    @click="addCollaboratorSelected"
                >
                    {{
                        collaboratorBulkForm.processing
                            ? 'Menambahkan…'
                            : 'Tambah pendamping'
                    }}
                </button>
                <button
                    class="kgj-button-secondary"
                    type="button"
                    :disabled="selectionForm.processing"
                    @click="openArchiveDialog"
                >
                    Arsipkan
                </button>
            </template>
            <template v-else>
                <button
                    class="kgj-button-primary sm:ml-auto"
                    type="button"
                    :disabled="selectionForm.processing"
                    @click="restoreSelected"
                >
                    {{ selectionForm.processing ? 'Memproses…' : 'Pulihkan' }}
                </button>
                <button
                    class="inline-flex min-h-11 items-center justify-center rounded-lg border border-[#d8aaa2] px-3 text-sm font-semibold text-kgj-rose transition hover:bg-[#fff3f1]"
                    type="button"
                    :disabled="selectionForm.processing"
                    @click="openDeleteDialog"
                >
                    Hapus permanen
                </button>
            </template>
            <button
                class="kgj-button-secondary"
                type="button"
                @click="clearSelection"
            >
                Batal
            </button>
            <p
                v-if="isAdmin && bulkSelectionMessage"
                class="basis-full text-sm text-[#865124]"
                role="status"
            >
                {{ bulkSelectionMessage }}
            </p>
        </form>

        <div v-if="!isDesktopWorkspace" class="mt-3 grid gap-3">
            <!-- shortcut: CSS skips offscreen paint while keeping inline editor state mounted; use a state-backed virtual list when lead volume makes interaction slow. -->
            <article
                v-for="lead in leads.data"
                :key="lead.id"
                class="rounded-2xl border border-kgj-line bg-kgj-paper p-4 shadow-[0_8px_22px_rgba(58,48,31,0.06)] [contain-intrinsic-size:auto_18rem] [content-visibility:auto]"
            >
                <div class="flex items-start gap-3">
                    <label
                        v-if="mobileSelectionMode"
                        class="mt-1 flex size-6 shrink-0 items-center justify-center"
                    >
                        <input
                            v-model="selectedLeadIds"
                            class="size-4 accent-kgj-gold"
                            type="checkbox"
                            :aria-label="`Pilih ${lead.name || lead.contact_raw}`"
                            :disabled="
                                !selectedLeadIds.includes(lead.id) &&
                                selectedLeadIds.length >= 100
                            "
                            :value="lead.id"
                        />
                    </label>
                    <div class="min-w-0 flex-1">
                        <div class="block">
                            <strong class="block truncate text-base">{{
                                lead.name || lead.contact_raw
                            }}</strong>
                            <span
                                class="mt-1 block truncate text-sm text-kgj-muted"
                                >{{ lead.phone || lead.contact_raw }}</span
                            >
                        </div>
                    </div>
                    <span
                        class="shrink-0 text-right text-xs font-semibold tracking-wide text-[#8a6b2d] uppercase"
                        >{{
                            stageLabel(lead.current_stage) ||
                            lead.intent_buying ||
                            lead.imported_definition ||
                            'Belum dikualifikasi'
                        }}</span
                    >
                </div>

                <dl class="mt-4 grid grid-cols-2 gap-x-4 gap-y-3 text-sm">
                    <div>
                        <dt class="text-xs text-kgj-muted">Tanggal lead</dt>
                        <dd class="mt-1 font-medium">
                            {{ formatLeadDate(lead.lead_created_on) }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-xs text-kgj-muted">Nama CS</dt>
                        <dd
                            class="mt-1 truncate font-medium"
                            :title="ownerTitle(lead)"
                        >
                            {{ ownerLabel(lead) }}
                        </dd>
                    </div>
                    <div v-if="!isAdmin">
                        <dt class="text-xs text-kgj-muted">Penanganan</dt>
                        <dd class="mt-1 font-medium text-[#665a43]">
                            {{ handlingLabel(lead.handling_role) }}
                        </dd>
                    </div>
                    <div v-if="lead.next_follow_up_at">
                        <dt class="text-xs text-kgj-muted">Follow-up</dt>
                        <dd class="mt-1 font-medium">
                            {{ formatFollowUp(lead.next_follow_up_at) }}
                        </dd>
                    </div>
                </dl>

                <div class="mt-3 flex flex-wrap gap-2 text-xs font-semibold">
                    <span
                        v-if="lead.cs_filter_match"
                        class="rounded-full bg-[#fbf6e8] px-2 py-1 text-kgj-gold-deep"
                        >Cocok sebagai: {{ lead.cs_filter_match }}</span
                    >
                    <span
                        v-if="lead.collaborators.length"
                        class="rounded-full bg-[#f1eee7] px-2 py-1 text-[#665a43]"
                        >Pendamping:
                        {{
                            lead.collaborators
                                .map((item) => item.name)
                                .join(', ')
                        }}</span
                    >
                    <span
                        v-if="lead.crm_taken_over_at"
                        class="rounded-full bg-[#fbf6e8] px-2 py-1 text-[#8a6b2d]"
                        >Diambil alih CRM</span
                    >
                    <span
                        v-if="outcomeLabel(lead.outcome)"
                        class="rounded-full bg-[#e7f0e8] px-2 py-1 text-[#49654e]"
                        >Hasil: {{ outcomeLabel(lead.outcome) }}</span
                    >
                    <span
                        v-if="lead.source_conflict_at"
                        :class="
                            isAdmin
                                ? 'bg-[#fff4df] text-kgj-gold-deep'
                                : 'bg-[#f3f0e8] text-[#665a43]'
                        "
                        :title="
                            isAdmin
                                ? `Perlu review: nomor juga tercatat di ${lead.source_conflict_branch_name || 'cabang lain'}.`
                                : `Nomor juga tercatat di ${lead.source_conflict_branch_name || 'cabang lain'}. Sumber dan penanganan Anda tidak berubah.`
                        "
                        class="rounded-full px-2 py-1"
                        >{{
                            isAdmin
                                ? 'Perlu review: juga di'
                                : 'Nomor juga tercatat di'
                        }}
                        {{
                            lead.source_conflict_branch_name || 'cabang lain'
                        }}</span
                    >
                </div>

                <LeadSaveFeedback
                    :state="leadSaveState(lead.id)?.state"
                    :message="leadSaveState(lead.id)?.message"
                    @retry="retryLeadSave(lead.id)"
                    @resolve-conflict="resolveLeadConflict(lead.id)"
                />

                <div
                    v-if="
                        !archiveMode &&
                        !isAdmin &&
                        lead.handling_role === 'team'
                    "
                    class="mt-4 rounded-xl bg-[#fbf6e8] p-3 text-sm text-[#665a43]"
                >
                    <p>
                        Lead antrean cabang. Mulai bantu untuk dapat
                        memperbarui.
                    </p>
                    <button
                        class="kgj-button-gold mt-3"
                        type="button"
                        :disabled="assistingLeadId === lead.id"
                        @click="startAssisting(lead.id)"
                    >
                        {{
                            assistingLeadId === lead.id
                                ? 'Memulai...'
                                : 'Mulai bantu'
                        }}
                    </button>
                </div>
                <div
                    v-else-if="!archiveMode"
                    class="mt-4 flex flex-wrap items-center gap-3"
                >
                    <button
                        class="kgj-button-primary"
                        type="button"
                        @click="openMobileEditor(lead)"
                    >
                        <Pencil class="size-4" /> Update lead
                    </button>
                    <button
                        v-if="!isAdmin && lead.handling_role === 'collaborator'"
                        class="min-h-11 text-sm font-semibold text-[#62665b] underline underline-offset-4"
                        type="button"
                        :disabled="assistingLeadId === lead.id"
                        @click="stopAssisting(lead.id)"
                    >
                        {{
                            assistingLeadId === lead.id
                                ? 'Memproses...'
                                : 'Selesai membantu'
                        }}
                    </button>
                    <Link
                        :href="`/leads/${lead.id}`"
                        class="min-h-11 py-3 text-sm font-semibold text-[#62665b] underline underline-offset-4"
                        >Detail lengkap</Link
                    >
                </div>
                <Link
                    v-else
                    :href="`/leads/${lead.id}`"
                    class="mt-4 block min-h-11 py-3 text-sm font-semibold text-[#62665b] underline underline-offset-4"
                    >Detail lengkap</Link
                >
            </article>
            <p
                v-if="!leads.data.length"
                class="py-12 text-center text-kgj-muted"
            >
                {{
                    hasFilters
                        ? 'Tidak ada lead yang cocok dengan filter ini.'
                        : 'Belum ada lead. Tambahkan lead untuk memulai.'
                }}
                <button
                    v-if="hasFilters"
                    class="mt-3 block w-full font-semibold text-[#6c511e] underline underline-offset-4"
                    type="button"
                    @click="clearFilters"
                >
                    Tampilkan semua lead
                </button>
            </p>
        </div>

        <Sheet
            :open="Boolean(mobileEditorLead)"
            @update:open="setMobileEditorOpen"
        >
            <SheetContent
                side="bottom"
                :show-close-button="false"
                class="z-[60] h-[44rem] max-h-[calc(100dvh_-_1rem_-_env(safe-area-inset-bottom))] gap-0 overflow-hidden rounded-t-2xl bg-kgj-paper p-0"
            >
                <div
                    class="relative shrink-0 rounded-t-2xl border-b border-kgj-line bg-kgj-paper px-5 pt-5 pb-3 shadow-[0_3px_10px_rgba(58,48,31,0.06)]"
                >
                    <SheetHeader class="pr-10 text-left">
                        <SheetTitle>Update lead</SheetTitle>
                        <SheetDescription>
                            {{
                                mobileEditorLead?.name ||
                                mobileEditorLead?.contact_raw
                            }}
                        </SheetDescription>
                        <div class="mt-2 flex min-h-11 items-center gap-3">
                            <p
                                class="min-w-0 truncate text-xs font-medium"
                                :class="mobileSaveFeedback.tone"
                                role="status"
                            >
                                {{ mobileSaveFeedback.message }}
                            </p>
                            <LeadHistoryPopover
                                :lead-id="mobileEditorLead!.id"
                                label="Riwayat"
                                align="start"
                                content-class="z-[70]"
                                trigger-class="ml-auto inline-flex min-h-11 shrink-0 cursor-pointer items-center gap-1 text-xs font-semibold text-[#4f514a] underline underline-offset-4 hover:text-kgj-gold-deep focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-kgj-gold"
                            />
                        </div>
                    </SheetHeader>
                    <button
                        class="absolute top-4 right-5 flex size-11 items-center justify-center rounded-lg text-kgj-muted transition hover:bg-[#f3f0e8] hover:text-kgj-ink focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-kgj-gold"
                        type="button"
                        aria-label="Tutup update lead"
                        @click="setMobileEditorOpen(false)"
                    >
                        <X class="size-4" aria-hidden="true" />
                    </button>
                </div>
                <div class="min-h-0 flex-1 overflow-y-auto overscroll-contain">
                    <LeadQuickUpdate
                        v-if="mobileEditorLead"
                        :key="mobileEditorLead.id"
                        ref="mobileQuickUpdate"
                        class="px-5 pt-5 pb-[calc(1.5rem_+_env(safe-area-inset-bottom))]"
                        :lead="mobileEditorLead"
                        :outcome-factors="outcomeFactors"
                        :can-mark-crm="isAdmin"
                        :show-history="false"
                        :show-save-feedback="false"
                        :draft="leadEditState(mobileEditorLead).current"
                        :saved-draft="leadEditState(mobileEditorLead).saved"
                        :persistent-save-state="
                            leadEditState(mobileEditorLead).saveState
                        "
                        :retry-save-version="
                            leadRetryVersion(mobileEditorLead.id)
                        "
                        @dirty-change="trackDirtyLead"
                        @save-state="trackLeadSaveState"
                    />
                </div>
            </SheetContent>
        </Sheet>

        <div
            v-if="isDesktopWorkspace && desktopVisibleLeadRange"
            class="mt-4 flex flex-wrap items-center justify-between gap-2 text-xs text-kgj-muted"
        >
            <p>
                Menampilkan baris
                <strong class="font-semibold text-kgj-ink"
                    >{{ desktopVisibleLeadRange.start }}–{{
                        desktopVisibleLeadRange.end
                    }}</strong
                >
                dari {{ leads.data.length }} lead
            </p>
            <p
                class="rounded-full border border-[#e3d9c6] bg-[#faf7ef] px-3 py-1 font-semibold text-[#665a43]"
            >
                {{
                    desktopScrolledHorizontally
                        ? 'Kolom lanjutan terbuka'
                        : 'Geser horizontal untuk kolom lanjutan'
                }}
            </p>
        </div>

        <div
            v-if="isDesktopWorkspace"
            ref="desktopLeadViewport"
            class="kgj-lead-ledger kgj-table mt-4 max-h-[calc(100dvh-18rem)] overflow-auto"
            :class="{
                'kgj-lead-ledger--scrolled': desktopScrolledHorizontally,
            }"
            :aria-label="
                desktopVisibleLeadRange
                    ? `Tabel lead, baris ${desktopVisibleLeadRange.start} sampai ${desktopVisibleLeadRange.end} dari ${leads.data.length}`
                    : 'Tabel lead'
            "
            @scroll.passive="onDesktopTableScroll"
        >
            <table
                class="w-full min-w-[2300px] border-separate border-spacing-0 text-sm"
            >
                <thead
                    class="sticky top-0 z-30 text-left text-xs tracking-wider text-[#62665b] uppercase"
                >
                    <tr>
                        <th
                            class="sticky left-0 z-40 w-14 min-w-14 bg-[#f8f5ee] p-4"
                        >
                            <span class="sr-only">Pilih lead</span>
                        </th>
                        <th
                            class="sticky left-14 z-40 w-56 min-w-56 bg-[#f8f5ee] p-4"
                        >
                            Lead
                        </th>
                        <th
                            class="kgj-lead-ledger__sticky-edge sticky left-[17.5rem] z-40 w-44 min-w-44 bg-[#f8f5ee] p-4"
                        >
                            Nama CS
                        </th>
                        <th class="w-40 min-w-40 p-4">Tanggal lead</th>
                        <template v-if="!archiveMode">
                            <th class="p-4">Cold</th>
                            <th class="p-4">Warm</th>
                            <th class="p-4">Hot</th>
                            <th class="p-4">Hasil akhir</th>
                            <th class="p-4">Intent buying</th>
                            <th class="p-4">Waktu pakai</th>
                            <th class="p-4">Budget</th>
                            <th class="p-4">Keterangan</th>
                            <th class="p-4">Riwayat</th>
                        </template>
                        <th v-else class="p-4">Detail</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-if="desktopVirtualTop" aria-hidden="true">
                        <td
                            :colspan="archiveMode ? 5 : 13"
                            class="h-0 p-0"
                            :style="{ height: `${desktopVirtualTop}px` }"
                        />
                    </tr>
                    <template
                        v-for="{ lead, index } in virtualDesktopLeads"
                        :key="lead.id"
                    >
                        <tr
                            :ref="
                                (element) =>
                                    observeDesktopLeadRow(index, element)
                            "
                            class="group border-t border-[#e7eae5]"
                        >
                            <td
                                class="sticky left-0 z-10 w-14 min-w-14 bg-kgj-paper p-4 group-hover:bg-[#fcfaf5]"
                            >
                                <input
                                    v-model="selectedLeadIds"
                                    class="size-4 accent-kgj-gold"
                                    type="checkbox"
                                    :disabled="
                                        !selectedLeadIds.includes(lead.id) &&
                                        selectedLeadIds.length >= 100
                                    "
                                    :aria-label="`Pilih ${lead.name || lead.contact_raw}`"
                                    :value="lead.id"
                                />
                            </td>
                            <td
                                class="sticky left-14 z-10 w-56 min-w-56 overflow-hidden bg-kgj-paper p-4 font-semibold group-hover:bg-[#fcfaf5]"
                            >
                                <strong
                                    class="block truncate"
                                    :title="lead.name || lead.contact_raw"
                                    >{{ lead.name || lead.contact_raw }}</strong
                                ><small
                                    class="mt-1 block truncate font-normal text-[#62665b]"
                                    :title="lead.phone || lead.contact_raw"
                                    >{{ lead.phone || lead.contact_raw }}</small
                                >
                                <LeadSaveFeedback
                                    :state="leadSaveState(lead.id)?.state"
                                    :message="leadSaveState(lead.id)?.message"
                                    @retry="retryLeadSave(lead.id)"
                                    @resolve-conflict="
                                        resolveLeadConflict(lead.id)
                                    "
                                />
                            </td>
                            <td
                                class="kgj-lead-ledger__sticky-edge sticky left-[17.5rem] z-10 w-44 min-w-44 overflow-hidden bg-kgj-paper p-4 group-hover:bg-[#fcfaf5]"
                            >
                                <span
                                    class="block truncate"
                                    :title="ownerTitle(lead)"
                                    >{{ ownerLabel(lead) }}</span
                                ><small
                                    v-if="lead.cs_filter_match"
                                    class="mt-1 block truncate font-semibold text-kgj-gold-deep"
                                    >Cocok sebagai:
                                    {{ lead.cs_filter_match }}</small
                                ><small
                                    v-if="!isAdmin"
                                    class="mt-1 block font-semibold text-[#665a43]"
                                    >{{
                                        handlingLabel(lead.handling_role)
                                    }}</small
                                ><small
                                    v-if="lead.source_conflict_at"
                                    :class="
                                        isAdmin
                                            ? 'text-kgj-gold-deep'
                                            : 'text-[#665a43]'
                                    "
                                    :title="
                                        isAdmin
                                            ? `Perlu review: nomor juga tercatat di ${lead.source_conflict_branch_name || 'cabang lain'}.`
                                            : `Nomor juga tercatat di ${lead.source_conflict_branch_name || 'cabang lain'}. Sumber dan penanganan Anda tidak berubah.`
                                    "
                                    class="mt-1 block truncate font-semibold"
                                    >{{
                                        isAdmin
                                            ? 'Perlu review: juga di'
                                            : 'Tercatat juga di'
                                    }}
                                    {{
                                        lead.source_conflict_branch_name ||
                                        'cabang lain'
                                    }}</small
                                >
                                <small
                                    v-if="lead.collaborators.length"
                                    class="mt-1 block truncate font-semibold text-[#665a43]"
                                    :title="`CS pendamping: ${lead.collaborators.map((item) => item.name).join(', ')}`"
                                    >Pendamping:
                                    {{ lead.collaborators.length }} CS</small
                                >
                                <small
                                    v-if="lead.crm_taken_over_at"
                                    class="mt-1 block font-semibold text-[#8a6b2d]"
                                    >Diambil alih CRM</small
                                >
                                <button
                                    v-if="
                                        !archiveMode &&
                                        !isAdmin &&
                                        lead.handling_role === 'team'
                                    "
                                    class="mt-3 min-h-11 text-sm font-semibold text-kgj-gold-deep underline underline-offset-4"
                                    type="button"
                                    :disabled="assistingLeadId === lead.id"
                                    @click="startAssisting(lead.id)"
                                >
                                    {{
                                        assistingLeadId === lead.id
                                            ? 'Memulai…'
                                            : 'Mulai bantu'
                                    }}
                                </button>
                                <button
                                    v-else-if="
                                        !archiveMode &&
                                        !isAdmin &&
                                        lead.handling_role === 'collaborator'
                                    "
                                    class="mt-3 min-h-11 text-sm font-semibold text-[#62665b] underline underline-offset-4"
                                    type="button"
                                    :disabled="assistingLeadId === lead.id"
                                    @click="stopAssisting(lead.id)"
                                >
                                    {{
                                        assistingLeadId === lead.id
                                            ? 'Memproses…'
                                            : 'Selesai membantu'
                                    }}
                                </button>
                            </td>
                            <td
                                class="w-40 min-w-40 p-4 text-sm text-[#4f514a]"
                            >
                                <time
                                    v-if="lead.lead_created_on"
                                    :datetime="lead.lead_created_on"
                                >
                                    {{ formatLeadDate(lead.lead_created_on) }}
                                </time>
                                <span v-else>—</span>
                            </td>
                            <LeadQuickUpdate
                                v-if="
                                    !archiveMode &&
                                    (isAdmin || lead.handling_role !== 'team')
                                "
                                variant="table"
                                :lead="lead"
                                :outcome-factors="outcomeFactors"
                                :can-mark-crm="isAdmin"
                                :draft="leadEditState(lead).current"
                                :saved-draft="leadEditState(lead).saved"
                                :persistent-save-state="
                                    leadEditState(lead).saveState
                                "
                                :retry-save-version="leadRetryVersion(lead.id)"
                                @dirty-change="trackDirtyLead"
                                @save-state="trackLeadSaveState"
                            />
                            <td v-else class="p-4" colspan="1">
                                <Link
                                    :href="`/leads/${lead.id}`"
                                    class="font-semibold text-[#62665b] underline underline-offset-4"
                                    >Detail lengkap</Link
                                >
                            </td>
                        </tr>
                    </template>
                    <tr v-if="desktopVirtualBottom" aria-hidden="true">
                        <td
                            :colspan="archiveMode ? 5 : 13"
                            class="h-0 p-0"
                            :style="{ height: `${desktopVirtualBottom}px` }"
                        />
                    </tr>
                    <tr v-if="!leads.data.length">
                        <td
                            :colspan="archiveMode ? 5 : 13"
                            class="p-12 text-center text-[#62665b]"
                        >
                            {{
                                hasFilters
                                    ? 'Tidak ada lead yang cocok dengan filter ini.'
                                    : 'Belum ada lead. Tambahkan lead untuk memulai.'
                            }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <Dialog v-model:open="discardChangesDialogOpen">
            <DialogContent>
                <DialogHeader
                    ><DialogTitle>Buang perubahan belum disimpan?</DialogTitle
                    ><DialogDescription
                        >Checklist, keterangan, dan jadwal tindak lanjut yang
                        sedang diisi akan hilang bila Anda melanjutkan.
                    </DialogDescription></DialogHeader
                >
                <DialogFooter
                    ><DialogClose as-child
                        ><button class="kgj-button-secondary" type="button">
                            Tetap di halaman ini
                        </button></DialogClose
                    ><button
                        class="kgj-button-primary"
                        type="button"
                        @click="discardChangesAndContinue"
                    >
                        Lanjut tanpa menyimpan
                    </button></DialogFooter
                >
            </DialogContent>
        </Dialog>
        <Dialog v-model:open="archiveDialogOpen">
            <DialogContent>
                <DialogHeader
                    ><DialogTitle>Arsipkan lead terpilih?</DialogTitle
                    ><DialogDescription
                        >{{ selectedLeadIds.length }} lead akan disembunyikan
                        dari daftar kerja aktif, tetapi data dan riwayatnya
                        tetap tersimpan.</DialogDescription
                    ></DialogHeader
                >
                <DialogFooter
                    ><DialogClose as-child
                        ><button class="kgj-button-secondary" type="button">
                            Batalkan
                        </button></DialogClose
                    ><button
                        class="kgj-button-primary"
                        type="button"
                        :disabled="selectionForm.processing"
                        @click="archiveSelected"
                    >
                        Arsipkan lead
                    </button></DialogFooter
                >
            </DialogContent>
        </Dialog>
        <Dialog v-model:open="deleteDialogOpen">
            <DialogContent>
                <DialogHeader
                    ><DialogTitle>Hapus permanen lead terpilih?</DialogTitle
                    ><DialogDescription
                        >{{ selectedLeadIds.length }} lead, milestone, dan
                        riwayatnya akan dihapus dan tidak dapat
                        dipulihkan.</DialogDescription
                    ></DialogHeader
                >
                <DialogFooter
                    ><DialogClose as-child
                        ><button class="kgj-button-secondary" type="button">
                            Batalkan
                        </button></DialogClose
                    ><button
                        class="inline-flex min-h-11 items-center justify-center rounded-lg bg-kgj-rose px-4 text-sm font-semibold text-white transition hover:bg-[#84362e]"
                        type="button"
                        :disabled="selectionForm.processing"
                        @click="destroySelected"
                    >
                        Hapus permanen
                    </button></DialogFooter
                >
            </DialogContent>
        </Dialog>
    </section>
</template>
