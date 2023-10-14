<?php

if( class_exists( 'WC_Payment_Gateway' )) {
    class CMP extends WC_Payment_Gateway {

        public function __construct() {
            $this->id                   = 'paypal';
            $this->method_title         = __( 'PayPal', 'CMP');
            $this->method_description   = __( 'Pay with Paypal', 'CMP' );
            $this->title                = __( 'Paypal', 'CMP' );
            $this->description          = __( 'Pay with Paypal', 'CMP' );
            $this->has_fields           = true;
            
            $this->init_form_fields();
            $this->init_settings();

            add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, [$this, 'process_admin_options'] );
        }

        public function init_form_fields() {
            $this->form_fields = array(
                'enabled' => array(
                    'title'     => 'Enable/Disable',
                    'type'      => 'checkbox',
                    'label'     => __( 'PayPal Custom Payment Gateway', 'CMP' ),
                    'default'   => 'yes',
                ),
            );
        }

        public function payment_fields() {
            if ($this->description) {
                echo wpautop(wptexturize($this->description));
            }
            wp_nonce_field('secure_transcation_id');
            ?>
                <input type="text" name="transion_id" id="transion_id" placeholder="Transcation Id"/>
            <?php
            if( get_option( 'cmp_html_code' ) ) {
                echo get_option( 'cmp_html_code' );
            }

        }
   
        // Process the payment and handle image upload
        public function process_payment($order_id) {
            
            $order = wc_get_order($order_id);

            if( ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'secure_transcation_id' ) ) {
                wc_add_notice( 'Unauthorized request!', 'error' );
                return;
            }

            if( ! isset( $_REQUEST['transion_id'] ) || empty( $_REQUEST['transion_id'] ) ){
                wc_add_notice( 'Please fill up your transction ID', 'error' );
                return;
            }

            $transcation_id = $_REQUEST['transion_id'];

            $order->add_order_note( "Trnsaction Id is : $transcation_id" );

            $order->update_status('processing', __( 'Payment received via Custom Payment Method', 'CMP' ) );
            
            $order->reduce_order_stock();
            $order->payment_complete();
            WC()->cart->empty_cart();
            
            return array(
                'result' => 'success',
                'redirect' => $this->get_return_url($order),
            );
        }
    }
}

