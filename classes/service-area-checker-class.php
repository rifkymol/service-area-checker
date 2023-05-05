
<?php 

class Service_Area_Checker {
    
    public function __construct() {
        add_action('admin_menu', array($this, 'add_service_area_checker_menu'));
        add_action('admin_init', array($this, 'service_area_checker_options_page_save'));
        add_action( 'wp_enqueue_scripts', array($this, 'service_area_checker_load_scripts'));
        
        add_shortcode('service-area-checker', array($this, 'service_area_checker_shortcode'));

    }
    
    public function add_service_area_checker_menu() {
        add_menu_page(
            'Service Area Checker Settings',
            'Service Area Checker',
            'manage_options',
            'service-area-checker',
            array($this, 'service_area_checker_settings_page')
        );
    }

    public function service_area_checker_load_scripts() {
        global $post;
        
        if (is_a($post, 'WP_Post') && has_shortcode($post->post_content, 'service-area-checker')){
            // load css
            wp_enqueue_style('leaflet-css', 	plugins_url('/service-area-checker/assets/css/leaflet.css'));
            wp_enqueue_style('leaflet-min-css', 	plugins_url('/service-area-checker/assets/css/leaflet.min.css'));
            wp_enqueue_style('leaflet-geocoder-css', 	plugins_url('/service-area-checker/assets/css/leaflet-geocoder-locationiq.min.css'));
            wp_enqueue_style('jquery-ui-css', 	plugins_url('/service-area-checker/assets/css/jquery-ui.css'));
            
            // load js
            wp_enqueue_script('leaflet-js', plugins_url('/service-area-checker/assets/js/leaflet.js'));
            wp_enqueue_script('leaflet-min-js', plugins_url('/service-area-checker/assets/js/leaflet.min.js'));
            wp_enqueue_script('leaflet-omnivore', plugins_url('/service-area-checker/assets/js/leaflet-omnivore.min.js'));
            wp_enqueue_script('leaflet-geocoder', plugins_url('/service-area-checker/assets/js/leaflet-geocoder-locationiq.min.js'));
            wp_enqueue_script('jquery-min-js', plugins_url('/service-area-checker/assets/js/jquery-3.6.0.min.js'));
            wp_enqueue_script('jquery-ui', plugins_url('/service-area-checker/assets/js/jquery-ui.min.js'));
        }
    }
    
    public function service_area_checker_shortcode() {

        require_once 'service-area-checker-view.php';

    }
    
    public function service_area_checker_settings_page() {
        $kml_url = get_option("kml_url");
        $url_inside_kml = esc_attr(get_option('url_inside_kml'));
        $url_outside_kml = esc_attr(get_option('url_outside_kml'));
        
        ?>
        <div class="wrap">
            <div>
                <h2>Service Area Checker Settings</h2>
                <h3>Configure the settings for Service Area Checker plugin</h3>
                <p>Generate page by using [service-area-checker] shortcode</p>
                <p>You can input the kml file by uploading or input the url file below</p>
            </div>
            <div>
                <form method="post" enctype="multipart/form-data">
                    <div>
                        <div>
                            <p><strong>Upload KML file</strong></p>
                            <input type="file" name="service_area_checker_file_upload" />
                        </div>
                        <div>
                            <p><strong>Input KML url</strong></p>
                            <input type="text" name="kml_url" class="regular-text">
                        </div>
                        <div>
                            <p><strong>Action URL Inside KML Zone</strong></p>
                            <input type="text" name="url_inside_kml" class="regular-text" value="<?php echo $url_inside_kml; ?>" required>
                        </div>
                        <div>
                            <p><strong>Action URL Outside KML Zone</strong></p>
                            <input type="text" name="url_outside_kml" class="regular-text" value="<?php echo $url_outside_kml; ?>" required>
                        </div>
                    </div>`
                    <div>
                        <strong>
                            <p>Current File : <?php echo $kml_url; ?></p>
                            <p style="color:red;">*if the map does not show kml zones, it means the file was not found</p>
                        </strong>
                    </div>
                    <div style="margin-top:50px">
                        <input type="submit" name="submit_service_checker" id="submit_service_checker" class="button button-primary" value="Save Changes" />
                    </div>
                </form>
            </div>
        </div>
        <?php
    }

    public function service_area_checker_options_page_save() {
        if (isset($_POST['submit_service_checker'])) {
            $url_inside = $_POST['url_inside_kml'];
            $url_outside = $_POST['url_outside_kml'];

            update_option('url_inside_kml', $url_inside);
            update_option('url_outside_kml', $url_outside);

            if (!empty($_POST['kml_url'])) {
                update_option('kml_url', $_POST['kml_url']);
            }else {
                if (isset($_FILES['service_area_checker_file_upload'])) {
                    $target_dir = wp_upload_dir()["basedir"] . "/server-checker/";
                    $target_file = $target_dir . basename($_FILES["service_area_checker_file_upload"]["name"]);
                    $file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
                    if ($file_type == "kml") {
                        if (!file_exists($target_dir)) {
                            mkdir($target_dir, 0777, true);
                        }
                        move_uploaded_file($_FILES["service_area_checker_file_upload"]["tmp_name"], $target_file);
                        $upload = wp_upload_dir();
                        $url = $upload['baseurl']."/server-checker/".$_FILES["service_area_checker_file_upload"]["name"];
                        if (get_option('kml_url')) {
                            update_option('kml_url', $url);
                        }else {
                            add_option("kml_url", $url, '' , 'no');
                        }
                    }
                }
            }
        }
    }
}