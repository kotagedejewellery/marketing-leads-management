# Graph Report - marketing-leads  (2026-11-11)

## Corpus Check
- 288 files · ~50,742 words
- Verdict: corpus is large enough that graph structure adds value.
- Unclassified: 24 file(s) not represented in the graph (top: (none) 19, .example 1, .neon 1)

## Summary
- 1395 nodes · 2578 edges · 97 communities (77 shown, 20 thin omitted)
- Extraction: 100% EXTRACTED · 0% INFERRED · 0% AMBIGUOUS · INFERRED: 10 edges (avg confidence: 0.85)
- Token cost: 0 input · 0 output

## Community Hubs (Navigation)
- vue
- reka-ui
- Illuminate\Database\Schema\Blueprint
- SidebarProvider.vue
- @vueuse/core
- LeadController.php
- PasswordValidationRules
- SelectContent.vue
- User
- Checkbox.vue
- @inertiajs/vue3
- types/index.ts
- TwoFactorSetupModal.vue
- Illuminate\Http\Request
- TestCase
- DeleteUser.vue
- ProfileController.php
- package.json
- compilerOptions
- button/index.ts
- sidebar/index.ts
- Layout.vue
- Leads/Index.vue
- AppSidebar.vue
- ResetPassword.vue
- FortifyServiceProvider
- ManageTwoFactor.vue
- dependencies
- Show.vue
- sheet/index.ts
- useAppearance.ts
- DashboardController.php
- aliases
- PasskeyRegister.vue
- BootstrapAdmin.php
- UserFactory.php
- leads
- AppHeader.vue
- Badge.vue
- tooltip/index.ts
- global.d.ts
- settings.php
- scripts
- SecurityTest.php
- Branches/Index.vue
- composer.json
- require-dev
- InputOTPSlot.vue
- PasswordResetTest
- Product
- Accounts/Index.vue
- devDependencies
- LeadManagementTest
- bootstrap/app.php
- require
- AuthenticationTest
- Collapsible.vue
- dialog/index.ts
- SheetContent.vue
- useCurrentUrl.ts
- optionalDependencies
- AlertError.vue
- DashboardTest
- DialogContent.vue
- LogoutResponse.php
- @lucide/vue
- SidebarMenuSkeleton.vue
- config
- VerificationNotificationTest
- scripts
- Separator.vue
- DialogScrollContent.vue
- psr-4
- laravel
- logging.php
- DatabaseSeeder.php
- Design
- vite.config.ts
- DropdownMenuSubContent.vue
- Laravel\Fortify\Features
- console.php
- DropdownMenuCheckboxItem.vue
- DialogDescription.vue
- artisan
- GLOSSARY.md
- overrides
- README.md

## God Nodes (most connected - your core abstractions)
1. `vue` - 126 edges
2. `cn()` - 93 edges
3. `reka-ui` - 77 edges
4. `User` - 62 edges
5. `@vueuse/core` - 45 edges
6. `@inertiajs/vue3` - 44 edges
7. `@lucide/vue` - 37 edges
8. `TestCase` - 30 edges
9. `LeadController` - 24 edges
10. `compilerOptions` - 19 edges

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

## Communities (97 total, 20 thin omitted)

### Community 0 - "vue"
Cohesion: 0.06
Nodes (35): vue, props, props, props, props, props, props, props (+27 more)

### Community 1 - "reka-ui"
Cohesion: 0.06
Nodes (32): reka-ui, emits, forwarded, props, delegatedProps, emits, forwarded, props (+24 more)

### Community 2 - "Illuminate\Database\Schema\Blueprint"
Cohesion: 0.08
Nodes (26): {closure#1}(), {closure#2}(), {closure#3}(), {closure#1}(), {closure#2}(), {closure#1}(), {closure#2}(), {closure#3}() (+18 more)

### Community 3 - "SidebarProvider.vue"
Cohesion: 0.16
Nodes (16): emits, isMobile, open, openMobile, props, setOpen(), setOpenMobile(), state (+8 more)

### Community 4 - "@vueuse/core"
Cohesion: 0.05
Nodes (33): @vueuse/core, delegatedProps, props, delegatedProps, forwardedProps, props, navigationMenuTriggerStyle, delegatedProps (+25 more)

### Community 5 - "LeadController.php"
Cohesion: 0.09
Nodes (14): {closure#1}(), {closure#10}(), {closure#11}(), {closure#12}(), {closure#13}(), {closure#2}(), {closure#4}(), {closure#6}() (+6 more)

### Community 6 - "PasswordValidationRules"
Cohesion: 0.10
Nodes (13): CreateNewUser, ResetUserPassword, PasswordValidationRules, ProfileValidationRules, PasswordUpdateRequest, ProfileUpdateRequest, TwoFactorAuthenticationRequest, UpdateLeadRequest (+5 more)

### Community 7 - "SelectContent.vue"
Cohesion: 0.05
Nodes (27): resources_js_components_ui_select_index_selectscrolldownbutton, resources_js_components_ui_select_index_selectscrollupbutton, emits, forwarded, props, delegatedProps, emits, forwarded (+19 more)

### Community 8 - "User"
Cohesion: 0.10
Nodes (7): User, Illuminate\Database\Eloquent\Factories\HasFactory, Illuminate\Foundation\Auth\User, Illuminate\Notifications\Notifiable, EmailVerificationTest, ProfileUpdateTest, SecurityTest

### Community 9 - "Checkbox.vue"
Cohesion: 0.16
Nodes (8): delegatedProps, emits, forwarded, props, delegatedProps, emits, forwarded, props

### Community 10 - "@inertiajs/vue3"
Cohesion: 0.11
Nodes (18): @inertiajs/vue3, Props, isRecoveryCodesVisible, recoveryCodeSectionRef, { recoveryCodesList, fetchRecoveryCodes, errors }, resources_js_components_ui_card_index_card, resources_js_components_ui_card_index_cardcontent, resources_js_components_ui_card_index_carddescription (+10 more)

### Community 11 - "types/index.ts"
Cohesion: 0.11
Nodes (20): className, Props, Props, Props, resources_js_components_ui_breadcrumb_index_breadcrumb, resources_js_components_ui_breadcrumb_index_breadcrumbitem, resources_js_components_ui_breadcrumb_index_breadcrumblink, resources_js_components_ui_breadcrumb_index_breadcrumblist (+12 more)

### Community 12 - "TwoFactorSetupModal.vue"
Cohesion: 0.09
Nodes (19): code, { copy, copied }, isOpen, modalConfig, pinInputContainerRef, Props, { qrCodeSvg, manualSetupKey, clearSetupData, fetchSetupData, errors }, { resolvedAppearance } (+11 more)

### Community 13 - "Illuminate\Http\Request"
Cohesion: 0.11
Nodes (15): DashboardController, LoginResponse, {closure#1}(), {closure#10}(), {closure#11}(), {closure#2}(), {closure#3}(), {closure#4}() (+7 more)

### Community 14 - "TestCase"
Cohesion: 0.14
Nodes (7): Illuminate\Foundation\Testing\RefreshDatabase, Illuminate\Foundation\Testing\TestCase, PasswordConfirmationTest, RegistrationTest, ExampleTest, TestCase, ExampleTest

### Community 15 - "DeleteUser.vue"
Cohesion: 0.15
Nodes (17): passwordInput, emit, handleDelete(), isDeleting, props, resources_js_components_ui_dialog_index_dialog, resources_js_components_ui_dialog_index_dialogclose, resources_js_components_ui_dialog_index_dialogcontent (+9 more)

### Community 16 - "ProfileController.php"
Cohesion: 0.16
Nodes (8): Controller, ProfileController, SecurityController, ProfileDeleteRequest, Illuminate\Contracts\Auth\MustVerifyEmail, Illuminate\Http\RedirectResponse, Illuminate\Support\Facades\Auth, Inertia\Inertia

### Community 17 - "package.json"
Cohesion: 0.09
Nodes (21): private, $schema, type, clsx, concurrently, @inertiajs/vite, @laravel/multiplex, @laravel/vite-plugin-wayfinder (+13 more)

### Community 18 - "compilerOptions"
Cohesion: 0.09
Nodes (21): compilerOptions, allowJs, esModuleInterop, forceConsistentCasingInFileNames, isolatedModules, jsx, jsxImportSource, lib (+13 more)

### Community 19 - "button/index.ts"
Cohesion: 0.10
Nodes (22): ref_inertiajs_core, resources_js_actions_laravel_passkeys_http_controllers_passkeyconfirmationcontroller, resources_js_actions_laravel_passkeys_http_controllers_passkeyconfirmationcontroller_index, resources_js_actions_laravel_passkeys_http_controllers_passkeyconfirmationcontroller_store, Props, { verify, isLoading, error, isSupported }, Props, Props (+14 more)

### Community 20 - "sidebar/index.ts"
Cohesion: 0.10
Nodes (20): resources_js_components_ui_sheet_index_sheet, resources_js_components_ui_sheet_index_sheetcontent, SidebarMenuButtonVariants, SidebarProps, { isMobile, state, openMobile, setOpenMobile }, props, delegatedProps, { isMobile, state } (+12 more)

### Community 21 - "Layout.vue"
Cohesion: 0.10
Nodes (18): resources_js_actions_app_http_controllers_settings_profilecontroller, resources_js_actions_app_http_controllers_settings_securitycontroller, resources_js_actions_laravel_passkeys_http_controllers_passkeyregistrationcontroller, resources_js_actions_laravel_passkeys_http_controllers_passkeyregistrationcontroller_destroy, Props, Props, Props, { isCurrentOrParentUrl } (+10 more)

### Community 22 - "Leads/Index.vue"
Cohesion: 0.08
Nodes (19): activeFilterText, applyFilters(), branch, bulkForm, clearFilters(), hasFilters, importForm, isAdmin (+11 more)

### Community 23 - "AppSidebar.vue"
Cohesion: 0.11
Nodes (23): footerNavItems, mainNavItems, Props, { isCurrentUrl }, { isMobile, state }, page, user, resources_js_components_ui_dropdown_menu_index_dropdownmenu (+15 more)

### Community 24 - "ResetPassword.vue"
Cohesion: 0.14
Nodes (12): inputRef, props, showPassword, resources_js_components_ui_input_index_input, emits, modelValue, props, props (+4 more)

### Community 25 - "FortifyServiceProvider"
Cohesion: 0.17
Nodes (7): AppServiceProvider, {closure#1}(), FortifyServiceProvider, Carbon\CarbonImmutable, Illuminate\Support\Facades\Date, Illuminate\Support\ServiceProvider, Illuminate\Validation\Rules\Password

### Community 26 - "ManageTwoFactor.vue"
Cohesion: 0.13
Nodes (15): { hasSetupData, clearTwoFactorAuthData }, showSetupModal, errors, hasSetupData, manualSetupKey, qrCodeSvg, recoveryCodesList, useTwoFactorAuth() (+7 more)

### Community 27 - "dependencies"
Cohesion: 0.12
Nodes (17): dependencies, class-variance-authority, clsx, @inertiajs/vite, @inertiajs/vue3, @laravel/passkeys, laravel-vite-plugin, @lucide/vue (+9 more)

### Community 28 - "Show.vue"
Cohesion: 0.12
Nodes (13): zod, leadSchema, archiveDialogOpen, clientError, focusFirstError(), form, formFields, groups (+5 more)

### Community 29 - "sheet/index.ts"
Cohesion: 0.11
Nodes (11): emits, forwarded, props, props, delegatedProps, props, props, props (+3 more)

### Community 30 - "useAppearance.ts"
Cohesion: 0.09
Nodes (24): pages, { appearance, updateAppearance }, tabs, appearance, getStoredAppearance(), handleSystemThemeChange(), initializeTheme(), mediaQuery() (+16 more)

### Community 31 - "DashboardController.php"
Cohesion: 0.25
Nodes (3): Illuminate\Validation\Rule, Illuminate\Validation\Rules\Unique, Illuminate\Validation\ValidationException

### Community 32 - "aliases"
Cohesion: 0.12
Nodes (15): aliases, components, composables, lib, ui, utils, iconLibrary, $schema (+7 more)

### Community 33 - "PasskeyRegister.vue"
Cohesion: 0.17
Nodes (7): @laravel/passkeys, emit, name, { register, isLoading, error, isSupported }, showForm, delegatedProps, props

### Community 34 - "BootstrapAdmin.php"
Cohesion: 0.20
Nodes (6): BootstrapAdmin, HandleInertiaRequests, Illuminate\Console\Command, Illuminate\Support\Facades\DB, Illuminate\Support\Facades\Validator, Inertia\Middleware

### Community 35 - "UserFactory.php"
Cohesion: 0.16
Nodes (6): UserFactory, Illuminate\Database\Eloquent\Factories\Factory, Illuminate\Support\Facades\Hash, Illuminate\Support\Str, Pdo\Mysql, static

### Community 36 - "leads"
Cohesion: 0.19
Nodes (14): branches, lead_activity, lead_activity_lead_created_idx, lead_milestones, leads, leads_assigned_user_idx, leads_updated_at_idx, users (+6 more)

### Community 37 - "AppHeader.vue"
Cohesion: 0.09
Nodes (24): auth, { isCurrentUrl, whenCurrentUrl }, mainNavItems, page, Props, rightNavItems, props, resources_js_components_ui_avatar_index_avatar (+16 more)

### Community 38 - "Badge.vue"
Cohesion: 0.16
Nodes (8): class-variance-authority, props, props, props, AlertVariants, delegatedProps, props, BadgeVariants

### Community 39 - "tooltip/index.ts"
Cohesion: 0.22
Nodes (5): emits, forwarded, props, props, props

### Community 40 - "global.d.ts"
Cohesion: 0.16
Nodes (12): Auth, Passkey, TwoFactorConfigContent, User, ComponentCustomProperties, GlobalDirectives, ImportMeta, ImportMetaEnv (+4 more)

### Community 41 - "settings.php"
Cohesion: 0.14
Nodes (7): EnsureAdmin, HandleAppearance, Closure, Illuminate\Auth\Middleware\RequirePassword, Illuminate\Support\Facades\Route, Illuminate\Support\Facades\View, Symfony\Component\HttpFoundation\Response

### Community 42 - "scripts"
Cohesion: 0.15
Nodes (13): scripts, ci:check, dev, lint, lint:check, post-autoload-dump, post-create-project-cmd, post-root-package-install (+5 more)

### Community 43 - "SecurityTest.php"
Cohesion: 0.23
Nodes (6): Inertia\Testing\AssertableInertia, {closure#1}(), {closure#1}(), TwoFactorChallengeTest, {closure#1}(), {closure#2}()

### Community 44 - "Branches/Index.vue"
Cohesion: 0.20
Nodes (5): resources_js_components_ui_dialog_index_dialogfooter, Branch, branchToDelete, editingId, form

### Community 45 - "composer.json"
Cohesion: 0.17
Nodes (11): autoload-dev, psr-4, description, keywords, license, minimum-stability, name, prefer-stable (+3 more)

### Community 46 - "require-dev"
Cohesion: 0.17
Nodes (12): require-dev, fakerphp/faker, larastan/larastan, laravel/pail, laravel/pao, laravel/pint, laravel/sail, mockery/mockery (+4 more)

### Community 47 - "InputOTPSlot.vue"
Cohesion: 0.11
Nodes (15): vue-input-otp, delegatedProps, emits, forwarded, props, delegatedProps, forwarded, props (+7 more)

### Community 49 - "Product"
Cohesion: 0.18
Nodes (10): Capabilities and Constraints, Evidence on Hand, Operating Context, Platform, Positioning, Product, Product Principles, Product Purpose (+2 more)

### Community 50 - "Accounts/Index.vue"
Cohesion: 0.20
Nodes (8): Account, accountToToggle, cancel(), editingId, form, needsBranch, props, save()

### Community 51 - "devDependencies"
Cohesion: 0.20
Nodes (10): devDependencies, concurrently, @laravel/vite-plugin-wayfinder, @tailwindcss/vite, @types/node, typescript, vite, vite-plus (+2 more)

### Community 53 - "bootstrap/app.php"
Cohesion: 0.28
Nodes (7): {closure#1}(), {closure#2}(), {closure#3}(), Illuminate\Foundation\Application, Illuminate\Foundation\Configuration\Exceptions, Illuminate\Foundation\Configuration\Middleware, Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets

### Community 54 - "require"
Cohesion: 0.22
Nodes (9): require, inertiajs/inertia-laravel, laravel/chisel, laravel/fortify, laravel/framework, laravel/tinker, laravel/wayfinder, openspout/openspout (+1 more)

### Community 56 - "Collapsible.vue"
Cohesion: 0.22
Nodes (5): emits, forwarded, props, props, props

### Community 57 - "dialog/index.ts"
Cohesion: 0.22
Nodes (5): emits, forwarded, props, props, props

### Community 58 - "SheetContent.vue"
Cohesion: 0.22
Nodes (7): delegatedProps, emits, forwarded, props, SheetContentProps, delegatedProps, props

### Community 59 - "useCurrentUrl.ts"
Cohesion: 0.33
Nodes (8): currentUrlReactive, page, useCurrentUrl(), isCurrentOrParentUrl(), isCurrentUrl(), whenCurrentUrl(), UseCurrentUrlReturn, toUrl()

### Community 60 - "optionalDependencies"
Cohesion: 0.25
Nodes (8): optionalDependencies, @laravel/multiplex, lightningcss-linux-x64-gnu, lightningcss-win32-x64-msvc, @rollup/rollup-linux-x64-gnu, @rollup/rollup-win32-x64-msvc, @tailwindcss/oxide-linux-x64-gnu, @tailwindcss/oxide-win32-x64-msvc

### Community 61 - "AlertError.vue"
Cohesion: 0.33
Nodes (5): Props, uniqueErrors, resources_js_components_ui_alert_index_alert, resources_js_components_ui_alert_index_alertdescription, resources_js_components_ui_alert_index_alerttitle

### Community 63 - "DialogContent.vue"
Cohesion: 0.25
Nodes (6): delegatedProps, emits, forwarded, props, delegatedProps, props

### Community 64 - "LogoutResponse.php"
Cohesion: 0.50
Nodes (3): LogoutResponse, Illuminate\Http\JsonResponse, Laravel\Fortify\Contracts\LogoutResponse

### Community 65 - "@lucide/vue"
Cohesion: 0.12
Nodes (11): @lucide/vue, vue-sonner, resources_js_components_ui_dropdown_menu_index_dropdownmenugroup, resources_js_components_ui_dropdown_menu_index_dropdownmenuitem, resources_js_components_ui_dropdown_menu_index_dropdownmenulabel, resources_js_components_ui_dropdown_menu_index_dropdownmenuseparator, props, Props (+3 more)

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
Cohesion: 0.29
Nodes (4): resources_js_components_ui_separator_index_separator, delegatedProps, props, props

### Community 71 - "DialogScrollContent.vue"
Cohesion: 0.40
Nodes (4): delegatedProps, emits, forwarded, props

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

### Community 78 - "DropdownMenuSubContent.vue"
Cohesion: 0.40
Nodes (4): delegatedProps, emits, forwarded, props

### Community 79 - "Laravel\Fortify\Features"
Cohesion: 0.17
Nodes (7): Illuminate\Auth\Events\Verified, Illuminate\Auth\Notifications\ResetPassword, Illuminate\Auth\Notifications\VerifyEmail, Illuminate\Support\Facades\Event, Illuminate\Support\Facades\Notification, Illuminate\Support\Facades\URL, Laravel\Fortify\Features

### Community 81 - "DropdownMenuCheckboxItem.vue"
Cohesion: 0.40
Nodes (4): delegatedProps, emits, forwarded, props

### Community 82 - "DialogDescription.vue"
Cohesion: 0.50
Nodes (3): delegatedProps, forwardedProps, props

## Knowledge Gaps
- **523 isolated node(s):** `$schema`, `style`, `config`, `css`, `baseColor` (+518 more)
  These have ≤1 connection - possible missing edges or undocumented components. (Counts symbols only; 730 node(s) total have ≤1 connection when file, concept and rationale nodes are included.)
- **20 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `vue` connect `vue` to `reka-ui`, `SidebarProvider.vue`, `@vueuse/core`, `SelectContent.vue`, `Checkbox.vue`, `@inertiajs/vue3`, `types/index.ts`, `TwoFactorSetupModal.vue`, `DeleteUser.vue`, `package.json`, `button/index.ts`, `sidebar/index.ts`, `Layout.vue`, `Leads/Index.vue`, `AppSidebar.vue`, `ResetPassword.vue`, `ManageTwoFactor.vue`, `Show.vue`, `sheet/index.ts`, `useAppearance.ts`, `PasskeyRegister.vue`, `AppHeader.vue`, `Badge.vue`, `global.d.ts`, `Branches/Index.vue`, `InputOTPSlot.vue`, `Accounts/Index.vue`, `SheetContent.vue`, `useCurrentUrl.ts`, `AlertError.vue`, `DialogContent.vue`, `@lucide/vue`, `SidebarMenuSkeleton.vue`, `Separator.vue`, `DialogScrollContent.vue`, `DropdownMenuSubContent.vue`, `DropdownMenuCheckboxItem.vue`, `DialogDescription.vue`?**
  _High betweenness centrality (0.154) - this node is a cross-community bridge._
- **Why does `reka-ui` connect `reka-ui` to `vue`, `SidebarProvider.vue`, `@vueuse/core`, `SelectContent.vue`, `Checkbox.vue`, `package.json`, `button/index.ts`, `sidebar/index.ts`, `sheet/index.ts`, `PasskeyRegister.vue`, `AppHeader.vue`, `Badge.vue`, `tooltip/index.ts`, `InputOTPSlot.vue`, `Collapsible.vue`, `dialog/index.ts`, `SheetContent.vue`, `DialogContent.vue`, `Separator.vue`, `DialogScrollContent.vue`, `DropdownMenuSubContent.vue`, `DropdownMenuCheckboxItem.vue`, `DialogDescription.vue`?**
  _High betweenness centrality (0.077) - this node is a cross-community bridge._
- **Why does `@inertiajs/vue3` connect `@inertiajs/vue3` to `vue`, `@lucide/vue`, `AppHeader.vue`, `types/index.ts`, `TwoFactorSetupModal.vue`, `Branches/Index.vue`, `DeleteUser.vue`, `package.json`, `Accounts/Index.vue`, `button/index.ts`, `Layout.vue`, `Leads/Index.vue`, `AppSidebar.vue`, `ResetPassword.vue`, `ManageTwoFactor.vue`, `useCurrentUrl.ts`, `Show.vue`, `useAppearance.ts`?**
  _High betweenness centrality (0.040) - this node is a cross-community bridge._
- **What connects `$schema`, `style`, `config` to the rest of the system?**
  _523 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `vue` be split into smaller, more focused modules?**
  _Cohesion score 0.055130784708249496 - nodes in this community are weakly interconnected._
- **Should `reka-ui` be split into smaller, more focused modules?**
  _Cohesion score 0.05708245243128964 - nodes in this community are weakly interconnected._
- **Should `Illuminate\Database\Schema\Blueprint` be split into smaller, more focused modules?**
  _Cohesion score 0.07973421926910298 - nodes in this community are weakly interconnected._