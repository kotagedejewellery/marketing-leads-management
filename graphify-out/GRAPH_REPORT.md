# Graph Report - marketing-leads  (2026-11-12)

## Corpus Check
- 292 files · ~55,224 words
- Verdict: corpus is large enough that graph structure adds value.
- Unclassified: 25 file(s) not represented in the graph (top: (none) 19, .example 2, .neon 1)

## Summary
- 1491 nodes · 2749 edges · 125 communities (82 shown, 43 thin omitted)
- Extraction: 100% EXTRACTED · 0% INFERRED · 0% AMBIGUOUS · INFERRED: 13 edges (avg confidence: 0.85)
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `f28c43fd`
- Run `git rev-parse HEAD` and compare to check if the graph is stale.
- Run `graphify update .` after code changes (no API cost).

## Community Hubs (Navigation)
- Illuminate\Support\Facades\Schema
- reka-ui
- LeadController.php
- SidebarProvider.vue
- @vueuse/core
- PasswordValidationRules
- @lucide/vue
- SelectContent.vue
- Leads/Index.vue
- User
- Sidebar.vue
- types/index.ts
- TwoFactorSetupModal.vue
- Illuminate\Http\Request
- DialogContent.vue
- DeleteUser.vue
- Illuminate\Http\RedirectResponse
- TestCase
- package.json
- resources_js_components_ui_button_index_button
- lib/utils.ts
- vue
- compilerOptions
- AppSidebar.vue
- LeadController
- AuthenticationTest
- resources_js_routes_two_factor_index
- InputOTPSlot.vue
- web.php
- leads
- useAppearance.ts
- Show.vue
- ProfileController.php
- Layout.vue
- KGJ Leads
- dependencies
- AppHeader.vue
- DropdownMenu.vue
- aliases
- UserMenuContent.vue
- scripts
- UserFactory.php
- Badge.vue
- global.d.ts
- TwoFactorChallengeTest.php
- LeadManagementTest
- AlertError.vue
- TwoFactorRecoveryCodes.vue
- SecurityTest.php
- composer.json
- require-dev
- Laravel\Fortify\Features
- Product
- @inertiajs/vue3
- devDependencies
- button/index.ts
- bootstrap/app.php
- require
- DashboardController.php
- Collapsible.vue
- dialog/index.ts
- SheetContent.vue
- useCurrentUrl.ts
- optionalDependencies
- Tooltip.vue
- Illuminate\Database\Schema\Blueprint
- SidebarMenuSkeleton.vue
- config
- DashboardTest
- scripts
- Separator.vue
- PasswordResetTest
- psr-4
- laravel
- logging.php
- DatabaseSeeder.php
- Design
- vite.config.ts
- console.php
- Inertia\Testing\AssertableInertia
- DropdownMenuRadioGroup.vue
- BootstrapAdmin.php
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
- app.ts
- Accounts/Index.vue
- Select.vue
- VerificationNotificationTest
- NavUser.vue
- 0001_01_01_000000_create_users_table.php
- Input.vue
- Sheet.vue
- Login.vue
- Label.vue

## God Nodes (most connected - your core abstractions)
1. `vue` - 126 edges
2. `cn()` - 93 edges
3. `reka-ui` - 77 edges
4. `User` - 68 edges
5. `@vueuse/core` - 45 edges
6. `@inertiajs/vue3` - 44 edges
7. `@lucide/vue` - 37 edges
8. `LeadController` - 34 edges
9. `TestCase` - 32 edges
10. `LeadManagementTest` - 23 edges

## Surprising Connections (you probably didn't know these)
- `ProfileUpdateRequest` --mixes_in--> `ProfileValidationRules`  [EXTRACTED]
  app/Http/Requests/Settings/ProfileUpdateRequest.php → app/Concerns/ProfileValidationRules.php
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

## Communities (125 total, 43 thin omitted)

### Community 0 - "Illuminate\Support\Facades\Schema"
Cohesion: 0.14
Nodes (7): {closure#1}(), {closure#2}(), {closure#1}(), {closure#1}(), {closure#2}(), Illuminate\Database\Migrations\Migration, Illuminate\Support\Facades\Schema

### Community 1 - "reka-ui"
Cohesion: 0.06
Nodes (30): reka-ui, delegatedProps, emits, forwarded, props, delegatedProps, emits, forwarded (+22 more)

### Community 2 - "LeadController.php"
Cohesion: 0.10
Nodes (12): {closure#10}(), {closure#11}(), {closure#13}(), {closure#17}(), {closure#18}(), {closure#19}(), {closure#6}(), {closure#8}() (+4 more)

### Community 3 - "SidebarProvider.vue"
Cohesion: 0.16
Nodes (16): emits, isMobile, open, openMobile, props, setOpen(), setOpenMobile(), state (+8 more)

### Community 4 - "@vueuse/core"
Cohesion: 0.05
Nodes (37): @vueuse/core, delegatedProps, props, delegatedProps, forwardedProps, props, navigationMenuTriggerStyle, delegatedProps (+29 more)

### Community 5 - "PasswordValidationRules"
Cohesion: 0.06
Nodes (23): CreateNewUser, ResetUserPassword, PasswordValidationRules, ProfileValidationRules, PasswordUpdateRequest, ProfileDeleteRequest, TwoFactorAuthenticationRequest, UpdateLeadRequest (+15 more)

### Community 6 - "@lucide/vue"
Cohesion: 0.08
Nodes (19): @lucide/vue, { appearance, updateAppearance }, tabs, delegatedProps, emits, forwarded, props, delegatedProps (+11 more)

### Community 7 - "SelectContent.vue"
Cohesion: 0.06
Nodes (21): resources_js_components_ui_select_index_selectscrolldownbutton, resources_js_components_ui_select_index_selectscrollupbutton, delegatedProps, emits, forwarded, props, props, props (+13 more)

### Community 8 - "Leads/Index.vue"
Cohesion: 0.06
Nodes (30): activeFilterText, allVisibleSelected, applyFilters(), archiveDialogOpen, archiveSelected(), branch, bulkForm, clearFilters() (+22 more)

### Community 9 - "User"
Cohesion: 0.11
Nodes (10): User, Illuminate\Database\Eloquent\Factories\HasFactory, Illuminate\Foundation\Auth\User, Illuminate\Notifications\Notifiable, Illuminate\Support\Carbon, Laravel\Fortify\Contracts\PasskeyUser, Laravel\Fortify\PasskeyAuthenticatable, Laravel\Fortify\TwoFactorAuthenticatable (+2 more)

### Community 10 - "Sidebar.vue"
Cohesion: 0.10
Nodes (14): resources_js_components_ui_sheet_index_sheet, resources_js_components_ui_sheet_index_sheetcontent, props, delegatedProps, props, props, props, delegatedProps (+6 more)

### Community 11 - "types/index.ts"
Cohesion: 0.11
Nodes (20): className, Props, Props, Props, resources_js_components_ui_breadcrumb_index_breadcrumb, resources_js_components_ui_breadcrumb_index_breadcrumbitem, resources_js_components_ui_breadcrumb_index_breadcrumblink, resources_js_components_ui_breadcrumb_index_breadcrumblist (+12 more)

### Community 12 - "TwoFactorSetupModal.vue"
Cohesion: 0.10
Nodes (18): ref_routes_two_factor_login, code, { copy, copied }, isOpen, modalConfig, pinInputContainerRef, Props, { qrCodeSvg, manualSetupKey, clearSetupData, fetchSetupData, errors } (+10 more)

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
Cohesion: 0.19
Nodes (3): DashboardController, Illuminate\Http\RedirectResponse, Inertia\Response

### Community 17 - "TestCase"
Cohesion: 0.11
Nodes (9): Illuminate\Foundation\Testing\RefreshDatabase, Illuminate\Foundation\Testing\TestCase, {closure#1}(), PasswordConfirmationTest, RegistrationTest, ExampleTest, TrustedProxyTest, TestCase (+1 more)

### Community 18 - "package.json"
Cohesion: 0.09
Nodes (21): private, $schema, type, clsx, concurrently, @inertiajs/vite, @laravel/multiplex, @laravel/vite-plugin-wayfinder (+13 more)

### Community 19 - "resources_js_components_ui_button_index_button"
Cohesion: 0.15
Nodes (17): ref_actions_laravel_passkeys_http_controllers_passkeyconfirmationcontroller, ref_routes_register, inputRef, props, showPassword, resources_js_components_ui_button_index_button, resources_js_components_ui_input_index_input, resources_js_components_ui_label_index_label (+9 more)

### Community 20 - "lib/utils.ts"
Cohesion: 0.07
Nodes (30): props, props, props, props, props, SidebarMenuButtonVariants, props, props (+22 more)

### Community 21 - "vue"
Cohesion: 0.07
Nodes (19): vue, props, props, props, props, props, props, props (+11 more)

### Community 22 - "compilerOptions"
Cohesion: 0.09
Nodes (21): compilerOptions, allowJs, esModuleInterop, forceConsistentCasingInFileNames, isolatedModules, jsx, jsxImportSource, lib (+13 more)

### Community 23 - "AppSidebar.vue"
Cohesion: 0.17
Nodes (16): footerNavItems, mainNavItems, Props, { isCurrentUrl }, resources_js_components_ui_sidebar_index_sidebar, resources_js_components_ui_sidebar_index_sidebarcontent, resources_js_components_ui_sidebar_index_sidebarfooter, resources_js_components_ui_sidebar_index_sidebargroup (+8 more)

### Community 24 - "LeadController"
Cohesion: 0.14
Nodes (4): {closure#1}(), {closure#3}(), {closure#5}(), LeadController

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
Nodes (18): vue-sonner, props, appearance, getStoredAppearance(), handleSystemThemeChange(), initializeTheme(), mediaQuery(), prefersDark() (+10 more)

### Community 31 - "Show.vue"
Cohesion: 0.10
Nodes (15): zod, leadSchema, archiveDialogOpen, clientError, deleteDialogOpen, focusFirstError(), form, formFields (+7 more)

### Community 32 - "ProfileController.php"
Cohesion: 0.11
Nodes (8): Controller, ProfileController, SecurityController, ProfileUpdateRequest, Illuminate\Auth\Middleware\RequirePassword, Illuminate\Contracts\Auth\MustVerifyEmail, Illuminate\Support\Facades\Auth, Inertia\Inertia

### Community 33 - "Layout.vue"
Cohesion: 0.11
Nodes (17): ref_actions_laravel_passkeys_http_controllers_passkeyregistrationcontroller, ref_routes_verification, resources_js_actions_app_http_controllers_settings_profilecontroller, resources_js_actions_app_http_controllers_settings_securitycontroller, Props, Props, { isCurrentOrParentUrl }, sidebarNavItems (+9 more)

### Community 34 - "KGJ Leads"
Cohesion: 0.12
Nodes (15): Auto deploy, Deploy manual, Migrasi dan backup, Panduan Deployment KGJ Leads, Production VPS 1, Admin, Alur kerja harian, Bantuan (+7 more)

### Community 35 - "dependencies"
Cohesion: 0.12
Nodes (17): dependencies, class-variance-authority, clsx, @inertiajs/vite, @inertiajs/vue3, @laravel/passkeys, laravel-vite-plugin, @lucide/vue (+9 more)

### Community 36 - "AppHeader.vue"
Cohesion: 0.09
Nodes (21): auth, { isCurrentUrl, whenCurrentUrl }, mainNavItems, page, Props, rightNavItems, resources_js_components_ui_navigation_menu_index_navigationmenu, resources_js_components_ui_navigation_menu_index_navigationmenuitem (+13 more)

### Community 37 - "DropdownMenu.vue"
Cohesion: 0.50
Nodes (3): emits, forwarded, props

### Community 38 - "aliases"
Cohesion: 0.12
Nodes (15): aliases, components, composables, lib, ui, utils, iconLibrary, $schema (+7 more)

### Community 39 - "UserMenuContent.vue"
Cohesion: 0.10
Nodes (19): props, resources_js_components_ui_avatar_index_avatar, resources_js_components_ui_avatar_index_avatarfallback, resources_js_components_ui_avatar_index_avatarimage, resources_js_components_ui_dropdown_menu_index_dropdownmenugroup, resources_js_components_ui_dropdown_menu_index_dropdownmenuitem, resources_js_components_ui_dropdown_menu_index_dropdownmenulabel, resources_js_components_ui_dropdown_menu_index_dropdownmenuseparator (+11 more)

### Community 40 - "scripts"
Cohesion: 0.14
Nodes (14): scripts, ci:check, dev, lint, lint:check, post-autoload-dump, post-create-project-cmd, post-root-package-install (+6 more)

### Community 41 - "UserFactory.php"
Cohesion: 0.18
Nodes (5): UserFactory, Illuminate\Database\Eloquent\Factories\Factory, Illuminate\Support\Str, Pdo\Mysql, static

### Community 42 - "Badge.vue"
Cohesion: 0.20
Nodes (7): class-variance-authority, props, props, AlertVariants, delegatedProps, props, BadgeVariants

### Community 43 - "global.d.ts"
Cohesion: 0.20
Nodes (10): Auth, Passkey, ComponentCustomProperties, GlobalDirectives, ImportMeta, ImportMetaEnv, InertiaConfig, @inertiajs/core (+2 more)

### Community 46 - "AlertError.vue"
Cohesion: 0.33
Nodes (5): Props, uniqueErrors, resources_js_components_ui_alert_index_alert, resources_js_components_ui_alert_index_alertdescription, resources_js_components_ui_alert_index_alerttitle

### Community 47 - "TwoFactorRecoveryCodes.vue"
Cohesion: 0.12
Nodes (19): ref_routes_two_factor, { hasSetupData, clearTwoFactorAuthData }, Props, showSetupModal, isRecoveryCodesVisible, recoveryCodeSectionRef, { recoveryCodesList, fetchRecoveryCodes, errors }, resources_js_components_ui_card_index_card (+11 more)

### Community 48 - "SecurityTest.php"
Cohesion: 0.22
Nodes (3): {closure#1}(), {closure#2}(), SecurityTest

### Community 49 - "composer.json"
Cohesion: 0.17
Nodes (11): autoload-dev, psr-4, description, keywords, license, minimum-stability, name, prefer-stable (+3 more)

### Community 50 - "require-dev"
Cohesion: 0.17
Nodes (12): require-dev, fakerphp/faker, larastan/larastan, laravel/pail, laravel/pao, laravel/pint, laravel/sail, mockery/mockery (+4 more)

### Community 51 - "Laravel\Fortify\Features"
Cohesion: 0.17
Nodes (7): Illuminate\Auth\Events\Verified, Illuminate\Auth\Notifications\ResetPassword, Illuminate\Auth\Notifications\VerifyEmail, Illuminate\Support\Facades\Event, Illuminate\Support\Facades\Notification, Illuminate\Support\Facades\URL, Laravel\Fortify\Features

### Community 52 - "Product"
Cohesion: 0.18
Nodes (10): Capabilities and Constraints, Evidence on Hand, Operating Context, Platform, Positioning, Product, Product Principles, Product Purpose (+2 more)

### Community 53 - "@inertiajs/vue3"
Cohesion: 0.17
Nodes (9): @inertiajs/vue3, Props, Props, page, { title = '', description = '' }, resources_js_routes_index, resources_js_routes_index_dashboard, resources_js_routes_index_home (+1 more)

### Community 54 - "devDependencies"
Cohesion: 0.20
Nodes (10): devDependencies, concurrently, @laravel/vite-plugin-wayfinder, @tailwindcss/vite, @types/node, typescript, vite, vite-plus (+2 more)

### Community 55 - "button/index.ts"
Cohesion: 0.14
Nodes (11): ref_inertiajs_core, @laravel/passkeys, emit, name, { register, isLoading, error, isSupported }, showForm, Props, { verify, isLoading, error, isSupported } (+3 more)

### Community 56 - "bootstrap/app.php"
Cohesion: 0.28
Nodes (7): {closure#1}(), {closure#2}(), {closure#3}(), Illuminate\Foundation\Application, Illuminate\Foundation\Configuration\Exceptions, Illuminate\Foundation\Configuration\Middleware, Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets

### Community 57 - "require"
Cohesion: 0.22
Nodes (9): require, inertiajs/inertia-laravel, laravel/chisel, laravel/fortify, laravel/framework, laravel/tinker, laravel/wayfinder, openspout/openspout (+1 more)

### Community 58 - "DashboardController.php"
Cohesion: 0.25
Nodes (3): {closure#2}(), Illuminate\Database\Query\Builder, Illuminate\Validation\ValidationException

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

### Community 64 - "Tooltip.vue"
Cohesion: 0.50
Nodes (3): emits, forwarded, props

### Community 65 - "Illuminate\Database\Schema\Blueprint"
Cohesion: 0.19
Nodes (12): {closure#1}(), {closure#2}(), {closure#3}(), Illuminate\Database\Schema\Blueprint, Illuminate\Http\UploadedFile, OpenSpout\Common\Entity\Row, OpenSpout\Writer\XLSX\Writer, {closure#1}() (+4 more)

### Community 66 - "SidebarMenuSkeleton.vue"
Cohesion: 0.25
Nodes (5): props, width, resources_js_components_ui_skeleton_index_skeleton, props, SkeletonProps

### Community 67 - "config"
Cohesion: 0.29
Nodes (7): pestphp/pest-plugin, php-http/discovery, config, allow-plugins, optimize-autoloader, preferred-install, sort-packages

### Community 69 - "scripts"
Cohesion: 0.29
Nodes (7): scripts, build, build:ssr, check, check:fix, dev, types:check

### Community 70 - "Separator.vue"
Cohesion: 0.33
Nodes (3): delegatedProps, props, props

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

### Community 79 - "Inertia\Testing\AssertableInertia"
Cohesion: 0.18
Nodes (14): Inertia\Testing\AssertableInertia, {closure#1}(), {closure#2}(), {closure#3}(), {closure#4}(), {closure#5}(), {closure#6}(), {closure#7}() (+6 more)

### Community 80 - "DropdownMenuRadioGroup.vue"
Cohesion: 0.50
Nodes (3): emits, forwarded, props

### Community 81 - "BootstrapAdmin.php"
Cohesion: 0.20
Nodes (6): BootstrapAdmin, HandleInertiaRequests, Illuminate\Console\Command, Illuminate\Support\Facades\DB, Illuminate\Support\Facades\Hash, Inertia\Middleware

### Community 115 - "app.ts"
Cohesion: 0.17
Nodes (6): pages, initializeFlashToast(), priorityRows, props, props, stages

### Community 116 - "Accounts/Index.vue"
Cohesion: 0.20
Nodes (8): Account, accountToToggle, cancel(), editingId, form, needsBranch, props, save()

### Community 117 - "Select.vue"
Cohesion: 0.50
Nodes (3): emits, forwarded, props

### Community 119 - "NavUser.vue"
Cohesion: 0.25
Nodes (7): { isMobile, state }, page, user, resources_js_components_ui_dropdown_menu_index_dropdownmenu, resources_js_components_ui_dropdown_menu_index_dropdownmenucontent, resources_js_components_ui_dropdown_menu_index_dropdownmenutrigger, resources_js_components_ui_sidebar_index_usesidebar

### Community 120 - "0001_01_01_000000_create_users_table.php"
Cohesion: 0.33
Nodes (3): {closure#1}(), {closure#2}(), {closure#3}()

### Community 121 - "Input.vue"
Cohesion: 0.50
Nodes (3): emits, modelValue, props

### Community 122 - "Sheet.vue"
Cohesion: 0.50
Nodes (3): emits, forwarded, props

## Knowledge Gaps
- **546 isolated node(s):** `$schema`, `style`, `config`, `css`, `baseColor` (+541 more)
  These have ≤1 connection - possible missing edges or undocumented components. (Counts symbols only; 770 node(s) total have ≤1 connection when file, concept and rationale nodes are included.)
- **43 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `vue` connect `vue` to `reka-ui`, `SidebarProvider.vue`, `@vueuse/core`, `@lucide/vue`, `SelectContent.vue`, `Leads/Index.vue`, `Sidebar.vue`, `types/index.ts`, `TwoFactorSetupModal.vue`, `DialogContent.vue`, `DeleteUser.vue`, `package.json`, `resources_js_components_ui_button_index_button`, `lib/utils.ts`, `InputOTPSlot.vue`, `useAppearance.ts`, `Show.vue`, `Layout.vue`, `AppHeader.vue`, `UserMenuContent.vue`, `Badge.vue`, `global.d.ts`, `AlertError.vue`, `TwoFactorRecoveryCodes.vue`, `@inertiajs/vue3`, `button/index.ts`, `SheetContent.vue`, `useCurrentUrl.ts`, `SidebarMenuSkeleton.vue`, `Separator.vue`, `app.ts`, `Accounts/Index.vue`, `NavUser.vue`, `Input.vue`, `Login.vue`, `Label.vue`?**
  _High betweenness centrality (0.119) - this node is a cross-community bridge._
- **Why does `@inertiajs/vue3` connect `@inertiajs/vue3` to `Leads/Index.vue`, `types/index.ts`, `TwoFactorSetupModal.vue`, `DeleteUser.vue`, `package.json`, `resources_js_components_ui_button_index_button`, `lib/utils.ts`, `AppSidebar.vue`, `useAppearance.ts`, `Show.vue`, `Layout.vue`, `AppHeader.vue`, `UserMenuContent.vue`, `TwoFactorRecoveryCodes.vue`, `button/index.ts`, `useCurrentUrl.ts`, `app.ts`, `Accounts/Index.vue`, `NavUser.vue`, `Login.vue`?**
  _High betweenness centrality (0.049) - this node is a cross-community bridge._
- **Why does `reka-ui` connect `reka-ui` to `SidebarProvider.vue`, `@vueuse/core`, `@lucide/vue`, `SelectContent.vue`, `Sidebar.vue`, `DialogContent.vue`, `package.json`, `lib/utils.ts`, `vue`, `InputOTPSlot.vue`, `AppHeader.vue`, `DropdownMenu.vue`, `UserMenuContent.vue`, `Badge.vue`, `button/index.ts`, `Collapsible.vue`, `dialog/index.ts`, `SheetContent.vue`, `Tooltip.vue`, `Separator.vue`, `DropdownMenuRadioGroup.vue`, `Select.vue`, `Sheet.vue`, `Label.vue`?**
  _High betweenness centrality (0.048) - this node is a cross-community bridge._
- **What connects `$schema`, `style`, `config` to the rest of the system?**
  _546 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `Illuminate\Support\Facades\Schema` be split into smaller, more focused modules?**
  _Cohesion score 0.14166666666666666 - nodes in this community are weakly interconnected._
- **Should `reka-ui` be split into smaller, more focused modules?**
  _Cohesion score 0.06097560975609756 - nodes in this community are weakly interconnected._
- **Should `LeadController.php` be split into smaller, more focused modules?**
  _Cohesion score 0.1 - nodes in this community are weakly interconnected._