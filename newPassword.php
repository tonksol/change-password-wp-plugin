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

// use return error_log() for debugging
if ( ! defined( 'ABSPATH' ) ) exit; // Security: Exit if accessed directly


class NewPasswordPlugin {
    public function __construct() {
        // hook, array(class name, method name)
        add_action('wp_enqueue_scripts', array($this, 'loadResources'));
        add_action('admin_menu', array($this, 'addPage'));
        add_filter('the_content', array($this, 'addLoginContent'));
        add_action('admin_post_new_password_hook', array($this, 'formValidation'));
        
        // hooks for admin
        add_action('init', array($this,'add_newclients_to_admin')); // add a menu in the WordPress admin panal
        add_filter('manage_cpt_newclients_posts_columns' , array($this,'add_columns_to_newclients'));
        add_action( 'manage_cpt_newclients_posts_custom_column' , array($this, 'read_columns'), 10, 2);
        add_action('add_meta_boxes', array($this, 'add_metaboxes_newclients'));
        add_action('save_post', array($this, 'save_newclient_meta_data'));
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

/* 
------------------------
    ADMIN
------------------------
*/
    // Create Custom Post Type (CPT) New Clients
    public function add_newclients_to_admin() {
        register_post_type( 'cpt_newclients',
            array(
                'labels' => array(
                    'name' => __('New Clients'),
                    'singular_name' => __('New client'),
                    'add_new' => __('Add Client'),
                    'add_new_item' => __('Add New Client'),
                    'edit_item' => __('Edit Client'),
                    'new_item' => __('New Client'),
                    'view_item' => __('View Client'),
                    'view_items' => __('View Clients'),
                    'search_items' => __('Search Clients'),
                    'not_found' => __('No Clients found'),
                    'not_found_in_trash' => __('No Clients found in Trash'),
                    'all_items' => __('All Clients'),
                    'archives' => __('Client Archives'),
                    'attributes' => __('Client Attributes')       
                ),
                'public' => true,
                'has_archive' => true,
                'rewrite' => array('slug' => 'New Clients'),
                'menu_position' => 100,
                'supports' => false
            )
        );
    }

    // Add the columns for the CPT
    public function add_columns_to_newclients() {
        return array(
            'cb' => '<input type="checkbox" />',
            'firstname' => __('First Name'),
            'prefix' => __('Prefix'),
            'lastname' => __('Last Name'),
            'email' => __('Email'),
            'password' =>__( 'Password'),
            'redirect' => __('Redirect to'),
            'date' => __('Date')
        );
    }

    // Displays the data in the columns
    public function read_columns($column, $post_id) {
        switch ( $column ) {
            case 'firstname':
                $fname = get_post_meta($post_id, 'firstname', true);
                echo sanitize_text_field($fname);
                break;
            case 'prefix':
                $prefix = get_post_meta( $post_id, 'prefix', true ); 
                echo sanitize_text_field($prefix);
                break;
            case 'lastname':
                $lname = get_post_meta( $post_id, 'lastname', true ); 
                echo sanitize_text_field($lname);
                break;
            case 'email':
                $email = get_post_meta( $post_id, 'email', true ); 
                echo sanitize_text_field($email);
                break;
            case 'password':
                $password = get_post_meta( $post_id, 'password', true); 
                echo sanitize_text_field($password);
                break;
            case 'redirect':
                $redirect = get_post_meta( $post_id, 'redirect', true );
                echo sanitize_text_field($redirect); 
                break;
            case 'date':
                $date = get_post_meta( $post_id, 'date', true ); 
                echo sanitize_text_field($date);
                break;
        }
    }

    // Add a metabox, it will show after you have clicked on "Add client".
    public function add_metaboxes_newclients() {
        $screens = ['post', 'cpt_newclients'];
        foreach ($screens as $screen) {
            add_meta_box(
                'newclients_box_id',                    // Unique ID
                'Add Client Info',                      // Box title
                array($this,'newclients_box_html'),     // Content callback, function below
                $screen                                 // Post type
            );
        }
    }

    // Add content, in this case the input fields, to the metabox. 
    public function newclients_box_html() {
        ?>
        <input type="text" name="firstname" placeholder="First Name"> 
        <br><br>
        <input type="text" name="prefix" placeholder="Prefix"> 
        <br><br>
        <input type="text" name="lastname" placeholder="Last Name"> 
        <br><br>
        <input type="text" name="email" placeholder="Email">
        <br><br>
        <input type="text" name="password" placeholder="Password"> 
        <br><br>
        <?php
    }


     //$update Whether this is an existing post being updated or not.
    
    // Save the input data. 
    public function save_newclient_meta_data($post_id) {
        $post_type = get_post_type($post_id);
        
        if ( "cpt_newclients" != $post_type ) return; // If this isn't a post for 'cpt_newclients', don't update it.

        if ( isset( $_POST['firstname'] ) ) {
            update_post_meta($post_id, 'firstname', sanitize_text_field( $_POST['firstname']));
        }
        if ( isset( $_POST['prefix'] ) ) {
            update_post_meta($post_id, 'prefix', sanitize_text_field( $_POST['prefix']));
        }
        if ( isset( $_POST['lastname'] ) ) {
            update_post_meta($post_id, 'lastname', sanitize_text_field( $_POST['lastname']));
        }
        if ( isset( $_POST['email'] ) && is_email($_POST['email'])) {
            update_post_meta($post_id, 'email', sanitize_text_field( $_POST['email']));
        } elseif (isset( $_POST['email'] ) && is_email($_POST['email']) == false) {
            // update_post_meta($post_id, 'email', sanitize_text_field("Not an email"));
            wp_die("not an email");
        }
        if ( isset( $_POST['password'] ) ) {
            update_post_meta( $post_id, 'password', sanitize_text_field( $_POST['password']));
        }
        if ( isset( $_POST['redirect'] ) ) {
            update_post_meta( $post_id, 'redirect', sanitize_text_field( $_POST['redirect']));
        }
    }

    // https://premium.wpmudev.org/blog/handling-form-submissions/
    public function formValidation() {
        if (isset($_POST['submit'])){
            htmlspecialchars(trim($_POST['email']));
        }
    }


 } // ./ class NewPasswordPlugin





$plugin = new NewPasswordPlugin();