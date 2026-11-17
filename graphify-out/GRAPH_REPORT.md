# Graph Report - marketing-leads  (2026-11-17)

## Corpus Check
- 304 files · ~60,912 words
- Verdict: corpus is large enough that graph structure adds value.
- Unclassified: 25 file(s) not represented in the graph (top: (none) 19, .example 2, .neon 1)

## Summary
- 1614 nodes · 2957 edges · 126 communities (80 shown, 46 thin omitted)
- Extraction: 99% EXTRACTED · 1% INFERRED · 0% AMBIGUOUS · INFERRED: 21 edges (avg confidence: 0.85)
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `33b7f995`
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
- types/index.ts
- TwoFactorSetupModal.vue
- Illuminate\Http\Request
- DialogContent.vue
- AppLayout.vue
- DashboardController.php
- TestCase
- package.json
- DeleteUser.vue
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
- FortifyServiceProvider
- Separator.vue
- LeadImportService
- Layout.vue
- KGJ Leads
- dependencies
- button/index.ts
- useAppearance.ts
- aliases
- UserInfo.vue
- scripts
- GoogleSheetSyncService
- Show.vue
- global.d.ts
- UserFactory.php
- LeadManagementTest
- LeadQuickUpdate.vue
- VerificationNotificationTest.php
- Accounts/Index.vue
- composer.json
- require-dev
- ProfileValidationRules
- Product
- devDependencies
- PasskeyRegister.vue
- bootstrap/app.php
- require
- AuthenticationTest
- Collapsible.vue
- dialog/index.ts
- SheetContent.vue
- useCurrentUrl.ts
- DashboardTest
- TooltipContent.vue
- Badge.vue
- SidebarMenuSkeleton.vue
- config
- PasswordValidationRules
- guardUnsavedChanges
- @inertiajs/vue3
- PasswordResetTest.php
- psr-4
- laravel
- logging.php
- DatabaseSeeder.php
- Design
- BootstrapAdmin.php
- console.php
- Breadcrumbs.vue
- TwoFactorRecoveryCodes.vue
- LoginResponse.php
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
- settings.php
- AlertError.vue
- ResetUserPassword.php
- Laravel\Fortify\Features
- TwoFactorAuthenticationRequest
- clearSelection
- assignSelected
- syncGoogleSheet
- toggleArchive

## God Nodes (most connected - your core abstractions)
1. `vue` - 127 edges
2. `cn()` - 93 edges
3. `reka-ui` - 77 edges
4. `User` - 69 edges
5. `@inertiajs/vue3` - 45 edges
6. `@vueuse/core` - 45 edges
7. `@lucide/vue` - 38 edges
8. `LeadController` - 35 edges
9. `LeadManagementTest` - 35 edges
10. `TestCase` - 32 edges

## Surprising Connections (you probably didn't know these)
- `CreateNewUser` --mixes_in--> `PasswordValidationRules`  [EXTRACTED]
  app/Actions/Fortify/CreateNewUser.php → app/Concerns/PasswordValidationRules.php
- `ResetUserPassword` --mixes_in--> `PasswordValidationRules`  [EXTRACTED]
  app/Actions/Fortify/ResetUserPassword.php → app/Concerns/PasswordValidationRules.php
- `ProfileUpdateRequest` --mixes_in--> `ProfileValidationRules`  [EXTRACTED]
  app/Http/Requests/Settings/ProfileUpdateRequest.php → app/Concerns/ProfileValidationRules.php
- `DashboardController` --inherits--> `Controller`  [EXTRACTED]
  app/Http/Controllers/DashboardController.php → app/Http/Controllers/Controller.php
- `LeadController` --inherits--> `Controller`  [EXTRACTED]
  app/Http/Controllers/LeadController.php → app/Http/Controllers/Controller.php

## Import Cycles
- None detected.

## Communities (126 total, 46 thin omitted)

### Community 0 - "Illuminate\Database\Schema\Blueprint"
Cohesion: 0.06
Nodes (40): {closure#1}(), {closure#2}(), {closure#3}(), {closure#1}(), {closure#2}(), {closure#1}(), {closure#2}(), {closure#3}() (+32 more)

### Community 1 - "reka-ui"
Cohesion: 0.05
Nodes (36): reka-ui, emits, forwarded, props, delegatedProps, emits, forwarded, props (+28 more)

### Community 2 - "LeadController.php"
Cohesion: 0.07
Nodes (18): {closure#1}(), {closure#13}(), {closure#14}(), {closure#15}(), {closure#2}(), {closure#4}(), {closure#5}(), {closure#6}() (+10 more)

### Community 3 - "Sidebar.vue"
Cohesion: 0.09
Nodes (17): resources_js_components_ui_sheet_index_sheet, resources_js_components_ui_sheet_index_sheetcontent, emits, forwarded, props, props, delegatedProps, props (+9 more)

### Community 4 - "@vueuse/core"
Cohesion: 0.05
Nodes (36): @vueuse/core, delegatedProps, props, delegatedProps, forwardedProps, props, emits, modelValue (+28 more)

### Community 5 - "ProfileController.php"
Cohesion: 0.18
Nodes (6): Controller, ProfileController, ProfileUpdateRequest, Illuminate\Contracts\Auth\MustVerifyEmail, Illuminate\Support\Facades\Auth, Inertia\Inertia

### Community 6 - "@lucide/vue"
Cohesion: 0.07
Nodes (20): @lucide/vue, delegatedProps, emits, forwarded, props, delegatedProps, emits, forwarded (+12 more)

### Community 7 - "SelectContent.vue"
Cohesion: 0.06
Nodes (21): resources_js_components_ui_select_index_selectscrolldownbutton, resources_js_components_ui_select_index_selectscrollupbutton, emits, forwarded, props, delegatedProps, emits, forwarded (+13 more)

### Community 8 - "Leads/Index.vue"
Cohesion: 0.05
Nodes (31): activeFilterText, allVisibleSelected, archiveDialogOpen, branch, bulkForm, deleteDialogOpen, dirtyLeadIds, discardChangesDialogOpen (+23 more)

### Community 9 - "User"
Cohesion: 0.11
Nodes (10): User, Illuminate\Database\Eloquent\Factories\HasFactory, Illuminate\Foundation\Auth\User, Illuminate\Notifications\Notifiable, Illuminate\Support\Carbon, Laravel\Fortify\Contracts\PasskeyUser, Laravel\Fortify\PasskeyAuthenticatable, Laravel\Fortify\TwoFactorAuthenticatable (+2 more)

### Community 10 - "vue"
Cohesion: 0.05
Nodes (48): vue, props, props, props, props, props, props, props (+40 more)

### Community 11 - "types/index.ts"
Cohesion: 0.13
Nodes (15): className, Props, Props, resources_js_components_ui_sidebar_index_sidebarinset, resources_js_components_ui_sidebar_index_sidebarprovider, resources_js_components_ui_sidebar_index_sidebartrigger, resources_js_components_ui_sonner_index_toaster, Props (+7 more)

### Community 12 - "TwoFactorSetupModal.vue"
Cohesion: 0.10
Nodes (17): ref_routes_two_factor_login, code, { copy, copied }, isOpen, modalConfig, pinInputContainerRef, Props, { qrCodeSvg, manualSetupKey, clearSetupData, fetchSetupData, errors } (+9 more)

### Community 13 - "Illuminate\Http\Request"
Cohesion: 0.13
Nodes (12): DashboardController, {closure#1}(), {closure#10}(), {closure#11}(), {closure#2}(), {closure#3}(), {closure#4}(), {closure#5}() (+4 more)

### Community 14 - "DialogContent.vue"
Cohesion: 0.25
Nodes (6): delegatedProps, emits, forwarded, props, delegatedProps, props

### Community 15 - "AppLayout.vue"
Cohesion: 0.14
Nodes (15): emit, handleDelete(), isDeleting, props, resources_js_components_ui_dialog_index_dialog, resources_js_components_ui_dialog_index_dialogcontent, resources_js_components_ui_dialog_index_dialogfooter, resources_js_components_ui_dialog_index_dialogheader (+7 more)

### Community 16 - "DashboardController.php"
Cohesion: 0.25
Nodes (3): {closure#2}(), Illuminate\Database\Query\Builder, Illuminate\Validation\ValidationException

### Community 17 - "TestCase"
Cohesion: 0.09
Nodes (11): Illuminate\Foundation\Testing\RefreshDatabase, Illuminate\Foundation\Testing\TestCase, {closure#1}(), PasswordConfirmationTest, RegistrationTest, {closure#1}(), TwoFactorChallengeTest, ExampleTest (+3 more)

### Community 18 - "package.json"
Cohesion: 0.07
Nodes (29): overrides, vite, private, $schema, type, clsx, concurrently, @inertiajs/vite (+21 more)

### Community 19 - "DeleteUser.vue"
Cohesion: 0.14
Nodes (16): ref_routes_register, resources_js_actions_app_http_controllers_settings_profilecontroller, passwordInput, inputRef, props, showPassword, resources_js_components_ui_input_index_input, resources_js_components_ui_label_index_label (+8 more)

### Community 20 - "Branches/Index.vue"
Cohesion: 0.15
Nodes (6): resources_js_components_ui_dialog_index_dialogdescription, Branch, branchToDelete, editingId, form, syncingBranchId

### Community 21 - "SidebarProvider.vue"
Cohesion: 0.16
Nodes (16): emits, isMobile, open, openMobile, props, setOpen(), setOpenMobile(), state (+8 more)

### Community 22 - "compilerOptions"
Cohesion: 0.09
Nodes (21): compilerOptions, allowJs, esModuleInterop, forceConsistentCasingInFileNames, isolatedModules, jsx, jsxImportSource, lib (+13 more)

### Community 23 - "AppSidebar.vue"
Cohesion: 0.11
Nodes (24): footerNavItems, mainNavItems, Props, { isCurrentUrl }, { isMobile, state }, page, user, resources_js_components_ui_dropdown_menu_index_dropdownmenu (+16 more)

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
Cohesion: 0.11
Nodes (20): branches, lead_activity, lead_activity_lead_created_idx, lead_milestones, leads, leads_assigned_user_idx, leads_updated_at_idx, users (+12 more)

### Community 30 - "FortifyServiceProvider"
Cohesion: 0.16
Nodes (7): AppServiceProvider, {closure#1}(), FortifyServiceProvider, Carbon\CarbonImmutable, Illuminate\Support\Facades\Date, Illuminate\Support\ServiceProvider, Illuminate\Validation\Rules\Password

### Community 31 - "Separator.vue"
Cohesion: 0.29
Nodes (4): resources_js_components_ui_separator_index_separator, delegatedProps, props, props

### Community 32 - "LeadImportService"
Cohesion: 0.21
Nodes (4): {closure#1}(), {closure#3}(), LeadImportService, Illuminate\Support\Facades\DB

### Community 33 - "Layout.vue"
Cohesion: 0.14
Nodes (12): ref_actions_laravel_passkeys_http_controllers_passkeyregistrationcontroller, resources_js_actions_app_http_controllers_settings_securitycontroller, Props, Props, { isCurrentOrParentUrl }, sidebarNavItems, Props, resources_js_routes_appearance_index (+4 more)

### Community 34 - "KGJ Leads"
Cohesion: 0.11
Nodes (16): Auto deploy, Deploy manual, Migrasi dan backup, Panduan Deployment KGJ Leads, Production VPS 1, Admin, Alur kerja harian, Bantuan (+8 more)

### Community 35 - "dependencies"
Cohesion: 0.12
Nodes (17): dependencies, class-variance-authority, clsx, @inertiajs/vite, @inertiajs/vue3, @laravel/passkeys, laravel-vite-plugin, @lucide/vue (+9 more)

### Community 36 - "button/index.ts"
Cohesion: 0.13
Nodes (15): ref_actions_laravel_passkeys_http_controllers_passkeyconfirmationcontroller, ref_inertiajs_core, @laravel/passkeys, ref_routes_verification, Props, { verify, isLoading, error, isSupported }, Props, Props (+7 more)

### Community 37 - "useAppearance.ts"
Cohesion: 0.08
Nodes (24): pages, { appearance, updateAppearance }, tabs, appearance, getStoredAppearance(), handleSystemThemeChange(), initializeTheme(), mediaQuery() (+16 more)

### Community 38 - "aliases"
Cohesion: 0.12
Nodes (15): aliases, components, composables, lib, ui, utils, iconLibrary, $schema (+7 more)

### Community 39 - "UserInfo.vue"
Cohesion: 0.15
Nodes (12): props, resources_js_components_ui_avatar_index_avatar, resources_js_components_ui_avatar_index_avatarfallback, resources_js_components_ui_avatar_index_avatarimage, { getInitials }, Props, showAvatar, getInitial() (+4 more)

### Community 40 - "scripts"
Cohesion: 0.14
Nodes (14): scripts, ci:check, dev, lint, lint:check, post-autoload-dump, post-create-project-cmd, post-root-package-install (+6 more)

### Community 41 - "GoogleSheetSyncService"
Cohesion: 0.15
Nodes (5): GoogleSheetSyncService, Google\Auth\Credentials\ServiceAccountCredentials, Illuminate\Http\Client\PendingRequest, Illuminate\Support\Facades\Http, Throwable

### Community 42 - "Show.vue"
Cohesion: 0.11
Nodes (16): zod, budgetOptions, leadSchema, archiveDialogOpen, clientError, deleteDialogOpen, focusFirstError(), form (+8 more)

### Community 43 - "global.d.ts"
Cohesion: 0.16
Nodes (12): Auth, Passkey, TwoFactorConfigContent, User, ComponentCustomProperties, GlobalDirectives, ImportMeta, ImportMetaEnv (+4 more)

### Community 44 - "UserFactory.php"
Cohesion: 0.16
Nodes (6): UserFactory, Illuminate\Database\Eloquent\Factories\Factory, Illuminate\Support\Facades\Hash, Illuminate\Support\Str, Pdo\Mysql, static

### Community 46 - "LeadQuickUpdate.vue"
Cohesion: 0.09
Nodes (25): changedPayload(), emit, groups, hasSaved, invalidField, isDirty, latestActivityTime, Lead (+17 more)

### Community 47 - "VerificationNotificationTest.php"
Cohesion: 0.29
Nodes (3): Illuminate\Auth\Notifications\VerifyEmail, Illuminate\Support\Facades\Notification, VerificationNotificationTest

### Community 48 - "Accounts/Index.vue"
Cohesion: 0.18
Nodes (9): resources_js_components_ui_dialog_index_dialogclose, Account, accountToToggle, cancel(), editingId, form, needsBranch, props (+1 more)

### Community 49 - "composer.json"
Cohesion: 0.17
Nodes (11): autoload-dev, psr-4, description, keywords, license, minimum-stability, name, prefer-stable (+3 more)

### Community 50 - "require-dev"
Cohesion: 0.17
Nodes (12): require-dev, fakerphp/faker, larastan/larastan, laravel/pail, laravel/pao, laravel/pint, laravel/sail, mockery/mockery (+4 more)

### Community 51 - "ProfileValidationRules"
Cohesion: 0.39
Nodes (3): CreateNewUser, ProfileValidationRules, Laravel\Fortify\Contracts\CreatesNewUsers

### Community 52 - "Product"
Cohesion: 0.18
Nodes (10): Capabilities and Constraints, Evidence on Hand, Operating Context, Platform, Positioning, Product, Product Principles, Product Purpose (+2 more)

### Community 54 - "devDependencies"
Cohesion: 0.07
Nodes (25): devDependencies, concurrently, @laravel/vite-plugin-wayfinder, @tailwindcss/vite, @types/node, typescript, vite, vite-plus (+17 more)

### Community 55 - "PasskeyRegister.vue"
Cohesion: 0.29
Nodes (4): emit, name, { register, isLoading, error, isSupported }, showForm

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
Cohesion: 0.22
Nodes (5): emits, forwarded, props, props, props

### Community 61 - "SheetContent.vue"
Cohesion: 0.22
Nodes (7): delegatedProps, emits, forwarded, props, SheetContentProps, delegatedProps, props

### Community 62 - "useCurrentUrl.ts"
Cohesion: 0.33
Nodes (8): currentUrlReactive, page, useCurrentUrl(), isCurrentOrParentUrl(), isCurrentUrl(), whenCurrentUrl(), UseCurrentUrlReturn, toUrl()

### Community 64 - "TooltipContent.vue"
Cohesion: 0.14
Nodes (9): emits, forwarded, props, delegatedProps, emits, forwarded, props, props (+1 more)

### Community 65 - "Badge.vue"
Cohesion: 0.16
Nodes (8): class-variance-authority, props, props, props, AlertVariants, delegatedProps, props, BadgeVariants

### Community 66 - "SidebarMenuSkeleton.vue"
Cohesion: 0.25
Nodes (5): props, width, resources_js_components_ui_skeleton_index_skeleton, props, SkeletonProps

### Community 67 - "config"
Cohesion: 0.29
Nodes (7): pestphp/pest-plugin, php-http/discovery, config, allow-plugins, optimize-autoloader, preferred-install, sort-packages

### Community 68 - "PasswordValidationRules"
Cohesion: 0.27
Nodes (5): PasswordValidationRules, PasswordUpdateRequest, ProfileDeleteRequest, Illuminate\Contracts\Validation\ValidationRule, Illuminate\Foundation\Http\FormRequest

### Community 69 - "guardUnsavedChanges"
Cohesion: 0.22
Nodes (10): applyFilters(), applyFiltersNow(), clearFilters(), createLead(), createLeadNow(), guardUnsavedChanges(), importCsv(), importCsvNow() (+2 more)

### Community 70 - "@inertiajs/vue3"
Cohesion: 0.20
Nodes (8): @inertiajs/vue3, Props, page, { title = '', description = '' }, resources_js_routes_index, resources_js_routes_index_home, resources_js_routes_index_login, resources_js_routes_index_register

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

### Community 77 - "BootstrapAdmin.php"
Cohesion: 0.32
Nodes (4): BootstrapAdmin, SyncGoogleLeads, Illuminate\Console\Command, RuntimeException

### Community 78 - "console.php"
Cohesion: 0.40
Nodes (3): Illuminate\Foundation\Inspiring, Illuminate\Support\Facades\Artisan, Illuminate\Support\Facades\Schedule

### Community 79 - "Breadcrumbs.vue"
Cohesion: 0.25
Nodes (7): Props, resources_js_components_ui_breadcrumb_index_breadcrumb, resources_js_components_ui_breadcrumb_index_breadcrumbitem, resources_js_components_ui_breadcrumb_index_breadcrumblink, resources_js_components_ui_breadcrumb_index_breadcrumblist, resources_js_components_ui_breadcrumb_index_breadcrumbpage, resources_js_components_ui_breadcrumb_index_breadcrumbseparator

### Community 80 - "TwoFactorRecoveryCodes.vue"
Cohesion: 0.11
Nodes (19): ref_routes_two_factor, { hasSetupData, clearTwoFactorAuthData }, Props, showSetupModal, isRecoveryCodesVisible, recoveryCodeSectionRef, { recoveryCodesList, fetchRecoveryCodes, errors }, resources_js_components_ui_card_index_card (+11 more)

### Community 81 - "LoginResponse.php"
Cohesion: 0.22
Nodes (6): LoginResponse, LogoutResponse, Illuminate\Http\JsonResponse, Laravel\Fortify\Contracts\LoginResponse, Laravel\Fortify\Contracts\LogoutResponse, Laravel\Fortify\Fortify

### Community 84 - "UserMenuContent.vue"
Cohesion: 0.25
Nodes (6): resources_js_components_ui_dropdown_menu_index_dropdownmenugroup, resources_js_components_ui_dropdown_menu_index_dropdownmenuitem, resources_js_components_ui_dropdown_menu_index_dropdownmenulabel, resources_js_components_ui_dropdown_menu_index_dropdownmenuseparator, Props, resources_js_routes_profile_index

### Community 116 - "AlertError.vue"
Cohesion: 0.33
Nodes (5): Props, uniqueErrors, resources_js_components_ui_alert_index_alert, resources_js_components_ui_alert_index_alertdescription, resources_js_components_ui_alert_index_alerttitle

### Community 118 - "ResetUserPassword.php"
Cohesion: 0.50
Nodes (3): ResetUserPassword, Illuminate\Support\Facades\Validator, Laravel\Fortify\Contracts\ResetsUserPasswords

### Community 119 - "Laravel\Fortify\Features"
Cohesion: 0.13
Nodes (7): Illuminate\Auth\Events\Verified, Illuminate\Support\Facades\Event, Illuminate\Support\Facades\URL, Laravel\Fortify\Features, {closure#1}(), {closure#2}(), SecurityTest

### Community 121 - "clearSelection"
Cohesion: 0.40
Nodes (5): archiveSelected(), clearSelection(), destroySelected(), restoreSelected(), restoreSelectedNow()

## Knowledge Gaps
- **573 isolated node(s):** `$schema`, `style`, `config`, `css`, `baseColor` (+568 more)
  These have ≤1 connection - possible missing edges or undocumented components. (Counts symbols only; 815 node(s) total have ≤1 connection when file, concept and rationale nodes are included.)
- **46 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `vue` connect `vue` to `reka-ui`, `Sidebar.vue`, `@vueuse/core`, `@lucide/vue`, `SelectContent.vue`, `Leads/Index.vue`, `types/index.ts`, `TwoFactorSetupModal.vue`, `DialogContent.vue`, `AppLayout.vue`, `package.json`, `DeleteUser.vue`, `Branches/Index.vue`, `SidebarProvider.vue`, `AppSidebar.vue`, `AppHeader.vue`, `InputOTPSlot.vue`, `Separator.vue`, `button/index.ts`, `useAppearance.ts`, `UserInfo.vue`, `Show.vue`, `global.d.ts`, `LeadQuickUpdate.vue`, `Accounts/Index.vue`, `PasskeyRegister.vue`, `SheetContent.vue`, `useCurrentUrl.ts`, `TooltipContent.vue`, `Badge.vue`, `SidebarMenuSkeleton.vue`, `@inertiajs/vue3`, `TwoFactorRecoveryCodes.vue`, `AlertError.vue`?**
  _High betweenness centrality (0.132) - this node is a cross-community bridge._
- **Why does `User` connect `User` to `Illuminate\Database\Schema\Blueprint`, `LeadController.php`, `PasswordResetTest.php`, `DatabaseSeeder.php`, `UserFactory.php`, `Illuminate\Http\Request`, `LeadManagementTest`, `VerificationNotificationTest.php`, `TestCase`, `ProfileValidationRules`, `ResetUserPassword.php`, `Laravel\Fortify\Features`, `AuthenticationTest`, `DashboardTest`?**
  _High betweenness centrality (0.045) - this node is a cross-community bridge._
- **Why does `@inertiajs/vue3` connect `@inertiajs/vue3` to `Leads/Index.vue`, `vue`, `types/index.ts`, `TwoFactorSetupModal.vue`, `AppLayout.vue`, `package.json`, `DeleteUser.vue`, `Branches/Index.vue`, `AppSidebar.vue`, `AppHeader.vue`, `Layout.vue`, `button/index.ts`, `useAppearance.ts`, `Show.vue`, `LeadQuickUpdate.vue`, `Accounts/Index.vue`, `useCurrentUrl.ts`, `Breadcrumbs.vue`, `TwoFactorRecoveryCodes.vue`, `UserMenuContent.vue`?**
  _High betweenness centrality (0.034) - this node is a cross-community bridge._
- **What connects `$schema`, `style`, `config` to the rest of the system?**
  _573 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `Illuminate\Database\Schema\Blueprint` be split into smaller, more focused modules?**
  _Cohesion score 0.06265664160401002 - nodes in this community are weakly interconnected._
- **Should `reka-ui` be split into smaller, more focused modules?**
  _Cohesion score 0.05102040816326531 - nodes in this community are weakly interconnected._
- **Should `LeadController.php` be split into smaller, more focused modules?**
  _Cohesion score 0.07226890756302522 - nodes in this community are weakly interconnected._