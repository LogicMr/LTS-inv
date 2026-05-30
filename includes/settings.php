<?php
/**
 * Settings Functions
 * Inventory Management System
 */

/**
 * Get system setting value
 */
function getSetting($key, $default = null) {
    global $pdo;
    
    // Ensure database connection is available
    if (!$pdo) {
        return $default;
    }
    
    // Always fetch fresh data from database
    try {
        $stmt = $pdo->prepare("SELECT setting_value, setting_type FROM system_settings WHERE setting_key = ?");
        $stmt->execute([$key]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($row) {
            $value = $row['setting_value'];
            
            // Type casting
            switch ($row['setting_type']) {
                case 'number':
                    $value = is_numeric($value) ? (float)$value : 0;
                    break;
                case 'boolean':
                    $value = (bool)$value;
                    break;
                case 'json':
                    $value = json_decode($value, true) ?: [];
                    break;
                default:
                    $value = (string)$value;
            }
            
            return $value;
        }
        
        return $default;
    } catch (PDOException $e) {
        return $default;
    }
}

/**
 * Update system setting
 */
function updateSetting($key, $value, $type = 'string') {
    global $pdo;
    
    // Ensure database connection is available
    if (!$pdo) {
        return false;
    }
    
    // Type casting
    switch ($type) {
        case 'number':
            $value = (string)(float)$value;
            break;
        case 'boolean':
            $value = $value ? '1' : '0';
            break;
        case 'json':
            $value = json_encode($value);
            break;
        default:
            $value = (string)$value;
    }
    
    try {
        $stmt = $pdo->prepare("UPDATE system_settings SET setting_value = ?, updated_at = CURRENT_TIMESTAMP WHERE setting_key = ?");
        return $stmt->execute([$value, $key]);
    } catch (PDOException $e) {
        return false;
    }
}

/**
 * Get all settings by group
 */
function getSettingsByGroup($group) {
    global $pdo;
    
    // Check if database connection exists
    if (!$pdo) {
        return [];
    }
    
    // Check if settings table exists
    try {
        $stmt = $pdo->prepare("SELECT setting_key, setting_value, setting_type FROM system_settings WHERE setting_group = ?");
        $stmt->execute([$group]);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $settings = [];
        foreach ($result as $row) {
            $value = $row['setting_value'];
            
            // Type casting
            switch ($row['setting_type']) {
                case 'number':
                    $value = is_numeric($value) ? (float)$value : 0;
                    break;
                case 'boolean':
                    $value = (bool)$value;
                    break;
                case 'json':
                    $value = json_decode($value, true) ?: [];
                    break;
                default:
                    $value = (string)$value;
            }
            
            $settings[$row['setting_key']] = $value;
        }
        
        return $settings;
    } catch (PDOException $e) {
        // Table doesn't exist or other database error, return empty array
        return [];
    } catch (Exception $e) {
        // Any other error, return empty array
        return [];
    }
}

/**
 * Get formatted currency with decimal places
 */
function formatCurrencyWithSettings($amount, $currencyId = null) {
    $decimalPlaces = getSetting('decimal_places', 2);
    $thousandsSeparator = getSetting('thousands_separator', ',');
    $decimalSeparator = getSetting('decimal_separator', '.');
    
    if ($currencyId) {
        $currency = fetchRow("SELECT symbol FROM currencies WHERE id = ?", [$currencyId]);
        $symbol = $currency['symbol'] ?? '$';
    } else {
        $symbol = getSetting('default_currency_symbol', '$');
    }
    
    return $symbol . number_format($amount, $decimalPlaces, $decimalSeparator, $thousandsSeparator);
}

/**
 * Get formatted date with settings
 */
function formatDateWithSettings($date, $format = null) {
    if (!$format) {
        $dateFormat = getSetting('date_format', 'Y-m-d');
        $timeFormat = getSetting('time_format', 'H:i:s');
        $format = $dateFormat . ' ' . $timeFormat;
    }
    
    return date($format, strtotime($date));
}
?>
