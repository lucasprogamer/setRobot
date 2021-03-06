<?php


namespace App;

class Coming
{
    /**
     *
     *
     * @var object
     */
    private static $instance = null;

    protected $coming_settings;

    /**
     * Constructor function
     */
    public function __construct() {
        $this->form_settings();

        add_action( 'admin_init', array( &$this, 'admin_init' ));
        add_action( 'admin_menu', array( &$this, 'admin_menu' ));

        if ($this->coming_settings['status'] === 'TRUE') {
            add_filter( 'login_message', array( &$this, 'login_message' ));
            add_action( 'admin_notices', array( &$this, 'admin_notices' ));
            add_action( 'wp_loaded', array( &$this, 'apply_coming_mode' ));
            add_filter( 'wp_title', array( &$this, 'wpTitle' ), 9999, 2 );
        }
    }
    /**
     *
     */
    public function form_settings() {
        if( false == get_option( 'coming_settings' )) {
            add_option( 'coming_settings' );
            $default = array(
                'status'           => FALSE,
                'description'      => '',
                'time_activated'   => '',
                'duration_days'    => '',
                'duration_hours'   => '',
                'duration_minutes' => '',
                'url_allowed'      => '',
                'role_allow_front' => '',
                'role_allow_back'  => ''
            );
            update_option( 'coming_settings', $default );
        }
        $this->coming_settings = get_option( 'coming_settings' );
        if (!isset($this->coming_settings['status'])) $this->coming_settings['status'] = FALSE;
    }

    /**
     * Create page options
     */
    function admin_init(){
        add_settings_section(
            'section_coming',
            __('Configurações', 'wp-coming'),
            '__return_false',
            'wp-coming'
        );

        add_settings_field(
            'status',
            __('Habilitar Manutenção:', 'wp-coming'),
            array( &$this, 'html_input_status' ),
            'wp-coming',
            'section_coming'
        );

        add_settings_field(
            'description',
            __('Descrição:', 'wp-coming'),
            array( &$this, 'html_input_description' ),
            'wp-coming',
            'section_coming'
        );

        add_settings_field(
            'url_allowed',
            __('Paginas Liberadas:', 'wp-coming'),
            array( &$this, 'html_input_url_allowed' ),
            'wp-coming',
            'section_coming'
        );

        add_settings_field(
            'role_allow',
            __('Permitir Usuarios:', 'wp-coming'),
            array( &$this, 'html_input_role_allow' ),
            'wp-coming',
            'section_coming'
        );

        register_setting(
            'wp-coming',
            'coming_settings'
        );
    }

    /**
     *
     */
    public function html_input_status(){
        if ($this->coming_settings['status'] == TRUE) :
            $return    = $this->calc_time_coming();

            $message = sprintf( __("Modo de Manutenção Termina em  <strong>%s</strong>", 'wp-coming'), $return['return-date'] );
            echo ("<p><span class='description'>$message</span></p><br/>");
        endif;

        $days  = $this->coming_settings['status'] == TRUE ? $return['remaining-array']['days'] : '1';
        $hours = $this->coming_settings['status'] == TRUE ? $return['remaining-array']['hours'] : '0';
        $mins  = $this->coming_settings['status'] == TRUE ? $return['remaining-array']['mins'] : '0';
        ?>

        <input type="hidden" name="coming_settings[time_activated]" value="<?php echo current_time('timestamp'); ?>">

        <label>
            <input type="checkbox" id="status" name="coming_settings[status]" value="TRUE" <?php checked( 'TRUE', $this->coming_settings['status'] ) ?> /> <?php _e('Habilitar', 'wp-coming'); ?>
        </label>

        <br/>
        <table>
            <tbody>
                <tr>
                    <td><strong><?php _e('Retorno:'); ?></strong></td>
                    <td><input type="text" id="duration_days" name="coming_settings[duration_days]" value="<?php echo $days; ?>" size="4" maxlength="5"> <label for="duration_days"><?php _e('Dias', 'wp-coming'); ?></label></td>
                    <td><input type="text" id="duration_hours" name="coming_settings[duration_hours]" value="<?php echo $hours; ?>" size="4" maxlength="5"> <label for="duration_hours"><?php _e('Horas', 'wp-coming'); ?></label></td>
                    <td><input type="text" id="duration_minutes" name="coming_settings[duration_minutes]" value="<?php echo $mins; ?>" size="4" maxlength="5"> <label for="duration_minutes"><?php _e('Minutos', 'wp-coming'); ?></label></td>
                </tr>
            </tbody>
        </table>
        <?php
    }

    /**
     *
     */
    public function html_input_description(){
        $html = '<textarea id="description" name="coming_settings[description]" cols="80" rows="5" class="large-text">'.$this->coming_settings['description'].'</textarea>';
        echo $html;
    }

    /**
     *
     */
    public function html_input_url_allowed(){
        $html = '<textarea id="url_allowed" name="coming_settings[url_allowed]" cols="80" rows="5" class="large-text">'.$this->coming_settings['url_allowed'].'</textarea>';
        $html .= '<br/><span class="description">Digite os caminhos que devem estar acessíveis mesmo em modo de manutenção. Separe os vários caminhos com quebras de linha.<br/>Exemplo: Se você quer liberar acesso á pagina <strong>http://site.com/sobre/</strong>, você deve digitar <strong>/sobre/</strong>.<br/>Dica: Se você quiser liberar acesso a página inicial digite <strong>[HOME]</strong>.</span>';
        echo $html;
    }

    /**
     *
     */
    public function html_input_role_allow(){

        $html = '<label>'. __('Painel Administrativo:', 'wp-coming');
        $html .= ' <select id="role_allow_back" name="coming_settings[role_allow_back]">
                    <option value="manage_options" ' . selected( $this->coming_settings['role_allow_back'], 'manage_options', false) . '>Ninguém</option>
                    <option value="manage_categories" ' . selected( $this->coming_settings['role_allow_back'], 'manage_categories', false) . '>Editor</option>
                    <option value="publish_posts" ' . selected( $this->coming_settings['role_allow_back'], 'publish_posts', false) . '>Autor</option>
                    <option value="edit_posts" ' . selected( $this->coming_settings['role_allow_back'], 'edit_posts', false) . '>Colaborador</option>
                    <option value="read" ' . selected( $this->coming_settings['role_allow_back'], 'read', false) . '>Visitante</option>
                </select>';
        $html .= '</label><br />';


        $html .= '<label>'. __('Site:', 'wp-coming');
        $html .= ' <select id="role_allow_front" name="coming_settings[role_allow_front]">
                    <option value="manage_options" ' . selected( $this->coming_settings['role_allow_front'], 'manage_options', false) . '>Ninguém</option>
                    <option value="manage_categories" ' . selected( $this->coming_settings['role_allow_front'], 'manage_categories', false) . '>Editor</option>
                    <option value="publish_posts" ' . selected( $this->coming_settings['role_allow_front'], 'publish_posts', false) . '>Autor</option>
                    <option value="edit_posts" ' . selected( $this->coming_settings['role_allow_front'], 'edit_posts', false) . '>Colaborador</option>
                    <option value="read" ' . selected( $this->coming_settings['role_allow_front'], 'read', false) . '>Visitante</option>
                </select>';
        $html .= '</label><br />';
        echo $html;
    }


    /**
     *
     */
    function admin_menu(){
        add_submenu_page(
            'options-general.php',
            __('Manutenção', 'wp-coming'),
            __('Manutenção', 'wp-coming'),
            'administrator',
            'wp-coming',
            array( &$this, 'html_form_settings' )
        );
    }

    /**
     *
     */
    public function html_form_settings(){
    ?>
        <div class="wrap">
            <div id="icon-options-general" class="icon32"></div>
            <h2><?php _e('General Settings'); ?></h2>
            <form method="post" action="options.php">
                <?php
                settings_fields( 'wp-coming' );
                do_settings_sections( 'wp-coming' );
                submit_button();
                ?>
            </form>
        </div>
    <?php
    }

    /**
     *
     */
    public function calc_time_coming(){
        // How long will it stay off in seconds
        $time_duration = 0;
        $time_duration += intval($this->coming_settings['duration_days']) * 24 * 60;
        $time_duration += intval($this->coming_settings['duration_hours']) * 60;
        $time_duration += intval($this->coming_settings['duration_minutes']);
        $time_duration = intval($time_duration * 60);

        // Timestamp of time activated, time finished, time current e time remaining
        $time_activated = intval($this->coming_settings['time_activated']);
        $time_finished  = intval($time_activated + $time_duration);
        $time_current   = current_time('timestamp');
        $time_remaining = $time_finished - $time_current;

        // Format the date in the format defined by the system
        $return_day  = date_i18n( get_option('date_format'), $time_finished );
        $return_time = date_i18n( get_option('time_format'), $time_finished );
        $return_date = $return_day . ' ' . $return_time;

        $time_calculated = $this->calc_separate_time($time_remaining);

        return array(
            'return-date'       => $return_date,
            'remaining-seconds' => $time_remaining,
            'remaining-array'   => $time_calculated,
        );
    }


    /**
     * Calculates the days, hours and minutes remaining based on the number of seconds
     *
     * @return array Array containing the values of days, hours and minutes remaining
     */
    private function calc_separate_time($seconds){
        $minutes = round(($seconds/(60)), 0);

        $minutes = intval($minutes);
        $vals_arr = array(  'days' => (int) ($minutes / (24*60) ),
                            'hours' => $minutes / 60 % 24,
                            'mins' => $minutes % 60);
        $return_arr = array();
        $is_added = false;
        foreach ($vals_arr as $unit => $amount) {
            $return_arr[$unit] = 0;

            if ( ($amount > 0) || $is_added ) {
                $is_added          = true;
                $return_arr[$unit] = $amount;
            }
        }
        return $return_arr;
    }

    /**
     *
     */
    function apply_coming_mode()
    {
        if ( strstr($_SERVER['PHP_SELF'],'wp-login.php')) return;
        if ( strstr($_SERVER['PHP_SELF'], 'wp-admin/admin-ajax.php')) return;
        if ( strstr($_SERVER['PHP_SELF'], 'async-upload.php')) return;
        if ( strstr(htmlspecialchars($_SERVER['REQUEST_URI']), '/plugins/')) return;
        if ( strstr($_SERVER['PHP_SELF'], 'upgrade.php')) return;
        if ( $this->check_url_allowed()) return;

        //Never show coming page in wp-admin
        if ( is_admin() || strstr(htmlspecialchars($_SERVER['REQUEST_URI']), '/wp-admin/') ) {
            if ( !is_user_logged_in() ) {
                auth_redirect();
            }
            if ( $this->user_allow('admin') ) {
                return;
            } else {
                $this->display_coming_page();
            }
        } else {
            if( $this->user_allow('public') ) {
                return;
            } else {
                $this->display_coming_page();
            }
        }
    }


    /**
     * display maintence page
     */
    function display_coming_page()
    {
        $time_coming = $this->calc_time_coming();
        $time_coming = $time_coming['remaining-seconds'];

        //Define header as unavailable
        header('HTTP/1.1 503 Service Temporarily Unavailable');
        header('Status: 503 Service Temporarily Unavailable');

        if ( $time_coming > 1 ) header('Retry-After: ' . $time_coming );

          wp_redirect(get_permalink( get_page_by_title( 'Maintenance' )->ID ));

        exit();
    }


    /**
     * check url
     */
    function check_url_allowed()
    {
        $urlarray = $this->coming_settings['url_allowed'];
        $urlarray = preg_replace("/\r|\n/s", ' ', $urlarray); //TRANSFORM BREAK LINES IN SPACE
        $urlarray = explode(' ', $urlarray); //TRANSFORM STRING IN ARRAY
        $oururl = 'http://' . $_SERVER['HTTP_HOST'] . htmlspecialchars($_SERVER['REQUEST_URI']);
        if (strpos($oururl, 'login') !== false) return true;
        if (strpos($oururl, 'maintenance') !== false) return true;
        foreach ($urlarray as $expath) {
            if (!empty($expath)) {
                $expath = str_replace(' ', '', $expath);
                if (strpos($oururl, $expath) !== false) return true;
                if ( (strtoupper($expath) == '[HOME]') && ( trailingslashit(get_bloginfo('url')) == trailingslashit($oururl) ) )    return true;
            }
        }
        return false;
    }


    /**
     * Create form on page option
     */
    function user_allow($where)
    {
        if ($where == 'public') {
            $optval = $this->coming_settings['role_allow_front'];
        } elseif ($where == 'admin') {
            $optval = $this->coming_settings['role_allow_back'];
        } else {
            return false;
        }

        if ( $optval == 'manage_options' && current_user_can('manage_options') ) { return true; }
        elseif ( $optval == 'manage_categories' && current_user_can('manage_categories') ) { return true; }
        elseif ( $optval == 'publish_posts' && current_user_can('publish_posts') ) { return true;   }
        elseif ( $optval == 'edit_posts' && current_user_can('edit_posts') ) { return true; }
        elseif ( $optval == 'read' && current_user_can('read') ) { return true; }
        else { return false; }
    }

    /**
     *
     */
    function login_message( $message ){
        $message = apply_filters( 'coming_loginnotice', __('Atualmente o site passa por um período de Manutenção.', 'wp-coming') );

        return '<div id="login_error"><p class="text-center">'. $message .'</p></div>';
    }

    /**
     *
     */
    function admin_notices()
    {
        $edit_url = site_url() . '/wp-admin/admin.php?page=wp-coming';

        $message1 = __('Site em Manutenção.', 'wp-coming');
        $message2 = sprintf( __('Para remover <a href="%s">Clique aqui</a>.', 'wp-coming'), $edit_url);

        $message = apply_filters( 'coming_adminnotice', $message1. ' '. $message2 );

        echo '<div id="message" class="error"><p>'. $message .'</p></div>';
    }

    /**
     *
     */
    function wpTitle()
    {
        return get_bloginfo( 'name' ). ' | Modo Manutenção';
    }

}
