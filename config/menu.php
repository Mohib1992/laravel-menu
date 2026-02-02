<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Menu System Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains configuration options for the menu system. You can
    | customize CSS classes, rendering behavior, and other settings here.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | CSS Classes
    |--------------------------------------------------------------------------
    |
    | Define the CSS classes used for menu rendering. These can be customized
    | to match your application's styling framework.
    |
    */

    'css_classes' => [
        'menu' => 'nav-menu',
        'section' => 'nav-section',
        'section_title' => 'nav-section-title',
        'list' => 'nav-list',
        'item' => 'nav-item',
        'link' => 'nav-link',
        'active' => 'active',
        'text' => 'nav-text',
        'badge' => 'nav-badge',
        'chevron' => 'nav-chevron',
        'icon' => 'nav-icon',
        'sub_list' => 'nav-sub-list',
    ],

    /*
    |--------------------------------------------------------------------------
    | Icons and Symbols
    |--------------------------------------------------------------------------
    |
    | Configure the icons and symbols used in menu rendering.
    |
    */

    'chevron_icon' => '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>',

    'loading_icon' => '<svg class="animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>',

    /*
    |--------------------------------------------------------------------------
    | Rendering Behavior
    |--------------------------------------------------------------------------
    |
    | Configure how menus are rendered and processed.
    |
    */

    'max_depth' => 10,                  // Maximum nesting depth for menu items
    'escape_all' => true,               // Escape all dynamic content (security)
    'auto_register' => true,            // Automatically register created menus
    'cache_enabled' => false,            // Enable menu rendering cache
    'cache_ttl' => 3600,                // Cache time-to-live in seconds

    /*
    |--------------------------------------------------------------------------
    | Security Settings
    |--------------------------------------------------------------------------
    |
    | Security-related configuration for menu rendering.
    |
    */

    'allowed_attributes' => [
        'icon', 'badge', 'active', 'class', 'id', 'target', 'title', 'rel',
    ],

    'allowed_protocols' => [
        'http', 'https', 'mailto', 'tel', 'ftp',
    ],

    'sanitize_urls' => true,

    /*
    |--------------------------------------------------------------------------
    | Accessibility
    |--------------------------------------------------------------------------
    |
    | Configure accessibility features for menu rendering.
    |
    */

    'auto_aria' => true,                // Automatically add ARIA attributes
    'semantic_html' => true,             // Use semantic HTML elements
    'keyboard_navigation' => true,        // Enable keyboard navigation

    /*
    |--------------------------------------------------------------------------
    | Development Settings
    |--------------------------------------------------------------------------
    |
    | Development and debugging options.
    |
    */

    'debug' => env('APP_DEBUG', false),  // Enable debug mode
    'validate_menus' => true,            // Validate menu structure
    'log_errors' => true,                // Log rendering errors

    /*
    |--------------------------------------------------------------------------
    | Default Menus
    |--------------------------------------------------------------------------
    |
    | Define default menus that should be available in every request.
    | These can be used for common navigation elements.
    |
    */

    'default_menus' => [
        // 'main_navigation' => [
        //     'auto_create' => true,
        //     'cache' => true,
        //     'roles' => ['guest', 'user', 'admin'],
        // ],
    ],
];
