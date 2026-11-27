# Graph Report - marketing-leads  (2026-11-27)

## Corpus Check
- 330 files · ~165,412 words
- Verdict: corpus is large enough that graph structure adds value.
- Unclassified: 25 file(s) not represented in the graph (top: (none) 19, .example 2, .neon 1)

## Summary
- 2134 nodes · 3877 edges · 140 communities (82 shown, 58 thin omitted)
- Extraction: 99% EXTRACTED · 1% INFERRED · 0% AMBIGUOUS · INFERRED: 23 edges (avg confidence: 0.85)
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `2032c65c`
- Run `git rev-parse HEAD` and compare to check if the graph is stale.
- Run `graphify update .` after code changes (no API cost).

## Community Hubs (Navigation)
- Illuminate\Database\Schema\Blueprint
- reka-ui
- LeadController.php
- sheet/index.ts
- navigation-menu/index.ts
- ProfileController.php
- dialog/index.ts
- SelectContent.vue
- Leads/Index.vue
- User
- vue
- LeadImportService
- TwoFactorSetupModal.vue
- DashboardController
- LeadManagementTest.php
- AppLayout.vue
- DashboardController.php
- TestCase
- package.json
- button/index.ts
- Branches/Index.vue
- global.d.ts
- compilerOptions
- AppSidebar.vue
- LeadController
- resources_js_routes_two_factor_index
- InputOTPSlot.vue
- bootstrap/app.php
- leads
- 2026-11-25T08-39-06Z__resources-js-pages-auth-login-vue.md
- PasswordValidationRules
- Illuminate\Http\Request
- GoogleSheetSyncService
- KGJ Leads
- tooltip/index.ts
- SidebarProvider.vue
- useAppearance.ts
- aliases
- AppHeader.vue
- scripts
- HandleAppearance.php
- Show.vue
- AvatarFallback.vue
- UserFactory.php
- LeadManagementTest
- LeadQuickUpdate.vue
- VerificationNotificationTest.php
- LeadHistoryPopover.vue
- composer.json
- require-dev
- @lucide/vue
- Product
- dependencies
- Illuminate\Http\Client\Request
- TooltipContent.vue
- require
- AuthenticationTest
- Collapsible.vue
- Illuminate\Support\Facades\DB
- LeadOutcomeFactors
- DashboardTest.php
- sidebar/index.ts
- CsName
- resources_js_routes_index
- config
- EmailVerificationTest
- guardUnsavedChanges
- @vueuse/core
- PasswordResetTest.php
- psr-4
- laravel
- logging.php
- DatabaseSeeder.php
- Design
- Accounts/Index.vue
- console.php
- LeadSourceDate
- TwoFactorRecoveryCodes.vue
- types/index.ts
- GLOSSARY.md
- artisan
- @inertiajs/vue3
- deploy-production.sh
- migrate-postgres.sh
- resources_js_actions_laravel_passkeys_http_controllers_passkeyconfirmationcontroller
- resources_js_actions_laravel_passkeys_http_controllers_passkeyconfirmationcontroller_index
- resources_js_actions_laravel_passkeys_http_controllers_passkeyconfirmationcontroller_store
- resources_js_actions_laravel_passkeys_http_controllers_passkeyregistrationcontroller
- resources_js_actions_laravel_passkeys_http_controllers_passkeyregistrationcontroller_destroy
- resources_js_routes_register
- resources_js_routes_register_store
- resources_js_routes_two_factor_index_confirm
- resources_js_routes_two_factor_index_disable
- resources_js_routes_two_factor_index_enable
- resources_js_routes_two_factor_index_qrcode
- resources_js_routes_two_factor_index_recoverycodes
- resources_js_routes_two_factor_index_regeneraterecoverycodes
- resources_js_routes_two_factor_index_secretkey
- resources_js_routes_two_factor_login
- resources_js_routes_two_factor_login_store
- resources_js_routes_verification
- resources_js_routes_verification_send
- Project agent rules
- SecurityTest
- DialogContent.vue
- clearSelection
- Checkbox.vue
- .historyPreview
- queueSave
- ownerLabel
- LeadSaveFeedback.vue
- outcomeLabel
- PopoverContent.vue
- ProfileUpdateTest
- resolveLeadConflict
- SheetContent.vue
- createDesktopRowResizeObserver
- hasUnsavedChanges
- LeadEditState
- observeDesktopViewport
- saveImmediately
- {closure#16}

## God Nodes (most connected - your core abstractions)
1. `vue` - 130 edges
2. `LeadManagementTest` - 115 edges
3. `cn()` - 94 edges
4. `User` - 85 edges
5. `reka-ui` - 80 edges
6. `LeadController` - 46 edges
7. `@inertiajs/vue3` - 46 edges
8. `@vueuse/core` - 46 edges
9. `DashboardController` - 44 edges
10. `@lucide/vue` - 40 edges

## Surprising Connections (you probably didn't know these)
- `ProfileUpdateRequest` --mixes_in--> `ProfileValidationRules`  [EXTRACTED]
  app/Http/Requests/Settings/ProfileUpdateRequest.php → app/Concerns/ProfileValidationRules.php
- `DashboardController` --inherits--> `Controller`  [EXTRACTED]
  app/Http/Controllers/DashboardController.php → app/Http/Controllers/Controller.php
- `LeadController` --inherits--> `Controller`  [EXTRACTED]
  app/Http/Controllers/LeadController.php → app/Http/Controllers/Controller.php
- `{closure#4}()` --calls--> `CsName`  [EXTRACTED]
  app/Http/Controllers/DashboardController.php → app/Support/CsName.php
- `{closure#13}()` --calls--> `CsName`  [EXTRACTED]
  app/Http/Controllers/DashboardController.php → app/Support/CsName.php

## Import Cycles
- None detected.

## Communities (140 total, 58 thin omitted)

### Community 0 - "Illuminate\Database\Schema\Blueprint"
Cohesion: 0.07
Nodes (27): {closure#1}(), {closure#2}(), {closure#3}(), {closure#1}(), {closure#2}(), {closure#1}(), {closure#2}(), {closure#3}() (+19 more)

### Community 1 - "reka-ui"
Cohesion: 0.05
Nodes (40): reka-ui, emits, forwarded, props, delegatedProps, emits, forwarded, props (+32 more)

### Community 2 - "LeadController.php"
Cohesion: 0.07
Nodes (15): {closure#12}(), {closure#16}(), {closure#17}(), {closure#20}(), {closure#21}(), {closure#22}(), {closure#24}(), {closure#32}() (+7 more)

### Community 3 - "sheet/index.ts"
Cohesion: 0.11
Nodes (11): emits, forwarded, props, props, delegatedProps, props, props, props (+3 more)

### Community 4 - "navigation-menu/index.ts"
Cohesion: 0.06
Nodes (27): navigationMenuTriggerStyle, delegatedProps, emits, forwarded, props, delegatedProps, emits, forwarded (+19 more)

### Community 5 - "ProfileController.php"
Cohesion: 0.09
Nodes (9): Controller, ProfileController, SecurityController, ProfileUpdateRequest, Illuminate\Auth\Middleware\RequirePassword, Illuminate\Contracts\Auth\MustVerifyEmail, Illuminate\Support\Facades\Auth, Illuminate\Support\Facades\Route (+1 more)

### Community 6 - "dialog/index.ts"
Cohesion: 0.22
Nodes (5): emits, forwarded, props, props, props

### Community 7 - "SelectContent.vue"
Cohesion: 0.05
Nodes (27): resources_js_components_ui_select_index_selectscrolldownbutton, resources_js_components_ui_select_index_selectscrollupbutton, emits, forwarded, props, delegatedProps, emits, forwarded (+19 more)

### Community 8 - "Leads/Index.vue"
Cohesion: 0.02
Nodes (75): resources_js_components_ui_sheet_index_sheetdescription, resources_js_components_ui_sheet_index_sheetheader, resources_js_components_ui_sheet_index_sheettitle, allVisibleSelected, archiveDialogOpen, branch, budget, bulkCsAccounts (+67 more)

### Community 9 - "User"
Cohesion: 0.09
Nodes (9): User, Illuminate\Database\Eloquent\Factories\HasFactory, Illuminate\Foundation\Auth\User, Illuminate\Notifications\Notifiable, Illuminate\Support\Carbon, Laravel\Fortify\Contracts\PasskeyUser, Laravel\Fortify\PasskeyAuthenticatable, Laravel\Fortify\TwoFactorAuthenticatable (+1 more)

### Community 10 - "vue"
Cohesion: 0.04
Nodes (47): vue, props, props, props, AlertVariants, props, props, props (+39 more)

### Community 12 - "TwoFactorSetupModal.vue"
Cohesion: 0.09
Nodes (20): ref_routes_two_factor_login, code, { copy, copied }, isOpen, modalConfig, pinInputContainerRef, Props, { qrCodeSvg, manualSetupKey, clearSetupData, fetchSetupData, errors } (+12 more)

### Community 14 - "LeadManagementTest.php"
Cohesion: 0.10
Nodes (33): Illuminate\Http\UploadedFile, Inertia\Testing\AssertableInertia, Mockery, OpenSpout\Common\Entity\Cell, OpenSpout\Common\Entity\Row, OpenSpout\Writer\XLSX\Writer, {closure#15}(), {closure#16}() (+25 more)

### Community 15 - "AppLayout.vue"
Cohesion: 0.14
Nodes (17): emit, handleDelete(), isDeleting, props, resources_js_components_ui_dialog_index_dialog, resources_js_components_ui_dialog_index_dialogclose, resources_js_components_ui_dialog_index_dialogcontent, resources_js_components_ui_dialog_index_dialogdescription (+9 more)

### Community 16 - "DashboardController.php"
Cohesion: 0.07
Nodes (19): {closure#10}(), {closure#2}(), {closure#3}(), {closure#36}(), {closure#37}(), {closure#38}(), {closure#39}(), {closure#5}() (+11 more)

### Community 17 - "TestCase"
Cohesion: 0.09
Nodes (13): Illuminate\Foundation\Testing\RefreshDatabase, Illuminate\Foundation\Testing\TestCase, Illuminate\Support\Facades\RateLimiter, Laravel\Fortify\Features, {closure#1}(), PasswordConfirmationTest, RegistrationTest, {closure#1}() (+5 more)

### Community 18 - "package.json"
Cohesion: 0.07
Nodes (29): overrides, vite, private, $schema, type, clsx, concurrently, @inertiajs/vite (+21 more)

### Community 19 - "button/index.ts"
Cohesion: 0.07
Nodes (37): ref_actions_laravel_passkeys_http_controllers_passkeyconfirmationcontroller, ref_inertiajs_core, @laravel/passkeys, ref_routes_register, ref_routes_verification, resources_js_actions_app_http_controllers_settings_profilecontroller, passwordInput, emit (+29 more)

### Community 20 - "Branches/Index.vue"
Cohesion: 0.05
Nodes (18): resources_js_components_ui_dropdown_menu_index_dropdownmenuitem, Branch, branchToDelete, branchToDisconnect, canSync(), editingId, form, importCsOptions (+10 more)

### Community 21 - "global.d.ts"
Cohesion: 0.22
Nodes (9): Auth, ComponentCustomProperties, GlobalDirectives, ImportMeta, ImportMetaEnv, InertiaConfig, @inertiajs/core, vite/client (+1 more)

### Community 22 - "compilerOptions"
Cohesion: 0.09
Nodes (21): compilerOptions, allowJs, esModuleInterop, forceConsistentCasingInFileNames, isolatedModules, jsx, jsxImportSource, lib (+13 more)

### Community 23 - "AppSidebar.vue"
Cohesion: 0.12
Nodes (23): footerNavItems, mainNavItems, Props, { isCurrentUrl }, resources_js_components_ui_sidebar_index_sidebar, resources_js_components_ui_sidebar_index_sidebarcontent, resources_js_components_ui_sidebar_index_sidebarfooter, resources_js_components_ui_sidebar_index_sidebargroup (+15 more)

### Community 27 - "InputOTPSlot.vue"
Cohesion: 0.11
Nodes (15): vue-input-otp, delegatedProps, emits, forwarded, props, delegatedProps, forwarded, props (+7 more)

### Community 28 - "bootstrap/app.php"
Cohesion: 0.16
Nodes (9): HandleInertiaRequests, {closure#1}(), {closure#2}(), {closure#3}(), Illuminate\Foundation\Application, Illuminate\Foundation\Configuration\Exceptions, Illuminate\Foundation\Configuration\Middleware, Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets (+1 more)

### Community 29 - "leads"
Cohesion: 0.06
Nodes (35): branches, lead_activity, lead_activity_lead_created_idx, lead_milestones, leads, leads_assigned_user_idx, leads_outcome_idx, leads_updated_at_idx (+27 more)

### Community 30 - "2026-11-25T08-39-06Z__resources-js-pages-auth-login-vue.md"
Cohesion: 0.25
Nodes (7): Design Health Score, Design Specificity Verdict, Minor Observations, Overall Impression, Persona Red Flags, Priority Issues, What's Working

### Community 31 - "PasswordValidationRules"
Cohesion: 0.05
Nodes (26): CreateNewUser, ResetUserPassword, PasswordValidationRules, ProfileValidationRules, PasswordUpdateRequest, ProfileDeleteRequest, TwoFactorAuthenticationRequest, {closure#1}() (+18 more)

### Community 32 - "Illuminate\Http\Request"
Cohesion: 0.17
Nodes (13): LoginResponse, {closure#1}(), {closure#10}(), {closure#11}(), {closure#2}(), {closure#3}(), {closure#4}(), {closure#5}() (+5 more)

### Community 33 - "GoogleSheetSyncService"
Cohesion: 0.06
Nodes (12): {closure#10}(), {closure#2}(), GoogleSheetSyncService, DailyLeadTabParser, Google\Auth\Credentials\ServiceAccountCredentials, Illuminate\Http\Client\PendingRequest, Illuminate\Http\Client\RequestException, Illuminate\Support\Facades\Cache (+4 more)

### Community 34 - "KGJ Leads"
Cohesion: 0.11
Nodes (16): Auto deploy, Deploy manual, Migrasi dan backup, Panduan Deployment KGJ Leads, Production VPS 1, Admin, Alur kerja harian, Bantuan (+8 more)

### Community 35 - "tooltip/index.ts"
Cohesion: 0.22
Nodes (5): emits, forwarded, props, props, props

### Community 36 - "SidebarProvider.vue"
Cohesion: 0.11
Nodes (22): resources_js_components_ui_sheet_index_sheet, resources_js_components_ui_sheet_index_sheetcontent, SidebarProps, { isMobile, state, openMobile, setOpenMobile }, props, emits, isMobile, open (+14 more)

### Community 37 - "useAppearance.ts"
Cohesion: 0.06
Nodes (32): pages, { appearance, updateAppearance }, tabs, appearance, getStoredAppearance(), handleSystemThemeChange(), initializeTheme(), mediaQuery() (+24 more)

### Community 38 - "aliases"
Cohesion: 0.12
Nodes (15): aliases, components, composables, lib, ui, utils, iconLibrary, $schema (+7 more)

### Community 39 - "AppHeader.vue"
Cohesion: 0.07
Nodes (33): auth, { isCurrentUrl, whenCurrentUrl }, mainNavItems, page, Props, rightNavItems, { isMobile, state }, page (+25 more)

### Community 40 - "scripts"
Cohesion: 0.14
Nodes (14): scripts, ci:check, dev, lint, lint:check, post-autoload-dump, post-create-project-cmd, post-root-package-install (+6 more)

### Community 41 - "HandleAppearance.php"
Cohesion: 0.36
Nodes (5): EnsureAdmin, HandleAppearance, Closure, Illuminate\Support\Facades\View, Symfony\Component\HttpFoundation\Response

### Community 42 - "Show.vue"
Cohesion: 0.07
Nodes (25): zod, budgetOptions, leadSchema, archiveDialogOpen, changeMilestone(), clientError, deleteDialogOpen, displayContact (+17 more)

### Community 43 - "AvatarFallback.vue"
Cohesion: 0.25
Nodes (4): props, delegatedProps, props, props

### Community 44 - "UserFactory.php"
Cohesion: 0.16
Nodes (6): UserFactory, Illuminate\Database\Eloquent\Factories\Factory, Illuminate\Support\Facades\Hash, Illuminate\Support\Str, Pdo\Mysql, static

### Community 46 - "LeadQuickUpdate.vue"
Cohesion: 0.08
Nodes (23): awaitingClosingFactor, emit, groups, hasClosingMilestone, hasConflict, hasFinalClosing, hasSaved, hasSavedClosing (+15 more)

### Community 47 - "VerificationNotificationTest.php"
Cohesion: 0.29
Nodes (3): Illuminate\Auth\Notifications\VerifyEmail, Illuminate\Support\Facades\Notification, VerificationNotificationTest

### Community 48 - "LeadHistoryPopover.vue"
Cohesion: 0.12
Nodes (15): elapsed(), error, followUpLabel, history, HistoryEntry, HistoryMeta, leadAge, loading (+7 more)

### Community 49 - "composer.json"
Cohesion: 0.17
Nodes (11): autoload-dev, psr-4, description, keywords, license, minimum-stability, name, prefer-stable (+3 more)

### Community 50 - "require-dev"
Cohesion: 0.17
Nodes (12): require-dev, fakerphp/faker, larastan/larastan, laravel/pail, laravel/pao, laravel/pint, laravel/sail, mockery/mockery (+4 more)

### Community 51 - "@lucide/vue"
Cohesion: 0.14
Nodes (10): @lucide/vue, Props, uniqueErrors, resources_js_components_ui_alert_index_alert, resources_js_components_ui_alert_index_alertdescription, resources_js_components_ui_alert_index_alerttitle, props, emailInput (+2 more)

### Community 52 - "Product"
Cohesion: 0.18
Nodes (10): Capabilities and Constraints, Evidence on Hand, Operating Context, Platform, Positioning, Product, Product Principles, Product Purpose (+2 more)

### Community 54 - "dependencies"
Cohesion: 0.04
Nodes (42): dependencies, class-variance-authority, clsx, @inertiajs/vite, @inertiajs/vue3, @laravel/passkeys, laravel-vite-plugin, @lucide/vue (+34 more)

### Community 55 - "Illuminate\Http\Client\Request"
Cohesion: 0.33
Nodes (6): Illuminate\Http\Client\Request, {closure#11}(), {closure#12}(), {closure#13}(), {closure#14}(), {closure#25}()

### Community 56 - "TooltipContent.vue"
Cohesion: 0.40
Nodes (4): delegatedProps, emits, forwarded, props

### Community 57 - "require"
Cohesion: 0.20
Nodes (10): require, google/auth, inertiajs/inertia-laravel, laravel/chisel, laravel/fortify, laravel/framework, laravel/tinker, laravel/wayfinder (+2 more)

### Community 59 - "Collapsible.vue"
Cohesion: 0.22
Nodes (5): emits, forwarded, props, props, props

### Community 61 - "Illuminate\Support\Facades\DB"
Cohesion: 0.19
Nodes (7): BootstrapAdmin, {closure#2}(), {closure#3}(), MarkOverdueLeadsForCrm, SyncGoogleLeads, Illuminate\Console\Command, Illuminate\Support\Facades\DB

### Community 63 - "DashboardTest.php"
Cohesion: 0.10
Nodes (18): {closure#11}(), {closure#14}(), {closure#15}(), {closure#16}(), {closure#17}(), {closure#18}(), {closure#19}(), {closure#20}() (+10 more)

### Community 64 - "sidebar/index.ts"
Cohesion: 0.20
Nodes (11): class-variance-authority, SidebarMenuButtonVariants, delegatedProps, { isMobile, state }, props, props, SidebarMenuButtonProps, resources_js_components_ui_sidebar_utils_usesidebar (+3 more)

### Community 65 - "CsName"
Cohesion: 0.11
Nodes (13): {closure#13}(), {closure#23}(), {closure#26}(), {closure#30}(), {closure#31}(), {closure#32}(), {closure#33}(), {closure#34}() (+5 more)

### Community 66 - "resources_js_routes_index"
Cohesion: 0.16
Nodes (8): Props, page, { title = '', description = '' }, resources_js_routes_index, resources_js_routes_index_dashboard, resources_js_routes_index_home, resources_js_routes_index_login, resources_js_routes_index_register

### Community 67 - "config"
Cohesion: 0.29
Nodes (7): pestphp/pest-plugin, php-http/discovery, config, allow-plugins, optimize-autoloader, preferred-install, sort-packages

### Community 68 - "EmailVerificationTest"
Cohesion: 0.15
Nodes (4): Illuminate\Auth\Events\Verified, Illuminate\Support\Facades\Event, Illuminate\Support\Facades\URL, EmailVerificationTest

### Community 69 - "guardUnsavedChanges"
Cohesion: 0.13
Nodes (16): applyFilters(), applyFiltersNow(), assignSelected(), assignSelectedNow(), clearFilters(), createLead(), createLeadNow(), guardUnsavedChanges() (+8 more)

### Community 70 - "@vueuse/core"
Cohesion: 0.07
Nodes (21): @vueuse/core, delegatedProps, props, BadgeVariants, delegatedProps, forwardedProps, props, delegatedProps (+13 more)

### Community 72 - "psr-4"
Cohesion: 0.40
Nodes (5): autoload, psr-4, App\\, Database\\Factories\\, Database\\Seeders\\

### Community 73 - "laravel"
Cohesion: 0.40
Nodes (5): extra, laravel, post-create-project, dont-discover, installer

### Community 74 - "logging.php"
Cohesion: 0.40
Nodes (4): Monolog\Handler\NullHandler, Monolog\Handler\StreamHandler, Monolog\Handler\SyslogUdpHandler, Monolog\Processor\PsrLogMessageProcessor

### Community 75 - "DatabaseSeeder.php"
Cohesion: 0.60
Nodes (3): DatabaseSeeder, Illuminate\Database\Console\Seeds\WithoutModelEvents, Illuminate\Database\Seeder

### Community 76 - "Design"
Cohesion: 0.40
Nodes (4): Design, Direction Contract, Mode, Visual Direction

### Community 77 - "Accounts/Index.vue"
Cohesion: 0.12
Nodes (12): Account, accountToLink, accountToReconcile, accountToToggle, cancel(), editingAccount, editingId, form (+4 more)

### Community 78 - "console.php"
Cohesion: 0.40
Nodes (3): Illuminate\Foundation\Inspiring, Illuminate\Support\Facades\Artisan, Illuminate\Support\Facades\Schedule

### Community 79 - "LeadSourceDate"
Cohesion: 0.22
Nodes (3): {closure#1}(), LeadSourceDate, DateTimeImmutable

### Community 80 - "TwoFactorRecoveryCodes.vue"
Cohesion: 0.12
Nodes (18): ref_routes_two_factor, { hasSetupData, clearTwoFactorAuthData }, showSetupModal, isRecoveryCodesVisible, recoveryCodeSectionRef, { recoveryCodesList, fetchRecoveryCodes, errors }, resources_js_components_ui_card_index_card, resources_js_components_ui_card_index_cardcontent (+10 more)

### Community 81 - "types/index.ts"
Cohesion: 0.11
Nodes (21): className, Props, Props, Props, resources_js_components_ui_breadcrumb_index_breadcrumb, resources_js_components_ui_breadcrumb_index_breadcrumbitem, resources_js_components_ui_breadcrumb_index_breadcrumblink, resources_js_components_ui_breadcrumb_index_breadcrumblist (+13 more)

### Community 84 - "@inertiajs/vue3"
Cohesion: 0.12
Nodes (15): ref_actions_laravel_passkeys_http_controllers_passkeyregistrationcontroller, @inertiajs/vue3, resources_js_actions_app_http_controllers_settings_securitycontroller, Props, Props, Props, resources_js_components_ui_separator_index_separator, props (+7 more)

### Community 116 - "Project agent rules"
Cohesion: 0.25
Nodes (7): Data and database safety, Docker runtime verification, Project agent rules, Project context, Required working order, Scope and compatibility, Validation and tools

### Community 120 - "DialogContent.vue"
Cohesion: 0.25
Nodes (6): delegatedProps, emits, forwarded, props, delegatedProps, props

### Community 121 - "clearSelection"
Cohesion: 0.33
Nodes (6): archiveSelected(), clearSelection(), destroySelected(), restoreSelected(), restoreSelectedNow(), toggleMobileSelectionMode()

### Community 123 - "Checkbox.vue"
Cohesion: 0.33
Nodes (4): delegatedProps, emits, forwarded, props

### Community 124 - ".historyPreview"
Cohesion: 0.40
Nodes (3): LogoutResponse, Illuminate\Http\JsonResponse, Laravel\Fortify\Contracts\LogoutResponse

### Community 126 - "queueSave"
Cohesion: 0.22
Nodes (10): changedPayload(), flushPendingSave(), isDirty, persistSaveState(), queueSave(), retrySave(), save(), saveAfterTyping() (+2 more)

### Community 129 - "LeadSaveFeedback.vue"
Cohesion: 0.40
Nodes (4): emit, label, props, SaveState

### Community 131 - "PopoverContent.vue"
Cohesion: 0.15
Nodes (9): emits, forwarded, props, delegatedProps, emits, forwarded, props, forwardedProps (+1 more)

### Community 138 - "SheetContent.vue"
Cohesion: 0.22
Nodes (7): delegatedProps, emits, forwarded, props, SheetContentProps, delegatedProps, props

### Community 145 - "saveImmediately"
Cohesion: 0.33
Nodes (6): changeMilestone(), changeOutcome(), reopenClosing(), saveImmediately(), saveOutcome(), setClosingOutcome()

## Knowledge Gaps
- **686 isolated node(s):** `$schema`, `style`, `config`, `css`, `baseColor` (+681 more)
  These have ≤1 connection - possible missing edges or undocumented components. (Counts symbols only; 1028 node(s) total have ≤1 connection when file, concept and rationale nodes are included.)
- **58 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `vue` connect `vue` to `LeadSaveFeedback.vue`, `reka-ui`, `PopoverContent.vue`, `navigation-menu/index.ts`, `sheet/index.ts`, `SelectContent.vue`, `Leads/Index.vue`, `SheetContent.vue`, `TwoFactorSetupModal.vue`, `AppLayout.vue`, `package.json`, `button/index.ts`, `Branches/Index.vue`, `global.d.ts`, `AppSidebar.vue`, `InputOTPSlot.vue`, `SidebarProvider.vue`, `useAppearance.ts`, `AppHeader.vue`, `Show.vue`, `AvatarFallback.vue`, `LeadQuickUpdate.vue`, `LeadHistoryPopover.vue`, `@lucide/vue`, `TooltipContent.vue`, `sidebar/index.ts`, `resources_js_routes_index`, `@vueuse/core`, `Accounts/Index.vue`, `TwoFactorRecoveryCodes.vue`, `types/index.ts`, `@inertiajs/vue3`, `DialogContent.vue`, `Checkbox.vue`?**
  _High betweenness centrality (0.125) - this node is a cross-community bridge._
- **Why does `User` connect `User` to `Illuminate\Http\Request`, `EmailVerificationTest`, `ProfileUpdateTest`, `PasswordResetTest.php`, `DatabaseSeeder.php`, `UserFactory.php`, `LeadManagementTest.php`, `VerificationNotificationTest.php`, `TestCase`, `SecurityTest`, `.user`, `AuthenticationTest`, `DashboardTest.php`, `PasswordValidationRules`?**
  _High betweenness centrality (0.040) - this node is a cross-community bridge._
- **Why does `reka-ui` connect `reka-ui` to `PopoverContent.vue`, `navigation-menu/index.ts`, `sheet/index.ts`, `dialog/index.ts`, `SelectContent.vue`, `vue`, `SheetContent.vue`, `package.json`, `button/index.ts`, `InputOTPSlot.vue`, `tooltip/index.ts`, `SidebarProvider.vue`, `AvatarFallback.vue`, `TooltipContent.vue`, `Collapsible.vue`, `sidebar/index.ts`, `@vueuse/core`, `DialogContent.vue`, `Checkbox.vue`?**
  _High betweenness centrality (0.035) - this node is a cross-community bridge._
- **What connects `$schema`, `style`, `config` to the rest of the system?**
  _686 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `Illuminate\Database\Schema\Blueprint` be split into smaller, more focused modules?**
  _Cohesion score 0.06968641114982578 - nodes in this community are weakly interconnected._
- **Should `reka-ui` be split into smaller, more focused modules?**
  _Cohesion score 0.04612159329140461 - nodes in this community are weakly interconnected._
- **Should `LeadController.php` be split into smaller, more focused modules?**
  _Cohesion score 0.06827880512091039 - nodes in this community are weakly interconnected._