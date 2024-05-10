<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleAndPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            ["name" => "company-index"],
            ["name" => "company-store"],
            ["name" => "company-show"],
            ["name" => "company-update"],
            ["name" => "company-delete"],


            ["name" => "category-index"],
            ["name" => "category-store"],
            ["name" => "category-show"],
            ["name" => "category-update"],
            ["name" => "category-delete"],


            ["name" => "organizationalrole-index"],
            ["name" => "organizationalrole-store"],
            ["name" => "organizationalrole-show"],
            ["name" => "organizationalrole-update"],
            ["name" => "organizationalrole-delete"],


            ["name" => "form-index"],
            ["name" => "form-store"],
            ["name" => "form-show"],
            ["name" => "form-update"],
            ["name" => "form-delete"],


            // ["name" => "checklist-index"],
            // ["name" => "checklist-store"],
            // ["name" => "checklist-show"],
            // ["name" => "checklist-update"],
            // ["name" => "checklist-delete"],
            // ["name" => "checklist-template"], // This Checklist Only For Admin


            ["name" => "role-index"],
            ["name" => "role-store"],
            ["name" => "role-show"],
            ["name" => "role-update"],


            ["name" => "user-index"],
            ["name" => "user-store"],
            ["name" => "user-show"],
            ["name" => "user-update"],
            ["name" => "user-delete"],

            ["name" => "team-index"],
            ["name" => "team-store"],
            ["name" => "team-show"],
            ["name" => "team-update"],

            ["name" => "ip-index"],
            ["name" => "ip-store"],
            ["name" => "ip-show"],
            ["name" => "ip-update"],
            ["name" => "ip-list"], // Navbar


            // ["name" => "checklist-config-index"],
            // ["name" => "checklist-config-store"],
            // ["name" => "checklist-config-show"],
            // ["name" => "checklist-config-update"],
            // ["name" => "checklist-config-list"],

            ["name" => "risk-index"],
            ["name" => "risk-store"],
            ["name" => "risk-show"],
            ["name" => "risk-update"],
            ["name" => "risk-list"],
            ["name" => "risk-export"],

            ["name" => "daily-checklist-index"],
            ["name" => "daily-checklist-status"],

            ["name" => "admin-template-index"],
            ["name" => "admin-template-show"],


            ["name" => "package-plan-index"],
            ["name" => "package-plan-store"],
            ["name" => "package-plan-show"],
            ["name" => "package-plan-update"],
            ["name" => "package-plan-delete"], // Navbar


            // Tasks
            ["name" => "task-dashboard"],

            ["name" => "task-index"],
            ["name" => "task-store"],
            ["name" => "task-show"],
            ["name" => "task-update"],
            ["name" => "task-delete"],
            ["name" => "task-delete"],
            ["name" => "task-clone"],

            ["name" => "task-config-index"],
            ["name" => "task-config-store"],
            ["name" => "task-config-show"],
            ["name" => "task-config-update"],

            ["name" => "task-config-status"],


            ["name" => "task-template"], // This task Only For Admin

            ["name" => "schedule-index"],
            ["name" => "schedule-status"],
            ["name" => "schedule-show"],

            ["name" => "config-show"],

            ["name" => "subscribed-plan-detail"],


            // Myth Buster
            ["name" => "mybuster-index"],
            ["name" => "mybuster-store"],
            ["name" => "mybuster-show"],
            ["name" => "mybuster-update"],
            ["name" => "mybuster-delete"],




            ["name" => "theme-store"],

            // Stock Start
            ["name" => "stock-categories-index"],
            ["name" => "stock-categories-store"],
            ["name" => "stock-categories-show"],
            ["name" => "stock-categories-update"],
            // Stock End

            // Product Start
            ["name" => "product-index"],
            ["name" => "product-store"],
            ["name" => "product-show"],
            ["name" => "product-update"],
            ["name" => "product-delete"],
            ["name" => "product-listing"],
            ["name" => "product-order"],
            // Product End

            // Quote on Product Start
            ["name" => "quote-index"],
            ["name" => "quote-store"],
            ["name" => "quote-show"],
            ["name" => "quote-update"],
            ["name" => "quote-delete"],
            ["name" => "quote-export"],
            ["name" => "quote-upload"],
            ["name" => "quote-admin-list"],
            // Quote on Product End

            // Supplier Start
            ["name" => "supplier-index"],
            ["name" => "supplier-store"],
            ["name" => "supplier-show"],
            ["name" => "supplier-update"],
            ["name" => "supplier-delete"],
            // Supplier End

            // Attendence Start
            ["name" => "attendance-index"],
            ["name" => "attendance-edit"],
            ["name" => "attendance-update"],
            ["name" => "attendance-store"],
            ["name" => "attendance-show"],
            ["name" => "attendance-delete"],
            // Attendence End

            // Profile Start
            ["name" => "profile-update"],
            // Profile End

            ["name" => "cqc-visit-index"],
            ["name" => "cqc-visit-store"],
            ["name" => "cqc-visit-update"],
            ["name" => "cqc-visit-delete"],

            // Order Start
            ["name" => "order-index"],
            ["name" => "order-edit"],
            ["name" => "order-update"],
            ["name" => "order-store"],
            ["name" => "order-show"],
            ["name" => "order-delete"],
            ["name" => "supplier-order-index"],
            ["name" => "supplier-order-update"],
            ["name" => "supplier-order-show"],
            // Order End

            // Employee End
            ["name" => "employee-index"],
            ["name" => "employee-update"],
            ["name" => "employee-store"],
            ["name" => "employee-show"],
            ["name" => "employee-delete"],
            // Employee End
        ];

        foreach ($permissions as $permission) {
            $new_permission = Permission::where('name', $permission['name'])->first();
            if (!$new_permission) {
                Permission::create($permission);
            }
        }

        $super_admin = Role::where('name', 'Super Admin')->first();
        if (!$super_admin) {
            $super_admin = Role::create(['name' => 'Super Admin']);
        }
        $super_admin->givePermissionTo([
            "company-index",
            "company-store",
            "company-show",
            "company-update",
            "company-delete",

            "category-index",
            "category-store",
            "category-show",
            "category-update",
            "category-delete",
            
            "organizationalrole-index",
            "organizationalrole-store",
            "organizationalrole-show",
            "organizationalrole-update",
            "organizationalrole-delete",

            "form-index",
            "form-store",
            "form-show",
            "form-update",
            "form-delete",

            // "checklist-index",
            // "checklist-store",
            // "checklist-show",
            // "checklist-update",
            // "checklist-delete",
            // 'checklist-template',

            'ip-store',
            "ip-show",
            "ip-update",
            'admin-template-index',
            'admin-template-show',
            "role-index",
            "role-store",
            "role-show",
            "role-update",

            "package-plan-index",
            "package-plan-store",
            "package-plan-show",
            "package-plan-update",
            "package-plan-delete",

            // Tasks
            "task-index",
            "task-store",
            "task-show",
            "task-update",
            "task-delete",
            "task-clone",
            'task-template',

            "schedule-index",
            "schedule-status",
            "schedule-show",

            "mybuster-index",
            "mybuster-store",
            "mybuster-show",
            "mybuster-update",
            "mybuster-delete",

            "package-plan-index",

            // Stock Start
            "stock-categories-index",
            "stock-categories-store",
            "stock-categories-show",
            "stock-categories-update",
            // Stock End

            // Product Start
            "product-index",
            "product-store",
            "product-show",
            "product-update",
            "product-delete",
            // Product End

            // Supplier Start
            "supplier-index",
            "supplier-store",
            "supplier-show",
            "supplier-update",
            "supplier-delete",
            // Supplier End

            "profile-update",

            // Quotes
            "quote-export",
            "quote-upload",
            "quote-admin-list",
        ]);
        $manager = Role::where('name', 'Manager')->first();
        if (!$manager) {
            $manager = Role::create(['name' => 'Manager']);
        }
        $manager->givePermissionTo([
            // "role-index",
            // "role-store",
            // "role-show",
            // "role-update",
            "user-index",
            "user-store",
            "user-show",
            "user-update",
            "user-delete",
            "team-index",
            "team-store",
            "team-show",
            "team-update",
            "ip-index",
            "ip-store",
            "ip-show",
            "ip-update",
            "ip-list",

            // "checklist-index",
            // "checklist-store",
            // "checklist-show",
            // "checklist-update",
            // //                "checklist-delete",
            // "checklist-config-index",
            // "checklist-config-store",
            // "checklist-config-show",
            // "checklist-config-update",
            // "checklist-config-list",


            "risk-index",
            "risk-store",
            "risk-show",
            "risk-update",
            "risk-list",
            "risk-export",

            "daily-checklist-index",
            'admin-template-index',
            "daily-checklist-status",

            // Tasks
            "task-dashboard",

            "task-index",
            "task-store",
            "task-show",
            "task-update",
            // "task-clone",

            "task-config-index",
            "task-config-store",
            "task-config-show",
            "task-config-update",
            "task-config-status",


            "config-show",

            "schedule-index",
            "schedule-status",
            "schedule-show",

            "subscribed-plan-detail",
            "attendance-index",
            "attendance-show",
            "attendance-edit",
            "attendance-update",
            "attendance-delete",


            "theme-store",

            "attendance-store",

            "cqc-visit-index",
            "cqc-visit-store",
            "cqc-visit-update",
            "cqc-visit-delete",

            "profile-update",

            "product-listing",
            "product-order",

            // Start order
            "order-index",
            "order-store",
            "order-show",
            "order-edit",
            "order-update",
            "order-delete",
            // End order

            // Employee End
            "employee-index",
            "employee-update",
            "employee-store",
            "employee-show",
            "employee-delete",
            // Employee End

        ]);
        $staff = Role::where('name', 'Staff')->first();
        if (!$staff) {
            Role::create(['name' => 'Staff']);
        }

        $approval = Role::where('name', 'Approver')->first();

        if (!$approval) {
            $approval = Role::create(['name' => 'Approver']);
        }
        $approval->givePermissionTo([
            "daily-checklist-index",
            "daily-checklist-status",

            "schedule-index",
            "schedule-status",
            "schedule-show"
        ]);

        $manager->givePermissionTo([
            "daily-checklist-index",
            "daily-checklist-status",

            "schedule-index",
            "schedule-status"
        ]);


        $supplier = Role::where('name', 'Supplier')->first();
        if (!$supplier) {
            $supplier = Role::create(['name' => 'Supplier']);
        }

        $supplier->givePermissionTo([
            "quote-index",
            "quote-store",
            "quote-show",
            "quote-update",
            "quote-delete",

            "profile-update",

            // Order
            "supplier-order-index",
            "supplier-order-update",
            "supplier-order-show",
        ]);
    }
}
