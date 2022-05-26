<?php 
/*!
 * Jetpack CRM
 * https://jetpackcrm.com
 *
 * WooSync: Segment Condition: Is WooCommerce Customer
 *
 */

// block direct access
defined( 'ZEROBSCRM_PATH' ) || exit;

/**
 * WooSync: Segment Condition: Is WooCommerce Customer class
 */
class Segment_Condition_Woo_Customer extends zeroBSCRM_segmentCondition {

    public $key = 'is_woo_customer';
    public $condition = array(
        'name'      => 'WooCommerce Customer',
        'operators' => array( 'istrue', 'isfalse' ),
        'fieldname' =>'is_woo_customer'
    );
    public function conditionArg( $startingArg=false, $condition=false, $conditionKeySuffix=false ){
                
        global $zbs, $ZBSCRM_t;
        
            if ( $condition['operator'] == 'istrue' )
                return array('additionalWhereArr'=>        
                            array(
                                'is_woo_customer' . $conditionKeySuffix => array(
                                    'ID','IN',
                                    '(SELECT DISTINCT zbss_objid FROM ' . $ZBSCRM_t['externalsources'] . " WHERE zbss_objtype = ".ZBS_TYPE_CONTACT." AND zbss_source = %s)",
                                    array( 'woo' )
                                )
                            )
                        );
        
            if ( $condition['operator'] == 'isfalse' )
                return array('additionalWhereArr'=>        
                            array(
                                'is_woo_customer' . $conditionKeySuffix => array(
                                    'ID','NOT IN',
                                    '(SELECT DISTINCT zbss_objid FROM ' . $ZBSCRM_t['externalsources'] . " WHERE zbss_objtype = ".ZBS_TYPE_CONTACT." AND zbss_source = %s)",
                                    array( 'woo' )
                                )
                            )
                        );

        return $startingArg;
    }
    
}