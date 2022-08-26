<?php
 /*
   Plugin Name: Woo Points
   description: Point system extention for woocommerce.
   Version: 1.0
   Author: Nikita Y.
   Author URI: https://github.com/solfon/
   License: GPL2
   */
   
class GlobalPoints {

 private $wpdb;
 
 public function __construct()
{
    global $wpdb;
    $this->wpdb = $wpdb;
}
 public function add_creds($log, $post_id, $user_id, $came_from_user_id, $creds, $type, $campaign_id) { 
 
   if (empty($log) || empty($post_id) || empty($user_id) || empty($came_from_user_id) || empty($creds) || empty($type)){exit;}
 
   if(!empty($campaign_id)){
 $campaignarray = $this->wpdb->get_results("SELECT * FROM `campaigns` WHERE `ID` = '". $campaign_id."'" );  
 if(!empty($campaignarray)){
 foreach($campaignarray as $campaigndata):
 $balanceartist = $campaigndata->priority;
 $date = $campaigndata->date;
 $paid = $campaigndata->paid;
 endforeach;
 // update admin about new important orders
   if( strtotime( $date ) < strtotime('-2 days') && $paid==1 ) {
   if(!empty($post_id)){
      $order_obj = wc_get_order($post_id);
      $order_status  = $order_obj->get_status();
      if($order_status!=='seeding' && $order_status!=='completed'){
      $order_obj->update_status('seeding');  
           }
       }
   }
     // if balance is negative
     if($balanceartist-$creds<=0){
  $campaignstatus = $this->wpdb->update( 'campaigns', array( 'status' => 'run out of points', 'priority' => $balanceartist-$creds ), array( 'ID' => $campaign_id ) );
           if(!empty($post_id)){
      $order_obj = wc_get_order($post_id);
      $order_obj->update_status('completed');  
       }
   } else {
   $this->wpdb->update( 'campaigns', array( 'priority' => $balanceartist-$creds ), array( 'ID' => $campaign_id ) );
          }
      }
          if($came_from_user_id!==1){
                    $this->wpdb->insert('my_points', array(
					'log' => $log,
					'post_id' => $post_id,
                    'user_id' => $came_from_user_id,
                    'creds' => $creds,
                    'type' => $type
				)); 
                }
          }
    }  
    public function get_user_points_count($user_id){
    $data = 'creds';
    $creds = $this->wpdb->get_results("SELECT SUM($data) AS 'result_value' FROM `my_points` WHERE `type`='comarketing' AND `user_id`='".$user_id."'");
    $balance = $creds[0]->result_value;
    return $balance;
    }
}

   ?>
