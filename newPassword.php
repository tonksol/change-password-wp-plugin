<?php
/*
* plugin name: New Password
* Description: After the first log in the user must create a new password. 
* Version: 1.0
* Author: Tonke Bult
* Author URI: http://tonkebult.nl 
* Text Domain: 
* 
*/

if ( ! defined( 'ABSPATH' ) ) exit; // Security: Exit if accessed directly


class NewPasswordPlugin {
    public function __construct() {
        // hook, array(class name, method name)
        add_action('wp_enqueue_scripts', array($this, 'loadResources'));
        add_action('admin_menu', array($this, 'addPage'));
       // add_action('wp_head', 'addInHeadForLogin');
        add_filter('the_content', array($this, 'addLoginContent'));
        add_action('admin_post_new_password_hook', array($this, 'formValidation'));
        
        add_action('init', array($this,'add_admin_menu')); // add a menu in the WordPress admin panal
    }

    function addInHeadForLogin() {
        return 
            '<link href="//cdnjs.cloudflare.com/ajax/libs/materialize/0.98.2/css/materialize.min.css" rel="stylesheet" id="bootstrap-css">
            <script src="//cdnjs.cloudflare.com/ajax/libs/materialize/0.98.2/js/materialize.min.js"></script>
            <script src="//code.jquery.com/jquery-1.11.1.min.js"></script>s';   
    }

    function loadResources() {
        wp_register_script('materializeScript', 'https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0-beta/js/materialize.min.js');
        wp_register_style('materializeStyle', 'https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0-beta/css/materialize.min.css');
        wp_register_style('materializeIcons', 'https://fonts.googleapis.com/icon?family=Material+Icons');
        wp_register_style('loginStyle', get_stylesheet_uri() . '/../../../plugins/newPassword/admin/css/loginStyle.css');
        wp_register_script('loginScript', get_template_directory_uri() . '/../../plugins/newPassword/admin/js/loginScript.js');
        if (is_page('Log In')) {
            wp_enqueue_script('materializeScript');
             wp_enqueue_style('materializeStyle');
             wp_enqueue_style('materializeIcons');
            wp_enqueue_style('loginStyle');
            wp_enqueue_script('loginScript');
        }
    }



    public function addPage() {
       $page_title = get_page_by_title('Log In');
       if (get_post_status($page_title->ID) != false && get_post_status($page_title->ID) != 'publish') {
            wp_insert_post(array('post_title' => 'Log In', 'post_status' => 'publish', 'post_type' => 'page', 'comment_status' => 'closed'), $wp_error = false);
        }
    }

    public function addLoginContent($content) {
        if (is_page('Log In')) {
            $content = 
            "
            <div class='row'>
            <form class='col s12' action='<?php echo esc_url(admin_url('admin-post.php')); ?>' method='POST'>
            <div class='row'> 
                <div class='input-field col s12'>
                <input id='email' type='text' class='validate' name='email'>
                <label for='email'>Email</label>
                </div>
            </div>
    
            <div class='row'>
                <div class='input-field col s12'>
                <input id='password' type='password' class='validate'>
                <label for='password'>Password</label>
                </div>
            </div>
            
            <button class='btn waves-effect waves-light' type='submit' name='action'>Submit
                <i class='material-icons right'>send</i>
            </button>
            </form>
        </div>
            ";
        }
        return $content;
    }

    function add_admin_menu() {
        register_post_type( 'cpt_newclients',
        // CPT Options
            array(
                'labels' => array(
                    'name' => __( 'New Clients' ),
                    'singular_name' => __( 'New client' )
                ),
                'public' => true,
                'has_archive' => true,
                'rewrite' => array('slug' => 'movies'),
            )
        );
    }


    function showNewClients( $user_id ){
        $args = [
            'post_type'      => 'product',
            'posts_per_page' => 10,
        ];
        $loop = new WP_Query($args);
        while ($loop->have_posts()) {
            $loop->the_post();
            ?>
            <div class="entry-content">
                <?php the_title(); ?>
                <?php the_content(); ?>
            </div>
            <?php
        }
    }

    // https://premium.wpmudev.org/blog/handling-form-submissions/
    public function formValidation() {
        if (isset($_POST['submit'])){
            htmlspecialchars(trim($_POST['email']));
        }
    }

    public function createNewClient() {
        $current_user = wp_get_current_user();
        if (user_can( $current_user, 'administrator' )) {
        // user is an admin
        }
    }

 } // ./ class NewPasswordPlugin





$plugin = new NewPasswordPlugin();