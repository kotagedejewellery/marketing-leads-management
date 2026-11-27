# Graph Report - marketing-leads  (2026-11-27)

## Corpus Check
- 330 files · ~162,712 words
- Verdict: corpus is large enough that graph structure adds value.
- Unclassified: 25 file(s) not represented in the graph (top: (none) 19, .example 2, .neon 1)

## Summary
- 2105 nodes · 3826 edges · 155 communities (99 shown, 56 thin omitted)
- Extraction: 99% EXTRACTED · 1% INFERRED · 0% AMBIGUOUS · INFERRED: 23 edges (avg confidence: 0.85)
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `2e2cc66e`
- Run `git rev-parse HEAD` and compare to check if the graph is stale.
- Run `graphify update .` after code changes (no API cost).

## Community Hubs (Navigation)
- Illuminate\Database\Schema\Blueprint
- reka-ui
- LeadController.php
- Sidebar.vue
- @vueuse/core
- ProfileController.php
- @lucide/vue
- SelectContent.vue
- Leads/Index.vue
- User
- vue
- LeadImportService
- TwoFactorSetupModal.vue
- Illuminate\Http\Request
- LeadManagementTest.php
- DeleteUser.vue
- DashboardController.php
- TestCase
- package.json
- button/index.ts
- Branches/Index.vue
- Layout.vue
- compilerOptions
- AppSidebar.vue
- LeadController
- LeadManagementTest
- resources_js_routes_two_factor_index
- InputOTPSlot.vue
- bootstrap/app.php
- leads
- 2026-11-25T08-39-06Z__resources-js-pages-auth-login-vue.md
- LeadOutcomeFactors
- DashboardController
- GoogleSheetSyncService
- KGJ Leads
- dependencies
- SidebarProvider.vue
- useAppearance.ts
- aliases
- AppHeader.vue
- scripts
- web.php
- Show.vue
- global.d.ts
- UserFactory.php
- LeadQuickUpdate.vue
- VerificationNotificationTest.php
- LeadHistoryPopover.vue
- composer.json
- require-dev
- Separator.vue
- Product
- devDependencies
- Illuminate\Support\Facades\Schema
- UserInfo.vue
- require
- AuthenticationTest
- Collapsible.vue
- dialog/index.ts
- MarkOverdueLeadsForCrm.php
- useCurrentUrl.ts
- DashboardTest.php
- tooltip/index.ts
- CsName
- Statistics.vue
- config
- EmailVerificationTest
- guardUnsavedChanges
- Badge.vue
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
- UserMenuContent.vue
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
- {closure#16}
- Project agent rules
- optionalDependencies
- SecurityTest.php
- Illuminate\Foundation\Http\FormRequest
- clearSelection
- PasskeyRegister.vue
- AppContent.vue
- @inertiajs/vue3
- queueSave
- AppServiceProvider.php
- ownerLabel
- LeadSaveFeedback.vue
- outcomeLabel
- Popover.vue
- Laravel\Fortify\Features
- resolveLeadConflict
- AlertError.vue
- SheetContent.vue
- ProfileValidationRules
- createDesktopRowResizeObserver
- hasUnsavedChanges
- LeadEditState
- observeDesktopViewport
- saveImmediately
- DialogContent.vue
- .historyPreview
- SidebarMenuSkeleton.vue
- PasswordValidationRules
- FortifyServiceProvider
- PasswordInput.vue
- TwoFactorAuthenticationRequest
- PopoverContent.vue
- TooltipContent.vue

## God Nodes (most connected - your core abstractions)
1. `vue` - 130 edges
2. `LeadManagementTest` - 99 edges
3. `cn()` - 94 edges
4. `User` - 84 edges
5. `reka-ui` - 80 edges
6. `LeadController` - 46 edges
7. `@inertiajs/vue3` - 46 edges
8. `@vueuse/core` - 46 edges
9. `DashboardController` - 43 edges
10. `@lucide/vue` - 40 edges

## Surprising Connections (you probably didn't know these)
- `CreateNewUser` --mixes_in--> `PasswordValidationRules`  [EXTRACTED]
  app/Actions/Fortify/CreateNewUser.php → app/Concerns/PasswordValidationRules.php
- `PasswordUpdateRequest` --mixes_in--> `PasswordValidationRules`  [EXTRACTED]
  app/Http/Requests/Settings/PasswordUpdateRequest.php → app/Concerns/PasswordValidationRules.php
- `ProfileDeleteRequest` --mixes_in--> `PasswordValidationRules`  [EXTRACTED]
  app/Http/Requests/Settings/ProfileDeleteRequest.php → app/Concerns/PasswordValidationRules.php
- `ProfileUpdateRequest` --mixes_in--> `ProfileValidationRules`  [EXTRACTED]
  app/Http/Requests/Settings/ProfileUpdateRequest.php → app/Concerns/ProfileValidationRules.php
- `DashboardController` --inherits--> `Controller`  [EXTRACTED]
  app/Http/Controllers/DashboardController.php → app/Http/Controllers/Controller.php

## Import Cycles
- None detected.

## Communities (155 total, 56 thin omitted)

### Community 0 - "Illuminate\Database\Schema\Blueprint"
Cohesion: 0.10
Nodes (20): {closure#1}(), {closure#2}(), {closure#3}(), {closure#1}(), {closure#2}(), {closure#3}(), Illuminate\Database\Schema\Blueprint, {closure#2}() (+12 more)

### Community 1 - "reka-ui"
Cohesion: 0.06
Nodes (32): reka-ui, emits, forwarded, props, delegatedProps, emits, forwarded, props (+24 more)

### Community 2 - "LeadController.php"
Cohesion: 0.06
Nodes (16): {closure#12}(), {closure#16}(), {closure#17}(), {closure#19}(), {closure#20}(), {closure#21}(), {closure#22}(), {closure#24}() (+8 more)

### Community 3 - "Sidebar.vue"
Cohesion: 0.10
Nodes (15): resources_js_components_ui_sheet_index_sheet, resources_js_components_ui_sheet_index_sheetcontent, emits, forwarded, props, props, delegatedProps, props (+7 more)

### Community 4 - "@vueuse/core"
Cohesion: 0.05
Nodes (33): @vueuse/core, delegatedProps, props, delegatedProps, forwardedProps, props, navigationMenuTriggerStyle, delegatedProps (+25 more)

### Community 5 - "ProfileController.php"
Cohesion: 0.14
Nodes (7): Controller, ProfileController, SecurityController, Illuminate\Auth\Middleware\RequirePassword, Illuminate\Contracts\Auth\MustVerifyEmail, Illuminate\Support\Facades\Auth, Inertia\Inertia

### Community 6 - "@lucide/vue"
Cohesion: 0.07
Nodes (21): @lucide/vue, props, delegatedProps, emits, forwarded, props, delegatedProps, emits (+13 more)

### Community 7 - "SelectContent.vue"
Cohesion: 0.06
Nodes (23): resources_js_components_ui_select_index_selectscrolldownbutton, resources_js_components_ui_select_index_selectscrollupbutton, emits, forwarded, props, delegatedProps, emits, forwarded (+15 more)

### Community 8 - "Leads/Index.vue"
Cohesion: 0.02
Nodes (73): resources_js_components_ui_sheet_index_sheetdescription, allVisibleSelected, archiveDialogOpen, branch, budget, bulkCsAccounts, bulkForm, bulkSelectionMessage (+65 more)

### Community 9 - "User"
Cohesion: 0.09
Nodes (9): User, Illuminate\Database\Eloquent\Factories\HasFactory, Illuminate\Foundation\Auth\User, Illuminate\Notifications\Notifiable, Illuminate\Support\Carbon, Laravel\Fortify\Contracts\PasskeyUser, Laravel\Fortify\PasskeyAuthenticatable, Laravel\Fortify\TwoFactorAuthenticatable (+1 more)

### Community 10 - "vue"
Cohesion: 0.05
Nodes (49): vue, props, props, props, props, props, props, props (+41 more)

### Community 12 - "TwoFactorSetupModal.vue"
Cohesion: 0.10
Nodes (17): ref_routes_two_factor_login, code, { copy, copied }, isOpen, modalConfig, pinInputContainerRef, Props, { qrCodeSvg, manualSetupKey, clearSetupData, fetchSetupData, errors } (+9 more)

### Community 13 - "Illuminate\Http\Request"
Cohesion: 0.11
Nodes (13): LoginResponse, {closure#1}(), {closure#10}(), {closure#11}(), {closure#2}(), {closure#3}(), {closure#4}(), {closure#5}() (+5 more)

### Community 14 - "LeadManagementTest.php"
Cohesion: 0.10
Nodes (34): Illuminate\Http\Client\Request, Illuminate\Http\UploadedFile, Inertia\Testing\AssertableInertia, Mockery, OpenSpout\Common\Entity\Cell, OpenSpout\Common\Entity\Row, OpenSpout\Writer\XLSX\Writer, {closure#11}() (+26 more)

### Community 15 - "DeleteUser.vue"
Cohesion: 0.15
Nodes (18): passwordInput, emit, handleDelete(), isDeleting, props, resources_js_components_ui_dialog_index_dialog, resources_js_components_ui_dialog_index_dialogclose, resources_js_components_ui_dialog_index_dialogcontent (+10 more)

### Community 16 - "DashboardController.php"
Cohesion: 0.07
Nodes (19): {closure#10}(), {closure#2}(), {closure#3}(), {closure#36}(), {closure#37}(), {closure#38}(), {closure#39}(), {closure#5}() (+11 more)

### Community 17 - "TestCase"
Cohesion: 0.09
Nodes (10): Illuminate\Foundation\Testing\RefreshDatabase, Illuminate\Foundation\Testing\TestCase, {closure#1}(), PasswordConfirmationTest, RegistrationTest, ExampleTest, ProfileUpdateTest, TrustedProxyTest (+2 more)

### Community 18 - "package.json"
Cohesion: 0.07
Nodes (29): overrides, vite, private, $schema, type, clsx, concurrently, @inertiajs/vite (+21 more)

### Community 19 - "button/index.ts"
Cohesion: 0.11
Nodes (22): ref_actions_laravel_passkeys_http_controllers_passkeyconfirmationcontroller, ref_inertiajs_core, ref_routes_register, ref_routes_verification, Props, { verify, isLoading, error, isSupported }, Props, Props (+14 more)

### Community 20 - "Branches/Index.vue"
Cohesion: 0.06
Nodes (17): Branch, branchToDelete, branchToDisconnect, canSync(), editingId, form, importCsOptions, props (+9 more)

### Community 21 - "Layout.vue"
Cohesion: 0.12
Nodes (14): ref_actions_laravel_passkeys_http_controllers_passkeyregistrationcontroller, resources_js_actions_app_http_controllers_settings_securitycontroller, Props, Props, { isCurrentOrParentUrl }, sidebarNavItems, Props, resources_js_routes_appearance_index (+6 more)

### Community 22 - "compilerOptions"
Cohesion: 0.09
Nodes (21): compilerOptions, allowJs, esModuleInterop, forceConsistentCasingInFileNames, isolatedModules, jsx, jsxImportSource, lib (+13 more)

### Community 23 - "AppSidebar.vue"
Cohesion: 0.11
Nodes (23): footerNavItems, mainNavItems, Props, { isCurrentUrl }, { isMobile, state }, page, user, resources_js_components_ui_dropdown_menu_index_dropdownmenu (+15 more)

### Community 27 - "InputOTPSlot.vue"
Cohesion: 0.11
Nodes (15): vue-input-otp, delegatedProps, emits, forwarded, props, delegatedProps, forwarded, props (+7 more)

### Community 28 - "bootstrap/app.php"
Cohesion: 0.15
Nodes (10): HandleInertiaRequests, {closure#1}(), {closure#2}(), {closure#3}(), Illuminate\Foundation\Application, Illuminate\Foundation\Configuration\Exceptions, Illuminate\Foundation\Configuration\Middleware, Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets (+2 more)

### Community 29 - "leads"
Cohesion: 0.06
Nodes (35): branches, lead_activity, lead_activity_lead_created_idx, lead_milestones, leads, leads_assigned_user_idx, leads_outcome_idx, leads_updated_at_idx (+27 more)

### Community 30 - "2026-11-25T08-39-06Z__resources-js-pages-auth-login-vue.md"
Cohesion: 0.25
Nodes (7): Design Health Score, Design Specificity Verdict, Minor Observations, Overall Impression, Persona Red Flags, Priority Issues, What's Working

### Community 31 - "LeadOutcomeFactors"
Cohesion: 0.13
Nodes (7): {closure#1}(), UpdateLeadRequest, LeadBudgetRanges, LeadOutcomeFactors, Illuminate\Validation\Rule, Illuminate\Validation\Rules\Unique, Illuminate\Validation\Validator

### Community 32 - "DashboardController"
Cohesion: 0.13
Nodes (3): DashboardController, Illuminate\Http\RedirectResponse, Inertia\Response

### Community 33 - "GoogleSheetSyncService"
Cohesion: 0.05
Nodes (17): BootstrapAdmin, SyncGoogleLeads, {closure#2}(), {closure#8}(), GoogleSheetSyncService, DailyLeadTabParser, Google\Auth\Credentials\ServiceAccountCredentials, Illuminate\Console\Command (+9 more)

### Community 34 - "KGJ Leads"
Cohesion: 0.11
Nodes (16): Auto deploy, Deploy manual, Migrasi dan backup, Panduan Deployment KGJ Leads, Production VPS 1, Admin, Alur kerja harian, Bantuan (+8 more)

### Community 35 - "dependencies"
Cohesion: 0.12
Nodes (17): dependencies, class-variance-authority, clsx, @inertiajs/vite, @inertiajs/vue3, @laravel/passkeys, laravel-vite-plugin, @lucide/vue (+9 more)

### Community 36 - "SidebarProvider.vue"
Cohesion: 0.15
Nodes (17): emits, isMobile, open, openMobile, props, setOpen(), setOpenMobile(), state (+9 more)

### Community 37 - "useAppearance.ts"
Cohesion: 0.19
Nodes (15): { appearance, updateAppearance }, tabs, appearance, getStoredAppearance(), handleSystemThemeChange(), initializeTheme(), mediaQuery(), prefersDark() (+7 more)

### Community 38 - "aliases"
Cohesion: 0.12
Nodes (15): aliases, components, composables, lib, ui, utils, iconLibrary, $schema (+7 more)

### Community 39 - "AppHeader.vue"
Cohesion: 0.10
Nodes (19): auth, { isCurrentUrl, whenCurrentUrl }, mainNavItems, page, Props, rightNavItems, resources_js_components_ui_navigation_menu_index_navigationmenu, resources_js_components_ui_navigation_menu_index_navigationmenuitem (+11 more)

### Community 40 - "scripts"
Cohesion: 0.14
Nodes (14): scripts, ci:check, dev, lint, lint:check, post-autoload-dump, post-create-project-cmd, post-root-package-install (+6 more)

### Community 41 - "web.php"
Cohesion: 0.22
Nodes (6): EnsureAdmin, HandleAppearance, Closure, Illuminate\Support\Facades\Route, Illuminate\Support\Facades\View, Symfony\Component\HttpFoundation\Response

### Community 42 - "Show.vue"
Cohesion: 0.07
Nodes (25): zod, budgetOptions, leadSchema, archiveDialogOpen, changeMilestone(), clientError, deleteDialogOpen, displayContact (+17 more)

### Community 43 - "global.d.ts"
Cohesion: 0.22
Nodes (9): Auth, ComponentCustomProperties, GlobalDirectives, ImportMeta, ImportMetaEnv, InertiaConfig, @inertiajs/core, vite/client (+1 more)

### Community 44 - "UserFactory.php"
Cohesion: 0.18
Nodes (5): UserFactory, Illuminate\Database\Eloquent\Factories\Factory, Illuminate\Support\Str, Pdo\Mysql, static

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

### Community 51 - "Separator.vue"
Cohesion: 0.29
Nodes (4): resources_js_components_ui_separator_index_separator, delegatedProps, props, props

### Community 52 - "Product"
Cohesion: 0.18
Nodes (10): Capabilities and Constraints, Evidence on Hand, Operating Context, Platform, Positioning, Product, Product Principles, Product Purpose (+2 more)

### Community 54 - "devDependencies"
Cohesion: 0.11
Nodes (17): devDependencies, concurrently, @laravel/vite-plugin-wayfinder, @tailwindcss/vite, @types/node, typescript, vite, vite-plus (+9 more)

### Community 55 - "Illuminate\Support\Facades\Schema"
Cohesion: 0.14
Nodes (7): {closure#1}(), {closure#2}(), {closure#1}(), {closure#1}(), {closure#2}(), Illuminate\Database\Migrations\Migration, Illuminate\Support\Facades\Schema

### Community 56 - "UserInfo.vue"
Cohesion: 0.15
Nodes (12): props, resources_js_components_ui_avatar_index_avatar, resources_js_components_ui_avatar_index_avatarfallback, resources_js_components_ui_avatar_index_avatarimage, { getInitials }, Props, showAvatar, getInitial() (+4 more)

### Community 57 - "require"
Cohesion: 0.20
Nodes (10): require, google/auth, inertiajs/inertia-laravel, laravel/chisel, laravel/fortify, laravel/framework, laravel/tinker, laravel/wayfinder (+2 more)

### Community 59 - "Collapsible.vue"
Cohesion: 0.22
Nodes (5): emits, forwarded, props, props, props

### Community 60 - "dialog/index.ts"
Cohesion: 0.22
Nodes (5): emits, forwarded, props, props, props

### Community 61 - "MarkOverdueLeadsForCrm.php"
Cohesion: 0.38
Nodes (3): {closure#2}(), {closure#3}(), MarkOverdueLeadsForCrm

### Community 62 - "useCurrentUrl.ts"
Cohesion: 0.33
Nodes (8): currentUrlReactive, page, useCurrentUrl(), isCurrentOrParentUrl(), isCurrentUrl(), whenCurrentUrl(), UseCurrentUrlReturn, toUrl()

### Community 63 - "DashboardTest.php"
Cohesion: 0.10
Nodes (18): {closure#1}(), {closure#10}(), {closure#11}(), {closure#13}(), {closure#14}(), {closure#15}(), {closure#16}(), {closure#17}() (+10 more)

### Community 64 - "tooltip/index.ts"
Cohesion: 0.22
Nodes (5): emits, forwarded, props, props, props

### Community 65 - "CsName"
Cohesion: 0.12
Nodes (13): {closure#13}(), {closure#23}(), {closure#26}(), {closure#30}(), {closure#31}(), {closure#32}(), {closure#33}(), {closure#34}() (+5 more)

### Community 66 - "Statistics.vue"
Cohesion: 0.13
Nodes (10): branch, outcomeDateFrom, outcomeDateTo, outcomePeriodText, outcomeReports, props, selectedBranchName, stages (+2 more)

### Community 67 - "config"
Cohesion: 0.29
Nodes (7): pestphp/pest-plugin, php-http/discovery, config, allow-plugins, optimize-autoloader, preferred-install, sort-packages

### Community 68 - "EmailVerificationTest"
Cohesion: 0.15
Nodes (4): Illuminate\Auth\Events\Verified, Illuminate\Support\Facades\Event, Illuminate\Support\Facades\URL, EmailVerificationTest

### Community 69 - "guardUnsavedChanges"
Cohesion: 0.13
Nodes (16): applyFilters(), applyFiltersNow(), assignSelected(), assignSelectedNow(), clearFilters(), createLead(), createLeadNow(), guardUnsavedChanges() (+8 more)

### Community 70 - "Badge.vue"
Cohesion: 0.24
Nodes (6): class-variance-authority, props, AlertVariants, delegatedProps, props, BadgeVariants

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
Cohesion: 0.25
Nodes (3): {closure#1}(), LeadSourceDate, DateTimeImmutable

### Community 80 - "TwoFactorRecoveryCodes.vue"
Cohesion: 0.11
Nodes (19): ref_routes_two_factor, { hasSetupData, clearTwoFactorAuthData }, Props, showSetupModal, isRecoveryCodesVisible, recoveryCodeSectionRef, { recoveryCodesList, fetchRecoveryCodes, errors }, resources_js_components_ui_card_index_card (+11 more)

### Community 81 - "types/index.ts"
Cohesion: 0.12
Nodes (16): Props, resources_js_components_ui_breadcrumb_index_breadcrumb, resources_js_components_ui_breadcrumb_index_breadcrumbitem, resources_js_components_ui_breadcrumb_index_breadcrumblink, resources_js_components_ui_breadcrumb_index_breadcrumblist, resources_js_components_ui_breadcrumb_index_breadcrumbpage, resources_js_components_ui_breadcrumb_index_breadcrumbseparator, resources_js_components_ui_sidebar_index_sidebartrigger (+8 more)

### Community 84 - "UserMenuContent.vue"
Cohesion: 0.17
Nodes (10): resources_js_actions_app_http_controllers_settings_profilecontroller, resources_js_components_ui_dropdown_menu_index_dropdownmenugroup, resources_js_components_ui_dropdown_menu_index_dropdownmenuitem, resources_js_components_ui_dropdown_menu_index_dropdownmenulabel, resources_js_components_ui_dropdown_menu_index_dropdownmenuseparator, Props, page, user (+2 more)

### Community 116 - "Project agent rules"
Cohesion: 0.25
Nodes (7): Data and database safety, Docker runtime verification, Project agent rules, Project context, Required working order, Scope and compatibility, Validation and tools

### Community 118 - "optionalDependencies"
Cohesion: 0.25
Nodes (8): optionalDependencies, @laravel/multiplex, lightningcss-linux-x64-gnu, lightningcss-win32-x64-msvc, @rollup/rollup-linux-x64-gnu, @rollup/rollup-win32-x64-msvc, @tailwindcss/oxide-linux-x64-gnu, @tailwindcss/oxide-win32-x64-msvc

### Community 119 - "SecurityTest.php"
Cohesion: 0.22
Nodes (3): {closure#1}(), {closure#2}(), SecurityTest

### Community 120 - "Illuminate\Foundation\Http\FormRequest"
Cohesion: 0.24
Nodes (5): PasswordUpdateRequest, ProfileDeleteRequest, ProfileUpdateRequest, Illuminate\Contracts\Validation\ValidationRule, Illuminate\Foundation\Http\FormRequest

### Community 121 - "clearSelection"
Cohesion: 0.33
Nodes (6): archiveSelected(), clearSelection(), destroySelected(), restoreSelected(), restoreSelectedNow(), toggleMobileSelectionMode()

### Community 122 - "PasskeyRegister.vue"
Cohesion: 0.17
Nodes (7): @laravel/passkeys, emit, name, { register, isLoading, error, isSupported }, showForm, delegatedProps, props

### Community 123 - "AppContent.vue"
Cohesion: 0.17
Nodes (10): className, Props, Props, resources_js_components_ui_sidebar_index_sidebarinset, resources_js_components_ui_sidebar_index_sidebarprovider, resources_js_types_index_appvariant, Appearance, AppVariant (+2 more)

### Community 124 - "@inertiajs/vue3"
Cohesion: 0.11
Nodes (13): @inertiajs/vue3, pages, Props, page, { title = '', description = '' }, initializeFlashToast(), priorityRows, props (+5 more)

### Community 126 - "queueSave"
Cohesion: 0.22
Nodes (10): changedPayload(), flushPendingSave(), isDirty, persistSaveState(), queueSave(), retrySave(), save(), saveAfterTyping() (+2 more)

### Community 127 - "AppServiceProvider.php"
Cohesion: 0.24
Nodes (6): AppServiceProvider, {closure#1}(), Carbon\CarbonImmutable, Illuminate\Support\Facades\Date, Illuminate\Support\ServiceProvider, Illuminate\Validation\Rules\Password

### Community 129 - "LeadSaveFeedback.vue"
Cohesion: 0.40
Nodes (4): emit, label, props, SaveState

### Community 131 - "Popover.vue"
Cohesion: 0.25
Nodes (5): emits, forwarded, props, forwardedProps, props

### Community 132 - "Laravel\Fortify\Features"
Cohesion: 0.25
Nodes (3): Laravel\Fortify\Features, {closure#1}(), TwoFactorChallengeTest

### Community 137 - "AlertError.vue"
Cohesion: 0.33
Nodes (5): Props, uniqueErrors, resources_js_components_ui_alert_index_alert, resources_js_components_ui_alert_index_alertdescription, resources_js_components_ui_alert_index_alerttitle

### Community 138 - "SheetContent.vue"
Cohesion: 0.22
Nodes (7): delegatedProps, emits, forwarded, props, SheetContentProps, delegatedProps, props

### Community 139 - "ProfileValidationRules"
Cohesion: 0.39
Nodes (3): CreateNewUser, ProfileValidationRules, Laravel\Fortify\Contracts\CreatesNewUsers

### Community 145 - "saveImmediately"
Cohesion: 0.33
Nodes (6): changeMilestone(), changeOutcome(), reopenClosing(), saveImmediately(), saveOutcome(), setClosingOutcome()

### Community 146 - "DialogContent.vue"
Cohesion: 0.25
Nodes (6): delegatedProps, emits, forwarded, props, delegatedProps, props

### Community 147 - ".historyPreview"
Cohesion: 0.40
Nodes (3): LogoutResponse, Illuminate\Http\JsonResponse, Laravel\Fortify\Contracts\LogoutResponse

### Community 148 - "SidebarMenuSkeleton.vue"
Cohesion: 0.25
Nodes (5): props, width, resources_js_components_ui_skeleton_index_skeleton, props, SkeletonProps

### Community 149 - "PasswordValidationRules"
Cohesion: 0.38
Nodes (3): ResetUserPassword, PasswordValidationRules, Laravel\Fortify\Contracts\ResetsUserPasswords

### Community 151 - "PasswordInput.vue"
Cohesion: 0.18
Nodes (8): inputRef, props, showPassword, resources_js_components_ui_input_index_input, emits, modelValue, props, props

### Community 153 - "PopoverContent.vue"
Cohesion: 0.40
Nodes (4): delegatedProps, emits, forwarded, props

### Community 154 - "TooltipContent.vue"
Cohesion: 0.40
Nodes (4): delegatedProps, emits, forwarded, props

## Knowledge Gaps
- **686 isolated node(s):** `$schema`, `style`, `config`, `css`, `baseColor` (+681 more)
  These have ≤1 connection - possible missing edges or undocumented components. (Counts symbols only; 1016 node(s) total have ≤1 connection when file, concept and rationale nodes are included.)
- **56 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `vue` connect `vue` to `LeadSaveFeedback.vue`, `reka-ui`, `Sidebar.vue`, `@vueuse/core`, `@lucide/vue`, `SelectContent.vue`, `Leads/Index.vue`, `AlertError.vue`, `SheetContent.vue`, `TwoFactorSetupModal.vue`, `DeleteUser.vue`, `package.json`, `button/index.ts`, `DialogContent.vue`, `SidebarMenuSkeleton.vue`, `Branches/Index.vue`, `AppSidebar.vue`, `PasswordInput.vue`, `PopoverContent.vue`, `TooltipContent.vue`, `InputOTPSlot.vue`, `SidebarProvider.vue`, `useAppearance.ts`, `AppHeader.vue`, `Show.vue`, `global.d.ts`, `LeadQuickUpdate.vue`, `LeadHistoryPopover.vue`, `Separator.vue`, `UserInfo.vue`, `useCurrentUrl.ts`, `Statistics.vue`, `Badge.vue`, `Accounts/Index.vue`, `TwoFactorRecoveryCodes.vue`, `types/index.ts`, `UserMenuContent.vue`, `PasskeyRegister.vue`, `AppContent.vue`, `@inertiajs/vue3`?**
  _High betweenness centrality (0.129) - this node is a cross-community bridge._
- **Why does `User` connect `User` to `EmailVerificationTest`, `Laravel\Fortify\Features`, `PasswordResetTest.php`, `ProfileValidationRules`, `UserFactory.php`, `Illuminate\Http\Request`, `DatabaseSeeder.php`, `VerificationNotificationTest.php`, `LeadManagementTest.php`, `TestCase`, `.user`, `PasswordValidationRules`, `SecurityTest.php`, `AuthenticationTest`, `DashboardTest.php`, `LeadOutcomeFactors`?**
  _High betweenness centrality (0.039) - this node is a cross-community bridge._
- **Why does `reka-ui` connect `reka-ui` to `Popover.vue`, `@vueuse/core`, `Sidebar.vue`, `@lucide/vue`, `SelectContent.vue`, `vue`, `SheetContent.vue`, `package.json`, `button/index.ts`, `DialogContent.vue`, `PopoverContent.vue`, `TooltipContent.vue`, `InputOTPSlot.vue`, `SidebarProvider.vue`, `Separator.vue`, `UserInfo.vue`, `Collapsible.vue`, `dialog/index.ts`, `tooltip/index.ts`, `Badge.vue`, `PasskeyRegister.vue`?**
  _High betweenness centrality (0.034) - this node is a cross-community bridge._
- **What connects `$schema`, `style`, `config` to the rest of the system?**
  _686 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `Illuminate\Database\Schema\Blueprint` be split into smaller, more focused modules?**
  _Cohesion score 0.09538461538461539 - nodes in this community are weakly interconnected._
- **Should `reka-ui` be split into smaller, more focused modules?**
  _Cohesion score 0.05708245243128964 - nodes in this community are weakly interconnected._
- **Should `LeadController.php` be split into smaller, more focused modules?**
  _Cohesion score 0.06341463414634146 - nodes in this community are weakly interconnected._