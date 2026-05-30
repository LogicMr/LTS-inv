<?php
/**
 * Currency Helper Functions
 * Simplified to use only Tanzanian Shillings (TSh)
 */

// Get default currency (always TSh)
function getDefaultCurrency() {
    return [
        'id' => 1,
        'code' => 'TZS',
        'name' => 'Tanzanian Shilling',
        'symbol' => 'TSh',
        'exchange_rate' => 1.000000,
        'is_default' => TRUE,
        'is_active' => TRUE
    ];
}

// Get currency by ID (always returns TSh)
function getCurrency($id) {
    return getDefaultCurrency();
}

// Get currency by code (always returns TSh)
function getCurrencyByCode($code) {
    return getDefaultCurrency();
}

// Get all currencies (returns only TSh)
function getCurrencies() {
    return [getDefaultCurrency()];
}

// Convert amount (no conversion needed - always TSh)
function convertCurrency($amount, $fromCurrencyId, $toCurrencyId) {
    return $amount;
}

// Convert to default currency (no conversion needed)
function convertToDefault($amount, $fromCurrencyId) {
    return $amount;
}

// Get currency select options (only TSh)
function getCurrencySelectOptions($selectedId = null) {
    $currency = getDefaultCurrency();
    $selected = ($selectedId == $currency['id']) ? 'selected' : '';
    return "<option value='{$currency['id']}' {$selected}>{$currency['code']} - {$currency['name']} (Default)</option>";
}

// Update exchange rate (not needed but kept for compatibility)
function updateExchangeRate($currencyId, $newRate) {
    return true; // Always returns true since we don't need exchange rates
}

// Set default currency (not needed but kept for compatibility)
function setDefaultCurrency($currencyId) {
    return true; // Always returns true since TSh is always default
}

// Get exchange rate (always 1.0)
function getExchangeRate($fromCurrencyId, $toCurrencyId) {
    return 1.0;
}

// Get default currency symbol (always TSh)
function getDefaultCurrencySymbol() {
    return 'TSh';
}

// Get currency symbol (always TSh)
function getCurrencySymbol($currencyId = null) {
    return 'TSh';
}

?>
