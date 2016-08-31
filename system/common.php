<?php

class common extends Model{

    /**
     * Returns the id of user type
     *
     * @param int       $id     User id
     * @return int      Returns the id of user type
     *
     * @author Rogerio Albandes <rogerio.albandes@helpdezk.cc>
     */
    public function getIdTypePerson($id)
    {
        $ret = $this->select("select idtypeperson from tbperson where idperson = $id");
        if (!$ret) {
            $sError = "Arq: " . __FILE__ . " Line: " . __LINE__ . "<br>DB ERROR: " . $this->db->ErrorMsg();
            $this->error($sError);
        }
        return $ret->fields['idtypeperson'];
    }

    /**
     * Returns the user's login
     * @param int       $id     User id
     * @return int      Returns the user's login
     *
     * @author Rogerio Albandes <rogerio.albandes@helpdezk.cc>
     *
     */
    public function getUserLogin($id)
    {
        $ret = $this->select("select login from tbperson where idperson = $id");
        $nom = $ret->fields['login'];
        return $nom;
    }

    /**
     * Returns the number of programs
     * @return int      Returns the number of programs
     *
     * @author Rogerio Albandes <rogerio.albandes@helpdezk.cc>
     *
     */
    public function countPrograms()
    {
        $count = $this->select("select max(idprogram) as total from tbprogram");
        return $count->fields['total'];
    }

    /**
     * Returns a recordset with the permission access per group
     * @param  int      $idperson   User id
     * @param  int      $type       Id of user type
     * @return object   Returns a recordset with the permission access per group
     *
     * @author Rogerio Albandes <rogerio.albandes@helpdezk.cc>
     *
     */
    public function selectGroupPermissionMenu($idperson, $type='', $idmodule)
    {
        $rsGroupperm = $this->select("
									select
									  m.idmodule           as idmodule_pai,
									  m.name               as module,
									  cat.idmodule          as idmodule_origem,
									  cat.name              as category,
									  cat.idprogramcategory as category_pai,
									  cat.smarty as cat_smarty,
									  pr.idprogramcategory  as idcategory_origem,
									  pr.name               as program,
									  pr.controller         as controller,
									  pr.smarty  as pr_smarty,
									  pr.idprogram          as idprogram,
									  a.idaccesstype    as permission,
									  g.allow
									from tbperson  p,
									  tbtypepersonpermission  g,
									  tbaccesstype  a,
									  tbprogram  pr,
									  tbmodule  m,
									  tbprogramcategory  cat,
									  tbtypeperson  tp
									WHERE g.idaccesstype = a.idaccesstype
										and g.idprogram = pr.idprogram
										and m.idmodule = cat.idmodule
										and cat.idprogramcategory = pr.idprogramcategory
										and tp.idtypeperson = g.idtypeperson
										AND m.status = 'A'
										AND pr.status = 'A'
										AND p.idperson = '$idperson'
                                        AND pr.idprogramcategory IN
                                          (SELECT
                                            idprogramcategory
                                          FROM
                                            tbprogramcategory
                                          WHERE idmodule = $idmodule)
										AND tp.idtypeperson IN (
                                                                SELECT
                                                                   idtypeperson
                                                                FROM
                                                                   tbperson
                                                                WHERE idperson = $idperson
                                                                UNION
                                                                SELECT
                                                                   idtypeperson
                                                                FROM
                                                                   tbpersonmodule
                                                                WHERE idperson = $idperson
										)
										AND g.idaccesstype = '1'
										AND g.allow = 'Y'");

        // Old version :  AND tp.idtypeperson = '$type'

        if ($rsGroupperm->fields['idmodule_pai'])
            return $rsGroupperm;

    }

    public function getPermissionMenu($idperson,  $andModule)
    {
        $rsGroupperm = $this->select("
									(
									select
									  m.idmodule           as idmodule_pai,
									  m.name               as module,
									  cat.idmodule          as idmodule_origem,
									  cat.name              as category,
									  cat.idprogramcategory as category_pai,
									  cat.smarty as cat_smarty,
									  pr.idprogramcategory  as idcategory_origem,
									  pr.name               as program,
									  pr.controller         as controller,
									  pr.smarty  as pr_smarty,
									  pr.idprogram          as idprogram,
									  g.allow
									from tbperson  p,
									  tbtypepersonpermission  g,
									  tbaccesstype  a,
									  tbprogram  pr,
									  tbmodule  m,
									  tbprogramcategory  cat,
									  tbtypeperson  tp
									WHERE g.idaccesstype = a.idaccesstype
										and g.idprogram = pr.idprogram
										and m.idmodule = cat.idmodule
										and cat.idprogramcategory = pr.idprogramcategory
										and tp.idtypeperson = g.idtypeperson
										AND m.status = 'A'
										AND pr.status = 'A'
										AND p.idperson = '$idperson'
										AND tp.idtypeperson IN
                                            (SELECT
                                                  idtypeperson
                                               FROM
                                                  tbpersontypes
                                               WHERE idperson = '$idperson'  )
										AND g.idaccesstype = '1'
										AND g.allow = 'Y'
										AND $andModule
									)
									UNION
									(
										select
										  m.idmodule           as idmodule_pai,
										  m.name               as module,
										  cat.idmodule          as idmodule_origem,
										  cat.name              as category,
										  cat.idprogramcategory as category_pai,
										  cat.smarty as cat_smarty,
										  pr.idprogramcategory  as idcategory_origem,
										  pr.name               as program,
										  pr.controller         as controller,
										  pr.smarty  as pr_smarty,
										  pr.idprogram          as idprogram,
										  p.allow
										from tbperson  per,
										  tbpermission  p,
										  tbprogram  pr,
										  tbmodule  m,
										  tbprogramcategory  cat,
										  tbaccesstype  acc
										where m.idmodule = cat.idmodule
											and pr.idprogramcategory = cat.idprogramcategory
											and per.idperson = p.idperson
											AND pr.idprogram = p.idprogram
											and m.status = 'A'
											and pr.status = 'A'
											AND p.idperson = '$idperson'
											AND p.idaccesstype = acc.idaccesstype
											AND p.idaccesstype = '1'
											AND $andModule
									)
                                  ");

        if ($rsGroupperm->fields['idmodule_pai'])
            return $rsGroupperm;

    }

    /**
     * Returns a recordset with the permission access per user
     * @param  int      $idperson   User id
     * @return object   Returns a recordset with the permission access per user
     *
     * @author Rogerio Albandes <rogerio.albandes@helpdezk.cc>
     *
     */
    public function selectPersonPermissionMenu($idperson,$idmodule)
    {
        $individualperm = $this->select("
										select
										  m.idmodule           as idmodule_pai,
										  m.name               as module,
										  cat.idmodule          as idmodule_origem,
										  cat.name              as category,
										  cat.idprogramcategory as category_pai,
										  cat.smarty as cat_smarty,
										  pr.idprogramcategory  as idcategory_origem,
										  pr.name               as program,
										  pr.controller         as controller,
										  pr.smarty  as pr_smarty,
										  pr.idprogram          as idprogram,
										  p.allow
										from tbperson  per,
										  tbpermission  p,
										  tbprogram  pr,
										  tbmodule  m,
										  tbprogramcategory  cat,
										  tbaccesstype  acc
										where m.idmodule = cat.idmodule
											and pr.idprogramcategory = cat.idprogramcategory
											and per.idperson = p.idperson
											AND pr.idprogram = p.idprogram
											AND pr.idprogramcategory IN
                                              (SELECT
                                                idprogramcategory
                                              FROM
                                                tbprogramcategory
                                              WHERE idmodule = $idmodule)
											and m.status = 'A'
											and pr.status = 'A'
											AND p.idperson = '$idperson'
											AND p.idaccesstype = acc.idaccesstype
											AND p.idaccesstype = '1'");

        if ($individualperm->fields['idmodule_pai'])
            return $individualperm;

    }

    /**
     * Returns the number of program's categories
     *
     * @return int      Returns the number of program's categories
     *
     * @author Rogerio Albandes <rogerio.albandes@helpdezk.cc>
     *
     */
    public function countCategories()
    {
        $count = $this->select("select max(idprogramcategory) as total from tbprogramcategory");
        return $count->fields['total'];
    }

    /**
     * Returns the number of modules actives
     * @return int      Returns the number of modules actives
     *
     * @author Rogerio Albandes <rogerio.albandes@helpdezk.cc>
     *
     */
    public function countModules()
    {
        $count = $this->select("select max(idmodule) as total from tbmodule where status = 'A'");
        return $count->fields['total'];
    }

    /**
     * Returns the if of module
     * @param  string   name    Module name
     * @return int              Returns the number of modules actives
     *
     * @author Rogerio Albandes <rogerio.albandes@helpdezk.cc>
     *
     */
    public function getIdModule($name)
    {
        $ret = $this->select("
                                SELECT
                                  idmodule
                                FROM
                                  tbmodule
                                WHERE `name` = '$name' ;
                            ");
        return $ret->fields['idmodule'] ;
    }

    /**
     * Returns a recordset with the data of modules that the User has access
     * @param  int      $idperson   User id
     * @return object   Returns a recordset with the data of modules that the User has access
     *
     * @author Rogerio Albandes <rogerio.albandes@helpdezk.cc>
     *
     */
    public function getExtraModulesPerson($idperson)
    {
        $sql = 	"
				SELECT
				  DISTINCT temp.idmodule,
				  temp.name,
				  temp.index,
				  temp.path,
				  temp.smarty,
				  temp.class
				FROM
				  (
					(
                    SELECT
                       m.idmodule,
                       m.name,
                       m.index,
                       m.path,
                       m.smarty,
                       m.class
                    FROM
                       tbperson per,
                       tbpermission p,
                       tbprogram pr,
                       tbmodule m,
                       tbprogramcategory cat,
                       tbaccesstype acc
                    WHERE m.idmodule = cat.idmodule
                       AND pr.idprogramcategory = cat.idprogramcategory
                       AND per.idperson = p.idperson
                       AND pr.idprogram = p.idprogram
                       AND m.status = 'A'
                       AND pr.status = 'A'
                       AND p.idperson = '$idperson'
                       AND p.allow = 'Y'
                       AND p.idaccesstype = acc.idaccesstype
                       AND p.idaccesstype = '1'
                       AND m.idmodule > 3
                    GROUP BY m.idmodule
                    )
					UNION
					(SELECT
					  d.idmodule,
					  d.name,
					  d.index,
					  d.path,
					  d.smarty,
					  d.class
					FROM
					  tbtypepersonpermission a,
					  tbprogram b,
					  tbprogramcategory c,
					  tbmodule d
					WHERE a.idtypeperson IN
                        (SELECT
                              idtypeperson
                           FROM
                              tbpersontypes
                           WHERE idperson = '$idperson'  )
                      AND a.allow = 'Y'
					  AND d.status = 'A'
					  AND d.idmodule > 3
					  AND a.idprogram = b.idprogram
					  AND c.idprogramcategory = b.idprogramcategory
					  AND d.idmodule = c.idmodule
					GROUP BY d.idmodule)
				  ) AS temp
                ";

        $ret = $this->select($sql);
        if (!$ret) {
            $sError = "Error in:  " . __FILE__ . ", line: " . __LINE__ . "<br>DB ERROR: " . $this->db->ErrorMsg() . "<BR>Query: " . $sql;
            $this->error($sError);
        } else {
            return $ret;
        }

    }

    /**
     * Returns a recordset with data of header logo image
     * @return object   Returns a recordset with data of header logo image
     *
     * @author Rogerio Albandes <rogerio.albandes@helpdezk.cc>
     *
     */
    public function getHeaderLogo(){
        return $this->select("select name, height, width, file_name from tblogos where name = 'header'");
    }

    /**
     * Returns a recordset with data of report logo image
     * @return object   Returns a recordset with data of header logo image
     *
     * @author Rogerio Albandes <rogerio.albandes@helpdezk.cc>
     *
     */
    public function getReportsLogo(){
        return $this->select("select name, height, width, file_name from tblogos where name = 'reports'");
    }

    /**
     * Returns a recordset with the permission access per user
     *
     * @param  string   $where   Sql query
     * @param  string   $order   Sql order
     * @return object   Returns a recordset with the permission access per user
     *
     * @author Rogerio Albandes <rogerio.albandes@helpdezk.cc>
     *
     */
    public function getErpCompanies($where = null,$order = null)
    {
        return $this->select("SELECT  idperson as idcompany, name FROM tbperson $where $order");
    }

    /**
     * Get recordset with the  account's data
     * @param  string   $where   Sql query
     * @param  string   $order   Sql order
     * @param  string   $limit   Sql limit
     * @return object   Returns a recordset with the  account's data
     *
     * @author Rogerio Albandes <rogerio.albandes@helpdezk.cc>
     *
     */
    public function getErpAccount($where, $order, $limit)
    {
        $database = $this->getConfig('db_connect');
        if ($database == 'mysqlt') {
            $qry =  "
                    SELECT a.idaccount,
                      a.idperson,
                      a.idcostcenter,
                      a.name,
                      a.cod_account,
                      b.name company,
                      a.status,
                      c.type
                    FROM erp_tbaccount a,
                      tbperson b,
                      erp_tbcostcenter c
                    WHERE a.idperson   = b.idperson
                    AND a.idcostcenter = c.idcostcenter
                    $where $order $limit
                    ";
        } elseif ($database == 'oci8po') {
            $core = "
                    SELECT a.idaccount,
                      a.idperson,
                      a.idcostcenter,
                      a.name,
                      a.cod_account,
                      b.name company,
                      a.status,
                      c.type
                    FROM erp_tbaccount a,
                      tbperson b,
                      erp_tbcostcenter c
                    WHERE a.idperson   = b.idperson
                    AND a.idcostcenter = c.idcostcenter
                    $where $order
                    " ;
            if($limit){
                $limit = str_replace('LIMIT', "", $limit);
                $p     = explode(",", $limit);
                $start = $p[0] + 1;
                $end   = $p[0] +  $p[1];
                $qry  = "
                        SELECT *
                        FROM
                          (SELECT a .*, ROWNUM rnum FROM ( $core ) a WHERE ROWNUM <= $end
                          )
                        WHERE rnum >= $start
                       ";
            }else{
                $qry = $core;
            }
        }

        return $this->db->Execute($qry);
    }

    /**
     * Returns the id of program depending on controller
     *
     * @param  string   $controller     Controller name
     * @return int      Returns the id of program
     *
     * @author Rogerio Albandes <rogerio.albandes@helpdezk.cc>
     */

    public function selectProgramIDByController($controller)
    {
        $database = $this->getConfig('db_connect');
        if (strpos($controller, '/') == false) {
            $contr_dash =  $controller . '/';
            $contr = $controller;
        } else {
            $contr = substr($controller,0,-1);
            $contr_dash = $controller;
        }

        if ($database == 'mysqlt') {
            $sel = $this->select("select idprogram from tbprogram where controller = '$contr' || controller = '$contr_dash'");
        } elseif ($database == 'oci8po') {
            $sel = $this->select("select idprogram from tbprogram where controller = '$contr' or controller = '$contr_dash'");
        }

        return $sel->fields['idprogram'];
    }

    /**
     * Returns a recordset with the permission access for a especific user
     *
     * @param  int      $idperson   User id
     * @param  int      $idprogram  Program id
     * @return object   Returns a recordset with the permission access for especific user
     *
     * @author Rogerio Albandes <rogerio.albandes@helpdezk.cc>
     *
     */
    public function selectPersonPermission($idperson, $idprogram) {
        $database = $this->getConfig('db_connect');
        if ($database == 'mysqlt') {
            $concat = "concat(pr.name, '–' ,p.idaccesstype) as programname";
        } elseif ($database == 'oci8po') {
            $concat = "pr.name || '–' || p.idaccesstype as programname";
        }
        $query = "
				select
				   p.idpermission,
				   p.idaccesstype,
				   p.idprogram,
				   p.idperson,
				   p.allow,
				   m.idmodule     as module,
				   ".$concat."
				from tbpermission  p,
				   tbprogram  pr,
				   tbprogramcategory  cat,
				   tbmodule  m
				where m.idmodule = cat.idmodule
					 and pr.idprogramcategory = cat.idprogramcategory
					 and p.idprogram = pr.idprogram
					 and m.status = 'A'
					 and pr.status = 'A'
					 AND idperson = '$idperson'
					 AND p.idprogram = '$idprogram'
				";
        //die($query);
        $individualperm = $this->select($query);
        return $individualperm;
    }

    /**
     * Returns a recordset with the permission access for a especific user also considering the type of user
     *
     * @param  int      $idperson   User id
     * @param  int      $idprogram  Program id
     * @param  int      $type       Person type ID
     * @return object   Returns a recordset with the permission access for especific user
     *
     * @author Rogerio Albandes <rogerio.albandes@helpdezk.cc>
     *
     */
    public function selectGroupPermission($idperson, $idprogram, $type='')
    {
        $database = $this->getConfig('db_connect');
        if ($database == 'mysqlt') {
            $concat = "concat(pr.name, '–' ,a.idaccesstype) as programname";
        } elseif ($database == 'oci8po') {
            $concat = "pr.name || '–' || a.idaccesstype as programname";
        }
        $query = "
					select
					  g.idpermissiongroup,
					  pr.idprogram        as idprogram,
					  pr.smarty  as pr_smarty,
					  a.idaccesstype,
					  m.idmodule          as idmodule,
					  tp.idtypeperson     as idtypeperson,
					  ".$concat.",
					  g.allow
					from tbperson  p,
					  tbtypepersonpermission  g,
					  tbaccesstype  a,
					  tbprogram  pr,
					  tbmodule  m,
					  tbprogramcategory  cat,
					  tbtypeperson  tp
					WHERE g.idaccesstype = a.idaccesstype
						and g.idprogram = pr.idprogram
						and m.idmodule = cat.idmodule
						and cat.idprogramcategory = pr.idprogramcategory
						and tp.idtypeperson = g.idtypeperson
						AND m.status = 'A'
						AND pr.status = 'A'
						AND p.idperson = '$idperson'
						AND tp.idtypeperson IN (
                                                SELECT
                                                   idtypeperson
                                                FROM
                                                   tbperson
                                                WHERE idperson = $idperson
                                                UNION
                                                SELECT
                                                    idtypeperson
                                                  FROM
                                                    tbpersontypes
                                                  WHERE idperson = '$idperson'
										        )

						AND pr.idprogram = '$idprogram'
				";

        // Old version : AND tp.idtypeperson = '$type'
        //die($query) ;
        $groupperm = $this->select($query);

        return $groupperm;

    }

    /**
     * Returns a query date format so use in a database query
     * Use in method formatSaveDate
     *
     * @param  string   $date       Date
     * @param  int      $format     Date format
     * @return object   Returns a recordset with the permission access for especific user
     *
     * @author Rogerio Albandes <rogerio.albandes@helpdezk.cc>
     *
     */
    public function getSaveDate($date, $format)
    {
        $database = $this->getConfig('db_connect');
        if ($database == 'mysqlt') {
            $ret = $this->db->Execute("SELECT STR_TO_DATE('$date','$format') as date");
            if (!$ret) {
                $sError = "Arq: " . __FILE__ . " Line: " . __LINE__ . "<br>DB ERROR: " . $this->db->ErrorMsg() . "<br>--" . $query;
                $this->error($sError);
                return false;
            }
            return $ret->fields['date'];
        } elseif ($database == 'oci8po') {
            $format = $this->getConfig('oracle_format_date');
            return "TO_DATE('$date', '$format')";
        }
    }

    public function foundRows(){
        return $this->select("SELECT FOUND_ROWS() AS `found_rows`");
    }


    public function getValueParam($module,$param) {
        $query =    "
                    SELECT
                      `value`
                    FROM
                      tbparameter
                    WHERE `name` = '$param'
                      AND idmodule =
                      (SELECT
                        idmodule
                      FROM
                        tbmodule
                      WHERE `name` = '$module')
                    ";

        $rs = $this->db->Execute($query);
        return $rs->fields['value'] ;
    }


    public function getEmailConfigs() {
        $conf = $this->select("select session_name,value from hdk_tbconfig where idconfigcategory = 5");
        while (!$conf->EOF) {
            $ses = $conf->fields['session_name'];
            $val = $conf->fields['value'];
            $emailConfs[$ses] = $val;
            $conf->MoveNext();
        }
        return $emailConfs;
    }

    public function selectPerson($where = NULL, $order = NULL, $limit = NULL) {
        $database = $this->getConfig('db_connect');
        if ($database == 'mysqlt') {
            $number = "addr.number";
        } elseif ($database == 'oci8po') {
            $number = "addr.number_";
        }

        $ret = $this->select("
							select
								  tbp.idperson,
								  tbp.name,
								  tbp.login,
								  tbp.email,
								  tbp.status,
								  tbp.user_vip,
								  tbp.phone_number as telephone,
								  tbp.branch_number,
								  tbp.cel_phone as cellphone,
								  tbtp.name         as typeperson,
								  tbtp.idtypeperson,
								  ctry.printablename as country,
								  ctry.idcountry,
								  stt.name as state,
								  stt.idstate,
								  nbh.name as neighborhood,
								  ct.name as city,
								  ct.idcity,
								  tpstr.name as typestreet,
								  tpstr.idtypestreet,
								  st.name as street,
								  $number,
								  addr.complement,
								  addr.zipcode
                            from tbperson tbp,
								  tbtypeperson tbtp,
								  tbaddress addr,
								  tbcity ct,
								  tbcountry ctry,
								  tbstate stt,
								  tbstreet st,
								  tbneighborhood nbh,
								  tbtypeaddress tpad,
								  tbtypestreet tpstr
                            where tbp.idtypeperson = tbtp.idtypeperson
									AND addr.idperson = tbp.idperson
									AND addr.idcity = ct.idcity
									AND addr.idneighborhood = nbh.idneighborhood
									AND addr.idstreet = st.idstreet
									AND addr.idtypeaddress = tpad.idtypeaddress
									AND st.idtypestreet = tpstr.idtypestreet
									AND ct.idstate = stt.idstate
									and stt.idcountry = ctry.idcountry $where $order $limit"
        );
        if (!$ret) {
            $sError = "Arq: " . __FILE__ . " Line: " . __LINE__ . "<br>DB ERROR: " . $this->db->ErrorMsg();
            $this->error($sError);
        }
        return $ret;
    }

    public function getDate($date, $format) {
        $database = $this->getConfig('db_connect');
        if ($database == 'mysqlt') {
            $query = "SELECT DATE_FORMAT('$date','$format') as date" ;
        } elseif ($database == 'oci8po') {
            if ((strpos($date, '-') === false)&&(strpos($date, '/') === false)){
                $query = "SELECT to_char(TO_DATE('$date','YYYYMMDD'), 'DD/MM/YYYY') as \"date\"  from dual";
            }else if(strpos($date, '-') === false){
                $query = "SELECT to_char(TO_DATE('$date','DD/MM/YYYY HH24:MI'), 'DD/MM/YYYY') as \"date\"  from dual";
            }else{
                $query = "SELECT to_char(TO_DATE('$date','YYYY-MM-DD HH24:MI'), 'DD/MM/YYYY') as \"date\" from dual";
            }

        }
        $ret = $this->db->Execute($query);
        if(!$ret) {
            $sError = $query."Arq: " . __FILE__ . " Line: " . __LINE__ . "<br>DB ERROR: " .  $this->db->ErrorMsg()  ;
            $this->error($sError);
            return false;
        }
        return $ret->fields['date'];
    }

    /**
     * returns the value of the values in tbauxdatabase
     *
     * @since Version 1.2
     * @author Rogerio Albandes <rogerio.albandes@helpdezk.cc>
     */

    public function getAuxDB()
    {
        $q =    "
                SELECT
                  COUNT(idauxdatabase) as amt,
                  dbtype,
                  dbhostname,
                  dbport,
                  dbname,
                  dbusername,
                  dbpassword
                FROM
                  tbauxdatabase
                ";
        //die($q);
        $ret = $this->db->Execute($q);
        if (!$ret) {
            $sError = "Arq: " . __FILE__ . " Line: " . __LINE__ . "<br>DB ERROR: " . $this->db->ErrorMsg();
            $this->error($sError);
            return false;
        }
        return $ret;
    }

    /**
     * Returns the id of the department, search by name
     *
     * @since Version 1.2
     * @author Rogerio Albandes <rogerio.albandes@helpdezk.cc>
     */

    public function getIdDepartmentByName($name)
    {
        $q =    "
                SELECT
                  iddepartment
                FROM
                  hdk_tbdepartment
                WHERE `name` = '$name'
                ";

        $ret = $this->db->Execute($q);
        if (!$ret) {
            $sError = "Arq: " . __FILE__ . " Line: " . __LINE__ . "<br>DB ERROR: " . $this->db->ErrorMsg();
            $this->error($sError);
            return false;
        }
        return $ret->fields['iddepartment'];
    }

    /*
     *  Methods DASHBOARD
     */
    public function getWidget($id) {
        return $this->select("select widgets from dsh_tbwidgetusuario where idusuario = '$id'");
    }
    /*
     *  End methods dashboard
     */

}
