<?php
/**
 * Plugin Name: Class Cost Calculator
 * Description: Lightweight cost calculator with shortcode [class_cost_calculator]. Shows crossed old price and "After discount" price. USD only.
 * Version: 1.0.0
 * Author: MOHAMED HAMDY
 * Author URI: https://hamdyweb.com
 * Requires at least: 5.8
 * Requires PHP: 7.4
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class Class_Cost_Calculator {
    const SLUG = 'class-cost-calculator';
    private static $instance = null;

    public static function instance() {
        if ( self::$instance === null ) self::$instance = new self();
        return self::$instance;
    }

    private function __construct() {
        add_shortcode( 'class_cost_calculator', [ $this, 'render_shortcode' ] );
        add_action( 'wp_enqueue_scripts', [ $this, 'register_assets' ] );
        add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_front' ] );
    }

    public function register_assets() {
        $v = '1.0.0';
        wp_register_style(
            self::SLUG,
            plugins_url( 'assets/style.css', __FILE__ ),
            [],
            $v
        );

        wp_register_script(
            self::SLUG,
            plugins_url( 'assets/app.js', __FILE__ ),
            [],
            $v,
            true
        );
    }

    public function enqueue_front() {
        if ( ! is_singular() && ! is_front_page() ) return; // keep light

        // Read packages.json
        $json_path = plugin_dir_path( __FILE__ ) . 'packages.json';
        $json = file_exists( $json_path ) ? file_get_contents( $json_path ) : '{}';
        $packages = json_decode( $json, true );
        if ( ! is_array( $packages ) ) $packages = [];

        // Settings you can customize
        $settings = [
            'currency' => 'USD',
            'decimals' => 2,
            // 'colors'   => [
            //     'primary'   => 'var(--e-global-color-secondary)', // اصفر
            //     'dark'      => 'var(--e-global-color-6f59b8c)', // بني غامق
            //     'paper'     => 'var(--e-global-color-primary)', // بيج فاتح
            // ],
            // Discount tiers: order matters (threshold => percent)
            // Applies on the base monthly price BEFORE discount.
            'discounts' => [
                [ 'threshold' => 180, 'percent' => 15 ],
                [ 'threshold' => 150, 'percent' => 10 ],
                [ 'threshold' => 100, 'percent' => 5 ],
            ],
            // URLs to use for subscribe buttons per key.
            // Keep "#" placeholder until you have real links.
            'subscribe_links' => [
                // e.g. "25m_12" => "https://paypal.com/..."
            ],
            'disallow_six' => true,
        ];

        wp_enqueue_style( self::SLUG );
        wp_enqueue_script( self::SLUG );
        wp_localize_script( self::SLUG, 'CCC_DATA', [
            'packages' => $packages,
            'settings' => $settings,
        ] );
    }

    public function render_shortcode( $atts ) {
        ob_start(); ?>
        <div class="ccc-wrap" data-ccc>
            <div class="ccc-controls">
                <div class="ccc-duration">
                    <span class="ccc-label">Duration</span>
                    <div class="ccc-toggle" role="tablist" aria-label="Select duration">
                        <button type="button" class="ccc-tab is-active" data-duration="25">25 min</button>
                        <button type="button" class="ccc-tab" data-duration="50">50 min</button>
                    </div>
                </div>

                <div class="ccc-classes">
                    <span class="ccc-label">Classes / week</span>
                    <div class="ccc-cards" role="list">
                        <?php foreach ( [1,2,3,4,5] as $n ) : ?>
                            <button type="button" class="ccc-card" role="listitem" data-perweek="<?php echo esc_attr($n); ?>">
                                <strong><?php echo esc_html($n); ?></strong>
                                <span>per week</span>
                            </button>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <div class="ccc-result" aria-live="polite">
                <div class="ccc-billing-label">
                    <span class="ccc-billing-text">Billed Monthly</span>
                </div>

                <div class="ccc-pricing">
                    <div class="ccc-old-price" data-old-price-container >
                        <s data-oldprice>$0.00</s>
                    </div>
                    <div class="ccc-main-price">
                        <strong data-newprice>$0.00</strong>
                        <span class="ccc-discount" data-discountbadge></span>
                    </div>
                </div>

                <div class="ccc-meta">
                    <div class="ccc-package"><span class="ccc-muted">Package:</span> <strong data-package>—</strong></div>
                    <div class="ccc-details">
                        <span data-duration-label>25 min</span> •
                        <span><span data-perweek-display>1</span>/week</span> •
                        <span><span data-permonth>4</span>/month</span>
                    </div>
                </div>

                <div class="ccc-cta">
                    <a href="#" class="ccc-btn" data-subscribe>Get Started</a>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
}

Class_Cost_Calculator::instance();
