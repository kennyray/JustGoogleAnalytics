<?php
/*
Plugin Name: Just Google Analytics
Plugin URI: http://phpblog.kennyray.com
Description: Allows you to add Google Analytics to your blog
Version: 1.0
Author: Kenny Ray
Author URI: http://phpblog.kennyray.com
License: MIT
*/
 
/*  MIT License

    Copyright (c) 2017 Kenny Ray

    Permission is hereby granted, free of charge, to any person obtaining a copy
    of this software and associated documentation files (the "Software"), to deal
    in the Software without restriction, including without limitation the rights
    to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
    copies of the Software, and to permit persons to whom the Software is
    furnished to do so, subject to the following conditions:

    The above copyright notice and this permission notice shall be included in all
    copies or substantial portions of the Software.

    THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
    IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
    FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
    AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
    LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
    OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
    SOFTWARE.
*/

define( 'JGA_DIR_PATH', plugin_dir_path( __FILE__ ) );
define( 'JGA_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

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
        register_activation_hook( JGA_DIR_PATH,  array( 'JustGoogleAnalytics', 'activate' ) );
        register_deactivation_hook( JGA_DIR_PATH,  array( 'JustGoogleAnalytics', 'deactivate' ) );



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
            wp_enqueue_script('jga-gacodescript', JGA_PLUGIN_URL . 'jga.js', array(), '1.0');
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

