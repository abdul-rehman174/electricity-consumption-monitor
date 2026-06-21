<?php
/**
 * WAPDA residential tariff configuration  (NFR-05: single source of truth).
 *
 * The active slab rates are stored in the `tariff_slabs` database table and can
 * be edited from the app:  Admin -> Tariff.  The array below is only a fallback
 * used to seed the table and to keep the app working if the table is missing.
 *
 * Each slab covers a unit range [from, to] and charges `rate` (PKR) per unit
 * that falls inside that range (progressive / tiered billing).
 * The top slab's `to` is unbounded (stored as NULL in the database).
 */

// Default / fallback rates (also used to seed the tariff_slabs table).
$DEFAULT_WAPDA_SLABS = [
    ['from' => 1,   'to' => 100,         'rate' => 13.48],
    ['from' => 101, 'to' => 200,         'rate' => 18.95],
    ['from' => 201, 'to' => 300,         'rate' => 22.14],
    ['from' => 301, 'to' => 400,         'rate' => 25.53],
    ['from' => 401, 'to' => 500,         'rate' => 27.74],
    ['from' => 501, 'to' => 600,         'rate' => 29.16],
    ['from' => 601, 'to' => 700,         'rate' => 30.30],
    ['from' => 701, 'to' => PHP_INT_MAX, 'rate' => 35.24],
];

/**
 * Load the active slabs from the database, falling back to the defaults if the
 * table is missing or empty. `slab_to = NULL` means "and above" (unbounded).
 */
$WAPDA_SLABS = $DEFAULT_WAPDA_SLABS;
if (isset($conn) && $conn) {
    $res = @mysqli_query($conn, 'SELECT slab_from, slab_to, rate FROM tariff_slabs ORDER BY slab_from ASC');
    if ($res && mysqli_num_rows($res) > 0) {
        $loaded = [];
        while ($row = mysqli_fetch_assoc($res)) {
            $loaded[] = [
                'from' => (int) $row['slab_from'],
                'to'   => is_null($row['slab_to']) ? PHP_INT_MAX : (int) $row['slab_to'],
                'rate' => (float) $row['rate'],
            ];
        }
        $WAPDA_SLABS = $loaded;
    }
}

/**
 * Calculate the estimated bill (PKR) for a given number of units using the
 * progressive slab method: each unit is charged at the rate of the slab it
 * falls into, and the charges are summed.
 *
 * @param float $units Total monthly consumption in kWh.
 * @return float       Estimated bill amount in PKR (rounded to 2 decimals).
 */
function calculate_bill($units)
{
    global $WAPDA_SLABS;
    $units = max(0, (float) $units);
    $bill  = 0.0;

    foreach ($WAPDA_SLABS as $slab) {
        if ($units <= $slab['from'] - 1) {
            break; // consumption does not reach this slab
        }
        // Units that fall within the current slab's range.
        $upper        = min($units, $slab['to']);
        $units_in_slab = $upper - ($slab['from'] - 1);
        if ($units_in_slab > 0) {
            $bill += $units_in_slab * $slab['rate'];
        }
    }

    return round($bill, 2);
}

/**
 * Return a human-readable breakdown of how the bill was built up per slab.
 * Used on the bill summary page so the calculation is transparent.
 *
 * @param float $units
 * @return array  List of ['range', 'units', 'rate', 'amount'].
 */
function bill_breakdown($units)
{
    global $WAPDA_SLABS;
    $units = max(0, (float) $units);
    $rows  = [];

    foreach ($WAPDA_SLABS as $slab) {
        if ($units <= $slab['from'] - 1) {
            break;
        }
        $upper         = min($units, $slab['to']);
        $units_in_slab = $upper - ($slab['from'] - 1);
        if ($units_in_slab > 0) {
            $label = ($slab['to'] === PHP_INT_MAX)
                ? $slab['from'] . '+'
                : $slab['from'] . '-' . $slab['to'];
            $rows[] = [
                'range'  => $label,
                'units'  => round($units_in_slab, 2),
                'rate'   => $slab['rate'],
                'amount' => round($units_in_slab * $slab['rate'], 2),
            ];
        }
    }

    return $rows;
}
