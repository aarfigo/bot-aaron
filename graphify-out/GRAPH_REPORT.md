# Graph Report - .  (2026-07-28)

## Corpus Check
- cluster-only mode — file stats not available

## Summary
- 621 nodes · 817 edges · 140 communities (125 shown, 15 thin omitted)
- Extraction: 94% EXTRACTED · 6% INFERRED · 0% AMBIGUOUS · INFERRED: 49 edges (avg confidence: 0.8)
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `eaf42327`
- Run `git rev-parse HEAD` and compare to check if the graph is stale.
- Run `graphify update .` after code changes (no API cost).

## Community Hubs (Navigation)
- User
- Controller
- Illuminate\Http\Request
- composer.json
- Illuminate\Foundation\Http\FormRequest
- Illuminate\Database\Eloquent\Model
- MenuItemController
- devDependencies
- Illuminate\Database\Seeder
- scripts
- Illuminate\Database\Migrations\Migration
- CleanedOrdersController
- Handler
- SyncLegacyUsers
- AppServiceProvider
- EnsureUserHasRole.php
- CreateTblMenuTable
- CreateTblMenuitemTable
- CreateTblOrderTable
- CreateTblOrderdetailTable
- CreateTblRoleTable
- CreateTblStaffTable
- AddRoleToUsersTable
- ExampleTest
- profile/edit.blade.php
- app.blade.php
- kitchen/index.blade.php
- orders/index.blade.php

## God Nodes (most connected - your core abstractions)
1. `Controller` - 49 edges
2. `User` - 30 edges
3. `TestCase` - 22 edges
4. `OrderController` - 17 edges
5. `TableController` - 10 edges
6. `MenuItemController` - 9 edges
7. `LoginRequest` - 9 edges
8. `Menu` - 9 edges
9. `MenuItem` - 9 edges
10. `require-dev` - 9 edges

## Surprising Connections (you probably didn't know these)
- `CleanedOrdersController` --inherits--> `Controller`  [EXTRACTED]
  app/Http/Controllers/Admin/CleanedOrdersController.php → app/Http/Controllers/Controller.php
- `ExchangeRateController` --inherits--> `Controller`  [EXTRACTED]
  app/Http/Controllers/Admin/ExchangeRateController.php → app/Http/Controllers/Controller.php
- `MenuController` --inherits--> `Controller`  [EXTRACTED]
  app/Http/Controllers/Admin/MenuController.php → app/Http/Controllers/Controller.php
- `MenuItemController` --inherits--> `Controller`  [EXTRACTED]
  app/Http/Controllers/Admin/MenuItemController.php → app/Http/Controllers/Controller.php
- `StaffController` --inherits--> `Controller`  [EXTRACTED]
  app/Http/Controllers/Admin/StaffController.php → app/Http/Controllers/Controller.php

## Import Cycles
- None detected.

## Communities (140 total, 15 thin omitted)

### Community 0 - "User"
Cohesion: 0.06
Nodes (17): User, Illuminate\Database\Eloquent\Factories\HasFactory, Illuminate\Foundation\Auth\User, Illuminate\Foundation\Testing\RefreshDatabase, Illuminate\Foundation\Testing\TestCase, Illuminate\Notifications\Notifiable, AuthenticationTest, EmailVerificationTest (+9 more)

### Community 1 - "Controller"
Cohesion: 0.08
Nodes (21): AuthenticatedSessionController, ConfirmablePasswordController, EmailVerificationNotificationController, EmailVerificationPromptController, NewPasswordController, PasswordController, PasswordResetLinkController, RegisteredUserController (+13 more)

### Community 2 - "Illuminate\Http\Request"
Cohesion: 0.07
Nodes (8): TableController, OrderApiController, AuthController, ClientBoardController, KitchenController, OrderController, TableController, Illuminate\Http\Request

### Community 3 - "composer.json"
Cohesion: 0.05
Nodes (41): pestphp/pest-plugin, php-http/discovery, autoload, autoload-dev, psr-4, psr-4, config, allow-plugins (+33 more)

### Community 4 - "Illuminate\Foundation\Http\FormRequest"
Cohesion: 0.08
Nodes (8): StaffController, LoginRequest, ProfileUpdateRequest, StoreOrderRequest, StoreStaffRequest, Staff, StoreOrderRequest, Illuminate\Foundation\Http\FormRequest

### Community 5 - "Illuminate\Database\Eloquent\Model"
Cohesion: 0.09
Nodes (12): ExchangeRateController, OrderController, Admin, ExchangeRate, Order, OrderDetail, Role, Table (+4 more)

### Community 6 - "MenuItemController"
Cohesion: 0.11
Nodes (6): MenuController, MenuItemController, StoreMenuItemRequest, StoreMenuRequest, Menu, MenuItem

### Community 7 - "devDependencies"
Cohesion: 0.07
Nodes (27): alpinejs, autoprefixer, axios, concurrently, laravel-vite-plugin, devDependencies, alpinejs, autoprefixer (+19 more)

### Community 8 - "Illuminate\Database\Seeder"
Cohesion: 0.10
Nodes (10): AdminSeeder, DatabaseSeeder, MenuItemSeeder, MenuSeeder, OrderDetailSeeder, OrderSeeder, RoleSeeder, StaffSeeder (+2 more)

### Community 9 - "scripts"
Cohesion: 0.08
Nodes (26): scripts, dev, post-autoload-dump, post-create-project-cmd, post-root-package-install, post-update-cmd, pre-package-uninstall, setup (+18 more)

### Community 10 - "Illuminate\Database\Migrations\Migration"
Cohesion: 0.28
Nodes (3): CreateTblAdminTable, AddStaffnameToTblStaff, Illuminate\Database\Migrations\Migration

### Community 12 - "Handler"
Cohesion: 0.40
Nodes (3): Handler, Illuminate\Foundation\Exceptions\Handler, Throwable

### Community 25 - "profile/edit.blade.php"
Cohesion: 0.50
Nodes (3): profile.partials.delete-user-form, profile.partials.update-password-form, profile.partials.update-profile-information-form

## Knowledge Gaps
- **68 isolated node(s):** `$schema`, `name`, `type`, `description`, `laravel` (+63 more)
  These have ≤1 connection - possible missing edges or undocumented components.
- **15 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `Controller` connect `Controller` to `User`, `Illuminate\Http\Request`, `Illuminate\Foundation\Http\FormRequest`, `Illuminate\Database\Eloquent\Model`, `MenuItemController`, `CleanedOrdersController`?**
  _High betweenness centrality (0.067) - this node is a cross-community bridge._
- **Why does `User` connect `User` to `Controller`, `Illuminate\Foundation\Http\FormRequest`?**
  _High betweenness centrality (0.033) - this node is a cross-community bridge._
- **Are the 25 inferred relationships involving `User` (e.g. with `.store()` and `.update()`) actually correct?**
  _`User` has 25 INFERRED edges - model-reasoned connections that need verification._
- **What connects `$schema`, `name`, `type` to the rest of the system?**
  _68 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `User` be split into smaller, more focused modules?**
  _Cohesion score 0.06292966684294024 - nodes in this community are weakly interconnected._
- **Should `Controller` be split into smaller, more focused modules?**
  _Cohesion score 0.07609427609427609 - nodes in this community are weakly interconnected._
- **Should `Illuminate\Http\Request` be split into smaller, more focused modules?**
  _Cohesion score 0.07227891156462585 - nodes in this community are weakly interconnected._