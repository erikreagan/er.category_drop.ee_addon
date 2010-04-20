<?php
// ini_set('error_reporting',E_ALL);
/**
 * Category Drop
 * 
 * This file must be placed in the
 * /system/extensions/ folder in your ExpressionEngine installation.
 *
 * @package CategoryDrop
 * @version 1.0.0
 * @author Erik Reagan http://erikreagan.com
 * @copyright Copyright (c) 2010 Erik Reagan
 * @see http://github.com/erikreagan/er.category_drop.ee_addon
 */


if ( ! defined('EXT')) exit('Invalid file request');

define('CD_name', 'Category Drop');
define('CD_version', '1.0.0');
define('CD_underscores', 'Category_drop');

class Category_drop
{
   
   private  $settings       = array();

   public   $name           = CD_name;
   public   $version        = CD_version;
   public   $description    = 'Changes the category select element to a single dropdown select in the Publish form';
   public   $settings_exist = 'y';
   public   $docs_url       = '';



   /**
    * PHP4 Constructor
    *
    * @access   public
    * @see      __construct()
    */
   public function Category_drop($settings='')
   {
      $this->__construct($settings);
   }

   
   /**
    * PHP 5 Constructor
    *
    * @access  public
    * @param   array|string  Extension settings associative array or an empty string
    */
   public function __construct($settings='')
   {
      $this->settings = $settings;
   }


   /**
    * Configuration for the extension settings page
    *
    * @access     public
    * @return     array
    */
   public function settings()
   {
      global $DB, $LANG;
      
      // Get our weblog ids and titles
      $weblog_query = $DB->query("SELECT weblog_id,blog_title FROM exp_weblogs");
      $weblogs = $weblog_query->result;
      
      // Creat an array to be used in our settings multi-select
      foreach ($weblogs as $value) {
         $weblog_list[$value['weblog_id']] = $value['blog_title'];
      }
      
      $settings = array();
      
      $settings['weblog_id_list'] = array('ms',$weblog_list,'');
      
      return $settings;
   }
   
   
   
   /**
    * Activates the extension
    *
    * @access     public
    * @return     bool
    */
   public function activate_extension()
   {
      global $DB;

      $hooks = array(
         'show_full_control_panel_end' => 'show_full_control_panel_end'
      );

      foreach ($hooks as $hook => $method)
      {
         $sql[] = $DB->insert_string('exp_extensions',
            array(
               'extension_id' => '',
               'class'        => CD_underscores,
               'method'       => $method,
               'hook'         => $hook,
               'settings'     => '',
               'priority'     => 10,
               'version'      => CD_version,
               'enabled'      => "y"
            )
         );
      }

      // run all sql queries
      foreach ($sql as $query)
      {
         $DB->query($query);
      }
      
      return TRUE;
   }
   
   
   
   /**
    * Update the extension
    *
    * @access     public
    * @param      string
    * @return     bool
    */
   public function update_extension($current='')
   {
       global $DB;

       if ($current == '' OR $current == CD_version)
       {
           return FALSE;
       }

       $DB->query("UPDATE exp_extensions 
                   SET version = '".$DB->escape_str(CD_version)."' 
                   WHERE class = '".CD_underscores."'");
   }
   
   
   
   /**
    * Disables the extension the extension and deletes settings from DB
    * 
    * @access     public
    */
   public function disable_extension()
   {
       global $DB;
       $DB->query("DELETE FROM exp_extensions WHERE class = '".CD_underscores."'");
   }
   
   
   
   /**
    * Change out the form
    *
    * @access     public
    * @return     string
    */
   public function show_full_control_panel_end($out)
   {
      global $EXT, $IN;
      
      if($EXT->last_call !== FALSE)
      {
         $out = $EXT->last_call;
      }

      // If we're on the publish form or edit entry form
      if($IN->GBL('M') == 'entry_form' || $IN->GBL('M') == 'edit_entry')
      {
         // We only want to execute the code if we are in a weblog we selected in our extension settings
         if(in_array($IN->GBL('weblog_id'),$this->settings['weblog_id_list']))
         {
            // Regex to match the category multiselect box
            $out = preg_replace("/<select name='category\[\]' class='multiselect' size='\d' multiple='multiple' style='width:45%'/","<select name='category[]' size='1'",$out);
            // Change some javascript for the pop-up category editor
            $out = str_replace("document.getElementById('categorytree').innerHTML = str;","str = str.replace(/<select name='category\[\]' class='multiselect' size='\d' multiple='multiple' style='width:45%/,\"<select name='category[]' size='1'\");document.getElementById('categorytree').innerHTML = str;",$out);
         }
         
      }
      
      return $out;
      
   }
   
   
}
// END class

/* End of file ext.category_drop.php */
/* Location: ./system/extensions/ext.category_drop.php */