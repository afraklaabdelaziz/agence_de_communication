<?php

if ( ! defined( 'ABSPATH' ) ) exit;


if ( ! class_exists( 'Taiowc_Markup' ) ):

    class Taiowc_Markup {
         /**
         * Member Variable
         *
         * @var object instance
         */
       
       private static $instance;

       /**
         * Initiator
         */
        public static function instance() {
            if ( ! isset( self::$instance ) ) {
                self::$instance = new self();
            }
            return self::$instance;
        }

        /**
         * Constructor
         */
        public function __construct(){
            

        }

        public function taiowc_cart_show(){
                  

            ?>
               
                       <a class="taiowc-content" href="#">
                           
                        <?php if(taiowc()->get_option( 'taiowc-cart_hd' )!==''){ ?>

                          <h4><?php echo esc_html(taiowc()->get_option( 'taiowc-cart_hd' ));?></h4>

                           <?php } ?>

                            <?php if ( ! WC()->cart->is_empty() ) { ?>

                            <div class="cart-count-item">
                                
                                <?php taiowc()->get_cart_count(); ?>
                                    
                            </div>

                            <?php } ?>
                           
                            <div class="taiowc-cart-item">

                                <div class="taiowc-icon">
                                    <?php do_action('taiowc_cart_show_icon'); ?>
                                 </div>

                                 <?php if ( ! WC()->cart->is_empty() ) {  
                                    if(taiowc()->get_option( 'taiowc-tpcrt_show_price' ) == true){ 
                                        ?>
                                 <div class="taiowc-total">

                                    <span><?php echo WC()->cart->get_cart_total(); ?></span>

                                </div>
                                <?php } } ?>

                            </div>
                        </a>
                

        <?php }


        public function taiowc_cart_item_show(){ ?>

            <div class="taiowc-cart-model">   

               <div class="taiowc-cart-model-wrap">

                    <?php $this->taiowc_cart_header();?>

                    <div class="taiowc-cart-model-body">
                        
                        <?php 

                        do_action('taiowc_mini_cart'); 


                        ?>

                    </div>

                    <div class="taiowc-cart-model-footer">

                     <?php 

                     $this->taiowc_cart_footer(); 
                    
                    ?>

                   </div>

                   

               </div>
              

                    <div class="taiowc-notice-box">

                    <span class="taiowc-notice"></span>

                    </div>

             
            </div>

            

        <?php }


        public function taiowc_cart_header(){?>


                    <div class="taiowc-cart-model-header">

                        <div class="cart-heading">

                            <?php do_action('taiowc_cart_show_icon');?>

                           <?php if(taiowc()->get_option( 'taiowc-cart_hd' )!==''){ ?>
                          <h4><?php echo esc_html(taiowc()->get_option( 'taiowc-cart_hd' ));?></h4>
                           <?php } ?>

                          <a class="taiowc-cart-close"></a>

                        </div> 

                    </div>


        <?php }


        public function taiowc_cart_footer(){ ?>

                    <?php   

                     $this->taiowc_cart_total();

                     $this->taiowc_cart_button(); 

                    ?>
        <?php }
        
          public function taiowc_cart_total(){  
        

            ?>
                <div class="cart-total">
                    <span class="taiowc-payment-title"><?php echo esc_html(taiowc()->get_option('taiowc-pay_hd')); ?></span>

                     <div class="taiowc-total-wrap">
                                
                            <div class="taiowc-subtotal">
                                <span class="taiowc-label"><?php echo esc_html(taiowc()->get_option('taiowc-sub_total')); ?></span>
                                <span class="taiowc-value"><?php echo WC()->cart->get_cart_subtotal(); ?></span>
                              </div>

                   </div>

                </div>


       <?php  }

        
        public function taiowc_cart_button(){ ?>
                

                     <div class="cart-button">
                            
                        <p class="buttons normal">

                        <?php do_action( 'woocommerce_widget_shopping_cart_buttons' ); ?>
                            
                        </p>
                              
                     </div>

       <?php  }

    
    public function taiowc_add_to_cart_url($product){

         $cart_url =  apply_filters( 'woocommerce_loop_add_to_cart_link',
            sprintf( '<a href="%s" rel="nofollow" data-product_id="%s" data-product_sku="%s" quantity="%s" class="button th-button %s %s"><span class="dashicons dashicons-plus-alt2"></span></a>',
                esc_url( $product->add_to_cart_url() ),
                esc_attr( $product->get_id() ),
                esc_attr( $product->get_sku() ),
                esc_attr( isset( $quantity ) ? $quantity : 1 ),
                $product->is_purchasable() && $product->is_in_stock() ? 'th-add_to_cart_button' : '',
                $product->is_purchasable() && $product->is_in_stock() && $product->supports( 'ajax_add_to_cart' ) ? 'th-ajax_add_to_cart' : '',
                esc_html( $product->add_to_cart_text() )
            ),$product );
         return $cart_url;
        }

        

}

function taiowc_markup(){

        return Taiowc_Markup::instance();

}
endif; 