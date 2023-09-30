<?php
/**
 * Quantity Discount For WooCommerce
 *
 * @package   quantity_discount_for_woocommerce
 * @author    Mohammad Mahdi Kabir <2m.kabir@gmail.com>
 * @license   GPL-2.0+
 * @link      https://github.com/2m.kabir/quantity-discount-for-woocommerce
 * @copyright 2023 Mohammad Mahdi Kabir
 *
 * @wordpress-plugin
 * Plugin Name:       Quantity Discount For WooCommerce
 * Plugin URI:        https://github.com/2m.kabir/quantity-discount-for-woocommerce
 * Description:       Minimum and maximum quantity of products restrictions for coupons.
 * Version:           1.0
 * Author:            Mohammad Mahdi Kabir
 * Author URI:        https://www.linkedin.com/in/mohammad-mahdi-kabir/
 * Text Domain:       quantity-discount-for-woocommerce
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path:       /languages
 * GitHub Plugin URI: https://github.com/2m.kabir/quantity-discount-for-woocommerce
 * GitHub Branch:     master
 */

defined('ABSPATH') || exit;

class Quantity_Discount_For_Woocommerce_Ya59
{
    static $instance = false;

    private function __construct()
    {
        if (get_option('woocommerce_enable_coupons') === 'yes') {
            add_action('woocommerce_coupon_options_usage_restriction', array($this, 'show_fields_in_coupon_options'), 10, 2);
            add_action('woocommerce_coupon_options_save', array($this, 'save_data_in_coupon_options'), 10, 2);
            add_filter('woocommerce_coupon_is_valid_for_product', array($this, 'coupon_is_valid_for_product'), 10, 4);
        }
    }

    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new self;
        }
        return self::$instance;
    }

    public function show_fields_in_coupon_options($coupon_id, $coupon)
    {
        echo '<div class="options_group">';
        woocommerce_wp_text_input(
            array(
                'id' => '_quantity_discount_for_woocommerce_minimum_quantity',
                'label' => __('Minimum product quantity', 'quantity-discount-for-woocommerce'),
                'placeholder' => __('No minimum', 'quantity-discount-for-woocommerce'),
                'description' => __('This field allows you to set the minimum quantity of products allowed to use the coupon.', 'quantity-discount-for-woocommerce'),
                'type' => 'number',
                'desc_tip' => true,
                'class' => 'short',
                'custom_attributes' => array(
                    'step' => 1,
                    'min' => 0,
                ),
                'value' => $coupon->meta_exists('_quantity_discount_for_woocommerce_minimum_quantity') ? $coupon->get_meta('_quantity_discount_for_woocommerce_minimum_quantity', true, 'edit') : '',
            )
        );
        woocommerce_wp_text_input(
            array(
                'id' => '_quantity_discount_for_woocommerce_maximum_quantity',
                'label' => __('Maximum product quantity', 'quantity-discount-for-woocommerce'),
                'placeholder' => __('No maximum', 'quantity-discount-for-woocommerce'),
                'description' => __('This field allows you to set the maximum quantity of products allowed to use the coupon.', 'quantity-discount-for-woocommerce'),
                'type' => 'number',
                'desc_tip' => true,
                'class' => 'short',
                'custom_attributes' => array(
                    'step' => 1,
                    'min' => 0,
                ),
                'value' => $coupon->meta_exists('_quantity_discount_for_woocommerce_maximum_quantity') ? $coupon->get_meta('_quantity_discount_for_woocommerce_maximum_quantity', true, 'edit') : '',
            )
        );
        echo '</div>';
    }

    public function save_data_in_coupon_options($coupon_id, $coupon)
    {
        if (isset($_POST['_quantity_discount_for_woocommerce_minimum_quantity']) && $_POST['_quantity_discount_for_woocommerce_minimum_quantity'] !== '') {
            $coupon->update_meta_data('_quantity_discount_for_woocommerce_minimum_quantity', absint($_POST['_quantity_discount_for_woocommerce_minimum_quantity']));
        } else {
            $coupon->delete_meta_data('_quantity_discount_for_woocommerce_minimum_quantity');
        }
        if (isset($_POST['_quantity_discount_for_woocommerce_maximum_quantity']) && $_POST['_quantity_discount_for_woocommerce_maximum_quantity'] !== '') {
            $coupon->update_meta_data('_quantity_discount_for_woocommerce_maximum_quantity', absint($_POST['_quantity_discount_for_woocommerce_maximum_quantity']));
        } else {
            $coupon->delete_meta_data('_quantity_discount_for_woocommerce_maximum_quantity');
        }
        $coupon->save_meta_data();
    }

    public function coupon_is_valid($valid, $coupon, $discount)
    {
        $minimum_quantity = $coupon->meta_exists('_quantity_discount_for_woocommerce_minimum_quantity') ? absint($coupon->get_meta('_quantity_discount_for_woocommerce_minimum_quantity', true, 'edit')) : -1;
        $maximum_quantity = $coupon->meta_exists('_quantity_discount_for_woocommerce_maximum_quantity') ? absint($coupon->get_meta('_quantity_discount_for_woocommerce_maximum_quantity', true, 'edit')) : -1;
        if ($minimum_quantity >= 0 && $maximum_quantity >= 0) {
            $valid = false;
            foreach ($discount->get_items_to_validate() as $item) {
                if ($item->quantity && $item->quantity >= $minimum_quantity && $item->quantity <= $maximum_quantity) {
                    $valid = true;
                    break;
                }
            }
        } elseif ($minimum_quantity >= 0) {
            $valid = false;
            foreach ($discount->get_items_to_validate() as $item) {
                if ($item->quantity && $item->quantity >= $minimum_quantity) {
                    $valid = true;
                    break;
                }
            }
        } elseif ($maximum_quantity >= 0) {
            $valid = false;
            foreach ($discount->get_items_to_validate() as $item) {
                if ($item->quantity && $item->quantity <= $maximum_quantity) {
                    $valid = true;
                    break;
                }
            }
        }
        return $valid;
    }

    public function coupon_is_valid_for_product($valid, $product, $coupon, $values)
    {
        if (!$valid) {
            return false;
        }
        $minimum_quantity = $coupon->meta_exists('_quantity_discount_for_woocommerce_minimum_quantity') ? absint($coupon->get_meta('_quantity_discount_for_woocommerce_minimum_quantity', true, 'edit')) : -1;
        $maximum_quantity = $coupon->meta_exists('_quantity_discount_for_woocommerce_maximum_quantity') ? absint($coupon->get_meta('_quantity_discount_for_woocommerce_maximum_quantity', true, 'edit')) : -1;
        if ($minimum_quantity >= 0 && $maximum_quantity >= 0) {
            $valid = false;
            if ($values['quantity'] >= $minimum_quantity && $values['quantity'] <= $maximum_quantity) {
                $valid = true;
            }
        } elseif ($minimum_quantity >= 0) {
            $valid = false;
            if ($values['quantity'] >= $minimum_quantity) {
                $valid = true;
            }

        } elseif ($maximum_quantity >= 0) {
            $valid = false;
            if ($values['quantity'] <= $maximum_quantity) {
                $valid = true;
            }
        }
        return $valid;
    }
}

$Quantity_Discount_For_Woocommerce = Quantity_Discount_For_Woocommerce_Ya59::getInstance();



