# Graph Report - marketing-leads  (2026-11-25)

## Corpus Check
- 325 files · ~152,292 words
- Verdict: corpus is large enough that graph structure adds value.
- Unclassified: 25 file(s) not represented in the graph (top: (none) 19, .example 2, .neon 1)

## Summary
- 1944 nodes · 3527 edges · 145 communities (90 shown, 55 thin omitted)
- Extraction: 99% EXTRACTED · 1% INFERRED · 0% AMBIGUOUS · INFERRED: 24 edges (avg confidence: 0.85)
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `bf4e2922`
- Run `git rev-parse HEAD` and compare to check if the graph is stale.
- Run `graphify update .` after code changes (no API cost).

## Community Hubs (Navigation)
- Illuminate\Database\Schema\Blueprint
- reka-ui
- LeadController.php
- Sidebar.vue
- @vueuse/core
- Inertia\Response
- @lucide/vue
- SelectContent.vue
- Leads/Index.vue
- User
- lib/utils.ts
- types/index.ts
- TwoFactorSetupModal.vue
- Illuminate\Http\Request
- LeadManagementTest.php
- DeleteUser.vue
- DashboardController.php
- TestCase
- package.json
- resources_js_components_ui_button_index_button
- Branches/Index.vue
- SidebarProvider.vue
- compilerOptions
- AppSidebar.vue
- LeadController
- AppHeader.vue
- resources_js_routes_two_factor_index
- InputOTPSlot.vue
- web.php
- leads
- useAppearance.ts
- PasswordValidationRules
- LeadImportService
- PasskeyRegister.vue
- KGJ Leads
- dependencies
- PopoverContent.vue
- @inertiajs/vue3
- aliases
- UserMenuContent.vue
- scripts
- GoogleSheetSyncService.php
- Show.vue
- global.d.ts
- UserFactory.php
- LeadManagementTest
- LeadQuickUpdate.vue
- Laravel\Fortify\Features
- LeadHistoryPopover.vue
- composer.json
- require-dev
- DailyLeadTabParser
- Product
- devDependencies
- 0001_01_01_000000_create_users_table.php
- bootstrap/app.php
- require
- AuthenticationTest
- Collapsible.vue
- dialog/index.ts
- SheetContent.vue
- useCurrentUrl.ts
- DashboardTest.php
- tooltip/index.ts
- Badge.vue
- SidebarMenuSkeleton.vue
- config
- EmailVerificationTest
- guardUnsavedChanges
- resources_js_routes_index
- PasswordResetTest
- psr-4
- laravel
- logging.php
- DatabaseSeeder.php
- Design
- Illuminate\Support\Facades\DB
- console.php
- LeadOutcomeFactors
- TwoFactorRecoveryCodes.vue
- FortifyServiceProvider.php
- GLOSSARY.md
- artisan
- button/index.ts
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
- GoogleSheetSyncService
- Project agent rules
- optionalDependencies
- SecurityTest.php
- AvatarFallback.vue
- clearSelection
- scripts
- ProfileUpdateTest
- Accounts/Index.vue
- queueSave
- vite.config.ts
- Popover.vue
- LeadSaveFeedback.vue
- outcomeLabel
- overrides
- Input.vue
- resolveLeadConflict
- Sonner.vue
- isDirty
- AlertError.vue
- createDesktopRowResizeObserver
- hasUnsavedChanges
- LeadEditState
- observeDesktopViewport
- vue

## God Nodes (most connected - your core abstractions)
1. `vue` - 130 edges
2. `cn()` - 94 edges
3. `reka-ui` - 80 edges
4. `User` - 78 edges
5. `LeadManagementTest` - 67 edges
6. `@inertiajs/vue3` - 46 edges
7. `@vueuse/core` - 46 edges
8. `LeadController` - 45 edges
9. `@lucide/vue` - 40 edges
10. `TestCase` - 34 edges

## Surprising Connections (you probably didn't know these)
- `DashboardController` --inherits--> `Controller`  [EXTRACTED]
  app/Http/Controllers/DashboardController.php → app/Http/Controllers/Controller.php
- `LeadController` --inherits--> `Controller`  [EXTRACTED]
  app/Http/Controllers/LeadController.php → app/Http/Controllers/Controller.php
- `LeadController` --references--> `LeadImportService`  [EXTRACTED]
  app/Http/Controllers/LeadController.php → app/Services/LeadImportService.php
- `LeadController` --references--> `DailyLeadTabParser`  [EXTRACTED]
  app/Http/Controllers/LeadController.php → app/Support/DailyLeadTabParser.php
- `{closure#1}()` --calls--> `LeadOutcomeFactors`  [EXTRACTED]
  app/Http/Requests/UpdateLeadRequest.php → app/Support/LeadOutcomeFactors.php

## Import Cycles
- None detected.

## Communities (145 total, 55 thin omitted)

### Community 0 - "Illuminate\Database\Schema\Blueprint"
Cohesion: 0.11
Nodes (16): {closure#1}(), {closure#2}(), {closure#3}(), {closure#1}(), {closure#2}(), Illuminate\Database\Schema\Blueprint, {closure#3}(), {closure#7}() (+8 more)

### Community 1 - "reka-ui"
Cohesion: 0.06
Nodes (32): reka-ui, emits, forwarded, props, delegatedProps, emits, forwarded, props (+24 more)

### Community 2 - "LeadController.php"
Cohesion: 0.07
Nodes (16): {closure#12}(), {closure#16}(), {closure#17}(), {closure#19}(), {closure#20}(), {closure#21}(), {closure#23}(), {closure#27}() (+8 more)

### Community 3 - "Sidebar.vue"
Cohesion: 0.10
Nodes (14): emits, forwarded, props, props, delegatedProps, props, props, props (+6 more)

### Community 4 - "@vueuse/core"
Cohesion: 0.06
Nodes (30): @vueuse/core, delegatedProps, props, navigationMenuTriggerStyle, delegatedProps, emits, forwarded, props (+22 more)

### Community 5 - "Inertia\Response"
Cohesion: 0.11
Nodes (8): Controller, ProfileController, SecurityController, Illuminate\Auth\Middleware\RequirePassword, Illuminate\Contracts\Auth\MustVerifyEmail, Illuminate\Support\Facades\Auth, Inertia\Inertia, Inertia\Response

### Community 6 - "@lucide/vue"
Cohesion: 0.07
Nodes (20): @lucide/vue, delegatedProps, emits, forwarded, props, delegatedProps, emits, forwarded (+12 more)

### Community 7 - "SelectContent.vue"
Cohesion: 0.06
Nodes (21): resources_js_components_ui_select_index_selectscrolldownbutton, resources_js_components_ui_select_index_selectscrollupbutton, emits, forwarded, props, delegatedProps, emits, forwarded (+13 more)

### Community 8 - "Leads/Index.vue"
Cohesion: 0.02
Nodes (72): resources_js_components_ui_sheet_index_sheetdescription, allVisibleSelected, archiveDialogOpen, assistingLeadId, branch, budget, bulkCsAccounts, bulkForm (+64 more)

### Community 9 - "User"
Cohesion: 0.10
Nodes (9): User, Illuminate\Database\Eloquent\Factories\HasFactory, Illuminate\Foundation\Auth\User, Illuminate\Notifications\Notifiable, Illuminate\Support\Carbon, Laravel\Fortify\Contracts\PasskeyUser, Laravel\Fortify\PasskeyAuthenticatable, Laravel\Fortify\TwoFactorAuthenticatable (+1 more)

### Community 10 - "lib/utils.ts"
Cohesion: 0.06
Nodes (35): props, props, props, props, props, props, props, props (+27 more)

### Community 11 - "types/index.ts"
Cohesion: 0.10
Nodes (21): className, Props, Props, Props, resources_js_components_ui_breadcrumb_index_breadcrumb, resources_js_components_ui_breadcrumb_index_breadcrumbitem, resources_js_components_ui_breadcrumb_index_breadcrumblink, resources_js_components_ui_breadcrumb_index_breadcrumblist (+13 more)

### Community 12 - "TwoFactorSetupModal.vue"
Cohesion: 0.10
Nodes (17): ref_routes_two_factor_login, code, { copy, copied }, isOpen, modalConfig, pinInputContainerRef, Props, { qrCodeSvg, manualSetupKey, clearSetupData, fetchSetupData, errors } (+9 more)

### Community 14 - "LeadManagementTest.php"
Cohesion: 0.13
Nodes (26): Illuminate\Http\Client\Request, Illuminate\Http\UploadedFile, Inertia\Testing\AssertableInertia, Mockery, OpenSpout\Common\Entity\Row, OpenSpout\Writer\XLSX\Writer, {closure#16}(), {closure#10}() (+18 more)

### Community 15 - "DeleteUser.vue"
Cohesion: 0.15
Nodes (18): passwordInput, emit, handleDelete(), isDeleting, props, resources_js_components_ui_dialog_index_dialog, resources_js_components_ui_dialog_index_dialogclose, resources_js_components_ui_dialog_index_dialogcontent (+10 more)

### Community 16 - "DashboardController.php"
Cohesion: 0.15
Nodes (14): {closure#10}(), {closure#11}(), {closure#13}(), {closure#14}(), {closure#2}(), {closure#3}(), {closure#4}(), {closure#5}() (+6 more)

### Community 17 - "TestCase"
Cohesion: 0.09
Nodes (11): Illuminate\Foundation\Testing\RefreshDatabase, Illuminate\Foundation\Testing\TestCase, {closure#1}(), PasswordConfirmationTest, RegistrationTest, {closure#1}(), TwoFactorChallengeTest, ExampleTest (+3 more)

### Community 18 - "package.json"
Cohesion: 0.09
Nodes (21): private, $schema, type, clsx, concurrently, @inertiajs/vite, @laravel/multiplex, @laravel/vite-plugin-wayfinder (+13 more)

### Community 19 - "resources_js_components_ui_button_index_button"
Cohesion: 0.11
Nodes (25): ref_actions_laravel_passkeys_http_controllers_passkeyconfirmationcontroller, ref_inertiajs_core, ref_routes_register, ref_routes_verification, resources_js_actions_app_http_controllers_settings_profilecontroller, Props, { verify, isLoading, error, isSupported }, inputRef (+17 more)

### Community 20 - "Branches/Index.vue"
Cohesion: 0.10
Nodes (10): Branch, branchToDelete, canSync(), editingId, form, importCsOptions, props, readyBranches (+2 more)

### Community 21 - "SidebarProvider.vue"
Cohesion: 0.15
Nodes (17): emits, isMobile, open, openMobile, props, setOpen(), setOpenMobile(), state (+9 more)

### Community 22 - "compilerOptions"
Cohesion: 0.09
Nodes (21): compilerOptions, allowJs, esModuleInterop, forceConsistentCasingInFileNames, isolatedModules, jsx, jsxImportSource, lib (+13 more)

### Community 23 - "AppSidebar.vue"
Cohesion: 0.11
Nodes (23): footerNavItems, mainNavItems, Props, { isCurrentUrl }, { isMobile, state }, page, user, resources_js_components_ui_dropdown_menu_index_dropdownmenu (+15 more)

### Community 25 - "AppHeader.vue"
Cohesion: 0.09
Nodes (21): auth, { isCurrentUrl, whenCurrentUrl }, mainNavItems, page, Props, rightNavItems, resources_js_components_ui_navigation_menu_index_navigationmenu, resources_js_components_ui_navigation_menu_index_navigationmenuitem (+13 more)

### Community 27 - "InputOTPSlot.vue"
Cohesion: 0.11
Nodes (15): vue-input-otp, delegatedProps, emits, forwarded, props, delegatedProps, forwarded, props (+7 more)

### Community 28 - "web.php"
Cohesion: 0.22
Nodes (6): EnsureAdmin, HandleAppearance, Closure, Illuminate\Support\Facades\Route, Illuminate\Support\Facades\View, Symfony\Component\HttpFoundation\Response

### Community 29 - "leads"
Cohesion: 0.07
Nodes (28): branches, lead_activity, lead_activity_lead_created_idx, lead_milestones, leads, leads_assigned_user_idx, leads_outcome_idx, leads_updated_at_idx (+20 more)

### Community 30 - "useAppearance.ts"
Cohesion: 0.15
Nodes (18): { appearance, updateAppearance }, tabs, appearance, getStoredAppearance(), handleSystemThemeChange(), initializeTheme(), mediaQuery(), prefersDark() (+10 more)

### Community 31 - "PasswordValidationRules"
Cohesion: 0.05
Nodes (26): CreateNewUser, ResetUserPassword, PasswordValidationRules, ProfileValidationRules, PasswordUpdateRequest, ProfileDeleteRequest, ProfileUpdateRequest, TwoFactorAuthenticationRequest (+18 more)

### Community 32 - "LeadImportService"
Cohesion: 0.13
Nodes (6): {closure#1}(), {closure#2}(), {closure#4}(), LeadImportService, LeadSourceDate, DateTimeImmutable

### Community 33 - "PasskeyRegister.vue"
Cohesion: 0.25
Nodes (5): @laravel/passkeys, emit, name, { register, isLoading, error, isSupported }, showForm

### Community 34 - "KGJ Leads"
Cohesion: 0.11
Nodes (16): Auto deploy, Deploy manual, Migrasi dan backup, Panduan Deployment KGJ Leads, Production VPS 1, Admin, Alur kerja harian, Bantuan (+8 more)

### Community 35 - "dependencies"
Cohesion: 0.12
Nodes (17): dependencies, class-variance-authority, clsx, @inertiajs/vite, @inertiajs/vue3, @laravel/passkeys, laravel-vite-plugin, @lucide/vue (+9 more)

### Community 36 - "PopoverContent.vue"
Cohesion: 0.40
Nodes (4): delegatedProps, emits, forwarded, props

### Community 37 - "@inertiajs/vue3"
Cohesion: 0.09
Nodes (16): @inertiajs/vue3, pages, initializeFlashToast(), priorityRows, props, branch, outcomeDateFrom, outcomeDateTo (+8 more)

### Community 38 - "aliases"
Cohesion: 0.12
Nodes (15): aliases, components, composables, lib, ui, utils, iconLibrary, $schema (+7 more)

### Community 39 - "UserMenuContent.vue"
Cohesion: 0.12
Nodes (16): resources_js_components_ui_avatar_index_avatar, resources_js_components_ui_avatar_index_avatarfallback, resources_js_components_ui_avatar_index_avatarimage, resources_js_components_ui_dropdown_menu_index_dropdownmenugroup, resources_js_components_ui_dropdown_menu_index_dropdownmenuitem, resources_js_components_ui_dropdown_menu_index_dropdownmenulabel, resources_js_components_ui_dropdown_menu_index_dropdownmenuseparator, { getInitials } (+8 more)

### Community 40 - "scripts"
Cohesion: 0.14
Nodes (14): scripts, ci:check, dev, lint, lint:check, post-autoload-dump, post-create-project-cmd, post-root-package-install (+6 more)

### Community 41 - "GoogleSheetSyncService.php"
Cohesion: 0.13
Nodes (8): {closure#2}(), {closure#8}(), Google\Auth\Credentials\ServiceAccountCredentials, Illuminate\Http\Client\PendingRequest, Illuminate\Http\Client\RequestException, Illuminate\Support\Facades\Cache, Illuminate\Support\Facades\Http, Throwable

### Community 42 - "Show.vue"
Cohesion: 0.07
Nodes (23): zod, budgetOptions, leadSchema, archiveDialogOpen, assisting, clientError, collaboratorForm, deleteDialogOpen (+15 more)

### Community 43 - "global.d.ts"
Cohesion: 0.22
Nodes (9): Auth, ComponentCustomProperties, GlobalDirectives, ImportMeta, ImportMetaEnv, InertiaConfig, @inertiajs/core, vite/client (+1 more)

### Community 44 - "UserFactory.php"
Cohesion: 0.18
Nodes (5): UserFactory, Illuminate\Database\Eloquent\Factories\Factory, Illuminate\Support\Str, Pdo\Mysql, static

### Community 46 - "LeadQuickUpdate.vue"
Cohesion: 0.09
Nodes (21): changeOutcome(), emit, groups, hasConflict, hasSaved, initialValues, invalidField, Lead (+13 more)

### Community 47 - "Laravel\Fortify\Features"
Cohesion: 0.17
Nodes (5): Illuminate\Auth\Notifications\ResetPassword, Illuminate\Auth\Notifications\VerifyEmail, Illuminate\Support\Facades\Notification, Laravel\Fortify\Features, VerificationNotificationTest

### Community 48 - "LeadHistoryPopover.vue"
Cohesion: 0.12
Nodes (15): elapsed(), error, followUpLabel, history, HistoryEntry, HistoryMeta, leadAge, loading (+7 more)

### Community 49 - "composer.json"
Cohesion: 0.17
Nodes (11): autoload-dev, psr-4, description, keywords, license, minimum-stability, name, prefer-stable (+3 more)

### Community 50 - "require-dev"
Cohesion: 0.17
Nodes (12): require-dev, fakerphp/faker, larastan/larastan, laravel/pail, laravel/pao, laravel/pint, laravel/sail, mockery/mockery (+4 more)

### Community 51 - "DailyLeadTabParser"
Cohesion: 0.18
Nodes (3): DailyLeadTabParser, RuntimeException, DailyLeadTabParserTest

### Community 52 - "Product"
Cohesion: 0.18
Nodes (10): Capabilities and Constraints, Evidence on Hand, Operating Context, Platform, Positioning, Product, Product Principles, Product Purpose (+2 more)

### Community 54 - "devDependencies"
Cohesion: 0.20
Nodes (10): devDependencies, concurrently, @laravel/vite-plugin-wayfinder, @tailwindcss/vite, @types/node, typescript, vite, vite-plus (+2 more)

### Community 55 - "0001_01_01_000000_create_users_table.php"
Cohesion: 0.13
Nodes (8): {closure#1}(), {closure#2}(), {closure#3}(), {closure#1}(), {closure#2}(), {closure#1}(), Illuminate\Database\Migrations\Migration, Illuminate\Support\Facades\Schema

### Community 56 - "bootstrap/app.php"
Cohesion: 0.16
Nodes (9): HandleInertiaRequests, {closure#1}(), {closure#2}(), {closure#3}(), Illuminate\Foundation\Application, Illuminate\Foundation\Configuration\Exceptions, Illuminate\Foundation\Configuration\Middleware, Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets (+1 more)

### Community 57 - "require"
Cohesion: 0.20
Nodes (10): require, google/auth, inertiajs/inertia-laravel, laravel/chisel, laravel/fortify, laravel/framework, laravel/tinker, laravel/wayfinder (+2 more)

### Community 59 - "Collapsible.vue"
Cohesion: 0.22
Nodes (5): emits, forwarded, props, props, props

### Community 60 - "dialog/index.ts"
Cohesion: 0.06
Nodes (23): emits, forwarded, props, props, delegatedProps, emits, forwarded, props (+15 more)

### Community 61 - "SheetContent.vue"
Cohesion: 0.22
Nodes (7): delegatedProps, emits, forwarded, props, SheetContentProps, delegatedProps, props

### Community 62 - "useCurrentUrl.ts"
Cohesion: 0.33
Nodes (8): currentUrlReactive, page, useCurrentUrl(), isCurrentOrParentUrl(), isCurrentUrl(), whenCurrentUrl(), UseCurrentUrlReturn, toUrl()

### Community 63 - "DashboardTest.php"
Cohesion: 0.12
Nodes (15): {closure#1}(), {closure#10}(), {closure#11}(), {closure#12}(), {closure#13}(), {closure#14}(), {closure#15}(), {closure#17}() (+7 more)

### Community 64 - "tooltip/index.ts"
Cohesion: 0.22
Nodes (5): emits, forwarded, props, props, props

### Community 65 - "Badge.vue"
Cohesion: 0.24
Nodes (6): class-variance-authority, props, AlertVariants, delegatedProps, props, BadgeVariants

### Community 66 - "SidebarMenuSkeleton.vue"
Cohesion: 0.25
Nodes (5): props, width, resources_js_components_ui_skeleton_index_skeleton, props, SkeletonProps

### Community 67 - "config"
Cohesion: 0.29
Nodes (7): pestphp/pest-plugin, php-http/discovery, config, allow-plugins, optimize-autoloader, preferred-install, sort-packages

### Community 68 - "EmailVerificationTest"
Cohesion: 0.15
Nodes (4): Illuminate\Auth\Events\Verified, Illuminate\Support\Facades\Event, Illuminate\Support\Facades\URL, EmailVerificationTest

### Community 69 - "guardUnsavedChanges"
Cohesion: 0.10
Nodes (22): addCollaboratorSelected(), addCollaboratorSelectedNow(), applyFilters(), applyFiltersNow(), assignSelected(), assignSelectedNow(), clearFilters(), createLead() (+14 more)

### Community 70 - "resources_js_routes_index"
Cohesion: 0.16
Nodes (8): Props, page, { title = '', description = '' }, resources_js_routes_index, resources_js_routes_index_dashboard, resources_js_routes_index_home, resources_js_routes_index_login, resources_js_routes_index_register

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

### Community 77 - "Illuminate\Support\Facades\DB"
Cohesion: 0.17
Nodes (8): BootstrapAdmin, {closure#2}(), {closure#3}(), MarkOverdueLeadsForCrm, SyncGoogleLeads, Illuminate\Console\Command, Illuminate\Support\Facades\DB, Illuminate\Support\Facades\Validator

### Community 78 - "console.php"
Cohesion: 0.40
Nodes (3): Illuminate\Foundation\Inspiring, Illuminate\Support\Facades\Artisan, Illuminate\Support\Facades\Schedule

### Community 80 - "TwoFactorRecoveryCodes.vue"
Cohesion: 0.12
Nodes (19): ref_routes_two_factor, { hasSetupData, clearTwoFactorAuthData }, Props, showSetupModal, isRecoveryCodesVisible, recoveryCodeSectionRef, { recoveryCodesList, fetchRecoveryCodes, errors }, resources_js_components_ui_card_index_card (+11 more)

### Community 81 - "FortifyServiceProvider.php"
Cohesion: 0.10
Nodes (15): LoginResponse, LogoutResponse, {closure#1}(), {closure#10}(), {closure#11}(), {closure#2}(), {closure#3}(), {closure#4}() (+7 more)

### Community 84 - "button/index.ts"
Cohesion: 0.10
Nodes (17): ref_actions_laravel_passkeys_http_controllers_passkeyregistrationcontroller, resources_js_actions_app_http_controllers_settings_securitycontroller, Props, Props, resources_js_components_ui_separator_index_separator, { isCurrentOrParentUrl }, sidebarNavItems, Props (+9 more)

### Community 116 - "Project agent rules"
Cohesion: 0.25
Nodes (7): Data and database safety, Docker runtime verification, Project agent rules, Project context, Required working order, Scope and compatibility, Validation and tools

### Community 118 - "optionalDependencies"
Cohesion: 0.25
Nodes (8): optionalDependencies, @laravel/multiplex, lightningcss-linux-x64-gnu, lightningcss-win32-x64-msvc, @rollup/rollup-linux-x64-gnu, @rollup/rollup-win32-x64-msvc, @tailwindcss/oxide-linux-x64-gnu, @tailwindcss/oxide-win32-x64-msvc

### Community 119 - "SecurityTest.php"
Cohesion: 0.20
Nodes (4): Illuminate\Support\Facades\Hash, {closure#1}(), {closure#2}(), SecurityTest

### Community 120 - "AvatarFallback.vue"
Cohesion: 0.25
Nodes (4): props, delegatedProps, props, props

### Community 121 - "clearSelection"
Cohesion: 0.33
Nodes (6): archiveSelected(), clearSelection(), destroySelected(), restoreSelected(), restoreSelectedNow(), toggleMobileSelectionMode()

### Community 122 - "scripts"
Cohesion: 0.29
Nodes (7): scripts, build, build:ssr, check, check:fix, dev, types:check

### Community 124 - "Accounts/Index.vue"
Cohesion: 0.20
Nodes (8): Account, accountToToggle, cancel(), editingId, form, needsBranch, props, save()

### Community 126 - "queueSave"
Cohesion: 0.33
Nodes (7): changedPayload(), flushPendingSave(), persistSaveState(), queueSave(), retrySave(), save(), saveAfterTyping()

### Community 127 - "vite.config.ts"
Cohesion: 0.40
Nodes (4): laravel-vite-plugin, @tailwindcss/vite, vite-plus, @vitejs/plugin-vue

### Community 128 - "Popover.vue"
Cohesion: 0.25
Nodes (5): emits, forwarded, props, forwardedProps, props

### Community 129 - "LeadSaveFeedback.vue"
Cohesion: 0.40
Nodes (4): emit, label, props, SaveState

### Community 132 - "Input.vue"
Cohesion: 0.50
Nodes (3): emits, modelValue, props

### Community 138 - "isDirty"
Cohesion: 0.67
Nodes (3): isDirty, signature(), snapshot()

### Community 139 - "AlertError.vue"
Cohesion: 0.33
Nodes (5): Props, uniqueErrors, resources_js_components_ui_alert_index_alert, resources_js_components_ui_alert_index_alertdescription, resources_js_components_ui_alert_index_alerttitle

### Community 148 - "vue"
Cohesion: 0.08
Nodes (18): vue, props, props, props, props, props, props, props (+10 more)

## Knowledge Gaps
- **661 isolated node(s):** `$schema`, `style`, `config`, `css`, `baseColor` (+656 more)
  These have ≤1 connection - possible missing edges or undocumented components. (Counts symbols only; 956 node(s) total have ≤1 connection when file, concept and rationale nodes are included.)
- **55 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `vue` connect `vue` to `LeadSaveFeedback.vue`, `reka-ui`, `Sidebar.vue`, `Input.vue`, `@vueuse/core`, `@lucide/vue`, `SelectContent.vue`, `Leads/Index.vue`, `lib/utils.ts`, `types/index.ts`, `AlertError.vue`, `TwoFactorSetupModal.vue`, `DeleteUser.vue`, `package.json`, `resources_js_components_ui_button_index_button`, `Branches/Index.vue`, `SidebarProvider.vue`, `AppSidebar.vue`, `AppHeader.vue`, `InputOTPSlot.vue`, `useAppearance.ts`, `PasskeyRegister.vue`, `PopoverContent.vue`, `@inertiajs/vue3`, `UserMenuContent.vue`, `Show.vue`, `global.d.ts`, `LeadQuickUpdate.vue`, `LeadHistoryPopover.vue`, `dialog/index.ts`, `SheetContent.vue`, `useCurrentUrl.ts`, `Badge.vue`, `SidebarMenuSkeleton.vue`, `resources_js_routes_index`, `TwoFactorRecoveryCodes.vue`, `AvatarFallback.vue`, `Accounts/Index.vue`?**
  _High betweenness centrality (0.111) - this node is a cross-community bridge._
- **Why does `User` connect `User` to `EmailVerificationTest`, `PasswordResetTest`, `DatabaseSeeder.php`, `UserFactory.php`, `LeadManagementTest`, `LeadManagementTest.php`, `Laravel\Fortify\Features`, `FortifyServiceProvider.php`, `TestCase`, `SecurityTest.php`, `AuthenticationTest`, `ProfileUpdateTest`, `DashboardTest.php`, `PasswordValidationRules`?**
  _High betweenness centrality (0.046) - this node is a cross-community bridge._
- **Why does `@inertiajs/vue3` connect `@inertiajs/vue3` to `Leads/Index.vue`, `lib/utils.ts`, `types/index.ts`, `TwoFactorSetupModal.vue`, `DeleteUser.vue`, `package.json`, `resources_js_components_ui_button_index_button`, `Branches/Index.vue`, `AppSidebar.vue`, `AppHeader.vue`, `UserMenuContent.vue`, `Show.vue`, `LeadQuickUpdate.vue`, `LeadHistoryPopover.vue`, `useCurrentUrl.ts`, `resources_js_routes_index`, `TwoFactorRecoveryCodes.vue`, `button/index.ts`, `Accounts/Index.vue`?**
  _High betweenness centrality (0.041) - this node is a cross-community bridge._
- **What connects `$schema`, `style`, `config` to the rest of the system?**
  _661 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `Illuminate\Database\Schema\Blueprint` be split into smaller, more focused modules?**
  _Cohesion score 0.11255411255411256 - nodes in this community are weakly interconnected._
- **Should `reka-ui` be split into smaller, more focused modules?**
  _Cohesion score 0.05708245243128964 - nodes in this community are weakly interconnected._
- **Should `LeadController.php` be split into smaller, more focused modules?**
  _Cohesion score 0.06827880512091039 - nodes in this community are weakly interconnected._