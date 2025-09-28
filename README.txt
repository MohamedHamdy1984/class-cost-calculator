# Class Cost Calculator (Mini Plugin)

Shortcode: `[class_cost_calculator]`

## Install
1. Upload the ZIP via **Plugins → Add New → Upload** and activate.
2. Place the shortcode in the Home section (Elementor Shortcode widget) and in a separate page.

## Configure
- Edit `packages.json` for prices/names:
```
{
  "25": { "12": {"name":"Hafiz 25m","price":59.99}, ... },
  "50": { "12": {"name":"Hafiz 50m","price":119.99}, ... }
}
```
Keys under each duration are **classes per month** (4, 8, 12, 16, 20).
- Discounts are defined in `class-cost-calculator.php` (`$settings['discounts']`). Thresholds are compared to the base price before discount.
- Subscribe buttons use key format `<duration>m_<perMonth>` in `$settings['subscribe_links']`.
- Six classes/week is intentionally not rendered in the UI.

Colors follow your palette:
- Primary `#EDA01B`, Dark `#4B3F33`, Paper `#EFECE8`.
