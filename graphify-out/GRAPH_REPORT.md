# Graph Report - marketing-leads  (2026-11-26)

## Corpus Check
- 330 files · ~159,492 words
- Verdict: corpus is large enough that graph structure adds value.
- Unclassified: 25 file(s) not represented in the graph (top: (none) 19, .example 2, .neon 1)

## Summary
- 2064 nodes · 3739 edges · 152 communities (97 shown, 55 thin omitted)
- Extraction: 99% EXTRACTED · 1% INFERRED · 0% AMBIGUOUS · INFERRED: 24 edges (avg confidence: 0.85)
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `d00f20af`
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
- vue
- Statistics.vue
- TwoFactorSetupModal.vue
- DashboardController
- LeadManagementTest.php
- DeleteUser.vue
- DashboardController.php
- TestCase
- package.json
- button/index.ts
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
- 2026-11-25T08-39-06Z__resources-js-pages-auth-login-vue.md
- PasswordValidationRules
- LeadImportService
- SheetContent.vue
- KGJ Leads
- dependencies
- PasskeyRegister.vue
- useAppearance.ts
- aliases
- UserInfo.vue
- scripts
- Separator.vue
- Show.vue
- global.d.ts
- UserFactory.php
- LeadManagementTest
- LeadQuickUpdate.vue
- VerificationNotificationTest.php
- LeadHistoryPopover.vue
- composer.json
- require-dev
- PopoverContent.vue
- Product
- devDependencies
- Illuminate\Support\Facades\Schema
- bootstrap/app.php
- require
- AuthenticationTest
- Collapsible.vue
- dialog/index.ts
- PasswordInput.vue
- useCurrentUrl.ts
- DashboardTest.php
- tooltip/index.ts
- Badge.vue
- SidebarMenuSkeleton.vue
- config
- EmailVerificationTest
- guardUnsavedChanges
- @inertiajs/vue3
- PasswordResetTest.php
- psr-4
- laravel
- logging.php
- DatabaseSeeder.php
- Design
- GoogleSheetSyncService
- console.php
- LeadOutcomeFactors
- TwoFactorRecoveryCodes.vue
- types/index.ts
- GLOSSARY.md
- artisan
- Layout.vue
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
- Illuminate\Database\Query\Builder
- Project agent rules
- optionalDependencies
- SecurityTest.php
- DailyLeadTabParser
- clearSelection
- GoogleSheetSyncService.php
- Accounts/Index.vue
- LeadImportService.php
- queueSave
- Illuminate\Console\Command
- ownerLabel
- LeadSaveFeedback.vue
- outcomeLabel
- DailyLeadTabParserTest
- Laravel\Fortify\Features
- resolveLeadConflict
- NavUser.vue
- DialogContent.vue
- Popover.vue
- AlertError.vue
- createDesktopRowResizeObserver
- hasUnsavedChanges
- LeadEditState
- observeDesktopViewport
- saveImmediately
- UserMenuContent.vue
- Illuminate\Http\Request
- Illuminate\Support\Facades\DB
- .historyPreview
- TooltipContent.vue
- Login.vue

## God Nodes (most connected - your core abstractions)
1. `vue` - 130 edges
2. `cn()` - 94 edges
3. `User` - 84 edges
4. `reka-ui` - 80 edges
5. `LeadManagementTest` - 78 edges
6. `@inertiajs/vue3` - 46 edges
7. `@vueuse/core` - 46 edges
8. `LeadController` - 45 edges
9. `DashboardController` - 41 edges
10. `@lucide/vue` - 40 edges

## Surprising Connections (you probably didn't know these)
- `{closure#4}()` --calls--> `CsName`  [EXTRACTED]
  app/Services/LeadImportService.php → app/Support/CsName.php
- `{closure#5}()` --calls--> `CsName`  [EXTRACTED]
  app/Services/LeadImportService.php → app/Support/CsName.php
- `DashboardController` --inherits--> `Controller`  [EXTRACTED]
  app/Http/Controllers/DashboardController.php → app/Http/Controllers/Controller.php
- `LeadController` --inherits--> `Controller`  [EXTRACTED]
  app/Http/Controllers/LeadController.php → app/Http/Controllers/Controller.php
- `{closure#10}()` --calls--> `CsName`  [EXTRACTED]
  app/Http/Controllers/DashboardController.php → app/Support/CsName.php

## Import Cycles
- None detected.

## Communities (152 total, 55 thin omitted)

### Community 0 - "Illuminate\Database\Schema\Blueprint"
Cohesion: 0.11
Nodes (18): {closure#1}(), {closure#2}(), {closure#3}(), {closure#1}(), {closure#2}(), {closure#3}(), Illuminate\Database\Schema\Blueprint, {closure#10}() (+10 more)

### Community 1 - "reka-ui"
Cohesion: 0.05
Nodes (36): reka-ui, emits, forwarded, props, delegatedProps, emits, forwarded, props (+28 more)

### Community 2 - "LeadController.php"
Cohesion: 0.07
Nodes (16): {closure#12}(), {closure#16}(), {closure#17}(), {closure#19}(), {closure#20}(), {closure#21}(), {closure#22}(), {closure#24}() (+8 more)

### Community 3 - "Sidebar.vue"
Cohesion: 0.10
Nodes (15): resources_js_components_ui_sheet_index_sheet, resources_js_components_ui_sheet_index_sheetcontent, emits, forwarded, props, props, delegatedProps, props (+7 more)

### Community 4 - "@vueuse/core"
Cohesion: 0.05
Nodes (33): @vueuse/core, delegatedProps, props, delegatedProps, forwardedProps, props, navigationMenuTriggerStyle, delegatedProps (+25 more)

### Community 5 - "Inertia\Response"
Cohesion: 0.11
Nodes (8): Controller, ProfileController, SecurityController, Illuminate\Auth\Middleware\RequirePassword, Illuminate\Contracts\Auth\MustVerifyEmail, Illuminate\Support\Facades\Auth, Inertia\Inertia, Inertia\Response

### Community 6 - "@lucide/vue"
Cohesion: 0.07
Nodes (20): @lucide/vue, props, delegatedProps, emits, forwarded, props, delegatedProps, emits (+12 more)

### Community 7 - "SelectContent.vue"
Cohesion: 0.07
Nodes (20): resources_js_components_ui_select_index_selectscrolldownbutton, resources_js_components_ui_select_index_selectscrollupbutton, emits, forwarded, props, delegatedProps, emits, forwarded (+12 more)

### Community 8 - "Leads/Index.vue"
Cohesion: 0.02
Nodes (74): resources_js_components_ui_sheet_index_sheetdescription, allVisibleSelected, archiveDialogOpen, assistingLeadId, branch, budget, bulkCsAccounts, bulkForm (+66 more)

### Community 9 - "User"
Cohesion: 0.09
Nodes (9): User, Illuminate\Database\Eloquent\Factories\HasFactory, Illuminate\Foundation\Auth\User, Illuminate\Notifications\Notifiable, Illuminate\Support\Carbon, Laravel\Fortify\Contracts\PasskeyUser, Laravel\Fortify\PasskeyAuthenticatable, Laravel\Fortify\TwoFactorAuthenticatable (+1 more)

### Community 10 - "vue"
Cohesion: 0.05
Nodes (46): vue, props, props, props, props, props, props, props (+38 more)

### Community 11 - "Statistics.vue"
Cohesion: 0.13
Nodes (10): branch, outcomeDateFrom, outcomeDateTo, outcomePeriodText, outcomeReports, props, selectedBranchName, stages (+2 more)

### Community 12 - "TwoFactorSetupModal.vue"
Cohesion: 0.09
Nodes (20): ref_routes_two_factor_login, code, { copy, copied }, isOpen, modalConfig, pinInputContainerRef, Props, { qrCodeSvg, manualSetupKey, clearSetupData, fetchSetupData, errors } (+12 more)

### Community 14 - "LeadManagementTest.php"
Cohesion: 0.11
Nodes (29): Illuminate\Http\Client\Request, Illuminate\Http\UploadedFile, Inertia\Testing\AssertableInertia, Mockery, OpenSpout\Common\Entity\Row, OpenSpout\Writer\XLSX\Writer, {closure#16}(), {closure#20}() (+21 more)

### Community 15 - "DeleteUser.vue"
Cohesion: 0.15
Nodes (18): passwordInput, emit, handleDelete(), isDeleting, props, resources_js_components_ui_dialog_index_dialog, resources_js_components_ui_dialog_index_dialogclose, resources_js_components_ui_dialog_index_dialogcontent (+10 more)

### Community 16 - "DashboardController.php"
Cohesion: 0.09
Nodes (12): {closure#10}(), {closure#12}(), {closure#16}(), {closure#18}(), {closure#21}(), {closure#25}(), {closure#26}(), {closure#27}() (+4 more)

### Community 17 - "TestCase"
Cohesion: 0.09
Nodes (10): Illuminate\Foundation\Testing\RefreshDatabase, Illuminate\Foundation\Testing\TestCase, {closure#1}(), PasswordConfirmationTest, RegistrationTest, ExampleTest, ProfileUpdateTest, TrustedProxyTest (+2 more)

### Community 18 - "package.json"
Cohesion: 0.06
Nodes (36): overrides, vite, private, $schema, scripts, build, build:ssr, check (+28 more)

### Community 19 - "button/index.ts"
Cohesion: 0.11
Nodes (22): ref_actions_laravel_passkeys_http_controllers_passkeyconfirmationcontroller, ref_inertiajs_core, ref_routes_register, ref_routes_verification, Props, { verify, isLoading, error, isSupported }, Props, Props (+14 more)

### Community 20 - "Branches/Index.vue"
Cohesion: 0.07
Nodes (14): Branch, branchToDelete, branchToDisconnect, canSync(), editingId, form, importCsOptions, props (+6 more)

### Community 21 - "SidebarProvider.vue"
Cohesion: 0.15
Nodes (17): emits, isMobile, open, openMobile, props, setOpen(), setOpenMobile(), state (+9 more)

### Community 22 - "compilerOptions"
Cohesion: 0.09
Nodes (21): compilerOptions, allowJs, esModuleInterop, forceConsistentCasingInFileNames, isolatedModules, jsx, jsxImportSource, lib (+13 more)

### Community 23 - "AppSidebar.vue"
Cohesion: 0.17
Nodes (16): footerNavItems, mainNavItems, Props, { isCurrentUrl }, resources_js_components_ui_sidebar_index_sidebar, resources_js_components_ui_sidebar_index_sidebarcontent, resources_js_components_ui_sidebar_index_sidebarfooter, resources_js_components_ui_sidebar_index_sidebargroup (+8 more)

### Community 25 - "AppHeader.vue"
Cohesion: 0.10
Nodes (19): auth, { isCurrentUrl, whenCurrentUrl }, mainNavItems, page, Props, rightNavItems, resources_js_components_ui_navigation_menu_index_navigationmenu, resources_js_components_ui_navigation_menu_index_navigationmenuitem (+11 more)

### Community 27 - "InputOTPSlot.vue"
Cohesion: 0.11
Nodes (15): vue-input-otp, delegatedProps, emits, forwarded, props, delegatedProps, forwarded, props (+7 more)

### Community 28 - "web.php"
Cohesion: 0.22
Nodes (6): EnsureAdmin, HandleAppearance, Closure, Illuminate\Support\Facades\Route, Illuminate\Support\Facades\View, Symfony\Component\HttpFoundation\Response

### Community 29 - "leads"
Cohesion: 0.06
Nodes (35): branches, lead_activity, lead_activity_lead_created_idx, lead_milestones, leads, leads_assigned_user_idx, leads_outcome_idx, leads_updated_at_idx (+27 more)

### Community 30 - "2026-11-25T08-39-06Z__resources-js-pages-auth-login-vue.md"
Cohesion: 0.25
Nodes (7): Design Health Score, Design Specificity Verdict, Minor Observations, Overall Impression, Persona Red Flags, Priority Issues, What's Working

### Community 31 - "PasswordValidationRules"
Cohesion: 0.06
Nodes (22): CreateNewUser, ResetUserPassword, PasswordValidationRules, ProfileValidationRules, PasswordUpdateRequest, ProfileDeleteRequest, ProfileUpdateRequest, TwoFactorAuthenticationRequest (+14 more)

### Community 33 - "SheetContent.vue"
Cohesion: 0.22
Nodes (7): delegatedProps, emits, forwarded, props, SheetContentProps, delegatedProps, props

### Community 34 - "KGJ Leads"
Cohesion: 0.11
Nodes (16): Auto deploy, Deploy manual, Migrasi dan backup, Panduan Deployment KGJ Leads, Production VPS 1, Admin, Alur kerja harian, Bantuan (+8 more)

### Community 35 - "dependencies"
Cohesion: 0.12
Nodes (17): dependencies, class-variance-authority, clsx, @inertiajs/vite, @inertiajs/vue3, @laravel/passkeys, laravel-vite-plugin, @lucide/vue (+9 more)

### Community 36 - "PasskeyRegister.vue"
Cohesion: 0.17
Nodes (7): @laravel/passkeys, emit, name, { register, isLoading, error, isSupported }, showForm, delegatedProps, props

### Community 37 - "useAppearance.ts"
Cohesion: 0.19
Nodes (15): { appearance, updateAppearance }, tabs, appearance, getStoredAppearance(), handleSystemThemeChange(), initializeTheme(), mediaQuery(), prefersDark() (+7 more)

### Community 38 - "aliases"
Cohesion: 0.12
Nodes (15): aliases, components, composables, lib, ui, utils, iconLibrary, $schema (+7 more)

### Community 39 - "UserInfo.vue"
Cohesion: 0.16
Nodes (11): props, resources_js_components_ui_avatar_index_avatar, resources_js_components_ui_avatar_index_avatarfallback, resources_js_components_ui_avatar_index_avatarimage, { getInitials }, Props, showAvatar, getInitial() (+3 more)

### Community 40 - "scripts"
Cohesion: 0.14
Nodes (14): scripts, ci:check, dev, lint, lint:check, post-autoload-dump, post-create-project-cmd, post-root-package-install (+6 more)

### Community 41 - "Separator.vue"
Cohesion: 0.29
Nodes (4): resources_js_components_ui_separator_index_separator, delegatedProps, props, props

### Community 42 - "Show.vue"
Cohesion: 0.06
Nodes (27): zod, budgetOptions, leadSchema, archiveDialogOpen, assisting, changeMilestone(), clientError, collaboratorForm (+19 more)

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

### Community 51 - "PopoverContent.vue"
Cohesion: 0.19
Nodes (7): delegatedProps, forwardedProps, props, delegatedProps, emits, forwarded, props

### Community 52 - "Product"
Cohesion: 0.18
Nodes (10): Capabilities and Constraints, Evidence on Hand, Operating Context, Platform, Positioning, Product, Product Principles, Product Purpose (+2 more)

### Community 54 - "devDependencies"
Cohesion: 0.20
Nodes (10): devDependencies, concurrently, @laravel/vite-plugin-wayfinder, @tailwindcss/vite, @types/node, typescript, vite, vite-plus (+2 more)

### Community 55 - "Illuminate\Support\Facades\Schema"
Cohesion: 0.14
Nodes (7): {closure#1}(), {closure#2}(), {closure#1}(), {closure#1}(), {closure#2}(), Illuminate\Database\Migrations\Migration, Illuminate\Support\Facades\Schema

### Community 56 - "bootstrap/app.php"
Cohesion: 0.28
Nodes (7): {closure#1}(), {closure#2}(), {closure#3}(), Illuminate\Foundation\Application, Illuminate\Foundation\Configuration\Exceptions, Illuminate\Foundation\Configuration\Middleware, Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets

### Community 57 - "require"
Cohesion: 0.20
Nodes (10): require, google/auth, inertiajs/inertia-laravel, laravel/chisel, laravel/fortify, laravel/framework, laravel/tinker, laravel/wayfinder (+2 more)

### Community 59 - "Collapsible.vue"
Cohesion: 0.22
Nodes (5): emits, forwarded, props, props, props

### Community 60 - "dialog/index.ts"
Cohesion: 0.22
Nodes (5): emits, forwarded, props, props, props

### Community 61 - "PasswordInput.vue"
Cohesion: 0.18
Nodes (8): inputRef, props, showPassword, resources_js_components_ui_input_index_input, emits, modelValue, props, props

### Community 62 - "useCurrentUrl.ts"
Cohesion: 0.33
Nodes (8): currentUrlReactive, page, useCurrentUrl(), isCurrentOrParentUrl(), isCurrentUrl(), whenCurrentUrl(), UseCurrentUrlReturn, toUrl()

### Community 63 - "DashboardTest.php"
Cohesion: 0.11
Nodes (17): {closure#1}(), {closure#11}(), {closure#13}(), {closure#14}(), {closure#15}(), {closure#17}(), {closure#18}(), {closure#19}() (+9 more)

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

### Community 70 - "@inertiajs/vue3"
Cohesion: 0.11
Nodes (13): @inertiajs/vue3, pages, Props, page, { title = '', description = '' }, initializeFlashToast(), priorityRows, props (+5 more)

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

### Community 78 - "console.php"
Cohesion: 0.40
Nodes (3): Illuminate\Foundation\Inspiring, Illuminate\Support\Facades\Artisan, Illuminate\Support\Facades\Schedule

### Community 79 - "LeadOutcomeFactors"
Cohesion: 0.16
Nodes (5): {closure#1}(), UpdateLeadRequest, LeadBudgetRanges, LeadOutcomeFactors, Illuminate\Validation\Validator

### Community 80 - "TwoFactorRecoveryCodes.vue"
Cohesion: 0.11
Nodes (19): ref_routes_two_factor, { hasSetupData, clearTwoFactorAuthData }, Props, showSetupModal, isRecoveryCodesVisible, recoveryCodeSectionRef, { recoveryCodesList, fetchRecoveryCodes, errors }, resources_js_components_ui_card_index_card (+11 more)

### Community 81 - "types/index.ts"
Cohesion: 0.09
Nodes (23): className, Props, Props, Props, resources_js_components_ui_breadcrumb_index_breadcrumb, resources_js_components_ui_breadcrumb_index_breadcrumbitem, resources_js_components_ui_breadcrumb_index_breadcrumblink, resources_js_components_ui_breadcrumb_index_breadcrumblist (+15 more)

### Community 84 - "Layout.vue"
Cohesion: 0.11
Nodes (16): ref_actions_laravel_passkeys_http_controllers_passkeyregistrationcontroller, resources_js_actions_app_http_controllers_settings_profilecontroller, resources_js_actions_app_http_controllers_settings_securitycontroller, Props, Props, { isCurrentOrParentUrl }, sidebarNavItems, page (+8 more)

### Community 115 - "Illuminate\Database\Query\Builder"
Cohesion: 0.11
Nodes (17): {closure#2}(), {closure#3}(), MarkOverdueLeadsForCrm, {closure#2}(), {closure#3}(), {closure#31}(), {closure#32}(), {closure#33}() (+9 more)

### Community 116 - "Project agent rules"
Cohesion: 0.25
Nodes (7): Data and database safety, Docker runtime verification, Project agent rules, Project context, Required working order, Scope and compatibility, Validation and tools

### Community 118 - "optionalDependencies"
Cohesion: 0.25
Nodes (8): optionalDependencies, @laravel/multiplex, lightningcss-linux-x64-gnu, lightningcss-win32-x64-msvc, @rollup/rollup-linux-x64-gnu, @rollup/rollup-win32-x64-msvc, @tailwindcss/oxide-linux-x64-gnu, @tailwindcss/oxide-win32-x64-msvc

### Community 119 - "SecurityTest.php"
Cohesion: 0.22
Nodes (3): {closure#1}(), {closure#2}(), SecurityTest

### Community 121 - "clearSelection"
Cohesion: 0.33
Nodes (6): archiveSelected(), clearSelection(), destroySelected(), restoreSelected(), restoreSelectedNow(), toggleMobileSelectionMode()

### Community 122 - "GoogleSheetSyncService.php"
Cohesion: 0.14
Nodes (7): {closure#2}(), {closure#8}(), Google\Auth\Credentials\ServiceAccountCredentials, Illuminate\Http\Client\RequestException, Illuminate\Support\Facades\Cache, Illuminate\Support\Facades\Http, Throwable

### Community 123 - "Accounts/Index.vue"
Cohesion: 0.14
Nodes (10): Account, accountToLink, accountToReconcile, accountToToggle, cancel(), editingId, form, needsBranch (+2 more)

### Community 124 - "LeadImportService.php"
Cohesion: 0.15
Nodes (5): {closure#1}(), {closure#4}(), {closure#5}(), LeadSourceDate, DateTimeImmutable

### Community 126 - "queueSave"
Cohesion: 0.22
Nodes (10): changedPayload(), flushPendingSave(), isDirty, persistSaveState(), queueSave(), retrySave(), save(), saveAfterTyping() (+2 more)

### Community 127 - "Illuminate\Console\Command"
Cohesion: 0.28
Nodes (5): BootstrapAdmin, SyncGoogleLeads, Illuminate\Console\Command, Illuminate\Support\Facades\Hash, Illuminate\Support\Facades\Validator

### Community 129 - "LeadSaveFeedback.vue"
Cohesion: 0.40
Nodes (4): emit, label, props, SaveState

### Community 132 - "Laravel\Fortify\Features"
Cohesion: 0.25
Nodes (3): Laravel\Fortify\Features, {closure#1}(), TwoFactorChallengeTest

### Community 137 - "NavUser.vue"
Cohesion: 0.25
Nodes (7): { isMobile, state }, page, user, resources_js_components_ui_dropdown_menu_index_dropdownmenu, resources_js_components_ui_dropdown_menu_index_dropdownmenucontent, resources_js_components_ui_dropdown_menu_index_dropdownmenutrigger, resources_js_components_ui_sidebar_index_usesidebar

### Community 138 - "DialogContent.vue"
Cohesion: 0.25
Nodes (6): delegatedProps, emits, forwarded, props, delegatedProps, props

### Community 139 - "Popover.vue"
Cohesion: 0.25
Nodes (5): emits, forwarded, props, forwardedProps, props

### Community 140 - "AlertError.vue"
Cohesion: 0.33
Nodes (5): Props, uniqueErrors, resources_js_components_ui_alert_index_alert, resources_js_components_ui_alert_index_alertdescription, resources_js_components_ui_alert_index_alerttitle

### Community 145 - "saveImmediately"
Cohesion: 0.33
Nodes (6): changeMilestone(), changeOutcome(), reopenClosing(), saveImmediately(), saveOutcome(), setClosingOutcome()

### Community 146 - "UserMenuContent.vue"
Cohesion: 0.25
Nodes (6): resources_js_components_ui_dropdown_menu_index_dropdownmenugroup, resources_js_components_ui_dropdown_menu_index_dropdownmenuitem, resources_js_components_ui_dropdown_menu_index_dropdownmenulabel, resources_js_components_ui_dropdown_menu_index_dropdownmenuseparator, Props, resources_js_types_index_user

### Community 147 - "Illuminate\Http\Request"
Cohesion: 0.17
Nodes (13): LoginResponse, {closure#1}(), {closure#10}(), {closure#11}(), {closure#2}(), {closure#3}(), {closure#4}(), {closure#5}() (+5 more)

### Community 148 - "Illuminate\Support\Facades\DB"
Cohesion: 0.33
Nodes (3): HandleInertiaRequests, Illuminate\Support\Facades\DB, Inertia\Middleware

### Community 149 - ".historyPreview"
Cohesion: 0.40
Nodes (3): LogoutResponse, Illuminate\Http\JsonResponse, Laravel\Fortify\Contracts\LogoutResponse

### Community 150 - "TooltipContent.vue"
Cohesion: 0.40
Nodes (4): delegatedProps, emits, forwarded, props

### Community 151 - "Login.vue"
Cohesion: 0.40
Nodes (3): emailInput, form, passwordInput

## Knowledge Gaps
- **684 isolated node(s):** `$schema`, `style`, `config`, `css`, `baseColor` (+679 more)
  These have ≤1 connection - possible missing edges or undocumented components. (Counts symbols only; 1005 node(s) total have ≤1 connection when file, concept and rationale nodes are included.)
- **55 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `vue` connect `vue` to `LeadSaveFeedback.vue`, `reka-ui`, `Sidebar.vue`, `@vueuse/core`, `@lucide/vue`, `SelectContent.vue`, `Leads/Index.vue`, `NavUser.vue`, `DialogContent.vue`, `Statistics.vue`, `AlertError.vue`, `TwoFactorSetupModal.vue`, `DeleteUser.vue`, `package.json`, `button/index.ts`, `Branches/Index.vue`, `SidebarProvider.vue`, `TooltipContent.vue`, `Login.vue`, `AppHeader.vue`, `InputOTPSlot.vue`, `SheetContent.vue`, `PasskeyRegister.vue`, `useAppearance.ts`, `UserInfo.vue`, `Separator.vue`, `Show.vue`, `global.d.ts`, `LeadQuickUpdate.vue`, `LeadHistoryPopover.vue`, `PopoverContent.vue`, `PasswordInput.vue`, `useCurrentUrl.ts`, `Badge.vue`, `SidebarMenuSkeleton.vue`, `@inertiajs/vue3`, `TwoFactorRecoveryCodes.vue`, `types/index.ts`, `Layout.vue`, `Accounts/Index.vue`?**
  _High betweenness centrality (0.134) - this node is a cross-community bridge._
- **Why does `reka-ui` connect `reka-ui` to `Sidebar.vue`, `@vueuse/core`, `@lucide/vue`, `SelectContent.vue`, `vue`, `DialogContent.vue`, `Popover.vue`, `package.json`, `button/index.ts`, `SidebarProvider.vue`, `TooltipContent.vue`, `InputOTPSlot.vue`, `SheetContent.vue`, `PasskeyRegister.vue`, `UserInfo.vue`, `Separator.vue`, `PopoverContent.vue`, `Collapsible.vue`, `dialog/index.ts`, `tooltip/index.ts`, `Badge.vue`?**
  _High betweenness centrality (0.043) - this node is a cross-community bridge._
- **Why does `User` connect `User` to `EmailVerificationTest`, `Laravel\Fortify\Features`, `PasswordResetTest.php`, `DatabaseSeeder.php`, `UserFactory.php`, `LeadManagementTest`, `LeadManagementTest.php`, `VerificationNotificationTest.php`, `TestCase`, `Illuminate\Http\Request`, `SecurityTest.php`, `AuthenticationTest`, `DashboardTest.php`, `PasswordValidationRules`?**
  _High betweenness centrality (0.035) - this node is a cross-community bridge._
- **What connects `$schema`, `style`, `config` to the rest of the system?**
  _684 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `Illuminate\Database\Schema\Blueprint` be split into smaller, more focused modules?**
  _Cohesion score 0.10507246376811594 - nodes in this community are weakly interconnected._
- **Should `reka-ui` be split into smaller, more focused modules?**
  _Cohesion score 0.05102040816326531 - nodes in this community are weakly interconnected._
- **Should `LeadController.php` be split into smaller, more focused modules?**
  _Cohesion score 0.06747638326585695 - nodes in this community are weakly interconnected._