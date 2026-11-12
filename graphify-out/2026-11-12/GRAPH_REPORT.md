# Graph Report - marketing-leads  (2026-11-12)

## Corpus Check
- 292 files · ~54,859 words
- Verdict: corpus is large enough that graph structure adds value.
- Unclassified: 25 file(s) not represented in the graph (top: (none) 19, .example 2, .neon 1)

## Summary
- 1486 nodes · 2741 edges · 119 communities (77 shown, 42 thin omitted)
- Extraction: 100% EXTRACTED · 0% INFERRED · 0% AMBIGUOUS · INFERRED: 13 edges (avg confidence: 0.85)
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `f28c43fd`
- Run `git rev-parse HEAD` and compare to check if the graph is stale.
- Run `graphify update .` after code changes (no API cost).

## Community Hubs (Navigation)
- Illuminate\Database\Schema\Blueprint
- reka-ui
- LeadController.php
- SidebarProvider.vue
- @vueuse/core
- PasswordValidationRules
- Checkbox.vue
- @lucide/vue
- Leads/Index.vue
- User
- sheet/index.ts
- types/index.ts
- TwoFactorSetupModal.vue
- Illuminate\Http\Request
- dialog/index.ts
- DeleteUser.vue
- DashboardController
- TestCase
- package.json
- button/index.ts
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
- FortifyServiceProvider
- Badge.vue
- global.d.ts
- TwoFactorChallengeTest.php
- LeadManagementTest
- AlertError.vue
- @inertiajs/vue3
- SecurityTest.php
- composer.json
- require-dev
- Laravel\Fortify\Features
- Product
- AvatarFallback.vue
- devDependencies
- PasskeyRegister.vue
- bootstrap/app.php
- require
- DashboardController.php
- Collapsible.vue
- Dialog.vue
- SheetContent.vue
- useCurrentUrl.ts
- optionalDependencies
- SidebarMenuButton.vue
- Breadcrumbs.vue
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
- LoginResponse.php
- DropdownMenuRadioGroup.vue
- DropdownMenuSub.vue
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
- InputOTPGroup.vue
- Select.vue
- VerificationNotificationTest

## God Nodes (most connected - your core abstractions)
1. `vue` - 126 edges
2. `cn()` - 93 edges
3. `reka-ui` - 77 edges
4. `User` - 67 edges
5. `@vueuse/core` - 45 edges
6. `@inertiajs/vue3` - 44 edges
7. `@lucide/vue` - 37 edges
8. `LeadController` - 34 edges
9. `TestCase` - 32 edges
10. `LeadManagementTest` - 22 edges

## Surprising Connections (you probably didn't know these)
- `DashboardController` --inherits--> `Controller`  [EXTRACTED]
  app/Http/Controllers/DashboardController.php → app/Http/Controllers/Controller.php
- `LeadController` --inherits--> `Controller`  [EXTRACTED]
  app/Http/Controllers/LeadController.php → app/Http/Controllers/Controller.php
- `{closure#1}()` --calls--> `User`  [EXTRACTED]
  app/Providers/FortifyServiceProvider.php → app/Models/User.php
- `users_one_active_cs_per_branch` --indexes--> `users`  [EXTRACTED]
  database/postgres/004_crud_lifecycle.sql → database/postgres/001_schema.sql
- `leads_contact_key_unique` --indexes--> `leads`  [EXTRACTED]
  database/postgres/002_lead_contact_key.sql → database/postgres/001_schema.sql

## Import Cycles
- None detected.

## Communities (119 total, 42 thin omitted)

### Community 0 - "Illuminate\Database\Schema\Blueprint"
Cohesion: 0.07
Nodes (33): {closure#1}(), {closure#2}(), {closure#3}(), {closure#1}(), {closure#2}(), {closure#1}(), {closure#2}(), {closure#3}() (+25 more)

### Community 1 - "reka-ui"
Cohesion: 0.06
Nodes (31): reka-ui, delegatedProps, emits, forwarded, props, delegatedProps, emits, forwarded (+23 more)

### Community 2 - "LeadController.php"
Cohesion: 0.10
Nodes (12): {closure#10}(), {closure#11}(), {closure#13}(), {closure#17}(), {closure#18}(), {closure#19}(), {closure#6}(), {closure#8}() (+4 more)

### Community 3 - "SidebarProvider.vue"
Cohesion: 0.16
Nodes (16): emits, isMobile, open, openMobile, props, setOpen(), setOpenMobile(), state (+8 more)

### Community 4 - "@vueuse/core"
Cohesion: 0.05
Nodes (33): @vueuse/core, emits, modelValue, props, delegatedProps, props, navigationMenuTriggerStyle, delegatedProps (+25 more)

### Community 5 - "PasswordValidationRules"
Cohesion: 0.08
Nodes (17): CreateNewUser, ResetUserPassword, PasswordValidationRules, ProfileValidationRules, PasswordUpdateRequest, ProfileDeleteRequest, ProfileUpdateRequest, TwoFactorAuthenticationRequest (+9 more)

### Community 6 - "Checkbox.vue"
Cohesion: 0.33
Nodes (4): delegatedProps, emits, forwarded, props

### Community 7 - "@lucide/vue"
Cohesion: 0.05
Nodes (28): @lucide/vue, forwarded, props, resources_js_components_ui_select_index_selectscrolldownbutton, resources_js_components_ui_select_index_selectscrollupbutton, delegatedProps, emits, forwarded (+20 more)

### Community 8 - "Leads/Index.vue"
Cohesion: 0.06
Nodes (30): activeFilterText, allVisibleSelected, applyFilters(), archiveDialogOpen, archiveSelected(), branch, bulkForm, clearFilters() (+22 more)

### Community 9 - "User"
Cohesion: 0.11
Nodes (10): User, Illuminate\Database\Eloquent\Factories\HasFactory, Illuminate\Foundation\Auth\User, Illuminate\Notifications\Notifiable, Illuminate\Support\Carbon, Laravel\Fortify\Contracts\PasskeyUser, Laravel\Fortify\PasskeyAuthenticatable, Laravel\Fortify\TwoFactorAuthenticatable (+2 more)

### Community 10 - "sheet/index.ts"
Cohesion: 0.12
Nodes (10): emits, forwarded, props, props, delegatedProps, props, props, delegatedProps (+2 more)

### Community 11 - "types/index.ts"
Cohesion: 0.11
Nodes (17): vue-sonner, className, Props, Props, resources_js_components_ui_sidebar_index_sidebarinset, resources_js_components_ui_sidebar_index_sidebarprovider, resources_js_components_ui_sidebar_index_sidebartrigger, resources_js_components_ui_sonner_index_toaster (+9 more)

### Community 12 - "TwoFactorSetupModal.vue"
Cohesion: 0.10
Nodes (17): ref_routes_two_factor_login, code, { copy, copied }, isOpen, modalConfig, pinInputContainerRef, Props, { qrCodeSvg, manualSetupKey, clearSetupData, fetchSetupData, errors } (+9 more)

### Community 13 - "Illuminate\Http\Request"
Cohesion: 0.12
Nodes (11): {closure#1}(), {closure#10}(), {closure#11}(), {closure#2}(), {closure#3}(), {closure#4}(), {closure#5}(), {closure#9}() (+3 more)

### Community 14 - "dialog/index.ts"
Cohesion: 0.07
Nodes (20): props, delegatedProps, emits, forwarded, props, delegatedProps, forwardedProps, props (+12 more)

### Community 15 - "DeleteUser.vue"
Cohesion: 0.08
Nodes (30): passwordInput, emit, handleDelete(), isDeleting, props, resources_js_components_ui_dialog_index_dialog, resources_js_components_ui_dialog_index_dialogclose, resources_js_components_ui_dialog_index_dialogcontent (+22 more)

### Community 17 - "TestCase"
Cohesion: 0.11
Nodes (9): Illuminate\Foundation\Testing\RefreshDatabase, Illuminate\Foundation\Testing\TestCase, {closure#1}(), PasswordConfirmationTest, RegistrationTest, ExampleTest, TrustedProxyTest, TestCase (+1 more)

### Community 18 - "package.json"
Cohesion: 0.09
Nodes (21): private, $schema, type, clsx, concurrently, @inertiajs/vite, @laravel/multiplex, @laravel/vite-plugin-wayfinder (+13 more)

### Community 19 - "button/index.ts"
Cohesion: 0.11
Nodes (25): ref_actions_laravel_passkeys_http_controllers_passkeyconfirmationcontroller, ref_inertiajs_core, ref_routes_register, ref_routes_verification, resources_js_actions_app_http_controllers_settings_profilecontroller, Props, { verify, isLoading, error, isSupported }, inputRef (+17 more)

### Community 20 - "lib/utils.ts"
Cohesion: 0.06
Nodes (35): props, props, props, resources_js_components_ui_sheet_index_sheet, resources_js_components_ui_sheet_index_sheetcontent, props, SidebarMenuButtonVariants, SidebarProps (+27 more)

### Community 21 - "vue"
Cohesion: 0.07
Nodes (18): vue, props, props, props, props, props, props, props (+10 more)

### Community 22 - "compilerOptions"
Cohesion: 0.09
Nodes (21): compilerOptions, allowJs, esModuleInterop, forceConsistentCasingInFileNames, isolatedModules, jsx, jsxImportSource, lib (+13 more)

### Community 23 - "AppSidebar.vue"
Cohesion: 0.16
Nodes (17): footerNavItems, mainNavItems, Props, { isCurrentUrl }, resources_js_components_ui_sidebar_index_sidebar, resources_js_components_ui_sidebar_index_sidebarcontent, resources_js_components_ui_sidebar_index_sidebarfooter, resources_js_components_ui_sidebar_index_sidebargroup (+9 more)

### Community 24 - "LeadController"
Cohesion: 0.22
Nodes (4): {closure#1}(), {closure#3}(), {closure#5}(), LeadController

### Community 27 - "InputOTPSlot.vue"
Cohesion: 0.17
Nodes (10): vue-input-otp, delegatedProps, emits, forwarded, props, context, delegatedProps, forwarded (+2 more)

### Community 28 - "web.php"
Cohesion: 0.22
Nodes (6): EnsureAdmin, HandleAppearance, Closure, Illuminate\Support\Facades\Route, Illuminate\Support\Facades\View, Symfony\Component\HttpFoundation\Response

### Community 29 - "leads"
Cohesion: 0.19
Nodes (14): branches, lead_activity, lead_activity_lead_created_idx, lead_milestones, leads, leads_assigned_user_idx, leads_updated_at_idx, users (+6 more)

### Community 30 - "useAppearance.ts"
Cohesion: 0.09
Nodes (24): pages, { appearance, updateAppearance }, tabs, appearance, getStoredAppearance(), handleSystemThemeChange(), initializeTheme(), mediaQuery() (+16 more)

### Community 31 - "Show.vue"
Cohesion: 0.10
Nodes (15): zod, leadSchema, archiveDialogOpen, clientError, deleteDialogOpen, focusFirstError(), form, formFields (+7 more)

### Community 32 - "ProfileController.php"
Cohesion: 0.11
Nodes (10): Controller, ProfileController, SecurityController, LogoutResponse, Illuminate\Auth\Middleware\RequirePassword, Illuminate\Contracts\Auth\MustVerifyEmail, Illuminate\Http\JsonResponse, Illuminate\Support\Facades\Auth (+2 more)

### Community 33 - "Layout.vue"
Cohesion: 0.14
Nodes (14): resources_js_actions_app_http_controllers_settings_securitycontroller, Props, { hasSetupData, clearTwoFactorAuthData }, Props, showSetupModal, resources_js_components_ui_separator_index_separator, { isCurrentOrParentUrl }, sidebarNavItems (+6 more)

### Community 34 - "KGJ Leads"
Cohesion: 0.12
Nodes (15): Auto deploy, Deploy manual, Migrasi dan backup, Panduan Deployment KGJ Leads, Production VPS 1, Admin, Alur kerja harian, Bantuan (+7 more)

### Community 35 - "dependencies"
Cohesion: 0.12
Nodes (17): dependencies, class-variance-authority, clsx, @inertiajs/vite, @inertiajs/vue3, @laravel/passkeys, laravel-vite-plugin, @lucide/vue (+9 more)

### Community 36 - "AppHeader.vue"
Cohesion: 0.10
Nodes (20): auth, { isCurrentUrl, whenCurrentUrl }, mainNavItems, page, Props, rightNavItems, { isMobile, state }, page (+12 more)

### Community 37 - "DropdownMenu.vue"
Cohesion: 0.50
Nodes (3): emits, forwarded, props

### Community 38 - "aliases"
Cohesion: 0.12
Nodes (15): aliases, components, composables, lib, ui, utils, iconLibrary, $schema (+7 more)

### Community 39 - "UserMenuContent.vue"
Cohesion: 0.11
Nodes (18): resources_js_components_ui_avatar_index_avatar, resources_js_components_ui_avatar_index_avatarfallback, resources_js_components_ui_avatar_index_avatarimage, resources_js_components_ui_dropdown_menu_index_dropdownmenugroup, resources_js_components_ui_dropdown_menu_index_dropdownmenuitem, resources_js_components_ui_dropdown_menu_index_dropdownmenulabel, resources_js_components_ui_dropdown_menu_index_dropdownmenuseparator, { getInitials } (+10 more)

### Community 40 - "scripts"
Cohesion: 0.14
Nodes (14): scripts, ci:check, dev, lint, lint:check, post-autoload-dump, post-create-project-cmd, post-root-package-install (+6 more)

### Community 41 - "FortifyServiceProvider"
Cohesion: 0.07
Nodes (16): BootstrapAdmin, AppServiceProvider, {closure#1}(), FortifyServiceProvider, Carbon\CarbonImmutable, UserFactory, Illuminate\Console\Command, Illuminate\Database\Eloquent\Factories\Factory (+8 more)

### Community 42 - "Badge.vue"
Cohesion: 0.24
Nodes (6): class-variance-authority, props, AlertVariants, delegatedProps, props, BadgeVariants

### Community 43 - "global.d.ts"
Cohesion: 0.22
Nodes (9): Auth, ComponentCustomProperties, GlobalDirectives, ImportMeta, ImportMetaEnv, InertiaConfig, @inertiajs/core, vite/client (+1 more)

### Community 46 - "AlertError.vue"
Cohesion: 0.33
Nodes (5): Props, uniqueErrors, resources_js_components_ui_alert_index_alert, resources_js_components_ui_alert_index_alertdescription, resources_js_components_ui_alert_index_alerttitle

### Community 47 - "@inertiajs/vue3"
Cohesion: 0.09
Nodes (24): @inertiajs/vue3, ref_routes_two_factor, Props, isRecoveryCodesVisible, recoveryCodeSectionRef, { recoveryCodesList, fetchRecoveryCodes, errors }, resources_js_components_ui_card_index_card, resources_js_components_ui_card_index_cardcontent (+16 more)

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

### Community 53 - "AvatarFallback.vue"
Cohesion: 0.25
Nodes (4): props, delegatedProps, props, props

### Community 54 - "devDependencies"
Cohesion: 0.20
Nodes (10): devDependencies, concurrently, @laravel/vite-plugin-wayfinder, @tailwindcss/vite, @types/node, typescript, vite, vite-plus (+2 more)

### Community 55 - "PasskeyRegister.vue"
Cohesion: 0.13
Nodes (9): ref_actions_laravel_passkeys_http_controllers_passkeyregistrationcontroller, @laravel/passkeys, Props, emit, name, { register, isLoading, error, isSupported }, showForm, Passkey (+1 more)

### Community 56 - "bootstrap/app.php"
Cohesion: 0.16
Nodes (9): HandleInertiaRequests, {closure#1}(), {closure#2}(), {closure#3}(), Illuminate\Foundation\Application, Illuminate\Foundation\Configuration\Exceptions, Illuminate\Foundation\Configuration\Middleware, Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets (+1 more)

### Community 57 - "require"
Cohesion: 0.22
Nodes (9): require, inertiajs/inertia-laravel, laravel/chisel, laravel/fortify, laravel/framework, laravel/tinker, laravel/wayfinder, openspout/openspout (+1 more)

### Community 58 - "DashboardController.php"
Cohesion: 0.24
Nodes (4): {closure#2}(), {closure#5}(), Illuminate\Database\Query\Builder, Illuminate\Validation\ValidationException

### Community 59 - "Collapsible.vue"
Cohesion: 0.22
Nodes (5): emits, forwarded, props, props, props

### Community 60 - "Dialog.vue"
Cohesion: 0.50
Nodes (3): emits, forwarded, props

### Community 61 - "SheetContent.vue"
Cohesion: 0.22
Nodes (7): delegatedProps, emits, forwarded, props, SheetContentProps, delegatedProps, props

### Community 62 - "useCurrentUrl.ts"
Cohesion: 0.33
Nodes (8): currentUrlReactive, page, useCurrentUrl(), isCurrentOrParentUrl(), isCurrentUrl(), whenCurrentUrl(), UseCurrentUrlReturn, toUrl()

### Community 63 - "optionalDependencies"
Cohesion: 0.25
Nodes (8): optionalDependencies, @laravel/multiplex, lightningcss-linux-x64-gnu, lightningcss-win32-x64-msvc, @rollup/rollup-linux-x64-gnu, @rollup/rollup-win32-x64-msvc, @tailwindcss/oxide-linux-x64-gnu, @tailwindcss/oxide-win32-x64-msvc

### Community 64 - "SidebarMenuButton.vue"
Cohesion: 0.12
Nodes (11): delegatedProps, { isMobile, state }, props, resources_js_components_ui_tooltip_index_tooltip, resources_js_components_ui_tooltip_index_tooltipcontent, resources_js_components_ui_tooltip_index_tooltiptrigger, emits, forwarded (+3 more)

### Community 65 - "Breadcrumbs.vue"
Cohesion: 0.25
Nodes (7): Props, resources_js_components_ui_breadcrumb_index_breadcrumb, resources_js_components_ui_breadcrumb_index_breadcrumbitem, resources_js_components_ui_breadcrumb_index_breadcrumblink, resources_js_components_ui_breadcrumb_index_breadcrumblist, resources_js_components_ui_breadcrumb_index_breadcrumbpage, resources_js_components_ui_breadcrumb_index_breadcrumbseparator

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

### Community 79 - "LoginResponse.php"
Cohesion: 0.50
Nodes (3): LoginResponse, Laravel\Fortify\Contracts\LoginResponse, Laravel\Fortify\Fortify

### Community 80 - "DropdownMenuRadioGroup.vue"
Cohesion: 0.50
Nodes (3): emits, forwarded, props

### Community 81 - "DropdownMenuSub.vue"
Cohesion: 0.50
Nodes (3): emits, forwarded, props

### Community 115 - "TooltipContent.vue"
Cohesion: 0.40
Nodes (4): delegatedProps, emits, forwarded, props

### Community 116 - "InputOTPGroup.vue"
Cohesion: 0.50
Nodes (3): delegatedProps, forwarded, props

### Community 117 - "Select.vue"
Cohesion: 0.50
Nodes (3): emits, forwarded, props

## Knowledge Gaps
- **546 isolated node(s):** `$schema`, `style`, `config`, `css`, `baseColor` (+541 more)
  These have ≤1 connection - possible missing edges or undocumented components. (Counts symbols only; 769 node(s) total have ≤1 connection when file, concept and rationale nodes are included.)
- **42 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `vue` connect `vue` to `reka-ui`, `SidebarProvider.vue`, `@vueuse/core`, `Checkbox.vue`, `@lucide/vue`, `Leads/Index.vue`, `sheet/index.ts`, `types/index.ts`, `TwoFactorSetupModal.vue`, `dialog/index.ts`, `DeleteUser.vue`, `package.json`, `button/index.ts`, `lib/utils.ts`, `InputOTPSlot.vue`, `useAppearance.ts`, `Show.vue`, `Layout.vue`, `AppHeader.vue`, `UserMenuContent.vue`, `Badge.vue`, `global.d.ts`, `AlertError.vue`, `@inertiajs/vue3`, `AvatarFallback.vue`, `PasskeyRegister.vue`, `SheetContent.vue`, `useCurrentUrl.ts`, `SidebarMenuButton.vue`, `SidebarMenuSkeleton.vue`, `Separator.vue`, `TooltipContent.vue`, `InputOTPGroup.vue`?**
  _High betweenness centrality (0.131) - this node is a cross-community bridge._
- **Why does `reka-ui` connect `reka-ui` to `SidebarProvider.vue`, `@vueuse/core`, `Checkbox.vue`, `@lucide/vue`, `sheet/index.ts`, `dialog/index.ts`, `package.json`, `lib/utils.ts`, `vue`, `InputOTPSlot.vue`, `DropdownMenu.vue`, `Badge.vue`, `AvatarFallback.vue`, `Collapsible.vue`, `Dialog.vue`, `SheetContent.vue`, `SidebarMenuButton.vue`, `Separator.vue`, `DropdownMenuRadioGroup.vue`, `DropdownMenuSub.vue`, `TooltipContent.vue`, `InputOTPGroup.vue`, `Select.vue`?**
  _High betweenness centrality (0.044) - this node is a cross-community bridge._
- **Why does `@inertiajs/vue3` connect `@inertiajs/vue3` to `Breadcrumbs.vue`, `Layout.vue`, `AppHeader.vue`, `UserMenuContent.vue`, `Leads/Index.vue`, `types/index.ts`, `TwoFactorSetupModal.vue`, `DeleteUser.vue`, `package.json`, `button/index.ts`, `lib/utils.ts`, `PasskeyRegister.vue`, `AppSidebar.vue`, `useCurrentUrl.ts`, `useAppearance.ts`, `Show.vue`?**
  _High betweenness centrality (0.041) - this node is a cross-community bridge._
- **What connects `$schema`, `style`, `config` to the rest of the system?**
  _546 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `Illuminate\Database\Schema\Blueprint` be split into smaller, more focused modules?**
  _Cohesion score 0.07020408163265306 - nodes in this community are weakly interconnected._
- **Should `reka-ui` be split into smaller, more focused modules?**
  _Cohesion score 0.059233449477351915 - nodes in this community are weakly interconnected._
- **Should `LeadController.php` be split into smaller, more focused modules?**
  _Cohesion score 0.1 - nodes in this community are weakly interconnected._