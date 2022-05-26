<?php 
/*!
 * Jetpack CRM
 * https://jetpackcrm.com
 * V1.20
 *
 * Copyright 2020 Automattic
 *
 * Date: 01/11/16
 */

/* ======================================================
  Breaking Checks ( stops direct access )
   ====================================================== */
    if ( ! defined( 'ZEROBSCRM_PATH' ) ) exit;
/* ======================================================
  / Breaking Checks
   ====================================================== */

   /* DAL3.0 Notes:

        This file remains a copy of old DAL as we now simply switch by file-switch-out (originally I had made an ugly chimera baby, but spent the time re-organising into DAL3 proper)

   */


/* ======================================================
   DB GENERIC/OBJ Helpers (not DAL GET etc.)
   ====================================================== */

    // ===============================================================================
    // ===========  PERMISSIONS HELPERS  =============================================

    // in time this'll allow multiple 'sites' per install (E.g. site 1 = epic.zbs.com site 2 = zbs.zbs.com)
    // for now, this is hard-coded to 1
    // replaces "zeroBSCRM_installSite" from old DAL
    function zeroBSCRM_site(){

        return 1;

    }
    // in time this'll allow multiple 'team' per site (E.g. branch1,branch2 etc.)
    // for now, this is hard-coded to 1
    // replaces "zeroBSCRM_installTeam" from old DAL
    function zeroBSCRM_team(){

        return 1;

    }
    // active user id - helper func
    // replaces "zeroBSCRM_currentUserID" from old DAL
    // can alternatively user $zbs->user()
    function zeroBSCRM_user(){

        return get_current_user_id();

    }

    // =========== /  PERMISSIONS HELPERS  ===========================================
    // ===============================================================================

    // ===============================================================================
    // ===========  TYPES ============================================================


        define('ZBS_TYPE_CONTACT',      1);
        define('ZBS_TYPE_COMPANY',      2);
        define('ZBS_TYPE_QUOTE',        3);
        define('ZBS_TYPE_INVOICE',      4);
        define('ZBS_TYPE_TRANSACTION',  5);
        define('ZBS_TYPE_EVENT',        6);
        define('ZBS_TYPE_FORM',         7);
        define('ZBS_TYPE_LOG',          8);
        define('ZBS_TYPE_SEGMENT',      9);

        // backward compat, only become usable v3+
        define('ZBS_TYPE_LINEITEM',     10);
        define('ZBS_TYPE_EVENTREMINDER', 11);
        define('ZBS_TYPE_QUOTETEMPLATE', 12);
        define('ZBS_TYPE_ADDRESS',      13); // this is a precursor to v4 where we likely need to split out addresses from current in-object model (included here as custom fields now managed as if obj)



    // =========== /  TYPES  =========================================================
    // ===============================================================================


/* ======================================================
   / DB GENERIC/OBJ Helpers (not DAL GET etc.)
   ====================================================== */




/**
* zbsDAL is the Data Access Layer for ZBS v2.5+
*
* zbsDAL provides expanded CRUD actions to the 
* Jetpack CRM generally, and will be initiated globally
* like the WordPress $wpdb. 
*
* @author   Woody Hayday <hello@jetpackcrm.com>
* @version  2.0
* @access   public
* @see      https://jetpackcrm.com/kb
*/
class zbsDAL {

    public $version = 2.53;

    // ===============================================================================
    // ===========  v3 Prep Hack =====================================================
    // v3 prep hack, some places we've started using $zbs->DAL->contacts too early, so this is a re-ref,
    // (not ideal, but perhaps cleaner than doin if ($zbs->isDAL3()) switching everywhere
    public $contacts = false;
    function __construct($args=array()) {

        // init sub-layers:
        $this->contacts = $this;

    }
    // ===========  / v3 Prep Hack ===================================================
    // ===============================================================================




    // ===============================================================================
    // ===========  OBJECT TYPE DEFINITIONS    =======================================
    // These are here to validate types, use CONSTANTS in code (e.g. ZBS_TYPE_CONTACT)

    private $typesByID = array(


            ZBS_TYPE_CONTACT => 'contact',
            ZBS_TYPE_COMPANY => 'company',
            ZBS_TYPE_QUOTE => 'quote',
            ZBS_TYPE_INVOICE => 'invoice',
            ZBS_TYPE_TRANSACTION => 'transaction',
            ZBS_TYPE_EVENT => 'event',
            ZBS_TYPE_FORM => 'form',
            ZBS_TYPE_SEGMENT => 'segment',
            ZBS_TYPE_LOG => 'log',
            ZBS_TYPE_LINEITEM => 'lineitem',
            ZBS_TYPE_EVENTREMINDER => 'eventreminder',
            ZBS_TYPE_QUOTETEMPLATE => 'quotetemplate',
            ZBS_TYPE_ADDRESS => 'address'

    );

    // retrieve via DAL->oldCPT(1)
    private $typeCPT = array(

            ZBS_TYPE_CONTACT => 'zerobs_customer',
            ZBS_TYPE_COMPANY => 'zerobs_company',
            ZBS_TYPE_QUOTE => 'zerobs_quote',
            ZBS_TYPE_INVOICE => 'zerobs_invoice',
            ZBS_TYPE_TRANSACTION => 'zerobs_transaction',
            ZBS_TYPE_EVENT => 'zerobs_event',
            ZBS_TYPE_FORM => 'zerobs_form'

    );

    // retrieve via DAL->typeStr(1)
    private $typeNames = array(

            ZBS_TYPE_CONTACT =>         array('Contact','Contacts'),
            ZBS_TYPE_COMPANY =>         array('Company','Companies'),
            ZBS_TYPE_QUOTE =>           array('Quote','Quotes'),
            ZBS_TYPE_INVOICE =>         array('Invoice','Invoices'),
            ZBS_TYPE_TRANSACTION =>     array('Transaction','Transactions'),
            ZBS_TYPE_EVENT =>           array('Event','Events'),
            ZBS_TYPE_FORM =>            array('Form','Forms'),
            ZBS_TYPE_SEGMENT =>         array('Segment','Segments'),
            ZBS_TYPE_LOG =>             array('Log','Logs'),
            ZBS_TYPE_LINEITEM =>        array('Line Item','Line Items'),
            ZBS_TYPE_EVENTREMINDER =>   array('Event Reminder','Event Reminders'),
            ZBS_TYPE_QUOTETEMPLATE =>   array('Quote Template','Quote Templates'),
            ZBS_TYPE_ADDRESS =>   array('Address','Addresses')

    );

    // List View refs
    private $listViewRefs = array(

            // each of these is a slug for $zbs->slugs e.g. $zbs->slugs['managecontacts']
            ZBS_TYPE_CONTACT =>         'managecontacts',
            ZBS_TYPE_COMPANY =>         'managecompanies',
            ZBS_TYPE_QUOTE =>           'managequotes',
            ZBS_TYPE_INVOICE =>         'manageinvoices',
            ZBS_TYPE_TRANSACTION =>     'managetransactions',
            ZBS_TYPE_EVENT =>           'manage-events',
            ZBS_TYPE_FORM =>            'manageformscrm',
            ZBS_TYPE_SEGMENT =>         'segments',
            //no list page ZBS_TYPE_LOG =>             'managecontacts',
            //no list page ZBS_TYPE_LINEITEM =>        'managecontacts',
            //no list page ZBS_TYPE_EVENTREMINDER =>   'managecontacts',
            ZBS_TYPE_QUOTETEMPLATE =>   'quote-templates'
    );

    // this is a shorthand for grabbing all addr fields 
    private $field_list_address = array(

            'zbsc_addr1','zbsc_addr2','zbsc_city','zbsc_postcode','zbsc_county','zbsc_country'

    );
    private $field_list_address2 = array(

            'zbsc_secaddr1','zbsc_secaddr2','zbsc_seccity','zbsc_secpostcode','zbsc_seccounty','zbsc_seccountry'

    );
    private $field_list_address_full = array(

            'zbsc_addr1','zbsc_addr2','zbsc_city','zbsc_postcode','zbsc_county','zbsc_country',
            'zbsc_secaddr1','zbsc_secaddr2','zbsc_seccity','zbsc_secpostcode','zbsc_seccounty','zbsc_seccountry'

    );

    public function getObjectTypesByKey(){

        return array_flip($this->typesByID);

    }

    // takes in an obj type str (e.g. 'contact') and returns DEFINED KEY ID = 1
    public function objTypeID($objTypeStr=''){

        $byStr = $this->getObjectTypesByKey();
        if (isset($byStr[$objTypeStr])) return $byStr[$objTypeStr];

        return -1;

    }

    // takes in an obj type str (e.g. 1) and returns key (e.g. 'contact')
    public function objTypeKey($objTypeID=-1){

        if (isset($this->typesByID[$objTypeID])) return $this->typesByID[$objTypeID];

        return -1;

    }

    public function typeStr($typeInt=-1,$plural=false){

        $typeInt = (int)$typeInt;
        if ($typeInt > 0){

            if (isset($this->typeNames[$typeInt])){

                // plural
                if ($plural) return __($this->typeNames[$typeInt][1],'zero-bs-crm');
                // single
                return __($this->typeNames[$typeInt][0],'zero-bs-crm');

            }

        }
        return '';
    }

    // retrieves old CPT for that type
    public function typeCPT($typeInt=-1){

        $typeInt = (int)$typeInt;
        if ($typeInt > 0){

            if (isset($this->typeCPT[$typeInt])) return $this->typeCPT[$typeInt];
        }
        return false;
    }

    // takes in an obj ID and gives back the list view slug
    // Backward compat from v3.0
    public function listViewSlugFromObjID($objTypeID=-1){

        global $zbs;

        if (isset($this->listViewRefs[$objTypeID]) && isset($zbs->slugs[$this->listViewRefs[$objTypeID]])) return $zbs->slugs[$this->listViewRefs[$objTypeID]];

        return '';

    }

    // this stores any insert errors
    private $errorStack = array();

    // =========== /  OBJECT TYPE DEFINITIONS   ======================================
    // ===============================================================================



    // ===============================================================================
    // ===========  OWNERSHIP HELPERS  ===============================================

    // this is used to get specific user's settings via userSetting
    private $userSettingPrefix = 'usrset_'; // completes via usrset_*ID*_key

    /* old way of doing it: (we now use zbs_owner :))
    private function getUserSettingPrefix($userID=-1){

        // completes usrset_*ID*_key
        return $this->userSettingPrefix.$userID.'_';
    } */

    // Note: Following MUST be used together.

        // this makes query vars (as appropriate) team + site (and owner if  $ignoreOwner not false)
        public function ownershipQueryVars($ignoreOwner=false){

            $queryVars = array();

            // add site
            // FOR V3.0 SITE + TEAM NOT YET USED, (BUT THIS'll WORK)
            //$queryVars[] = zeroBSCRM_site();

            // add team
            // FOR V3.0 SITE + TEAM NOT YET USED, (BUT THIS'll WORK)
            //$queryVars[] = zeroBSCRM_team();

            // add owner
            if (!$ignoreOwner) $queryVars[] = zeroBSCRM_user();

            return $queryVars;
            
        }
        // this makes query str (as appropriate) team + site (and owner if  $ignoreOwner not false)
        // $table ONLY needed when is a LEFT JOIN or similar.
        public function ownershipSQL($ignoreOwner=false,$table=''){

            // build
            $q = ''; $tableStr = ''; if (!empty($table)) $tableStr = $table.'.';

            // add site
            // FOR V3.0 SITE + TEAM NOT YET USED, (BUT THIS'll WORK)
            //$q = $this->spaceAnd($q).$tableStr.'zbs_site = %d';

            // add team
            // FOR V3.0 SITE + TEAM NOT YET USED, (BUT THIS'll WORK)
            //$q = $this->spaceAnd($q).$tableStr.'zbs_team = %d';

            // add owner
            if (!$ignoreOwner) $q = $this->spaceAnd($q).$tableStr.'zbs_owner = %d';

            return $q;
            
        }



    // ===========  / OWNERSHIP HELPERS  =============================================
    // ===============================================================================






/* ======================================================
   DAL CRUD
   ====================================================== */


    // ===============================================================================
    // ===========   OBJ LINKS   =======================================================

    /**
     * returns objects against an obj (e.g. company's against contact id 101)
     * This is like getObjsLinksLinkedToObj, only it returns actual objs :)
     *
     * @param array $args   Associative array of arguments
     *                      obj array
     *
     * @return array result
     */
    public function getObjsLinkedToObj($args=array()){

        #} =========== LOAD ARGS ==============
        $defaultArgs = array(

            'objtypefrom'       => -1,
            'objtypeto'         => -1,

            // either or here, to specify direction of relationship
            'objfromid'         => -1,
            'objtoid'           => -1,

            // this will be passed to the getCompanies(array()) func, if given
            'objRetrievePassthrough' => array(), 

            'count' => false, // only return count

            // permissions
            //'ignoreowner'     => false // this'll let you not-check the owner of obj
            // NOTE 'owner' will ALWAYS be ignored by this, but allows for team/site
            // settings don't need owners yet :)

        ); foreach ($defaultArgs as $argK => $argV){ $$argK = $argV; if (is_array($args) && isset($args[$argK])) {  if (is_array($args[$argK])){ $newData = $$argK; if (!is_array($newData)) $newData = array(); foreach ($args[$argK] as $subK => $subV){ $newData[$subK] = $subV; }$$argK = $newData;} else { $$argK = $args[$argK]; } } }
        #} =========== / LOAD ARGS =============

        // hard ignored for now :)
        $ignoreowner = true;

        if (!isset($objtypefrom) || empty($objtypefrom)) return false;
        if ($this->objTypeKey($objtypefrom) === -1) return false;
        if (!isset($objtypeto) || empty($objtypeto)) return false;
        if ($this->objTypeKey($objtypeto) === -1) return false;

        #} Check ID
        $direction = 'from'; 
        $objfromid = (int)$objfromid; 
        $objtoid = (int)$objtoid; if ($objtoid > 0) $direction = 'to';

        if (
                (!empty($objfromid) && $objfromid > 0)
                ||
                (!empty($objtoid) && $objtoid > 0)

            ){

            $res = array();

            // get links - this could all be one query... optimise once other db objects moved over
            $objLinks = $this->getObjsLinksLinkedToObj(array(
                        'objtypefrom'   =>  $objtypefrom, // contact
                        'objtypeto'     =>  $objtypeto, // company
                        'objfromid'     =>  $objfromid, //-1 or id
                        'objtoid'       =>  $objtoid));

            if ($count) {
                if (is_array($objLinks))
                    return count($objLinks);
                else
                    return 0;
            }

            if (is_array($objLinks) && count($objLinks) > 0){ 

                // make an id array (useful)
                $idArray = array(); foreach ($objLinks as $l) {
                    
                    // switched direction
                    $xid = $l['objidto']; if ($direction == 'to') $xid = $l['objidfrom'];

                    if (!in_array($xid, $idArray)) $idArray[] = $xid;
                }

                // load them all (type dependent)
                switch ($objtypeto){


                    // not yet used, but will work :)
                    case ZBS_TYPE_CONTACT:
                        return $this->contacts->getContacts(array('inArr'=>$idArray));
                        break;

                    case ZBS_TYPE_COMPANY:
                        return $this->companies->getCompanies(array('inArr'=>$idArray));
                        break;

                    case ZBS_TYPE_QUOTE:
                        return $this->quotes->getQuotes(array('inArr'=>$idArray));
                        break;

                    case ZBS_TYPE_INVOICE:
                        return $this->invoices->getInvoices(array('inArr'=>$idArray));
                        break;

                    case ZBS_TYPE_TRANSACTION:
                        return $this->transactions->getTransactions(array('inArr'=>$idArray));
                        break;

                    case ZBS_TYPE_EVENT:
                        return $this->events->getEvents(array('inArr'=>$idArray));
                        break;

                    case ZBS_TYPE_QUOTETEMPLATE:
                        return $this->quotetemplates->getQuotetemplate(array('inArr'=>$idArray));
                        break;

                    /* not used
                    case ZBS_TYPE_LOG:
                        return $this->logs->getLogs(array('inArr'=>$idArray));
                        break;

                    case ZBS_TYPE_LINEITEM:
                        return $this->events->getEvents(array('inArr'=>$idArray));
                        break;

                    case ZBS_TYPE_EVENTREMINDER:
                        return $this->events->getEvents(array('inArr'=>$idArray));
                        break;
                    */
                        
                }


            }



            return $res;

        } // / if ID

        return false;

    }


    /**
     * returns object link lines against an obj (e.g. link id, company id's against contact id 101)
     *
     * @param array $args   Associative array of arguments
     *                      objtypeid, objid
     *
     * @return array result
     */
    /* 

    Replaced this with v3.0 variant with more params, left this in place in case of any issues
    .. brought up pre v3.0 RC for pre v3.0 users

    public function getObjsLinksLinkedToObj($args=array()){

        #} =========== LOAD ARGS ==============
        $defaultArgs = array(

            'objtypefrom'       => -1,
            'objtypeto'         => -1,
            'objfromid'         => -1,

            // permissions
            //'ignoreowner'     => false // this'll let you not-check the owner of obj
            // NOTE 'owner' will ALWAYS be ignored by this, but allows for team/site
            // settings don't need owners yet :)

        ); foreach ($defaultArgs as $argK => $argV){ $$argK = $argV; if (is_array($args) && isset($args[$argK])) {  if (is_array($args[$argK])){ $newData = $$argK; if (!is_array($newData)) $newData = array(); foreach ($args[$argK] as $subK => $subV){ $newData[$subK] = $subV; }$$argK = $newData;} else { $$argK = $args[$argK]; } } }
        #} =========== / LOAD ARGS =============

        // hard ignored for now
        $ignoreowner = true;
        
        if (!isset($objtypefrom) || empty($objtypefrom)) return false;
        if ($this->objTypeKey($objtypefrom) === -1) return false;
        if (!isset($objtypeto) || empty($objtypeto)) return false;
        if ($this->objTypeKey($objtypeto) === -1) return false;

        #} Check ID
        $objfromid = (int)$objfromid;
        if (!empty($objfromid) && $objfromid > 0 ){

            global $ZBSCRM_t,$wpdb; 
            $wheres = array('direct'=>array()); $whereStr = ''; $additionalWhere = ''; $params = array(); $res = array();

            #} Build query
            $query = "SELECT * FROM ".$ZBSCRM_t['objlinks'];

            #} ============= WHERE ================

                #} Add 
                $wheres['zbsol_objtype_from'] = array('zbsol_objtype_from','=','%s',$objtypefrom);
                $wheres['zbsol_objtype_to'] = array('zbsol_objtype_to','=','%s',$objtypeto);
                $wheres['zbsol_objid_from'] = array('zbsol_objid_from','=','%s',$objfromid);

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
            $query .= $this->buildWhereStr($whereStr,$additionalWhere) . $this->buildSort('ID','DESC') . $this->buildPaging(0,10000);

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
                            
                        // tidy
                        $resArr = $this->tidy_objlink($resDataLine);

                        $res[] = $resArr;

                }
            }

            return $res;

        } // / if ID

        return false;

    } */
    
    /**
     * returns object link lines against an obj (e.g. link id, company id's against contact id 101)
     *
     * @param array $args   Associative array of arguments
     *                      objtypeid, objid
     *
     * @return array result
     */
    public function getObjsLinksLinkedToObj($args=array()){

        #} =========== LOAD ARGS ==============
        $defaultArgs = array(

            'objtypefrom'       => -1,
            'objtypeto'         => -1,

            // either or here, to specify direction of relationship
            // if 'direction' = 'both', it'll check both
            'objfromid'         => -1,
            'objtoid'           => -1,

            'direction'          => 'from', // from, to, both (both checks for both id's and is used to validate if links exist)

            'count' => false, // only return count

            // permissions
            //'ignoreowner'     => false // this'll let you not-check the owner of obj
            // NOTE 'owner' will ALWAYS be ignored by this, but allows for team/site
            // settings don't need owners yet :)

        ); foreach ($defaultArgs as $argK => $argV){ $$argK = $argV; if (is_array($args) && isset($args[$argK])) {  if (is_array($args[$argK])){ $newData = $$argK; if (!is_array($newData)) $newData = array(); foreach ($args[$argK] as $subK => $subV){ $newData[$subK] = $subV; }$$argK = $newData;} else { $$argK = $args[$argK]; } } }
        #} =========== / LOAD ARGS =============

        // hard ignored for now
        $ignoreowner = true;
        
        if (!isset($objtypefrom) || empty($objtypefrom)) return false;
        if ($this->objTypeKey($objtypefrom) === -1) return false;
        if (!isset($objtypeto) || empty($objtypeto)) return false;
        if ($this->objTypeKey($objtypeto) === -1) return false;

        #} Check ID 
        $objfromid = (int)$objfromid; 
        $objtoid = (int)$objtoid; if ($objtoid > 0 && $direction != "both") $direction = 'to';

        if (
                (!empty($objfromid) && $objfromid > 0)
                ||
                (!empty($objtoid) && $objtoid > 0)

            ){

            global $ZBSCRM_t,$wpdb; 
            $wheres = array('direct'=>array()); $whereStr = ''; $additionalWhere = ''; $params = array(); $res = array();

            #} Build query
            $query = "SELECT * FROM ".$ZBSCRM_t['objlinks'];

            #} ============= WHERE ================

                #} Add 
                $wheres['zbsol_objtype_from'] = array('zbsol_objtype_from','=','%s',$objtypefrom);
                $wheres['zbsol_objtype_to'] = array('zbsol_objtype_to','=','%s',$objtypeto);

                // which direction?
                if ($direction == 'from' || $direction == 'both') $wheres['zbsol_objid_from'] = array('zbsol_objid_from','=','%s',$objfromid);
                if ($direction == 'to' || $direction == 'both') $wheres['zbsol_objid_to'] = array('zbsol_objid_to','=','%s',$objtoid);

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
            $query .= $this->buildWhereStr($whereStr,$additionalWhere) . $this->buildSort('ID','DESC') . $this->buildPaging(0,10000);

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

                // if count?
                if ($count) return count($potentialRes);

                #} Has results, tidy + return 
                foreach ($potentialRes as $resDataLine) {
                            
                        // tidy
                        $resArr = $this->tidy_objlink($resDataLine);

                        $res[] = $resArr;

                }
            }

            // if count?
            if ($count) return 0;

            return $res;

        } // / if ID

        return false;

    }


     /**
     * adds or updates a link object
     * E.G. Contact -> Company
     * this says "match obj X with obj Y" (effectively 'tagging' it)
     * Using this generic format, but as of v2.5+ there's only contact->company links in here
     *
     * @param array $args Associative array of arguments
     *              id (if update - probably never used here), data(objtype,objid,tagid)
     *
     * @return int line ID
     */
    public function addUpdateObjLink($args=array()){

        global $ZBSCRM_t,$wpdb;

        #} ============ LOAD ARGS =============
        $defaultArgs = array(

            'id'            => -1,

            // OWNERS will all be set to -1 for objlinks for now :) 
            //'owner'           => -1

            // fields (directly)
            'data'          => array(

                'objtypefrom'       => -1,
                'objtypeto'         => -1,
                'objfromid'         => -1,
                'objtoid'           => -1

            )

        ); foreach ($defaultArgs as $argK => $argV){ $$argK = $argV; if (is_array($args) && isset($args[$argK])) {  if (is_array($args[$argK])){ $newData = $$argK; if (!is_array($newData)) $newData = array(); foreach ($args[$argK] as $subK => $subV){ $newData[$subK] = $subV; }$$argK = $newData;} else { $$argK = $args[$argK]; } } }
        #} =========== / LOAD ARGS ============

        #} ========== CHECK FIELDS ============

            // check obtype is completed + legit
            if (!isset($data['objtypefrom']) || empty($data['objtypefrom'])) return false;
            if ($this->objTypeKey($data['objtypefrom']) === -1) return false;
            if (!isset($data['objtypeto']) || empty($data['objtypeto'])) return false;
            if ($this->objTypeKey($data['objtypeto']) === -1) return false;

            // if owner = -1, add current
            // for now, all -1 - not needed yet (makes tags dupe e.g.) if (!isset($owner) || $owner === -1) $owner = zeroBSCRM_user();
            $owner = -1;

            #} check obj ids
            if (empty($data['objfromid']) || $data['objfromid'] < 1 || empty($data['objtoid']) || $data['objtoid'] < 1) return false;

        #} ========= / CHECK FIELDS ===========

        #} Check if ID present
        $id = (int)$id;
        if (!empty($id) && $id > 0){

                #} Check if obj exists (here) - for now just brutal update (will error when doesn't exist)

                #} Attempt update
                if ($wpdb->update( 
                        $ZBSCRM_t['objlinks'], 
                        array( 

                            // ownership
                            // no need to update these (as of yet) - can't move teams etc.
                            //'zbs_site' => zeroBSCRM_installSite(),
                            //'zbs_team' => zeroBSCRM_installTeam(),
                            'zbs_owner' => $owner,

                            // fields
                            'zbsol_objtype_from' => $data['objtypefrom'],
                            'zbsol_objtype_to' => $data['objtypeto'],
                            'zbsol_objid_from' => $data['objfromid'],
                            'zbsol_objid_to' => $data['objtoid']
                        ), 
                        array( // where
                            'ID' => $id
                            ),
                        array( // field data types
                            '%d',
                            '%d', 
                            '%d', 
                            '%d', 
                            '%d'
                        ),
                        array( // where data types
                            '%d'
                            )) !== false){

                            // Successfully updated - Return id
                            return $id;

                        } else {

                            // FAILED update
                            return false;

                        }

        } else {
            
            #} No ID - must be an INSERT
            if ($wpdb->insert( 
                        $ZBSCRM_t['objlinks'], 
                        array( 

                            // ownership
                            'zbs_site' => zeroBSCRM_site(),
                            'zbs_team' => zeroBSCRM_team(),
                            'zbs_owner' => $owner,

                            // fields
                            'zbsol_objtype_from' => $data['objtypefrom'],
                            'zbsol_objtype_to' => $data['objtypeto'],
                            'zbsol_objid_from' => $data['objfromid'],
                            'zbsol_objid_to' => $data['objtoid']
                        ), 
                        array( // field data types
                            '%d',  // site
                            '%d',  // team
                            '%d',  // owner

                            '%d',  
                            '%d', 
                            '%d',  
                            '%d'  
                        ) ) > 0){

                    #} Successfully inserted, lets return new ID
                    $newID = $wpdb->insert_id;
                    return $newID;

                } else {

                    #} Failed to Insert
                    return false;

                }

        }

        return false;

    }

     /**
     * adds or updates object - object link  against an obj
     * this says "match company X,Y,Z with contact Y"
     *
     * @param array $args Associative array of arguments
     *              objtype,objid,tags (array of tagids)
     *
     * @return array $tags
     */
    public function addUpdateObjLinks($args=array()){

        global $ZBSCRM_t,$wpdb;

        #} ============ LOAD ARGS =============
        $defaultArgs = array(

            'owner'         => -1,

            'objtypefrom'       => -1,
            'objtypeto'         => -1,
            'objfromid'         => -1,
            'objtoids'          => -1, // array of tag ID's 

            'mode'          => 'replace' // replace|append|remove

        ); foreach ($defaultArgs as $argK => $argV){ $$argK = $argV; if (is_array($args) && isset($args[$argK])) {  if (is_array($args[$argK])){ $newData = $$argK; if (!is_array($newData)) $newData = array(); foreach ($args[$argK] as $subK => $subV){ $newData[$subK] = $subV; }$$argK = $newData;} else { $$argK = $args[$argK]; } } }
        #} =========== / LOAD ARGS ============


        #} ========== CHECK FIELDS ============

            // check obtype is completed + legit
            if (!isset($objtypefrom) || empty($objtypefrom)) return false;
            if ($this->objTypeKey($objtypefrom) === -1) return false;
            if (!isset($objtypeto) || empty($objtypeto)) return false;
            if ($this->objTypeKey($objtypeto) === -1) return false;

            // if owner = -1, add current
            if (!isset($owner) || $owner === -1) $owner = zeroBSCRM_user();

            // tagging id
            $objfromid = (int)$objfromid; if (empty($objfromid) || $objfromid < 1) return false;

            // to obj list
            if (!is_array($objtoids)) return false;

            // mode
            if (gettype($mode) != 'string' || !in_array($mode, array('replace','append','remove'))) return false;

        #} ========= / CHECK FIELDS ===========

            switch ($mode){

                case 'replace':
        
                    // cull all previous
                    $deleted = $this->deleteObjLinks(array(
                                'objtypefrom'   =>  $objtypefrom, // contact
                                'objtypeto'     =>  $objtypeto, // company
                                'objfromid'     =>  $objfromid)); // where contact id = 

                    // cycle through & add
                    foreach ($objtoids as $objtoid){

                        $added = $this->addUpdateObjLink(array(
                            'data'=>array(
                                'objtypefrom'   =>  $objtypefrom,
                                'objtypeto'     =>  $objtypeto,
                                'objfromid'     =>  $objfromid,
                                'objtoid'       =>  $objtoid,
                                'owner'         =>  $owner
                                )));


                    }

                    break;

                case 'append':

                    // get existing
                    $objLinks = $this->getObjsLinksLinkedToObj(array(
                                'objtypefrom'   =>  $objtypefrom, // contact
                                'objtypeto'     =>  $objtypeto, // company
                                'objfromid'     =>  $objfromid));

                    // make just ids
                    $existingLinkIDs = array(); foreach ($objLinks as $l) $existingLinkIDs[] = $l['id'];

                    // cycle through& add
                    foreach ($objtoids as $objtoid){

                        if (!in_array($objtoid,$existingLinkIDs)){

                            // add a link
                            $this->addUpdateObjLink(array(
                            'data'=>array(
                                'objtypefrom'   =>  $objtypefrom,
                                'objtypeto'     =>  $objtypeto,
                                'objfromid'     =>  $objfromid,
                                'objtoid'       =>  $objtoid,
                                'owner'         =>  $owner
                                )));

                        }

                    }

                    break;

                case 'remove':

                    // cycle through & remove links
                    foreach ($objtoids as $objtoid){

                        // add a link
                        $this->deleteObjLinks(array(
                                'objtypefrom'   =>  $objtypefrom, // contact
                                'objtypeto'     =>  $objtypeto, // company
                                'objfromid'     =>  $objfromid,
                                'objtoid'       =>  $objtoid)); // where contact id = 


                    }

                    break;


            }


        return false;

    }

     /**
     * deletes all object links for a specific obj
     *
     * @param array $args Associative array of arguments
     *              id
     *
     * @return int success;
     */
    public function deleteObjLinks($args=array()){

        global $ZBSCRM_t,$wpdb;

        #} ============ LOAD ARGS =============
        $defaultArgs = array(

            'objtypefrom'       => -1,
            'objtypeto'         => -1,
            'objfromid'         => -1,
            'objtoid'           => -1 // only toid/fromid to be set if want to delete all contact->company links

        ); foreach ($defaultArgs as $argK => $argV){ $$argK = $argV; if (is_array($args) && isset($args[$argK])) {  if (is_array($args[$argK])){ $newData = $$argK; if (!is_array($newData)) $newData = array(); foreach ($args[$argK] as $subK => $subV){ $newData[$subK] = $subV; }$$argK = $newData;} else { $$argK = $args[$argK]; } } }
        #} =========== / LOAD ARGS ============

        #} ========== CHECK FIELDS ============

            // check obtype is completed + legit
            if (!isset($objtypefrom) || empty($objtypefrom)) return false;
            if ($this->objTypeKey($objtypefrom) === -1) return false;
            if (!isset($objtypeto) || empty($objtypeto)) return false;
            if ($this->objTypeKey($objtypeto) === -1) return false;

            // obj id
            $objfromid = (int)$objfromid; $objtoid = (int)$objtoid; 
            if (
                (empty($objfromid) || $objfromid < 1)
                && 
                (empty($objtoid) || $objtoid < 1)
            ) return false;

            // CHECK PERMISSIONS?

        #} ========= / CHECK FIELDS ===========

            // basics
            $where = array( // where
                        'zbsol_objtype_from' => $objtypefrom,
                        'zbsol_objtype_to' => $objtypeto
                        );

            $whereFormat = array( // where
                        '%d',
                        '%d'
                        );

            // any to add?
            if (!empty($objfromid) && $objfromid > 0){
                $where['zbsol_objid_from'] = $objfromid;
                $whereFormat[] = '%d';
            }
            if (!empty($objtoid) && $objtoid > 0){
                $where['zbsol_objid_to'] = $objtoid;
                $whereFormat[] = '%d';
            }

        // brutal
        return $wpdb->delete( 
                    $ZBSCRM_t['objlinks'], 
                    $where,
                    $whereFormat);

    }
    /**
     * tidy's the object from wp db into clean array
     *
     * @param array $obj (DB obj)
     *
     * @return array (clean obj)
     */
    private function tidy_objlink($obj=false){

            $res = false;

            if (isset($obj->ID)){
            $res = array();
            $res['id'] = $obj->ID;
            /* 
              `zbs_site` INT NULL DEFAULT NULL,
              `zbs_team` INT NULL DEFAULT NULL,
              `zbs_owner` INT NOT NULL,
            */

            $res['objtypefrom'] = $obj->zbsol_objtype_from;
            $res['objtypeto'] = $obj->zbsol_objtype_to;
            $res['objidfrom'] = $obj->zbsol_objid_from;
            $res['objidto'] = $obj->zbsol_objid_to;

        } 

        return $res;


    }

    // ===========   OBJ LINKS   =====================================================
    // ===============================================================================




    // ===============================================================================
    // ===========   SETTINGS   ======================================================

     /**
     * Wrapper, use $this->setting($key) for easy retrieval of singular
     * Simplifies $this->getSetting
     *
     * @param string key
     *
     * @return bool result
     */
    public function setting($key='',$default=false){

        if (!empty($key)){

            return $this->getSetting(array(

                'key' => $key,
                'fullDetails' => false,
                'default' => $default

            ));

        }

        return $default;
    }

     /**
     * Wrapper, use $this->userSetting($key) for easy retrieval of singular setting FOR USER ID
     * Simplifies $this->getSetting
     * Specific for USER settings, this prefixes setting keys with usrset_ID_
     *
     * @param string key
     *
     * @return bool result
     */
    public function userSetting($userID=-1,$key='',$default=false){

        if (!empty($key) && $userID > 0){

            return $this->getSetting(array(

                // old way of doing it'key' => $this->getUserSettingPrefix($userID).$key,
                'key' => $this->userSettingPrefix.$key,
                'fullDetails' => false,
                'default' => $default,

                // this makes it 'per user'
                'ownedBy' => $userID

            ));

        }

        return $default;
    }

    /**
     * returns full setting line +- details
     *
     * @param array $args   Associative array of arguments
     *                      key, fullDetails, default
     *
     * @return array result
     */
    public function getSetting($args=array()){

        #} =========== LOAD ARGS ==============
        $defaultArgs = array(

            'key'   => false,
            'default' => false,
            'fullDetails' => false, // set this to 1 and get ID|key|val, rather than just the val

            // permissions - these are currently only used by screenoptions
            'ignoreowner'   => true, // this'll let you not-check the owner of obj
            'ownedBy'   => -1, 

            // returns scalar ID of line
            'onlyID'        => false

        ); foreach ($defaultArgs as $argK => $argV){ $$argK = $argV; if (is_array($args) && isset($args[$argK])) {  if (is_array($args[$argK])){ $newData = $$argK; if (!is_array($newData)) $newData = array(); foreach ($args[$argK] as $subK => $subV){ $newData[$subK] = $subV; }$$argK = $newData;} else { $$argK = $args[$argK]; } } }
        #} =========== / LOAD ARGS =============

        #} Check key
        if (!empty($key)){

            global $ZBSCRM_t,$wpdb; 
            $wheres = array('direct'=>array()); $whereStr = ''; $additionalWhere = ''; $params = array(); $res = array();

            #} Build query
            $query = "SELECT * FROM ".$ZBSCRM_t['settings'];

            #} ============= WHERE ================

                #} Add ID
                $wheres['zbsset_key'] = array('zbsset_key','=','%s',$key);

                #} Owned by
                if (!empty($ownedBy) && $ownedBy > 0){
                    
                    // would never hard-type this in (would make generic as in buildWPMetaQueryWhere)
                    // but this is only here until MIGRATED to db2 globally
                    //$wheres['incompany'] = array('ID','IN','(SELECT DISTINCT post_id FROM '.$wpdb->prefix."postmeta WHERE meta_key = 'zbs_company' AND meta_value = %d)",$inCompany);
                    // Use obj links now 
                    $wheres['ownedBy'] = array('zbs_owner','=','%s',$ownedBy);

                }


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

                    #} full line or scalar setting val
                    if ($fullDetails)
                        return $this->tidy_setting($potentialRes);
                    else 
                        return $this->tidy_settingSingular($potentialRes);

            }

        } // / if ID

        return $default;

    }


    /**
     * returns all settings as settings arr (later add autoload)
     *
     * @param array $args Associative array of arguments
     *
     * @return array of settings lines
     */
    public function getSettings($args=array()){

        #} ============ LOAD ARGS =============
        $defaultArgs = array(

            'autoloadOnly' => true,
            'fullDetails' => false, // if true returns inc id etc.

            // permissions
            //'ignoreowner'     => false // this'll let you not-check the owner of obj
            // NOTE 'owner' will ALWAYS be ignored by this, but allows for team/site
            // settings don't need owners yet :)

        ); foreach ($defaultArgs as $argK => $argV){ $$argK = $argV; if (is_array($args) && isset($args[$argK])) {  if (is_array($args[$argK])){ $newData = $$argK; if (!is_array($newData)) $newData = array(); foreach ($args[$argK] as $subK => $subV){ $newData[$subK] = $subV; }$$argK = $newData;} else { $$argK = $args[$argK]; } } }
        #} =========== / LOAD ARGS =============

        #} ========== CHECK FIELDS ============

            // check obtype is legit
            // autoload?

            $fields = 'ID,zbsset_key,zbsset_val';
            if ($fullDetails) $fields = '*';

            // always ignore owner for now (settings global)
            $ignoreowner = true;
        
        #} ========= / CHECK FIELDS ===========

        global $ZBSCRM_t,$wpdb; 
        $wheres = array('direct'=>array()); $whereStr = ''; $additionalWhere = ''; $params = array(); $res = array();

        #} Build query
        $query = "SELECT $fields FROM ".$ZBSCRM_t['settings'];

        #} ============= WHERE ================

            #} autoload?

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
        $query .= $this->buildWhereStr($whereStr,$additionalWhere) . $this->buildSort('ID','ASC') . $this->buildPaging(0,10000);

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

                    if ($fullDetails){
                        // tidy
                        $resArr = $this->tidy_setting($resDataLine);
                        $res[$resArr['key']] = $resArr;
                    } else
                        $res[$resDataLine->zbsset_key] = $this->tidy_settingSingular($resDataLine);

            }
        }

        return $res;
    } 

     /**
     * Wrapper, use $this->updateSetting($key,$val) for easy update of setting
     * Uses $this->addUpdateSetting
     *
     * @param string key
     * @param string value
     *
     * @return bool result
     */
    public function updateSetting($key='',$val=''){

        if (!empty($key)){

            return $this->addUpdateSetting(array(

                'data' => array(

                    'key' => $key,
                    'val' => $val
                )

            ));

        }

        return false;
    }

     /**
     * Wrapper, use $this->updateSetting($key,$val) for easy update of setting
     * Uses $this->addUpdateSetting
     *
     * @param string key
     * @param string value
     *
     * @return bool result
     */
    public function updateUserSetting($userID=-1,$key='',$val=''){

        // if -1 passed use current user?

        if (!empty($key) && $userID > 0){

            // because the following addUpdateSetting is dumb to owners (e.g. can't update 'per owner')
            // we must set perOwnerSetting to force 1 setting per key per user (owner)
            return $this->addUpdateSetting(array(

                'owner' => $userID,
                'data' => array(

                    // old way of doing it'key' => $this->getUserSettingPrefix($userID).$key,
                    'key' => $this->userSettingPrefix.$key,
                    'val' => $val,
                ),

                'perOwnerSetting' => true

            ));

        }

        return false;
    }

     /**
     * adds or updates a setting object
     * ... for a quicker wrapper, use $this->updateSetting($key,$val)
     *
     * @param array $args Associative array of arguments
     *              id (not req.), owner (not req.) data -> key/val
     *
     * @return int line ID
     */
    public function addUpdateSetting($args=array()){

        global $ZBSCRM_t,$wpdb;

        #} ============ LOAD ARGS =============
        $defaultArgs = array(

            'id'            => -1,
            // NOTE 'owner' will ALWAYS be ignored by this, but allows for team/site
            // meta don't need owners yet :)
            // not anymore! use this for screenoptions, will be ignored unless specifically set
            'owner'         => -1,

            // fields (directly)
            'data'          => array(

                'key'       => '',
                'val'       => '',
                
            ),

            'perOwnerSetting' => false // if set to true this'll make sure only 1 key per 'owner' (potentially multi-key if set incorrectly, so beware)

        ); foreach ($defaultArgs as $argK => $argV){ $$argK = $argV; if (is_array($args) && isset($args[$argK])) {  if (is_array($args[$argK])){ $newData = $$argK; if (!is_array($newData)) $newData = array(); foreach ($args[$argK] as $subK => $subV){ $newData[$subK] = $subV; }$$argK = $newData;} else { $$argK = $args[$argK]; } } }
        #} =========== / LOAD ARGS ============


        #} ========== CHECK FIELDS ============

            $id = (int)$id;

            // if owner = -1, add current
            // Hard -1 for now - settings don't need - if (!isset($owner) || $owner === -1) $owner = zeroBSCRM_user();
            // ... they do now, (screen options) $owner = -1;
            if (isset($owner) && $owner !== -1) $owner = (int)$owner;

            // check key present + legit
            if (!isset($data['key']) || empty($data['key'])) return false;

            // setting ID finder - if obj key provided, check setting not already present (if so overwrite) 
            // keeps unique...  
            if ((empty($id) || $id <= 0)
                && 
                (isset($data['key']) && !empty($data['key']))) {

                // if perOwnerSetting it's 1 key-ed ret per owner, so query bit diff here:
                if (!$perOwnerSetting){

                    // check existence + return ID
                    $potentialID = (int)$this->getSetting(array(
                                    'key'       => $data['key'],
                                    'onlyID'    => true
                                    ));

                } else {

                    // perownedBy

                    // if no owner, return false, cannot be (shouldn't be cos of above)
                    if ($owner <= 0) return false;

                    // check existence + return ID
                    $potentialID = (int)$this->getSetting(array(
                                    'key'       => $data['key'],
                                    'onlyID'    => true,
                                    'ownedBy' => $owner
                                    ));

                }

                // override empty ID 
                if (!empty($potentialID) && $potentialID > 0) $id = $potentialID;

            }


        #} ========= / CHECK FIELDS ===========

        #} Var up any val (json_encode)
        if (in_array(gettype($data['val']),array("object","array"))){

            // WH note: it was necessary to add JSON_UNESCAPED_SLASHES to properly save down without issue
            // combined with a more complex zeroBSCRM_stripSlashes recurrsive
            // https://stackoverflow.com/questions/7282755/how-to-remove-backslash-on-json-encode-function
            $data['val'] = json_encode($data['val'],JSON_UNESCAPED_SLASHES);

        }


        if (isset($id) && !empty($id) && $id > 0){

                #} Check if obj exists (here) - for now just brutal update (will error when doesn't exist)

                #} Attempt update
                if ($wpdb->update( 
                        $ZBSCRM_t['settings'], 
                        array( 

                            // ownership
                            // no need to update these (as of yet) - can't move teams etc.
                            //'zbs_site' => zeroBSCRM_installSite(),
                            //'zbs_team' => zeroBSCRM_installTeam(),
                            'zbs_owner' => $owner,

                            // fields
                            'zbsset_key' => $data['key'],
                            'zbsset_val' => $data['val'],
                            'zbsset_lastupdated' => time()
                        ), 
                        array( // where
                            'ID' => $id
                            ),
                        array( // field data types
                            '%d',
                            '%s', 
                            '%s', 
                            '%d'
                        ),
                        array( // where data types
                            '%d'
                            )) !== false){

                            // Successfully updated - Return id
                            return $id;

                        } else {

                            // FAILED update
                            return false;

                        }

        } else {
            
            #} No ID - must be an INSERT
            if ($wpdb->insert( 
                        $ZBSCRM_t['settings'], 
                        array( 

                            // ownership
                            'zbs_site' => zeroBSCRM_site(),
                            'zbs_team' => zeroBSCRM_team(),
                            'zbs_owner' => $owner,

                            // fields
                            'zbsset_key' => $data['key'],
                            'zbsset_val' => $data['val'],
                            'zbsset_created' => time(),
                            'zbsset_lastupdated' => time()
                        ), 
                        array( // field data types
                            '%d',  // site
                            '%d',  // team
                            '%d',  // owner

                            '%s',  
                            '%s',   
                            '%d',  
                            '%d'  
                        ) ) > 0){

                    #} Successfully inserted, lets return new ID
                    $newID = $wpdb->insert_id;
                    return $newID;

                } else {
                    
                    #} Failed to Insert
                    return false;

                }

        }

        return false;

    }

     /**
     * deletes a setting object
     *
     * @param array $args Associative array of arguments
     *              id
     *
     * @return int success;
     */
    public function deleteSetting($args=array()){

        global $ZBSCRM_t,$wpdb;

        #} ============ LOAD ARGS =============
        $defaultArgs = array(

            'id'            => -1

        ); foreach ($defaultArgs as $argK => $argV){ $$argK = $argV; if (is_array($args) && isset($args[$argK])) {  if (is_array($args[$argK])){ $newData = $$argK; if (!is_array($newData)) $newData = array(); foreach ($args[$argK] as $subK => $subV){ $newData[$subK] = $subV; }$$argK = $newData;} else { $$argK = $args[$argK]; } } }
        #} =========== / LOAD ARGS ============

        #} Check ID & Delete :)
        $id = (int)$id;
        if (!empty($id) && $id > 0) return zeroBSCRM_db2_deleteGeneric($id,'settings');

        return false;

    }

    /**
     * tidy's the object from wp db into clean array
     *
     * @param array $obj (DB obj)
     *
     * @return array (clean obj)
     */
    private function tidy_setting($obj=false){

            $res = false;

            if (isset($obj->ID)){
            $res = array();
            $res['id'] = $obj->ID;
            $res['key'] = $obj->zbsset_key;
            $res['val'] = $this->stripSlashes($obj->zbsset_val);
            $res['created'] = $obj->zbsset_created;
            $res['updated'] = $obj->zbsset_lastupdated;

        } 

        return $res;


    }

    /**
     * tidy's the object from wp db into clean array
     *
     * @param array $obj (DB obj)
     *
     * @return string
     */
    private function tidy_settingSingular($obj=false){

        $res = false;

        if (isset($obj->ID)) return $this->stripSlashes($this->decodeIfJSON($obj->zbsset_val));

        return $res;


    }

    // =========== / SETTINGS  =======================================================
    // ===============================================================================







    // ===============================================================================
    // ===========   META ============================================================

     /**
     * Wrapper, use $this->getContactMeta($contactID,$key) for easy retrieval of singular
     * Simplifies $this->getMeta
     *
     * @param int objtype
     * @param int objid
     * @param string key
     *
     * @return bool result
     */
    public function getContactMeta($id=-1,$key='',$default=false){

        if (!empty($key)){

            return $this->getMeta(array(

                'objtype' => ZBS_TYPE_CONTACT,
                'objid' => $id,
                'key' => $key,
                'fullDetails' => false,
                'default' => $default,
                'ignoreowner' => true // for now !!

            ));

        }

        return $default;
    }

     /**
     * Wrapper, use $this->meta($objtype,$objid,$key) for easy retrieval of singular
     * Simplifies $this->getMeta
     *
     * @param int objtype
     * @param int objid
     * @param string key
     *
     * @return bool result
     */
    public function meta($objtype=-1,$objid=-1,$key='',$default=false){

        if (!empty($key)){

            return $this->getMeta(array(

                'objtype' => $objtype,
                'objid' => $objid,
                'key' => $key,
                'fullDetails' => false,
                'default' => $default

            ));

        }

        return $default;
    }
    /**
     * returns full meta line +- details
     *
     * @param array $args   Associative array of arguments
     *                      key, fullDetails, default
     *
     * @return array result
     */
    public function getMeta($args=array()){

        #} =========== LOAD ARGS ==============
        $defaultArgs = array(


            'objid'         => -1, // OBJECT ID - REQ
            'objtype'       => -1, // REQ
            'key'   => false,

            'default' => false,

            'fullDetails' => false, // set this to 1 and get ID|key|val, rather than just the val

            // permissions
            //'ignoreowner'     => false // this'll let you not-check the owner of obj
            // NOTE 'owner' will ALWAYS be ignored by this, but allows for team/site
            // meta don't need owners yet :)

            // returns scalar ID of line
            'onlyID'        => false

        ); foreach ($defaultArgs as $argK => $argV){ $$argK = $argV; if (is_array($args) && isset($args[$argK])) {  if (is_array($args[$argK])){ $newData = $$argK; if (!is_array($newData)) $newData = array(); foreach ($args[$argK] as $subK => $subV){ $newData[$subK] = $subV; }$$argK = $newData;} else { $$argK = $args[$argK]; } } }
        #} =========== / LOAD ARGS =============

        #} =========== CHECK FIELDS =============

            // check obtype is completed + legit
            if (!isset($objtype) || empty($objtype)) return false;
            if ($this->objTypeKey($objtype) === -1) return false;
            
            // obj id
            $objid = (int)$objid; if (empty($objid) || $objid < 1) return false;

            // for now, meta hard ignores owners
            $ignoreowner = true;

        #} =========== / CHECK FIELDS =============
        
        #} Check key
        if (!empty($key)){

            global $ZBSCRM_t,$wpdb; 
            $wheres = array('direct'=>array()); $whereStr = ''; $additionalWhere = ''; $params = array(); $res = array();

            #} Build query
            $query = "SELECT * FROM ".$ZBSCRM_t['meta'];

            #} ============= WHERE ================

                #} Add ID
                $wheres['zbsm_objid'] = array('zbsm_objid','=','%d',$objid);
                #} Add OBJTYPE
                $wheres['zbsm_objtype'] = array('zbsm_objtype','=','%d',$objtype);
                #} Add KEY
                $wheres['zbsm_key'] = array('zbsm_key','=','%s',$key);

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

                    #} full line or scalar setting val
                    if ($fullDetails)
                        return $this->tidy_meta($potentialRes);
                    else 
                        return $this->tidy_metaSingular($potentialRes);

            }

        } // / if ID

        return $default;

    }

     /**
     * Wrapper, use $this->updateMeta($objtype,$objid,$key,$val) for easy update of setting
     * Uses $this->addUpdateMeta
     *
     * @param string key
     * @param string value
     *
     * @return bool result
     */
    public function updateMeta($objtype=-1,$objid=-1,$key='',$val=''){

        if (!empty($key)){ // && !empty($val)

            return $this->addUpdateMeta(array(

                'data' => array(

                    'objid'     => $objid,
                    'objtype'   => $objtype,
                    'key'       => $key,
                    'val'       => $val
                )

            ));

        }

        return false;
    }

     /**
     * adds or updates a setting object
     * ... for a quicker wrapper, use $this->updateMeta($key,$val)
     *
     * @param array $args Associative array of arguments
     *              id (not req.), owner (not req.) data -> key/val
     *
     * @return int line ID
     */
    public function addUpdateMeta($args=array()){

        global $ZBSCRM_t,$wpdb;

        #} ============ LOAD ARGS =============
        $defaultArgs = array(

            'id'            => -1,
            // owner HARD disabled for this for now - not req. for each meta
            //'owner'           => -1,

            // fields (directly)
            'data'          => array(

                'objid'         => -1,
                'objtype'       => -1,
                'key'       => '',
                'val'       => '',
                
            )

        ); foreach ($defaultArgs as $argK => $argV){ $$argK = $argV; if (is_array($args) && isset($args[$argK])) {  if (is_array($args[$argK])){ $newData = $$argK; if (!is_array($newData)) $newData = array(); foreach ($args[$argK] as $subK => $subV){ $newData[$subK] = $subV; }$$argK = $newData;} else { $$argK = $args[$argK]; } } }
        #} =========== / LOAD ARGS ============

        #} ========== CHECK FIELDS ============

            $id = (int)$id;

            // if owner = -1, add current
            //if (!isset($owner) || $owner === -1) $owner = zeroBSCRM_user();
            // owner HARD disabled for this for now - not req. for each meta
            $owner = -1;

            // check key present + legit
            if (!isset($data['key']) || empty($data['key'])) return false;

            // check obtype is completed + legit
            if (!isset($data['objtype']) || empty($data['objtype'])) return false;
            if ($this->objTypeKey($data['objtype']) === -1) return false;
            
            // obj id
            $objid = (int)$data['objid']; if (empty($objid) || $objid < 1) return false;

            // meta ID finder - if obj key provided, check meta not already present (if so overwrite)   
            // keeps unique...  
            if ((empty($id) || $id <= 0)
                && 
                (isset($data['key']) && !empty($data['key']))
                // no need to check obj id + type here, as will return false above if not legit :)
                ) {

                // check existence + return ID
                $potentialID = (int)$this->getMeta(array(
                                'objid'         => $objid,
                                'objtype'   => $data['objtype'],
                                'key'       => $data['key'],
                                'onlyID'    => true
                                ));


                // override empty ID 
                if (!empty($potentialID) && $potentialID > 0) $id = $potentialID;

            }

        #} ========= / CHECK FIELDS ===========

        #} Var up any val (json_encode)
        if (in_array(gettype($data['val']),array("object","array"))){

            $data['val'] = json_encode($data['val']);

        }


        if (isset($id) && !empty($id) && $id > 0){

                #} Check if obj exists (here) - for now just brutal update (will error when doesn't exist)

                #} Attempt update
                if ($wpdb->update( 
                        $ZBSCRM_t['meta'], 
                        array( 

                            // ownership
                            // no need to update these (as of yet) - can't move teams etc.
                            //'zbs_site' => zeroBSCRM_installSite(),
                            //'zbs_team' => zeroBSCRM_installTeam(),
                            'zbs_owner' => $owner,

                            // fields
                            'zbsm_objtype'  => $data['objtype'],
                            'zbsm_objid'    => $objid,
                            'zbsm_key'      => $data['key'],
                            'zbsm_val'      => $data['val'],
                            'zbsm_lastupdated' => time()
                        ), 
                        array( // where
                            'ID' => $id
                            ),
                        array( // field data types
                            '%d',
                            '%d',
                            '%d',
                            '%s', 
                            '%s', 
                            '%d'
                        ),
                        array( // where data types
                            '%d'
                            )) !== false){

                            // Successfully updated - Return id
                            return $id;

                        } else {

                            // FAILED update
                            return false;

                        }

        } else {
            
            #} No ID - must be an INSERT
            if ($wpdb->insert( 
                        $ZBSCRM_t['meta'], 
                        array( 

                            // ownership
                            'zbs_site' => zeroBSCRM_site(),
                            'zbs_team' => zeroBSCRM_team(),
                            'zbs_owner' => $owner,

                            // fields
                            'zbsm_objtype'  => $data['objtype'],
                            'zbsm_objid'    => $objid,
                            'zbsm_key'      => $data['key'],
                            'zbsm_val'      => $data['val'],
                            'zbsm_created' => time(),
                            'zbsm_lastupdated' => time()
                        ), 
                        array( // field data types
                            '%d',  // site
                            '%d',  // team
                            '%d',  // owner

                            '%d',  
                            '%d',  
                            '%s',  
                            '%s',   
                            '%d',  
                            '%d'  
                        ) ) > 0){

                    #} Successfully inserted, lets return new ID
                    $newID = $wpdb->insert_id;
                    return $newID;

                } else {

                    #} Failed to Insert
                    return false;

                }

        }

        return false;

    }

     /**
     * deletes a meta object based on objid + key
     *
     * @param array $args Associative array of arguments
     *              id
     *
     * @return int success;
     */
    public function deleteMeta($args=array()){

        global $ZBSCRM_t,$wpdb;

        #} ============ LOAD ARGS =============
        $defaultArgs = array(

            'objtype'           => -1,
            'objid'             => -1,
            'key'               => ''

        ); foreach ($defaultArgs as $argK => $argV){ $$argK = $argV; if (is_array($args) && isset($args[$argK])) {  if (is_array($args[$argK])){ $newData = $$argK; if (!is_array($newData)) $newData = array(); foreach ($args[$argK] as $subK => $subV){ $newData[$subK] = $subV; }$$argK = $newData;} else { $$argK = $args[$argK]; } } }
        #} =========== / LOAD ARGS ============

        #} Check ID, find, & Delete :)
        $objtype = (int)$objtype; if (isset($objtype) && $objtype !== -1 && $this->objTypeKey($objtype) === -1) return false;
        $objid = (int)$objid; if (empty($objid) || $objid < 1) return false;
        if (empty($key)) return false;

        #} FIND?
        $potentialID = (int)$this->getMeta(array(
                        'objid'     => $objid,
                        'objtype'   => $objtype,
                        'key'       => $key,
                        'onlyID'    => true
                        ));

        // override empty ID 
        if (!empty($potentialID) && $potentialID > 0) {

            return $this->deleteMetaByMetaID(array('id'=>$potentialID));

        }

        return false;

    }

     /**
     * deletes a meta object from a meta id
     *
     * @param array $args Associative array of arguments
     *              id
     *
     * @return int success;
     */
    public function deleteMetaByMetaID($args=array()){

        global $ZBSCRM_t,$wpdb;

        #} ============ LOAD ARGS =============
        $defaultArgs = array(

            'id'            => -1

        ); foreach ($defaultArgs as $argK => $argV){ $$argK = $argV; if (is_array($args) && isset($args[$argK])) {  if (is_array($args[$argK])){ $newData = $$argK; if (!is_array($newData)) $newData = array(); foreach ($args[$argK] as $subK => $subV){ $newData[$subK] = $subV; }$$argK = $newData;} else { $$argK = $args[$argK]; } } }
        #} =========== / LOAD ARGS ============

        #} Check ID & Delete :)
        $id = (int)$id;
        if (!empty($id) && $id > 0) return zeroBSCRM_db2_deleteGeneric($id,'meta');

        return false;

    }

    /**
     * tidy's the object from wp db into clean array
     *
     * @param array $obj (DB obj)
     *
     * @return array (clean obj)
     */
    private function tidy_meta($obj=false){

            $res = false;

            if (isset($obj->ID)){
            $res = array();
            $res['id'] = $obj->ID;
            $res['objtype'] = $obj->zbsm_objtype;
            $res['objid'] = $obj->zbsm_objid;
            $res['key'] = $obj->zbsm_key;
            $res['val'] = $this->stripSlashes($obj->zbsm_val);
            $res['created'] = $obj->zbsm_created;
            $res['updated'] = $obj->zbsm_lastupdated;

        } 

        return $res;


    }

    /**
     * tidy's the object from wp db into clean array
     *
     * @param array $obj (DB obj)
     *
     * @return string
     */
    private function tidy_metaSingular($obj=false){

        $res = false;

        if (isset($obj->ID)) return $this->stripSlashes($this->decodeIfJSON($obj->zbsm_val));

        return $res;


    }

    // =========== / META  ===========================================================
    // ===============================================================================





    // ===============================================================================
    // ===========   TAGS  ===========================================================
    /**
     * returns full tag line +- details
     *
     * @param int id        tag id
     * @param array $args   Associative array of arguments
     *                      withStats
     *
     * @return array result
     */
    public function getTag($id=-1,$args=array()){

        #} =========== LOAD ARGS ==============
        $defaultArgs = array(

            // Alternative search criteria to ID :)
            // .. LEAVE blank if using ID
            // objtype + name or slug
            'objtype'       => -1,
            'name'          => '',
            'slug'          => '',

            'withStats'     => false,

            // permissions
            //'ignoreowner'     => false // this'll let you not-check the owner of obj
            // NOTE 'owner' will ALWAYS be ignored by this, but allows for team/site
            // Tags don't need owners yet :)


            // returns scalar ID of line
            'onlyID'        => false

        ); foreach ($defaultArgs as $argK => $argV){ $$argK = $argV; if (is_array($args) && isset($args[$argK])) {  if (is_array($args[$argK])){ $newData = $$argK; if (!is_array($newData)) $newData = array(); foreach ($args[$argK] as $subK => $subV){ $newData[$subK] = $subV; }$$argK = $newData;} else { $$argK = $args[$argK]; } } }
        #} =========== / LOAD ARGS =============
        
        #} ========== CHECK FIELDS ============

            $id = (int)$id;

            // got objtype / name/slug?

            // check obtype is legit (if completed)
            if (isset($objtype) && $objtype !== -1 && $this->objTypeKey($objtype) === -1) {
            
                // if using obj type - check name/slug
                if (empty($name) && empty($slug)) return false;

                // ... else should be good to search

            }
        
            // Tags don't need owners yet :)
            $ignoreowner = true;

        #} ========= / CHECK FIELDS ===========
        
        #} Check ID or name/type
        if (
            (!empty($id) && $id > 0)
            ||
            (!empty($objtype) && !empty($slug))
            ){

            global $ZBSCRM_t,$wpdb; 
            $wheres = array('direct'=>array()); $whereStr = ''; $additionalWhere = ''; $params = array(); $res = array();

            #} Build query
            $query = "SELECT * FROM ".$ZBSCRM_t['tags'];

            #} ============= WHERE ================

                #} Add ID
                if (!empty($id) && $id > 0) $wheres['ID'] = array('ID','=','%d',$id);

                #} objtype + name/type/slug
                $wheres['zbstag_objtype'] = array('zbstag_objtype','=','%d',$objtype);
                if (!empty($name)) $wheres['zbstag_name'] = array('zbstag_name','=','%s',$name);
                if (!empty($slug)) $wheres['zbstag_slug'] = array('zbstag_slug','=','%s',$slug);

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
                
                    // tidy
                    $res = $this->tidy_tag($potentialRes);

                    // with stats?
                    if (isset($withStats) && $withStats){

                        // add all stats lines
                        $res['stats'] = $this->getTagStats(array('tagid'=>$potentialRes->ID));
                    
                    }

                    return $res;

            }

        } // / if ID

        return false;

    }

    /**
     * returns tag detail lines
     *
     * @param array $args Associative array of arguments
     *              withStats, searchPhrase, sortByField, sortOrder, page, perPage
     *
     * @return array of tag lines
     */
    public function getAllTags($args=array()){

        #} ============ LOAD ARGS =============
        $defaultArgs = array(

            'searchPhrase' => '',
            'withStats'     => false,

            'sortByField'   => 'ID',
            'sortOrder'     => 'ASC',
            'page'          => 0,
            'perPage'       => 100,

            // permissions
            //'ignoreowner'     => false // this'll let you not-check the owner of obj
            // NOTE 'owner' will ALWAYS be ignored by this, but allows for team/site
            // Tags don't need owners yet :)

        ); foreach ($defaultArgs as $argK => $argV){ $$argK = $argV; if (is_array($args) && isset($args[$argK])) {  if (is_array($args[$argK])){ $newData = $$argK; if (!is_array($newData)) $newData = array(); foreach ($args[$argK] as $subK => $subV){ $newData[$subK] = $subV; }$$argK = $newData;} else { $$argK = $args[$argK]; } } }
        #} =========== / LOAD ARGS =============

        // Tags don't need owners yet :)
        $ignoreowner = true;

        global $ZBSCRM_t,$wpdb; 
        $wheres = array('direct'=>array()); $whereStr = ''; $additionalWhere = ''; $params = array(); $res = array();

        #} Build query
        $query = "SELECT * FROM ".$ZBSCRM_t['tags'];

        #} ============= WHERE ================

            #} Add Search phrase
            if (!empty($searchPhrase)){

                $wheres['zbstag_name'] = array('zbstag_name','LIKE','%s','%'.$searchPhrase.'%');

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
                        
                    // tidy
                    $resArr = $this->tidy_tag($resDataLine);

                    // with stats?
                    if (isset($withStats) && $withStats){

                        // add all stats lines
                        $res['stats'] = $this->getTagStats(array('tagid'=>$resDataLine->ID));
                    
                    }

                    $res[] = $resArr;

            }
        }

        return $res;
    } 

     /**
     * adds or updates a tag object
     *
     * @param array $args Associative array of arguments
     *              id (if update), ???
     *
     * @return int line ID
     */
    public function addUpdateTag($args=array()){

        global $ZBSCRM_t,$wpdb;

        #} ============ LOAD ARGS =============
        $defaultArgs = array(

            'id'            => -1,

            // fields (directly)
            'data'          => array(

                'objtype'       => -1,
                'name'          => '',
                'slug'          => '',
                // OWNERS will all be set to -1 for tags for now :) 
                //'owner'           => -1

            )

        ); foreach ($defaultArgs as $argK => $argV){ $$argK = $argV; if (is_array($args) && isset($args[$argK])) {  if (is_array($args[$argK])){ $newData = $$argK; if (!is_array($newData)) $newData = array(); foreach ($args[$argK] as $subK => $subV){ $newData[$subK] = $subV; }$$argK = $newData;} else { $$argK = $args[$argK]; } } }
        #} =========== / LOAD ARGS ============

        #} ========== CHECK FIELDS ============

            $id = (int)$id;

            // check obtype is completed + legit
            if (!isset($data['objtype']) || empty($data['objtype'])) return false;
            if ($this->objTypeKey($data['objtype']) === -1) return false;

            // if owner = -1, add current
            // tags don't really need this level of ownership
            // so leaving as -1 for now :) 
            //if (!isset($data['owner']) || $data['owner'] === -1) $data['owner'] = zeroBSCRM_user();
            $data['owner'] = -1;

            // check name present + legit
            if (!isset($data['name']) || empty($data['name'])) return false;
            if (!isset($data['slug']) || empty($data['slug'])) {

                // generate one
                $data['slug'] = $this->makeSlug($data['name']);
            }

            // tag ID finder - if obj name provided, check tag not already present (if so overwrite)    
            // keeps unique...  
            if ((empty($id) || $id <= 0)
                && 
                (
                    (isset($data['name']) && !empty($data['name'])) ||
                    (isset($data['slug']) && !empty($data['slug']))
                )) {

                // check by slug
                // check existence + return ID
                $potentialID = (int)$this->getTag(-1,array(
                                'objtype'   => $data['objtype'],
                                'slug'      => $data['slug'],
                                'onlyID'    => true
                                ));

                // override empty ID 
                if (!empty($potentialID) && $potentialID > 0) $id = $potentialID;

            }

        #} ========= / CHECK FIELDS ===========

        #} Check if ID present
        $id = (int)$id;
        if (!empty($id) && $id > 0){

                #} Check if obj exists (here) - for now just brutal update (will error when doesn't exist)

                #} Attempt update
                if ($wpdb->update( 
                        $ZBSCRM_t['tags'], 
                        array( 

                            // ownership
                            // no need to update these (as of yet) - can't move teams etc.
                            //'zbs_site' => zeroBSCRM_installSite(),
                            //'zbs_team' => zeroBSCRM_installTeam(),
                            'zbs_owner' => $data['owner'],

                            // fields
                            'zbstag_objtype' => $data['objtype'],
                            'zbstag_name' => $data['name'],
                            'zbstag_slug' => $data['slug'],
                            'zbstag_lastupdated' => time()
                        ), 
                        array( // where
                            'ID' => $id
                            ),
                        array( // field data types
                            '%d',
                            '%d',
                            '%s', 
                            '%s', 
                            '%d'
                        ),
                        array( // where data types
                            '%d'
                            )) !== false){

                            // Successfully updated - Return id
                            return $id;

                        } else {

                            // FAILED update
                            return false;

                        }

        } else {
            
            #} No ID - must be an INSERT
            if ($wpdb->insert( 
                        $ZBSCRM_t['tags'], 
                        array( 

                            // ownership
                            'zbs_site' => zeroBSCRM_site(),
                            'zbs_team' => zeroBSCRM_team(),
                            'zbs_owner' => $data['owner'],

                            // fields
                            'zbstag_objtype' => $data['objtype'],
                            'zbstag_name' => $data['name'],
                            'zbstag_slug' => $data['slug'],
                            'zbstag_created' => time(),
                            'zbstag_lastupdated' => time()
                        ), 
                        array( // field data types
                            '%d',  // site
                            '%d',  // team
                            '%d',  // owner

                            '%d',  
                            '%s',  
                            '%s',  
                            '%d',  
                            '%d'  
                        ) ) > 0){

                    #} Successfully inserted, lets return new ID
                    $newID = $wpdb->insert_id;
                    return $newID;

                } else {

                    #} Failed to Insert
                    return false;

                }

        }

        return false;

    }

     /**
     * adds or updates any object's tags
     * ... this is really just a wrapper for addUpdateTagObjLinks
     *
     * @param array $args Associative array of arguments
     *              id (if update), owner, data (array of field data)
     *
     * @return int line ID
     */
    public function addUpdateObjectTags($args=array()){

        global $ZBSCRM_t,$wpdb;

        #} ============ LOAD ARGS =============
        $defaultArgs = array(

            'objid'         => -1, // REQ
            'objtype'       => -1, // REQ

            // EITHER of the following:
            'tagIDs'        => -1,
            'tags'          => -1,

            'mode'          => 'replace'

        ); foreach ($defaultArgs as $argK => $argV){ $$argK = $argV; if (is_array($args) && isset($args[$argK])) {  if (is_array($args[$argK])){ $newData = $$argK; if (!is_array($newData)) $newData = array(); foreach ($args[$argK] as $subK => $subV){ $newData[$subK] = $subV; }$$argK = $newData;} else { $$argK = $args[$argK]; } } }
        #} =========== / LOAD ARGS ============

        #} ========== CHECK FIELDS ============

            // check id
            $objid = (int)$objid; if (empty($objid) || $objid <= 0) return false;

            // check obtype is legit (if completed)
            if (!isset($objtype) || $objtype == -1 || $this->objTypeKey($objtype) == -1) return false;

        #} ========= / CHECK FIELDS ===========


            #} If using tags, convert these to id's :)
            if ($tags !== -1 && is_array($tags)){

                // overwrite
                $tagIDs = array();

                // cycle through + find
                foreach ($tags as $tag){

                    $tagID = $this->getTag(-1,array(
                        'objtype'       => $objtype,
                        'name'          => $tag,
                        'onlyID' => true
                        ));

                    if (!empty($tagID)) 
                        $tagIDs[] = $tagID;
                    else {
                        
                        //create
                        $tagID = $this->addUpdateTag(array(
                                                            'data'=>array(
                                                                'objtype'       => $objtype,
                                                                'name'          => $tag))); 
                        //add
                        if (!empty($tagID)) $tagIDs[] = $tagID;

                    }
                }

            }

        return $this->addUpdateTagObjLinks(array(
                'objtype'   =>$objtype,
                'objid'     =>$objid,
                'tagIDs'    =>$tagIDs,
                'mode'      =>$mode));

    }

     /**
     * deletes a tag object
     *
     * @param array $args Associative array of arguments
     *              id
     *
     * @return int success;
     */
    public function deleteTag($args=array()){

        global $ZBSCRM_t,$wpdb;

        #} ============ LOAD ARGS =============
        $defaultArgs = array(

            'id'            => -1,
            'deleteLinks'   => true

        ); foreach ($defaultArgs as $argK => $argV){ $$argK = $argV; if (is_array($args) && isset($args[$argK])) {  if (is_array($args[$argK])){ $newData = $$argK; if (!is_array($newData)) $newData = array(); foreach ($args[$argK] as $subK => $subV){ $newData[$subK] = $subV; }$$argK = $newData;} else { $$argK = $args[$argK]; } } }
        #} =========== / LOAD ARGS ============

        #} Check ID & Delete :)
        $id = (int)$id;
        if (!empty($id) && $id > 0) {

            $deleted = zeroBSCRM_db2_deleteGeneric($id,'tags');

            // if links, also delete them!
            if ($deleteLinks){

                $deletedLinks = $wpdb->delete( 
                    $ZBSCRM_t['taglinks'], 
                    array( // where
                        'zbstl_tagid' => $id
                        ),
                    array(
                        '%d'
                        )
                    );
            }

            return $deleted;

        }

        return false;

    }

     /**
     * retrieves stats for tag (how many contacts/obj's use this tag) (effectively counts tag links split per obj)
     *
     * @param array $args Associative array of arguments
     *              id
     *
     * @return array
     */
    public function getAllTagStats($args=array()){

        #} ============ LOAD ARGS =============
        $defaultArgs = array(

            'id'            => -1,
            'owner'         => -1,

            // permissions
            //'ignoreowner'     => false // this'll let you not-check the owner of obj
            // NOTE 'owner' will ALWAYS be ignored by this, but allows for team/site
            // Tags don't need owners yet :)

        ); foreach ($defaultArgs as $argK => $argV){ $$argK = $argV; if (is_array($args) && isset($args[$argK])) {  if (is_array($args[$argK])){ $newData = $$argK; if (!is_array($newData)) $newData = array(); foreach ($args[$argK] as $subK => $subV){ $newData[$subK] = $subV; }$$argK = $newData;} else { $$argK = $args[$argK]; } } }
        #} =========== / LOAD ARGS ============
        
        $ignoreowner = true; 

        #} Check ID
        $id = (int)$id;
        if (!empty($id) && $id > 0){

            global $ZBSCRM_t,$wpdb; 
            $wheres = array('direct'=>array()); $whereStr = ''; $additionalWhere = ''; $params = array(); $res = array();

            #} Build query
            $query = "SELECT COUNT(zbstl_objid) c, zbstl_objtype FROM ".$ZBSCRM_t['taglinks'];

            #} ============= WHERE ================

                #} Add ID
                $wheres['zbstl_tagid'] = array('zbstl_tagid','=','%d',$id);


                #} If 'owner' is set then have to ignore owner, because can't do both
                if (isset($owner) && $owner > 0) {
                    
                    // stops ownership check
                    $ignoreowner = true;

                    // adds owner to query
                    $wheres['zbs_owner'] = array('zbs_owner','=','%d',$owner);

                }

            #} ============ / WHERE ==============

            #} Build out any WHERE clauses
            $wheresArr = $this->buildWheres($wheres,$whereStr,$params);
            $whereStr = $wheresArr['where']; $params = $params + $wheresArr['params'];
            #} / Build WHERE

            #} Ownership v1.0 - the following adds SITE + TEAM checks, and (optionally), owner
            $params = array_merge($params,$this->ownershipQueryVars($ignoreowner)); // merges in any req.
            $ownQ = $this->ownershipSQL($ignoreowner); if (!empty($ownQ)) $additionalWhere = $this->spaceAnd($additionalWhere).$ownQ; // adds str to query
            #} / Ownership

            #} ============ CUSTOM GROUP/ORDERBY ==============
            
                // this allows grouping :) 
                $orderByCustom = ' GROUP BY zbstl_objtype ORDER BY c ASC';

            #} ============ / CUSTOM GROUP/ORDERBY ============

            #} Append to sql (and use our custom order by etc.)
            $query .= $this->buildWhereStr($whereStr,$additionalWhere) . $orderByCustom;

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
                            
                        // tidy
                        $res[] = $this->tidy_tagstat($resDataLine);

                }
            }

            return $res;

        } // / if ID

        return false;

    }

     /**
     * retrieves stats for tag (how many contacts/obj's use this tag) 
     * this version returns specific count of uses for an objtypeid
     *
     * @param array $args Associative array of arguments
     *              id
     *
     * @return array
     */
    public function getTagObjStats($args=array()){

        #} ============ LOAD ARGS =============
        $defaultArgs = array(

            'id'            => -1,
            'objtypeid'     => -1,
            'owner'         => -1,

            // permissions
            //'ignoreowner'     => false // this'll let you not-check the owner of obj
            // NOTE 'owner' will ALWAYS be ignored by this, but allows for team/site
            // Tags don't need owners yet :)

            // returns scalar ID of line
            'onlyID'        => false

        ); foreach ($defaultArgs as $argK => $argV){ $$argK = $argV; if (is_array($args) && isset($args[$argK])) {  if (is_array($args[$argK])){ $newData = $$argK; if (!is_array($newData)) $newData = array(); foreach ($args[$argK] as $subK => $subV){ $newData[$subK] = $subV; }$$argK = $newData;} else { $$argK = $args[$argK]; } } }
        #} =========== / LOAD ARGS ============
        
        $ignoreowner = true; 

        #} Check ID
        $id = (int)$id;
        if (!empty($id) && $id > 0){

            global $ZBSCRM_t,$wpdb; 
            $wheres = array('direct'=>array()); $whereStr = ''; $additionalWhere = ''; $params = array(); $res = array();

            #} Build query
            $query = "SELECT COUNT(zbstl_objid) c, zbstl_objtype FROM ".$ZBSCRM_t['taglinks'];

            #} ============= WHERE ================

                #} Add ID
                $wheres['zbstl_tagid'] = array('zbstl_tagid','=','%d',$id);

                #} Adds a specific type id
                if (!empty($objtypeid)){

                    $wheres['zbstl_objtype'] = array('zbstl_objtype','=','%d',$objtypeid);

                }


                #} If 'owner' is set then have to ignore owner, because can't do both
                if (isset($owner) && $owner > 0) {
                    
                    // stops ownership check
                    $ignoreowner = true;

                    // adds owner to query
                    $wheres['zbs_owner'] = array('zbs_owner','=','%d',$owner);

                }

            #} ============ / WHERE ==============

            #} Build out any WHERE clauses
            $wheresArr = $this->buildWheres($wheres,$whereStr,$params);
            $whereStr = $wheresArr['where']; $params = $params + $wheresArr['params'];
            #} / Build WHERE

            #} Ownership v1.0 - the following adds SITE + TEAM checks, and (optionally), owner
            $params = array_merge($params,$this->ownershipQueryVars($ignoreowner)); // merges in any req.
            $ownQ = $this->ownershipSQL($ignoreowner); if (!empty($ownQ)) $additionalWhere = $this->spaceAnd($additionalWhere).$ownQ; // adds str to query
            #} / Ownership

            #} ============ CUSTOM GROUP/ORDERBY ==============
            
                // this allows grouping :) 
                $orderByCustom = ' GROUP BY zbstl_objtype ORDER BY c ASC LIMIT 0,1';

            #} ============ / CUSTOM GROUP/ORDERBY ============

            #} Append to sql (and use our custom order by etc.)
            $query .= $this->buildWhereStr($whereStr,$additionalWhere) . $orderByCustom;

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
            
                #} Only ID? return it directly
                if ($onlyID === true) return $potentialRes->ID;

                #} Has results, tidy + return 
                return $this->tidy_tagstat($potentialRes);

            }

        } // / if ID

        return false;

    }

    /**
     * tidy's the object from wp db into clean array
     *
     * @param array $obj (DB obj)
     *
     * @return array (clean obj)
     */
    private function tidy_tag($obj=false){

            $res = false;

            if (isset($obj->ID)){
            $res = array();
            $res['id'] = $obj->ID;
            /* 
              `zbs_site` INT NULL DEFAULT NULL,
              `zbs_team` INT NULL DEFAULT NULL,
              `zbs_owner` INT NOT NULL,
            */

            $res['objtype'] = $obj->zbstag_objtype;
            $res['name'] = $this->stripSlashes($obj->zbstag_name);
            $res['slug'] = $obj->zbstag_slug;


            $res['created'] = $obj->zbstag_created;
            $res['lastupdated'] = $obj->zbstag_lastupdated;

        } 

        return $res;


    }

    /**
     * tidy's the object from wp db into clean array
     *
     * @param array $obj (DB obj)
     *
     * @return array (clean obj)
     */
    private function tidy_tagstat($obj=false){

            $res = false;

            if (isset($obj->ID)){
            $res = array();
            $res['count'] = $obj->c;
            $res['objtypeid'] = $obj->zbstl_objtype;
            $res['objtype'] = $this->objTypeKey($obj->zbstl_objtype);

        } 

        return $res;


    }

    // =========== / TAGS      =======================================================
    // ===============================================================================





    // ===============================================================================
    // ===========   TAG LINKS  =======================================================
    /**
     * returns tags against an obj type (e.g. contact tags)
     *
     * @param array $args   Associative array of arguments
     *                      objtypeid
     *
     * @return array result
     */
    public function getTagsForObjType($args=array()){

        #} =========== LOAD ARGS ==============
        $defaultArgs = array(

            'objtypeid'     => -1,

            // select
            'excludeEmpty'  => -1,

            // with
            'withCount'     => -1,
            
            // sort
            'sortByField'   => 'zbstag_name',
            'sortOrder'     => 'ASC',

            'page'          => 0, // this is what page it is (gets * by for limit)
            'perPage'       => 10000

            // permissions
            //'ignoreowner'     => false // this'll let you not-check the owner of obj
            // NOTE 'owner' will ALWAYS be ignored by this, but allows for team/site
            // Tags don't need owners yet :)

        ); foreach ($defaultArgs as $argK => $argV){ $$argK = $argV; if (is_array($args) && isset($args[$argK])) {  if (is_array($args[$argK])){ $newData = $$argK; if (!is_array($newData)) $newData = array(); foreach ($args[$argK] as $subK => $subV){ $newData[$subK] = $subV; }$$argK = $newData;} else { $$argK = $args[$argK]; } } }
        #} =========== / LOAD ARGS =============
        
        $ignoreowner = true;

        #} Check ID
        $objtypeid = (int)$objtypeid;
        if (!empty($objtypeid) && $objtypeid > 0){

            global $ZBSCRM_t,$wpdb; 
            $wheres = array('direct'=>array()); $whereStr = ''; $additionalWhere = ''; $params = array(); $res = array();


            #} ============ EXTRA SELECT ==============

                $extraSelect = '';

                if ($withCount !== -1 || $excludeEmpty !== -1) {

                    // could make this distinct zbstl_objid if need more precision
                    // NOTE! Ownership leak here - this'll count GLOBALLY! todo: add ownership into this subquery
                    $extraSelect = ',(SELECT COUNT(taglink.ID) FROM '.$ZBSCRM_t['taglinks'].' taglink WHERE zbstl_tagid = tags.ID AND zbstl_objtype = %d) tagcount';
                    $params[] = $objtypeid;

                }

            #} ============ / EXTRA SELECT ==============

            #} Build query
            $query = "SELECT tags.*".$extraSelect." FROM ".$ZBSCRM_t['tags'].' tags';

            #} ============= WHERE ================
                
                // type id
                $wheres['zbstag_objtype'] = array('zbstag_objtype','=','%d',$objtypeid);

                // if exclude empty
                if ($excludeEmpty){
                    $wheres['direct'][] = array('(SELECT COUNT(taglink.ID) FROM '.$ZBSCRM_t['taglinks'].' taglink WHERE zbstl_tagid = tags.ID AND zbstl_objtype = %d) > 0',array($objtypeid));

                }

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
                            
                        // tidy
                        $resArr = $this->tidy_tag($resDataLine);

                        if ($withCount !== -1){

                            if (isset($resDataLine->tagcount))
                                $resArr['count'] = $resDataLine->tagcount;
                            else
                                $resArr['count'] = -1;

                        }

                        $res[] = $resArr;

                }
            }

            return $res;

        } // / if ID

        return false;

    }
    /**
     * returns tags against an obj (e.g. contact id 101)
     *
     * @param array $args   Associative array of arguments
     *                      objtypeid, objid
     *
     * @return array result
     */
    public function getTagsForObjID($args=array()){

        #} =========== LOAD ARGS ==============
        $defaultArgs = array(

            'objtypeid'     => -1,
            'objid'         => -1,

            // with
            'withCount'     => -1,
            'onlyID'        => -1,

            // permissions
            //'ignoreowner'     => false // this'll let you not-check the owner of obj
            // NOTE 'owner' will ALWAYS be ignored by this, but allows for team/site
            // Tags don't need owners yet :)

        ); foreach ($defaultArgs as $argK => $argV){ $$argK = $argV; if (is_array($args) && isset($args[$argK])) {  if (is_array($args[$argK])){ $newData = $$argK; if (!is_array($newData)) $newData = array(); foreach ($args[$argK] as $subK => $subV){ $newData[$subK] = $subV; }$$argK = $newData;} else { $$argK = $args[$argK]; } } }
        #} =========== / LOAD ARGS =============
        
        $ignoreowner = true;
        
        #} Check ID
        $objtypeid = (int)$objtypeid; $objid = (int)$objid; 
        if (!empty($objtypeid) && $objtypeid > 0 && !empty($objid) && $objid > 0){

            global $ZBSCRM_t,$wpdb; 
            $wheres = array('direct'=>array()); $whereStr = ''; $additionalWhere = ''; $params = array(); $res = array();

            #} Build query
            $query = "SELECT * FROM ".$ZBSCRM_t['tags'];

            #} ============= WHERE ================

                #} Add ID
                // rather than using the $wheres, here we have to manually add, because sub queries don't work otherwise.
                $whereStr = ' WHERE ID in (SELECT zbstl_tagid FROM '.$ZBSCRM_t['taglinks'].' WHERE zbstl_objtype = %d AND zbstl_objid = %d)';
                $params[] = $objtypeid; $params[] = $objid;

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
            $query .= $this->buildWhereStr($whereStr,$additionalWhere) . $this->buildSort('ID','DESC') . $this->buildPaging(0,10000);

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
                            

                        #} Only ID? return it directly
                        if ($onlyID === true) 
                            $resObj = $resDataLine->ID;
                        else
                            // tidy
                            $resObj = $this->tidy_tag($resDataLine);

                        if ($withCount){


                        }

                        $res[] = $resObj;

                }
            }

            return $res;

        } // / if ID

        return false;

    }


     /**
     * adds or updates a tag link object
     * this says "match tag X with obj Y" (effectively 'tagging' it)
     * NOTE: DO NOT CALL DIRECTLY, ALWAYS use addUpdateTagObjLinks (or it's wrappers) - because those fire actions :)
     *
     * @param array $args Associative array of arguments
     *              id (if update - probably never used here), data(objtype,objid,tagid)
     *
     * @return int line ID
     */
    public function addUpdateTagObjLink($args=array()){

        global $ZBSCRM_t,$wpdb;

        #} ============ LOAD ARGS =============
        $defaultArgs = array(

            'id'            => -1,
            'owner'         => -1,

            // fields (directly)
            'data'          => array(

                'objtype'       => -1,
                'objid'         => -1,
                'tagid'         => -1

            )

        ); foreach ($defaultArgs as $argK => $argV){ $$argK = $argV; if (is_array($args) && isset($args[$argK])) {  if (is_array($args[$argK])){ $newData = $$argK; if (!is_array($newData)) $newData = array(); foreach ($args[$argK] as $subK => $subV){ $newData[$subK] = $subV; }$$argK = $newData;} else { $$argK = $args[$argK]; } } }
        #} =========== / LOAD ARGS ============

        #} ========== CHECK FIELDS ============

            // check obtype is completed + legit
            if (!isset($data['objtype']) || empty($data['objtype'])) return false;
            if ($this->objTypeKey($data['objtype']) === -1) return false;

            // if owner = -1, add current
            if (!isset($owner) || $owner === -1) $owner = zeroBSCRM_user();

            $objid = (int)$data['objid']; $tagid = (int)$data['tagid'];
            if (empty($data['objid']) || $data['objid'] < 1 || empty($data['tagid']) || $data['tagid'] < 1) return false;

        #} ========= / CHECK FIELDS ===========

        #} Check if ID present
        $id = (int)$id;
        if (!empty($id) && $id > 0){

                #} Check if obj exists (here) - for now just brutal update (will error when doesn't exist)

                #} Attempt update
                if ($wpdb->update( 
                        $ZBSCRM_t['taglinks'], 
                        array( 

                            // ownership
                            // no need to update these (as of yet) - can't move teams etc.
                            //'zbs_site' => zeroBSCRM_installSite(),
                            //'zbs_team' => zeroBSCRM_installTeam(),
                            'zbs_owner' => $owner,

                            // fields
                            'zbstl_objtype' => $data['objtype'],
                            'zbstl_objid' => $data['objid'],
                            'zbstl_tagid' => $data['tagid']
                        ), 
                        array( // where
                            'ID' => $id
                            ),
                        array( // field data types
                            '%d',
                            '%d', 
                            '%d', 
                            '%d'
                        ),
                        array( // where data types
                            '%d'
                            )) !== false){

                            // Successfully updated - Return id
                            return $id;

                        } else {

                            // FAILED update
                            return false;

                        }

        } else {
            
            #} No ID - must be an INSERT
            if ($wpdb->insert( 
                        $ZBSCRM_t['taglinks'], 
                        array( 

                            // ownership
                            'zbs_site' => zeroBSCRM_site(),
                            'zbs_team' => zeroBSCRM_team(),
                            'zbs_owner' => $owner,

                            // fields
                            'zbstl_objtype' => $data['objtype'],
                            'zbstl_objid' => $data['objid'],
                            'zbstl_tagid' => $data['tagid']
                        ), 
                        array( // field data types
                            '%d',  // site
                            '%d',  // team
                            '%d',  // owner

                            '%d',  
                            '%d',  
                            '%d'  
                        ) ) > 0){

                    #} Successfully inserted, lets return new ID
                    $newID = $wpdb->insert_id;
                    return $newID;

                } else {

                    #} Failed to Insert
                    return false;

                }

        }

        return false;

    }


     /**
     * adds or updates tag link objects against an obj
     * this says "match tag X,Y,Z with obj Y" (effectively 'tagging' it)
     *
     * @param array $args Associative array of arguments
     *              objtype,objid,tags (array of tagids)
     *
     * @return array $tags
     */
    public function addUpdateTagObjLinks($args=array()){

        global $ZBSCRM_t,$wpdb;

        #} ============ LOAD ARGS =============
        $defaultArgs = array(

            'owner'         => -1,

            'objtype'       => -1,
            'objid'         => -1,
            'tagIDs'        => -1, // array of tag ID's 

            'mode'          => 'replace' // replace|append|remove

        ); foreach ($defaultArgs as $argK => $argV){ $$argK = $argV; if (is_array($args) && isset($args[$argK])) {  if (is_array($args[$argK])){ $newData = $$argK; if (!is_array($newData)) $newData = array(); foreach ($args[$argK] as $subK => $subV){ $newData[$subK] = $subV; }$$argK = $newData;} else { $$argK = $args[$argK]; } } }
        #} =========== / LOAD ARGS ============


        #} ========== CHECK FIELDS ============

            // check obtype is completed + legit
            if (!isset($objtype) || empty($objtype)) return false;
            if ($this->objTypeKey($objtype) === -1) return false;

            // if owner = -1, add current
            if (!isset($owner) || $owner === -1) $owner = zeroBSCRM_user();

            // tagging id
            $objid = (int)$objid; if (empty($objid) || $objid < 1) return false;

            // tag list
            if (!is_array($tagIDs)) return false;

            // mode
            if (gettype($mode) != 'string' || !in_array($mode, array('replace','append','remove'))) return false;

        #} ========= / CHECK FIELDS ===========

            switch ($mode){

                case 'replace':

                    // (for actions) log starting objs
                    $existingTagIDs = $this->getTagsForObjID(array('objtypeid'=>$objtype,'objid'=>$objid,'onlyID'=>true));
                    if (!is_array($existingTagIDs)) $existingTagIDs = array();
                    $removedTagsByID = array(); $addedTagsByID = array();
        
                    // cull all previous
                    $deleted = $this->deleteTagObjLinks(array('objid'=>$objid,'objtype'=>$objtype));

                    // cycle through & add
                    foreach ($tagIDs as $tid){

                        $added = $this->addUpdateTagObjLink(array(
                            'data'=>array(
                                'objid'     =>  $objid,
                                'objtype'   =>  $objtype,
                                'tagid'     =>  $tid)));

                        if ($added !== false){
                            
                            if (!in_array($tid, $existingTagIDs)) 
                                $addedTagsByID[] = $tid; // tag was added
                            //else 
                                // tag was already in there, just re-added
                        }

                    }

                    // actions

                        // check removed
                         foreach ($existingTagIDs as $tid){

                            if (!in_array($tid, $tagIDs)) $removedTagsByID[] = $tid;

                         }

                        // fire actions for each tag

                            // added to
                            if (count($addedTagsByID) > 0) foreach ($addedTagsByID as $tagID) do_action('zbs_tag_added_to_objid',$tagID, $objtype, $objid);

                            // removed from
                            if (count($removedTagsByID) > 0) foreach ($removedTagsByID as $tagID) do_action('zbs_tag_removed_from_objid',$tagID, $objtype, $objid);


                    // return
                    return true;

                    break;

                case 'append':

                    // get existing
                    $existingTagIDs = $this->getTagsForObjID(array('objtypeid'=>$objtype,'objid'=>$objid,'onlyID'=>true));

                    // make just ids
                    // no need, added ,'onlyID'=>true above
                    //$existingTagIDs = array(); foreach ($tags as $t) $existingTagIDs[] = $t['id'];

                    // cycle through& add
                    foreach ($tagIDs as $tid){

                        if (!in_array($tid,$existingTagIDs)){

                            // add a link
                            $this->addUpdateTagObjLink(array(
                            'data'=>array(
                                'objid'     =>  $objid,
                                'objtype'   =>  $objtype,
                                'tagid'     =>  $tid)));

                            // fire action
                            do_action('zbs_tag_added_to_objid',$tid, $objtype, $objid);

                        }

                    }
                    return true;

                    break;

                case 'remove':

                    // get existing
                    $existingTagIDs = $this->getTagsForObjID(array('objtypeid'=>$objtype,'objid'=>$objid,'onlyID'=>true));

                    // cycle through & remove links
                    foreach ($tagIDs as $tid){

                        if (in_array($tid, $existingTagIDs)){

                            // delete link
                            $this->deleteTagObjLink(array(
                                'objid'     =>  $objid,
                                'objtype'   =>  $objtype,
                                'tagid'     =>  $tid));

                            // action
                            do_action('zbs_tag_removed_from_objid',$tid, $objtype, $objid);

                        }


                    }

                    return true;

                    break;


            }


        return false;

    }

     /**
     * deletes a tag object link
     *
     * @param array $args Associative array of arguments
     *              id
     *
     * @return int success;
     */
    public function deleteTagObjLink($args=array()){

        global $ZBSCRM_t,$wpdb;

        #} ============ LOAD ARGS =============
        $defaultArgs = array(

            'id'            => -1,

            // or...

            'objtype'       => -1,
            'objid'         => -1,
            'tagid'         => -1


        ); foreach ($defaultArgs as $argK => $argV){ $$argK = $argV; if (is_array($args) && isset($args[$argK])) {  if (is_array($args[$argK])){ $newData = $$argK; if (!is_array($newData)) $newData = array(); foreach ($args[$argK] as $subK => $subV){ $newData[$subK] = $subV; }$$argK = $newData;} else { $$argK = $args[$argK]; } } }
        #} =========== / LOAD ARGS ============

        #} Check ID & Delete :) (IF ID PRESENT)
        $id = (int)$id;
        if (!empty($id) && $id > 0) return zeroBSCRM_db2_deleteGeneric($id,'taglinks');

        #} ... else delete by objtype etc.

        #} ========== CHECK FIELDS ============

            // check obtype is completed + legit
            if (!isset($objtype) || empty($objtype)) return false;
            if ($this->objTypeKey($objtype) === -1) return false;
            
            // obj id
            $objid = (int)$objid; if (empty($objid) || $objid < 1) return false;

            // tag id
            $tagid = (int)$tagid; if (empty($tagid) || $tagid < 1) return false;

            // CHECK PERMISSIONS?

        #} ========= / CHECK FIELDS ===========

            #} ... if here then is trying to delete specific tag linkid
            return $wpdb->delete( 
                        $ZBSCRM_t['taglinks'], 
                        array( // where
                            'zbstl_objtype' => $objtype,
                            'zbstl_objid' => $objid,
                            'zbstl_tagid' => $tagid
                            ),
                        array(
                            '%d',
                            '%d',
                            '%d'
                            )
                        );

    }

     /**
     * deletes all tag object links for a specific obj
     *
     * @param array $args Associative array of arguments
     *              id
     *
     * @return int success;
     */
    public function deleteTagObjLinks($args=array()){

        global $ZBSCRM_t,$wpdb;

        #} ============ LOAD ARGS =============
        $defaultArgs = array(

            'objtype'       => -1,
            'objid'         => -1,

        ); foreach ($defaultArgs as $argK => $argV){ $$argK = $argV; if (is_array($args) && isset($args[$argK])) {  if (is_array($args[$argK])){ $newData = $$argK; if (!is_array($newData)) $newData = array(); foreach ($args[$argK] as $subK => $subV){ $newData[$subK] = $subV; }$$argK = $newData;} else { $$argK = $args[$argK]; } } }
        #} =========== / LOAD ARGS ============

        #} ========== CHECK FIELDS ============

            // check obtype is completed + legit
            if (!isset($objtype) || empty($objtype)) return false;
            if ($this->objTypeKey($objtype) === -1) return false;
            
            // obj id
            $objid = (int)$objid; if (empty($objid) || $objid < 1) return false;

            // CHECK PERMISSIONS?

        #} ========= / CHECK FIELDS ===========

        // brutal
        return $wpdb->delete( 
                    $ZBSCRM_t['taglinks'], 
                    array( // where
                        'zbstl_objtype' => $objtype,
                        'zbstl_objid' => $objid
                        ),
                    array(
                        '%d',
                        '%d'
                        )
                    );

    }


    /**
     * tidy's the object from wp db into clean array
     *
     * @param array $obj (DB obj)
     *
     * @return array (clean obj)
     */
    private function tidy_taglink($obj=false){

            $res = false;

            if (isset($obj->ID)){
            $res = array();
            $res['id'] = $obj->ID;
            /* 
              `zbs_site` INT NULL DEFAULT NULL,
              `zbs_team` INT NULL DEFAULT NULL,
              `zbs_owner` INT NOT NULL,
            */

            $res['objtype'] = $obj->zbstag_objtype;
            $res['name'] = $this->stripSlashes($obj->zbstag_name);
            $res['slug'] = $obj->zbstag_slug;


            $res['created'] = $obj->zbstag_created;
            $res['lastupdated'] = $obj->zbstag_lastupdated;

        } 

        return $res;


    }

    // =========== / TAG LINKS      ==================================================
    // ===============================================================================



    // ===============================================================================
    // ===========   CONTACTS  =======================================================
    /**
     * returns full contact line +- details
     * Replaces many funcs, inc zeroBS_getCustomerIDFromWPID, zeroBS_getCustomerIDWithEmail etc.
     *
     * @param int id        contact id
     * @param array $args   Associative array of arguments
     *                      withQuotes, withInvoices, withTransactions, withLogs
     *
     * @return array result
     */
    public function getContact($id=-1,$args=array()){

        #} =========== LOAD ARGS ==============
        $defaultArgs = array(

            'email'             => false, // if id -1 and email given, will return based on email search
            'WPID'              => false, // if id -1 and wpid given, will return based on wpid search

            // if theset wo passed, will search based on these 
            'externalSource'    => false,
            'externalSourceUID' => false,

            // with what?
            'withCustomFields'  => true,
            'withQuotes'        => false,
            'withInvoices'      => false,
            'withTransactions'  => false,
            'withLogs'          => false,
            'withLastLog'       => false,
            'withTags'          => false,
            'withCompanies'     => false,
            'withOwner'         => false,

            // permissions
            'ignoreowner'   => false, // this'll let you not-check the owner of obj

            // returns scalar ID of line
            'onlyID'        => false,

            'fields'        => false // false = *, array = fieldnames

        ); foreach ($defaultArgs as $argK => $argV){ $$argK = $argV; if (is_array($args) && isset($args[$argK])) {  if (is_array($args[$argK])){ $newData = $$argK; if (!is_array($newData)) $newData = array(); foreach ($args[$argK] as $subK => $subV){ $newData[$subK] = $subV; }$$argK = $newData;} else { $$argK = $args[$argK]; } } }
        #} =========== / LOAD ARGS =============
        
        #} Check ID
        $id = (int)$id;
        if (
            (!empty($id) && $id > 0)
            ||
            (!empty($email))
            ||
            (!empty($externalSource) && !empty($externalSourceUID))
            ){

            global $ZBSCRM_t,$wpdb; 
            $wheres = array('direct'=>array()); $whereStr = ''; $additionalWhere = ''; $params = array(); $res = array(); $extraSelect = '';


            #} ============= PRE-QUERY ============

                #} Custom Fields
                if ($withCustomFields && !$onlyID){
                    
                    #} Retrieve any cf
                    $custFields = $this->getActiveCustomFields(array('objtypeid'=>ZBS_TYPE_CONTACT));

                    #} Cycle through + build into query
                    if (is_array($custFields)) foreach ($custFields as $cK => $cF){

                        // add as subquery
                        $extraSelect .= ',(SELECT zbscf_objval FROM '.$ZBSCRM_t['customfields']." WHERE zbscf_objid = contact.ID AND zbscf_objkey = %s AND zbscf_objtype = %d) '".$cK."'";
                        
                        // add params
                        $params[] = $cK; $params[] = ZBS_TYPE_CONTACT;

                    }

                }

                // Add any addr custom fields for addr1+addr2
                $addrCustomFields = zeroBSCRM_getAddressCustomFields();
                if ($withCustomFields && !$onlyID && is_array($addrCustomFields) && count($addrCustomFields) > 0){

                    foreach ($addrCustomFields as $cK => $cF){

                        // hacky temp solution.
                        $cKN = (int)$cK+1;
                        $cKey = 'addr_cf'.$cKN;
                        $cKey2 = 'secaddr_cf'.$cKN;

                        // addr1
                            // add as subquery
                            $extraSelect .= ',(SELECT zbscf_objval FROM '.$ZBSCRM_t['customfields']." WHERE zbscf_objid = contact.ID AND zbscf_objkey = %s AND zbscf_objtype = %d) '".$cKey."'";                        
                            // add params
                            $params[] = $cKey; $params[] = ZBS_TYPE_CONTACT;
                            // add as subquery
                            $extraSelect .= ',(SELECT zbscf_objval FROM '.$ZBSCRM_t['customfields']." WHERE zbscf_objid = contact.ID AND zbscf_objkey = %s AND zbscf_objtype = %d) '".$cKey2."'";                        
                            // add params
                            $params[] = $cKey2; $params[] = ZBS_TYPE_CONTACT;

                    }


                }

                $selector = 'contact.*';
                if (isset($fields) && is_array($fields)) {
                    $selector = '';

                    // always needs id, so add if not present
                    if (!in_array('ID',$fields)) $selector = 'contact.ID';

                    foreach ($fields as $f) {
                        if (!empty($selector)) $selector .= ',';
                        $selector .= 'contact.'.$f;
                    }
                } else if ($onlyID){
                    $selector = 'contact.ID';
                }

            #} ============ / PRE-QUERY ===========


            #} Build query
            $query = "SELECT ".$selector.$extraSelect." FROM ".$ZBSCRM_t['contacts'].' as contact';
            #} ============= WHERE ================

                if (!empty($id) && $id > 0){

                    #} Add ID
                    $wheres['ID'] = array('ID','=','%d',$id);

                } 

                if (!empty($email)){

                    $emailWheres = array();

                    #} Add ID
                    $emailWheres['emailcheck'] = array('zbsc_email','=','%s',$email);

                    #} Check AKA
                    $emailWheres['email_alias'] = array('ID','IN',"(SELECT aka_id FROM ".$ZBSCRM_t['aka']." WHERE aka_type = ".zeroBS_getAKAType('customer')." AND aka_alias = %s)",$email);
                    
                    // This generates a query like 'zbsc_email = %s OR zbsc_email2 = %s', 
                    // which we then need to include as direct subquery (below) in main query :)
                    $emailSearchQueryArr = $this->buildWheres($emailWheres,'',array(),'OR',false);
                    
                    if (is_array($emailSearchQueryArr) && isset($emailSearchQueryArr['where']) && !empty($emailSearchQueryArr['where'])){

                        // add it
                        $wheres['direct'][] = array('('.$emailSearchQueryArr['where'].')',$emailSearchQueryArr['params']);

                    }

                } 

                if (!empty($WPID) && $WPID > 0){

                    #} Add ID
                    $wheres['WPID'] = array('zbsc_wpid','=','%d',$WPID);

                } 
                
                if (!empty($externalSource) && !empty($externalSourceUID)){

                    $wheres['extsourcecheck'] = array('ID','IN','(SELECT DISTINCT zbss_objid FROM '.$ZBSCRM_t['externalsources']." WHERE zbss_objtype = ".ZBS_TYPE_CONTACT." AND zbss_source = %s AND zbss_uid = %s)",array($externalSource,$externalSourceUID));

                }

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
                    if ($onlyID) return $potentialRes->ID;
                
                    // tidy
                    if (is_array($fields)){
                        // guesses fields based on table col names
                        $res = $this->lazyTidyGeneric($potentialRes);
                    } else {
                        // proper tidy
                        $res = $this->tidy_contact($potentialRes,$withCustomFields);
                    }

                    if ($withTags){

                        // add all tags lines
                        $res['tags'] = $this->getTagsForObjID(array('objtypeid'=>ZBS_TYPE_CONTACT,'objid'=>$potentialRes->ID));
                    
                    }

                    // ===================================================
                    // ========== #} #DB1LEGACY (TOMOVE)
                    // == Following is all using OLD DB stuff, here until we migrate inv etc.
                    // ===================================================

                    #} With most recent log? #DB1LEGACY (TOMOVE)
                    if ($withLastLog){

                        $res['lastlog'] = $this->getLogsForObj(array(

                                                'objtype' => ZBS_TYPE_CONTACT,
                                                'objid' => $potentialRes->ID,

                                                'incMeta'   => true,

                                                'sortByField'   => 'zbsl_created',
                                                'sortOrder'     => 'DESC',
                                                'page'          => 0,
                                                'perPage'       => 1

                                            ));

                    }

                    #} With Assigned?
                    if ($withOwner){

                        $retObj['owner'] = zeroBS_getOwner($potentialRes->ID);

                    }

                    #} use sql instead #DB1LEGACY (TOMOVE)
                    if ($withInvoices && $withQuotes && $withTransactions){

                        $custDeets = zeroBS_getCustomerExtrasViaSQL($potentialRes->ID);
                        $res['quotes'] = $custDeets['quotes'];
                        $res['invoices'] = $custDeets['invoices'];
                        $res['transactions'] = $custDeets['transactions'];


                    } else {

                        #} These might be broken again into counts/dets? more efficient way? #DB1LEGACY (TOMOVE)
                        if ($withInvoices){
                            
                            #} only gets first 100?
                            #} CURRENTLY inc meta..? (isn't huge... but isn't efficient)
                            $res['invoices']        = zeroBS_getInvoicesForCustomer($potentialRes->ID,true,100);

                        }

                        #} These might be broken again into counts/dets? more efficient way? #DB1LEGACY (TOMOVE)
                        if ($withQuotes){

                            
                            #} only gets first 100?
                            $res['quotes']      = zeroBS_getQuotesForCustomer($potentialRes->ID,true,100,0,false,false);

                        }

                        #} ... brutal for mvp #DB1LEGACY (TOMOVE)
                        if ($withTransactions){
                            
                            #} only gets first 100?
                            $res['transactions'] = zeroBS_getTransactionsForCustomer($potentialRes->ID,false,100);

                        }

                    }

                    #} With co's?
                    if ($withCompanies){

                        // add all tags lines
                        $res['companies'] = $this->getObjsLinkedToObj(array(
                                'objtypefrom'   =>  ZBS_TYPE_CONTACT, // contact
                                'objtypeto'     =>  ZBS_TYPE_COMPANY, // company
                                'objfromid'     =>  $potentialRes->ID));
                    
                    }


                    // ===================================================
                    // ========== / #DB1LEGACY (TOMOVE)
                    // ===================================================


                    return $res;

            }

        } // / if ID

        return false;

    }

    // TODO $argsOverride=false
    /**
     * returns contact detail lines
     *
     * @param array $args Associative array of arguments
     *              withQuotes, withInvoices, withTransactions, withLogs, searchPhrase, sortByField, sortOrder, page, perPage
     *
     * @return array of contact lines
     */
    public function getContacts($args=array()){

        #} ============ LOAD ARGS =============
        $defaultArgs = array(

            // Search/Filtering (leave as false to ignore)
            'searchPhrase'  => '', // searches which fields?
            'inCompany'         => false, // will be an ID if used
            'inArr'             => false,
            'quickFilters'      => false,
            'isTagged'          => false, // 1x INT OR array(1,2,3)
            'isNotTagged'       => false, // 1x INT OR array(1,2,3)
            'ownedBy'           => false,
            'externalSource'    => false, // e.g. paypal
            'olderThan'         => false, // uts
            'newerThan'         => false, // uts
            'hasStatus'         => false, // Lead (this takes over from the quick filter post 19/6/18)
            'otherStatus'       => false, // status other than 'Lead'

            // last contacted
            'contactedBefore'   => false, // uts
            'contactedAfter'    => false, // uts

            // email
            'hasEmail'          => false, // 'x@y.com' either in main field or as AKA

            // addr
            'inCounty'          => false, // Hertfordshire
            'inPostCode'        => false, // AL1 1AA
            'inCountry'         => false, // United Kingdom
            'notInCounty'       => false, // Hertfordshire
            'notInPostCode'     => false, // AL1 1AA
            'notInCountry'      => false, // United Kingdom

            // returns
            'count'             => false,
            'withCustomFields'  => true,
            'withQuotes'        => false,
            'withInvoices'      => false,
            'withTransactions'  => false,
            'withLogs'          => false,
            'withLastLog'       => false,
            'withTags'          => false,
            'withOwner'         => false,
            'withDND'           => false, // if true, returns getContactDoNotMail as well :)
            'onlyIDs'           => false, // if true, returns array of ID's only 

            'sortByField'   => 'ID',
            'sortOrder'     => 'ASC',
            'page'          => 0, // this is what page it is (gets * by for limit)
            'perPage'       => 100,

            // permissions
            'ignoreowner'   => false, // this'll let you not-check the owner of obj

            // 'argsOverride' => ?? Still req?

            // specifics
            // NOTE: this is ONLY for use where a sql query is 1 time use, otherwise add as argument
            // ... for later use, (above)
            // PLEASE do not use the or switch without discussing case with WH
            'additionalWhereArr' => false, 
            'whereCase'          => 'AND' // DEFAULT = AND



        ); foreach ($defaultArgs as $argK => $argV){ $$argK = $argV; if (is_array($args) && isset($args[$argK])) {  if (is_array($args[$argK])){ $newData = $$argK; if (!is_array($newData)) $newData = array(); foreach ($args[$argK] as $subK => $subV){ $newData[$subK] = $subV; }$$argK = $newData;} else { $$argK = $args[$argK]; } } }
        #} =========== / LOAD ARGS =============

        global $ZBSCRM_t,$wpdb,$zbs;  
        $wheres = array('direct'=>array()); $whereStr = ''; $additionalWhere = ''; $params = array(); $res = array(); $joinQ = ''; $extraSelect = '';

        #} ============= PRE-QUERY ============

            #} Capitalise this
            $sortOrder = strtoupper($sortOrder);

            #} If just count, turn off any extra gumpf
            if ($count) {
                $withCustomFields = false;
                $withQuotes = false;
                $withInvoices = false;
                $withTransactions = false;
                $withLogs = false;
                $withLastLog = false;
                $withTags = false;
                $withOwner = false;
                $withDND = false;
            }

            #} Custom Fields
            if ($withCustomFields && !$onlyIDs){
                
                #} Retrieve any cf
                $custFields = $this->getActiveCustomFields(array('objtypeid'=>ZBS_TYPE_CONTACT));

                #} Cycle through + build into query
                if (is_array($custFields)) foreach ($custFields as $cK => $cF){

                    // custom field (e.g. 'third name') it'll be passed here as 'third-name'
                    // ... problem is mysql does not like that :) so we have to chage here:
                    // in this case we prepend cf's with cf_ and we switch - for _
                    $cKey = 'cf_'.str_replace('-','_',$cK);

                    // we also check the $sortByField in case that's the same cf
                    if ($cK == $sortByField) $sortByField = $cKey;

                    // add as subquery
                    $extraSelect .= ',(SELECT zbscf_objval FROM '.$ZBSCRM_t['customfields']." WHERE zbscf_objid = contact.ID AND zbscf_objkey = %s AND zbscf_objtype = %d) ".$cKey;
                    
                    // add params
                    $params[] = $cK; $params[] = ZBS_TYPE_CONTACT;

                }



                // Add any addr custom fields for addr1+addr2
                $addrCustomFields = zeroBSCRM_getAddressCustomFields();
                if (is_array($addrCustomFields) && count($addrCustomFields) > 0){

                    foreach ($addrCustomFields as $cK => $cF){

                        // hacky temp solution.
                        $cKN = (int)$cK+1;
                        $cKey = 'addr_cf'.$cKN;
                        $cKey2 = 'secaddr_cf'.$cKN;

                        // addr1
                            // add as subquery
                            $extraSelect .= ',(SELECT zbscf_objval FROM '.$ZBSCRM_t['customfields']." WHERE zbscf_objid = contact.ID AND zbscf_objkey = %s AND zbscf_objtype = %d) '".$cKey."'";                        
                            // add params
                            $params[] = $cKey; $params[] = ZBS_TYPE_CONTACT;
                            // add as subquery
                            $extraSelect .= ',(SELECT zbscf_objval FROM '.$ZBSCRM_t['customfields']." WHERE zbscf_objid = contact.ID AND zbscf_objkey = %s AND zbscf_objtype = %d) '".$cKey2."'";                        
                            // add params
                            $params[] = $cKey2; $params[] = ZBS_TYPE_CONTACT;

                    }


                }

            }

            if ($withDND){

                // add as subquery
                $extraSelect .= ',(SELECT zbsm_val FROM '.$ZBSCRM_t['meta']." WHERE zbsm_objid = contact.ID AND zbsm_key = %s AND zbsm_objtype = %d) dnd";
                
                // add params
                $params[] = 'do-not-email'; $params[] = ZBS_TYPE_CONTACT;

            }


            // ... though if id somehow get's through... (shouldn't)
            if ($sortByField == 'zbsc_id') $sortByField = 'ID';
            if ($sortByField == 'zbsc_owner') $sortByField = 'zbs_owner';
            if ($sortByField == 'zbsc_zbs_owner') $sortByField = 'zbs_owner';
        
            // this translated fullname to actual column names 
            if ($sortByField == 'zbsc_fullname' || $sortByField == 'fullname') {
                if (!in_array($sortOrder,array('ASC','DESC'))) $sortOrder = 'ASC';
                $sortByField = array('zbsc_lname'=>$sortOrder,'zbsc_fname'=>$sortOrder);
            }
 
            $selector = 'contact.*';
            if ($onlyIDs) $selector = 'contact.ID';
            

        #} ============ / PRE-QUERY ===========

        #} Build query
        $query = "SELECT ".$selector.$extraSelect." FROM ".$ZBSCRM_t['contacts'].' as contact'.$joinQ;

        #} Count override
        if ($count) $query = "SELECT COUNT(contact.ID) FROM ".$ZBSCRM_t['contacts'].' as contact'.$joinQ;

        #} ============= WHERE ================

            #} Add Search phrase
            if (!empty($searchPhrase)){

                // inefficient searching all fields. Maybe get settings from user "which fields to search"
                // ... and auto compile for each contact ahead of time
                $searchWheres = array();
                $searchWheres['search_fullname'] = array('CONCAT(zbsc_prefix, " ", zbsc_fname, " ", zbsc_lname)','LIKE','%s','%'.$searchPhrase.'%');
                $searchWheres['search_fname'] = array('zbsc_fname','LIKE','%s','%'.$searchPhrase.'%');
                $searchWheres['search_lname'] = array('zbsc_lname','LIKE','%s','%'.$searchPhrase.'%');
                $searchWheres['search_email'] = array('zbsc_email','LIKE','%s','%'.$searchPhrase.'%');
                $searchWheres['search_addr1'] = array('zbsc_addr1','LIKE','%s','%'.$searchPhrase.'%');
                $searchWheres['search_city'] = array('zbsc_city','LIKE','%s','%'.$searchPhrase.'%');
                $searchWheres['search_postcode'] = array('zbsc_postcode','LIKE','%s','%'.$searchPhrase.'%');

                    // added 16/05/18 for Melanie, remove if not useful/blocking
                    $searchWheres['search_hometel'] = array('zbsc_hometel','LIKE','%s','%'.$searchPhrase.'%');
                    $searchWheres['search_worktel'] = array('zbsc_worktel','LIKE','%s','%'.$searchPhrase.'%');
                    $searchWheres['search_mobtel'] = array('zbsc_mobtel','LIKE','%s','%'.$searchPhrase.'%');

                // We also add this, which finds AKA emails if using email
                $searchWheres['search_alias'] = array('ID','IN',"(SELECT aka_id FROM ".$ZBSCRM_t['aka']." WHERE aka_type = ".zeroBS_getAKAType('customer')." AND aka_alias = %s)",$searchPhrase);

                // 2.99.9.11 - Added ability to search custom fields (optionally)
                $customFieldSearch = zeroBSCRM_getSetting('customfieldsearch');
                if ($customFieldSearch == 1){
                
                    // simplistic add
                    // NOTE: This IGNORES ownership of custom field lines.
                    $searchWheres['search_customfields'] = array('ID','IN',"(SELECT zbscf_objid FROM ".$ZBSCRM_t['customfields']." WHERE zbscf_objval LIKE %s AND zbscf_objtype = ".ZBS_TYPE_CONTACT.")",'%'.$searchPhrase.'%');

                }

                // This generates a query like 'zbsc_fname LIKE %s OR zbsc_lname LIKE %s', 
                // which we then need to include as direct subquery (below) in main query :)
                $searchQueryArr = $this->buildWheres($searchWheres,'',array(),'OR',false);
                
                if (is_array($searchQueryArr) && isset($searchQueryArr['where']) && !empty($searchQueryArr['where'])){

                    // add it
                    $wheres['direct'][] = array('('.$searchQueryArr['where'].')',$searchQueryArr['params']);

                }

            }

            #} In company? #DB1LEGACY (TOMOVE -> where)
            if (!empty($inCompany) && $inCompany > 0){
                
                // would never hard-type this in (would make generic as in buildWPMetaQueryWhere)
                // but this is only here until MIGRATED to db2 globally
                //$wheres['incompany'] = array('ID','IN','(SELECT DISTINCT post_id FROM '.$wpdb->prefix."postmeta WHERE meta_key = 'zbs_company' AND meta_value = %d)",$inCompany);
                // Use obj links now 
                $wheres['incompany'] = array('ID','IN','(SELECT DISTINCT zbsol_objid_from FROM '.$ZBSCRM_t['objlinks']." WHERE zbsol_objtype_from = ".ZBS_TYPE_CONTACT." AND zbsol_objtype_to = ".ZBS_TYPE_COMPANY." AND zbsol_objid_to = %d)",$inCompany);

            }

            #} In array (if inCompany passed, this'll currently overwrite that?! (todo2.5))
            if (is_array($inArr) && count($inArr) > 0){

                // clean for ints
                $inArrChecked = array(); foreach ($inArr as $x){ $inArrChecked[] = (int)$x; }

                // add where
                $wheres['inarray'] = array('ID','IN','('.implode(',',$inArrChecked).')');

            }

            #} Owned by
            if (!empty($ownedBy) && $ownedBy > 0){
                
                // would never hard-type this in (would make generic as in buildWPMetaQueryWhere)
                // but this is only here until MIGRATED to db2 globally
                //$wheres['incompany'] = array('ID','IN','(SELECT DISTINCT post_id FROM '.$wpdb->prefix."postmeta WHERE meta_key = 'zbs_company' AND meta_value = %d)",$inCompany);
                // Use obj links now 
                $wheres['ownedBy'] = array('zbs_owner','=','%s',$ownedBy);

            }

            #} External sources
            if (!empty($externalSource) && array_key_exists($externalSource,$zbs->external_sources)){

                // NO owernship built into this, check when roll out multi-layered ownsership
                $wheres['externalsource'] = array('ID','IN','(SELECT DISTINCT zbss_objid FROM '.$ZBSCRM_t['externalsources']." WHERE zbss_objtype = ".ZBS_TYPE_CONTACT." AND zbss_source = %s)",$externalSource);

            }

            // quick addition for mike
            #} olderThan
            if (!empty($olderThan) && $olderThan > 0 && $olderThan !== false) $wheres['olderThan'] = array('zbsc_created','<=','%d',$olderThan);
            #} newerThan
            if (!empty($newerThan) && $newerThan > 0 && $newerThan !== false) $wheres['newerThan'] = array('zbsc_created','>=','%d',$newerThan);

            // status
            if (!empty($hasStatus) && $hasStatus !== false) $wheres['hasStatus'] = array('zbsc_status','=','%s',$hasStatus);
            if (!empty($otherStatus) && $otherStatus !== false) $wheres['otherStatus'] = array('zbsc_status','<>','%s',$otherStatus);

            #} contactedBefore
            if (!empty($contactedBefore) && $contactedBefore > 0 && $contactedBefore !== false) $wheres['contactedBefore'] = array('zbsc_lastcontacted','<=','%d',$contactedBefore);
            #} contactedAfter
            if (!empty($contactedAfter) && $contactedAfter > 0 && $contactedAfter !== false) $wheres['contactedAfter'] = array('zbsc_lastcontacted','>=','%d',$contactedAfter);

            #} hasEmail
            if (!empty($hasEmail) && !empty($hasEmail) && $hasEmail !== false) {
                $wheres['hasEmail'] = array('zbsc_email','=','%s',$hasEmail);
                $wheres['hasEmailAlias'] = array('ID','IN',"(SELECT aka_id FROM ".$ZBSCRM_t['aka']." WHERE aka_type = ".zeroBS_getAKAType('customer')." AND aka_alias = %s)",$hasEmail);
            }

            #} inCounty
            if (!empty($inCounty) && !empty($inCounty) && $inCounty !== false) {
                $wheres['inCounty'] = array('zbsc_county','=','%s',$inCounty);
                $wheres['inCountyAddr2'] = array('zbsc_secaddrcounty','=','%s',$inCounty);
            }
            #} inPostCode
            if (!empty($inPostCode) && !empty($inPostCode) && $inPostCode !== false) {
                $wheres['inPostCode'] = array('zbsc_postcode','=','%s',$inPostCode);
                $wheres['inPostCodeAddr2'] = array('zbsc_secaddrpostcode','=','%s',$inPostCode);
            }
            #} inCountry
            if (!empty($inCountry) && !empty($inCountry) && $inCountry !== false) {
                $wheres['inCountry'] = array('zbsc_country','=','%s',$inCountry);
                $wheres['inCountryAddr2'] = array('zbsc_secaddrcountry','=','%s',$inCountry);
            }
            #} notInCounty
            if (!empty($notInCounty) && !empty($notInCounty) && $notInCounty !== false) {
                $wheres['notInCounty'] = array('zbsc_county','<>','%s',$notInCounty);
                $wheres['notInCountyAddr2'] = array('zbsc_secaddrcounty','<>','%s',$notInCounty);
            }
            #} notInPostCode
            if (!empty($notInPostCode) && !empty($notInPostCode) && $notInPostCode !== false) {
                $wheres['notInPostCode'] = array('zbsc_postcode','<>','%s',$notInPostCode);
                $wheres['notInPostCodeAddr2'] = array('zbsc_secaddrpostcode','<>','%s',$notInPostCode);
            }
            #} notInCountry
            if (!empty($notInCountry) && !empty($notInCountry) && $notInCountry !== false) {
                $wheres['notInCountry'] = array('zbsc_country','<>','%s',$notInCountry);
                $wheres['notInCountryAddr2'] = array('zbsc_secaddrcountry','<>','%s',$notInCountry);
            }

            #} Any additionalWhereArr?
            if (isset($additionalWhereArr) && is_array($additionalWhereArr) && count($additionalWhereArr) > 0){

                // add em onto wheres (note these will OVERRIDE if using a key used above)
                // Needs to be multi-dimensional $wheres = array_merge($wheres,$additionalWhereArr);
                $wheres = array_merge_recursive($wheres,$additionalWhereArr);

            }


            #} Quick filters - adapted from DAL1 (probs can be slicker)
            if (is_array($quickFilters) && count($quickFilters) > 0){

                // cycle through
                foreach ($quickFilters as $qFilter){

                        // always lower for now :)
                        $qFilter = strtolower($qFilter);

                        // where status = x
                        // USE hasStatus above now...
                        if (substr($qFilter,0,7) == 'status_'){

                            $qFilterStatus = substr($qFilter,7);
                            $qFilterStatus = str_replace('_',' ',$qFilterStatus);

                            // check status
                            $wheres['quickfilterstatus'] = array('zbsc_status','LIKE','LOWER(%s)',ucwords($qFilterStatus));

                        } elseif (substr($qFilter,0,14) == 'notcontactedin'){

                                // check
                                $notcontactedinDays = (int)substr($qFilter,14);
                                $notcontactedinDaysSeconds = $notcontactedinDays*86400;
                                $wheres['notcontactedinx'] = array('zbsc_lastcontacted','<','%d',time()-$notcontactedinDaysSeconds);

                        } elseif (substr($qFilter,0,9) == 'olderthan'){

                                // check
                                $olderThanDays = (int)substr($qFilter,9);
                                $olderThanDaysSeconds = $olderThanDays*86400;
                                $wheres['olderthanx'] = array('zbsc_created','<','%d',time()-$olderThanDaysSeconds);

                        } elseif (substr($qFilter,0,8) == 'segment_'){

                            // a SEGMENT
                            $qFilterSegmentSlug = substr($qFilter,8);

                                #} Retrieve segment + conditions
                                $segment = $this->getSegmentBySlug($qFilterSegmentSlug,true,false);
                                $conditions = array(); if (isset($segment['conditions'])) $conditions = $segment['conditions'];
                                $matchType = 'all'; if (isset($segment['matchtype'])) $matchType = $segment['matchtype'];

                                // here we zeroBSCRM_textExpose because all will have had textProcess inbound.
                                // probs needs another layer abstraction above this?
                                $conditions = zeroBSCRM_segments_unencodeConditions($conditions);

                                // retrieve getContacts arguments from a list of segment conditions
                                // as at launch of segments (26/6/18) - these are all $additionalWhere args
                                // ... if it stays that way, this is nice and simple, so going to proceed with that.
                                // be aware if $this->segmentConditionArgs() changes, will affect this.
                                $contactGetArgs = $this->segmentConditionsToArgs($conditions,$matchType);

                                    // as at above, contactGetArgs should have this:
                                    if (isset($contactGetArgs['additionalWhereArr']) && is_array($contactGetArgs['additionalWhereArr'])){

                                        // This was required to work with OR and AND situs, along with the usual getContacts vars as well
                                        // -----------------------
                                        // match type ALL is default, this switches to ANY
                                        $segmentOperator = 'AND'; if ($matchType == 'one') $segmentOperator = 'OR';

                                        // This generates a query like 'zbsc_fname LIKE %s OR/AND zbsc_lname LIKE %s', 
                                        // which we then need to include as direct subquery (below) in main query :)
                                        $segmentQueryArr = $this->buildWheres($contactGetArgs['additionalWhereArr'],'',array(),$segmentOperator,false);
                                        
                                        if (is_array($segmentQueryArr) && isset($segmentQueryArr['where']) && !empty($segmentQueryArr['where'])){

                                            // add it
                                            $wheres['direct'][] = array('('.$segmentQueryArr['where'].')',$segmentQueryArr['params']);

                                        }
                                        // -----------------------


                                        //  following didn't work for OR situations: (worked for most situations though, is a shame)
                                        // -----------------------
                                        // so we MERGE that into our wheres... :o
                                        // this'll override any settings above. 
                                        // Needs to be multi-dimensional 
                                        //$wheres = array_merge_recursive($wheres,$contactGetArgs['additionalWhereArr']);
                                        // -----------------------

                                    }


                        } else {

                                // normal/hardtyped

                                switch ($qFilter){


                                    case 'lead':

                                        // hack "leads only" - adapted from DAL1 (probs can be slicker)
                                        $wheres['quickfilterlead'] = array('zbsc_status','LIKE','%s','Lead');

                                        break;


                                    case 'customer':

                                        // hack - adapted from DAL1 (probs can be slicker)
                                        $wheres['quickfiltercustomer'] = array('zbsc_status','LIKE','%s','Customer');

                                        break;

                                    /* Disabled in v2.2 - as paging causes these to not work performantly 
                                    (e.g. get 2k customers, and their invoices, then page AFTER
                                    ... when this system is made to page at point of Query)

                                    case 'over100':

                                        // pass on
                                        $postQueryFilters[] = 'over100';

                                        // also requires invs/trans
                                        $withTransactions = true; $withInvoices = true;

                                        break;

                                    case 'over200':

                                        $postQueryFilters[] = 'over200';

                                        // also requires invs/trans
                                        $withTransactions = true; $withInvoices = true;

                                        break;

                                    case 'over300':

                                        $postQueryFilters[] = 'over300';

                                        // also requires invs/trans
                                        $withTransactions = true; $withInvoices = true;

                                        break;

                                    case 'over400':

                                        $postQueryFilters[] = 'over400';

                                        // also requires invs/trans
                                        $withTransactions = true; $withInvoices = true;

                                        break;

                                    case 'over500':

                                        $postQueryFilters[] = 'over500';

                                        // also requires invs/trans
                                        $withTransactions = true; $withInvoices = true;

                                        break;

                                        */


                                }  // / switch

                            } // / hardtyped

                        }
                } // / quickfilters

            #} Is Tagged (expects 1 tag ID OR array)

                // catch 1 item arr
                if (is_array($isTagged) && count($isTagged) == 1) $isTagged = $isTagged[0];

            if (!is_array($isTagged) && !empty($isTagged) && $isTagged > 0){

                // add where tagged                 
                // 1 int: 
                $wheres['direct'][] = array('((SELECT COUNT(ID) FROM '.$ZBSCRM_t['taglinks'].' WHERE zbstl_objtype = %d AND zbstl_objid = contact.ID AND zbstl_tagid = %d) > 0)',array(ZBS_TYPE_CONTACT,$isTagged));

            } else if (is_array($isTagged) && count($isTagged) > 0){

                // foreach in array :) 
                $tagStr = '';
                foreach ($isTagged as $iTag){
                    $i = (int)$iTag;
                    if ($i > 0){

                        if ($tagStr !== '') $tagStr .',';
                        $tagStr .= $i;
                    }
                }
                if (!empty($tagStr)){
                    
                    $wheres['direct'][] = array('((SELECT COUNT(ID) FROM '.$ZBSCRM_t['taglinks'].' WHERE zbstl_objtype = %d AND zbstl_objid = contact.ID AND zbstl_tagid IN (%s)) > 0)',array(ZBS_TYPE_CONTACT,$tagStr));

                }

            }
            #} Is NOT Tagged (expects 1 tag ID OR array)

                // catch 1 item arr
                if (is_array($isNotTagged) && count($isNotTagged) == 1) $isNotTagged = $isNotTagged[0];
                
            if (!is_array($isNotTagged) && !empty($isNotTagged) && $isNotTagged > 0){

                // add where tagged                 
                // 1 int: 
                $wheres['direct'][] = array('((SELECT COUNT(ID) FROM '.$ZBSCRM_t['taglinks'].' WHERE zbstl_objtype = %d AND zbstl_objid = contact.ID AND zbstl_tagid = %d) = 0)',array(ZBS_TYPE_CONTACT,$isNotTagged));

            } else if (is_array($isNotTagged) && count($isNotTagged) > 0){

                // foreach in array :) 
                $tagStr = '';
                foreach ($isNotTagged as $iTag){
                    $i = (int)$iTag;
                    if ($i > 0){

                        if ($tagStr !== '') $tagStr .',';
                        $tagStr .= $i;
                    }
                }
                if (!empty($tagStr)){
                    
                    $wheres['direct'][] = array('((SELECT COUNT(ID) FROM '.$ZBSCRM_t['taglinks'].' WHERE zbstl_objtype = %d AND zbstl_objid = contact.ID AND zbstl_tagid IN (%s)) = 0)',array(ZBS_TYPE_CONTACT,$tagStr));

                }

            }

        

        #} ============ / WHERE ===============

        #} CHECK this + reset to default if faulty
        if (!in_array($whereCase,array('AND','OR'))) $whereCase = 'AND';

        #} Build out any WHERE clauses
        $wheresArr = $this->buildWheres($wheres,$whereStr,$params,$whereCase);
        $whereStr = $wheresArr['where']; $params = $params + $wheresArr['params'];
        #} / Build WHERE

        #} Ownership v1.0 - the following adds SITE + TEAM checks, and (optionally), owner
        $params = array_merge($params,$this->ownershipQueryVars($ignoreowner)); // merges in any req.
        $ownQ = $this->ownershipSQL($ignoreowner,'contact'); if (!empty($ownQ)) $additionalWhere = $this->spaceAnd($additionalWhere).$ownQ; // adds str to query
        #} / Ownership

        #} Append to sql (this also automatically deals with sortby and paging)
        $query .= $this->buildWhereStr($whereStr,$additionalWhere) . $this->buildSort($sortByField,$sortOrder) . $this->buildPaging($page,$perPage);

        try {

            #} Prep & run query
            $queryObj = $this->prepare($query,$params);

            #} Catch count + return if requested
            if ($count) return $wpdb->get_var($queryObj);

            #} else continue..
            $potentialRes = $wpdb->get_results($queryObj, OBJECT);

        } catch (Exception $e){

            #} General SQL Err
            $this->catchSQLError($e);

        }

        #} Interpret results (Result Set - multi-row)
        if (isset($potentialRes) && is_array($potentialRes) && count($potentialRes) > 0) {

            #} Has results, tidy + return 
            foreach ($potentialRes as $resDataLine) {

                    #} Only ID? just stack em up
                    if ($onlyIDs) {

                        $res[] = $resDataLine->ID;

                    } else {

                        // 99% of queries:

                        // tidy
                        $resArr = $this->tidy_contact($resDataLine,$withCustomFields);

                        if ($withTags){

                            // add all tags lines
                            $resArr['tags'] = $this->getTagsForObjID(array('objtypeid'=>ZBS_TYPE_CONTACT,'objid'=>$resDataLine->ID));

                        }

                        if ($withDND){

                            // retrieve :) (paranoia mode)
                            $dnd = -1; $potentialDND = $this->stripSlashes($this->decodeIfJSON($resDataLine->dnd));
                            if ($potentialDND == "1") $dnd = 1;

                            $resArr['dnd'] = $dnd;
                        }


                        // ===================================================
                        // ========== #} #DB1LEGACY (TOMOVE)
                        // == Following is all using OLD DB stuff, here until we migrate inv etc.
                        // ===================================================

                        #} With most recent log? #DB1LEGACY (TOMOVE)
                        if ($withLastLog){

                            // doesn't return singular, for now using arr
                            $potentialLogs = $this->getLogsForObj(array(

                                                    'objtype' => ZBS_TYPE_CONTACT,
                                                    'objid' => $resDataLine->ID,
                                                    
                                                    'incMeta'   => true,

                                                    'sortByField'   => 'zbsl_created',
                                                    'sortOrder'     => 'DESC',
                                                    'page'          => 0,
                                                    'perPage'       => 1

                                                ));

                            if (is_array($potentialLogs) && count($potentialLogs) > 0) $resArr['lastlog'] = $potentialLogs[0];

                        }

                        #} With Assigned?
                        if ($withOwner){

                            $retObj['owner'] = zeroBS_getOwner($resDataLine->ID);

                        }

                        #} use sql instead #DB1LEGACY (TOMOVE)
                        if ($withInvoices && $withQuotes && $withTransactions){

                            $custDeets = zeroBS_getCustomerExtrasViaSQL($resDataLine->ID);
                            $resArr['quotes'] = $custDeets['quotes'];
                            $resArr['invoices'] = $custDeets['invoices'];
                            $resArr['transactions'] = $custDeets['transactions'];


                        } else {

                            #} These might be broken again into counts/dets? more efficient way? #DB1LEGACY (TOMOVE)
                            if ($withInvoices){
                                
                                #} only gets first 100?
                                #} CURRENTLY inc meta..? (isn't huge... but isn't efficient)
                                $resArr['invoices']         = zeroBS_getInvoicesForCustomer($resDataLine->ID,true,100);

                            }

                            #} These might be broken again into counts/dets? more efficient way? #DB1LEGACY (TOMOVE)
                            if ($withQuotes){
                                
                                #} only gets first 100?
                                $resArr['quotes']       = zeroBS_getQuotesForCustomer($resDataLine->ID,true,100,0,false,false);

                            }

                            #} ... brutal for mvp #DB1LEGACY (TOMOVE)
                            if ($withTransactions){
                                
                                
                                #} only gets first 100?
                                $resArr['transactions'] = zeroBS_getTransactionsForCustomer($resDataLine->ID,false,100);

                            }

                        }

                        // ===================================================
                        // ========== / #DB1LEGACY (TOMOVE)
                        // ===================================================


                        $res[] = $resArr;

                    } // normal query (not onlyID)

            }
        }

        return $res;
    } 



     /**
     * adds or updates a contact object
     *
     * @param array $args Associative array of arguments
     *              id (if update), owner, data (array of field data)
     *
     * @return int line ID
     */
    public function addUpdateContact($args=array()){

        global $ZBSCRM_t,$wpdb,$zbs;
            
        #} Retrieve any cf
        $customFields = $this->getActiveCustomFields(array('objtypeid'=>ZBS_TYPE_CONTACT));
        $addrCustomFields = zeroBSCRM_getAddressCustomFields();

        // this fires the contact.vitals.updated hook if names, email, address, phonenumbers updated
        $contactVitalsUpdated = false;

        #} ============ LOAD ARGS =============
        $defaultArgs = array(

            'id'            => -1,
            'owner'         => -1,

            // fields (directly)
            'data'          => array(

                'email' => '', // Unique Field ! 

                'status' => '',
                'prefix' => '',
                'fname' => '',
                'lname' => '',
                'addr1' => '',
                'addr2' => '',
                'city' => '',
                'county' => '',
                'country' => '',
                'postcode' => '',
                'secaddr1' => '',
                'secaddr2' => '',
                'seccity' => '',
                'seccounty' => '',
                'seccountry' => '',
                'secpostcode' => '',
                'hometel' => '',
                'worktel' => '',
                'mobtel' => '',
                'wpid'  => -1,
                'avatar' => '',

                // social basics :)
                'tw' => '',
                'fb' => '',
                'li' => '',

                // Note Custom fields may be passed here, but will not have defaults so check isset()

                'tags' => -1, // if this is an array of ID's, they'll REPLACE existing tags against contact

                'externalSources' => -1, // if this is an array(array('source'=>src,'uid'=>uid),multiple()) it'll add :)

                'companies' => -1, // array of co id's :)


                // wh added for later use.
                'lastcontacted' => -1,
                // allow this to be set for MS sync etc.
                'created' => -1,

            ),

            'limitedFields' => -1, // if this is set it OVERRIDES data (allowing you to set specific fields + leave rest in tact)
            // ^^ will look like: array(array('key'=>x,'val'=>y,'type'=>'%s'))

            // this function as DAL1 func did. 
            'extraMeta'     => -1,
            'automatorPassthrough' => -1,
            'fallBackLog' => -1,


            'silentInsert' => false, // this was for init Migration - it KILLS all IA for newContact (because is migrating, not creating new :) this was -1 before


            'do_not_update_blanks' => false // this allows you to not update fields if blank (same as fieldoverride for extsource -> in)

        ); foreach ($defaultArgs as $argK => $argV){ $$argK = $argV; if (is_array($args) && isset($args[$argK])) {  if (is_array($args[$argK])){ $newData = $$argK; if (!is_array($newData)) $newData = array(); foreach ($args[$argK] as $subK => $subV){ $newData[$subK] = $subV; }$$argK = $newData;} else { $$argK = $args[$argK]; } } }
        
            // Needs this to grab custom fields (if passed) too :)
            if (is_array($customFields)) foreach ($customFields as $cK => $cF){

                // only for data, limited fields below
                if (is_array($data)) {

                    if (isset($args['data'][$cK])) $data[$cK] = $args['data'][$cK];

                }

            }
            /* NOT REQ: // Needs this to grab custom addr fields (if passed) too :)
            if (is_array($addrCustomFields)) foreach ($addrCustomFields as $cK => $cF){

                // only for data, limited fields below
                if (is_array($data)) {

                    //if (isset($args['data'][$cK])) $data[$cK] = $args['data'][$cK];

                }

            } */

            // this takes limited fields + checks through for custom fields present
            // (either as key zbsc_source or source, for example)
            // then switches them into the $data array, for separate update
            // where this'll fall over is if NO normal contact data is sent to update, just custom fields
            if (is_array($limitedFields) && is_array($customFields)){

                    //$customFieldKeys = array_keys($customFields);
                    $newLimitedFields = array();

                    // cycle through
                    foreach ($limitedFields as $field){

                        // some weird case where getting empties, so added check
                        if (isset($field['key']) && !empty($field['key'])){ 

                            $dePrefixed = ''; if (substr($field['key'],0,strlen('zbsc_')) === 'zbsc_') $dePrefixed = substr($field['key'], strlen('zbsc_'));

                            if (isset($customFields[$field['key']])){

                                // is custom, move to data
                                $data[$field['key']] = $field['val'];

                            } else if (!empty($dePrefixed) && isset($customFields[$dePrefixed])){

                                // is custom, move to data
                                $data[$dePrefixed] = $field['val'];

                            } else {

                                // add it to limitedFields (it's not dealt with post-update)
                                $newLimitedFields[] = $field;
                            }

                        }

                    }

                    // move this back in
                    $limitedFields = $newLimitedFields;
                    unset($newLimitedFields);

                }

        #} =========== / LOAD ARGS ============

        #} ========== CHECK FIELDS ============

            $id = (int)$id;
            
            // here we check that the potential owner CAN even own
            if (!user_can($owner,'admin_zerobs_usr')) $owner = -1;

            // if owner = -1, add current
            if (!isset($owner) || $owner === -1) { $owner = zeroBSCRM_user(); }

            if (is_array($limitedFields)){ 

                // LIMITED UPDATE (only a few fields.)
                if (!is_array($limitedFields) || count ($limitedFields) <= 0) return false;
                // REQ. ID too (can only update)
                if (empty($id) || $id <= 0) return false;

            } else {

                // NORMAL, FULL UPDATE

                // check email + load that user if present
                if (!isset($data['email']) || empty($data['email'])){

                    // no email
                    // Allow users without emails? WH removed this for db1->2 migration
                    // leaving this in breaks MIGRATIONS from DAL 1
                    // in that those contacts without emails will not be copied in
                    // return false;

                } else {

                    // email present, check if it matches ID? 
                    if (!empty($id) && $id > 0){

                        // if ID + email, check if existing contact with email, (e.g. in use)
                        // ... allow it if the ID of that email contact matches the ID given here
                        // (else e.g. add email x to ID y without checking)
                        $potentialUSERID = (int)$this->getContact(-1,array('email'=>$data['email'],'ignoreOwner'=>1,'onlyID'=>1));
                        if (!empty($potentialUSERID) && $potentialUSERID > 0 && $id > 0 && $potentialUSERID != $id){

                            // email doesn't match ID 
                            return false;
                        }

                        // also check if has rights?!? Could be just email passed here + therefor got around owner check? hmm.

                    } else {

                        // no ID, check if email present, and then update that user if so
                        $potentialUSERID = (int)$this->getContact(-1,array('email'=>$data['email'],'ignoreOwner'=>1,'onlyID'=>1));
                        if (isset($potentialUSERID) && !empty($potentialUSERID) && $potentialUSERID > 0) { $id = $potentialUSERID; }

                    }


                }


                // companies
                if (isset($data['companies']) && is_array($data['companies'])){


                    $coArr = array();
                    /* 
                    there was a bug happening here where same company could get dude at a few times... 
                    so for now only use the first company */
                    /*
                    foreach ($data['companies'] as $c){
                        $cI = (int)$c;
                        if ($cI > 0 && !in_array($cI, $coArr)) $coArr[] = $cI;
                    }*/

                    $cI = (int)$data['companies'][0];
                    if ($cI > 0 && !in_array($cI, $coArr)) $coArr[] = $cI;

                    // reset the main
                    if (count($coArr) > 0) 
                        $data['companies'] = $coArr; 
                    else
                        $data['companies'] = 'unset';
                    unset($coArr);

                }


            }

            #} If no status, and default is specified in settings, add that in :)
            if (is_null($data['status']) || !isset($data['status']) || empty($data['status'])){

                $zbsCustomerMeta['status'] = zeroBSCRM_getSetting('defaultstatus');

            }

        #} ========= / CHECK FIELDS ===========

        #} ========= OVERRIDE SETTING (Deny blank overrides) ===========

            // this only functions if externalsource is set (e.g. api/form, etc.)
            if (isset($data['externalSources']) && is_array($data['externalSources']) && count($data['externalSources']) > 0) {
                if (zeroBSCRM_getSetting('fieldoverride') == "1"){

                    $do_not_update_blanks = true;

                }

            }

            // either ext source + setting, or set by the func call
            if ($do_not_update_blanks){

                    // this setting says 'don't override filled-out data with blanks'
                    // so here we check through any passed blanks + convert to limitedFields
                    // only matters if $id is set (there is somt to update not add
                    if (isset($id) && !empty($id) && $id > 0){

                        // get data to copy over (for now, this is required to remove 'fullname' etc.)
                        $dbData = $this->db_ready_contact($data); 
                        //unset($dbData['id']); // this is unset because we use $id, and is update, so not req. legacy issue
                        //unset($dbData['created']); // this is unset because this uses an obj which has been 'updated' against original details, where created is output in the WRONG format :)

                        $origData = $data; //$data = array();               
                        $limitedData = array(); // array(array('key'=>'zbsc_x','val'=>y,'type'=>'%s'))

                        // cycle through + translate into limitedFields (removing any blanks, or arrays (e.g. externalSources))
                        // we also have to remake a 'faux' data (removing blanks for tags etc.) for the post-update updates
                        foreach ($dbData as $k => $v){

                            $intV = (int)$v;

                            // only add if valuenot empty
                            if (!is_array($v) && !empty($v) && $v != '' && $v !== 0 && $v !== -1 && $intV !== -1){

                                // add to update arr
                                $limitedData[] = array(
                                    'key' => 'zbsc_'.$k, // we have to add zbsc_ here because translating from data -> limited fields
                                    'val' => $v,
                                    'type' => $this->getTypeStr('zbsc_'.$k)
                                );                              

                                // add to remade $data for post-update updates
                                $data[$k] = $v;

                            }

                        }

                        // copy over
                        $limitedFields = $limitedData;

                    } // / if ID

            } // / if do_not_update_blanks
            //exit();

        #} ========= / OVERRIDE SETTING (Deny blank overrides) ===========


        #} ========= BUILD DATA ===========

            $update = false; $dataArr = array(); $typeArr = array();

            if (is_array($limitedFields)){

                // LIMITED FIELDS
                $update = true;

                // cycle through
                foreach ($limitedFields as $field){

                    // some weird case where getting empties, so added check
                    if (!empty($field['key'])){ 
                        $dataArr[$field['key']] = $field['val']; 
                        $typeArr[] = $field['type'];
                    }

                }

                // add update time
                if (!isset($dataArr['zbsc_lastupdated'])){ $dataArr['zbsc_lastupdated'] = time(); $typeArr[] = '%d'; }

            } else {

                // FULL UPDATE/INSERT

                    // UPDATE
                    $dataArr = array( 

                                // ownership
                                // no need to update these (as of yet) - can't move teams etc.
                                //'zbs_site' => zeroBSCRM_installSite(),
                                //'zbs_team' => zeroBSCRM_installTeam(),
                                //'zbs_owner' => $owner,

                                // fields
                                'zbsc_status' => $data['status'],
                                'zbsc_email' => $data['email'],
                                'zbsc_prefix' => $data['prefix'],
                                'zbsc_fname' => $data['fname'],
                                'zbsc_lname' => $data['lname'],
                                'zbsc_addr1' => $data['addr1'],
                                'zbsc_addr2' => $data['addr2'],
                                'zbsc_city' => $data['city'],
                                'zbsc_county' => $data['county'],
                                'zbsc_country' => $data['country'],
                                'zbsc_postcode' => $data['postcode'],
                                'zbsc_secaddr1' => $data['secaddr1'],
                                'zbsc_secaddr2' => $data['secaddr2'],
                                'zbsc_seccity' => $data['seccity'],
                                'zbsc_seccounty' => $data['seccounty'],
                                'zbsc_seccountry' => $data['seccountry'],
                                'zbsc_secpostcode' => $data['secpostcode'],
                                'zbsc_hometel' => $data['hometel'],
                                'zbsc_worktel' => $data['worktel'],
                                'zbsc_mobtel' => $data['mobtel'],
                                'zbsc_wpid' => $data['wpid'],
                                'zbsc_avatar' => $data['avatar'],


                                'zbsc_tw' => $data['tw'],
                                'zbsc_fb' => $data['fb'],
                                'zbsc_li' => $data['li'],

                                //'zbsc_created' => time(),
                                'zbsc_lastupdated' => time()
                            );

                    // if set
                    if ($data['lastcontacted'] !== -1) $dataArr['zbsc_lastcontacted'] = $data['lastcontacted'];

                    $typeArr = array( // field data types
                                //'%d',  // site
                                //'%d',  // team
                                //'%d',  // owner

                                '%s',  
                                '%s',  
                                '%s', 
                                '%s',  
                                '%s',  
                                '%s', 
                                '%s',  
                                '%s',  
                                '%s',  
                                '%s',  
                                '%s', 
                                '%s',  
                                '%s',  
                                '%s', 
                                '%s',  
                                '%s',  
                                '%s', 
                                '%s',  
                                '%s',  
                                '%s',  
                                '%d',  
                                '%s',

                                '%s', 
                                '%s', 
                                '%s',  
     
                                '%d'   // last updated
                            );
                    // if set
                    if ($data['lastcontacted'] !== -1) $typeArr[] = '%d';

                if (!empty($id) && $id > 0){

                    // is update
                    $update = true;

                } else {

                    // INSERT (get's few extra :D)
                    $update = false;
                    $dataArr['zbs_site'] = zeroBSCRM_site();    $typeArr[] = '%d';
                    $dataArr['zbs_team'] = zeroBSCRM_team();    $typeArr[] = '%d';
                    $dataArr['zbs_owner'] = $owner;             $typeArr[] = '%d';
                    if (isset($data['created']) && !empty($data['created']) && $data['created'] !== -1){
                        $dataArr['zbsc_created'] = $data['created'];$typeArr[] = '%d';
                    } else {
                        $dataArr['zbsc_created'] = time();          $typeArr[] = '%d';
                    }
                    $dataArr['zbsc_lastcontacted'] = -1; $typeArr[] = '%d';

                }

            }

        #} ========= / BUILD DATA ===========
        #} Check if ID present
        if ($update){

                #} get any segments (whom counts may be affected by changes)
                $contactsPreUpdateSegments = $this->getSegmentsContainingContact($id,true);

                #} Check if obj exists (here) - for now just brutal update (will error when doesn't exist)
                // now got below $originalStatus = $this->getContactStatus($id);

                #} From 2.98.9+ we check for contact.vitals.updated, so we need to get names, email, address, phonenumbers
                // ... so we get status here too, more efficient
                $vitalChanges = array();
                $originalContact = $this->getContact($id);
                $originalStatus = $originalContact['status'];

                // log any change of status
                // if we use dataArr rather than data, we also catch the limitedFields changes of status too
                // if (isset($data['status']) && !empty($data['status']) && !empty($originalStatus) && $data['status'] != $originalStatus){
                if (isset($dataArr['zbsc_status']) && !empty($dataArr['zbsc_status']) && !empty($originalStatus) && $dataArr['zbsc_status'] != $originalStatus){
                
                    // status change
                    $statusChange = array(
                        'from' => $originalStatus,
                        'to' => $dataArr['zbsc_status']
                    );
                }

                // log any change of vitals

                    // name, email, numbers
                    $checkVitalChangeFields = array(
                                'fname','lname',
                                'email',
                                'hometel','worktel','mobtel',
                                'addr1','addr2','city','county','postcode','country',
                                'secaddr1','secaddr2','seccity','seccounty','secpostcode','seccountry');
                    foreach ($checkVitalChangeFields as $vfk){
                            
                        $dataArrK = 'zbsc_'.$vfk;
                        $contactK = $vfk;
                        // these need translating, legacy.
                        if (substr($vfk,0,3) == 'sec') $contactK = str_replace('sec','secaddr_',$vfk);

                        if (isset($dataArr[$dataArrK]) && !empty($dataArr[$dataArrK]) && $dataArr[$dataArrK] != $originalContact[$contactK]){ // !empty($originalContact[$contactK]) 

                            $vitalChanges[$vfk] = array(
                                'from' => $originalContact[$contactK],
                                'to' => $dataArr[$dataArrK]
                            );

                        }

                    }
                

                #} Attempt update
                if ($wpdb->update( 
                        $ZBSCRM_t['contacts'], 
                        $dataArr, 
                        array( // where
                            'ID' => $id
                            ),
                        $typeArr,
                        array( // where data types
                            '%d'
                            )) !== false){

                            // if passing limitedFields instead of data, we ignore the following
                                // this doesn't work, because data is in args default as arr
                                //if (isset($data) && is_array($data)){
                                // so...
                            if (!isset($limitedFields) || !is_array($limitedFields) || $limitedFields == -1){

                                // tag work?
                                if (isset($data['tags']) && is_array($data['tags'])) $this->addUpdateContactTags(array('id'=>$id,'tagIDs'=>$data['tags'],'mode'=>'replace'));

                                // externalSource work?
                                $approvedExternalSource = ''; // for IA below
                                if (isset($data['externalSources']) && is_array($data['externalSources']) && count($data['externalSources']) > 0) {
                                    
                                    foreach ($data['externalSources'] as $es){

                                        $esID = -1; if (isset($es['id'])) $esID = $es['id'];

                                        $esID = $this->addUpdateExternalSource(array(

                                            'id'            => $esID,

                                            // fields (directly)
                                            'data'          => array(

                                                'objectID'     => $id,
                                                'objectType'   => ZBS_TYPE_CONTACT,
                                                'source'        => $es['source'],
                                                'uid'           => $es['uid']

                                        )));

                                        $approvedExternalSource = array(
                                            'id' => $esID,
                                            'objID'     => $id,
                                            'objType'   => ZBS_TYPE_CONTACT,
                                            'source'        => $es['source'],
                                            'uid'           => $es['uid']
                                            );

                                    } // / each ext source

                                }

                                // co's work?
                                if (isset($data['companies']) && is_array($data['companies']) && count($data['companies']) > 0){

                                    // remove previous links? make sure mode=replace
                                    $this->addUpdateObjLinks(array(
                                                                    'objtypefrom'       => ZBS_TYPE_CONTACT,
                                                                    'objtypeto'         => ZBS_TYPE_COMPANY,
                                                                    'objfromid'         => $id,
                                                                    'objtoids'          => $data['companies'],
                                                                    'mode'              => 'replace'));


                                } else if (isset($data['companies']) && $data['companies'] == 'unset') {

                                    // wipe previous links
                                    $deleted = $this->deleteObjLinks(array(
                                                'objtypefrom'   =>  ZBS_TYPE_CONTACT, // contact
                                                'objtypeto'     =>  ZBS_TYPE_COMPANY, // company
                                                'objfromid'     =>  $id)); // where contact id =
                                    //exit(); 

                                } 

                            } // / if $data (not $limitedFields)

                            // 2.98.1+ ... custom fields should update if present, regardless of limitedData rule
                            // ... UNLESS BLANK!
                            // Custom fields?

                                #} Cycle through + add/update if set
                                if (is_array($customFields)) foreach ($customFields as $cK => $cF){

                                    // any?
                                    if (isset($data[$cK])){

                                        // updating blanks?
                                        if ($do_not_update_blanks && empty($data[$cK])){

                                            // skip it

                                        } else {

                                            // it's either not in do_not_update_blank mode, or it has a val

                                            // add update
                                            $cfID = $this->addUpdateCustomField(array(
                                                'data'  => array(
                                                        'objtype'   => ZBS_TYPE_CONTACT,
                                                        'objid'     => $id,
                                                        'objkey'    => $cK,
                                                        'objval'    => $data[$cK]
                                                )));

                                        }

                                    }

                                }

                                // Also got to catch any 'addr' custom fields :) 
                                if (is_array($addrCustomFields) && count($addrCustomFields) > 0){

                                    // cycle through addr custom fields + save
                                    // see #ZBS-518, not easy until addr's get DAL2
                                    // WH deferring here

                                    // WH later added via the addUpdateContactField method - should work fine if we catch properly in get
                                    foreach ($addrCustomFields as $cK => $cF){

                                        // hacky temp solution.
                                        $cKN = (int)$cK+1;
                                        $cKey = 'addr_cf'.$cKN;
                                        $cKey2 = 'secaddr_cf'.$cKN;

                                        // any?
                                        if (isset($data[$cKey])){

                                            // updating blanks?
                                            if ($do_not_update_blanks && empty($data[$cKey])){

                                                // skip it

                                            } else {

                                                // it's either not in do_not_update_blank mode, or it has a val

                                                // add update
                                                $cfID = $this->addUpdateCustomField(array(
                                                    'data'  => array(
                                                            'objtype'   => ZBS_TYPE_CONTACT,
                                                            'objid'     => $id,
                                                            'objkey'    => $cKey,
                                                            'objval'    => $data[$cKey]
                                                    )));

                                                // log as vital change :) (addr's count)
                                                $vitalChanges[$cKey] = array(
                                                    'from' => $originalContact[$cKey],
                                                    'to' => $data['zbsc_'.$cKey]
                                                );

                                            }

                                        }

                                        // any?
                                        if (isset($data[$cKey2])){

                                            // updating blanks?
                                            if ($do_not_update_blanks && empty($data[$cKey2])){

                                                // skip it

                                            } else {

                                                // it's either not in do_not_update_blank mode, or it has a val

                                                // add update
                                                $cfID = $this->addUpdateCustomField(array(
                                                    'data'  => array(
                                                            'objtype'   => ZBS_TYPE_CONTACT,
                                                            'objid'     => $id,
                                                            'objkey'    => $cKey2,
                                                            'objval'    => $data[$cKey2]
                                                    )));

                                                // log as vital change :) (addr's count)
                                                $vitalChanges[$cKey] = array(
                                                    'from' => $originalContact[$cKey],
                                                    'to' => $data['zbsc_'.$cKey]
                                                );

                                            }
                                        }

                                    }

                                }

                                // DEBUG exit();

                            // / Custom Fields

                            #} Any extra meta keyval pairs?
                            // BRUTALLY updates (no checking)
                            $confirmedExtraMeta = false;
                            if (isset($extraMeta) && is_array($extraMeta)) {

                                $confirmedExtraMeta = array();

                                    foreach ($extraMeta as $k => $v){

                                    #} This won't fix stupid keys, just catch basic fails... 
                                    $cleanKey = strtolower(str_replace(' ','_',$k));

                                    #} Brutal update
                                    //update_post_meta($postID, 'zbs_customer_extra_'.$cleanKey, $v);
                                    $this->updateMeta(ZBS_TYPE_CONTACT,$id,'extra_'.$cleanKey,$v);

                                    #} Add it to this, which passes to IA
                                    $confirmedExtraMeta[$cleanKey] = $v;

                                }

                            }


                            #} INTERNAL AUTOMATOR 
                            #} & 
                            #} FALLBACKS
                            // UPDATING CONTACT
                            if (!$silentInsert){

                                #} FALLBACK 
                                #} (This fires for customers that weren't added because they already exist.)
                                #} e.g. x@g.com exists, so add log "x@g.com filled out form"
                                #} Requires a type and a shortdesc
                                if (
                                    isset($fallBackLog) && is_array($fallBackLog) 
                                    && isset($fallBackLog['type']) && !empty($fallBackLog['type'])
                                    && isset($fallBackLog['shortdesc']) && !empty($fallBackLog['shortdesc'])
                                ){

                                    #} Brutal add, maybe validate more?!

                                    #} Long desc if present:
                                    $zbsNoteLongDesc = ''; if (isset($fallBackLog['longdesc']) && !empty($fallBackLog['longdesc'])) $zbsNoteLongDesc = $fallBackLog['longdesc'];

                                        #} Only raw checked... but proceed.
                                        $newOrUpdatedLogID = zeroBS_addUpdateContactLog($id,-1,-1,array(
                                            #} Anything here will get wrapped into an array and added as the meta vals
                                            'type' => $fallBackLog['type'],
                                            'shortdesc' => $fallBackLog['shortdesc'],
                                            'longdesc' => $zbsNoteLongDesc
                                        ));


                                }

                                // catch dirty flag (update of status) (note, after update_post_meta - as separate)
                                //if (isset($_POST['zbsc_status_dirtyflag']) && $_POST['zbsc_status_dirtyflag'] == "1"){
                                // actually here, it's set above
                                if (isset($statusChange) && is_array($statusChange)){

                                    // status has changed

                                    // IA
                                    zeroBSCRM_FireInternalAutomator('contact.status.update',array(
                                        'id'=>$id,
                                        'againstid' => $id,
                                        'userMeta'=> $dataArr,
                                        'from' => $statusChange['from'],
                                        'to' => $statusChange['to']
                                        ));

                                }


                                // IA General contact update (2.87+)
                                zeroBSCRM_FireInternalAutomator('contact.update',array(
                                    'id'=>$id,
                                    'againstid' => $id,
                                    'userMeta'=> $dataArr,
                                    'prevSegments' => $contactsPreUpdateSegments
                                    ));

                                // IA Contact Update (email) (2.98.9+)
                                if (is_array($vitalChanges) && isset($vitalChanges['email'])) zeroBSCRM_FireInternalAutomator('contact.email.update',array(
                                    'id'=>$id,
                                    'againstid' => $id,
                                    'userMeta'=> $dataArr,
                                    'emailChange' => $vitalChanges['email']
                                    ));

                                // IA Contact Update (vital info) (2.98.9+)
                                if (is_array($vitalChanges) && count($vitalChanges) > 0) zeroBSCRM_FireInternalAutomator('contact.vitals.update',array(
                                    'id'=>$id,
                                    'againstid' => $id,
                                    'userMeta'=> $dataArr,
                                    'vitalChanges' => $vitalChanges
                                    ));

                            }

                                
                            // Successfully updated - Return id
                            return $id;

                        } else {

                            // FAILED update
                            return false;

                        }

        } else {
            
            #} No ID - must be an INSERT
            if ($wpdb->insert( 
                        $ZBSCRM_t['contacts'], 
                        $dataArr, 
                        $typeArr ) > 0){

                    #} Successfully inserted, lets return new ID
                    $newID = $wpdb->insert_id;

                    // tag work?
                    if (isset($data['tags']) && is_array($data['tags'])) $this->addUpdateContactTags(array('id'=>$newID,'tagIDs'=>$data['tags'],'mode'=>'replace'));

                    // externalSource work?
                    $approvedExternalSource = ''; // for IA below
                    if (isset($data['externalSources']) && is_array($data['externalSources']) && count($data['externalSources']) > 0) {

                        foreach ($data['externalSources'] as $es){

                            $esID = -1; if (isset($es['id'])) $esID = $es['id'];

                            $esID = $this->addUpdateExternalSource(array(

                                'id'            => $esID,

                                // fields (directly)
                                'data'          => array(

                                    'objectID'     => $newID,
                                    'objectType'   => ZBS_TYPE_CONTACT,
                                    'source'        => $es['source'],
                                    'uid'           => $es['uid']

                            )));

                            $approvedExternalSource = array(
                                'id' => $esID,
                                'objID'     => $newID,
                                'objType'   => ZBS_TYPE_CONTACT,
                                'source'        => $es['source'],
                                'uid'           => $es['uid']
                                );

                        } // / each ext source

                    }

                    // co's work?
                    if (isset($data['companies']) && is_array($data['companies']) && count($data['companies']) > 0) 
                        $this->addUpdateObjLinks(array(
                                                        'objtypefrom'       => ZBS_TYPE_CONTACT,
                                                        'objtypeto'         => ZBS_TYPE_COMPANY,
                                                        'objfromid'         => $newID,
                                                        'objtoids'          => $data['companies']));
                    // Custom fields?

                        #} Cycle through + add/update if set
                        if (is_array($customFields)) foreach ($customFields as $cK => $cF){

                            // any?
                            if (isset($data[$cK])){

                                // add update
                                $cfID = $this->addUpdateCustomField(array(
                                    'data'  => array(
                                            'objtype'   => ZBS_TYPE_CONTACT,
                                            'objid'     => $newID,
                                            'objkey'    => $cK,
                                            'objval'    => $data[$cK]
                                    )));

                            }

                        }


                        // Also got to catch any 'addr' custom fields :) 
                        if (is_array($addrCustomFields) && count($addrCustomFields) > 0){

                            // cycle through addr custom fields + save
                            // see #ZBS-518, not easy until addr's get DAL2
                            // WH deferring here

                            // WH later added via the addUpdateContactField method - should work fine if we catch properly in get
                            foreach ($addrCustomFields as $cK => $cF){

                                // hacky temp solution.
                                $cKN = (int)$cK+1;
                                $cKey = 'addr_cf'.$cKN;
                                $cKey2 = 'secaddr_cf'.$cKN;

                                // any?
                                if (isset($data[$cKey])){

                                    // add update
                                    $cfID = $this->addUpdateCustomField(array(
                                        'data'  => array(
                                                'objtype'   => ZBS_TYPE_CONTACT,
                                                'objid'     => $newID,
                                                'objkey'    => $cKey,
                                                'objval'    => $data[$cKey]
                                        )));

                                }

                                // any?
                                if (isset($data[$cKey2])){

                                    // add update
                                    $cfID = $this->addUpdateCustomField(array(
                                        'data'  => array(
                                                'objtype'   => ZBS_TYPE_CONTACT,
                                                'objid'     => $newID,
                                                'objkey'    => $cKey2,
                                                'objval'    => $data[$cKey2]
                                        )));

                                }

                            }

                        }

                    // / Custom Fields

                    #} Any extra meta keyval pairs?
                    // BRUTALLY updates (no checking)
                    $confirmedExtraMeta = false;
                    if (isset($extraMeta) && is_array($extraMeta)) {

                        $confirmedExtraMeta = array();

                            foreach ($extraMeta as $k => $v){

                            #} This won't fix stupid keys, just catch basic fails... 
                            $cleanKey = strtolower(str_replace(' ','_',$k));

                            #} Brutal update
                            //update_post_meta($postID, 'zbs_customer_extra_'.$cleanKey, $v);
                            $this->updateMeta(ZBS_TYPE_CONTACT,$id,'extra_'.$cleanKey,$v);

                            #} Add it to this, which passes to IA
                            $confirmedExtraMeta[$cleanKey] = $v;

                        }

                    }



                    #} INTERNAL AUTOMATOR 
                    #} & 
                    #} FALLBACKS
                    // NEW CONTACT

                   // zbs_write_log("ABOUT TO HIT THE AUTOMATOR... " . $silentInsert);

                    if (!$silentInsert){

                        //zbs_write_log("HITTING IT NOW...");

                        #} Add to automator
                        zeroBSCRM_FireInternalAutomator('contact.new',array(
                            'id'=>$newID,
                            'customerMeta'=>$dataArr,
                            'extsource'=>$approvedExternalSource,
                            'automatorpassthrough'=>$automatorPassthrough, #} This passes through any custom log titles or whatever into the Internal automator recipe.
                            'customerExtraMeta'=>$confirmedExtraMeta #} This is the "extraMeta" passed (as saved)
                        ));

                    }
                    
                    return $newID;

                } else {

                    #} Failed to Insert
                    return false;

                }

        }

        return false;

    }

     /**
     * adds or updates a contact's tags
     * ... this is really just a wrapper for addUpdateObjectTags
     *
     * @param array $args Associative array of arguments
     *              id (if update), owner, data (array of field data)
     *
     * @return int line ID
     */
    public function addUpdateContactTags($args=array()){

        global $ZBSCRM_t,$wpdb;

        #} ============ LOAD ARGS =============
        $defaultArgs = array(

            'id'            => -1,

            // EITHER of the following:
            'tagIDs'        => -1,
            'tags'          => -1,

            'mode'          => 'append'

        ); foreach ($defaultArgs as $argK => $argV){ $$argK = $argV; if (is_array($args) && isset($args[$argK])) {  if (is_array($args[$argK])){ $newData = $$argK; if (!is_array($newData)) $newData = array(); foreach ($args[$argK] as $subK => $subV){ $newData[$subK] = $subV; }$$argK = $newData;} else { $$argK = $args[$argK]; } } }
        #} =========== / LOAD ARGS ============

        #} ========== CHECK FIELDS ============

            // check id
            $id = (int)$id; if (empty($id) || $id <= 0) return false;

        #} ========= / CHECK FIELDS ===========     

        return $this->addUpdateObjectTags(array(
                'objtype'   =>ZBS_TYPE_CONTACT,
                'objid'     =>$id,
                'tags'      =>$tags,
                'tagIDs'    =>$tagIDs,
                'mode'      =>$mode));

    }

     /**
     * adds or updates a contact's company links
     * ... this is really just a wrapper for addUpdateObjLinks
     * fill in for zbsCRM_addUpdateCustomerCompany + zeroBS_setCustomerCompanyID
     *
     * @param array $args Associative array of arguments
     *              id (if update), owner, data (array of field data)
     *
     * @return int line ID
     */
    public function addUpdateContactCompanies($args=array()){

        global $ZBSCRM_t,$wpdb;

        #} ============ LOAD ARGS =============
        $defaultArgs = array(

            'id'                => -1,
            'companyIDs'        => -1

        ); foreach ($defaultArgs as $argK => $argV){ $$argK = $argV; if (is_array($args) && isset($args[$argK])) {  if (is_array($args[$argK])){ $newData = $$argK; if (!is_array($newData)) $newData = array(); foreach ($args[$argK] as $subK => $subV){ $newData[$subK] = $subV; }$$argK = $newData;} else { $$argK = $args[$argK]; } } }
        #} =========== / LOAD ARGS ============

        #} ========== CHECK FIELDS ============

            // check id
            $id = (int)$id; if (empty($id) || $id <= 0) return false;

            // if owner = -1, add current
            if (!isset($owner) || $owner === -1) $owner = zeroBSCRM_user();

            // check co id's
            if (!is_array($companyIDs)) $companyIDs = array();

        #} ========= / CHECK FIELDS ===========
                            
        return $this->addUpdateObjLinks(array(
                'objtypefrom'       => ZBS_TYPE_CONTACT,
                'objtypeto'         => ZBS_TYPE_COMPANY,
                'objfromid'         => $id,
                'objtoids'          => $companyIDs));

    }

     /**
     * adds or updates a contact's WPID
     * ... this is really just a wrapper for addUpdateContact
     * ... and replaces zeroBS_setCustomerWPID
     *
     * @param array $args Associative array of arguments
     *              id (if update), owner, data (array of field data)
     *
     * @return int line ID
     */
    public function addUpdateContactWPID($args=array()){

        global $ZBSCRM_t,$wpdb;

        #} ============ LOAD ARGS =============
        $defaultArgs = array(

            'id'            => -1,
            'WPID'          => -1

        ); foreach ($defaultArgs as $argK => $argV){ $$argK = $argV; if (is_array($args) && isset($args[$argK])) {  if (is_array($args[$argK])){ $newData = $$argK; if (!is_array($newData)) $newData = array(); foreach ($args[$argK] as $subK => $subV){ $newData[$subK] = $subV; }$$argK = $newData;} else { $$argK = $args[$argK]; } } }
        #} =========== / LOAD ARGS ============

        #} ========== CHECK FIELDS ============

            // if owner = -1, add current
            if (!isset($owner) || $owner === -1) $owner = zeroBSCRM_user();

            // check id
            $id = (int)$id; if (empty($id) || $id <= 0) return false;

            // WPID may be -1 (NULL)
            // -1 does okay here if ($WPID == -1) $WPID = '';

        #} ========= / CHECK FIELDS ===========


        #} Enact
        return $this->addUpdateContact(array(
            'id'            =>  $id,
            'limitedFields' =>array(
                array('key'=>'zbsc_wpid','val'=>$WPID,'type'=>'%d')
                )));

    

    }

     /**
     * deletes a contact object
     *
     * @param array $args Associative array of arguments
     *              id
     *
     * @return int success;
     */
    public function deleteContact($args=array()){

        global $ZBSCRM_t,$wpdb,$zbs;

        #} ============ LOAD ARGS =============
        $defaultArgs = array(

            'id'            => -1,
            'saveOrphans'   => true

        ); foreach ($defaultArgs as $argK => $argV){ $$argK = $argV; if (is_array($args) && isset($args[$argK])) {  if (is_array($args[$argK])){ $newData = $$argK; if (!is_array($newData)) $newData = array(); foreach ($args[$argK] as $subK => $subV){ $newData[$subK] = $subV; }$$argK = $newData;} else { $$argK = $args[$argK]; } } }
        #} =========== / LOAD ARGS ============

        #} Check ID & Delete :)
        $id = (int)$id;
        if (!empty($id) && $id > 0) {
            
            // delete orphans?
            if ($saveOrphans === false){

                #DB1LEGACY (TOMOVE -> where)
                // delete transactions
                $trans = zeroBS_getTransactionsForCustomer($id,false,1000000,0,false);
                foreach ($trans as $tran){

                    // delete post - not forced?
                    $res = wp_delete_post($tran['id'],false);

                } unset($trans);

                #DB1LEGACY (TOMOVE -> where)
                // delete invoices
                $is = zeroBS_getInvoicesForCustomer($id,false,1000000,0,false);
                foreach ($is as $i){

                    // delete post - not forced?
                    $res = wp_delete_post($i['id'],false);

                } unset($qs);

                #DB1LEGACY (TOMOVE -> where)
                // delete quotes
                $qs = zeroBS_getQuotesForCustomer($id,false,1000000,0,false,false);
                foreach ($qs as $q){

                    // delete post - not forced?
                    $res = wp_delete_post($q['id'],false);

                } unset($qs);

                // delete events
                // TBC? not sure how mike's savd, not an issue for now?!


                // delete any tag links
                if ($zbs->isDAL2()) $this->deleteTagObjLinks(array(

                        'objtype'       => ZBS_TYPE_CONTACT,
                        'objid'         => $id
                    ));

            }

            $del = zeroBSCRM_db2_deleteGeneric($id,'contacts');

            #} Add to automator
            zeroBSCRM_FireInternalAutomator('contact.delete',array(
                'id'=>$id,
                'saveOrphans'=>$saveOrphans
            ));

            return $del;

        }

        return false;

    }

    /**
     * tidy's the object from wp db into clean array
     *
     * @param array $obj (DB obj)
     *
     * @return array (clean obj)
     */
    private function tidy_contact($obj=false,$withCustomFields=false){

            global $zbs;

            $res = false;

            if (isset($obj->ID)){
            $res = array();
            $res['id'] = $obj->ID;
            /* 
              `zbs_site` INT NULL DEFAULT NULL,
              `zbs_team` INT NULL DEFAULT NULL,
              `zbs_owner` INT NOT NULL,
            */
            $res['owner'] = $obj->zbs_owner;

            $res['status'] = $this->stripSlashes($obj->zbsc_status);
            $res['email'] = $obj->zbsc_email;
            $res['prefix'] = $this->stripSlashes($obj->zbsc_prefix);
            $res['fname'] = $this->stripSlashes($obj->zbsc_fname);
            $res['lname'] = $this->stripSlashes($obj->zbsc_lname);
            $res['addr1'] = $this->stripSlashes($obj->zbsc_addr1);
            $res['addr2'] = $this->stripSlashes($obj->zbsc_addr2);
            $res['city'] = $this->stripSlashes($obj->zbsc_city);
            $res['county'] = $this->stripSlashes($obj->zbsc_county);
            $res['country'] = $this->stripSlashes($obj->zbsc_country);
            $res['postcode'] = $this->stripSlashes($obj->zbsc_postcode);

            // until we add multi-addr support, these get translated into old field names (secaddr_)
            $res['secaddr_addr1'] = $this->stripSlashes($obj->zbsc_secaddr1);
            $res['secaddr_addr2'] = $this->stripSlashes($obj->zbsc_secaddr2);
            $res['secaddr_city'] = $this->stripSlashes($obj->zbsc_seccity);
            $res['secaddr_county'] = $this->stripSlashes($obj->zbsc_seccounty);
            $res['secaddr_country'] = $this->stripSlashes($obj->zbsc_seccountry);
            $res['secaddr_postcode'] = $this->stripSlashes($obj->zbsc_secpostcode);
            $res['hometel'] = $obj->zbsc_hometel;
            $res['worktel'] = $obj->zbsc_worktel;
            $res['mobtel'] = $obj->zbsc_mobtel;
            //$res['notes'] = $obj->zbsc_notes;
            $res['worktel'] = $obj->zbsc_worktel;
            $res['wpid'] = $obj->zbsc_wpid;
            $res['avatar'] = $obj->zbsc_avatar;


            // gross backward compat
            if ($zbs->db1CompatabilitySupport) $res['meta'] = $res;


            // to maintain old obj more easily, here we refine created into datestamp
            $res['created'] = zeroBSCRM_locale_utsToDatetime($obj->zbsc_created);
            if ($obj->zbsc_lastcontacted != -1 && !empty($obj->zbsc_lastcontacted) && $obj->zbsc_lastcontacted > 0)
                $res['lastcontacted'] = zeroBSCRM_locale_utsToDatetime($obj->zbsc_lastcontacted);
            else
                $res['lastcontacted'] = -1;
            $res['createduts'] = $obj->zbsc_created; // this is the UTS (int14)
            $res['lastupdated'] = $obj->zbsc_lastupdated;
            $res['lastcontacteduts'] = $obj->zbsc_lastcontacted; // this is the UTS (int14)


            // Build any extra formats (using fields)
            $res['fullname'] = $this->format_fullname($res);
            $res['name'] = $res['fullname']; // this one is for backward compat (pre db2)

            // custom fields?
            if ($withCustomFields){
                
                #} Retrieve any cf
                $custFields = $this->getActiveCustomFields(array('objtypeid'=>ZBS_TYPE_CONTACT));

                if (is_array($custFields)) foreach ($custFields as $cK => $cF){

                    // custom field (e.g. 'third name') it'll be passed here as 'third-name'
                    // ... problem is mysql does not like that :) so we have to chage here:
                    // in this case we REVERSE this: prepend cf's with cf_ and we switch - for _
                    // ... by using $cKey below, instead of cK
                    $cKey = 'cf_'.str_replace('-','_',$cK);

                    $res[$cK] = '';

                    // if normal
                    if (isset($obj->$cK)) $res[$cK] = $this->stripSlashes($obj->$cK);
                    
                    // if cf
                    if (isset($obj->$cKey)) $res[$cK] = $this->stripSlashes($obj->$cKey);

                }

                #} Retrieve addr custfiedls
                $addrCustomFields = zeroBSCRM_getAddressCustomFields();

                if (is_array($addrCustomFields)) foreach ($addrCustomFields as $cK => $cF){

                    // hacky temp solution.
                    $cKN = (int)$cK+1;
                    $cKey = 'addr_cf'.$cKN;
                    $cKey2 = 'secaddr_cf'.$cKN;

                    $res[$cKey] = '';
                    $res[$cKey2] = '';
                    if (isset($obj->$cKey)) $res[$cKey] = $this->stripSlashes($obj->$cKey);
                    if (isset($obj->$cKey2)) $res[$cKey2] = $this->stripSlashes($obj->$cKey2);

                }

            }

        } 


        return $res;


    }


    /**
     * remove any non-db fields from the object
     * basically takes array like array('owner'=>1,'fname'=>'x','fullname'=>'x')
     * and returns array like array('owner'=>1,'fname'=>'x')
     *
     * @param array $obj (clean obj)
     *
     * @return array (db ready arr)
     */
    private function db_ready_contact($obj=false){

            global $zbs;

            /*
            if (is_array($obj)){

                $removeNonDBFields = array('meta','fullname','name');

                foreach ($removeNonDBFields as $fKey){

                    if (isset($obj[$fKey])) unset($obj[$fKey]);

                }

            }
            */

            $legitFields = array(
                'owner','status','email','prefix','fname','lname',
                'addr1','addr2','city','county','country','postcode',
                // WH corrected 13/06/18 2.84 'secaddr_addr1','secaddr_addr2','secaddr_city','secaddr_county','secaddr_country','secaddr_postcode',
                'secaddr1','secaddr2','seccity','seccounty','seccountry','secpostcode',
                'hometel','worktel','mobtel',
                'wpid','avatar',
                'tw','fb','li',
                'created','lastupdated','lastcontacted');


            $ret = array();
            if (is_array($obj)){

                foreach ($legitFields as $fKey){

                    if (isset($obj[$fKey])) $ret[$fKey] = $obj[$fKey];

                }

            }

            return $ret;


    }

    /**
     * Returns an ownerid against a contact
     * Replaces zeroBS_getCustomerOwner
     *
     * @param int id Contact ID
     *
     * @return int contact owner id
     */
    public function getContactOwner($id=-1){

        $id = (int)$id;

        if ($id > 0){

            return $this->getFieldByID(array(
                'id' => $id,
                'objtype' => ZBS_TYPE_CONTACT,
                'colname' => 'zbs_owner',
                'ignoreowner'=>true));

        }

        return false;
        
    }

    /**
     * Returns an status against a contact
     *
     * @param int id Contact ID
     *
     * @return str contact status string
     */
    public function getContactStatus($id=-1){

        $id = (int)$id;

        if ($id > 0){

            return $this->getFieldByID(array(
                'id' => $id,
                'objtype' => ZBS_TYPE_CONTACT,
                'colname' => 'zbsc_status',
                'ignoreowner'=>true));

        }

        return false;
        
    }

    /**
     * Returns an email addr against a contact
     * Replaces getContactEmail
     *
     * @param int id Contact ID
     *
     * @return string Contact email
     */
    public function getContactEmail($id=-1){

        $id = (int)$id;

        if ($id > 0){

            return $this->getFieldByID(array(
                'id' => $id,
                'objtype' => ZBS_TYPE_CONTACT,
                'colname' => 'zbsc_email',
                'ignoreowner' => true));

        }

        return false;
        
    }

    /**
     * Returns an email addr against a contact
     * Replaces zeroBS_customerMobile
     *
     * @param int id Contact ID
     *
     * @return string Contact email
     */
    public function getContactMobile($id=-1){

        $id = (int)$id;

        if ($id > 0){

            return $this->getFieldByID(array(
                'id' => $id,
                'objtype' => ZBS_TYPE_CONTACT,
                'colname' => 'zbsc_mobtel',
                'ignoreowner' => true));

        }

        return false;
        
    }

    /**
     * Returns a formatted fullname of a 
     * Replaces zeroBS_customerName
     *
     * @param int id Contact ID
     * @param array Contact array (if already loaded can pass)
     * @param array args (see format_fullname func)
     *
     * @return string Contact full name
     */
    public function getContactFullName($id=-1,$contactArr=false){

        $id = (int)$id;

        if ($id > 0){

            // get a limited-fields contact obj
            $contact = $this->getContact($id,array('withCustomFields' => false,'fields'=>array('zbsc_prefix','zbsc_fname','zbsc_lname'),'ignoreowner' => true));
            if (isset($contact) && is_array($contact) && isset($contact['prefix']))
                return $this->format_fullname($contact);

        } elseif (is_array($contactArr)){

            // pass through
            return $this->format_fullname($contactArr);

        }

        return false;
        
    }

    /**
     * Returns a formatted fullname (optionally including ID + first line of addr)
     * Replaces zeroBS_customerName more fully than getContactFullName
     * Also replaces zeroBS_getCustomerName
     *
     * @param int id Contact ID
     * @param array Contact array (if already loaded can pass)
     * @param array args (see format_fullname func)
     *
     * @return string Contact full name
     */
    public function getContactFullNameEtc($id=-1,$contactArr=false,$args=array()){

        $id = (int)$id;

        if ($id > 0){

            // get a limited-fields contact obj
            $contact = $this->getContact($id,array('withCustomFields' => false,'fields'=>array('zbsc_addr1','zbsc_prefix','zbsc_fname','zbsc_lname'),'ignoreowner' => true));
            if (isset($contact) && is_array($contact) && isset($contact['prefix']))
                return $this->format_name_etc($contact,$args);

        } elseif (is_array($contactArr)){

            // pass through
            return $this->format_name_etc($contactArr,$args);

        }

        return false;
        
    }

    /**
     * Returns a formatted address of a contact
     * Replaces zeroBS_customerAddr
     *
     * @param int id Contact ID
     * @param array Contact array (if already loaded can pass)
     * @param array args (see format_address func)
     *
     * @return string Contact addr html
     */
    public function getContactAddress($id=-1,$contactArr=false,$args=array()){

        $id = (int)$id;

        if ($id > 0){

            // get a limited-fields contact obj
            // this is hacky, but basically get whole basic contact record for this for now, because 
            // this doesn't properly get addr custom fields:
            // $contact = $this->getContact($id,array('withCustomFields' => false,'fields'=>$this->field_list_address,'ignoreowner'=>true));
            $contact = $this->getContact($id,array('withCustomFields' => true,'ignoreowner'=>true));
            if (isset($contact) && is_array($contact) && isset($contact['addr1']))
                return $this->format_address($contact,$args);

        } elseif (is_array($contactArr)){

            // pass through
            return $this->format_address($contactArr,$args);

        }

        return false;
        
    }

    /**
     * Returns a formatted address of a contact (2nd addr)
     * Replaces zeroBS_customerAddr
     *
     * @param int id Contact ID
     * @param array Contact array (if already loaded can pass)
     * @param array args (see format_address func)
     *
     * @return string Contact addr html
     */
    public function getContact2ndAddress($id=-1,$contactArr=false,$args=array()){

        $id = (int)$id;

        $args['secondaddr'] = true;

        if ($id > 0){

            // get a limited-fields contact obj
            // this is hacky, but basically get whole basic contact record for this for now, because 
            // this doesn't properly get addr custom fields:
            // $contact = $this->getContact($id,array('withCustomFields' => false,'fields'=>$this->field_list_address2,'ignoreowner'=>true));
            $contact = $this->getContact($id,array('withCustomFields' => true,'ignoreowner'=>true));            
            if (isset($contact) && is_array($contact) && isset($contact['addr1']))
                return $this->format_address($contact,$args);

        } elseif (is_array($contactArr)){

            // pass through
            return $this->format_address($contactArr,$args);

        }

        return false;
        
    }
    
    /**
     * Returns a contacts tag array
     * Replaces zeroBSCRM_getCustomerTags AND  zeroBSCRM_getContactTagsArr
     *
     * @param int id Contact ID
     *
     * @return mixed
     */
    public function getContactTags($id=-1){

        $id = (int)$id;

        if ($id > 0){

            return $this->getTagsForObjID(array('objtypeid'=>ZBS_TYPE_CONTACT,'objid'=>$id));

        }

        return false;
        
    }


    /**
     * Returns last contacted uts against a contact
     *
     * @param int id Contact ID
     *
     * @return int Contact last contacted date as uts (or -1)
     */
    public function getContactLastContactUTS($id=-1){

        $id = (int)$id;

        if ($id > 0){

            return $this->getFieldByID(array(
                'id' => $id,
                'objtype' => ZBS_TYPE_CONTACT,
                'colname' => 'zbsc_lastcontacted',
                'ignoreowner' => true));

        }

        return false;
        
    }

    /**
     * updates lastcontacted date for a contact
     *
     * @param int id Contact ID
     * @param int uts last contacted
     *
     * @return bool
     */
    public function setContactLastContactUTS($id=-1,$lastContactedUTS=-1){

        $id = (int)$id;

        if ($id > 0){

            return $this->addUpdateContact(array(
                'id'=>$id,
                'limitedFields'=>array(
                    array('key'=>'zbsc_lastcontacted','val' => $lastContactedUTS,'type' => '%d')
            )));

        }

        return false;
        
    }

    /**
     * Returns a set of social accounts for a contact (tw,li,fb)
     *
     * @param int id Contact ID
     *
     * @return array social acc's
     */
    public function getContactSocials($id=-1){

        $id = (int)$id;

        if ($id > 0){

            // lazy 3 queries, optimise later

            $tw = $this->getFieldByID(array(
                'id' => $id,
                'objtype' => ZBS_TYPE_CONTACT,
                'colname' => 'zbsc_tw',
                'ignoreowner' => true));

            $li = $this->getFieldByID(array(
                'id' => $id,
                'objtype' => ZBS_TYPE_CONTACT,
                'colname' => 'zbsc_li',
                'ignoreowner' => true));

            $fb = $this->getFieldByID(array(
                'id' => $id,
                'objtype' => ZBS_TYPE_CONTACT,
                'colname' => 'zbsc_fb',
                'ignoreowner' => true));

            return array('tw'=>$tw,'li' => $li, 'fb' => $fb);

        }

        return false;
        
    }
    
    /**
     * Returns a linked WP ID against a contact
     * Replaces zeroBS_getCustomerWPID
     *
     * @param int id Contact ID
     *
     * @return int Contact wp id
     */
    public function getContactWPID($id=-1){

        $id = (int)$id;

        if ($id > 0){

            return $this->getFieldByID(array(
                'id' => $id,
                'objtype' => ZBS_TYPE_CONTACT,
                'colname' => 'zbsc_wpid',
                'ignoreowner' => true));

        }

        return false;
        
    }
    
    /**
     * Returns true/false whether or not user has 'do-not-email' flag (from unsub email link click)
     *
     * @param int id Contact ID
     *
     * @return bool
     */
    public function getContactDoNotMail($id=-1){

        $id = (int)$id;

        if ($id > 0){

            return $this->meta(ZBS_TYPE_CONTACT,$id,'do-not-email',false);

        }

        return false;
        
    }
    
    /**
     * updates true/false whether or not user has 'do-not-email' flag (from unsub email link click)
     *
     * @param int id Contact ID
     * @param bool whether or not to set donotmail
     *
     * @return bool
     */
    public function setContactDoNotMail($id=-1,$doNotMail=true){

        $id = (int)$id;

        if ($id > 0){

            if ($doNotMail)
                return $this->updateMeta(ZBS_TYPE_CONTACT,$id,'do-not-email',true);
            else
                // remove
                return $this->deleteMeta(array(
                    'objtype' => ZBS_TYPE_CONTACT,
                    'objid' => $id,
                    'key' => 'do-not-email'));

        }

        return false;
        
    }
    
    /**
     * Returns an url to contact avatar (Gravatar if not set?)
     * For now just returns the field
     * Replaces zeroBS_getCustomerIcoHTML?
     *
     * @param int id Contact ID
     *
     * @return int Contact wp id
     */
    public function getContactAvatarURL($id=-1){

        $id = (int)$id;

        if ($id > 0){

            return $this->getFieldByID(array(
                'id' => $id,
                'objtype' => ZBS_TYPE_CONTACT,
                'colname' => 'zbsc_avatar',
                'ignoreowner' => true));

        }

        return false;
        
    }
    
    /**
     * Returns an url to contact avatar (Gravatar if not set?)
     * Or empty if 'show default empty' = false
     *
     * @param int id Contact ID
     * @param bool showPlaceholder does what it says on tin
     *
     * @return string URL for img
     */
    public function getContactAvatar($id=-1,$showPlaceholder=true){

        $id = (int)$id;

        if ($id > 0){

            $avatarMode = zeroBSCRM_getSetting('avatarmode');
            switch ($avatarMode){


                case 1: // gravitar
                
                    $potentialEmail = $this->getContactEmail($id);
                    if (!empty($potentialEmail)) return zeroBSCRM_getGravatarURLfromEmail($potentialEmail);
                    
                    // default
                    return zeroBSCRM_getDefaultContactAvatar();

                    break;

                case 2: // custom img
                        
                    $dbURL = $this->getContactAvatarURL($id);
                    if (!empty($dbURL)) return $dbURL;

                    // default
                    return zeroBSCRM_getDefaultContactAvatar();

                    break;

                case 3: // none
                    return '';
                    break;
                

            }


        }

        // fallback
        if ($showPlaceholder) return zeroBSCRM_getDefaultContactAvatar();

        return false;
        
    }

    
    /**
     * Returns html of contact avatar (Gravatar if not set?)
     * Or empty if 'show default empty' = false
     *
     * @param int id Contact ID
     *
     * @return string HTML
     */
    public function getContactAvatarHTML($id=-1,$size=100,$extraClasses=''){

        $id = (int)$id;

        if ($id > 0){

            $avatarMode = zeroBSCRM_getSetting('avatarmode');
            switch ($avatarMode){


                case 1: // gravitar
                
                    $potentialEmail = $this->getContactEmail($id);
                    if (!empty($potentialEmail)) return '<img src="'.zeroBSCRM_getGravatarURLfromEmail($potentialEmail,$size).'" class="'.$extraClasses.' zbs-gravatar" alt="" />';
                    
                    // default
                    return zeroBSCRM_getDefaultContactAvatarHTML();

                    break;

                case 2: // custom img
                        
                    $dbURL = $this->getContactAvatarURL($id);
                    if (!empty($dbURL)) return '<img src="'.$dbURL.'" class="'.$extraClasses.' zbs-custom-avatar" alt="" />';

                    // default
                    return zeroBSCRM_getDefaultContactAvatarHTML();

                    break;

                case 3: // none
                    return '';
                    break;
                

            }


        }

        return '';
        
    }




    /**
     * Returns a count of contacts (owned)
     * Replaces zeroBS_customerCount AND zeroBS_getCustomerCount AND zeroBS_customerCountByStatus
     *
     *
     * @return int count
     */
    public function getContactCount($args=array()){

        #} ============ LOAD ARGS =============
        $defaultArgs = array(

            // Search/Filtering (leave as false to ignore)
            'inCompany'     => false, // will be an ID if used
            'withStatus'    => false, // will be str if used

            // permissions
            'ignoreowner'   => true, // this'll let you not-check the owner of obj

        ); foreach ($defaultArgs as $argK => $argV){ $$argK = $argV; if (is_array($args) && isset($args[$argK])) {  if (is_array($args[$argK])){ $newData = $$argK; if (!is_array($newData)) $newData = array(); foreach ($args[$argK] as $subK => $subV){ $newData[$subK] = $subV; }$$argK = $newData;} else { $$argK = $args[$argK]; } } }
        #} =========== / LOAD ARGS =============

        $whereArr = array();

        if ($inCompany) $whereArr['incompany'] = array('ID','IN','(SELECT DISTINCT zbsol_objid_from FROM '.$ZBSCRM_t['objlinks']." WHERE zbsol_objtype_from = ".ZBS_TYPE_CONTACT." AND zbsol_objtype_to = ".ZBS_TYPE_COMPANY." AND zbsol_objid_to = %d)",$inCompany);

        if ($withStatus !== false && !empty($withStatus)) $whereArr['status'] = array('zbsc_status','=','%s',$withStatus);

        return $this->getFieldByWHERE(array(
            'objtype' => ZBS_TYPE_CONTACT,
            'colname' => 'COUNT(ID)',
            'where' => $whereArr,
            'ignoreowner' => $ignoreowner));

    

        return 0;
        
    }

    /**
     * Returns a customer's associated company ID's
     * Replaces zeroBS_getCustomerCompanyID (via LEGACY func)
     *
     * @param int id
     *
     * @return array int id
     */
    public function getContactCompanies($id=-1){

        if (!empty($id)){

            /*
            $contact = $this->getContact($id,array(
                'withCompanies' => true,
                'fields' => array('ID')));

            if (is_array($contact) && isset($contact['companies'])) return $contact['companies'];
            */

            // cleaner:
            return $this->getObjsLinkedToObj(array(
                                'objtypefrom'   =>  ZBS_TYPE_CONTACT, // contact
                                'objtypeto'     =>  ZBS_TYPE_COMPANY, // company
                                'objfromid'     =>  $id,
                                'ignoreowner' => true));

        }

        return array();
        
    }


    /**
     * Returns the next customer ID and the previous customer ID
     * Used for the navigation between contacts. 
     *
     * @param int id
     *
     * @return array int id
     */
    public function getContactPrevNext($id=-1){

        global $ZBSCRM_t, $wpdb;

        if($id > 0){
            //then run the queries.. 
            $nextSQL = $this->prepare("SELECT MIN(ID) FROM ".$ZBSCRM_t['contacts']." WHERE ID > %d", $id);

            $res['next'] = $wpdb->get_var($nextSQL);

            $prevSQL = $this->prepare("SELECT MAX(ID) FROM ".$ZBSCRM_t['contacts']." WHERE ID < %d", $id);

            $res['prev'] = $wpdb->get_var($prevSQL);

            return $res;

        }

        return false;

    }   

    

    // =========== / CONTACTS      ===================================================
    // ===============================================================================






    // ===============================================================================
    // ===========   CUSTOM FIELDS   =================================================

    /**
     * returns true if field key exists as custom field for CONTACT
     *
     * @param array $args Associative array of arguments
     *              objtypeid
     *
     * @return array of customfield field keys
     */
    public function isActiveCustomField_Contact($customFieldKey=''){

        #} These are simply stored in settings with a key of customfields_objtype e.g. customfields_contact
        if (!empty($objtypeid) && $objtypeid > 0 && !empty($customFieldKey)) {

            // get custom fields
            $customFields = $this->getActiveCustomFields(array('objtypeid'=>ZBS_TYPE_CONTACT));

            // validate there
            if (is_array($customFields)) foreach ($customFields as $cfK => $cfV){

                if ($cfK == $customFieldKey) return true;
            }

        }

        return false;
    } 

    /**
     * returns true if field key exists as custom field for obj
     *
     * @param array $args Associative array of arguments
     *              objtypeid
     *
     * @return array of customfield field keys
     */
    public function isActiveCustomField($args=array()){

        #} ============ LOAD ARGS =============
        $defaultArgs = array(

            'objtypeid' => -1,
            'customFieldKey' => ''

        ); foreach ($defaultArgs as $argK => $argV){ $$argK = $argV; if (is_array($args) && isset($args[$argK])) {  if (is_array($args[$argK])){ $newData = $$argK; if (!is_array($newData)) $newData = array(); foreach ($args[$argK] as $subK => $subV){ $newData[$subK] = $subV; }$$argK = $newData;} else { $$argK = $args[$argK]; } } }
        #} =========== / LOAD ARGS =============

        #} These are simply stored in settings with a key of customfields_objtype e.g. customfields_contact
        if (!empty($objtypeid) && $objtypeid > 0 && !empty($customFieldKey)) {

            // get custom fields
            $customFields = $this->getActiveCustomFields(array('objtypeid'=>$objtypeid));

            // validate there
            if (is_array($customFields)) foreach ($customFields as $cfK => $cfV){

                if ($cfK == $customFieldKey) return true;
            }

        }

        return false;
    } 



    /**
     * returns active custom field keys for an obj type
     *
     * @param array $args Associative array of arguments
     *              objtypeid
     *
     * @return array of customfield field keys
     */
    public function getActiveCustomFields($args=array()){

        #} ============ LOAD ARGS =============
        $defaultArgs = array(

            'objtypeid' => -1,

        ); foreach ($defaultArgs as $argK => $argV){ $$argK = $argV; if (is_array($args) && isset($args[$argK])) {  if (is_array($args[$argK])){ $newData = $$argK; if (!is_array($newData)) $newData = array(); foreach ($args[$argK] as $subK => $subV){ $newData[$subK] = $subV; }$$argK = $newData;} else { $$argK = $args[$argK]; } } }
        #} =========== / LOAD ARGS =============

        #} These are simply stored in settings with a key of customfields_objtype e.g. customfields_contact
        if (!empty($objtypeid) && $objtypeid > 0) {

            return $this->setting('customfields_'.$this->objTypeKey($objtypeid),array());

        }

        return array();
    } 

    /**
     * updates active custom field keys for an obj type
     * No checking whatsoever
     *
     * @param array $args Associative array of arguments
     *              objtypeid
     *
     * @return array of customfield field keys
     */
    public function updateActiveCustomFields($args=array()){

        #} ============ LOAD ARGS =============
        $defaultArgs = array(

            'objtypeid' => -1,
            'fields' => array()

        ); foreach ($defaultArgs as $argK => $argV){ $$argK = $argV; if (is_array($args) && isset($args[$argK])) {  if (is_array($args[$argK])){ $newData = $$argK; if (!is_array($newData)) $newData = array(); foreach ($args[$argK] as $subK => $subV){ $newData[$subK] = $subV; }$$argK = $newData;} else { $$argK = $args[$argK]; } } }
        #} =========== / LOAD ARGS =============

        #} These are simply stored in settings with a key of customfields_objtype e.g. customfields_contact
        if (!empty($objtypeid) && $objtypeid > 0) {

            return $this->updateSetting('customfields_'.$this->objTypeKey($objtypeid),$fields);

        }

        return array();
    } 


    /**
     * returns scalar value of 1 custom field line (or it's ID)
     * ... real custom fields will be got as part of getCustomers more commonly (this is for 1 alone)
     *
     * @param array $args   Associative array of arguments
     *                      objtypeid,objid,objkey
     *
     * @return array result
     */
    public function getCustomFieldVal($args=array()){

        #} =========== LOAD ARGS ==============
        $defaultArgs = array(

            'objtypeid'     => -1, // e.g. 1 = contact
            'objid'         => -1, // e.g. contact #101
            'objkey'        => '', // e.g. notes

            // permissions
            'ignoreowner'   => false, // this'll let you not-check the owner of obj

            // returns scalar ID of line
            'onlyID'        => false

        ); foreach ($defaultArgs as $argK => $argV){ $$argK = $argV; if (is_array($args) && isset($args[$argK])) {  if (is_array($args[$argK])){ $newData = $$argK; if (!is_array($newData)) $newData = array(); foreach ($args[$argK] as $subK => $subV){ $newData[$subK] = $subV; }$$argK = $newData;} else { $$argK = $args[$argK]; } } }
        #} =========== / LOAD ARGS =============
        
        #} Check IDs
        $objtypeid = (int)$objtypeid; $objid = (int)$objid;
        if (!empty($objtypeid) && $objtypeid > 0 && !empty($objid) && $objid > 0 && !empty($objkey)){

            global $ZBSCRM_t,$wpdb; 
            $wheres = array('direct'=>array()); $whereStr = ''; $additionalWhere = ''; $params = array(); $res = array();

            #} Build query
            $query = "SELECT ID,zbscf_objval FROM ".$ZBSCRM_t['customfields'];

            #} ============= WHERE ================

                #} Add obj type
                $wheres['zbscf_objtype'] = array('zbscf_objtype','=','%d',$objtypeid);

                #} Add obj ID
                $wheres['zbscf_objid'] = array('zbscf_objid','=','%d',$objid);

                #} Add obj key
                $wheres['zbscf_objkey'] = array('zbscf_objkey','=','%s',$objkey);

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
                    if ($onlyID === true)  return $potentialRes->ID;

                    // tidy
                    $res = $this->tidy_customfieldvalSingular($potentialRes);

                    return $res;

            }

        } // / if ID

        return false;

    }

     /**
     * adds or updates a customfield object
     * NOTE: because these are specific to unique ID of obj, there's no need for site/team etc. here
     *
     * @param array $args Associative array of arguments
     *              id (if update), owner, data (array of field data)
     *
     * @return int line ID
     */
    public function addUpdateCustomField($args=array()){

        global $ZBSCRM_t,$wpdb;

        #} ============ LOAD ARGS =============
        $defaultArgs = array(

            'id'            => -1, // Custom field line ID (not obj id!)
            'owner'         => -1,

            // fields (directly)
            'data'          => array(
                'objtype' => -1,
                'objid' => -1,
                'objkey' => '',
                'objval' => 'NULL'
            )

        ); foreach ($defaultArgs as $argK => $argV){ $$argK = $argV; if (is_array($args) && isset($args[$argK])) {  if (is_array($args[$argK])){ $newData = $$argK; if (!is_array($newData)) $newData = array(); foreach ($args[$argK] as $subK => $subV){ $newData[$subK] = $subV; }$$argK = $newData;} else { $$argK = $args[$argK]; } } }
        #} =========== / LOAD ARGS ============


        #} ========== CHECK FIELDS ============


            $id = (int)$id;

            if (isset($data['objid'])) $data['objid'] = (int)$data['objid'];
            if (isset($data['objtype'])) $data['objtype'] = (int)$data['objtype'];

            // check obtype is completed + legit
            if (!isset($data['objtype']) || empty($data['objtype'])) return false;
            if ($this->objTypeKey($data['objtype']) === -1) return false;


            // check key + ID present
            if (!isset($data['objkey']) || empty($data['objkey'])) return false;
            if (!isset($data['objid']) || $data['objid'] <= 0) return false;

            // if owner = -1, add current
            if (!isset($owner) || $owner === -1) $owner = zeroBSCRM_user();

            // ID finder - if obj id +  key + val + typeid provided, check CF not already present (if so overwrite)     
            if ((empty($id) || $id <= 0)
                && 
                (isset($data['objtype']) && !empty($data['objtype']))
                && 
                (isset($data['objid']) && !empty($data['objid']))
                &&
                (isset($data['objkey']) && !empty($data['objkey']))) {

                // check existence + return ID
                $potentialID = (int)$this->getCustomFieldVal(array(
                                'objtypeid'     => $data['objtype'],
                                'objid'         => $data['objid'],
                                'objkey'        => $data['objkey'],
                                'onlyID'        => true,
                                'ignoreowner'   => true
                                ));
                // override empty ID 
                if (!empty($potentialID) && $potentialID > 0) $id = $potentialID;

            }

        #} ========= / CHECK FIELDS ===========

        #} Check if ID present
        if (!empty($id) && $id > 0){

                #} Check if obj exists (here) - for now just brutal update (will error when doesn't exist)

                #} Attempt update
                if ($wpdb->update( 
                        $ZBSCRM_t['customfields'], 
                        array( 

                            // ownership
                            // no need to update these (as of yet) - can't move teams etc.
                            //'zbs_site' => zeroBSCRM_installSite(),
                            //'zbs_team' => zeroBSCRM_installTeam(),
                            'zbs_owner' => $owner,

                            // fields
                            'zbscf_objtype' => $data['objtype'],
                            'zbscf_objid' => $data['objid'],
                            'zbscf_objkey' => $data['objkey'],
                            'zbscf_objval' => $data['objval'],

                            //'zbscf_created' => time(),
                            'zbscf_lastupdated' => time()
                        ), 
                        array( // where
                            'ID' => $id
                            ),
                        array( // field data types
                            //'%d',  // site
                            //'%d',  // team
                            '%d',  // owner

                            '%d',  
                            '%d',  
                            '%s', 
                            '%s',
 
                            '%d' // last updated
                        ),
                        array( // where data types
                            '%d'
                            )) !== false){

                            // Successfully updated - Return id
                            return $id;

                        } else {

                            // FAILED update
                            return false;

                        }

        } else {
            
            #} No ID - must be an INSERT
            if ($wpdb->insert( 
                        $ZBSCRM_t['customfields'], 
                        array( 

                            // ownership
                            'zbs_site' => zeroBSCRM_site(),
                            'zbs_team' => zeroBSCRM_team(),
                            'zbs_owner' => $owner,

                            // fields
                            'zbscf_objtype' => $data['objtype'],
                            'zbscf_objid' => $data['objid'],
                            'zbscf_objkey' => $data['objkey'],
                            'zbscf_objval' => $data['objval'],

                            'zbscf_created' => time(),
                            'zbscf_lastupdated' => time()
                        ), 
                        array( // field data types
                            '%d',  // site
                            '%d',  // team
                            '%d',  // owner

                            '%d',  
                            '%d',  
                            '%s', 
                            '%s',

                            '%d',  
                            '%d'  
                        ) ) > 0){

                    #} Successfully inserted, lets return new ID
                    $newID = $wpdb->insert_id;
                    return $newID;

                } else {

                    #} Failed to Insert
                    return false;

                }

        }

        return false;

    }

     /**
     * deletes a customfield object
     *
     * @param array $args Associative array of arguments
     *              id
     *
     * @return int success;
     */
    public function deleteCustomField($args=array()){

        global $ZBSCRM_t,$wpdb;

        #} ============ LOAD ARGS =============
        $defaultArgs = array(

            'id'            => -1

        ); foreach ($defaultArgs as $argK => $argV){ $$argK = $argV; if (is_array($args) && isset($args[$argK])) {  if (is_array($args[$argK])){ $newData = $$argK; if (!is_array($newData)) $newData = array(); foreach ($args[$argK] as $subK => $subV){ $newData[$subK] = $subV; }$$argK = $newData;} else { $$argK = $args[$argK]; } } }
        #} =========== / LOAD ARGS ============

        #} Check ID & Delete :)
        $id = (int)$id;
        if (!empty($id) && $id > 0) return zeroBSCRM_db2_deleteGeneric($id,'customfields');

        return false;

    }

    /**
     * tidy's the object from wp db into clean array
     *
     * @param array $obj (DB obj)
     *
     * @return array (clean obj)
     */
    private function tidy_customfieldvalSingular($obj=false){

        $res = false;

        if (isset($obj->ID)){

            // just return the value here!
            $res = $this->stripSlashes($obj->zbscf_objval);

        }

        return $res;


    }


    // =========== / CUSTOM FIELDS     ===============================================
    // ===============================================================================





    // ===============================================================================
    // ===========   LOGS   ==========================================================


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
            'ignoreowner'   => false, // this'll let you not-check the owner of obj

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

                    if ($incMeta) $potentialRes->meta = $this->getMeta(array(

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
            'inArr' => -1, // if is an ARRAY of objid's will use

            'searchPhrase'  => '', // specify to search through all note descs
            'notetype' => -1, // specify a permified type to grab collection
            'notetypes' => -1, // specify an array of permified types to grab collection
            'owner' => -1, // specify a creator to query
            'olderThan'         => false, // uts
            'newerThan'         => false, // uts

            // return
            'incMeta'   => false,

            'sortByField'   => 'ID',
            'sortOrder'     => 'ASC',
            'page'          => 0,
            'perPage'       => 100,

            // permissions
            'ignoreowner'   => false // this'll let you not-check the owner of obj

        ); foreach ($defaultArgs as $argK => $argV){ $$argK = $argV; if (is_array($args) && isset($args[$argK])) {  if (is_array($args[$argK])){ $newData = $$argK; if (!is_array($newData)) $newData = array(); foreach ($args[$argK] as $subK => $subV){ $newData[$subK] = $subV; }$$argK = $newData;} else { $$argK = $args[$argK]; } } }
        #} =========== / LOAD ARGS =============

        #} ========== CHECK FIELDS ============

            // ID
            $objid = (int)$objid;
            // since v2.99.4 can be passed as empty. 
            //if (empty($objid) || $objid <= 0) return false;
            //if ($objid <  0) return false;

            // check obtype is legit..
            $objtype = (int)$objtype;
            if (!isset($objtype) || $objtype == -1 || $this->objTypeKey($objtype) === -1) return false;
        
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

            #} owner
            if (!empty($owner) && $owner > 0) $wheres['zbs_owner'] = array('zbs_owner','=','%d',$owner);

            #} in Array of id's
            // this is very brutal, at scale it may well bug out?
            // for now we'll see how much it's used.
            if (is_array($inArr) && count($inArr) > 0){

                $wheres['zbsl_objid'] = array('IN','('.implode(',', $inArr).')','%s');

            }

            #} olderThan
            if (!empty($olderThan) && $olderThan > 0 && $olderThan !== false) $wheres['olderThan'] = array('zbsl_created','<=','%d',$olderThan);
            #} newerThan
            if (!empty($newerThan) && $newerThan > 0 && $newerThan !== false) $wheres['newerThan'] = array('zbsl_created','>=','%d',$newerThan);


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

                    if ($incMeta) $resDataLine->meta = $this->getMeta(array(

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
            'ignoreowner'   => false // this'll let you not-check the owner of obj

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

                    if ($incMeta) $resDataLine->meta = $this->getMeta(array(

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
     * adds or updates a log object
     *
     * @param array $args Associative array of arguments
     *              id (not req.), owner (not req.) data -> key/val
     *
     * @return int line ID
     */
    public function addUpdateLog($args=array()){

        global $ZBSCRM_t,$wpdb;

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
            if (!isset($data['objtype']) || $data['objtype'] == -1 || $this->objTypeKey($data['objtype']) === -1) return false;

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
                                $this->updateMeta(ZBS_TYPE_LOG,$id,'logmeta',$data['meta']);

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
                        $this->updateMeta(ZBS_TYPE_LOG,$newID,'logmeta',$data['meta']);

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

    // =========== / LOGS  ===========================================================
    // ===============================================================================




    // ===============================================================================
    // ===========   EXTERNAL SOURCES  ===============================================
    /**
     * returns full external source line +- details
     *
     * @param int id        tag id
     * @param array $args   Associative array of arguments
     *                      withStats
     *
     * @return array result
     */
    public function getExternalSource($id=-1,$args=array()){

        #} =========== LOAD ARGS ==============
        $defaultArgs = array(

            // Alternative search criteria to ID :)
            // .. LEAVE blank if using ID
            //'contactID'         => -1, // NOTE: This only returns the FIRST source, if using multiple sources, use getExternalSourcesForContact
            'objectID'         => -1, 
            'objectType'       => -1, 
            'source'            => -1, // OPTIONAL, if used with contact ID will return 1 line :D

            // permissions
            'ignoreowner'   => false, // this'll let you not-check the owner of obj

            // returns scalar ID of line
            'onlyID'        => false

        ); foreach ($defaultArgs as $argK => $argV){ $$argK = $argV; if (is_array($args) && isset($args[$argK])) {  if (is_array($args[$argK])){ $newData = $$argK; if (!is_array($newData)) $newData = array(); foreach ($args[$argK] as $subK => $subV){ $newData[$subK] = $subV; }$$argK = $newData;} else { $$argK = $args[$argK]; } } }
        #} =========== / LOAD ARGS =============
        
        #} ========== CHECK FIELDS ============

            $id = (int)$id;
            $objectID = (int)$objectID;
            $objectType = (int)$objectType;

        #} ========= / CHECK FIELDS ===========
        
        #} Check ID or name/type
        if (
            $objectType > 0 && 
                (
                    (!empty($id) && $id > 0)
                    ||
                    (!empty($objectID) && $objectID > 0)
                )
            ){

            global $ZBSCRM_t,$wpdb; 
            $wheres = array('direct'=>array()); $whereStr = ''; $additionalWhere = ''; $params = array(); $res = array();

            #} Build query
            $query = "SELECT * FROM ".$ZBSCRM_t['externalsources'];

            #} ============= WHERE ================

                #} Add ID
                if (!empty($id) && $id > 0) $wheres['ID'] = array('ID','=','%d',$id);

                #} zbss_objid
                if (!empty($objectID) && $objectID > 0) $wheres['zbss_objid'] = array('zbss_objid','=','%d',$objectID);

                #} zbss_objid
                if (!empty($objectType) && $objectType > 0) $wheres['zbss_objtype'] = array('zbss_objtype','=','%d',$objectType);

                #} Source
                if (!empty($source) && $source !== -1) $wheres['zbss_source'] = array('zbss_source','=','%s',$source);

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
                
                    // tidy
                    $res = $this->tidy_externalsource($potentialRes);

                    return $res;

            }

        } // / if ID

        return false;

    }

    /**
     * returns external source detail lines for a contact
     *
     * @param array $args Associative array of arguments
     *              withStats, searchPhrase, sortByField, sortOrder, page, perPage
     *
     * @return array of tag lines
     */
    public function getExternalSourcesForContact($args=array()){

        #} ============ LOAD ARGS =============
        $defaultArgs = array(

            'contactID' => -1,

            'sortByField'   => 'ID',
            'sortOrder'     => 'ASC',
            'page'          => 0,
            'perPage'       => 100,

            // permissions
            'ignoreowner'   => false // this'll let you not-check the owner of obj

        ); foreach ($defaultArgs as $argK => $argV){ $$argK = $argV; if (is_array($args) && isset($args[$argK])) {  if (is_array($args[$argK])){ $newData = $$argK; if (!is_array($newData)) $newData = array(); foreach ($args[$argK] as $subK => $subV){ $newData[$subK] = $subV; }$$argK = $newData;} else { $$argK = $args[$argK]; } } }
        #} =========== / LOAD ARGS =============

        #} ========== CHECK FIELDS ============

            $contactID = (int)$contactID;
        
        #} ========= / CHECK FIELDS ===========

        global $ZBSCRM_t,$wpdb; 
        $wheres = array('direct'=>array()); $whereStr = ''; $additionalWhere = ''; $params = array(); $res = array();

        #} Build query
        $query = "SELECT * FROM ".$ZBSCRM_t['externalsources'];

        #} ============= WHERE ================

            #} contactID
            if (!empty($contactID) && $contactID > 0) $wheres['zbss_objid'] = array('zbss_objid','=','%d',$contactID);
            
            // type
            $wheres['zbss_objtype'] = array('zbss_objtype','=','%d',1);


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
                        
                    // tidy
                    $resArr = $this->tidy_externalsource($resDataLine);

                    $res[] = $resArr;

            }
        }

        return $res;
    } 

     /**
     * adds or updates an external source object
     *
     * @param array $args Associative array of arguments
     *
     * @return int line ID
     */
    public function addUpdateExternalSource($args=array()){

        global $ZBSCRM_t,$wpdb;

        #} ============ LOAD ARGS =============
        $defaultArgs = array(

            'id'            => -1,

            // fields (directly)
            'data'          => array(

                'objectType'   => -1, // enforced 2.97.5+
                'objectID'     => -1,
                'source'        => '',
                'uid'           => '',
                'owner'         => -1

            )

        ); foreach ($defaultArgs as $argK => $argV){ $$argK = $argV; if (is_array($args) && isset($args[$argK])) {  if (is_array($args[$argK])){ $newData = $$argK; if (!is_array($newData)) $newData = array(); foreach ($args[$argK] as $subK => $subV){ $newData[$subK] = $subV; }$$argK = $newData;} else { $$argK = $args[$argK]; } } }
        #} =========== / LOAD ARGS ============

        #} ========== CHECK FIELDS ============


            $id = (int)$id;

            // objectType
            if (!isset($data['objectType']) || $data['objectType'] <= 0) return false;

            // objectID
            if (!isset($data['objectID']) || $data['objectID'] <= 0) return false;

            // if owner = -1, add current
            if (!isset($data['owner']) || $data['owner'] === -1) $data['owner'] = zeroBSCRM_user();

            // check name present + legit
            if (!isset($data['source']) || empty($data['source'])) return false;

            // extsource ID finder - if obj source + cid provided, check not already present (if so overwrite)  
            // keeps unique...  
            if ((empty($id) || $id <= 0)
                && 
                (
                    (isset($data['objectType']) && !empty($data['objectType'])) ||
                    (isset($data['objectID']) && !empty($data['objectID'])) ||
                    (isset($data['source']) && !empty($data['source']))
                )) {

                // check by source + cid
                // check existence + return ID
                $potentialID = (int)$this->getExternalSource(-1,array(
                                'objectType'     => $data['objectType'],
                                'objectID'     => $data['objectID'],
                                'source'        => $data['source'],
                                'onlyID'    => true
                                ));

                // override empty ID 
                if (!empty($potentialID) && $potentialID > 0) $id = $potentialID;

            }


        #} ========= / CHECK FIELDS ===========

        #} Check if ID present
        $id = (int)$id;
        if (!empty($id) && $id > 0){

                #} Check if obj exists (here) - for now just brutal update (will error when doesn't exist)

                #} Attempt update
                if ($wpdb->update( 
                        $ZBSCRM_t['externalsources'], 
                        array( 

                            // ownership
                            // no need to update these (as of yet) - can't move teams etc.
                            //'zbs_site' => zeroBSCRM_installSite(),
                            //'zbs_team' => zeroBSCRM_installTeam(),
                            'zbs_owner' => $data['owner'],

                            // fields
                            'zbss_objid' => $data['objectID'],
                            'zbss_objtype' => $data['objectType'],
                            'zbss_source' => $data['source'],
                            'zbss_uid' => $data['uid'],
                            'zbss_lastupdated' => time()
                        ), 
                        array( // where
                            'ID' => $id
                            ),
                        array( // field data types
                            '%d',
                            '%d',
                            '%d',
                            '%s', 
                            '%s', 
                            '%d'
                        ),
                        array( // where data types
                            '%d'
                            )) !== false){

                            // Successfully updated - Return id
                            return $id;

                        } else {

                            // FAILED update
                            return false;

                        }

        } else {
            
            #} No ID - must be an INSERT
            if ($wpdb->insert( 
                        $ZBSCRM_t['externalsources'], 
                        array( 

                            // ownership
                            'zbs_site' => zeroBSCRM_site(),
                            'zbs_team' => zeroBSCRM_team(),
                            'zbs_owner' => $data['owner'],

                            // fields
                            'zbss_objid' => $data['objectID'],
                            'zbss_objtype' => $data['objectType'],
                            'zbss_source' => $data['source'],
                            'zbss_uid' => $data['uid'],
                            'zbss_created' => time(),
                            'zbss_lastupdated' => time()
                        ), 
                        array( // field data types
                            '%d',  // site
                            '%d',  // team
                            '%d',  // owner

                            '%d',  
                            '%d',  
                            '%s',  
                            '%s',  
                            '%d',  
                            '%d'  
                        ) ) > 0){

                    #} Successfully inserted, lets return new ID
                    $newID = $wpdb->insert_id;
                    return $newID;

                } else {

                    #} Failed to Insert
                    return false;

                }

        }

        return false;

    }

     /**
     * deletes an external source object
     *
     * @param array $args Associative array of arguments
     *              id
     *
     * @return int success;
     */
    public function deleteExternalSource($args=array()){

        global $ZBSCRM_t,$wpdb;

        #} ============ LOAD ARGS =============
        $defaultArgs = array(

            'id'            => -1

        ); foreach ($defaultArgs as $argK => $argV){ $$argK = $argV; if (is_array($args) && isset($args[$argK])) {  if (is_array($args[$argK])){ $newData = $$argK; if (!is_array($newData)) $newData = array(); foreach ($args[$argK] as $subK => $subV){ $newData[$subK] = $subV; }$$argK = $newData;} else { $$argK = $args[$argK]; } } }
        #} =========== / LOAD ARGS ============

        #} Check ID & Delete :)
        $id = (int)$id;
        if (!empty($id) && $id > 0) return zeroBSCRM_db2_deleteGeneric($id,'externalsources');

        return false;

    }

    /**
     * tidy's the object from wp db into clean array
     *
     * @param array $obj (DB obj)
     *
     * @return array (clean obj)
     */
    private function tidy_externalsource($obj=false){

            $res = false;

            if (isset($obj->ID)){
            $res = array();
            $res['id'] = $obj->ID;
            /* 
              `zbs_site` INT NULL DEFAULT NULL,
              `zbs_team` INT NULL DEFAULT NULL,
              `zbs_owner` INT NOT NULL,
            */

            $res['objid'] = $obj->zbss_objid;
            $res['objtype'] = $obj->zbss_objtype;
            $res['source'] = $obj->zbss_source;
            $res['uid'] = $this->stripSlashes($obj->zbss_uid);


            $res['created'] = $obj->zbss_created;
            $res['lastupdated'] = $obj->zbss_lastupdated;

        } 

        return $res;


    }


    // =========== / External Sources      ===========================================
    // ===============================================================================





    // ===============================================================================
    // ===========   Web Tracking (UTM etc.)  ========================================

    /**
     * returns full tracking line +- details
     *
     * @param int id        tag id
     * @param array $args   Associative array of arguments
     *                      withStats
     *
     * @return array result
     */
    public function getTracking($id=-1,$args=array()){

        #} =========== LOAD ARGS ==============
        $defaultArgs = array(

            // permissions
            'ignoreowner'   => false, // this'll let you not-check the owner of obj

            // returns scalar ID of line
            'onlyID'        => false

        ); foreach ($defaultArgs as $argK => $argV){ $$argK = $argV; if (is_array($args) && isset($args[$argK])) {  if (is_array($args[$argK])){ $newData = $$argK; if (!is_array($newData)) $newData = array(); foreach ($args[$argK] as $subK => $subV){ $newData[$subK] = $subV; }$$argK = $newData;} else { $$argK = $args[$argK]; } } }
        #} =========== / LOAD ARGS =============
        
        #} ========== CHECK FIELDS ============

            $id = (int)$id;
        #} ========= / CHECK FIELDS ===========
        
        #} Check ID or name/type
        if (!empty($id) && $id > 0){

            global $ZBSCRM_t,$wpdb; 
            $wheres = array('direct'=>array()); $whereStr = ''; $additionalWhere = ''; $params = array(); $res = array();

            #} Build query
            $query = "SELECT * FROM ".$ZBSCRM_t['tracking'];

            #} ============= WHERE ================

                #} Add ID
                if (!empty($id) && $id > 0) $wheres['ID'] = array('ID','=','%d',$id);

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
                
                    // tidy
                    $res = $this->tidy_tracking($potentialRes);

                    return $res;

            }

        } // / if ID

        return false;

    }

    /**
     * returns tracking detail lines for a contact
     *
     * @param array $args Associative array of arguments
     *              withStats, searchPhrase, sortByField, sortOrder, page, perPage
     *
     * @return array of tag lines
     */
    public function getTrackingForContact($args=array()){

        #} ============ LOAD ARGS =============
        $defaultArgs = array(

            'contactID' => -1,

            // optional
            'action' => '',

            'sortByField'   => 'ID',
            'sortOrder'     => 'ASC',
            'page'          => 0,
            'perPage'       => 100,

            // permissions
            'ignoreowner'   => false // this'll let you not-check the owner of obj

        ); foreach ($defaultArgs as $argK => $argV){ $$argK = $argV; if (is_array($args) && isset($args[$argK])) {  if (is_array($args[$argK])){ $newData = $$argK; if (!is_array($newData)) $newData = array(); foreach ($args[$argK] as $subK => $subV){ $newData[$subK] = $subV; }$$argK = $newData;} else { $$argK = $args[$argK]; } } }
        #} =========== / LOAD ARGS =============

        #} ========== CHECK FIELDS ============

            $contactID = (int)$contactID;
        
        #} ========= / CHECK FIELDS ===========

        global $ZBSCRM_t,$wpdb; 
        $wheres = array('direct'=>array()); $whereStr = ''; $additionalWhere = ''; $params = array(); $res = array();

        #} Build query
        $query = "SELECT * FROM ".$ZBSCRM_t['tracking'];

        #} ============= WHERE ================

            #} contactID
            if (!empty($contactID) && $contactID > 0) $wheres['zbst_contactid'] = array('zbst_contactid','=','%d',$contactID);

            #} action
            if (!empty($action)) $wheres['zbst_action'] = array('zbst_action','=','%s',$action);

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
                        
                    // tidy
                    $resArr = $this->tidy_tracking($resDataLine);

                    $res[] = $resArr;

            }
        }

        return $res;
    } 

     /**
     * adds or updates a tracking object
     *
     * @param array $args Associative array of arguments
     *
     * @return int line ID
     */
    public function addUpdateTracking($args=array()){

        global $ZBSCRM_t,$wpdb;

        #} ============ LOAD ARGS =============
        $defaultArgs = array(

            'id'            => -1,

            // fields (directly)
            'data'          => array(

                'contactID'             => -1,
                'action'                => '',
                'action_detail'         => '',
                'referrer'              => '',
                'utm_source'            => '',
                'utm_medium'            => '',
                'utm_name'              => '',
                'utm_term'              => '',
                'utm_content'           => '',

                'owner'         => -1

            )

        ); foreach ($defaultArgs as $argK => $argV){ $$argK = $argV; if (is_array($args) && isset($args[$argK])) {  if (is_array($args[$argK])){ $newData = $$argK; if (!is_array($newData)) $newData = array(); foreach ($args[$argK] as $subK => $subV){ $newData[$subK] = $subV; }$$argK = $newData;} else { $$argK = $args[$argK]; } } }
        #} =========== / LOAD ARGS ============

        #} ========== CHECK FIELDS ============

            $id = (int)$id;

            // contactID
            if (!isset($data['contactID']) || $data['contactID'] <= 0) return false;

            // if owner = -1, add current
            if (!isset($data['owner']) || $data['owner'] === -1) $data['owner'] = zeroBSCRM_user();

            // check action present + legit
            if (!isset($data['action']) || empty($data['action'])) return false;

        #} ========= / CHECK FIELDS ===========

        #} Check if ID present
        $id = (int)$id;
        if (!empty($id) && $id > 0){

                #} Check if obj exists (here) - for now just brutal update (will error when doesn't exist)

                #} Attempt update
                if ($wpdb->update( 
                        $ZBSCRM_t['tracking'], 
                        array( 

                            // ownership
                            // no need to update these (as of yet) - can't move teams etc.
                            //'zbs_site' => zeroBSCRM_installSite(),
                            //'zbs_team' => zeroBSCRM_installTeam(),
                            'zbs_owner' => $data['owner'],

                            // fields
                            'zbst_contactid' => $data['contactID'],
                            'zbst_action' => $data['action'],
                            'zbst_action_detail' => $data['action_detail'],
                            'zbst_referrer' => $data['referrer'],
                            'zbst_utm_source' => $data['utm_source'],
                            'zbst_utm_medium' => $data['utm_medium'],
                            'zbst_utm_name' => $data['utm_name'],
                            'zbst_utm_term' => $data['utm_term'],
                            'zbst_utm_content' => $data['utm_content'],

                            'zbst_lastupdated' => time()
                        ), 
                        array( // where
                            'ID' => $id
                            ),
                        array( // field data types
                            '%d',

                            '%d',
                            '%s',
                            '%s', 
                            '%s', 
                            '%s', 
                            '%s',
                            '%s', 
                            '%s', 
                            '%s', 

                            '%d'
                        ),
                        array( // where data types
                            '%d'
                            )) !== false){

                            // Successfully updated - Return id
                            return $id;

                        } else {

                            // FAILED update
                            return false;

                        }

        } else {
            
            #} No ID - must be an INSERT
            if ($wpdb->insert( 
                        $ZBSCRM_t['tracking'], 
                        array( 

                            // ownership
                            'zbs_site' => zeroBSCRM_site(),
                            'zbs_team' => zeroBSCRM_team(),
                            'zbs_owner' => $data['owner'],

                            // fields
                            'zbst_contactid' => $data['contactID'],
                            'zbst_action' => $data['action'],
                            'zbst_action_detail' => $data['action_detail'],
                            'zbst_referrer' => $data['referrer'],
                            'zbst_utm_source' => $data['utm_source'],
                            'zbst_utm_medium' => $data['utm_medium'],
                            'zbst_utm_name' => $data['utm_name'],
                            'zbst_utm_term' => $data['utm_term'],
                            'zbst_utm_content' => $data['utm_content'],

                            'zbst_created' => time(),
                            'zbst_lastupdated' => time()
                        ), 
                        array( // field data types
                            '%d',  // site
                            '%d',  // team
                            '%d',  // owner

                            '%d',
                            '%s',
                            '%s', 
                            '%s', 
                            '%s', 
                            '%s',
                            '%s', 
                            '%s', 
                            '%s', 

                            '%d',  
                            '%d'  
                        ) ) > 0){

                    #} Successfully inserted, lets return new ID
                    $newID = $wpdb->insert_id;
                    return $newID;

                } else {

                    #} Failed to Insert
                    return false;

                }

        }

        return false;

    }

     /**
     * deletes a tracking object
     *
     * @param array $args Associative array of arguments
     *              id
     *
     * @return int success;
     */
    public function deleteTracking($args=array()){

        global $ZBSCRM_t,$wpdb;

        #} ============ LOAD ARGS =============
        $defaultArgs = array(

            'id'            => -1

        ); foreach ($defaultArgs as $argK => $argV){ $$argK = $argV; if (is_array($args) && isset($args[$argK])) {  if (is_array($args[$argK])){ $newData = $$argK; if (!is_array($newData)) $newData = array(); foreach ($args[$argK] as $subK => $subV){ $newData[$subK] = $subV; }$$argK = $newData;} else { $$argK = $args[$argK]; } } }
        #} =========== / LOAD ARGS ============

        #} Check ID & Delete :)
        $id = (int)$id;
        if (!empty($id) && $id > 0) return zeroBSCRM_db2_deleteGeneric($id,'tracking');

        return false;

    }

    /**
     * tidy's the object from wp db into clean array
     *
     * @param array $obj (DB obj)
     *
     * @return array (clean obj)
     */
    private function tidy_tracking($obj=false){

            $res = false;

            if (isset($obj->ID)){
            $res = array();
            $res['id'] = $obj->ID;
            /* 
              `zbs_site` INT NULL DEFAULT NULL,
              `zbs_team` INT NULL DEFAULT NULL,
              `zbs_owner` INT NOT NULL,
            */

            $res['contactid'] = $obj->zbss_contactid;
            $res['action'] = $obj->zbst_action;
            $res['action_detail'] = $obj->zbst_action_detail;
            $res['referrer'] = $obj->zbst_referrer;
            $res['utm_source'] = $obj->zbst_utm_source;
            $res['utm_medium'] = $obj->zbst_utm_medium;
            $res['utm_name'] = $obj->zbst_utm_name;
            $res['utm_term'] = $obj->zbst_utm_term;
            $res['utm_content'] = $obj->zbst_utm_content;


            $res['created'] = $obj->zbst_created;
            $res['lastupdated'] = $obj->zbst_lastupdated;

        } 

        return $res;


    }


    // =========== / Web Tracking (UTM etc.)      ====================================
    // ===============================================================================




    // ===============================================================================
    // ===========   Segments      ===================================================
    // This was actually written pre DAL2 and so still has some legacy layout of func 
    // etc. To be slowly refined if needed.

     /**
     * get Sements Pass -1 for $perPage and $page and this'll return ALL
     */
    public function getSegments($ownerID=-1,$perPage=10,$page=0,$withConditions=false,$searchPhrase='',$inArr='',$sortByField='',$sortOrder='DESC'){

                global $ZBSCRM_t,$wpdb;

                $segments = false;

                // build query
                $sql = "SELECT * FROM ".$ZBSCRM_t['segments'];
                $wheres = array();
                $params = array();
                $orderByStr = '';

                    // Owner

                        // escape (all)
                        if ($ownerID != -99){

                            if ($ownerID === -1) $ownerID = get_current_user_id();

                            if (!empty($ownerID)) $wheres['zbs_owner'] = array('=',$ownerID,'%d');

                        }


                    // search phrase
                    if (!empty($searchPhrase)){

                        $wheres['zbsseg_name'] = array('LIKE','%'.$searchPhrase.'%','%s');

                    }

                    // in array
                    if (is_array($inArr) && count($inArr) > 0){

                        $wheres['ID'] = array('IN','('.implode(',', $inArr).')','%s');

                    }

                    // add where's to SQL
                    // + 
                    // feed in params
                    $whereStr = '';
                    if (count($wheres) > 0) foreach ($wheres as $key => $whereArr) {

                        if (!empty($whereStr)) 
                            $whereStr .= ' AND ';
                        else
                            $whereStr .= ' WHERE ';

                        // add in - NOTE: this is TRUSTING key + whereArr[0]
                        $whereStr .= $key.' '.$whereArr[0].' '.$whereArr[2];

                        // feed in params
                        $params[] = $whereArr[1];
                    }

                    // append to sql
                    $sql .= $whereStr;



                    // sort by
                    if (!empty($sortByField)){

                        if (!in_array($sortOrder, array('DESC','ASC'))) $sortOrder = 'DESC';

                        // parametise order field as is unchecked
                        //$orderByStr = ' ORDER BY %s '.$sortOrder;
                        //$params[] = $sortByField;
                        $orderByStr = ' ORDER BY '.$sortByField.' '.$sortOrder;

                    }


                    // pagination
                    if ($page == -1 && $perPage == -1){

                        // NO LIMITS :o


                    } else {

                        // Because SQL USING zero indexed page numbers, we remove -1 here
                        // ... DO NOT change this without seeing usage of the function (e.g. list view) - which'll break
                        $page = (int)$page-1;
                        if ($page < 0) $page = 0;

                        // check params realistic
                        // todo, for now, brute pass
                        $orderByStr .= ' LIMIT '.(int)$page.','.(int)$perPage;

                    }

                    // append to sql
                    $sql .= $orderByStr;

                $query = $this->prepare($sql,$params);

                try {

                    $potentialSegments = $wpdb->get_results( $query, OBJECT );

                } catch (Exception $e){

                    // error with sql :/ for now nothin

                }

                if (isset($potentialSegments) && is_array($potentialSegments)) $segments = $potentialSegments;

                // TIDY
                $res = array();
                if (count($segments) > 0) foreach ($segments as $segment) {
                                
                                // tidy
                                $resArr = $this->tidy_segment($segment);

                                // TO ADD to query / here withConditions
                                // TODO: REFACTOR into query? More efficient?
                                if ($withConditions) $resArr['conditions'] = $this->getSegmentConditions($segment->ID);

                                $res[] = $resArr;

                            }

                return $res;
            
           }

           // brutal simple temp func (should be a wrapper really. segments to tidy up post DAL2 other obj)
           public function getSegmentCount(){

                global $ZBSCRM_t,$wpdb;

                // build query
                $sql = "SELECT COUNT(ID) FROM ".$ZBSCRM_t['segments'];

                return $wpdb->get_var($sql);
            
           }


             /**
             * deletes a Segment object (and its conditions)
             *
             * @param array $args Associative array of arguments
             *              id
             *
             * @return int success;
             */
            public function deleteSegment($args=array()){

                global $ZBSCRM_t,$wpdb;

                #} ============ LOAD ARGS =============
                $defaultArgs = array(

                    'id'            => -1

                ); foreach ($defaultArgs as $argK => $argV){ $$argK = $argV; if (is_array($args) && isset($args[$argK])) {  if (is_array($args[$argK])){ $newData = $$argK; if (!is_array($newData)) $newData = array(); foreach ($args[$argK] as $subK => $subV){ $newData[$subK] = $subV; }$$argK = $newData;} else { $$argK = $args[$argK]; } } }
                #} =========== / LOAD ARGS ============

                #} Check ID & Delete :)
                $id = (int)$id;
                if (!empty($id) && $id > 0) {

                    $deleted = zeroBSCRM_db2_deleteGeneric($id,'segments');

                        // delete segment conditions?
                        // check $deleted?

                        return $wpdb->delete( 
                                    $ZBSCRM_t['segmentsconditions'], 
                                    array( // where
                                        'zbscondition_segmentid' => $id
                                        ),
                                    array(
                                        '%d'
                                        )
                                    );

                }

                return false;

            }

             /**
             * tidys a segment
             */
            public function tidy_segment($obj=false){

                $res = false;


                if (isset($obj->ID)){
                    $res = array();
                    $res['id'] = $obj->ID;
                    
                    $res['name'] = $obj->zbsseg_name;
                    $res['slug'] = $obj->zbsseg_slug;
                    $res['matchtype'] = $obj->zbsseg_matchtype;

                    $res['created'] = $obj->zbsseg_created;
                    $res['lastupdated'] = $obj->zbsseg_lastupdated;
                    $res['compilecount'] = $obj->zbsseg_compilecount;
                    $res['lastcompiled'] = $obj->zbsseg_lastcompiled;

                    // pretty date outputs for list viw
                    $res['createddate'] = zeroBSCRM_locale_utsToDate($obj->zbsseg_created);
                    $res['lastcompileddate'] = zeroBSCRM_locale_utsToDate($obj->zbsseg_lastcompiled);
                } 

                return $res;

           }

             /**
             * tidys a segment condition
             */
            public function tidy_segment_condition($obj=false){

                $res = false;

                if (isset($obj->ID)){
                    $res = array();
                    $res['id'] = $obj->ID;
                    
                    $res['segmentID'] = $obj->zbscondition_segmentid;
                    $res['type'] = $obj->zbscondition_type;
                    $res['operator'] = $obj->zbscondition_op;
                    $res['value'] = $obj->zbscondition_val;
                    $res['value2'] = $obj->zbscondition_val_secondary;

                    // applies any necessary conversions e.g. uts -> date
                    $res['valueconv'] = zeroBSCRM_segments_typeConversions($res['value'],$res['type'],$res['operator'],'out');
                    $res['value2conv'] = zeroBSCRM_segments_typeConversions($res['value2'],$res['type'],$res['operator'],'out');

                } 

                return $res;

           }

           
           /**
             * This is designed to mimic zeroBS_getSegments, but only to return a total count :) 
             */
            public function getSegmentsCountIncParams($ownerID=-1,$perPage=10,$page=0,$withConditions=false,$searchPhrase='',$inArr='',$sortByField='',$sortOrder='DESC'){

                global $ZBSCRM_t,$wpdb;

                $segmentCount = false;

                // build query
                $sql = "SELECT COUNT(ID) segcount FROM ".$ZBSCRM_t['segments'];
                $wheres = array();
                $params = array();
                $orderByStr = '';

                    // Owner

                        // escape (all)
                        if ($ownerID != -99){

                            if ($ownerID === -1) $ownerID = get_current_user_id();

                            if (!empty($ownerID)) $wheres['zbs_owner'] = array('=',$ownerID,'%d');

                        }


                    // search phrase
                    if (!empty($searchPhrase)){

                        $wheres['zbsseg_name'] = array('LIKE',$searchPhrase,'%s');

                    }

                    // in array
                    if (is_array($inArr) && count($inArr) > 0){

                        $wheres['ID'] = array('IN','('.implode(',', $inArr).')','%s');

                    }

                    // add where's to SQL
                    // + 
                    // feed in params
                    $whereStr = '';
                    if (count($wheres) > 0) foreach ($wheres as $key => $whereArr) {

                        if (!empty($whereStr)) 
                            $whereStr .= ' AND ';
                        else
                            $whereStr .= ' WHERE ';

                        // add in - NOTE: this is TRUSTING key + whereArr[0]
                        $whereStr .= $key.' '.$whereArr[0].' '.$whereArr[2];

                        // feed in params
                        $params[] = $whereArr[1];
                    }

                    // append to sql
                    $sql .= $whereStr;


                    /* Doesn't need this for counting total

                    // sort by
                    if (!empty($sortByField)){

                        if (!in_array($sortOrder, array('DESC','ASC'))) $sortOrder = 'DESC';

                        // parametise order field as is unchecked
                        $orderByStr = 'ORDER BY %s '.$sortOrder;
                        $params[] = $sortByField;

                    }


                    // pagination
                    if ($page == -1 && $perPage == -1){

                        // NO LIMITS :o


                    } else {

                        // check params realistic
                        // todo, for now, brute pass

                        if (!empty($orderByStr)) $orderByStr .= ' ';
                        $orderByStr .= 'LIMIT '.(int)$page.','.(int)$perPage;

                    }

                    // append to sql
                    $sql .= $orderByStr;

                    */

                $query = $this->prepare($sql,$params);

                try {

                    $potentialSegmentCount = $wpdb->get_row( $query, OBJECT );

                } catch (Exception $e){

                    // error with sql :/ for now nothin

                }

                if (isset($potentialSegmentCount) && isset($potentialSegmentCount->segcount)) $segmentCount = $potentialSegmentCount->segcount;

                return $segmentCount;
            
           }


           /**
             * builds a preview (top 5 + count) of a set of conditions which could be against a segment
             * expects a filtered list of conditions (e.g. zeroBSCRM_segments_filterConditions if sent through POST)
             */
            public function previewSegment($conditions=array(),$matchType='all',$countOnly=false){

                    // here we zeroBSCRM_textExpose because all will have had textProcess inbound.
                    // probs needs another layer abstraction above this?
                    $conditions = zeroBSCRM_segments_unencodeConditions($conditions);

                    // retrieve getContacts arguments from a list of segment conditions
                    $contactGetArgs = $this->segmentConditionsToArgs($conditions,$matchType);

                    // add top 5 + count params
                    $contactGetArgs['sortByField'] = 'ID';
                    $contactGetArgs['sortOrder'] = 'DESC';
                    $contactGetArgs['page'] = 0;
                    $contactGetArgs['perPage'] = 5;
                    $contactGetArgs['ignoreowner'] = zeroBSCRM_DAL2_ignoreOwnership(ZBS_TYPE_CONTACT);

                    // count ver
                    $countContactGetArgs = $contactGetArgs;
                    $countContactGetArgs['perPage'] = 100000;
                    $countContactGetArgs['count'] = true;

                    // count only
                    if ($countOnly) return $this->getContacts($countContactGetArgs);

                    // Retrieve
                    return array(
                        // DEBUG 
                        //'conditions' => $conditions, // TEMP - remove this
                        //'args' => $contactGetArgs, // TEMP - remove this
                        'count'=>$this->getContacts($countContactGetArgs),
                        'list'=>$this->getContacts($contactGetArgs)
                    );

           }


           /**
             * used by previewSegment and getSegmentAudience to build condition args
             */
           private function segmentConditionsToArgs($conditions=array(),$matchType='all'){

                    if (is_array($conditions) && count($conditions) > 0){

                            $contactGetArgs = array();
                            $conditionIndx = 0; // this allows multiple queries for SAME field (e.g. status = x or status = y)

                            // cycle through & add to contact request arr
                            foreach ($conditions as $condition){

                                $newArgs = $this->segmentConditionArgs($condition,$conditionIndx); $additionalWHERE = false;

                                // legit? merge (must be recursive)
                                if (is_array($newArgs)) $contactGetArgs = array_merge_recursive($contactGetArgs,$newArgs);

                                $conditionIndx++;

                            }

                            // match type ALL is default, this switches to ANY
                            if ($matchType == 'one') $contactGetArgs['whereCase'] = 'OR';

                            return $contactGetArgs;
                        }

                    return array();

           }
           
           /**
             * get a segment (header line)
             */
            public function getSegmentBySlug($segmentSlug=-1,$withConditions=false,$checkOwnershipID=false){

                if (!empty($segmentSlug)){
            
                    global $ZBSCRM_t,$wpdb;

                    $additionalWHERE = ''; $queryVars = array($segmentSlug);

                    // check ownership
                    // THIS ShoULD BE STANDARDISED THROUGHOUT DAL (ON DB2)
                        // $checkOwnershipID = ID = check against that ID
                        // $checkOwnershipID = true = check against get_current_user_id
                        // $checkOwnershipID = false = do not check
                    
                    if ($checkOwnershipID === true){

                        $segmentOwner = get_current_user_id();

                    } else if ($checkOwnershipID > 0){

                        $segmentOwner = (int)$checkOwnershipID;

                    } // else is false, don't test

                    if (isset($segmentOwner)){

                        // add check
                        $additionalWHERE = 'AND zbs_owner = %d';
                        $queryVars[] = $segmentOwner;

                    }
                    

                    $potentialSegment = $wpdb->get_row( $this->prepare("SELECT * FROM ".$ZBSCRM_t['segments']." WHERE zbsseg_slug = %s ".$additionalWHERE."ORDER BY ID ASC LIMIT 0,1",$queryVars), OBJECT );

                    if (isset($potentialSegment) && isset($potentialSegment->ID)){

                        #} Retrieved :) fill + return
                        
                            // tidy
                            $segment = $this->tidy_segment($potentialSegment);

                            if ($withConditions) {

                                $segment['conditions'] = $this->getSegmentConditions($segment['id']);

                            }


                        return $segment;
                    }

                }

                return false;

            }
           
           /**
             * get a segment (header line)
             */
            public function getSegment($segmentID=-1,$withConditions=false,$checkOwnershipID=false){

                if ($segmentID > 0){
            
                    global $ZBSCRM_t,$wpdb;

                    $additionalWHERE = ''; $queryVars = array($segmentID);

                    // check ownership
                    // THIS ShoULD BE STANDARDISED THROUGHOUT DAL (ON DB2)
                        // $checkOwnershipID = ID = check against that ID
                        // $checkOwnershipID = true = check against get_current_user_id
                        // $checkOwnershipID = false = do not check
                    
                    if ($checkOwnershipID === true){

                        $segmentOwner = get_current_user_id();

                    } else if ($checkOwnershipID > 0){

                        $segmentOwner = (int)$checkOwnershipID;

                    } // else is false, don't test

                    if (isset($segmentOwner)){

                        // add check
                        $additionalWHERE = 'AND zbs_owner = %d';
                        $queryVars[] = $segmentOwner;

                    }
                    

                    $potentialSegment = $wpdb->get_row( $this->prepare("SELECT * FROM ".$ZBSCRM_t['segments']." WHERE ID = %d ".$additionalWHERE."ORDER BY ID ASC LIMIT 0,1",$queryVars), OBJECT );

                    if (isset($potentialSegment) && isset($potentialSegment->ID)){

                        #} Retrieved :) fill + return
                        
                            // tidy
                            $segment = $this->tidy_segment($potentialSegment);

                            if ($withConditions) {

                                $segment['conditions'] = $this->getSegmentConditions($segment['id']);

                            }


                        return $segment;
                    }

                }

                return false;
            
           }

           /**
             * Runs a filtered search on customers based on a segment's condition
             * returns array or count ($onlyCount)
             */
            public function getSegementAudience($segmentID=-1,$page=0,$perPage=20,$sortByField='ID',$sortOrder='DESC',$onlyCount=false,$withDND=false){

                // assumes sensible paging + sort vars... no checking of them

                if ($segmentID > 0){

                    #} Retrieve segment + conditions
                    $segment = $this->getSegment($segmentID,true);
                    $conditions = array(); if (isset($segment['conditions'])) $conditions = $segment['conditions'];
                    $matchType = 'all'; if (isset($segment['matchtype'])) $matchType = $segment['matchtype'];

                    // here we zeroBSCRM_textExpose because all will have had textProcess inbound.
                    // probs needs another layer abstraction above this?
                    $conditions = zeroBSCRM_segments_unencodeConditions($conditions);

                    // retrieve getContacts arguments from a list of segment conditions
                    $contactGetArgs = $this->segmentConditionsToArgs($conditions,$matchType);

                        // needs to be ownerless for now
                        $contactGetArgs['ignoreowner'] = zeroBSCRM_DAL2_ignoreOwnership(ZBS_TYPE_CONTACT);

                        // add paging params
                        $contactGetArgs['sortByField'] = $sortByField;
                        $contactGetArgs['sortOrder'] = $sortOrder;
                        $contactGetArgs['page'] = $page;
                        if ($perPage !== -1)
                            $contactGetArgs['perPage'] = $perPage; // over 100k? :o
                        else { 
                            // no limits
                            $contactGetArgs['page'] = -1;
                            $contactGetArgs['perPage'] = -1;
                        }

                        // count ver
                        if ($onlyCount){
                            $contactGetArgs = $contactGetArgs;
                            $contactGetArgs['page'] = -1;
                            $contactGetArgs['perPage'] = -1;
                            $contactGetArgs['count'] = true;

                            $count = $this->getContacts($contactGetArgs);

                                // effectively a compile, so update compiled no on record
                                $this->updateSegmentCompiled($segmentID,$count,time());

                            return $count;
                        }

                        // got dnd?
                        if ($withDND) $contactGetArgs['withDND'] = true;

                        $contacts = $this->getContacts($contactGetArgs);

                        // if no limits, update compile record (effectively a compile)
                        if ($contactGetArgs['page'] == -1 && $contactGetArgs['perPage'] == -1){

                            $this->updateSegmentCompiled($segmentID,count($contacts),time());

                        }
           
                        // Retrieve
                        return $contacts;

                }

                return false;

           }

           /**
             * checks all segments against a contact
             */
            public function getSegmentsContainingContact($contactID=-1,$justIDs=false){

                $ret = array();

                if ($contactID > 0){

                    // get all segments
                    $segments = $this->getSegments(-1,1000,0,true);

                    if (count($segments) > 0) foreach ($segments as $segment){

                        // pass obj to check (saves it querying)
                        if ($this->isContactInSegment($contactID, $segment['id'],$segment)){

                            // is in segment
                            if ($justIDs)
                                $ret[] = $segment['id'];
                            else
                                $ret[] = $segment;

                        }

                    } // foreach segment

                } // if contact id

                return $ret;

           }

           /**
             * Checks if a contact matches segment conditions
             * ... can pass $segmentObj to avoid queries (performance) if already have it
             */
            public function isContactInSegment($contactID=-1,$segmentID=-1,$segmentObj=false){

                if ($segmentID > 0 && $contactID > 0){

                    #} Retrieve segment + conditions
                    if (is_array($segmentObj)) 
                        $segment = $segmentObj;
                    else
                        $segment = $this->getSegment($segmentID,true);

                    #} Set these
                    $conditions = array(); if (isset($segment['conditions'])) $conditions = $segment['conditions'];
                    $matchType = 'all'; if (isset($segment['matchtype'])) $matchType = $segment['matchtype'];

                    // here we zeroBSCRM_textExpose because all will have had textProcess inbound.
                    // probs needs another layer abstraction above this?
                    $conditions = zeroBSCRM_segments_unencodeConditions($conditions);

                    // retrieve getContacts arguments from a list of segment conditions
                    $contactGetArgs = $this->segmentConditionsToArgs($conditions,$matchType);

                        // add paging params
                        $contactGetArgs['page'] = -1;
                        $contactGetArgs['perPage'] = -1;
                        $contactGetArgs['count'] = true;

                        // add id check (via rough additionalWhere)
                        if (!isset($contactGetArgs['additionalWhereArr'])) $contactGetArgs['additionalWhereArr'] = array();
                        $contactGetArgs['additionalWhereArr']['idCheck'] = array("ID",'=','%d',$contactID);

                        // should only ever be 1 or 0
                        $count = $this->getContacts($contactGetArgs);

                        if ($count == 1) 
                            return true;

                        // nope.
                        return false;

                }

                return false;

           }

           /**
             * Compiles any segments which are affected on a single contact change
             * includeSegments is an array of id's - this allows you to pass 'what contact was in before' (because these need --1)
             */
            public function compileSegmentsAffectedByContact($contactID=-1,$includeSegments=array()){

                if ($contactID > 0){

                    // get all segments
                    $segments = $this->getSegments(-1,1000,0,true);

                    if (count($segments) > 0) foreach ($segments as $segment){

                        // pass obj to check (saves it querying)
                        if ($this->isContactInSegment($contactID, $segment['id'],$segment) || in_array($segment['id'], $includeSegments)){

                            // is in segment

                            // compile this segment
                            $this->compileSegment($segment['id']);

                        }

                    } // foreach segment

                } // if contact id

                return false;

           }


           
           /**
             * 
             */
            public function getSegmentConditions($segmentID=-1){

                if ($segmentID > 0){

                    global $ZBSCRM_t,$wpdb;

                    $potentialSegmentConditions = $wpdb->get_results( $this->prepare("SELECT * FROM ".$ZBSCRM_t['segmentsconditions']." WHERE zbscondition_segmentid = %d",$segmentID) );

                    if (is_array($potentialSegmentConditions) && count($potentialSegmentConditions) > 0) {

                        $returnConditions = array();

                        foreach ($potentialSegmentConditions as $condition){

                            $returnConditions[] = $this->tidy_segment_condition($condition);

                        }


                        return $returnConditions;

                    }
                    

                }

                return false;
            
           }


           /**
             * Simple func to update the segment compiled count (says how many contacts currently in segment)
             */
           public function updateSegmentCompiled($segmentID=-1,$segmentCount=0,$compiledUTS=-1){
                
                global $ZBSCRM_t,$wpdb;

                if ($segmentID > 0){

                    // checks
                    $count = 0; if ($segmentCount > 0) $count = (int)$segmentCount;
                    $compiled = time(); if ($compiledUTS > 0) $compiled = (int)$compiledUTS;

                    if ($wpdb->update( 
                            $ZBSCRM_t['segments'], 
                            array( 
                                'zbsseg_compilecount' => $count,
                                'zbsseg_lastcompiled' => $compiled
                            ), 
                            array( // where
                                'ID' => $segmentID
                                ),
                            array( 
                                '%d', 
                                '%d'
                            ),
                            array(
                                '%d'
                                )
                            ) !== false){

                            // udpdated
                            return true;

                        } else {

                            // could not update?!
                            return false;

                        }


                }

           }

           /**
             * 
             */
            public function addUpdateSegment($segmentID=-1,$segmentOwner=-1,$segmentName='',$segmentConditions=array(),$segmentMatchType='all',$forceCompile=false){

                global $ZBSCRM_t,$wpdb;

                #} After ops, shall I compile audience?
                $toCompile = $forceCompile;

                if ($segmentID > 0){

                    #} Update a segment

                        #} Owner - if -1 then use current user
                        if ($segmentOwner <= 0) $segmentOwner = get_current_user_id();

                        #} Empty name = untitled
                        if (empty($segmentName)) $segmentName = __('Untitled Segment',"zero-bs-crm");

                        // slug auto-updates with name, (fix later if issue)
                        // in fact, just leave as whatever first set? (affects quickfilter URLs etc?)
                        // just did in end
                        #} Generate slug
                        $segmentSlug = $this->makeSlug($segmentName);

                        #} update header line
                        if ($wpdb->update( 
                            $ZBSCRM_t['segments'], 
                            array( 
                                'zbs_owner' => $segmentOwner,
                                'zbsseg_name' => $segmentName,
                                'zbsseg_slug' => $segmentSlug,
                                'zbsseg_matchtype' => $segmentMatchType,
                                'zbsseg_lastupdated' => time()
                            ), 
                            array( // where
                                'ID' => $segmentID
                                ),
                            array( 
                                '%d', 
                                '%s',
                                '%s',
                                '%s',
                                '%d'
                            ),
                            array(
                                '%d'
                                )
                            ) !== false){

                            // updated, move on..

                            // add segment conditions
                            $this->addUpdateSegmentConditions($segmentID,$segmentConditions);

                            // return id
                            $returnID = $segmentID;

                            // force to compile
                            $toCompile = true; $compileID = $segmentID;

                        } else {

                            // could not update?!
                            return false;

                        }
                    

                } else {

                    #} Add a new segment

                        #} Owner - if -1 then use current user
                        if ($segmentOwner <= 0) $segmentOwner = get_current_user_id();

                        #} Empty name = untitled (should never happen because of UI)
                        if (empty($segmentName)) $segmentName = __('Untitled Segment',"zero-bs-crm");

                        #} Generate slug
                        $segmentSlug = $this->makeSlug($segmentName);

                        #} Add header line
                        if ($wpdb->insert( 
                            $ZBSCRM_t['segments'], 
                            array( 
                                'zbs_owner' => $segmentOwner,
                                'zbsseg_name' => $segmentName,
                                'zbsseg_slug' => $segmentSlug,
                                'zbsseg_matchtype' => $segmentMatchType,
                                'zbsseg_created' => time(),
                                'zbsseg_lastupdated' => time(),
                                'zbsseg_lastcompiled' => time(), // we'll compile it shortly, set as now :)
                            ), 
                            array( 
                                '%d', 
                                '%s',
                                '%s',
                                '%s',
                                '%d',
                                '%d',
                                '%d'
                            ) 
                        ) > 0){

                            // inserted, let's move on
                            $newSegmentID = $wpdb->insert_id;

                            // add segment conditions
                            $this->addUpdateSegmentConditions($newSegmentID,$segmentConditions);

                            // force to compile
                            $toCompile = true; $compileID = $newSegmentID;

                            // return id
                            $returnID = $newSegmentID;

                        } else {

                            // could not insert?!
                            return false;

                        }

                } // / new

                // "compile" segments?
                if ($toCompile && !empty($compileID)){

                    // compiles + logs how many in segment against record
                    $totalInSegment = $this->compileSegment($compileID);

                }

                if (isset($returnID))
                    return $returnID;
                else
                    return false;
            
           }


           public function addUpdateSegmentConditions($segmentID=-1,$conditions=array()){

                if ($segmentID > 0 && is_array($conditions)){

                    // lazy - here I NUKE all existing conditions then readd...
                    $this->removeSegmentConditions($segmentID);

                        if (is_array($conditions) && count($conditions) > 0){

                            $retConditions = array();

                            foreach ($conditions as $sCondition){


                                $newConditionID = $this->addUpdateSegmentCondition(-1,$segmentID,$sCondition);

                                if (!empty($newConditionID)){

                                    // new condition added, insert
                                    $retConditions[$newConditionID] = $sCondition;

                                } else {

                                    // error inserting condition?!
                                    return false;

                                }

                            }

                            return $retConditions;

                        }


                } 

                return array();

           }

           /**
             * 
             */
            public function addUpdateSegmentCondition($conditionID=-1,$segmentID=-1,$conditionDetails=array()){

                global $ZBSCRM_t,$wpdb;

                #} Check/build empty condition details
                $condition = array(
                    'type' => '',
                    'operator' => '',
                    'val' => '',
                    'valsecondary' => ''
                );
                if (isset($conditionDetails['type'])) $condition['type'] = $conditionDetails['type'];
                if (isset($conditionDetails['value'])) $condition['val'] = $conditionDetails['value'];
                if (isset($conditionDetails['operator']) && $conditionDetails['operator'] !== -1) $condition['operator'] = $conditionDetails['operator'];
                if (isset($conditionDetails['value2'])) $condition['valsecondary'] = $conditionDetails['value2'];

                // update or insert?
                if ($conditionID > 0){

                    #} Update a segment condition

                        #} update line
                        if ($wpdb->update( 
                            $ZBSCRM_t['segmentsconditions'], 
                            array( 
                                'zbscondition_segmentid' => $segmentID,
                                'zbscondition_type' => $condition['type'],
                                'zbscondition_op' => $condition['operator'],
                                'zbscondition_val' => $condition['val'],
                                'zbscondition_val_secondary' => $condition['valsecondary']
                            ), 
                            array( // where
                                'ID' => $conditionID
                                ),
                            array( 
                                '%d', 
                                '%s',
                                '%s',
                                '%s',
                                '%s'
                            ),
                            array(
                                '%d'
                                )
                            ) !== false){

                            return $conditionID;

                        } else {

                            // could not update?!
                            return false;

                        }
                    

                } else {

                    #} Add a new segmentcondition


                        #} Add condition line
                        if ($wpdb->insert( 
                            $ZBSCRM_t['segmentsconditions'], 
                            array( 
                                'zbscondition_segmentid' => $segmentID,
                                'zbscondition_type' => $condition['type'],
                                'zbscondition_op' => $condition['operator'],
                                'zbscondition_val' => $condition['val'],
                                'zbscondition_val_secondary' => $condition['valsecondary']
                            ), 
                            array( 
                                '%d', 
                                '%s',
                                '%s',
                                '%s',
                                '%s'
                            ) 
                        ) > 0){


                            // inserted
                            return $wpdb->insert_id;

                        } else {

                            // could not insert?!
                            return false;

                        }

                } // / new

                return false;

            
           }

           /**
             *  empty all conditions against seg
             */
            public function removeSegmentConditions($segmentID=-1){

                if (!empty($segmentID)) {

                    global $ZBSCRM_t,$wpdb;

                    return $wpdb->delete( 
                                $ZBSCRM_t['segmentsconditions'], 
                                array( // where
                                    'zbscondition_segmentid' => $segmentID
                                    ),
                                array(
                                    '%d'
                                    )
                                );

                }

                return false;
            
           }



           /**
             * Segment rules
             *  takes a condition + returns a contact dal2 get arr param
             */
            public function segmentConditionArgs($condition=array(),$conditionKeySuffix=''){

                if (is_array($condition) && isset($condition['type']) && isset($condition['operator'])){

                    global $zbs,$wpdb,$ZBSCRM_t;

                    switch ($condition['type']){

                        case 'status':

                        /* while this is right, it doesn't allow for MULTIPLE status cond lines, so do via sql:
                            if ($condition['operator'] == 'equal')
                                return array('hasStatus'=>$condition['value']);
                            else
                                return array('otherStatus'=>$condition['value']);
                        */
                            if ($condition['operator'] == 'equal')
                                return array('additionalWhereArr'=>
                                            array('statusEqual'.$conditionKeySuffix=>array("zbsc_status",'=','%s',$condition['value']))
                                        );
                            else
                                return array('additionalWhereArr'=>
                                            array('statusEqual'.$conditionKeySuffix=>array("zbsc_status",'<>','%s',$condition['value']))
                                        );

                            break;

                        case 'fullname': // 'equal','notequal','contains'

                            if ($condition['operator'] == 'equal')
                                return array('additionalWhereArr'=>
                                            array('fullnameEqual'.$conditionKeySuffix=>array("CONCAT(zbsc_fname,' ',zbsc_lname)",'=','%s',$condition['value']))
                                        );
                            else if ($condition['operator'] == 'notequal')
                                return array('additionalWhereArr'=>
                                            array('fullnameEqual'.$conditionKeySuffix=>array("CONCAT(zbsc_fname,' ',zbsc_lname)",'<>','%s',$condition['value']))
                                        );
                            else if ($condition['operator'] == 'contains')
                                return array('additionalWhereArr'=>
                                            array('fullnameEqual'.$conditionKeySuffix=>array("CONCAT(zbsc_fname,' ',zbsc_lname)",'LIKE','%s','%'.$condition['value'].'%'))
                                        );
                            break;

                        case 'email': // 'equal','notequal','contains'

                            if ($condition['operator'] == 'equal'){
                                // while this is right, it doesn't allow for MULTIPLE status cond lines, so do via sql:
                                // return array('hasEmail'=>$condition['value']);
                                /* // this was good, but was effectively AND
                                return array('additionalWhereArr'=>
                                            array(
                                                'email'.$conditionKeySuffix=>array('zbsc_email','=','%s',$condition['value']),
                                                'emailAKA'.$conditionKeySuffix=>array('ID','IN',"(SELECT aka_id FROM ".$ZBSCRM_t['aka']." WHERE aka_type = ".zeroBS_getAKAType('customer')." AND aka_alias = %s)",$condition['value'])
                                                )
                                        );
                                */
                                // This was required to work with OR (e.g. postcode 1 = x or postcode 2 = x)
                                // -----------------------
                                // This generates a query like 'zbsc_fname LIKE %s OR zbsc_lname LIKE %s', 
                                // which we then need to include as direct subquery
                                /* THIS WORKS: but refactored below
                                $conditionQArr = $this->buildWheres(array(
                                                                    'email'.$conditionKeySuffix=>array('zbsc_email','=','%s',$condition['value']),
                                                                    'emailAKA'.$conditionKeySuffix=>array('ID','IN',"(SELECT aka_id FROM ".$ZBSCRM_t['aka']." WHERE aka_type = ".zeroBS_getAKAType('customer')." AND aka_alias = %s)",$condition['value'])
                                                                    ),'',array(),'OR',false);

                                if (is_array($conditionQArr) && isset($conditionQArr['where']) && !empty($conditionQArr['where'])){                                    
                                    return array('additionalWhereArr'=>array('direct'=>array(array('('.$conditionQArr['where'].')',$conditionQArr['params']))));
                                }
                                return array();
                                */
                                // this way for OR situations
                                return $this->segmentBuildDirectOrClause(array(
                                                                    'email'.$conditionKeySuffix=>array('zbsc_email','=','%s',$condition['value']),
                                                                    'emailAKA'.$conditionKeySuffix=>array('ID','IN',"(SELECT aka_id FROM ".$ZBSCRM_t['aka']." WHERE aka_type = ".zeroBS_getAKAType('customer')." AND aka_alias = %s)",$condition['value'])
                                                                    ),'OR');
                                // -----------------------
                            } else if ($condition['operator'] == 'notequal')
                                return array('additionalWhereArr'=>
                                            array(
                                                'notEmail'.$conditionKeySuffix=>array('zbsc_email','<>','%s',$condition['value']),
                                                'notEmailAka'.$conditionKeySuffix=>array('ID','NOT IN',"(SELECT aka_id FROM ".$ZBSCRM_t['aka']." WHERE aka_type = ".zeroBS_getAKAType('customer')." AND aka_alias = %s)",$condition['value'])
                                                )
                                        );
                            else if ($condition['operator'] == 'contains')
                                return array('additionalWhereArr'=>
                                            array('emailContains'.$conditionKeySuffix=>array("zbsc_email",'LIKE','%s','%'.$condition['value'].'%'))
                                        );
                            break;




                        // TBA (When DAL2 trans etc.)
                        case 'totalval': // 'equal','notequal','larger','less','floatrange'

                            break;

                        case 'dateadded': // 'before','after','daterange'

                            // contactedAfter
                            if ($condition['operator'] == 'before')
                                // while this is right, it doesn't allow for MULTIPLE status cond lines, so do via sql:
                                // return array('olderThan'=>$condition['value']);
                                return array('additionalWhereArr'=>
                                            array('olderThan'.$conditionKeySuffix=>array('zbsc_created','<=','%d',$condition['value']))
                                        );
                            else if ($condition['operator'] == 'after')
                                // while this is right, it doesn't allow for MULTIPLE status cond lines, so do via sql:
                                // return array('newerThan'=>$condition['value']);
                                return array('additionalWhereArr'=>
                                            array('newerThan'.$conditionKeySuffix=>array('zbsc_created','>=','%d',$condition['value']))
                                        );
                            else if ($condition['operator'] == 'daterange'){

                                $before = false; $after = false;
                                // split out the value 
                                if (isset($condition['value']) && !empty($condition['value'])) $after = (int)$condition['value'];
                                if (isset($condition['value2']) && !empty($condition['value2'])) $before = (int)$condition['value2'];

                                // while this is right, it doesn't allow for MULTIPLE status cond lines, so do via sql:
                                // return array('newerThan'=>$after,'olderThan'=>$before);
                                return array('additionalWhereArr'=>
                                            array(
                                                'newerThan'.$conditionKeySuffix=>array('zbsc_created','>=','%d',$condition['value']),
                                                'olderThan'.$conditionKeySuffix=>array('zbsc_created','<=','%d',$condition['value2'])
                                            )
                                        );

                            }

                            break;

                        case 'datelastcontacted': // 'before','after','daterange'

                            // contactedAfter
                            if ($condition['operator'] == 'before')
                                // while this is right, it doesn't allow for MULTIPLE status cond lines, so do via sql:
                                // return array('contactedBefore'=>$condition['value']);
                                return array('additionalWhereArr'=>
                                            array('contactedBefore'.$conditionKeySuffix=>array('zbsc_lastcontacted','<=','%d',$condition['value']))
                                        );
                            else if ($condition['operator'] == 'after')
                                // while this is right, it doesn't allow for MULTIPLE status cond lines, so do via sql:
                                // return array('contactedAfter'=>$condition['value']);
                                return array('additionalWhereArr'=>
                                            array('contactedAfter'.$conditionKeySuffix=>array('zbsc_lastcontacted','>=','%d',$condition['value']))
                                        );
                            else if ($condition['operator'] == 'daterange'){

                                $before = false; $after = false;
                                // split out the value 
                                if (isset($condition['value']) && !empty($condition['value'])) $after = (int)$condition['value'];
                                if (isset($condition['value2']) && !empty($condition['value2'])) $before = (int)$condition['value2'];

                                // while this is right, it doesn't allow for MULTIPLE status cond lines, so do via sql:
                                // return array('contactedAfter'=>$after,'contactedBefore'=>$before);
                                return array('additionalWhereArr'=>
                                            array(
                                                'contactedAfter'.$conditionKeySuffix=>array('zbsc_lastcontacted','>=','%d',$after),
                                                'contactedBefore'.$conditionKeySuffix=>array('zbsc_lastcontacted','<=','%d',$before)
                                            )
                                        );
                            }

                            break;

                        case 'tagged': // 'tag'

                            // while this is right, it doesn't allow for MULTIPLE status cond lines, so do via sql:
                            // return array('isTagged'=>$condition['value']);
                            // NOTE
                            // ... this is a DIRECT query, so format for adding here is a little diff
                            // ... and only works (not overriding existing ['direct']) because the calling func of this func has to especially copy separately
                            return array('additionalWhereArr'=>
                                            array('direct' => array(
                                                array('(SELECT COUNT(ID) FROM '.$ZBSCRM_t['taglinks'].' WHERE zbstl_objtype = %d AND zbstl_objid = contact.ID AND zbstl_tagid = %d) > 0',array(ZBS_TYPE_CONTACT,$condition['value']))
                                                )
                                            )
                                        );                        

                            break;

                        case 'nottagged': // 'tag'

                            // while this is right, it doesn't allow for MULTIPLE status cond lines, so do via sql:
                            // return array('isNotTagged'=>$condition['value']);

                            // NOTE
                            // ... this is a DIRECT query, so format for adding here is a little diff
                            // ... and only works (not overriding existing ['direct']) because the calling func of this func has to especially copy separately
                            return array('additionalWhereArr'=>
                                            array('direct' => array(
                                                array('(SELECT COUNT(ID) FROM '.$ZBSCRM_t['taglinks'].' WHERE zbstl_objtype = %d AND zbstl_objid = contact.ID AND zbstl_tagid = %d) = 0',array(ZBS_TYPE_CONTACT,$condition['value']))
                                                )
                                            )
                                        ); 
                            break;

                        default:

                            // Allow for custom segmentArgument builders
                            if (!empty($condition['type'])){
                                $filterTag = $this->makeSlug($condition['type']).'_zbsSegmentArgumentBuild';
                                $potentialArgs = apply_filters( $filterTag, false, $condition,$conditionKeySuffix );

                                // got anything back? 
                                if ($potentialArgs !== false) return $potentialArgs;
                            }

                            break;



                    }



                }

                return false;

           }

            // ONLY USED FOR SEGMENT SQL BUILING CURRENTLY, deep.
            // -----------------------
            // This was required to work with OR (e.g. postcode 1 = x or postcode 2 = x)
            // -----------------------
            // This generates a query like 'zbsc_fname LIKE %s OR zbsc_lname LIKE %s', 
            // which we then need to include as direct subquery
            public function segmentBuildDirectOrClause($directQueries=array(),$andOr='OR'){
            /* this works, in segmentConditionArgs(), adapted below to fit generic func to keep it DRY
                $conditionQArr = $this->buildWheres(array(
                                                    'email'.$conditionKeySuffix=>array('zbsc_email','=','%s',$condition['value']),
                                                    'emailAKA'.$conditionKeySuffix=>array('ID','IN',"(SELECT aka_id FROM ".$ZBSCRM_t['aka']." WHERE aka_type = ".zeroBS_getAKAType('customer')." AND aka_alias = %s)",$condition['value'])
                                                    ),'',array(),'OR',false);

                if (is_array($conditionQArr) && isset($conditionQArr['where']) && !empty($conditionQArr['where'])){                                    
                    return array('additionalWhereArr'=>array('direct'=>array(array('('.$conditionQArr['where'].')',$conditionQArr['params']))));
                }
                return array();

            */
                $directArr = $this->buildWheres($directQueries,'',array(),$andOr,false);
                if (is_array($directArr) && isset($directArr['where']) && !empty($directArr['where'])){                                    
                    return array('additionalWhereArr'=>array('direct'=>array(array('('.$directArr['where'].')',$directArr['params']))));
                }
                return array();
            }


           /**
             *  Compile a segment ()
             */
            public function compileSegment($segmentID=-1){

                if (!empty($segmentID)) {

                    // 'GET' the segment count without paging limits
                    // ... this func then automatically updates the compile record, so nothing to do :) 
                    return $this-> getSegementAudience($segmentID,-1,-1,'ID','DESC',true);

                }

                return false;
            
           }


    // =========== / Segments      ===================================================
    // ===============================================================================



    // ===============================================================================
    // ===========   LOGS   ==========================================================

    /**
     * returns cron log lines
     *
     * @param array $args Associative array of arguments
     *              searchPhrase, sortByField, sortOrder, page, perPage
     *
     * @return array of tag lines
     */
    public function getCronLogs($args=array()){

        #} ============ LOAD ARGS =============
        $defaultArgs = array(


            'job'  => '', 


            'sortByField'   => 'ID',
            'sortOrder'     => 'DESC',
            'page'          => 0,
            'perPage'       => 100,

            // permissions
            'ignoreowner'   => false // this'll let you not-check the owner of obj

        ); foreach ($defaultArgs as $argK => $argV){ $$argK = $argV; if (is_array($args) && isset($args[$argK])) {  if (is_array($args[$argK])){ $newData = $$argK; if (!is_array($newData)) $newData = array(); foreach ($args[$argK] as $subK => $subV){ $newData[$subK] = $subV; }$$argK = $newData;} else { $$argK = $args[$argK]; } } }
        #} =========== / LOAD ARGS =============

        #} ========== CHECK FIELDS ============

        
        #} ========= / CHECK FIELDS ===========

        global $ZBSCRM_t,$wpdb; 
        $wheres = array('direct'=>array()); $whereStr = ''; $additionalWhere = ''; $params = array(); $res = array();

        #} Build query
        $query = "SELECT * FROM ".$ZBSCRM_t['cronmanagerlogs'];

        #} ============= WHERE ================

            #} job
            if (!empty($job) && $job > 0) $wheres['job'] = array('job','=','%s',$job);

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
                        
                    // tidy
                    $resArr = $this->tidy_cronlog($resDataLine);

                    $res[] = $resArr;

            }
        }

        return $res;
    } 



     /**
     * adds or updates a cron log object
     *
     * @param array $args Associative array of arguments
     *              id (not req.), owner (not req.) data -> key/val
     *
     * @return int line ID
     */
    public function addUpdateCronLog($args=array()){

        global $ZBSCRM_t,$wpdb;

        #} ============ LOAD ARGS =============
        $defaultArgs = array(

            'id'            => -1,
            'owner'         => -1,

            // fields (directly)
            'data'          => array(

                'job'   => '',
                'jobstatus'     => -1,
                'jobstarted'      => -1,
                'jobfinished' => -1,
                'jobnotes'  => ''
                
            )

        ); foreach ($defaultArgs as $argK => $argV){ $$argK = $argV; if (is_array($args) && isset($args[$argK])) {  if (is_array($args[$argK])){ $newData = $$argK; if (!is_array($newData)) $newData = array(); foreach ($args[$argK] as $subK => $subV){ $newData[$subK] = $subV; }$$argK = $newData;} else { $$argK = $args[$argK]; } } }
        #} =========== / LOAD ARGS ============


        #} ========== CHECK FIELDS ============

            $id = (int)$id;

            // if owner = -1, add current
            if (!isset($owner) || $owner === -1) $owner = zeroBSCRM_user();

        #} ========= / CHECK FIELDS ===========

        $dataArr = array( 

                            // ownership
                            // no need to update these (as of yet) - can't move teams etc.
                            //'zbs_site' => zeroBSCRM_installSite(),
                            //'zbs_team' => zeroBSCRM_installTeam(),
                            'zbs_owner' => $owner,

                            // fields
                            'job' => $data['job'],
                            'jobstatus' => $data['jobstatus'],
                            'jobstarted' => $data['jobstarted'],
                            'jobfinished' => $data['jobfinished'],
                            'jobnotes' => $data['jobnotes']
                        );

        $dataTypes = array( // field data types
                            '%d',

                            '%s',
                            '%d',
                            '%d', 
                            '%d',
                            '%s'
                        );


        if (isset($id) && !empty($id) && $id > 0){

                #} Check if obj exists (here) - for now just brutal update (will error when doesn't exist)

                #} Attempt update
                if ($wpdb->update( 
                        $ZBSCRM_t['cronmanagerlogs'], 
                        $dataArr, 
                        array( // where
                            'ID' => $id
                            ),
                        $dataTypes,
                        array( // where data types
                            '%d'
                            )) !== false){

                            // Successfully updated - Return id
                            return $id;

                        } else {

                            // FAILED update
                            return false;

                        }

        } else {

            // add team etc
            $dataArr['zbs_site'] = zeroBSCRM_site(); $dataTypes[] = '%d';
            $dataArr['zbs_team'] = zeroBSCRM_team(); $dataTypes[] = '%d';
            
            #} No ID - must be an INSERT
            if ($wpdb->insert( 
                        $ZBSCRM_t['cronmanagerlogs'], 
                        $dataArr, 
                        $dataTypes ) > 0){

                    #} Successfully inserted, lets return new ID
                    $newID = $wpdb->insert_id;

                    return $newID;

                } else {

                    #} Failed to Insert
                    return false;

                }

        }

        return false;

    }

     /**
     * deletes a CRON Log object
     * NOTE! this doesn't yet delete any META!
     *
     * @param array $args Associative array of arguments
     *              id
     *
     * @return int success;
     */
    public function deleteCronLog($args=array()){

        global $ZBSCRM_t,$wpdb;

        #} ============ LOAD ARGS =============
        $defaultArgs = array(

            'id'            => -1

        ); foreach ($defaultArgs as $argK => $argV){ $$argK = $argV; if (is_array($args) && isset($args[$argK])) {  if (is_array($args[$argK])){ $newData = $$argK; if (!is_array($newData)) $newData = array(); foreach ($args[$argK] as $subK => $subV){ $newData[$subK] = $subV; }$$argK = $newData;} else { $$argK = $args[$argK]; } } }
        #} =========== / LOAD ARGS ============

        #} Check ID & Delete :)
        $id = (int)$id;
        if (!empty($id) && $id > 0) return zeroBSCRM_db2_deleteGeneric($id,'cronmanagerlogs');

        return false;

    }

    /**
     * tidy's the object from wp db into clean array
     *
     * @param array $obj (DB obj)
     *
     * @return array (clean obj)
     */
    private function tidy_cronlog($obj=false){

            $res = false;

            if (isset($obj->ID)){
            $res = array();
            $res['id'] = $obj->ID;
            $res['owner'] = $obj->zbs_owner;
            
            $res['job'] = $obj->job;
            $res['jobstatus'] = $obj->jobstatus;
            $res['jobstarted'] = $obj->jobstarted;
            $res['jobfinished'] = $obj->jobfinished;
            $res['jobnotes'] = $obj->jobnotes;            

        } 

        return $res;


    }

    // =========== / CRONLOGS  =======================================================
    // ===============================================================================

/* ======================================================
   / DAL CRUD
   ====================================================== */





/* ======================================================
   Formatters
   ====================================================== */

    /**
     * Returns a formatted full name (e.g. Mr. Dave Davids)
     *
     * @param array $obj (tidied db obj)
     *
     * @return string fullname
     */
   private function format_fullname($contactArr=array()){

        $str = '';
        if (isset($contactArr['prefix'])) $str .= $contactArr['prefix'];
        if (isset($contactArr['fname'])) {
            if (!empty($str)) $str .= ' ';
            $str .= $contactArr['fname'];
        }
        if (isset($contactArr['lname'])) {
            if (!empty($str)) $str .= ' ';
            $str .= $contactArr['lname'];
        }

        return $str;
   }

    /**
     * Returns a formatted full name +- id, address (e.g. Mr. Dave Davids 12 London Street #23)
     * Replaces zeroBS_customerName from DAL1 more realistically than format_fullname
     *
     * @param array $obj (tidied db obj)
     *
     * @return string fullname
     */
   private function format_name_etc($contactArr=array(),$args=array()){

        #} =========== LOAD ARGS ==============
        $defaultArgs = array(

            'incFirstLineAddr'  => false,
            'incID'             => false

        ); foreach ($defaultArgs as $argK => $argV){ $$argK = $argV; if (is_array($args) && isset($args[$argK])) {  if (is_array($args[$argK])){ $newData = $$argK; if (!is_array($newData)) $newData = array(); foreach ($args[$argK] as $subK => $subV){ $newData[$subK] = $subV; }$$argK = $newData;} else { $$argK = $args[$argK]; } } }
        #} =========== / LOAD ARGS =============

        // full name first
        $str = $this->format_fullname($contactArr);

        #} Legacy from DAL1: 

            #} First line of addr?
            if ($incFirstLineAddr) if (isset($contactArr['addr1']) && !empty($contactArr['addr1'])) $str .= ' ('.$contactArr['addr1'].')';

            #} ID?
            if ($incID) $str .= ' #'.$contactArr['id'];

        return $str;
   }

    /**
     * Returns a formatted address
     * via getContactFullName this replaces zeroBS_customerAddr in dal1
     *
     * @param array $obj (tidied db obj)
     *
     * @return string address
     */
    private function format_address($contactArr=array(),$args=array()){

        #} =========== LOAD ARGS ==============
        $defaultArgs = array(

            'addrFormat'        => 'short',
            'delimiter'         => ', ', // could use <br>
            'secondaddr'        => false // if true, use second address (if present in contact_arr)

        ); foreach ($defaultArgs as $argK => $argV){ $$argK = $argV; if (is_array($args) && isset($args[$argK])) {  if (is_array($args[$argK])){ $newData = $$argK; if (!is_array($newData)) $newData = array(); foreach ($args[$argK] as $subK => $subV){ $newData[$subK] = $subV; }$$argK = $newData;} else { $$argK = $args[$argK]; } } }
        #} =========== / LOAD ARGS =============

        $ret = ''; $fieldPrefix = ''; if ($secondaddr) $fieldPrefix = 'sec';

        #} Legacy from DAL1: 
        $addrCustomFields = zeroBSCRM_getAddressCustomFields();

            if ($addrFormat == 'short'){

                if (isset($contactArr[$fieldPrefix.'addr1']) && !empty($contactArr[$fieldPrefix.'addr1'])) $ret = $contactArr[$fieldPrefix.'addr1'];
                if (isset($contactArr[$fieldPrefix.'city']) && !empty($contactArr[$fieldPrefix.'city'])) $ret .= $this->delimiterIf($delimiter,$ret).$contactArr[$fieldPrefix.'city'];

            } else if ($addrFormat == 'full'){

                if (isset($contactArr[$fieldPrefix.'addr1']) && !empty($contactArr[$fieldPrefix.'addr1'])) $ret = $contactArr[$fieldPrefix.'addr1'];
                if (isset($contactArr[$fieldPrefix.'addr2']) && !empty($contactArr[$fieldPrefix.'addr2'])) $ret .= $this->delimiterIf($delimiter,$ret).$contactArr[$fieldPrefix.'addr2'];
                if (isset($contactArr[$fieldPrefix.'city']) && !empty($contactArr[$fieldPrefix.'city'])) $ret .= $this->delimiterIf($delimiter,$ret).$contactArr[$fieldPrefix.'city'];
                if (isset($contactArr[$fieldPrefix.'county']) && !empty($contactArr[$fieldPrefix.'county'])) $ret .= $this->delimiterIf($delimiter,$ret).$contactArr[$fieldPrefix.'county'];
                if (isset($contactArr[$fieldPrefix.'postcode']) && !empty($contactArr[$fieldPrefix.'postcode'])) $ret .= $this->delimiterIf($delimiter,$ret).$contactArr[$fieldPrefix.'postcode'];

                // any custom fields here
                if (is_array($addrCustomFields) && count($addrCustomFields) > 0){

                    foreach ($addrCustomFields as $cK => $cF){


                        // hacky temp solution.
                        $cKN = (int)$cK+1;
                        $cKey = $fieldPrefix.'addr_cf'.$cKN;                       

                        if (isset($contactArr[$cKey]) && !empty($contactArr[$cKey])) $ret .= $this->delimiterIf($delimiter,$ret).$contactArr[$cKey];
                    }

                }


            }

        $trimRet = trim($ret);
        return $trimRet;
    }

    /**
     * Returns a slug, generated from a str :)
     * Uses this: https://gist.github.com/james2doyle/9158349
     * In future, perhaps switch to this: https://github.com/ausi/slug-generator
     *
     * @param array $obj (tidied db obj)
     *
     * @return string fullname
     */

    /* This was causing people without iconv to bug out ... so rewrote quickly, need to add in ausi-slug-gen later

    ... rethinking https://github.com/phalcon/incubator/blob/master/Library/Phalcon/Utils/Slug.php
   public function makeSlug($string, $replace = array(), $delimiter = '-') {
      // https://github.com/phalcon/incubator/blob/master/Library/Phalcon/Utils/Slug.php
      if (!extension_loaded('iconv')) {
        throw new Exception('iconv module not loaded');
      }
      // Save the old locale and set the new locale to UTF-8
      $oldLocale = setlocale(LC_ALL, '0');
      setlocale(LC_ALL, 'en_US.UTF-8');
      $clean = iconv('UTF-8', 'ASCII//TRANSLIT', $string);
      if (!empty($replace)) {
        $clean = str_replace((array) $replace, ' ', $clean);
      }
      $clean = preg_replace("/[^a-zA-Z0-9\/_|+ -]/", '', $clean);
      $clean = strtolower($clean);
      $clean = preg_replace("/[\/_|+ -]+/", $delimiter, $clean);
      $clean = trim($clean, $delimiter);
      // Revert back to the old locale
      setlocale(LC_ALL, $oldLocale);
      return $clean;
    }

    */
    public function makeSlug($string, $replace = array(), $delimiter = '-') {

      // https://github.com/phalcon/incubator/blob/master/Library/Phalcon/Utils/Slug.php
        // and
      // https://stackoverflow.com/questions/4910627/php-iconv-translit-for-removing-accents-not-working-as-excepted
      //if (!extension_loaded('iconv')) {
      //  throw new Exception('iconv module not loaded');
      //}
      // Save the old locale and set the new locale to UTF-8
      $oldLocale = setlocale(LC_ALL, '0');
      setlocale(LC_ALL, 'en_US.UTF-8');

      // replace non letter or digits by -
      $clean = preg_replace('#[^\\pL\d]+#u', '-', $string);

      // transliterate
      if (function_exists('iconv')) 
        $clean = @iconv('UTF-8', 'ASCII//TRANSLIT', $clean);
      // else? smt else?

      // replace
      if (!empty($replace)) {
        $clean = str_replace((array) $replace, ' ', $clean);
      }
      
      // clean
      $clean = $this->makeSlugCleanStr($clean,$delimiter);

      // Revert back to the old locale
      setlocale(LC_ALL, $oldLocale);
      return $clean;
    }

    private function makeSlugCleanStr($string, $delimiter){

        // fix for ascii passing (I think) of ' resulting in -039- in place of '
        $string = str_replace('-039-','',$string);

        // replace non letter or non digits by -
        $string = preg_replace('#[^\pL\d]+#u', '-', $string);
        // Trim trailing -
        $string = trim($string, '-');
        $clean = preg_replace('~[^-\w]+~', '', $string);
        $clean = strtolower($clean);
        $clean = preg_replace('#[\/_|+ -]+#', $delimiter, $clean);
        $clean = trim($clean, $delimiter);


        return $clean;
    }






/* ======================================================
   / Formatters
   ====================================================== */






/* ======================================================
    To be sorted helpers 
   ====================================================== */

        /**
         * helper - returns single field against db table WHERE X
         * Will only work for native fields (not Cutom fields)
         *
         * @param array WHERE clauses (not Req.)
         * @param string tablename
         * @param string colname
         *
         * @return string
         */
        public function getFieldByWHERE($args=array()){

            #} =========== LOAD ARGS ==============
            $defaultArgs = array(

                'where' => -1,
                'objtype' => -1,
                'colname' => '',

                // permissions
                'ignoreowner'   => false // this'll let you not-check the owner of obj

            ); foreach ($defaultArgs as $argK => $argV){ $$argK = $argV; if (is_array($args) && isset($args[$argK])) {  if (is_array($args[$argK])){ $newData = $$argK; if (!is_array($newData)) $newData = array(); foreach ($args[$argK] as $subK => $subV){ $newData[$subK] = $subV; }$$argK = $newData;} else { $$argK = $args[$argK]; } } }
            #} =========== / LOAD ARGS =============
            
            #} ========== CHECK FIELDS ============

                // check obtype is legit
                $objtype = (int)$objtype;
                if (!isset($objtype) || $objtype == -1 || $this->objTypeKey($objtype) === -1) return false;
            
                // check field (or 'COUNT(x)')
                if (empty($colname)) return false;

            #} ========= / CHECK FIELDS ===========

            global $ZBSCRM_t,$wpdb; 
            $wheres = array('direct'=>array()); $whereStr = ''; $additionalWhere = ''; $params = array(); $res = array();

            #} Build query - NOTE this is vulnerable to injection.
            $query = "SELECT $colname FROM ".$this->lazyTable($objtype);
            //$params[] = $colname;

            #} ============= WHERE ================

                #} Add any where's 
                $wheres = $where;

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
                return $wpdb->get_var($queryObj);

            } catch (Exception $e){

                #} General SQL Err
                $this->catchSQLError($e);

            }


            return false;

        }
        /**
         * helper - returns single field against db table (where ID =)
         * Will only work for native fields (not Cutom fields)
         *
         * @param int id        line id
         * @param string tablename
         * @param string colname
         *
         * @return string
         */
        public function getFieldByID($args=array()){

            #} =========== LOAD ARGS ==============
            $defaultArgs = array(

                'id' => -1,
                'objtype' => -1,
                'colname' => '',

                // permissions
                'ignoreowner'   => false // this'll let you not-check the owner of obj

            ); foreach ($defaultArgs as $argK => $argV){ $$argK = $argV; if (is_array($args) && isset($args[$argK])) {  if (is_array($args[$argK])){ $newData = $$argK; if (!is_array($newData)) $newData = array(); foreach ($args[$argK] as $subK => $subV){ $newData[$subK] = $subV; }$$argK = $newData;} else { $$argK = $args[$argK]; } } }
            #} =========== / LOAD ARGS =============
            
            #} ========== CHECK FIELDS ============

                // check id
                $id = (int)$id;
                if (!isset($id) || $id < 1) return false;

                // check obtype is legit
                $objtype = (int)$objtype;
                if (!isset($objtype) || $objtype == -1 || $this->objTypeKey($objtype) === -1) return false;
            
                // check field
                if (empty($colname)) return false;

            #} ========= / CHECK FIELDS ===========

            global $ZBSCRM_t,$wpdb; 
            $wheres = array('direct'=>array()); $whereStr = ''; $additionalWhere = ''; $params = array(); $res = array();

            #} Build query - NOTE this is vulnerable to injection.
            $query = "SELECT $colname FROM ".$this->lazyTable($objtype);
            //$params[] = $colname;

            #} ============= WHERE ================

                #} Add ID
                if (!empty($id) && $id > 0) $wheres['ID'] = array('ID','=','%d',$id);


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
                return $wpdb->get_var($queryObj);

            } catch (Exception $e){

                #} General SQL Err
                $this->catchSQLError($e);

            }


            return false;

        }

        // brutal switch for lazy tablenames
        public function lazyTable($objType=-1){

            global $ZBSCRM_t;

            switch ($objType){

                case ZBS_TYPE_CONTACT:
                    return $ZBSCRM_t['contacts'];
                    break;


                // dal3 precursors (used by v2->3 migration routine via getFieldByWHERE)
                case ZBS_TYPE_COMPANY:
                    return $ZBSCRM_t['companies'];
                    break;
                case ZBS_TYPE_QUOTE:
                    return $ZBSCRM_t['quotes'];
                    break;
                case ZBS_TYPE_INVOICE:
                    return $ZBSCRM_t['invoices'];
                    break;
                case ZBS_TYPE_TRANSACTION:
                    return $ZBSCRM_t['transactions'];
                    break;
                case ZBS_TYPE_EVENT:
                    return $ZBSCRM_t['events'];
                    break;
                case ZBS_TYPE_FORM:
                    return $ZBSCRM_t['forms'];
                    break;
                case ZBS_TYPE_LOG:
                    return $ZBSCRM_t['logs'];
                    break;
                case ZBS_TYPE_SEGMENT:
                    return $ZBSCRM_t['segments'];
                    break;
                case ZBS_TYPE_LINEITEM:
                    return $ZBSCRM_t['lineitems'];
                    break;
                case ZBS_TYPE_EVENTREMINDER:
                    return $ZBSCRM_t['eventreminders'];
                    break;
                case ZBS_TYPE_QUOTETEMPLATE:
                    return $ZBSCRM_t['quotetemplates'];
                    break;

            }
            
            return false;

        }

        // brutal switch for lazy tidy func
        public function lazyTidy($objType=-1,$obj=false){

            switch ($objType){

                case ZBS_TYPE_CONTACT:
                    return $this->tidy_contact($obj);
                    break;
                /* yet to be moved over
                case ZBS_TYPE_COMPANY:
                    break;
                */


            }
            
            return false;

        }

        // guesses at a tidy... lazy, remove these if hit walls
        public function lazyTidyGeneric($obj=false){

            $res = false;

            foreach ($obj as $propKey => $prop){

                if (!is_array($res)) $res = array();

                if ($propKey != 'ID' && strpos($propKey, '_') > 0){

                    // zbs_owner -> owner
                    $newKey = substr($propKey,strpos($propKey, '_')+1);
                    
                    $res[$newKey] = $this->stripSlashes($prop);

                } else $res['id'] = $prop;


            }

            return $res;

        }

        // appends a space, if req. (lazy helper for amongst queries)
        public function space($str='',$pre=false){

            if (!empty($str))
                if ($pre)
                    return ' '.$str;
                else
                    return $str.' ';

            return $str;

        }

        // appends a space and 'AND', if req. (lazy helper for amongst queries)
        public function spaceAnd($str=''){

            if (!empty($str)) return $str.' AND ';

            return $str;

        }

        // appends a space and 'Where', if req. (lazy helper for amongst queries)
        public function spaceWhere($str=''){

            $trimmedStr = trim($str);
            if (!empty($trimmedStr)) return ' WHERE '.$trimmedStr;

            return $str;

        }

        // returns delimiter, if str != epty
        // used to be zeroBS_delimiterIf pre dal1
        function delimiterIf($delimiter,$ifStr=''){

            if (!empty($ifStr)) return $delimiter;

            return '';
        }

        // internal middle man for zeroBSCRM_stripSlashes where ALWAYS returns
        function stripSlashes($obj=false){

            return zeroBSCRM_stripSlashes($obj,true);

        }

        // if it thinks str is json, it'll decode + return obj, otherwise returns str
        // this only works with arr/obj
        public function decodeIfJSON($str=''){

            if (zeroBSCRM_isJson($str)) return json_decode($str,true); // true req. https://stackoverflow.com/questions/22878219/json-encode-turns-array-into-an-object

            return $str;
        }


        // takes wherestr + additionalwhere and outputs legit SQL
        // GENERIC helper for all queries :)
        public function buildWhereStr($whereStr='',$additionalWhere=''){
            
            #} Build
            $where = trim($whereStr); 

            #} Any additional
            if (!empty($additionalWhere)){ 
                if (!empty($where)) 
                    $where = $this->spaceAnd($where);
                else
                    $where = 'WHERE ';
                $where .= $additionalWhere;
            }

            return $this->space($where,true);
        }

        // add where's to SQL
        // + 
        // feed in params
        // GENERIC helper for all queries :)
        public function buildWheres($wheres=array(),$whereStr='',$params=array(),$andOr='AND',$includeInitialWHERE=true){


                /* Note on searching international characters:
    
                    in our DB we use utf8_general_ci as our collation, which is great for general case-insensitive work
                    ... but where it falls short is international characters not being taken seriously. (e.g. é)
                    ... accented "é" is counted as "e" and so searches suck for those users who use accents.

                    ... to get the best of both worlds here, we use on-the-fly encoding. 
                    ... this is yet to be perf tested, but will resolve international char issues in this case

                    Fix:

                        SELECT * FROM `wp_zbs_contacts` WHERE zbsc_fname LIKE _utf8 '%ē%' COLLATE utf8_bin;

                        ^^ Note _utf8 prefix & Collation.
                        ... use wherever accent chars may be used for search (specifically)

                    Notes:
                        - https://stackoverflow.com/questions/901066/mysql-case-sensitive-search-for-utf8-bin-field
                        - https://stackoverflow.com/questions/10023776/accented-characters-and-mysql-searching
                        - https://stackoverflow.com/questions/7126527/accent-insensitive-searches-problems-with-utf8-general-ci-collation

                */
                //$expectingAccents = true; // global for now, prepping for smarter intuitive 
                $expectingAccents = false; // See JIRA-ZBS-947 #ZBS-947 (need CI search more than accent search temp.)


            $ret = array('where'=>$whereStr,'params'=>$params); if ($andOr != 'AND' && $andOr != 'OR') $andOr = 'AND';

              // clear empty direct
              if (isset($wheres['direct']) && is_array($wheres['direct']) && count($wheres['direct']) == 0) unset($wheres['direct']);

            if (is_array($wheres) && count($wheres) > 0) foreach ($wheres as $key => $whereArr) {

                if (empty($ret['where']) && $includeInitialWHERE) $ret['where'].= ' WHERE ';

                // Where's are passed 2 ways, "direct":
                // array(SQL,array(params))
                if ($key == 'direct'){

                    // several under 1 direct
                    foreach ($whereArr as $directWhere){

                        if (isset($directWhere[0]) && isset($directWhere[1])){

                            // multi-direct ANDor
                            if (!empty($ret['where']) && $ret['where'] != ' WHERE '){
                                $ret['where'] .= ' '.$andOr.' ';
                            }
                            
                            // ++ query
                            $ret['where'] .= $directWhere[0];

                            // ++ params (any number, if set)
                            if (is_array($directWhere[1]))
                                foreach ($directWhere[1] as $x) $ret['params'][] = $x;
                            else
                                $ret['params'][] = $directWhere[1];
                        }

                    }

                } else {

                    if (!empty($ret['where']) && $ret['where'] != ' WHERE '){
                        $ret['where'] .= ' '.$andOr.' ';
                    }

                    // Other way:
                    // irrelevantKEY => array(fieldname,operator,comparisonval,array(params))
                    // e.g. array('ID','=','%d',array(123))
                    // e.g. array('ID','IN','(SUBSELECT)',array(123))

                        // here we deal with accents (where comparing Strings)
                        if ($expectingAccents && strpos($whereArr[2], '%s') > -1){

                            if (in_array($whereArr[1],array('=','<>','LIKE'))){

                                // is comparable.
                                $whereArr[2] = '_utf8 '.$whereArr[2].' COLLATE utf8_bin';

                            }

                        }

                    // build where (e.g. "X = Y" or "Z IN (1,2,3)")
                    $ret['where'] .= $whereArr[0]. ' '.$whereArr[1].' '.$whereArr[2];

                    // ++ params (any number, if set)
                    if (isset($whereArr[3])) {
                        if (is_array($whereArr[3]))
                            foreach ($whereArr[3] as $x) $ret['params'][] = $x;
                        else
                            $ret['params'][] = $whereArr[3];
                    }
                    /* legacy

                    // add in - NOTE: this is TRUSTING key + whereArr[0]
                    $ret['where'] .= $key.' '.$whereArr[0].' '.$whereArr[2];

                    // feed in params
                    $ret['params'][] = $whereArr[1];
                    */

                }

            }

            return $ret;
        }


        // takes sortby field + order and returns str if not empty :)
        // Note: Is trusting legitimacy of $sortByField as parametised in wp db doesn't seem to work
        // can also now pass array (multi-sort)
        // e.g. $sortByField = 'zbsc_fname' OR $sortByField = array('zbsc_fname'=>'ASC','zbsc_lname' => 'DESC');
        public function buildSort($sortByField='',$sortOrder='ASC'){

            #} Sort by
            if (!is_array($sortByField) && !empty($sortByField)){

                $sortOrder = strtoupper($sortOrder);                

                if (!in_array($sortOrder, array('DESC','ASC'))) $sortOrder = 'DESC';
                return ' ORDER BY '.$sortByField.' '.$sortOrder;

            } else if (is_array($sortByField)){

                $orderByStr = '';
                foreach ($sortByField as $field => $order){

                    if (!empty($orderByStr)) $orderByStr .= ', ';
                    $orderByStr .= $field.' '.strtoupper($order);

                }

                if (!empty($orderByStr)) return ' ORDER BY '.$orderByStr;

            }

            return '';
        }


        // takes $page and $perPage and adds limit str if req.
        public function buildPaging($page=-1,$perPage=-1){

            #} Pagination
            if ($page == -1 && $perPage == -1){

                // NO LIMITS :o

            } else {

                $perPage = (int)$perPage;

                // Because SQL USING zero indexed page numbers, we remove -1 here
                // ... DO NOT change this without seeing usage of the function (e.g. list view) - which'll break
                $page = (int)$page-1; 
                if ($page < 0) $page = 0;

                // page needs multiplying :) 
                if ($page > 0) $page = $page * $perPage;

                // check params realistic
                // todo, for now, brute pass
                return ' LIMIT '.(int)$page.','.(int)$perPage;

            }

            return '';
        }


        // builds WHERE query for meta key / val pairs.
        // e.g. Get customers in Company id 9:
        // ... contacts where their ID is in post_id WHERE meta_key = zbs_company and meta_value = 9
        // infill for half-migrated stuff
        private function buildWPMetaQueryWhere($metaKey=-1,$metaVal=-1){

            if (!empty($metaKey) && !empty($metaVal)){

                global $wpdb;
                return array(
                    
                    'sql' => 'ID IN (SELECT DISTINCT post_id FROM '.$wpdb->prefix.'postmeta WHERE meta_key = %s AND meta_value = %d)',
                    'params' => array($metaKey,$metaVal)
                    );

            }

            return false;

        }

        // this returns %s etc. for common field names, will default to %s unless somt obv a date
        private function getTypeStr($fieldKey=''){

            if ($fieldKey == 'zbs_site' || $fieldKey == 'zbs_team' || $fieldKey == 'zbs_owner') return '%d';

            if (strpos($fieldKey, '_created') > 0) return '%d';
            if (strpos($fieldKey, '_lastupdated') > 0) return '%d';
            if (strpos($fieldKey, '_lastcontacted') > 0) return '%d';
            if (strpos($fieldKey, '_id') > 0) return '%d';
            if (strpos($fieldKey, '_ID') > 0) return '%d';
            if ($fieldKey == 'id' || $fieldKey == 'ID') return '%d';

            return '%s';

        }

        public function prepare($sql='',$params=array()){

            global $wpdb;

            // empty arrays causes issues in wpdb prepare
            if (is_array($params) && count($params) <= 0) return $sql;

            // normal return
            return $wpdb->prepare($sql,$params);

        }

        // not yet used
        private function catchSQLError($errObj=-1){


            // log?

            return false;

        }

        // ===============================================================================
        // ===========  ERROR HELPER FUNCS ===============================================
        /* These are shared between DAL2 + DAL3, though are only included from v3.0 +   */

            // retrieve errors from dal error stack
            public function getErrors($objTypeID=-1){

                // all:
                if ($objTypeID < 0) return $this->errorStack;

                // specific
                if (is_array($this->errorStack) && isset($this->errorStack[$objTypeID])) return $this->errorStack[$objTypeID];


                // ??
                return array();
            }

            // add error to dal error stack
            public function addError($errorCode=-1,$objTypeID=-1,$errStr='',$extraParam=false){

                if ($objTypeID > 0 && !empty($errStr)){

                    // init
                    if (!isset($this->errorStack) || !is_array($this->errorStack)) $this->errorStack = array();
                    if (!isset($this->errorStack[$objTypeID]) || !is_array($this->errorStack[$objTypeID])) $this->errorStack[$objTypeID] = array();

                    // if $errorCode, add to string
                    if ($errorCode > 0) $errStr .= ' ('.__('Error #','zero-bs-crm').$errorCode.')';

                    // add
                    $this->errorStack[$objTypeID][] = array('code'=>$errorCode,'str'=>$errStr,'param'=>$extraParam);
                }
            }

        // =========== / ERROR HELPER FUNCS ==============================================
        // ===============================================================================





        // ===============================================================================
        // ============ v2->v3 Migration HELPER FUNCS ====================================

        // get an obj model, if set
        // following written ad-hoc specifically for the v2-v3 migration (not to be used in run-of-the-mill way)
        // will only ever succeed mid-migration.
        public function objModel($objTypeID=-1){

            $potentialModel = false;

            switch ($objTypeID){

                case ZBS_TYPE_COMPANY:
                    global $tempCompaniesClass;
                    $potentialModel = $tempCompaniesClass->objModel();
                    break;

                case ZBS_TYPE_QUOTE:
                    global $tempQuotesClass;
                    $potentialModel = $tempQuotesClass->objModel();
                    break;

                case ZBS_TYPE_INVOICE:
                    global $tempInvoicesClass;
                    $potentialModel = $tempInvoicesClass->objModel();
                    break;

                case ZBS_TYPE_TRANSACTION:
                    global $tempTransactionsClass;
                    $potentialModel = $tempTransactionsClass->objModel();
                    break;

                case ZBS_TYPE_LINEITEM:
                    global $tempLineItemsClass;
                    $potentialModel = $tempLineItemsClass->objModel();
                    break;

                case ZBS_TYPE_ADDRESS:
                    global $tempAddressClass;
                    $potentialModel = $tempAddressClass->objModel();
                    break;

            }

            return $potentialModel;

        }

        // =========== / v2->v3 Migration HELPER FUNCS ===================================
        // ===============================================================================



} // / DAL class


/* ======================================================
    / To be sorted helpers  
   ====================================================== */