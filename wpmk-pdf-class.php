<?php

/**
 * @package WPMK PDF
 * 
 * Here we define plugin action hook
 * it will add link in plugin action bar
 * and all plugin setting and saving data
 * 
 */
 
if ( ! defined( 'ABSPATH' ) ) exit;
 
if(!class_exists('WPMK_PDF')){
    
    class WPMK_PDF{
        
        public function __construct() {
            $this->wpmk_pdf_init();
        }
        
        /**
         * Here active wpmk pdf
         * it is plugin init and
         * hold all functions
         */
        public function wpmk_pdf_init(){
            add_action( 'init', array( $this, 'wpmk_pdf_load_textdomain' ) );
            add_action( 'admin_enqueue_scripts', array( $this, 'wpmk_pdf_admin_enqueue_scripts' ) );
            add_action( 'init', array( $this, 'wpmk_pdf_scripts'), 1001 );
            add_action( 'admin_menu', array( $this, 'wpmk_pdf_setting_page' ) );
            add_action( 'wp_head', array ( $this, 'wpmk_pdf_generate_PDF' ) );
            add_action( 'wp_head', array ( $this, 'wpmk_pdf_style_frontend' ) );
            add_action( 'body_class', array ( $this, 'wpmk_pdf_add_body_class' ) );
            add_shortcode( 'wpmk_pdf_generate', array( $this, 'wpmk_pdf_shortcode' ) );
        }
        
        /**
         * Here we are installing plugin options
         * and also plugin require data
         */
        static function wpmk_pdf_install(){
            $wpmk_pdf_options = array(
                'wpmk-pdf-style'        => 'btn-icon',
                'wpmk-pdf-icon'         => 'pdf-1',
                'wpmk-pdf-text'         => 'Download PDF Here',
                'wpmk-pdf-page-mode'    => 'portrait',
                'wpmk-pdf-page-size'    => 'a3'
            );
            add_option( 'wpmk_pdf_option', $wpmk_pdf_options );
        }
        
        /**
         * Here we are uninstalling plugin options
         * and also plugin setup data
         */
        static function wpmk_pdf_uninstall(){
            delete_option('wpmk_pdf_option');
        }
        
        /**
         * Here We are setting wpmk pdf text domain
         */
        public function wpmk_pdf_load_textdomain() {
            load_plugin_textdomain( 'wpmk', false, WPMK_PDF_LANG ); 
        }
        
        /**
         * Here We are enqueue style for admin setting page
         */
        public function wpmk_pdf_admin_enqueue_scripts(){
            wp_register_style( 'wpmk-pdf', WPMK_PDF_ASSETS . 'css/style.css' , false, WPMK_PDF_VERSION );
            wp_enqueue_style( 'wpmk-pdf' );
        }
        
        /**
         *  Here is wpmk pdf enqueue scripts
         *  that used help to grnerate PDF
         */
        public function wpmk_pdf_scripts(){
            wp_register_script( 'wpmk-es6-promise-auto-min', WPMK_PDF_ASSETS . 'js/es6-promise.auto.min.js', array(), WPMK_PDF_VERSION, false );
            wp_register_script( 'wpmk-jspdf-min', WPMK_PDF_ASSETS . 'js/jspdf.min.js', array(), WPMK_PDF_VERSION, false );
            wp_register_script( 'wpmk-jhtml2canvas', WPMK_PDF_ASSETS . 'js/html2canvas.min.js', array(), WPMK_PDF_VERSION, false );
            wp_register_script( 'wpmk-html2pdf-min', WPMK_PDF_ASSETS . 'js/html2pdf.min.js', array(), WPMK_PDF_VERSION, false );
            
            wp_enqueue_script( 'jquery' );
            wp_enqueue_script( 'wpmk-es6-promise-auto-min' );
            wp_enqueue_script( 'wpmk-jspdf-min' );
            wp_enqueue_script( 'wpmk-jhtml2canvas' );
            wp_enqueue_script( 'wpmk-html2pdf-min' );
        } 
        
        /**
         * Here We are register wpmk pdf
         * setting page
         */
        public function wpmk_pdf_setting_page(){
            add_menu_page( __( 'WPMK PDF', 'wpmk' ), __( 'WPMK PDF', 'wpmk' ), 'manage_options', 'wpmk-pdf-settings', array( $this, 'wpmk_pdf_menu_page' ), plugins_url('/assets/images/pdf-icon.png', __FILE__), 10);
        }
        
        /**
         * Here We are setting wpmk pdf setting page
         * where user handel there wpmk pdf option
         */
        public function wpmk_pdf_menu_page(){
            
            $this->wpmk_pdf_save_data();
            $options = get_option( 'wpmk_pdf_option' );
            $wpmk_pdf_btn_text = $options['wpmk-pdf-text'];
            echo '<h1>WPMK PDF Generator Settings</h1>';
            echo '<div class="wpmk-pdf-main"><form action="" method="post">';
            echo '<p>Shortcode <code> [wpmk_pdf_generate] </code> PHP Code <code> &lt;?php wpmk_pdf_generate(); ?&gt; </code>';
            $this->wpmk_pdf_title( 'Choose Your PDF Button Style' );
            $this->wpmk_pdf_radio_button( 'PDF Image Button', 'wpmk-pdf-style', 'btn-icon' );
            $this->wpmk_pdf_icon_radio_button();
            $this->wpmk_pdf_radio_button( 'PDF Text Button', 'wpmk-pdf-style', 'btn-text' );
            echo '<input type="text" name="wpmk-pdf-text" value="'.$wpmk_pdf_btn_text.'" placeholder="Enter Your Button Text here" />';
            
            $this->wpmk_pdf_title( 'Choose Paper' );
            $this->wpmk_pdf_radio_button( 'Portrait', 'wpmk-pdf-page-mode', 'portrait' );
            $this->wpmk_pdf_radio_button( 'Landscape', 'wpmk-pdf-page-mode', 'landscape' );

            $this->wpmk_pdf_title( 'Choose Paper Size' );
            $this->wpmk_pdf_radio_button( 'Page A3', 'wpmk-pdf-page-size', 'a3' );
            $this->wpmk_pdf_radio_button( 'Page A4', 'wpmk-pdf-page-size', 'a4' );
            $this->wpmk_pdf_radio_button( 'Page A5', 'wpmk-pdf-page-size', 'a5' );
            
            echo '<p><input type="submit" name="wpmk_pdf_save" id="wpmk_pdf_save" class="button button-primary" value="Save Settings"/></p></form></div>';
        }
        
        /**
         * Here We are setting section title
         */
        private function wpmk_pdf_title( $wpmk_title ){
            echo '<div class="wpmk-pdf-section-title">' . $wpmk_title . '</div>';
        }
        
        /**
         * Here We are setting radio button
         * for setting page
         */
        private function wpmk_pdf_radio_button( $wpmk_pdf_text , $wpmk_pdf_name, $wpmk_pdf_value ){
            echo '<div class="wpmk-pdf-btn-box"><div class="wpmk-pdf-input"><input type="radio" name="' . $wpmk_pdf_name . '" value="' . $wpmk_pdf_value . '" id="' . $wpmk_pdf_name . '"  '. $this->wpmk_pdf_radio_checked( $wpmk_pdf_value ) .'/></div>';
            echo '<div class="wpmk-pdf-label"><label>' . $wpmk_pdf_text . '</label></div></div>';
        }
        
        /**
         * Here We are setting radio button
         * for icon select on setting page
         */
        private function wpmk_pdf_icon_radio_button(){
            echo '<div class="wpmk-pdf-icon-set">';
            for ($wpmk_pdf = 1; $wpmk_pdf <= 6; $wpmk_pdf++) {
                echo '<div class="wpmk-pdf-icon-box">';
                echo '<label for="wpmk-pdf-icon-'. $wpmk_pdf .'"><img src="'. WPMK_PDF_ASSETS . 'images/button/pdf-' . $wpmk_pdf .'.jpg" width="25" /></label>';
                echo '<div class="btn-pdf-input"><input type="radio" name="wpmk-pdf-icon" value="pdf-'. $wpmk_pdf .'" id="wpmk-pdf-icon-'. $wpmk_pdf .'" '. $this->wpmk_pdf_radio_checked( 'pdf-' . $wpmk_pdf ) .' /></div></div>';
            }
            echo '</div>';
        }
        
        /**
         * Here We are chaking current radio
         * button value
         */
        private function wpmk_pdf_radio_checked( $checked_val ){
            
            $options = get_option( 'wpmk_pdf_option' );
            $checked = 'checked="checked"';
            
            $wpmk_pdf_btn_style    = $options['wpmk-pdf-style'];
            $wpmk_pdf_btn_icon     = $options['wpmk-pdf-icon'];
            $wpmk_pdf_page_mode    = $options['wpmk-pdf-page-mode'];
            $wpmk_pdf_page_size    = $options['wpmk-pdf-page-size'];
            
            if( $wpmk_pdf_btn_style == $checked_val )
                return $checked;
            
            if( $wpmk_pdf_btn_icon == $checked_val )
                return $checked;
            
            if( $wpmk_pdf_page_mode == $checked_val )
                return $checked;
            
            if( $wpmk_pdf_page_size == $checked_val )
                return $checked;
        }
        
        /**
         * Here We are saving setting
         * page data
         */
        private function wpmk_pdf_save_data(){
            
            if(isset($_POST['wpmk_pdf_save'])){
                $wpmk_pdf_btn_style    = sanitize_key ( $_POST['wpmk-pdf-style'] ); 
                $wpmk_pdf_btn_icon     = sanitize_key ( $_POST['wpmk-pdf-icon'] ); 
                $wpmk_pdf_btn_text     = sanitize_text_field( $_POST['wpmk-pdf-text'] ); 
                $wpmk_pdf_page_mode    = sanitize_key ( $_POST['wpmk-pdf-page-mode'] );
                $wpmk_pdf_page_size    = sanitize_key ( $_POST['wpmk-pdf-page-size'] );
                
                $wpmk_pdf_option['wpmk-pdf-style']     = $wpmk_pdf_btn_style;
                $wpmk_pdf_option['wpmk-pdf-icon']      = $wpmk_pdf_btn_icon;
                $wpmk_pdf_option['wpmk-pdf-text']      = $wpmk_pdf_btn_text;
                $wpmk_pdf_option['wpmk-pdf-page-mode'] = $wpmk_pdf_page_mode;
                $wpmk_pdf_option['wpmk-pdf-page-size'] = $wpmk_pdf_page_size;
                
                update_option( 'wpmk_pdf_option', $wpmk_pdf_option );
            }
        }
        
        /**
         * 
         * Here We are adding little style for
         * shortcode button
         * 
         */
        public function wpmk_pdf_style_frontend(){ ?>
            <style>
            #wpmk_pdf_generate_file{
                overflow: hidden;
                padding: 5px;
                cursor: pointer;
            }
            </style>
        <?php
        }
        
        /**
         * 
         * Here We are adding body class
         * that will be use in genrate Pdf
         * 
         */
        public function wpmk_pdf_add_body_class(){
            $classes[] = 'wpmk-page';
            return $classes;
        }
        
        /**
         * Here We are generating PDF
         */
        public function wpmk_pdf_generate_PDF(){ 
            global $post;
            $options = get_option( 'wpmk_pdf_option' );
            $wpmk_pdf_page_mode    = $options['wpmk-pdf-page-mode'];
            $wpmk_pdf_page_size    = $options['wpmk-pdf-page-size'];
        ?> 
            <script>            
                jQuery(document).ready(function($) {
                    
                    $( "#wpmk_pdf_generate_file" ).click(function() {
                        
                        $("#wpmk_pdf_generate_file").css("display", "none");
                        var element = document.getElementsByClassName('wpmk-page')[0];
                        var opt = {
                            margin:       1,
                            filename:     'wpmk-<?php echo $post->post_name; ?>' + '.pdf',
                            image:        { type: 'jpeg', quality: 0.98 },
                            html2canvas:  { scale: 2 },
                            jsPDF:        { unit: 'pt', format: '<?php echo $wpmk_pdf_page_size; ?>', orientation: '<?php echo $wpmk_pdf_page_mode; ?>' }
                        };
                        html2pdf().from(element).set(opt).save();
                        
                        setTimeout(function(){
                            $('#wpmk_pdf_generate_file').show();
                        }, 5000);
                        
                    });
                });
            </script>
        <?php
        }
        
        /**
         * Here We are creating shortcode
         */
        public function wpmk_pdf_shortcode(){ 
            ob_start();
            
            $options = get_option( 'wpmk_pdf_option' );
            
            $wpmk_pdf_btn_style = $options['wpmk-pdf-style'];
            $wpmk_pdf_btn_icon  = $options['wpmk-pdf-icon'];
            $wpmk_pdf_btn_text  = $options['wpmk-pdf-text'];
            
            echo '<a class="button" href="#" id="wpmk_pdf_generate_file">';
                if( $wpmk_pdf_btn_style == 'btn-icon' ){
                    echo '<img src="'. WPMK_PDF_ASSETS . 'images/button/' . $wpmk_pdf_btn_icon . '.jpg" width="25" />';
                }
                
                if( $wpmk_pdf_btn_style == 'btn-text' ){
                    echo $wpmk_pdf_btn_text;
                }
            echo '</a>';
            
            return ob_get_clean();
        }
    }
}
?>