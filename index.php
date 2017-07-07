<?php
/*
Plugin Name: Just Google Analytics
Plugin URI: http://phpblog.kennyray.com
Description: Allows you to add Google Analytics to your blog
Version: 1.0
Author: Kenny Ray
Author URI: http://phpblog.kennyray.com
License: GPL2
*/
 
/*  Copyright 2017  Kenny Ray  (email : kenny@kennyray.com)
 
    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.
 
    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.
 
    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

define( 'DIR_PATH', plugin_dir_path( __FILE__ ) );
define( 'PLUGIN_URL', plugin_dir_url( __FILE__ ) );

class JustGoogleAnalytics
{
    private  $message = "";

    public function __construct()
    {
        add_action('init', array($this, 'init'));
        add_action( 'wp_enqueue_scripts', array($this, 'AddGACodeToHead') );
    }

    public function init()
    {
        add_action('admin_menu', array($this, 'add_menu_item'));
        register_activation_hook( DIR_PATH,  array( 'JustGoogleAnalytics', 'activate' ) );
        register_deactivation_hook( DIR_PATH,  array( 'JustGoogleAnalytics', 'deactivate' ) );



    }

    public static function activate()
    {

    }

    public static function deactivate()
    {
        delete_option('jga_gacode');
    }

    public function add_menu_item()
    {
       add_menu_page('Google Analytics', 'Google Analytics', 'manage_options', 'google-analytics', array($this,'google_analytics_code_entry_page'), '/wp-admin/images/icon-people.png');
    }


    public function google_analytics_code_entry_page()
    {
        $this->CheckForPost();

        $jga_gacode = $this->GetGACode();

        echo $this->FormContent($jga_gacode);

    }

    private function CheckForPost()
    {
        if(isset($_POST['jga_gacode']))
        {
            $this->SaveCode($_POST['jga_gacode']);
            $this->SetMessage();
        }
    }

    private function FormContent($jga_gacode = "YOUR_CODE_HERE")
    {
        $html = "";
        $html .= '<form action="' . $_SERVER['REQUEST_URI'] . '" name="jga-form" method="post">';
        $html .= '<h2>Google Analytics Tracking Code</h2>';
        $html .= 'Tracking Code: <input type="text" name="jga_gacode" value="' . $jga_gacode . '" id="jga_gacode"> <input type="submit" value="Save"> ';
        $html .= $this->message;
        $html .= '</form>';
        $html .= '<p>The following code will be inserted before the closing &lt;/head&gt; tag:</p>';
        $html .= '<pre>&lt;script&gt;
    (function(i,s,o,g,r,a,m){i[\'GoogleAnalyticsObject\']=r;i[r]=i[r]||function(){
      (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
      m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
      })(window,document,\'script\',\'https://www.google-analytics.com/analytics.js\',\'ga\');
      ga(\'create\', \'' . $jga_gacode .  '\', \'auto\');
      ga(\'send\', \'pageview\');
&lt;/script&gt;</pre>';


        return $html;
    }

    private function SetMessage()
    {
        $this->message = "<span style='color:darkgreen;'>Code saved</span>";
    }

    private function SaveCode($value)
    {
        update_option('jga_gacode', sanitize_text_field($value));
    }

    public function AddGACodeToHead()
    {   $jga_code = $this->GetGACode();
        if($jga_code) {
            wp_enqueue_script('jga-gacodescript', PLUGIN_URL . 'jga.js', array(), '1.0');
            wp_add_inline_script('jga-gacodescript', ' (function(i,s,o,g,r,a,m){i[\'GoogleAnalyticsObject\']=r;i[r]=i[r]||function(){
      (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
      m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
      })(window,document,\'script\',\'https://www.google-analytics.com/analytics.js\',\'ga\');
      ga(\'create\', \'' . $jga_code . '\', \'auto\');
      ga(\'send\', \'pageview\');');
        }
    }

    /**
     * @return mixed
     */
    private function GetGACode()
    {
        return get_option('jga_gacode', '');
    }
}

$jga = new JustGoogleAnalytics();

