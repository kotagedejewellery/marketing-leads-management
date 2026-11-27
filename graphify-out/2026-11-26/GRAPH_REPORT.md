# Graph Report - marketing-leads  (2026-11-26)

## Corpus Check
- 330 files · ~162,079 words
- Verdict: corpus is large enough that graph structure adds value.
- Unclassified: 25 file(s) not represented in the graph (top: (none) 19, .example 2, .neon 1)

## Summary
- 2088 nodes · 3792 edges · 152 communities (95 shown, 57 thin omitted)
- Extraction: 99% EXTRACTED · 1% INFERRED · 0% AMBIGUOUS · INFERRED: 24 edges (avg confidence: 0.85)
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `b4a3e8a4`
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
- LeadImportService
- TwoFactorSetupModal.vue
- DashboardController
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
- PasswordValidationRules
- GoogleSheetSyncService
- DailyLeadTabParser
- KGJ Leads
- dependencies
- SidebarProvider.vue
- useAppearance.ts
- aliases
- AppHeader.vue
- scripts
- Statistics.vue
- Show.vue
- global.d.ts
- UserFactory.php
- LeadQuickUpdate.vue
- VerificationNotificationTest.php
- LeadHistoryPopover.vue
- composer.json
- require-dev
- SheetContent.vue
- Product
- devDependencies
- Illuminate\Support\Facades\Schema
- UserInfo.vue
- require
- AuthenticationTest
- Collapsible.vue
- dialog/index.ts
- Illuminate\Console\Command
- useCurrentUrl.ts
- DashboardTest.php
- tooltip/index.ts
- CsName
- web.php
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
- Breadcrumbs.vue
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
- {closure#13}
- Project agent rules
- optionalDependencies
- SecurityTest.php
- LeadOutcomeFactors
- clearSelection
- NavUser.vue
- Illuminate\Support\Facades\DB
- @inertiajs/vue3
- queueSave
- UserMenuContent.vue
- ownerLabel
- LeadSaveFeedback.vue
- outcomeLabel
- Popover.vue
- Laravel\Fortify\Features
- resolveLeadConflict
- AlertError.vue
- PopoverContent.vue
- scripts
- createDesktopRowResizeObserver
- hasUnsavedChanges
- LeadEditState
- observeDesktopViewport
- saveImmediately
- vite.config.ts
- Illuminate\Http\Request
- Login.vue
- SidebarMenuSkeleton.vue
- overrides
- AvatarFallback.vue

## God Nodes (most connected - your core abstractions)
1. `vue` - 130 edges
2. `cn()` - 94 edges
3. `LeadManagementTest` - 92 edges
4. `User` - 84 edges
5. `reka-ui` - 80 edges
6. `@inertiajs/vue3` - 46 edges
7. `@vueuse/core` - 46 edges
8. `LeadController` - 45 edges
9. `DashboardController` - 43 edges
10. `@lucide/vue` - 40 edges

## Surprising Connections (you probably didn't know these)
- `DashboardController` --inherits--> `Controller`  [EXTRACTED]
  app/Http/Controllers/DashboardController.php → app/Http/Controllers/Controller.php
- `LeadController` --inherits--> `Controller`  [EXTRACTED]
  app/Http/Controllers/LeadController.php → app/Http/Controllers/Controller.php
- `{closure#10}()` --calls--> `CsName`  [EXTRACTED]
  app/Http/Controllers/DashboardController.php → app/Support/CsName.php
- `{closure#19}()` --calls--> `CsName`  [EXTRACTED]
  app/Http/Controllers/DashboardController.php → app/Support/CsName.php
- `{closure#22}()` --calls--> `CsName`  [EXTRACTED]
  app/Http/Controllers/DashboardController.php → app/Support/CsName.php

## Import Cycles
- None detected.

## Communities (152 total, 57 thin omitted)

### Community 0 - "Illuminate\Database\Schema\Blueprint"
Cohesion: 0.10
Nodes (19): {closure#1}(), {closure#2}(), {closure#3}(), {closure#1}(), {closure#2}(), {closure#3}(), Illuminate\Database\Schema\Blueprint, {closure#1}() (+11 more)

### Community 1 - "reka-ui"
Cohesion: 0.06
Nodes (32): reka-ui, emits, forwarded, props, delegatedProps, emits, forwarded, props (+24 more)

### Community 2 - "LeadController.php"
Cohesion: 0.07
Nodes (16): {closure#12}(), {closure#16}(), {closure#17}(), {closure#19}(), {closure#20}(), {closure#21}(), {closure#22}(), {closure#24}() (+8 more)

### Community 3 - "Sidebar.vue"
Cohesion: 0.09
Nodes (17): resources_js_components_ui_sheet_index_sheet, resources_js_components_ui_sheet_index_sheetcontent, emits, forwarded, props, props, delegatedProps, props (+9 more)

### Community 4 - "@vueuse/core"
Cohesion: 0.06
Nodes (30): @vueuse/core, delegatedProps, props, navigationMenuTriggerStyle, delegatedProps, emits, forwarded, props (+22 more)

### Community 5 - "Inertia\Response"
Cohesion: 0.11
Nodes (8): Controller, ProfileController, SecurityController, Illuminate\Auth\Middleware\RequirePassword, Illuminate\Contracts\Auth\MustVerifyEmail, Illuminate\Support\Facades\Auth, Inertia\Inertia, Inertia\Response

### Community 6 - "@lucide/vue"
Cohesion: 0.07
Nodes (22): @lucide/vue, vue-sonner, delegatedProps, emits, forwarded, props, delegatedProps, emits (+14 more)

### Community 7 - "SelectContent.vue"
Cohesion: 0.06
Nodes (21): resources_js_components_ui_select_index_selectscrolldownbutton, resources_js_components_ui_select_index_selectscrollupbutton, emits, forwarded, props, delegatedProps, emits, forwarded (+13 more)

### Community 8 - "Leads/Index.vue"
Cohesion: 0.02
Nodes (74): resources_js_components_ui_sheet_index_sheetdescription, allVisibleSelected, archiveDialogOpen, branch, budget, bulkCsAccounts, bulkForm, bulkSelectionMessage (+66 more)

### Community 9 - "User"
Cohesion: 0.09
Nodes (9): User, Illuminate\Database\Eloquent\Factories\HasFactory, Illuminate\Foundation\Auth\User, Illuminate\Notifications\Notifiable, Illuminate\Support\Carbon, Laravel\Fortify\Contracts\PasskeyUser, Laravel\Fortify\PasskeyAuthenticatable, Laravel\Fortify\TwoFactorAuthenticatable (+1 more)

### Community 10 - "vue"
Cohesion: 0.05
Nodes (46): vue, props, props, props, props, props, props, props (+38 more)

### Community 12 - "TwoFactorSetupModal.vue"
Cohesion: 0.09
Nodes (20): ref_routes_two_factor_login, code, { copy, copied }, isOpen, modalConfig, pinInputContainerRef, Props, { qrCodeSvg, manualSetupKey, clearSetupData, fetchSetupData, errors } (+12 more)

### Community 14 - "LeadManagementTest.php"
Cohesion: 0.11
Nodes (31): Illuminate\Http\Client\Request, Illuminate\Http\UploadedFile, Inertia\Testing\AssertableInertia, Mockery, OpenSpout\Common\Entity\Cell, OpenSpout\Common\Entity\Row, OpenSpout\Writer\XLSX\Writer, {closure#11}() (+23 more)

### Community 15 - "DeleteUser.vue"
Cohesion: 0.15
Nodes (18): passwordInput, emit, handleDelete(), isDeleting, props, resources_js_components_ui_dialog_index_dialog, resources_js_components_ui_dialog_index_dialogclose, resources_js_components_ui_dialog_index_dialogcontent (+10 more)

### Community 16 - "DashboardController.php"
Cohesion: 0.08
Nodes (14): {closure#3}(), {closure#2}(), {closure#3}(), {closure#32}(), {closure#33}(), {closure#4}(), {closure#5}(), {closure#6}() (+6 more)

### Community 17 - "TestCase"
Cohesion: 0.09
Nodes (10): Illuminate\Foundation\Testing\RefreshDatabase, Illuminate\Foundation\Testing\TestCase, {closure#1}(), PasswordConfirmationTest, RegistrationTest, ExampleTest, ProfileUpdateTest, TrustedProxyTest (+2 more)

### Community 18 - "package.json"
Cohesion: 0.09
Nodes (21): private, $schema, type, clsx, concurrently, @inertiajs/vite, @laravel/multiplex, @laravel/vite-plugin-wayfinder (+13 more)

### Community 19 - "button/index.ts"
Cohesion: 0.07
Nodes (31): ref_actions_laravel_passkeys_http_controllers_passkeyconfirmationcontroller, ref_inertiajs_core, @laravel/passkeys, ref_routes_register, ref_routes_verification, emit, name, { register, isLoading, error, isSupported } (+23 more)

### Community 20 - "Branches/Index.vue"
Cohesion: 0.06
Nodes (17): Branch, branchToDelete, branchToDisconnect, canSync(), editingId, form, importCsOptions, props (+9 more)

### Community 21 - "Layout.vue"
Cohesion: 0.14
Nodes (12): ref_actions_laravel_passkeys_http_controllers_passkeyregistrationcontroller, resources_js_actions_app_http_controllers_settings_securitycontroller, Props, Props, resources_js_components_ui_separator_index_separator, { isCurrentOrParentUrl }, sidebarNavItems, Props (+4 more)

### Community 22 - "compilerOptions"
Cohesion: 0.09
Nodes (21): compilerOptions, allowJs, esModuleInterop, forceConsistentCasingInFileNames, isolatedModules, jsx, jsxImportSource, lib (+13 more)

### Community 23 - "AppSidebar.vue"
Cohesion: 0.17
Nodes (16): footerNavItems, mainNavItems, Props, { isCurrentUrl }, resources_js_components_ui_sidebar_index_sidebar, resources_js_components_ui_sidebar_index_sidebarcontent, resources_js_components_ui_sidebar_index_sidebarfooter, resources_js_components_ui_sidebar_index_sidebargroup (+8 more)

### Community 27 - "InputOTPSlot.vue"
Cohesion: 0.11
Nodes (15): vue-input-otp, delegatedProps, emits, forwarded, props, delegatedProps, forwarded, props (+7 more)

### Community 28 - "bootstrap/app.php"
Cohesion: 0.28
Nodes (7): {closure#1}(), {closure#2}(), {closure#3}(), Illuminate\Foundation\Application, Illuminate\Foundation\Configuration\Exceptions, Illuminate\Foundation\Configuration\Middleware, Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets

### Community 29 - "leads"
Cohesion: 0.06
Nodes (35): branches, lead_activity, lead_activity_lead_created_idx, lead_milestones, leads, leads_assigned_user_idx, leads_outcome_idx, leads_updated_at_idx (+27 more)

### Community 30 - "2026-11-25T08-39-06Z__resources-js-pages-auth-login-vue.md"
Cohesion: 0.25
Nodes (7): Design Health Score, Design Specificity Verdict, Minor Observations, Overall Impression, Persona Red Flags, Priority Issues, What's Working

### Community 31 - "PasswordValidationRules"
Cohesion: 0.06
Nodes (23): CreateNewUser, ResetUserPassword, PasswordValidationRules, ProfileValidationRules, PasswordUpdateRequest, ProfileDeleteRequest, ProfileUpdateRequest, TwoFactorAuthenticationRequest (+15 more)

### Community 32 - "GoogleSheetSyncService"
Cohesion: 0.22
Nodes (3): GoogleSheetSyncService, Illuminate\Http\Client\PendingRequest, Throwable

### Community 33 - "DailyLeadTabParser"
Cohesion: 0.09
Nodes (9): {closure#2}(), {closure#8}(), DailyLeadTabParser, Google\Auth\Credentials\ServiceAccountCredentials, Illuminate\Http\Client\RequestException, Illuminate\Support\Facades\Cache, Illuminate\Support\Facades\Http, RuntimeException (+1 more)

### Community 34 - "KGJ Leads"
Cohesion: 0.11
Nodes (16): Auto deploy, Deploy manual, Migrasi dan backup, Panduan Deployment KGJ Leads, Production VPS 1, Admin, Alur kerja harian, Bantuan (+8 more)

### Community 35 - "dependencies"
Cohesion: 0.12
Nodes (17): dependencies, class-variance-authority, clsx, @inertiajs/vite, @inertiajs/vue3, @laravel/passkeys, laravel-vite-plugin, @lucide/vue (+9 more)

### Community 36 - "SidebarProvider.vue"
Cohesion: 0.16
Nodes (16): emits, isMobile, open, openMobile, props, setOpen(), setOpenMobile(), state (+8 more)

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

### Community 41 - "Statistics.vue"
Cohesion: 0.13
Nodes (10): branch, outcomeDateFrom, outcomeDateTo, outcomePeriodText, outcomeReports, props, selectedBranchName, stages (+2 more)

### Community 42 - "Show.vue"
Cohesion: 0.07
Nodes (26): zod, budgetOptions, leadSchema, archiveDialogOpen, changeMilestone(), clientError, collaboratorForm, deleteDialogOpen (+18 more)

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

### Community 51 - "SheetContent.vue"
Cohesion: 0.22
Nodes (7): delegatedProps, emits, forwarded, props, SheetContentProps, delegatedProps, props

### Community 52 - "Product"
Cohesion: 0.18
Nodes (10): Capabilities and Constraints, Evidence on Hand, Operating Context, Platform, Positioning, Product, Product Principles, Product Purpose (+2 more)

### Community 54 - "devDependencies"
Cohesion: 0.20
Nodes (10): devDependencies, concurrently, @laravel/vite-plugin-wayfinder, @tailwindcss/vite, @types/node, typescript, vite, vite-plus (+2 more)

### Community 55 - "Illuminate\Support\Facades\Schema"
Cohesion: 0.14
Nodes (7): {closure#1}(), {closure#2}(), {closure#1}(), {closure#1}(), {closure#2}(), Illuminate\Database\Migrations\Migration, Illuminate\Support\Facades\Schema

### Community 56 - "UserInfo.vue"
Cohesion: 0.19
Nodes (11): resources_js_components_ui_avatar_index_avatar, resources_js_components_ui_avatar_index_avatarfallback, resources_js_components_ui_avatar_index_avatarimage, { getInitials }, Props, showAvatar, getInitial(), getInitials() (+3 more)

### Community 57 - "require"
Cohesion: 0.20
Nodes (10): require, google/auth, inertiajs/inertia-laravel, laravel/chisel, laravel/fortify, laravel/framework, laravel/tinker, laravel/wayfinder (+2 more)

### Community 59 - "Collapsible.vue"
Cohesion: 0.22
Nodes (5): emits, forwarded, props, props, props

### Community 60 - "dialog/index.ts"
Cohesion: 0.06
Nodes (23): emits, forwarded, props, props, delegatedProps, emits, forwarded, props (+15 more)

### Community 61 - "Illuminate\Console\Command"
Cohesion: 0.24
Nodes (5): BootstrapAdmin, {closure#2}(), MarkOverdueLeadsForCrm, SyncGoogleLeads, Illuminate\Console\Command

### Community 62 - "useCurrentUrl.ts"
Cohesion: 0.33
Nodes (8): currentUrlReactive, page, useCurrentUrl(), isCurrentOrParentUrl(), isCurrentUrl(), whenCurrentUrl(), UseCurrentUrlReturn, toUrl()

### Community 63 - "DashboardTest.php"
Cohesion: 0.11
Nodes (17): {closure#10}(), {closure#11}(), {closure#13}(), {closure#14}(), {closure#15}(), {closure#16}(), {closure#17}(), {closure#18}() (+9 more)

### Community 64 - "tooltip/index.ts"
Cohesion: 0.22
Nodes (5): emits, forwarded, props, props, props

### Community 65 - "CsName"
Cohesion: 0.12
Nodes (12): {closure#10}(), {closure#19}(), {closure#22}(), {closure#26}(), {closure#27}(), {closure#28}(), {closure#29}(), {closure#30}() (+4 more)

### Community 66 - "web.php"
Cohesion: 0.22
Nodes (6): EnsureAdmin, HandleAppearance, Closure, Illuminate\Support\Facades\Route, Illuminate\Support\Facades\View, Symfony\Component\HttpFoundation\Response

### Community 67 - "config"
Cohesion: 0.29
Nodes (7): pestphp/pest-plugin, php-http/discovery, config, allow-plugins, optimize-autoloader, preferred-install, sort-packages

### Community 69 - "guardUnsavedChanges"
Cohesion: 0.12
Nodes (18): addCollaboratorSelected(), addCollaboratorSelectedNow(), applyFilters(), applyFiltersNow(), assignSelected(), assignSelectedNow(), clearFilters(), createLead() (+10 more)

### Community 70 - "Badge.vue"
Cohesion: 0.20
Nodes (7): class-variance-authority, props, props, AlertVariants, delegatedProps, props, BadgeVariants

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
Cohesion: 0.14
Nodes (10): Account, accountToLink, accountToReconcile, accountToToggle, cancel(), editingId, form, needsBranch (+2 more)

### Community 78 - "console.php"
Cohesion: 0.40
Nodes (3): Illuminate\Foundation\Inspiring, Illuminate\Support\Facades\Artisan, Illuminate\Support\Facades\Schedule

### Community 79 - "LeadSourceDate"
Cohesion: 0.25
Nodes (3): {closure#1}(), LeadSourceDate, DateTimeImmutable

### Community 80 - "TwoFactorRecoveryCodes.vue"
Cohesion: 0.11
Nodes (20): ref_routes_two_factor, { hasSetupData, clearTwoFactorAuthData }, Props, showSetupModal, isRecoveryCodesVisible, recoveryCodeSectionRef, { recoveryCodesList, fetchRecoveryCodes, errors }, resources_js_components_ui_button_index_button (+12 more)

### Community 81 - "types/index.ts"
Cohesion: 0.13
Nodes (16): className, Props, Props, resources_js_components_ui_sidebar_index_sidebarinset, resources_js_components_ui_sidebar_index_sidebarprovider, resources_js_components_ui_sidebar_index_sidebartrigger, resources_js_components_ui_sonner_index_toaster, Props (+8 more)

### Community 84 - "Breadcrumbs.vue"
Cohesion: 0.25
Nodes (7): Props, resources_js_components_ui_breadcrumb_index_breadcrumb, resources_js_components_ui_breadcrumb_index_breadcrumbitem, resources_js_components_ui_breadcrumb_index_breadcrumblink, resources_js_components_ui_breadcrumb_index_breadcrumblist, resources_js_components_ui_breadcrumb_index_breadcrumbpage, resources_js_components_ui_breadcrumb_index_breadcrumbseparator

### Community 116 - "Project agent rules"
Cohesion: 0.25
Nodes (7): Data and database safety, Docker runtime verification, Project agent rules, Project context, Required working order, Scope and compatibility, Validation and tools

### Community 118 - "optionalDependencies"
Cohesion: 0.25
Nodes (8): optionalDependencies, @laravel/multiplex, lightningcss-linux-x64-gnu, lightningcss-win32-x64-msvc, @rollup/rollup-linux-x64-gnu, @rollup/rollup-win32-x64-msvc, @tailwindcss/oxide-linux-x64-gnu, @tailwindcss/oxide-win32-x64-msvc

### Community 119 - "SecurityTest.php"
Cohesion: 0.20
Nodes (4): Illuminate\Support\Facades\Hash, {closure#1}(), {closure#2}(), SecurityTest

### Community 120 - "LeadOutcomeFactors"
Cohesion: 0.16
Nodes (5): {closure#1}(), UpdateLeadRequest, LeadBudgetRanges, LeadOutcomeFactors, Illuminate\Validation\Validator

### Community 121 - "clearSelection"
Cohesion: 0.33
Nodes (6): archiveSelected(), clearSelection(), destroySelected(), restoreSelected(), restoreSelectedNow(), toggleMobileSelectionMode()

### Community 122 - "NavUser.vue"
Cohesion: 0.25
Nodes (7): { isMobile, state }, page, user, resources_js_components_ui_dropdown_menu_index_dropdownmenu, resources_js_components_ui_dropdown_menu_index_dropdownmenucontent, resources_js_components_ui_dropdown_menu_index_dropdownmenutrigger, resources_js_components_ui_sidebar_index_usesidebar

### Community 123 - "Illuminate\Support\Facades\DB"
Cohesion: 0.33
Nodes (3): HandleInertiaRequests, Illuminate\Support\Facades\DB, Inertia\Middleware

### Community 124 - "@inertiajs/vue3"
Cohesion: 0.11
Nodes (13): @inertiajs/vue3, pages, Props, page, { title = '', description = '' }, initializeFlashToast(), priorityRows, props (+5 more)

### Community 126 - "queueSave"
Cohesion: 0.22
Nodes (10): changedPayload(), flushPendingSave(), isDirty, persistSaveState(), queueSave(), retrySave(), save(), saveAfterTyping() (+2 more)

### Community 127 - "UserMenuContent.vue"
Cohesion: 0.17
Nodes (10): resources_js_actions_app_http_controllers_settings_profilecontroller, resources_js_components_ui_dropdown_menu_index_dropdownmenugroup, resources_js_components_ui_dropdown_menu_index_dropdownmenuitem, resources_js_components_ui_dropdown_menu_index_dropdownmenulabel, resources_js_components_ui_dropdown_menu_index_dropdownmenuseparator, Props, page, user (+2 more)

### Community 129 - "LeadSaveFeedback.vue"
Cohesion: 0.40
Nodes (4): emit, label, props, SaveState

### Community 131 - "Popover.vue"
Cohesion: 0.25
Nodes (5): emits, forwarded, props, forwardedProps, props

### Community 132 - "Laravel\Fortify\Features"
Cohesion: 0.17
Nodes (6): Illuminate\Auth\Events\Verified, Illuminate\Support\Facades\Event, Illuminate\Support\Facades\URL, Laravel\Fortify\Features, {closure#1}(), TwoFactorChallengeTest

### Community 137 - "AlertError.vue"
Cohesion: 0.33
Nodes (5): Props, uniqueErrors, resources_js_components_ui_alert_index_alert, resources_js_components_ui_alert_index_alertdescription, resources_js_components_ui_alert_index_alerttitle

### Community 138 - "PopoverContent.vue"
Cohesion: 0.18
Nodes (8): delegatedProps, emits, forwarded, props, delegatedProps, emits, forwarded, props

### Community 139 - "scripts"
Cohesion: 0.29
Nodes (7): scripts, build, build:ssr, check, check:fix, dev, types:check

### Community 145 - "saveImmediately"
Cohesion: 0.33
Nodes (6): changeMilestone(), changeOutcome(), reopenClosing(), saveImmediately(), saveOutcome(), setClosingOutcome()

### Community 146 - "vite.config.ts"
Cohesion: 0.40
Nodes (4): laravel-vite-plugin, @tailwindcss/vite, vite-plus, @vitejs/plugin-vue

### Community 147 - "Illuminate\Http\Request"
Cohesion: 0.13
Nodes (16): LoginResponse, LogoutResponse, {closure#1}(), {closure#10}(), {closure#11}(), {closure#2}(), {closure#3}(), {closure#4}() (+8 more)

### Community 148 - "Login.vue"
Cohesion: 0.40
Nodes (3): emailInput, form, passwordInput

### Community 149 - "SidebarMenuSkeleton.vue"
Cohesion: 0.25
Nodes (5): props, width, resources_js_components_ui_skeleton_index_skeleton, props, SkeletonProps

### Community 154 - "AvatarFallback.vue"
Cohesion: 0.25
Nodes (4): props, delegatedProps, props, props

## Knowledge Gaps
- **686 isolated node(s):** `$schema`, `style`, `config`, `css`, `baseColor` (+681 more)
  These have ≤1 connection - possible missing edges or undocumented components. (Counts symbols only; 1015 node(s) total have ≤1 connection when file, concept and rationale nodes are included.)
- **57 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `vue` connect `vue` to `LeadSaveFeedback.vue`, `reka-ui`, `Sidebar.vue`, `@vueuse/core`, `@lucide/vue`, `SelectContent.vue`, `Leads/Index.vue`, `AlertError.vue`, `PopoverContent.vue`, `TwoFactorSetupModal.vue`, `DeleteUser.vue`, `package.json`, `button/index.ts`, `Login.vue`, `SidebarMenuSkeleton.vue`, `Branches/Index.vue`, `AvatarFallback.vue`, `InputOTPSlot.vue`, `SidebarProvider.vue`, `useAppearance.ts`, `AppHeader.vue`, `Statistics.vue`, `Show.vue`, `global.d.ts`, `LeadQuickUpdate.vue`, `LeadHistoryPopover.vue`, `SheetContent.vue`, `UserInfo.vue`, `dialog/index.ts`, `useCurrentUrl.ts`, `Badge.vue`, `Accounts/Index.vue`, `TwoFactorRecoveryCodes.vue`, `types/index.ts`, `NavUser.vue`, `@inertiajs/vue3`, `UserMenuContent.vue`?**
  _High betweenness centrality (0.126) - this node is a cross-community bridge._
- **Why does `reka-ui` connect `reka-ui` to `tooltip/index.ts`, `Popover.vue`, `@vueuse/core`, `Sidebar.vue`, `Badge.vue`, `@lucide/vue`, `InputOTPSlot.vue`, `SelectContent.vue`, `vue`, `PopoverContent.vue`, `SidebarProvider.vue`, `package.json`, `button/index.ts`, `SheetContent.vue`, `AvatarFallback.vue`, `Collapsible.vue`, `dialog/index.ts`?**
  _High betweenness centrality (0.037) - this node is a cross-community bridge._
- **Why does `@inertiajs/vue3` connect `@inertiajs/vue3` to `Leads/Index.vue`, `vue`, `TwoFactorSetupModal.vue`, `DeleteUser.vue`, `package.json`, `button/index.ts`, `Login.vue`, `Layout.vue`, `Branches/Index.vue`, `AppSidebar.vue`, `AppHeader.vue`, `Statistics.vue`, `Show.vue`, `LeadQuickUpdate.vue`, `LeadHistoryPopover.vue`, `useCurrentUrl.ts`, `Accounts/Index.vue`, `TwoFactorRecoveryCodes.vue`, `types/index.ts`, `Breadcrumbs.vue`, `NavUser.vue`, `UserMenuContent.vue`?**
  _High betweenness centrality (0.036) - this node is a cross-community bridge._
- **What connects `$schema`, `style`, `config` to the rest of the system?**
  _686 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `Illuminate\Database\Schema\Blueprint` be split into smaller, more focused modules?**
  _Cohesion score 0.1 - nodes in this community are weakly interconnected._
- **Should `reka-ui` be split into smaller, more focused modules?**
  _Cohesion score 0.05708245243128964 - nodes in this community are weakly interconnected._
- **Should `LeadController.php` be split into smaller, more focused modules?**
  _Cohesion score 0.06538461538461539 - nodes in this community are weakly interconnected._