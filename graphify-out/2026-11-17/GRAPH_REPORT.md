# Graph Report - marketing-leads  (2026-11-17)

## Corpus Check
- 301 files · ~59,244 words
- Verdict: corpus is large enough that graph structure adds value.
- Unclassified: 25 file(s) not represented in the graph (top: (none) 19, .example 2, .neon 1)

## Summary
- 1593 nodes · 2920 edges · 109 communities (67 shown, 42 thin omitted)
- Extraction: 99% EXTRACTED · 1% INFERRED · 0% AMBIGUOUS · INFERRED: 21 edges (avg confidence: 0.85)
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `4a2ced80`
- Run `git rev-parse HEAD` and compare to check if the graph is stale.
- Run `graphify update .` after code changes (no API cost).

## Community Hubs (Navigation)
- Illuminate\Database\Schema\Blueprint
- reka-ui
- LeadController.php
- Sidebar.vue
- navigation-menu/index.ts
- PasswordValidationRules
- Checkbox.vue
- SelectContent.vue
- Leads/Index.vue
- User
- vue
- types/index.ts
- TwoFactorSetupModal.vue
- Illuminate\Http\Request
- @vueuse/core
- button/index.ts
- DashboardController
- TestCase
- package.json
- @lucide/vue
- guardUnsavedChanges
- SidebarProvider.vue
- compilerOptions
- AppSidebar.vue
- LeadController
- SecurityTest.php
- resources_js_routes_two_factor_index
- InputOTPSlot.vue
- settings.php
- leads
- FortifyServiceProvider
- Separator.vue
- clearSelection
- Security.vue
- KGJ Leads
- dependencies
- ProfileValidationRules
- useAppearance.ts
- aliases
- AppHeader.vue
- scripts
- LeadImportService
- Layout.vue
- global.d.ts
- assignSelected
- LeadManagementTest
- LeadQuickUpdate.vue
- Laravel\Fortify\Features
- syncGoogleSheet
- composer.json
- require-dev
- toggleArchive
- Product
- devDependencies
- PasskeyRegister.vue
- bootstrap/app.php
- require
- AuthenticationTest
- SheetContent.vue
- useCurrentUrl.ts
- SidebarMenuButton.vue
- Badge.vue
- SidebarMenuSkeleton.vue
- config
- @inertiajs/vue3
- PasswordResetTest
- psr-4
- laravel
- logging.php
- DatabaseSeeder.php
- Design
- console.php
- TwoFactorRecoveryCodes.vue
- GLOSSARY.md
- artisan
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
- AlertError.vue
- EmailVerificationTest

## God Nodes (most connected - your core abstractions)
1. `vue` - 127 edges
2. `cn()` - 93 edges
3. `reka-ui` - 77 edges
4. `User` - 68 edges
5. `@inertiajs/vue3` - 45 edges
6. `@vueuse/core` - 45 edges
7. `@lucide/vue` - 38 edges
8. `LeadController` - 35 edges
9. `TestCase` - 32 edges
10. `LeadManagementTest` - 31 edges

## Surprising Connections (you probably didn't know these)
- `CreateNewUser` --mixes_in--> `PasswordValidationRules`  [EXTRACTED]
  app/Actions/Fortify/CreateNewUser.php → app/Concerns/PasswordValidationRules.php
- `ProfileUpdateRequest` --mixes_in--> `ProfileValidationRules`  [EXTRACTED]
  app/Http/Requests/Settings/ProfileUpdateRequest.php → app/Concerns/ProfileValidationRules.php
- `DashboardController` --inherits--> `Controller`  [EXTRACTED]
  app/Http/Controllers/DashboardController.php → app/Http/Controllers/Controller.php
- `LeadController` --inherits--> `Controller`  [EXTRACTED]
  app/Http/Controllers/LeadController.php → app/Http/Controllers/Controller.php
- `LeadController` --references--> `LeadImportService`  [EXTRACTED]
  app/Http/Controllers/LeadController.php → app/Services/LeadImportService.php

## Import Cycles
- None detected.

## Communities (109 total, 42 thin omitted)

### Community 0 - "Illuminate\Database\Schema\Blueprint"
Cohesion: 0.06
Nodes (40): {closure#1}(), {closure#2}(), {closure#3}(), {closure#1}(), {closure#2}(), {closure#1}(), {closure#2}(), {closure#3}() (+32 more)

### Community 1 - "reka-ui"
Cohesion: 0.03
Nodes (50): reka-ui, emits, forwarded, props, props, props, emits, forwarded (+42 more)

### Community 2 - "LeadController.php"
Cohesion: 0.08
Nodes (16): {closure#13}(), {closure#14}(), {closure#15}(), {closure#2}(), {closure#4}(), {closure#5}(), {closure#6}(), {closure#7}() (+8 more)

### Community 3 - "Sidebar.vue"
Cohesion: 0.09
Nodes (17): resources_js_components_ui_sheet_index_sheet, resources_js_components_ui_sheet_index_sheetcontent, emits, forwarded, props, props, delegatedProps, props (+9 more)

### Community 4 - "navigation-menu/index.ts"
Cohesion: 0.06
Nodes (27): navigationMenuTriggerStyle, delegatedProps, emits, forwarded, props, delegatedProps, emits, forwarded (+19 more)

### Community 5 - "PasswordValidationRules"
Cohesion: 0.09
Nodes (15): ResetUserPassword, PasswordValidationRules, Controller, ProfileController, SecurityController, PasswordUpdateRequest, ProfileDeleteRequest, ProfileUpdateRequest (+7 more)

### Community 6 - "Checkbox.vue"
Cohesion: 0.33
Nodes (4): delegatedProps, emits, forwarded, props

### Community 7 - "SelectContent.vue"
Cohesion: 0.05
Nodes (27): resources_js_components_ui_select_index_selectscrolldownbutton, resources_js_components_ui_select_index_selectscrollupbutton, emits, forwarded, props, delegatedProps, emits, forwarded (+19 more)

### Community 8 - "Leads/Index.vue"
Cohesion: 0.05
Nodes (30): activeFilterText, allVisibleSelected, archiveDialogOpen, branch, bulkForm, deleteDialogOpen, dirtyLeadIds, discardChangesDialogOpen (+22 more)

### Community 9 - "User"
Cohesion: 0.11
Nodes (10): User, Illuminate\Database\Eloquent\Factories\HasFactory, Illuminate\Foundation\Auth\User, Illuminate\Notifications\Notifiable, Illuminate\Support\Carbon, Laravel\Fortify\Contracts\PasskeyUser, Laravel\Fortify\PasskeyAuthenticatable, Laravel\Fortify\TwoFactorAuthenticatable (+2 more)

### Community 10 - "vue"
Cohesion: 0.05
Nodes (46): vue, props, props, props, props, props, props, props (+38 more)

### Community 11 - "types/index.ts"
Cohesion: 0.09
Nodes (22): className, Props, Props, Props, resources_js_components_ui_breadcrumb_index_breadcrumb, resources_js_components_ui_breadcrumb_index_breadcrumbitem, resources_js_components_ui_breadcrumb_index_breadcrumblink, resources_js_components_ui_breadcrumb_index_breadcrumblist (+14 more)

### Community 12 - "TwoFactorSetupModal.vue"
Cohesion: 0.10
Nodes (17): ref_routes_two_factor_login, code, { copy, copied }, isOpen, modalConfig, pinInputContainerRef, Props, { qrCodeSvg, manualSetupKey, clearSetupData, fetchSetupData, errors } (+9 more)

### Community 13 - "Illuminate\Http\Request"
Cohesion: 0.13
Nodes (17): LoginResponse, LogoutResponse, {closure#1}(), {closure#10}(), {closure#11}(), {closure#2}(), {closure#3}(), {closure#4}() (+9 more)

### Community 14 - "@vueuse/core"
Cohesion: 0.06
Nodes (28): @vueuse/core, delegatedProps, props, delegatedProps, emits, forwarded, props, delegatedProps (+20 more)

### Community 15 - "button/index.ts"
Cohesion: 0.08
Nodes (31): passwordInput, emit, handleDelete(), isDeleting, props, props, resources_js_components_ui_dialog_index_dialog, resources_js_components_ui_dialog_index_dialogclose (+23 more)

### Community 16 - "DashboardController"
Cohesion: 0.14
Nodes (5): {closure#2}(), DashboardController, Illuminate\Database\Query\Builder, Illuminate\Validation\ValidationException, Inertia\Response

### Community 17 - "TestCase"
Cohesion: 0.10
Nodes (10): Illuminate\Foundation\Testing\RefreshDatabase, Illuminate\Foundation\Testing\TestCase, {closure#1}(), PasswordConfirmationTest, RegistrationTest, TwoFactorChallengeTest, ExampleTest, TrustedProxyTest (+2 more)

### Community 18 - "package.json"
Cohesion: 0.08
Nodes (27): overrides, vite, private, $schema, type, clsx, concurrently, @inertiajs/vite (+19 more)

### Community 19 - "@lucide/vue"
Cohesion: 0.10
Nodes (27): ref_actions_laravel_passkeys_http_controllers_passkeyconfirmationcontroller, ref_inertiajs_core, @lucide/vue, ref_routes_register, ref_routes_verification, resources_js_actions_app_http_controllers_settings_profilecontroller, Props, { verify, isLoading, error, isSupported } (+19 more)

### Community 20 - "guardUnsavedChanges"
Cohesion: 0.22
Nodes (10): applyFilters(), applyFiltersNow(), clearFilters(), createLead(), createLeadNow(), guardUnsavedChanges(), importCsv(), importCsvNow() (+2 more)

### Community 21 - "SidebarProvider.vue"
Cohesion: 0.16
Nodes (16): emits, isMobile, open, openMobile, props, setOpen(), setOpenMobile(), state (+8 more)

### Community 22 - "compilerOptions"
Cohesion: 0.09
Nodes (21): compilerOptions, allowJs, esModuleInterop, forceConsistentCasingInFileNames, isolatedModules, jsx, jsxImportSource, lib (+13 more)

### Community 23 - "AppSidebar.vue"
Cohesion: 0.11
Nodes (24): footerNavItems, mainNavItems, Props, { isCurrentUrl }, { isMobile, state }, page, user, resources_js_components_ui_dropdown_menu_index_dropdownmenu (+16 more)

### Community 25 - "SecurityTest.php"
Cohesion: 0.22
Nodes (3): {closure#1}(), {closure#2}(), SecurityTest

### Community 27 - "InputOTPSlot.vue"
Cohesion: 0.11
Nodes (15): vue-input-otp, delegatedProps, emits, forwarded, props, delegatedProps, forwarded, props (+7 more)

### Community 28 - "settings.php"
Cohesion: 0.14
Nodes (7): EnsureAdmin, HandleAppearance, Closure, Illuminate\Auth\Middleware\RequirePassword, Illuminate\Support\Facades\Route, Illuminate\Support\Facades\View, Symfony\Component\HttpFoundation\Response

### Community 29 - "leads"
Cohesion: 0.15
Nodes (17): branches, lead_activity, lead_activity_lead_created_idx, lead_milestones, leads, leads_assigned_user_idx, leads_updated_at_idx, users (+9 more)

### Community 30 - "FortifyServiceProvider"
Cohesion: 0.16
Nodes (7): AppServiceProvider, {closure#1}(), FortifyServiceProvider, Carbon\CarbonImmutable, Illuminate\Support\Facades\Date, Illuminate\Support\ServiceProvider, Illuminate\Validation\Rules\Password

### Community 31 - "Separator.vue"
Cohesion: 0.29
Nodes (4): resources_js_components_ui_separator_index_separator, delegatedProps, props, props

### Community 32 - "clearSelection"
Cohesion: 0.40
Nodes (5): archiveSelected(), clearSelection(), destroySelected(), restoreSelected(), restoreSelectedNow()

### Community 33 - "Security.vue"
Cohesion: 0.16
Nodes (9): ref_actions_laravel_passkeys_http_controllers_passkeyregistrationcontroller, resources_js_actions_app_http_controllers_settings_securitycontroller, Props, Props, Props, Props, Passkey, TwoFactorConfigContent (+1 more)

### Community 34 - "KGJ Leads"
Cohesion: 0.12
Nodes (15): Auto deploy, Deploy manual, Migrasi dan backup, Panduan Deployment KGJ Leads, Production VPS 1, Admin, Alur kerja harian, Bantuan (+7 more)

### Community 35 - "dependencies"
Cohesion: 0.12
Nodes (17): dependencies, class-variance-authority, clsx, @inertiajs/vite, @inertiajs/vue3, @laravel/passkeys, laravel-vite-plugin, @lucide/vue (+9 more)

### Community 36 - "ProfileValidationRules"
Cohesion: 0.29
Nodes (4): CreateNewUser, ProfileValidationRules, Illuminate\Validation\Rules\Unique, Laravel\Fortify\Contracts\CreatesNewUsers

### Community 37 - "useAppearance.ts"
Cohesion: 0.07
Nodes (26): vue-sonner, pages, { appearance, updateAppearance }, tabs, props, appearance, getStoredAppearance(), handleSystemThemeChange() (+18 more)

### Community 38 - "aliases"
Cohesion: 0.12
Nodes (15): aliases, components, composables, lib, ui, utils, iconLibrary, $schema (+7 more)

### Community 39 - "AppHeader.vue"
Cohesion: 0.09
Nodes (24): auth, { isCurrentUrl, whenCurrentUrl }, mainNavItems, page, Props, rightNavItems, props, resources_js_components_ui_avatar_index_avatar (+16 more)

### Community 40 - "scripts"
Cohesion: 0.14
Nodes (14): scripts, ci:check, dev, lint, lint:check, post-autoload-dump, post-create-project-cmd, post-root-package-install (+6 more)

### Community 41 - "LeadImportService"
Cohesion: 0.05
Nodes (18): BootstrapAdmin, SyncGoogleLeads, GoogleSheetSyncService, {closure#1}(), {closure#3}(), LeadImportService, UserFactory, Google\Auth\Credentials\ServiceAccountCredentials (+10 more)

### Community 42 - "Layout.vue"
Cohesion: 0.12
Nodes (15): resources_js_components_ui_dropdown_menu_index_dropdownmenugroup, resources_js_components_ui_dropdown_menu_index_dropdownmenuitem, resources_js_components_ui_dropdown_menu_index_dropdownmenulabel, resources_js_components_ui_dropdown_menu_index_dropdownmenuseparator, Props, { isCurrentOrParentUrl }, sidebarNavItems, resources_js_routes_appearance_index (+7 more)

### Community 43 - "global.d.ts"
Cohesion: 0.22
Nodes (9): Auth, ComponentCustomProperties, GlobalDirectives, ImportMeta, ImportMetaEnv, InertiaConfig, @inertiajs/core, vite/client (+1 more)

### Community 46 - "LeadQuickUpdate.vue"
Cohesion: 0.05
Nodes (41): zod, changedPayload(), emit, groups, hasSaved, invalidField, isDirty, latestActivityTime (+33 more)

### Community 47 - "Laravel\Fortify\Features"
Cohesion: 0.17
Nodes (5): Illuminate\Auth\Notifications\ResetPassword, Illuminate\Auth\Notifications\VerifyEmail, Illuminate\Support\Facades\Notification, Laravel\Fortify\Features, VerificationNotificationTest

### Community 49 - "composer.json"
Cohesion: 0.17
Nodes (11): autoload-dev, psr-4, description, keywords, license, minimum-stability, name, prefer-stable (+3 more)

### Community 50 - "require-dev"
Cohesion: 0.17
Nodes (12): require-dev, fakerphp/faker, larastan/larastan, laravel/pail, laravel/pao, laravel/pint, laravel/sail, mockery/mockery (+4 more)

### Community 52 - "Product"
Cohesion: 0.18
Nodes (10): Capabilities and Constraints, Evidence on Hand, Operating Context, Platform, Positioning, Product, Product Principles, Product Purpose (+2 more)

### Community 54 - "devDependencies"
Cohesion: 0.07
Nodes (25): devDependencies, concurrently, @laravel/vite-plugin-wayfinder, @tailwindcss/vite, @types/node, typescript, vite, vite-plus (+17 more)

### Community 55 - "PasskeyRegister.vue"
Cohesion: 0.25
Nodes (5): @laravel/passkeys, emit, name, { register, isLoading, error, isSupported }, showForm

### Community 56 - "bootstrap/app.php"
Cohesion: 0.15
Nodes (10): HandleInertiaRequests, {closure#1}(), {closure#2}(), {closure#3}(), Illuminate\Foundation\Application, Illuminate\Foundation\Configuration\Exceptions, Illuminate\Foundation\Configuration\Middleware, Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets (+2 more)

### Community 57 - "require"
Cohesion: 0.20
Nodes (10): require, google/auth, inertiajs/inertia-laravel, laravel/chisel, laravel/fortify, laravel/framework, laravel/tinker, laravel/wayfinder (+2 more)

### Community 59 - "SheetContent.vue"
Cohesion: 0.22
Nodes (7): delegatedProps, emits, forwarded, props, SheetContentProps, delegatedProps, props

### Community 62 - "useCurrentUrl.ts"
Cohesion: 0.33
Nodes (8): currentUrlReactive, page, useCurrentUrl(), isCurrentOrParentUrl(), isCurrentUrl(), whenCurrentUrl(), UseCurrentUrlReturn, toUrl()

### Community 64 - "SidebarMenuButton.vue"
Cohesion: 0.12
Nodes (11): delegatedProps, { isMobile, state }, props, resources_js_components_ui_tooltip_index_tooltip, resources_js_components_ui_tooltip_index_tooltipcontent, resources_js_components_ui_tooltip_index_tooltiptrigger, emits, forwarded (+3 more)

### Community 65 - "Badge.vue"
Cohesion: 0.20
Nodes (7): class-variance-authority, props, props, AlertVariants, delegatedProps, props, BadgeVariants

### Community 66 - "SidebarMenuSkeleton.vue"
Cohesion: 0.25
Nodes (5): props, width, resources_js_components_ui_skeleton_index_skeleton, props, SkeletonProps

### Community 67 - "config"
Cohesion: 0.29
Nodes (7): pestphp/pest-plugin, php-http/discovery, config, allow-plugins, optimize-autoloader, preferred-install, sort-packages

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

### Community 78 - "console.php"
Cohesion: 0.40
Nodes (3): Illuminate\Foundation\Inspiring, Illuminate\Support\Facades\Artisan, Illuminate\Support\Facades\Schedule

### Community 80 - "TwoFactorRecoveryCodes.vue"
Cohesion: 0.12
Nodes (18): ref_routes_two_factor, { hasSetupData, clearTwoFactorAuthData }, showSetupModal, isRecoveryCodesVisible, recoveryCodeSectionRef, { recoveryCodesList, fetchRecoveryCodes, errors }, resources_js_components_ui_card_index_card, resources_js_components_ui_card_index_cardcontent (+10 more)

### Community 115 - "AlertError.vue"
Cohesion: 0.33
Nodes (5): Props, uniqueErrors, resources_js_components_ui_alert_index_alert, resources_js_components_ui_alert_index_alertdescription, resources_js_components_ui_alert_index_alerttitle

### Community 120 - "EmailVerificationTest"
Cohesion: 0.15
Nodes (4): Illuminate\Auth\Events\Verified, Illuminate\Support\Facades\Event, Illuminate\Support\Facades\URL, EmailVerificationTest

## Knowledge Gaps
- **570 isolated node(s):** `$schema`, `style`, `config`, `css`, `baseColor` (+565 more)
  These have ≤1 connection - possible missing edges or undocumented components. (Counts symbols only; 810 node(s) total have ≤1 connection when file, concept and rationale nodes are included.)
- **42 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `vue` connect `vue` to `reka-ui`, `Sidebar.vue`, `navigation-menu/index.ts`, `Checkbox.vue`, `SelectContent.vue`, `Leads/Index.vue`, `types/index.ts`, `TwoFactorSetupModal.vue`, `@vueuse/core`, `button/index.ts`, `package.json`, `@lucide/vue`, `SidebarProvider.vue`, `AppSidebar.vue`, `InputOTPSlot.vue`, `Separator.vue`, `useAppearance.ts`, `AppHeader.vue`, `global.d.ts`, `LeadQuickUpdate.vue`, `PasskeyRegister.vue`, `SheetContent.vue`, `useCurrentUrl.ts`, `SidebarMenuButton.vue`, `Badge.vue`, `SidebarMenuSkeleton.vue`, `@inertiajs/vue3`, `TwoFactorRecoveryCodes.vue`, `AlertError.vue`?**
  _High betweenness centrality (0.124) - this node is a cross-community bridge._
- **Why does `@inertiajs/vue3` connect `@inertiajs/vue3` to `Security.vue`, `useAppearance.ts`, `AppHeader.vue`, `Leads/Index.vue`, `Layout.vue`, `types/index.ts`, `TwoFactorSetupModal.vue`, `vue`, `LeadQuickUpdate.vue`, `button/index.ts`, `TwoFactorRecoveryCodes.vue`, `package.json`, `@lucide/vue`, `AppSidebar.vue`, `useCurrentUrl.ts`?**
  _High betweenness centrality (0.046) - this node is a cross-community bridge._
- **Why does `User` connect `User` to `Illuminate\Database\Schema\Blueprint`, `ProfileValidationRules`, `PasswordValidationRules`, `PasswordResetTest`, `LeadImportService`, `DatabaseSeeder.php`, `Illuminate\Http\Request`, `LeadManagementTest`, `Laravel\Fortify\Features`, `TestCase`, `EmailVerificationTest`, `SecurityTest.php`, `AuthenticationTest`?**
  _High betweenness centrality (0.041) - this node is a cross-community bridge._
- **What connects `$schema`, `style`, `config` to the rest of the system?**
  _570 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `Illuminate\Database\Schema\Blueprint` be split into smaller, more focused modules?**
  _Cohesion score 0.06110102843315184 - nodes in this community are weakly interconnected._
- **Should `reka-ui` be split into smaller, more focused modules?**
  _Cohesion score 0.03420523138832998 - nodes in this community are weakly interconnected._
- **Should `LeadController.php` be split into smaller, more focused modules?**
  _Cohesion score 0.08064516129032258 - nodes in this community are weakly interconnected._