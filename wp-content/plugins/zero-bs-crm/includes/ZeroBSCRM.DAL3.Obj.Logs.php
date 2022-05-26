<?php 
/*!
 * Jetpack CRM
 * https://jetpackcrm.com
 * V3.0+
 *
 * Copyright 2020 Automattic
 *
 * Date: 14/01/19
 */

/* ======================================================
  Breaking Checks ( stops direct access )
   ====================================================== */
    if ( ! defined( 'ZEROBSCRM_PATH' ) ) exit;
/* ======================================================
  / Breaking Checks
   ====================================================== */



/**
* ZBS DAL >> Logs
*
* @author   Woody Hayday <hello@jetpackcrm.com>
* @version  2.0
* @access   public
* @see      https://jetpackcrm.com/kb
*/
class zbsDAL_logs extends zbsDAL_ObjectLayer {

    protected $objectType = ZBS_TYPE_LOG;
    protected $objectModel = array(

        // ID
        'ID' => array('fieldname' => 'ID', 'format' => 'int'),

        // site + team generics
        'zbs_site' => array('fieldname' => 'zbs_site', 'format' => 'int'),
        'zbs_team' => array('fieldname' => 'zbs_team', 'format' => 'int'),
        'zbs_owner' => array('fieldname' => 'zbs_owner', 'format' => 'int'),

        // other fields
        'objtype' => array('fieldname' => 'zbsl_objtype', 'format' => 'int'),
        'objid' => array('fieldname' => 'zbsl_objid', 'format' => 'int'),
        'type' => array('fieldname' => 'zbsl_type', 'format' => 'str'),
        'shortdesc' => array('fieldname' => 'zbsl_shortdesc', 'format' => 'str'),
        'longdesc' => array('fieldname' => 'zbsl_longdesc', 'format' => 'str'),
        'created' => array('fieldname' => 'zbsl_created', 'format' => 'uts'),
        'lastupdated' => array('fieldname' => 'zbsl_lastupdated', 'format' => 'uts'),
        
        );



    /**
     * Returns log types which count as 'contact'
     * (These effect what updates 'last contacted' field against customer and 'latest contact log')
     * - For now hard typed  
     */
    public $contactLogTypes = array('Call','Email','Mail','Meeting','Feedback','Invoice: Sent','Quote: Sent');


    function __construct($args=array()) {


        #} =========== LOAD ARGS ==============
        $defaultArgs = array(

            //'tag' => false,

        ); foreach ($defaultArgs as $argK => $argV){ $this->$argK = $argV; if (is_array($args) && isset($args[$argK])) {  if (is_array($args[$argK])){ $newData = $this->$argK; if (!is_array($newData)) $newData = array(); foreach ($args[$argK] as $subK => $subV){ $newData[$subK] = $subV; }$this->$argK = $newData;} else { $this->$argK = $args[$argK]; } } }
        #} =========== / LOAD ARGS =============


    }


    // ===============================================================================
    // ===========   LOGS  ===========================================================

    // generic get Company (by ID)
    // Super simplistic wrapper used by edit page etc. (generically called via dal->contacts->getSingle etc.)
    public function getSingle($ID=-1){

        return $this->getLog(array('id'=>$ID));

    }
    

    /**
     * returns full Log line +- details
     *
     * @param array $args   Associative array of arguments
     *                      key, fullDetails, default
     *
     * @return array result
     */
    public function getLog($args=array()){

        #} =========== LOAD ARGS ==============
        $defaultArgs = array(

            'id' => -1,

            'incMeta'   => false,

            // permissions
            'ignoreowner'   => zeroBSCRM_DAL2_ignoreOwnership(ZBS_TYPE_LOG), // this'll let you not-check the owner of obj

            // returns scalar ID of line
            'onlyID'        => false

        ); foreach ($defaultArgs as $argK => $argV){ $$argK = $argV; if (is_array($args) && isset($args[$argK])) {  if (is_array($args[$argK])){ $newData = $$argK; if (!is_array($newData)) $newData = array(); foreach ($args[$argK] as $subK => $subV){ $newData[$subK] = $subV; }$$argK = $newData;} else { $$argK = $args[$argK]; } } }
        #} =========== / LOAD ARGS =============
            
        #} ========== CHECK FIELDS ============
        
            // check id
            $id = (int)$id;
            if (empty($id) || $id <= 0) return false;

        #} ========= / CHECK FIELDS ===========
        
        #} Check key
        if (!empty($id)){

            global $ZBSCRM_t,$wpdb; 
            $wheres = array('direct'=>array()); $whereStr = ''; $additionalWhere = ''; $params = array(); $res = array();

            #} Build query
            $query = "SELECT * FROM ".$ZBSCRM_t['logs'];

            #} ============= WHERE ================

                #} Add ID
                $wheres['ID'] = array('ID','=','%d',$id);

            #} ============ / WHERE ==============

            #} Build out any WHERE clauses
            $wheresArr = $this->buildWheres($wheres,$whereStr,$params);
            $whereStr = $wheresArr['where']; $params = $params + $wheresArr['params'];
            #} / Build WHERE

            #} Ownership v1.0 - the following adds SITE + TEAM checks, and (optionally), owner
            $params = array_merge($params,$this->ownershipQueryVars($ignoreowner)); // merges in any req.
            $ownQ = $this->ownershipSQL($ignoreowner); if (!empty($ownQ)) $additionalWhere = $this->spaceAnd($additionalWhere).$ownQ; // adds str to query
            #} / Ownership

            #} Append to sql (this also automatically deals with sortby and paging)
            $query .= $this->buildWhereStr($whereStr,$additionalWhere) . $this->buildSort('ID','DESC') . $this->buildPaging(0,1);


            try {

                #} Prep & run query
                $queryObj = $this->prepare($query,$params);
                $potentialRes = $wpdb->get_row($queryObj, OBJECT);

            } catch (Exception $e){

                #} General SQL Err
                $this->catchSQLError($e);

            }

            #} Interpret Results (ROW)
            if (isset($potentialRes) && isset($potentialRes->ID)) {

                #} Has results, tidy + return 
                
                    #} Only ID? return it directly
                    if ($onlyID === true) return $potentialRes->ID;

                    if ($incMeta) $potentialRes->meta = $this->DAL()->getMeta(array(

                                                            'objtype' => ZBS_TYPE_LOG,
                                                            'objid' => $potentialRes->ID,
                                                            'key' => 'logmeta',
                                                            'fullDetails' => false,
                                                            'default' => -1

                                                        ));

                    return $this->tidy_log($potentialRes);

            }

        } // / if ID

        return $default;

    }


    /**
     * returns log lines for an obj
     *
     * @param array $args Associative array of arguments
     *              searchPhrase, sortByField, sortOrder, page, perPage
     *
     * @return array of tag lines
     */
    public function getLogsForObj($args=array()){

        #} ============ LOAD ARGS =============
        $defaultArgs = array(

            'objtype' => -1,
            'objid' => -1,

            'searchPhrase'  => '', // specify to search through all note descs
            'notetype' => -1, // specify a permified type to grab collection
            'notetypes' => -1, // specify an array of permified types to grab collection

            // return
            'incMeta'   => false,

            'sortByField'   => 'ID',
            'sortOrder'     => 'ASC',
            'page'          => 0,
            'perPage'       => 100,

            // permissions
            'ignoreowner'   => zeroBSCRM_DAL2_ignoreOwnership(ZBS_TYPE_LOG) // this'll let you not-check the owner of obj

        ); foreach ($defaultArgs as $argK => $argV){ $$argK = $argV; if (is_array($args) && isset($args[$argK])) {  if (is_array($args[$argK])){ $newData = $$argK; if (!is_array($newData)) $newData = array(); foreach ($args[$argK] as $subK => $subV){ $newData[$subK] = $subV; }$$argK = $newData;} else { $$argK = $args[$argK]; } } }
        #} =========== / LOAD ARGS =============

        #} ========== CHECK FIELDS ============

            // ID
            $objid = (int)$objid;
            if (empty($objid) || $objid <= 0) return false;

            // This was returning false when $objid was 0 (or -1)
            if ($objid <  0) return false;

            // check obtype is legit..
            $objtype = (int)$objtype;
            if (!isset($objtype) || $objtype == -1 || $this->DAL()->objTypeKey($objtype) === -1) return false;
        
        #} ========= / CHECK FIELDS ===========

        global $ZBSCRM_t,$wpdb; 
        $wheres = array('direct'=>array()); $whereStr = ''; $additionalWhere = ''; $params = array(); $res = array();

        #} Build query
        $query = "SELECT * FROM ".$ZBSCRM_t['logs'];

        #} ============= WHERE ================

            #} objid
            if (!empty($objid) && $objid > 0) $wheres['zbsl_objid'] = array('zbsl_objid','=','%d',$objid);

            #} objtype
            if (!empty($objtype) && $objtype !== -1) $wheres['zbsl_objtype'] = array('zbsl_objtype','=','%d',$objtype);

            #} notetype
            if (!empty($notetype) && $notetype !== -1) $wheres['zbsl_type'] = array('zbsl_type','=','%s',$notetype);

            #} notetypes
            if (is_array($notetypes) && count($notetypes) > 0){ 

                // Generate escaped csv, e.g. 'Call','Email'
                $notetypesStr = $this->build_csv($notetypes);

                // add it as a direct where clause to avoid double escaping
                $wheres['direct'][] = array('zbsl_type IN ('.$notetypesStr.')',array());


            }

            #} Search
            if (!empty($searchPhrase)) {
                $wheres['zbsl_shortdesc'] = array('zbsl_shortdesc','LIKE','%s','%'.$searchPhrase.'%');
                $wheres['zbsl_longdesc'] = array('zbsl_longdesc','LIKE','%s','%'.$searchPhrase.'%');
            }

        #} ============ / WHERE ===============

        #} Build out any WHERE clauses
        $wheresArr= $this->buildWheres($wheres,$whereStr,$params);
        $whereStr = $wheresArr['where']; $params = $params + $wheresArr['params'];
        #} / Build WHERE

        #} Ownership v1.0 - the following adds SITE + TEAM checks, and (optionally), owner
        $params = array_merge($params,$this->ownershipQueryVars($ignoreowner)); // merges in any req.
        $ownQ = $this->ownershipSQL($ignoreowner); if (!empty($ownQ)) $additionalWhere = $this->spaceAnd($additionalWhere).$ownQ; // adds str to query
        #} / Ownership

        #} Append to sql (this also automatically deals with sortby and paging)
        $query .= $this->buildWhereStr($whereStr,$additionalWhere) . $this->buildSort($sortByField,$sortOrder) . $this->buildPaging($page,$perPage);

        try {

            #} Prep & run query
            $queryObj = $this->prepare($query,$params);
            $potentialRes = $wpdb->get_results($queryObj, OBJECT);

        } catch (Exception $e){

            #} General SQL Err
            $this->catchSQLError($e);

        }

        #} Interpret results (Result Set - multi-row)
        if (isset($potentialRes) && is_array($potentialRes) && count($potentialRes) > 0) {

            #} Has results, tidy + return 
            foreach ($potentialRes as $resDataLine) {

                    if ($incMeta) $resDataLine->meta = $this->DAL()->getMeta(array(

                                                            'objtype' => ZBS_TYPE_LOG,
                                                            'objid' => $resDataLine->ID,
                                                            'key' => 'logmeta',
                                                            'fullDetails' => false,
                                                            'default' => -1

                                                        ));
                        
                    // tidy
                    $resArr = $this->tidy_log($resDataLine);

                    $res[] = $resArr;

            }
        }

        return $res;
    } 


    /**
     * returns log lines, ignoring obj/owner - added to infill a few funcs ms added, not sure where used (not core)
     * (zeroBS_searchLogs + allLogs)
     *
     * @param array $args Associative array of arguments
     *              searchPhrase, sortByField, sortOrder, page, perPage
     *
     * @return array of tag lines
     */
    public function getLogsForANYObj($args=array()){

        #} ============ LOAD ARGS =============
        $defaultArgs = array(

            'objtype' => -1, // optional 

            'searchPhrase'  => -1, // specify to search through all note descs
            'notetype' => -1, // specify a permified type to grab collection
            'notetypes' => -1, // specify an array of permified types to grab collection

            // return
            'incMeta'   => false,

            'sortByField'   => 'ID',
            'sortOrder'     => 'ASC',
            'page'          => 0,
            'perPage'       => 100,

            // permissions
            'ignoreowner'   => zeroBSCRM_DAL2_ignoreOwnership(ZBS_TYPE_LOG) // this'll let you not-check the owner of obj

        ); foreach ($defaultArgs as $argK => $argV){ $$argK = $argV; if (is_array($args) && isset($args[$argK])) {  if (is_array($args[$argK])){ $newData = $$argK; if (!is_array($newData)) $newData = array(); foreach ($args[$argK] as $subK => $subV){ $newData[$subK] = $subV; }$$argK = $newData;} else { $$argK = $args[$argK]; } } }
        #} =========== / LOAD ARGS =============

        #} ========== CHECK FIELDS ============

            // check obtype is legit
            $objtype = (int)$objtype;

        #} ========= / CHECK FIELDS ===========

        global $ZBSCRM_t,$wpdb; 
        $wheres = array('direct'=>array()); $whereStr = ''; $additionalWhere = ''; $params = array(); $res = array();

        #} Build query
        $query = "SELECT * FROM ".$ZBSCRM_t['logs'];

        #} ============= WHERE ================

            #} objtype
            if (!empty($objtype)) $wheres['zbsl_objtype'] = array('zbsl_objtype','=','%d',$objtype);

            #} notetype
            if (!empty($notetype) && $notetype != -1) $wheres['zbsl_type'] = array('zbsl_type','=','%s',$notetype);

            #} notetypes
            if (is_array($notetypes) && count($notetypes) > 0){ 
                $notetypesStr = '';
                foreach ($notetypes as $nt){ 
                    if (!empty($notetypesStr)) $notetypesStr .= ',';
                    $notetypesStr .= '"'.$nt.'"';
                }

                $wheres['zbsl_types'] = array('zbsl_type','IN','%s','('.$notetypesStr.')');
            }

            #} Search
            if (!empty($searchPhrase)) {
                $wheres['zbsl_shortdesc'] = array('zbsl_shortdesc','LIKE','%s','%'.$searchPhrase.'%');
                $wheres['zbsl_longdesc'] = array('zbsl_longdesc','LIKE','%s','%'.$searchPhrase.'%');
            }

        #} ============ / WHERE ===============

        #} Build out any WHERE clauses
        $wheresArr= $this->buildWheres($wheres,$whereStr,$params);
        $whereStr = $wheresArr['where']; $params = $params + $wheresArr['params'];
        #} / Build WHERE

        #} Ownership v1.0 - the following adds SITE + TEAM checks, and (optionally), owner
        $params = array_merge($params,$this->ownershipQueryVars($ignoreowner)); // merges in any req.
        $ownQ = $this->ownershipSQL($ignoreowner); if (!empty($ownQ)) $additionalWhere = $this->spaceAnd($additionalWhere).$ownQ; // adds str to query
        #} / Ownership

        #} Append to sql (this also automatically deals with sortby and paging)
        $query .= $this->buildWhereStr($whereStr,$additionalWhere) . $this->buildSort($sortByField,$sortOrder) . $this->buildPaging($page,$perPage);

        try {

            #} Prep & run query
            $queryObj = $this->prepare($query,$params);
            $potentialRes = $wpdb->get_results($queryObj, OBJECT);

        } catch (Exception $e){

            #} General SQL Err
            $this->catchSQLError($e);

        }

        #} Interpret results (Result Set - multi-row)
        if (isset($potentialRes) && is_array($potentialRes) && count($potentialRes) > 0) {

            #} Has results, tidy + return 
            foreach ($potentialRes as $resDataLine) {

                    if ($incMeta) $resDataLine->meta = $this->DAL()->getMeta(array(

                                                            'objtype' => ZBS_TYPE_LOG,
                                                            'objid' => $resDataLine->ID,
                                                            'key' => 'logmeta',
                                                            'fullDetails' => false,
                                                            'default' => -1

                                                        ));
                        
                    // tidy
                    $resArr = $this->tidy_log($resDataLine);

                    $res[] = $resArr;

            }
        }

        return $res;
    } 


    /**
     * Returns a count of logs (owned)
     *
     * @return int count
     */
    public function getLogCount($args=array()){

        #} ============ LOAD ARGS =============
        $defaultArgs = array(

            // Search/Filtering (leave as false to ignore)
            'withType'    => false, // will be str if used

            // permissions
            'ignoreowner'   => zeroBSCRM_DAL2_ignoreOwnership(ZBS_TYPE_LOG), // this'll let you not-check the owner of obj

        ); foreach ($defaultArgs as $argK => $argV){ $$argK = $argV; if (is_array($args) && isset($args[$argK])) {  if (is_array($args[$argK])){ $newData = $$argK; if (!is_array($newData)) $newData = array(); foreach ($args[$argK] as $subK => $subV){ $newData[$subK] = $subV; }$$argK = $newData;} else { $$argK = $args[$argK]; } } }
        #} =========== / LOAD ARGS =============

        $whereArr = array();

        if ($withType !== false && !empty($withType)) $whereArr['status'] = array('zbsl_type','=','%s',$withType);

        return $this->DAL()->getFieldByWHERE(array(
            'objtype' => ZBS_TYPE_LOG,
            'colname' => 'COUNT(ID)',
            'where' => $whereArr,
            'ignoreowner' => $ignoreowner));
        
    }


     /**
     * adds or updates a log object
     *
     * @param array $args Associative array of arguments
     *              id (not req.), owner (not req.) data -> key/val
     *
     * @return int line ID
     */
    public function addUpdateLog($args=array()){

        global $zbs,$ZBSCRM_t,$wpdb;

        #} ============ LOAD ARGS =============
        $defaultArgs = array(

            'id'            => -1,
            'owner'         => -1,

            // fields (directly)
            'data'          => array(

                'objtype'   => -1,
                'objid'     => -1,
                'type'      => '', // log type e.g. zbsOc1 (zbsencoded) or custom e.g. "ALARM"
                'shortdesc' => '',
                'longdesc'  => '',

                'meta'      => -1, // can be any obj which'll be stored in meta table :)

                'created'   => -1 // override date? :(
                
            )

        ); foreach ($defaultArgs as $argK => $argV){ $$argK = $argV; if (is_array($args) && isset($args[$argK])) {  if (is_array($args[$argK])){ $newData = $$argK; if (!is_array($newData)) $newData = array(); foreach ($args[$argK] as $subK => $subV){ $newData[$subK] = $subV; }$$argK = $newData;} else { $$argK = $args[$argK]; } } }
        #} =========== / LOAD ARGS ============


        #} ========== CHECK FIELDS ============

            $id = (int)$id;

            // if owner = -1, add current
            if (!isset($owner) || $owner === -1) $owner = zeroBSCRM_user();

            // check obtype is legit
            $data['objtype'] = (int)$data['objtype'];
            if (!isset($data['objtype']) || $data['objtype'] == -1 || $this->DAL()->objTypeKey($data['objtype']) === -1) return false;

            // check id present + legit
            $data['objid'] = (int)$data['objid'];
            if (empty($data['objid']) || $data['objid'] <= 0) return false;

            // check type not empty
            if (empty($data['type'])) return false;

        #} ========= / CHECK FIELDS ===========

        $dataArr = array( 

                            // ownership
                            // no need to update these (as of yet) - can't move teams etc.
                            //'zbs_site' => zeroBSCRM_installSite(),
                            //'zbs_team' => zeroBSCRM_installTeam(),
                            'zbs_owner' => $owner,

                            // fields
                            'zbsl_objtype' => $data['objtype'],
                            'zbsl_objid' => $data['objid'],
                            'zbsl_type' => $data['type'],
                            'zbsl_shortdesc' => $data['shortdesc'],
                            'zbsl_longdesc' => $data['longdesc'],
                            'zbsl_lastupdated' => time()
                        );

        $dataTypes = array( // field data types
                            '%d',

                            '%d',
                            '%d',
                            '%s', 
                            '%s',
                            '%s', 
                            '%d'
                        );

            if (isset($data['created']) && !empty($data['created']) && $data['created'] !== -1){
                $dataArr['zbsl_created'] = $data['created']; $dataTypes[] = '%d';
            }


        if (isset($id) && !empty($id) && $id > 0){

                #} Check if obj exists (here) - for now just brutal update (will error when doesn't exist)

                #} Attempt update
                if ($wpdb->update( 
                        $ZBSCRM_t['logs'], 
                        $dataArr, 
                        array( // where
                            'ID' => $id
                            ),
                        $dataTypes,
                        array( // where data types
                            '%d'
                            )) !== false){

                            // any meta
                            if (isset($data['meta']) && $data['meta'] !== -1){

                                // brutal
                                $this->DAL()->updateMeta(ZBS_TYPE_LOG,$id,'logmeta',$data['meta']);

                            }
                
                            #} Internal Automator
                            if (!empty($id)){

                                zeroBSCRM_FireInternalAutomator('log.update',array(
                                    'id'=>$id,
                                    'logagainst'=>$data['objid'],
                                    'logagainsttype'=>$data['objtype'],
                                    'logtype'=> $data['type'],
                                    'logshortdesc' => $data['shortdesc'],
                                    'loglongdesc' => $data['longdesc']
                                    ));

                            }

                            // Successfully updated - Return id
                            return $id;

                        } else {
                            
                            $msg = __('DB Update Failed','zero-bs-crm');                    
                            $zbs->DAL->addError(302,$this->objectType,$msg,$dataArr);

                            // FAILED update
                            return false;

                        }

        } else {

            // set created if not set
            if (!isset($dataArr['zbsl_created'])) {
                $dataArr['zbsl_created'] = time(); $dataTypes[] = '%d';
            }

            // add team etc
            $dataArr['zbs_site'] = zeroBSCRM_site(); $dataTypes[] = '%d';
            $dataArr['zbs_team'] = zeroBSCRM_team(); $dataTypes[] = '%d';
            
            #} No ID - must be an INSERT
            if ($wpdb->insert( 
                        $ZBSCRM_t['logs'], 
                        $dataArr, 
                        $dataTypes ) > 0){

                    #} Successfully inserted, lets return new ID
                    $newID = $wpdb->insert_id;

                    // any meta
                    if (isset($data['meta']) && $data['meta'] !== -1){

                        // brutal
                        $this->DAL()->updateMeta(ZBS_TYPE_LOG,$newID,'logmeta',$data['meta']);

                    }
                
                    #} Internal Automator
                    if (!empty($newID)){

                        zeroBSCRM_FireInternalAutomator('log.new',array(
                            'id'=>$newID,
                            'logagainst'=>$data['objid'],
                            'logagainsttype'=>$data['objtype'],
                            'logtype'=> $data['type'],
                            'logshortdesc' => $data['shortdesc'],
                            'loglongdesc' => $data['longdesc']
                            ));

                    }

                    return $newID;

                } else {
                            
                    $msg = __('DB Insert Failed','zero-bs-crm');                    
                    $zbs->DAL->addError(303,$this->objectType,$msg,$dataArr);

                    #} Failed to Insert
                    return false;

                }

        }

        return false;

    }

     /**
     * deletes a Log object
     * NOTE! this doesn't yet delete any META!
     *
     * @param array $args Associative array of arguments
     *              id
     *
     * @return int success;
     */
    public function deleteLog($args=array()){

        global $ZBSCRM_t,$wpdb;

        #} ============ LOAD ARGS =============
        $defaultArgs = array(

            'id'            => -1

        ); foreach ($defaultArgs as $argK => $argV){ $$argK = $argV; if (is_array($args) && isset($args[$argK])) {  if (is_array($args[$argK])){ $newData = $$argK; if (!is_array($newData)) $newData = array(); foreach ($args[$argK] as $subK => $subV){ $newData[$subK] = $subV; }$$argK = $newData;} else { $$argK = $args[$argK]; } } }
        #} =========== / LOAD ARGS ============

        #} Check ID & Delete :)
        $id = (int)$id;
        if (!empty($id) && $id > 0) return zeroBSCRM_db2_deleteGeneric($id,'logs');

        return false;

    }

    /**
     * tidy's the object from wp db into clean array
     *
     * @param array $obj (DB obj)
     *
     * @return array (clean obj)
     */
    private function tidy_log($obj=false){

            $res = false;

            if (isset($obj->ID)){
            $res = array();
            $res['id'] = $obj->ID;
            $res['owner'] = $obj->zbs_owner;
            
            // added these two for backward compatibility / alias of (TROY modifications): 
            // please use owner ideally :)
            $res['authorid'] = $obj->zbs_owner;
            $res['author'] = get_the_author_meta('display_name',$obj->zbs_owner);

            $res['objtype'] = $obj->zbsl_objtype;
            $res['objid'] = $obj->zbsl_objid;

            $res['type'] = $this->stripSlashes($obj->zbsl_type);
            $res['shortdesc'] = $this->stripSlashes($obj->zbsl_shortdesc);
            $res['longdesc'] = $this->stripSlashes($obj->zbsl_longdesc);


            // to maintain old obj more easily, here we refine created into datestamp
            $res['created'] = zeroBSCRM_locale_utsToDatetime($obj->zbsl_created);
            $res['createduts'] = $obj->zbsl_created; // this is the UTS (int14)

            $res['lastupdated'] = $obj->zbsl_lastupdated;

            if (isset($obj->meta)) $res['meta'] = $obj->meta;

        } 

        return $res;


    }

    /**
     * Translates a clear text log type to a lowercase (kinda) permalink
     * ... this is kinda DAL1 legacy
     *
     * @param string 
     *
     * @return string
     */
    public function logTypeIn($str=''){

            $x = str_replace(' ','_',$str);
            $x = str_replace(':','_',$x);
            return strtolower($x);

    }

    /**
     * Translates a db text log type to a clear text output
     * ... this is kinda DAL1 legacy
     * *UNTESTED
     *
     * @param string 
     *
     * @return string
     */
    public function logTypeOut($str=''){

            $x = str_replace('_',' ',$str);
            $x = str_replace('  ',': ',$x);
            return ucwords($x);

    }



    // =========== / LOGS      =======================================================
    // ===============================================================================

} // / class
