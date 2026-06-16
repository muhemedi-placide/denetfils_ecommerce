<?php

namespace App\Support;

class CoreDefaults
{
    public const LOCALES = ['fr', 'en'];

    public const USER_STATUSES = ['active', 'invited', 'suspended', 'deleted_pending'];

    public const ADDRESS_TYPES = ['billing', 'shipping'];

    public const ROLES = [
        'super_admin',
        'admin',
        'operations_manager',
        'catalog_manager',
        'support_agent',
        'finance_manager',
        'customer',
    ];

    public const PERMISSIONS = [
        'users.view',
        'users.create',
        'users.update',
        'users.suspend',
        'roles.view',
        'roles.assign',
        'permissions.view',
        'catalog.view',
        'catalog.manage',
        'orders.view',
        'orders.manage',
        'payments.view',
        'payments.manage',
        'customers.view',
        'customers.manage',
        'compliance.view',
        'audit.view',
    ];

    public const CONSENT_VERSIONS = [
        'privacy_policy' => '2026-06-13',
        'terms' => '2026-06-13',
        'marketing_email' => '2026-06-13',
    ];

    public const DEFAULT_TIMEZONE = 'Europe/Paris';
}
