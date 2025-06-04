<?php
/**
 * Plugin Name: CRKR Custom Cart Loop
 * Description: Restringe la función de añadir al carrito a solo usuarios logueados en WooCommerce.
 * Version: 1.0.1
 * Author: Crunchy Kernel
 */

if (!defined('ABSPATH'))
    exit;

add_filter('woocommerce_add_to_cart_validation', 'redirigir_checkout_si_ya_esta_en_carrito', 10, 5);

function redirigir_checkout_si_ya_esta_en_carrito($passed, $product_id, $quantity, $variation_id = '', $variations = '') {
    foreach (WC()->cart->get_cart() as $cart_item) {
        if ($cart_item['product_id'] == $product_id) {
            // Producto ya en el carrito: redirigir manualmente
            wp_safe_redirect(wc_get_checkout_url());
            exit;
        }
    }
    return $passed;
}

// Mostrar mensaje en la página del producto
add_action('woocommerce_single_product_summary', function () {
    if (!is_user_logged_in()) {
        echo '<div class="woocommerce-info">Para continuar con la compra <a href="' . esc_url(wp_login_url(get_permalink())) . '">inicia sesión</a>   o <a href="' . esc_url(wp_registration_url()) . '">regístrate</a></div>';

    }
});

add_filter('woocommerce_is_purchasable', function ($purchasable, $product) {
    if (!is_user_logged_in()) {
        return false;
    }
    return $purchasable;
}, 10, 2);

// 1. Reemplazar botón para no logueados (solo el botón, sin modal)
add_filter('woocommerce_loop_add_to_cart_link', function ($button, $product) {
    if (!is_user_logged_in()) {
        return '<a href="#" class="button open-login-modal"><i class="fa fa-user" aria-hidden="true" style="margin-right:6px;"></i>Inicia sesión para comprar</a>';
    }
    return $button;
}, 10, 2);

// 2. Añadir modal en footer (solo una vez)
add_action('wp_footer', function () {
    if (!is_user_logged_in()) {
        ?>
        <div id="login-modal"
            style="display:none; position:fixed; top:50%; left:50%; transform: translate(-50%, -50%);
            background:#fff; padding:20px; border:1px solid #ccc; box-shadow:0 0 15px rgba(0,0,0,0.3); z-index:9999; max-width: 900px; border-radius: 10px;">
            <div class="woocommerce-info" style="margin-bottom: 1em;">
                Para continuar con la compra
                <a href="<?php echo esc_url(wp_login_url(get_permalink())); ?>">inicia sesión</a>
                <a href="<?php echo esc_url(wp_registration_url()); ?>">regístrate</a>.
            </div>
            <button class="close-login-modal"
                style="background:#333; color:#fff; border:none; padding:5px 10px; cursor:pointer;">Cerrar</button>
        </div>
        <div id="login-modal-overlay"
            style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background: rgba(0,0,0,0.5); z-index:9998;">
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const openBtns = document.querySelectorAll('.open-login-modal');
                const modal = document.getElementById('login-modal');
                const overlay = document.getElementById('login-modal-overlay');
                const closeBtn = modal.querySelector('.close-login-modal');

                openBtns.forEach(btn => {
                    btn.addEventListener('click', function (e) {
                        e.preventDefault();
                        modal.style.display = 'block';
                        overlay.style.display = 'block';
                    });
                });

                closeBtn.addEventListener('click', function () {
                    modal.style.display = 'none';
                    overlay.style.display = 'none';
                });

                overlay.addEventListener('click', function () {
                    modal.style.display = 'none';
                    overlay.style.display = 'none';
                });
            });
        </script>
        <?php
    }
});

