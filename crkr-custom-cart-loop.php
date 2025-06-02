<?php
/**
 * Plugin Name: CRKR Custom Cart Loop
 * Description: Restringe la función de añadir al carrito a solo usuarios logueados en WooCommerce.
 * Version: 1.0.2
 * Author: Crunchy Kernel
 */

if (!defined('ABSPATH'))
    exit;

add_filter('woocommerce_add_to_cart_validation', 'evitar_duplicado_y_redirigir_checkout', 10, 5);

function mostrar_mensaje_login_registro() {
    ?>
    <div class="woocommerce-info mensaje-login-registro">
        <strong>¿Quieres comprar este producto?</strong>
        Para continuar, por favor inicia sesión en tu cuenta o regístrate si aún no tienes una.
        <div class="botones-acceso">
            <a class="btn-login" href="<?php echo esc_url(wp_login_url(get_permalink())); ?>">Iniciar sesión</a>
            <a class="btn-registro" href="<?php echo esc_url(wp_registration_url()); ?>">Registrarse</a>
        </div>
    </div>
    <?php
}

function evitar_duplicado_y_redirigir_checkout($passed, $product_id, $quantity, $variation_id = '', $variations = '') {
    foreach (WC()->cart->get_cart() as $cart_item) {
        if ($cart_item['product_id'] == $product_id) {
            // Ya está en el carrito, no lo agregamos otra vez
            // Redirigimos al checkout
            add_filter('woocommerce_add_to_cart_redirect', function() {
                return wc_get_checkout_url();
            });
            return false;
        }
    }
    return true;
}


function redirigir_al_carrito($url) {
    return wc_get_cart_url();
}

// Mostrar mensaje en la página del producto
add_action('woocommerce_single_product_summary', function () {
    if (!is_user_logged_in()) {
        mostrar_mensaje_login_registro();
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
            <?php mostrar_mensaje_login_registro(); ?>
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

