# Graph Report - marketing-leads  (2026-11-12)

## Corpus Check
- 292 files · ~54,107 words
- Verdict: corpus is large enough that graph structure adds value.
- Unclassified: 25 file(s) not represented in the graph (top: (none) 19, .example 2, .neon 1)

## Summary
- 1467 nodes · 2696 edges · 117 communities (77 shown, 40 thin omitted)
- Extraction: 100% EXTRACTED · 0% INFERRED · 0% AMBIGUOUS · INFERRED: 13 edges (avg confidence: 0.85)
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `7bfaf8f0`
- Run `git rev-parse HEAD` and compare to check if the graph is stale.
- Run `graphify update .` after code changes (no API cost).

## Community Hubs (Navigation)
- Illuminate\Database\Schema\Blueprint
- reka-ui
- LeadController.php
- SidebarProvider.vue
- @vueuse/core
- PasswordValidationRules
- @lucide/vue
- SelectContent.vue
- Leads/Index.vue
- User
- sheet/index.ts
- types/index.ts
- TwoFactorSetupModal.vue
- Illuminate\Http\Request
- DialogContent.vue
- DeleteUser.vue
- Illuminate\Http\RedirectResponse
- TestCase
- package.json
- button/index.ts
- vue
- Layout.vue
- compilerOptions
- AppSidebar.vue
- LeadController
- @inertiajs/vue3
- resources_js_routes_two_factor_index
- InputOTPSlot.vue
- web.php
- leads
- useAppearance.ts
- Show.vue
- ProfileController.php
- Profile.vue
- KGJ Leads
- dependencies
- FortifyServiceProvider
- sidebar/index.ts
- aliases
- AppHeader.vue
- scripts
- UserFactory.php
- tooltip/index.ts
- global.d.ts
- Laravel\Fortify\Features
- EmailVerificationTest
- AlertError.vue
- TwoFactorRecoveryCodes.vue
- BootstrapAdmin.php
- composer.json
- require-dev
- PasswordResetTest.php
- Product
- Accounts/Index.vue
- devDependencies
- PasskeyRegister.vue
- bootstrap/app.php
- require
- AuthenticationTest
- Collapsible.vue
- dialog/index.ts
- SheetContent.vue
- useCurrentUrl.ts
- optionalDependencies
- Sonner.vue
- UserMenuContent.vue
- SidebarMenuSkeleton.vue
- config
- VerificationNotificationTest.php
- scripts
- Separator.vue
- DashboardTest
- psr-4
- laravel
- logging.php
- DatabaseSeeder.php
- Design
- vite.config.ts
- console.php
- DropdownMenuContent.vue
- resources_js_routes_index
- TwoFactorChallengeTest
- GLOSSARY.md
- artisan
- overrides
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
- TooltipContent.vue
- DialogDescription.vue

## God Nodes (most connected - your core abstractions)
1. `vue` - 126 edges
2. `cn()` - 93 edges
3. `reka-ui` - 77 edges
4. `User` - 65 edges
5. `@vueuse/core` - 45 edges
6. `@inertiajs/vue3` - 44 edges
7. `@lucide/vue` - 37 edges
8. `LeadController` - 34 edges
9. `TestCase` - 32 edges
10. `LeadManagementTest` - 21 edges

## Surprising Connections (you probably didn't know these)
- `ProfileDeleteRequest` --mixes_in--> `PasswordValidationRules`  [EXTRACTED]
  app/Http/Requests/Settings/ProfileDeleteRequest.php → app/Concerns/PasswordValidationRules.php
- `DashboardController` --inherits--> `Controller`  [EXTRACTED]
  app/Http/Controllers/DashboardController.php → app/Http/Controllers/Controller.php
- `LeadController` --inherits--> `Controller`  [EXTRACTED]
  app/Http/Controllers/LeadController.php → app/Http/Controllers/Controller.php
- `{closure#1}()` --calls--> `User`  [EXTRACTED]
  app/Providers/FortifyServiceProvider.php → app/Models/User.php
- `users_one_active_cs_per_branch` --indexes--> `users`  [EXTRACTED]
  database/postgres/004_crud_lifecycle.sql → database/postgres/001_schema.sql

## Import Cycles
- None detected.

## Communities (117 total, 40 thin omitted)

### Community 0 - "Illuminate\Database\Schema\Blueprint"
Cohesion: 0.06
Nodes (27): {closure#1}(), {closure#2}(), {closure#3}(), {closure#1}(), {closure#2}(), {closure#1}(), {closure#2}(), {closure#3}() (+19 more)

### Community 1 - "reka-ui"
Cohesion: 0.06
Nodes (32): reka-ui, emits, forwarded, props, props, delegatedProps, forwardedProps, props (+24 more)

### Community 2 - "LeadController.php"
Cohesion: 0.10
Nodes (12): {closure#10}(), {closure#11}(), {closure#13}(), {closure#17}(), {closure#18}(), {closure#19}(), {closure#6}(), {closure#8}() (+4 more)

### Community 3 - "SidebarProvider.vue"
Cohesion: 0.16
Nodes (16): emits, isMobile, open, openMobile, props, setOpen(), setOpenMobile(), state (+8 more)

### Community 4 - "@vueuse/core"
Cohesion: 0.05
Nodes (34): class-variance-authority, @vueuse/core, delegatedProps, props, delegatedProps, props, BadgeVariants, navigationMenuTriggerStyle (+26 more)

### Community 5 - "PasswordValidationRules"
Cohesion: 0.08
Nodes (17): CreateNewUser, ResetUserPassword, PasswordValidationRules, ProfileValidationRules, PasswordUpdateRequest, ProfileUpdateRequest, TwoFactorAuthenticationRequest, UpdateLeadRequest (+9 more)

### Community 6 - "@lucide/vue"
Cohesion: 0.07
Nodes (20): @lucide/vue, props, delegatedProps, emits, forwarded, props, delegatedProps, emits (+12 more)

### Community 7 - "SelectContent.vue"
Cohesion: 0.06
Nodes (21): resources_js_components_ui_select_index_selectscrolldownbutton, resources_js_components_ui_select_index_selectscrollupbutton, emits, forwarded, props, delegatedProps, emits, forwarded (+13 more)

### Community 8 - "Leads/Index.vue"
Cohesion: 0.06
Nodes (30): activeFilterText, allVisibleSelected, applyFilters(), archiveDialogOpen, archiveSelected(), branch, bulkForm, clearFilters() (+22 more)

### Community 9 - "User"
Cohesion: 0.12
Nodes (10): User, Illuminate\Database\Eloquent\Factories\HasFactory, Illuminate\Foundation\Auth\User, Illuminate\Notifications\Notifiable, Illuminate\Support\Carbon, Laravel\Fortify\Contracts\PasskeyUser, Laravel\Fortify\PasskeyAuthenticatable, Laravel\Fortify\TwoFactorAuthenticatable (+2 more)

### Community 10 - "sheet/index.ts"
Cohesion: 0.11
Nodes (11): emits, forwarded, props, props, delegatedProps, props, props, props (+3 more)

### Community 11 - "types/index.ts"
Cohesion: 0.11
Nodes (19): className, Props, Props, Props, resources_js_components_ui_breadcrumb_index_breadcrumb, resources_js_components_ui_breadcrumb_index_breadcrumbitem, resources_js_components_ui_breadcrumb_index_breadcrumblink, resources_js_components_ui_breadcrumb_index_breadcrumblist (+11 more)

### Community 12 - "TwoFactorSetupModal.vue"
Cohesion: 0.10
Nodes (17): ref_routes_two_factor_login, code, { copy, copied }, isOpen, modalConfig, pinInputContainerRef, Props, { qrCodeSvg, manualSetupKey, clearSetupData, fetchSetupData, errors } (+9 more)

### Community 13 - "Illuminate\Http\Request"
Cohesion: 0.13
Nodes (16): LoginResponse, LogoutResponse, {closure#1}(), {closure#10}(), {closure#11}(), {closure#2}(), {closure#3}(), {closure#4}() (+8 more)

### Community 14 - "DialogContent.vue"
Cohesion: 0.25
Nodes (6): delegatedProps, emits, forwarded, props, delegatedProps, props

### Community 15 - "DeleteUser.vue"
Cohesion: 0.11
Nodes (22): passwordInput, emit, handleDelete(), isDeleting, props, resources_js_components_ui_dialog_index_dialog, resources_js_components_ui_dialog_index_dialogclose, resources_js_components_ui_dialog_index_dialogcontent (+14 more)

### Community 16 - "Illuminate\Http\RedirectResponse"
Cohesion: 0.16
Nodes (4): DashboardController, Illuminate\Http\RedirectResponse, Illuminate\Validation\ValidationException, Inertia\Response

### Community 17 - "TestCase"
Cohesion: 0.14
Nodes (7): Illuminate\Foundation\Testing\RefreshDatabase, Illuminate\Foundation\Testing\TestCase, RegistrationTest, ExampleTest, TrustedProxyTest, TestCase, ExampleTest

### Community 18 - "package.json"
Cohesion: 0.09
Nodes (21): private, $schema, type, clsx, concurrently, @inertiajs/vite, @laravel/multiplex, @laravel/vite-plugin-wayfinder (+13 more)

### Community 19 - "button/index.ts"
Cohesion: 0.11
Nodes (21): ref_actions_laravel_passkeys_http_controllers_passkeyconfirmationcontroller, ref_inertiajs_core, ref_routes_register, Props, { verify, isLoading, error, isSupported }, Props, Props, resources_js_components_ui_button_index_button (+13 more)

### Community 20 - "vue"
Cohesion: 0.05
Nodes (41): vue, props, props, props, AlertVariants, props, props, props (+33 more)

### Community 21 - "Layout.vue"
Cohesion: 0.14
Nodes (12): ref_actions_laravel_passkeys_http_controllers_passkeyregistrationcontroller, resources_js_actions_app_http_controllers_settings_securitycontroller, Props, Props, { isCurrentOrParentUrl }, sidebarNavItems, Props, resources_js_routes_appearance_index (+4 more)

### Community 22 - "compilerOptions"
Cohesion: 0.09
Nodes (21): compilerOptions, allowJs, esModuleInterop, forceConsistentCasingInFileNames, isolatedModules, jsx, jsxImportSource, lib (+13 more)

### Community 23 - "AppSidebar.vue"
Cohesion: 0.11
Nodes (23): footerNavItems, mainNavItems, Props, { isCurrentUrl }, { isMobile, state }, page, user, resources_js_components_ui_dropdown_menu_index_dropdownmenu (+15 more)

### Community 24 - "LeadController"
Cohesion: 0.14
Nodes (4): {closure#1}(), {closure#3}(), {closure#5}(), LeadController

### Community 25 - "@inertiajs/vue3"
Cohesion: 0.15
Nodes (9): @inertiajs/vue3, pages, initializeFlashToast(), emailInput, form, props, stages, props (+1 more)

### Community 27 - "InputOTPSlot.vue"
Cohesion: 0.11
Nodes (15): vue-input-otp, delegatedProps, emits, forwarded, props, delegatedProps, forwarded, props (+7 more)

### Community 28 - "web.php"
Cohesion: 0.22
Nodes (6): EnsureAdmin, HandleAppearance, Closure, Illuminate\Support\Facades\Route, Illuminate\Support\Facades\View, Symfony\Component\HttpFoundation\Response

### Community 29 - "leads"
Cohesion: 0.19
Nodes (14): branches, lead_activity, lead_activity_lead_created_idx, lead_milestones, leads, leads_assigned_user_idx, leads_updated_at_idx, users (+6 more)

### Community 30 - "useAppearance.ts"
Cohesion: 0.14
Nodes (19): { appearance, updateAppearance }, tabs, appearance, getStoredAppearance(), handleSystemThemeChange(), initializeTheme(), mediaQuery(), prefersDark() (+11 more)

### Community 31 - "Show.vue"
Cohesion: 0.10
Nodes (15): zod, leadSchema, archiveDialogOpen, clientError, deleteDialogOpen, focusFirstError(), form, formFields (+7 more)

### Community 32 - "ProfileController.php"
Cohesion: 0.12
Nodes (8): Controller, ProfileController, SecurityController, ProfileDeleteRequest, Illuminate\Auth\Middleware\RequirePassword, Illuminate\Contracts\Auth\MustVerifyEmail, Illuminate\Support\Facades\Auth, Inertia\Inertia

### Community 33 - "Profile.vue"
Cohesion: 0.13
Nodes (12): ref_routes_verification, resources_js_actions_app_http_controllers_settings_profilecontroller, inputRef, props, showPassword, resources_js_components_ui_input_index_input, emits, modelValue (+4 more)

### Community 34 - "KGJ Leads"
Cohesion: 0.12
Nodes (15): Auto deploy, Deploy manual, Migrasi dan backup, Panduan Deployment KGJ Leads, Production VPS 1, Admin, Alur kerja harian, Bantuan (+7 more)

### Community 35 - "dependencies"
Cohesion: 0.12
Nodes (17): dependencies, class-variance-authority, clsx, @inertiajs/vite, @inertiajs/vue3, @laravel/passkeys, laravel-vite-plugin, @lucide/vue (+9 more)

### Community 36 - "FortifyServiceProvider"
Cohesion: 0.19
Nodes (5): AppServiceProvider, FortifyServiceProvider, Carbon\CarbonImmutable, Illuminate\Support\Facades\Date, Illuminate\Support\ServiceProvider

### Community 37 - "sidebar/index.ts"
Cohesion: 0.12
Nodes (18): resources_js_components_ui_sheet_index_sheet, resources_js_components_ui_sheet_index_sheetcontent, SidebarMenuButtonVariants, SidebarProps, { isMobile, state, openMobile, setOpenMobile }, props, delegatedProps, { isMobile, state } (+10 more)

### Community 38 - "aliases"
Cohesion: 0.12
Nodes (15): aliases, components, composables, lib, ui, utils, iconLibrary, $schema (+7 more)

### Community 39 - "AppHeader.vue"
Cohesion: 0.07
Nodes (28): auth, { isCurrentUrl, whenCurrentUrl }, mainNavItems, page, Props, rightNavItems, Props, props (+20 more)

### Community 40 - "scripts"
Cohesion: 0.14
Nodes (14): scripts, ci:check, dev, lint, lint:check, post-autoload-dump, post-create-project-cmd, post-root-package-install (+6 more)

### Community 41 - "UserFactory.php"
Cohesion: 0.16
Nodes (6): UserFactory, Illuminate\Database\Eloquent\Factories\Factory, Illuminate\Support\Facades\Hash, Illuminate\Support\Str, Pdo\Mysql, static

### Community 42 - "tooltip/index.ts"
Cohesion: 0.22
Nodes (5): emits, forwarded, props, props, props

### Community 43 - "global.d.ts"
Cohesion: 0.16
Nodes (12): Auth, Passkey, TwoFactorConfigContent, User, ComponentCustomProperties, GlobalDirectives, ImportMeta, ImportMetaEnv (+4 more)

### Community 44 - "Laravel\Fortify\Features"
Cohesion: 0.22
Nodes (7): Inertia\Testing\AssertableInertia, Laravel\Fortify\Features, {closure#1}(), PasswordConfirmationTest, {closure#1}(), {closure#1}(), {closure#2}()

### Community 45 - "EmailVerificationTest"
Cohesion: 0.15
Nodes (4): Illuminate\Auth\Events\Verified, Illuminate\Support\Facades\Event, Illuminate\Support\Facades\URL, EmailVerificationTest

### Community 46 - "AlertError.vue"
Cohesion: 0.33
Nodes (5): Props, uniqueErrors, resources_js_components_ui_alert_index_alert, resources_js_components_ui_alert_index_alertdescription, resources_js_components_ui_alert_index_alerttitle

### Community 47 - "TwoFactorRecoveryCodes.vue"
Cohesion: 0.11
Nodes (19): ref_routes_two_factor, { hasSetupData, clearTwoFactorAuthData }, Props, showSetupModal, isRecoveryCodesVisible, recoveryCodeSectionRef, { recoveryCodesList, fetchRecoveryCodes, errors }, resources_js_components_ui_card_index_card (+11 more)

### Community 48 - "BootstrapAdmin.php"
Cohesion: 0.20
Nodes (6): BootstrapAdmin, HandleInertiaRequests, Illuminate\Console\Command, Illuminate\Support\Facades\DB, Illuminate\Support\Facades\Validator, Inertia\Middleware

### Community 49 - "composer.json"
Cohesion: 0.17
Nodes (11): autoload-dev, psr-4, description, keywords, license, minimum-stability, name, prefer-stable (+3 more)

### Community 50 - "require-dev"
Cohesion: 0.17
Nodes (12): require-dev, fakerphp/faker, larastan/larastan, laravel/pail, laravel/pao, laravel/pint, laravel/sail, mockery/mockery (+4 more)

### Community 52 - "Product"
Cohesion: 0.18
Nodes (10): Capabilities and Constraints, Evidence on Hand, Operating Context, Platform, Positioning, Product, Product Principles, Product Purpose (+2 more)

### Community 53 - "Accounts/Index.vue"
Cohesion: 0.20
Nodes (8): Account, accountToToggle, cancel(), editingId, form, needsBranch, props, save()

### Community 54 - "devDependencies"
Cohesion: 0.20
Nodes (10): devDependencies, concurrently, @laravel/vite-plugin-wayfinder, @tailwindcss/vite, @types/node, typescript, vite, vite-plus (+2 more)

### Community 55 - "PasskeyRegister.vue"
Cohesion: 0.17
Nodes (7): @laravel/passkeys, emit, name, { register, isLoading, error, isSupported }, showForm, delegatedProps, props

### Community 56 - "bootstrap/app.php"
Cohesion: 0.28
Nodes (7): {closure#1}(), {closure#2}(), {closure#3}(), Illuminate\Foundation\Application, Illuminate\Foundation\Configuration\Exceptions, Illuminate\Foundation\Configuration\Middleware, Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets

### Community 57 - "require"
Cohesion: 0.22
Nodes (9): require, inertiajs/inertia-laravel, laravel/chisel, laravel/fortify, laravel/framework, laravel/tinker, laravel/wayfinder, openspout/openspout (+1 more)

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

### Community 63 - "optionalDependencies"
Cohesion: 0.25
Nodes (8): optionalDependencies, @laravel/multiplex, lightningcss-linux-x64-gnu, lightningcss-win32-x64-msvc, @rollup/rollup-linux-x64-gnu, @rollup/rollup-win32-x64-msvc, @tailwindcss/oxide-linux-x64-gnu, @tailwindcss/oxide-win32-x64-msvc

### Community 65 - "UserMenuContent.vue"
Cohesion: 0.25
Nodes (6): resources_js_components_ui_dropdown_menu_index_dropdownmenugroup, resources_js_components_ui_dropdown_menu_index_dropdownmenuitem, resources_js_components_ui_dropdown_menu_index_dropdownmenulabel, resources_js_components_ui_dropdown_menu_index_dropdownmenuseparator, Props, resources_js_routes_profile_index

### Community 66 - "SidebarMenuSkeleton.vue"
Cohesion: 0.25
Nodes (5): props, width, resources_js_components_ui_skeleton_index_skeleton, props, SkeletonProps

### Community 67 - "config"
Cohesion: 0.29
Nodes (7): pestphp/pest-plugin, php-http/discovery, config, allow-plugins, optimize-autoloader, preferred-install, sort-packages

### Community 68 - "VerificationNotificationTest.php"
Cohesion: 0.29
Nodes (3): Illuminate\Auth\Notifications\VerifyEmail, Illuminate\Support\Facades\Notification, VerificationNotificationTest

### Community 69 - "scripts"
Cohesion: 0.29
Nodes (7): scripts, build, build:ssr, check, check:fix, dev, types:check

### Community 70 - "Separator.vue"
Cohesion: 0.29
Nodes (4): resources_js_components_ui_separator_index_separator, delegatedProps, props, props

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

### Community 77 - "vite.config.ts"
Cohesion: 0.40
Nodes (4): laravel-vite-plugin, @tailwindcss/vite, vite-plus, @vitejs/plugin-vue

### Community 79 - "DropdownMenuContent.vue"
Cohesion: 0.40
Nodes (4): delegatedProps, emits, forwarded, props

### Community 80 - "resources_js_routes_index"
Cohesion: 0.20
Nodes (7): page, { title = '', description = '' }, resources_js_routes_index, resources_js_routes_index_dashboard, resources_js_routes_index_home, resources_js_routes_index_login, resources_js_routes_index_register

### Community 115 - "TooltipContent.vue"
Cohesion: 0.40
Nodes (4): delegatedProps, emits, forwarded, props

### Community 116 - "DialogDescription.vue"
Cohesion: 0.50
Nodes (3): delegatedProps, forwardedProps, props

## Knowledge Gaps
- **546 isolated node(s):** `$schema`, `style`, `config`, `css`, `baseColor` (+541 more)
  These have ≤1 connection - possible missing edges or undocumented components. (Counts symbols only; 767 node(s) total have ≤1 connection when file, concept and rationale nodes are included.)
- **40 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `vue` connect `vue` to `reka-ui`, `SidebarProvider.vue`, `@vueuse/core`, `@lucide/vue`, `SelectContent.vue`, `Leads/Index.vue`, `sheet/index.ts`, `types/index.ts`, `TwoFactorSetupModal.vue`, `DialogContent.vue`, `DeleteUser.vue`, `package.json`, `button/index.ts`, `AppSidebar.vue`, `@inertiajs/vue3`, `InputOTPSlot.vue`, `useAppearance.ts`, `Show.vue`, `Profile.vue`, `sidebar/index.ts`, `AppHeader.vue`, `global.d.ts`, `AlertError.vue`, `TwoFactorRecoveryCodes.vue`, `Accounts/Index.vue`, `PasskeyRegister.vue`, `SheetContent.vue`, `useCurrentUrl.ts`, `SidebarMenuSkeleton.vue`, `Separator.vue`, `DropdownMenuContent.vue`, `TooltipContent.vue`, `DialogDescription.vue`?**
  _High betweenness centrality (0.133) - this node is a cross-community bridge._
- **Why does `reka-ui` connect `reka-ui` to `SidebarProvider.vue`, `@vueuse/core`, `@lucide/vue`, `SelectContent.vue`, `sheet/index.ts`, `DialogContent.vue`, `package.json`, `button/index.ts`, `vue`, `InputOTPSlot.vue`, `sidebar/index.ts`, `AppHeader.vue`, `tooltip/index.ts`, `PasskeyRegister.vue`, `Collapsible.vue`, `dialog/index.ts`, `SheetContent.vue`, `Separator.vue`, `DropdownMenuContent.vue`, `TooltipContent.vue`, `DialogDescription.vue`?**
  _High betweenness centrality (0.048) - this node is a cross-community bridge._
- **Why does `@inertiajs/vue3` connect `@inertiajs/vue3` to `UserMenuContent.vue`, `Profile.vue`, `AppHeader.vue`, `Leads/Index.vue`, `types/index.ts`, `TwoFactorSetupModal.vue`, `DeleteUser.vue`, `TwoFactorRecoveryCodes.vue`, `resources_js_routes_index`, `package.json`, `button/index.ts`, `vue`, `Layout.vue`, `Accounts/Index.vue`, `AppSidebar.vue`, `useCurrentUrl.ts`, `Show.vue`?**
  _High betweenness centrality (0.040) - this node is a cross-community bridge._
- **What connects `$schema`, `style`, `config` to the rest of the system?**
  _546 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `Illuminate\Database\Schema\Blueprint` be split into smaller, more focused modules?**
  _Cohesion score 0.05711263881544157 - nodes in this community are weakly interconnected._
- **Should `reka-ui` be split into smaller, more focused modules?**
  _Cohesion score 0.05708245243128964 - nodes in this community are weakly interconnected._
- **Should `LeadController.php` be split into smaller, more focused modules?**
  _Cohesion score 0.1 - nodes in this community are weakly interconnected._