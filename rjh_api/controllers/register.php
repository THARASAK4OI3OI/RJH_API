<?php

    function register_general($id){
        
        /*เช็ค id ว่าเป็นสิทธิประกันสังคมใน ssn ที่นำเข้าหรือไม่ */
        $sql_ssn = " SELECT * FROM ssn WHERE id='".$id."' ";
        $res_ssn = dbQuery($sql_ssn);
        if(dbNumRows($res_ssn) > 0) {
            $ssn_sql = " SELECT * FROM ssn WHERE id='".$id."' and end<curdate() ";
            $ssn_res = dbQuery($ssn_sql);
            if(dbNumRows($ssn_res) > 0) {
                echo json_encode(array('message' => 'สิทธิประกันสังคมหมดอายุ','status' => FALSE));
            }else{
            /*เช็ค id ว่าเป็นผู้ป่วยของ รพ หรือไม่ */
                $sql_mis = " SELECT t1.hn,t2.date FROM mis as t1 
                            LEFT JOIN mis_del as t2 on (t1.hn=t2.hn and t2.user_re='')
                            WHERE t1.id='".$id."' and isnull(t2.date)
                            ORDER BY t1.hn DESC 
                            LIMIT 1 
                        ";
                $res_mis = dbQuery($sql_mis);
                if(dbNumRows($res_mis) > 0) {
                    $row_mis = dbFetchAssoc($res_mis);
                    $hn = $row_mis['hn'];
                
                /*เช็ค hn ว่าผู้ป่วยลงทะเบียนวันนี้ไปแล้วหรือไม่ */
                    $sql_reg_chk = " SELECT * FROM reg WHERE hn='".$hn."' and date=curdate() ";
                    $res_reg_chk = dbQuery($sql_reg_chk);
                    if(dbNumRows($res_reg_chk) > 0) {
                        echo json_encode(array('message' => 'มีการลงทะเบียนวันนี้แล้ว ไม่สามารถลงทะเบียนได้','status' => FALSE));
                    }else{
                        
                    /*เช็ค hn ว่าผู้ป่วยเป็นผู้ป่วยในหรือไม่ */
                        $sql_ipd = " SELECT * FROM ipd WHERE hn='".$hn."' ";
                        $res_ipd = dbQuery($sql_ipd);
                        if(dbNumRows($res_ipd) > 0) {
                            echo json_encode(array('message' => 'เป็นผู้ป่วยใน ไม่สามารถลงทะเบียนได้','status' => FALSE));
                        }else{
                        
                        /*เช็ค hn ว่าผู้ป่วยเสียชีวิตหรือไม่ */
                            $sql_death = " SELECT * FROM death WHERE hn='".$hn."' ";
                            $res_death = dbQuery($sql_death);
                            if(dbNumRows($res_death) > 0) {
                                echo json_encode(array('message' => 'ผู้ป่วยเสียชีวิตแล้ว!','status' => FALSE));
                            }else{

                                $mis_sql = " SELECT * FROM mis WHERE hn='".$hn."' ";
                                $mis_res = dbQuery($mis_sql);
                                if(dbNumRows($mis_res) > 0) {
                                    $mis_row = dbFetchAssoc($mis_res);
                                    $ttl = $mis_row['ttl'];
                                    $name = $mis_row['name'];
                                    $lname = $mis_row['lname'];
                                    $sex = $mis_row['sex'];
                                    $born = $mis_row['born'];
                                    $moo = $mis_row['moo'];
                                    $acode = $mis_row['acode'];
                                    $rac = $mis_row['rac'];
                                    $nat = $mis_row['nat'];
                                    $rel = $mis_row['rel']; 
                                    $occ = $mis_row['occ'];
                                    $sta = $mis_row['sta'];
                                    $hmain = $mis_row['hmain'];
                                    $hsub = $mis_row['hsub'];
                                    $typem = $mis_row['typem'];
                                    $typen = $mis_row['typen'];
                                    $typei = $mis_row['typei'];
                                    $typex = $mis_row['typex'];
                                    $types = $mis_row['types'];
                                    $alg = $mis_row['alg'];
                                    $blood = $mis_row['blood'];
                                    $rh = $mis_row['rh'];
                                    $unders = $mis_row['unders'];
                                    $comp = $mis_row['comp'];
                                    $note1 = $mis_row['note1'];
                                    $note2 = $mis_row['note2'];
                                    $note3 = $mis_row['note3'];
                                    $hn_old = $mis_row['hn_old'];

                                    $num = (int)$mis_row['num']+1;
                                    $type = 'A7';                             /* A7 สิทธิประกันสังคม*/ 
                                    $room = '23';                             /* 23 ผู้ป่วยนอกประกันสังคม  20 ปกส F1*/                   
                                    $comefor = '01';
                                    $user_ad = '1';                           /* user ที่ลงทะเบียน*/ 
                                    $bill = 0;

                            /*01 อายุรกรรม */
                                $spc_sql = " SELECT spc FROM tbllu WHERE title='clin' and code='".$room."' ";
                                $spc_res = dbQuery($spc_sql);
                                if(dbNumRows($spc_res) > 0) {
                                    $spc_row = dbFetchAssoc($spc_res);
                                    $spc = $spc_row['spc'];
                                }else{
                                    $spc = '';
                                }

                            /*หา วัน เวลา ปัจจุบัน ใน server */
                                $ck_date_sql = " SELECT date_format(curdate(),'%Y-%m-%d') as curdate__,time_format(curtime(),'%H:%i:%s') as curtime__ ";
                                $ck_date_res = dbQuery($ck_date_sql);
                                $ck_date_row = dbFetchAssoc($ck_date_res);
                                $date =  $ck_date_row['curdate__'];
                                $time = substr($ck_date_row['curtime__'],0,5);

                                $age = (int)substr($date,0,4) - (int)substr($born,0,4); /* คำนวณอายุ */

                                if((int)$age > 25){
                                    $under_sql = " SELECT under FROM reg WHERE hn='".$hn."' ORDER BY date DESC LIMIT 1 ";
                                    $under_res = dbQuery($under_sql);
                                    if(dbNumRows($under_res) > 0) {
                                        $under_row = dbFetchAssoc($under_res);
                                        $under = $under_row['under'];
                                    }else{
                                        $under = '';
                                    }
                                }else{
                                    $under = '';
                                }

                                /* insert reg */
                                    $int_reg = " INSERT INTO reg SET hn='".$hn."',ttl='".$ttl."',name='".$name."',lname='".$lname."',sex='".$sex."',age='".$age."',born='".$born."' 
                                                ,room='".$room."',spc='".$spc."',date='".$date."',time='".$time."',num='".$num."',acode='".$acode."',rac='".$rac."',nat='".$nat."'
                                                ,rel='".$rel."',occ='".$occ."',sta='".$sta."',id='".$id."',hmain='".$hmain."',hsub='".$hsub."',type='".$type."',typem='".$typem."'
                                                ,typen='".$typen."',user_ad='".$user_ad."',typei='".$typei."',typex='".$typex."',alg='".$alg."',blood='".$blood."',rh='".$rh."',bill='".$bill."'
                                                ,types='".$types."',under='".$under."',unders='".$unders."',note1='".$note1."',note2='".$note2."',note3='".$note3."'                           
                                                ";
                                    $int_reg_res = dbQuery($int_reg);
                                    
                                    if($int_reg_res){

                                    /* หา txn */
                                        $last_sql = " SELECT last_insert_id() as last_ ";
                                        $last_res = dbQuery($last_sql);
                                        $last_row = dbFetchAssoc($last_res);
                                        $txn = $last_row['last_'];

                                    /* insert reg_room */
                                        $int_reg_room = " INSERT INTO reg_room SET txn='".$txn."',room='".$room."',date=curdate(),time=curtime()";
                                        $reg_room_res = dbQuery($int_reg_room);

                                    /* Gen vn */
                                        do {
                                            
                                            $vn_sql = " SELECT max(cast(vn as decimal)) as vn_max FROM reg WHERE date=curdate() ";
                                            $vn_res = dbQuery($vn_sql);
                                            $vn_row = dbFetchAssoc($vn_res);
                                            $vn_max = (int)$vn_row['vn_max']+1;
                                            $len_vn = strlen($vn_max);
                                            if((int)$len_vn <= 3){
                                                $vn = str_repeat("0",(3-$len_vn)).$vn_max;
                                            }else{
                                                $vn = $vn_max;
                                            }
                                            
                                            $upd_reg = " UPDATE reg SET vn='".$vn."' WHERE txn='".$txn."'";
                                            $upd_reg_res = dbQuery($upd_reg);

                                        }while ($upd_reg_res === FALSE);

                                    /* update mis */
                                        $upd_mis = " UPDATE mis SET num='".$num."',last_room='".$room."',last='".$date."',last_time='".$time."' WHERE hn='".$hn."'";
                                        $upd_mis_res = dbQuery($upd_mis);
                                
                                    /* insert reg_occ */
                                        $ins_reg_occ = " INSERT INTO reg_occ SET hn='".$hn."',name='".$name."',lname='".$lname."',date='".$date."',time='".$time."',room='".$room."'
                                                        ,u_borrow ='".$user_ad."',opd = 1,txn ='".$txn."',stat_ca ='".$type."'";
                                        $reg_occ_res = dbQuery($ins_reg_occ);

                                    /* insert reg_track */
                                        $track_time = substr(date('H:i'),0,5);
                                        $how = 'Register';
                                        $track = $room.'|'.$how.'|'.$track_time.'|'.$user_ad.';' ;

                                        $ins_reg_track = " INSERT INTO reg_track SET txn='".$txn."',date=curdate(),track='".$track."'";
                                        $reg_track_res = dbQuery($ins_reg_track);

                                        /* insert autoreg */
                                        $autoreg_sql = " SELECT name FROM config WHERE code='autoreg'";
                                        $autoreg_res = dbQuery($autoreg_sql);
                                        if(dbNumRows($autoreg_res) > 0) {
                                            $fname = trim($ttl).trim($name).' '.trim($lname);
                                            $ins_autoreg = " INSERT INTO autoreg SET hn='".$hn."',name='".$fname."',sex='".$sex."',born='".$born."',room='".$room."',date=curdate()
                                                            ,time=curtime(),type='".$type."',txn='".$txn."'
                                                        ";
                                            $ins_autoreg_res = dbQuery($ins_autoreg);
                                        }

                                        echo json_encode(array('message' => 'ลงทะเบียนสำเร็จ','status' => TRUE));
                                
                                    }else{
                                        echo json_encode(array('message' => 'ลงทะเบียนไม่สำเร็จ','status' => FALSE));
                                    }
                                    
                                }else{
                                    echo json_encode(array('message' => 'ไม่พบข้อมูลคนไข้','status' => FALSE));
                                }
                            }
                        }
                    }
                }else{
                    echo json_encode(array('message' => 'ไม่พบข้อมูลคนไข้','status' => FALSE));
                }
            }
        }else{
            echo json_encode(array('message' => 'ไม่พบสิทธิในฐานข้อมูล','status' => FALSE));
        }
    }

    function register_appointment($ser_fu){
        require_once('./controllers/misu.php');
        $sql_fu = " SELECT * FROM fu WHERE ser='".$ser_fu."' and date=curdate() ";
        $res_fu = dbQuery($sql_fu);
        if(dbNumRows($res_fu) > 0) {
            $row_fu = dbFetchAssoc($res_fu);
            $hn = $row_fu['HN'];
            $room = $row_fu['ROOM'];
            $comefor = $row_fu['COMEFOR'];
            $doc = $row_fu['DOC'];
            $ser_next = $row_fu['ser_next'];
            $ord_fu_user = $row_fu['user'];

            if(empty($room)){
                echo json_encode(array('message' => 'ไม่มีห้องตรวจที่ทำการนัด'.$room,'status' => FALSE));
            }else{
                if($ser_next>0){
                    echo json_encode(array('message' => 'คนไข้เลื่อนนัดแล้ว','status' => FALSE));
                }else{
                    /*เช็ค id ว่าเป็นผู้ป่วยของ รพ หรือไม่ */
                    $sql_mis = " SELECT t1.hn,t2.date FROM mis as t1 
                                LEFT JOIN mis_del as t2 on (t1.hn=t2.hn and t2.user_re='')
                                WHERE t1.hn='".$hn."' and isnull(t2.date)
                                ORDER BY t1.hn DESC 
                                LIMIT 1 
                                ";
                    $res_mis = dbQuery($sql_mis);
                    if(dbNumRows($res_mis) > 0) {
                        $row_mis = dbFetchAssoc($res_mis);
                        $hn = $row_mis['hn'];
    
                        /*เช็ค hn ว่าผู้ป่วยลงทะเบียนวันนี้ไปแล้วหรือไม่ */
                            $sql_reg_chk = " SELECT * FROM reg WHERE hn='".$hn."' and date=curdate() ";
                            $res_reg_chk = dbQuery($sql_reg_chk);
                            if(dbNumRows($res_reg_chk) > 0) {
                                echo json_encode(array('message' => 'มีการลงทะเบียนวันนี้แล้ว ไม่สามารถลงทะเบียนได้','status' => FALSE));
                            }else{
                                
                            /*เช็ค hn ว่าผู้ป่วยเป็นผู้ป่วยในหรือไม่ */
                                $sql_ipd = " SELECT * FROM ipd WHERE hn='".$hn."' ";
                                $res_ipd = dbQuery($sql_ipd);
                                if(dbNumRows($res_ipd) > 0) {
                                    echo json_encode(array('message' => 'เป็นผู้ป่วยใน ไม่สามารถลงทะเบียนได้','status' => FALSE));
                                }else{
                                
                                /*เช็ค hn ว่าผู้ป่วยเสียชีวิตหรือไม่ */
                                    $sql_death = " SELECT * FROM death WHERE hn='".$hn."' ";
                                    $res_death = dbQuery($sql_death);
                                    if(dbNumRows($res_death) > 0) {
                                        echo json_encode(array('message' => 'ผู้ป่วยเสียชีวิตแล้ว!','status' => FALSE));
                                    }else{
                                        
                                        $mis_sql = " SELECT * FROM mis WHERE hn='".$hn."' ";
                                        $mis_res = dbQuery($mis_sql);
                                        if(dbNumRows($mis_res) > 0) {
                                            $mis_row = dbFetchAssoc($mis_res);
                                            $ttl = $mis_row['ttl'];
                                            $name = $mis_row['name'];
                                            $lname = $mis_row['lname'];
                                            $sex = $mis_row['sex'];
                                            $born = $mis_row['born'];
                                            $id = $mis_row['id'];
                                            $addr = $mis_row['addr'];
                                            $moo = $mis_row['moo'];
                                            $acode = $mis_row['acode'];
                                            $hmain = $mis_row['hmain'];
                                            $hsub = $mis_row['hsub'];
                                            $rac = $mis_row['rac'];
                                            $nat = $mis_row['nat'];
                                            $rel = $mis_row['rel']; 
                                            $occ = $mis_row['occ'];
                                            $sta = $mis_row['sta'];
                                            $alg = $mis_row['alg'];
                                            $blood = $mis_row['blood'];
                                            $rh = $mis_row['rh'];
                                            $typem = $mis_row['typem'];
                                            $typei = $mis_row['typei'];
                                            $typex = $mis_row['typex'];
                                            $typen = $mis_row['typen'];
                                            $types = $mis_row['types'];
                                            $unders = $mis_row['unders'];
                                            $hn_old = $mis_row['hn_old'];
                
                                            $num = (int)$mis_row['num']+1;
                                            $type = 'A7';                             /* A7 สิทธิประกันสังคม*/ 
                                            $user_ad = '1';                           /* user ที่ลงทะเบียน*/ 
                                            $bill = 0;
                                            $grp_fee = 0;

                                            /*01 อายุรกรรม */
                                            $spc_sql = " SELECT spc FROM tbllu WHERE title='clin' and code='".$room."' ";
                                            $spc_res = dbQuery($spc_sql);
                                            if(dbNumRows($spc_res) > 0) {
                                                $spc_row = dbFetchAssoc($spc_res);
                                                $spc = $spc_row['spc'];
                                            }else{
                                                $spc = '';
                                            }

                                            /*หา วัน เวลา ปัจจุบัน ใน server */
                                            $ck_date_sql = " SELECT date_format(curdate(),'%Y-%m-%d') as curdate__,time_format(curtime(),'%H:%i:%s') as curtime__ ";
                                            $ck_date_res = dbQuery($ck_date_sql);
                                            $ck_date_row = dbFetchAssoc($ck_date_res);
                                            $date =  $ck_date_row['curdate__'];
                                            $time = substr($ck_date_row['curtime__'],0,5);

                                            $age = (int)substr($date,0,4) - (int)substr($born,0,4); /* คำนวณอายุ */

                                            if((int)$age > 25){
                                                $under_sql = " SELECT under FROM reg WHERE hn='".$hn."' ORDER BY date DESC LIMIT 1 ";
                                                $under_res = dbQuery($under_sql);
                                                if(dbNumRows($under_res) > 0) {
                                                    $under_row = dbFetchAssoc($under_res);
                                                    $under = $under_row['under'];
                                                }else{
                                                    $under = '';
                                                }
                                            }else{
                                                $under = '';
                                            }

                                            $ty_01 = trim(strpos($type,"$"));
                                            $ty_02 = trim(strpos($type,"*"));
                                            
                                            if($ty_01 != ""){
                                                $ty = 1;
                                            }elseif($ty_02 != ""){
                                                $ty = 2;
                                            }else{
                                                $ty = 3;
                                            }

                                            /*เช็ค id ว่าเป็นสิทธิประกันสังคมใน ssn ที่นำเข้าหรือไม่ */
                                            $sql_ssn = " SELECT * FROM ssn WHERE id='".$id."' ";
                                            $res_ssn = dbQuery($sql_ssn);
                                            if(dbNumRows($res_ssn) > 0) {
                                                $ssn_sql = " SELECT * FROM ssn WHERE id='".$id."' and end<curdate() ";
                                                $ssn_res = dbQuery($ssn_sql);
                                                if(dbNumRows($ssn_res) > 0) {
                                                    echo json_encode(array('message' => 'สิทธิประกันสังคมหมดอายุ','status' => FALSE));
                                                }else{
                                                    /* insert reg */
                                                    $int_reg = " INSERT INTO reg SET hn='".$hn."',ttl='".$ttl."',name='".$name."',lname='".$lname."',sex='".$sex."',comefor='".$comefor."'
                                                                ,age='".$age."',born='".$born."',room='".$room."',spc='".$spc."',date='".$date."',time='".$time."',num='".$num."',addr='".$addr."'
                                                                ,moo='".$moo."',acode='".$acode."',rac='".$rac."',nat='".$nat."',rel='".$rel."',occ='".$occ."',sta='".$sta."',id='".$id."'
                                                                ,hmain='".$hmain."',hsub='".$hsub."',type='".$type."',typem='".$typem."',typen='".$typen."',user_ad='".$user_ad."',typei='".$typei."'
                                                                ,typex='".$typex."',alg='".$alg."',blood='".$blood."',rh='".$rh."',types='".$types."',under='".$under."',unders='".$unders."',doc='".$doc."'                           
                                                                ";
                                                    $int_reg_res = dbQuery($int_reg);
                                                    if($int_reg_res){

                                                    /* หา txn */
                                                        $last_sql = " SELECT last_insert_id() as last_ ";
                                                        $last_res = dbQuery($last_sql);
                                                        $last_row = dbFetchAssoc($last_res);
                                                        $txn = $last_row['last_'];

                                                        $upd_fu = " UPDATE fu SET txn='".$txn."' WHERE ser='".$ser_fu."'";
                                                        $upd_fu_res = dbQuery($upd_fu);

                                                    /* insert reg_room */
                                                        $int_reg_room = " INSERT INTO reg_room SET txn='".$txn."',room='".$room."',date=curdate(),time=curtime(),doc='".$doc."'";
                                                        $reg_room_res = dbQuery($int_reg_room);

                                                    /* Gen vn */
                                                        do {
                                                            
                                                            $vn_sql = " SELECT max(cast(vn as decimal)) as vn_max FROM reg WHERE date=curdate() ";
                                                            $vn_res = dbQuery($vn_sql);
                                                            $vn_row = dbFetchAssoc($vn_res);
                                                            $vn_max = (int)$vn_row['vn_max']+1;
                                                            $len_vn = strlen($vn_max);
                                                            if((int)$len_vn <= 3){
                                                                $vn = str_repeat("0",(3-$len_vn)).$vn_max;
                                                            }else{
                                                                $vn = $vn_max;
                                                            }
                                                            
                                                            $upd_reg = " UPDATE reg SET vn='".$vn."' WHERE txn='".$txn."'";
                                                            $upd_reg_res = dbQuery($upd_reg);

                                                        }while ($upd_reg_res === FALSE);

                                                        /* update mis */
                                                            $upd_mis = " UPDATE mis SET num='".$num."',last_room='".$room."',last='".$date."',last_time='".$time."' WHERE hn='".$hn."'";
                                                            $upd_mis_res = dbQuery($upd_mis);
                                                    
                                                        /* insert reg_occ */
                                                            $ins_reg_occ = " INSERT INTO reg_occ SET hn='".$hn."',name='".$name."',lname='".$lname."',date='".$date."',time='".$time."',room='".$room."'
                                                                            ,u_borrow ='".$user_ad."',opd = 1,txn ='".$txn."',stat_ca ='".$type."'";
                                                            $reg_occ_res = dbQuery($ins_reg_occ);

                                                        /* insert reg_track */
                                                            $track_time = substr(date('H:i'),0,5);
                                                            $how = 'Register';
                                                            $track = $room.'|'.$how.'|'.$track_time.'|'.$user_ad.';' ;

                                                            $ins_reg_track = " INSERT INTO reg_track SET txn='".$txn."',date=curdate(),track='".$track."'";
                                                            $reg_track_res = dbQuery($ins_reg_track);

                                                        /* insert autoreg */
                                                            $autoreg_sql = " SELECT name FROM config WHERE code='autoreg'";
                                                            $autoreg_res = dbQuery($autoreg_sql);
                                                            if(dbNumRows($autoreg_res) > 0) {
                                                                $fname = trim($ttl).trim($name).' '.trim($lname);
                                                                $ins_autoreg = " INSERT INTO autoreg SET hn='".$hn."',name='".$fname."',sex='".$sex."',born='".$born."',room='".$room."',date=curdate()
                                                                                ,time=curtime(),type='".$type."',txn='".$txn."'
                                                                            ";
                                                                $ins_autoreg_res = dbQuery($ins_autoreg);
                                                            }

                                                            $ord_fu_sql = " SELECT * FROM ord_fu WHERE txn='".$ser_fu."'";
                                                            $ord_fu_res = dbQuery($ord_fu_sql);
                                                            if(dbNumRows($ord_fu_res) > 0) {
                                                                $_D = FALSE;
                                                                $_L = FALSE;
                                                                $_R = FALSE;
                                                                $_E = FALSE;
                                                                while($ord_fu_row = dbFetchAssoc($ord_fu_res)) {
                                                                    $ord_fu_code = trim($ord_fu_row['CODE']);
                                                                    $ord_fu_name = $ord_fu_row['NAME'];
                                                                    $ord_fu_amt = (int)$ord_fu_row['AMT'];
                                                                    $ord_fu_sig = trim($ord_fu_row['SIG']);
                                                                    $ord_fu_sigadd = trim($ord_fu_row['SIGADD']);
                                                                    $ord_fu_signote = trim($ord_fu_row['SIGNOTE']);
                                                                    $ord_fu_lst = $ord_fu_row['LST'];
                                                                    $ord_fu_what = $ord_fu_row['WHAT'];

                                                                    $array_ord_fu_lst = array();    
                                                                    $explode_ord_fu_lst = explode(";",$ord_fu_lst);
                                                                    $count_ord_fu_lst = count($explode_ord_fu_lst);
                                                                    for ($i=0; $i < $count_ord_fu_lst; $i++) {
                                                                        $array_ord_fu_lst[] = $explode_ord_fu_lst[$i];
                                                                    }

                                                                    switch ($ord_fu_what) {

                                                                        case "D":

                                                                                $stock = lcname('stock','clin','code',$room);

                                                                                if(!empty($stock)){
                                                                                    $sup_s01_sql = " SELECT name,amt ,pri,pri1,pri2,pri3,grp,ex,typ,can_zero FROM sup_s01 WHERE code='".$ord_fu_code."' and stock='".$stock."'";
                                                                                    $sup_s01_res = dbQuery($sup_s01_sql);
                                                                                    if(dbNumRows($sup_s01_res) > 0) {
                                                                                        $dru_row = dbFetchAssoc($sup_s01_res);
                                                                                        $dru_name = $dru_row['name'];
                                                                                        $dru_amt = (int)$dru_row['amt'];
                                                                                        $dru_pri = pudkn($dru_row['pri'] * $ord_fu_amt);
                                                                                        $dru_pri1 = pudkn($dru_row['pri1'] * $ord_fu_amt);
                                                                                        $dru_pri2 = pudkn($dru_row['pri2'] * $ord_fu_amt);
                                                                                        $dru_pri3 = pudkn($dru_row['pri3'] * $ord_fu_amt);
                                                                                        $dru_grp = $dru_row['grp'];
                                                                                        $dru_ex = $dru_row['ex'];
                                                                                        $dru_typ = $dru_row['typ'];
                                                                                        $dru_can_zero = (int)$dru_row['can_zero'];

                                                                                        if($dru_amt > $ord_fu_amt){
                                                                                                                                                                                    
                                                                                            $lotno = cut_stock($ord_fu_code,$stock,$ord_fu_amt); 
                                                                                            $cost_lot = 0;
                                                                                            if(!empty($lotno)){
                                                                                                $lotno_x = explode(";",$lotno);
                                                                                                $lotnox = $lotno_x[0];

                                                                                                $ck_cost_lot_sql = " SELECT cost FROM sup_s01lot WHERE code='".$ord_fu_code."' and stock='".$stock."' and lotno='".$lotnox."'";
                                                                                                $ck_cost_lot_res = dbQuery($ck_cost_lot_sql);
                                                                                                if(dbNumRows($ck_cost_lot_res) > 0) {
                                                                                                    $ck_cost_lo_row = dbFetchAssoc($ck_cost_lot_res);
                                                                                                    $cost_lot = $ck_cost_lo_row['cost'];
                                                                                                }else{
                                                                                                    $cost_lot = 0;
                                                                                                }

                                                                                            }

                                                                                            $ins_dru = " INSERT INTO dru SET txn='".$txn."',code='".$ord_fu_code."',name='".$dru_name."',amt='".$ord_fu_amt."'
                                                                                                        ,date=curdate(),time=curtime(),sig='".$ord_fu_sig."',sigadd='".$ord_fu_sigadd."',signote='".$ord_fu_signote."'
                                                                                                        ,pri='".$dru_pri."',pri1='".$dru_pri1."',pri2='".$dru_pri2."',pri3='".$dru_pri3."',grp='".$dru_grp."',ex='".$dru_ex."'
                                                                                                        ,typ='".$dru_typ."',doc='',room='".$room."',stock='".$stock."',lotno='".$lotno."',cost='".$cost_lot."'
                                                                                                        ";
                                                                                            $dru_res = dbQuery($ins_dru);
                                                                                            
                                                                                            $_D = TRUE;
                                                                                        }
                                                                                    }
                                                                                }
                                                        
                                                                            break;
                                                                        case "L":

                                                                            $c_labx_sql = " SELECT * FROM labx WHERE code='".$ord_fu_code."' and trim(code)<>' ' and  cancel=0 order by code,suffix";
                                                                            $c_labx_res = dbQuery($c_labx_sql);
                                                                            if(dbNumRows($c_labx_res) > 0) {
                                                                                $c_labx_row = dbFetchAssoc($c_labx_res);
                                                                                $_mpri = $c_labx_row['pri'];
                                                                                $_mpri1 = $c_labx_row['pri1'];
                                                                                $_mpri2 = $c_labx_row['pri2'];
                                                                                $_mpri3 = $c_labx_row['pri3'];
                                                                                $_labx_name = $c_labx_row['name'];
                                                                                $_labx_grp = $c_labx_row['grp'];
                                                                                $_labx_ex = $c_labx_row['ex'];
                                                                                $_labx_typ = $c_labx_row['typ'];
                                                        
                                                                                $array_labx_ser= array();
                                                                                $array_labx_item= array();
                                                                                $array_labx_pri= array();
                                                                                $array_labx_pri1= array();
                                                                                $array_labx_pri2= array();
                                                                                $array_labx_pri3= array();
                                                                                $array_labx_labcode= array();
                                                        
                                                                                $labx_ser = "";
                                                                                $labx_item = "";
                                                                                $labx_pri = "";
                                                                                $labx_pri1 = "";
                                                                                $labx_pri2 = "";
                                                                                $labx_pri3 = "";
                                                                                $labx_labcode = "";
                                                        
                                                                                $lab_formx_sql = " SELECT * FROM labx WHERE title='".$ord_fu_code."' and trim(item)<>' ' and  cancel=0 ORDER BY suffix,ser ";
                                                                                $lab_formx_res = dbQuery($lab_formx_sql);
                                                                                if(dbNumRows($lab_formx_res) > 0) {
                                                                                    while($lab_fromx_row = dbFetchAssoc($lab_formx_res)) {
                                                                                        $array_labx_ser[] = $lab_fromx_row['ser'];
                                                                                        $array_labx_item[] = trim($lab_fromx_row['item']);
                                                                                        $array_labx_pri[] = $lab_fromx_row['pri'];
                                                                                        $array_labx_pri1[] = $lab_fromx_row['pri1'];
                                                                                        $array_labx_pri2[] = $lab_fromx_row['pri2'];
                                                                                        $array_labx_pri3[] = $lab_fromx_row['pri3'];
                                                                                        $array_labx_labcode[] = trim($lab_fromx_row['labcode']);
                                                                                    }
                                                        
                                                                                    $count_labx_ser = count($array_labx_ser);
                                                                                    for ($i=0; $i < $count_labx_ser; $i++) {
                                                                                        $ser_labx = trim((string)$array_labx_ser[$i]);
                                                        
                                                                                        $labx_ser = $labx_ser.$ser_labx.";";
                                                                                    }
                                                                                        $labx_ser = substr($labx_ser,0,strlen($labx_ser)-1);
                                                        
                                                                                    $count_labx_item = count($array_labx_item);
                                                                                    for ($i=0; $i < $count_labx_item; $i++) {
                                                                                        $item_labx = trim((string)$array_labx_item[$i]);
                                                        
                                                                                        $labx_item = $labx_item.$item_labx.";";
                                                                                    }
                                                                                        $labx_item = substr($labx_item,0,strlen($labx_item)-1);
                                                                                    
                                                                                    $count_labx_pri = count($array_labx_pri);
                                                                                    for ($i=0; $i < $count_labx_pri; $i++) {
                                                                                        $pri_labx = trim((string)$array_labx_pri[$i]);
                                                        
                                                                                        $labx_pri = $labx_pri.$pri_labx.";";
                                                                                    }
                                                                                    $labx_pri = substr($labx_pri,0,strlen($labx_pri)-1);
                                                        
                                                                                    $count_labx_pri1 = count($array_labx_pri1);
                                                                                    for ($i=0; $i < $count_labx_pri1; $i++) {
                                                                                        $pri1_labx = trim((string)$array_labx_pri1[$i]);
                                                        
                                                                                        $labx_pri1 = $labx_pri1.$pri1_labx.";";
                                                                                    }
                                                                                    $labx_pri1 = substr($labx_pri1,0,strlen($labx_pri1)-1);
                                                        
                                                                                    $count_labx_pri2 = count($array_labx_pri2);
                                                                                    for ($i=0; $i < $count_labx_pri2; $i++) {
                                                                                        $pri2_labx = trim((string)$array_labx_pri2[$i]);
                                                        
                                                                                        $labx_pri2 = $labx_pri2.$pri2_labx.";";
                                                                                    }
                                                                                    $labx_pri2 = substr($labx_pri2,0,strlen($labx_pri2)-1);
                                                                               
                                                                                    $count_labx_pri3 = count($array_labx_pri3);
                                                                                    for ($i=0; $i < $count_labx_pri3; $i++) {
                                                                                        $pri3_labx = trim((string)$array_labx_pri3[$i]);
                                                        
                                                                                        $labx_pri3 = $labx_pri3.$pri3_labx.";";
                                                                                    }
                                                                                    $labx_pri3 = substr($labx_pri3,0,strlen($labx_pri3)-1);
                                                        
                                                                                    $count_labx_labcode = count($array_labx_labcode);
                                                                                    for ($i=0; $i < $count_labx_labcode; $i++) {
                                                                                        $labcode_labx = trim((string)$array_labx_labcode[$i]);
                                                        
                                                                                        $labx_labcode = $labx_labcode.$labcode_labx.";";
                                                                                    }
                                                                                    $labx_labcode = substr($labx_labcode,0,strlen($labx_labcode)-1);
                                                                               
                                                                                    // print_r($labx_labcode);
                                                                                   
                                                                                }
                                                        
                                                                                $explode_labx_ser = explode(";",$labx_ser);
                                                                                $explode_labx_item =explode(";",$labx_item);
                                                                                $explode_labx_pri = explode(";",$labx_pri);
                                                                                $explode_labx_pri1 = explode(";",$labx_pri1);
                                                                                $explode_labx_pri2 = explode(";",$labx_pri2);
                                                                                $explode_labx_pri3 = explode(";",$labx_pri3);
                                                                                $explode_labx_labcode = explode(";",$labx_labcode);
                                                                                
                                                                                $_ser_lst = "";
                                                                                $_pri_lst = "";
                                                                                $_labcode = "";
                                                        
                                                                                $explode_ord_fu_lst = explode(";",$ord_fu_lst);
                                                                                $count_ord_fu_lst = count($explode_ord_fu_lst);
                                                        
                                                                                $array_lab_ser= array();
                                                                                $array_lab_item= array();
                                                                                $array_lab_pri= array();
                                                                                $array_lab_pri1= array();
                                                                                $array_lab_pri2= array();
                                                                                $array_lab_pri3= array();
                                                                                $array_lab_labcode= array();
                                                        
                                                                                for ($i=0; $i < $count_ord_fu_lst; $i++) {
                                                                                    $item = $explode_ord_fu_lst[$i];
                                                            
                                                                                    $lab_form_sql = " SELECT * FROM labx WHERE title='".$ord_fu_code."' and item='".$item."' ORDER BY suffix,ser ";
                                                                                    $lab_form_res = dbQuery($lab_form_sql);
                                                                                    if(dbNumRows($lab_form_res) > 0) {
                                                                                        $lab_from_row = dbFetchAssoc($lab_form_res);
                                                                                        $array_lab_ser[] = $lab_from_row['ser'];
                                                                                        $array_lab_item[] = trim($lab_from_row['item']);
                                                                                        $array_lab_pri[] = $lab_from_row['pri'];
                                                                                        $array_lab_pri1[] = $lab_from_row['pri1'];
                                                                                        $array_lab_pri2[] = $lab_from_row['pri2'];
                                                                                        $array_lab_pri3[] = $lab_from_row['pri3'];
                                                                                        $array_lab_labcode[] = trim($lab_from_row['labcode']);
                                                                                    }
                                                                                }
                                                        
                                                                                $_lst = "";
                                                                                $count_array_lab_item = count($array_lab_item);
                                                                                for ($j=0; $j < $count_array_lab_item; $j++) {
                                                                                    $ser_array_lab = trim((string)$array_lab_ser[$j]);
                                                                                    $item_array_lab = trim((string)$array_lab_item[$j]);
                                                                                    $pri_array_lab = trim((string)$array_lab_pri[$j]);
                                                                                    $labcode_array_lab = trim((string)$array_lab_labcode[$j]);
                                                        
                                                                                    $_lst = $_lst.$item_array_lab.";";
                                                                                    $_ser_lst = $_ser_lst.$ser_array_lab.";";
                                                                                    $_pri_lst = $_pri_lst.$pri_array_lab.";";
                                                                                    $_labcode = $_labcode.$labcode_array_lab.";";
                                                                                }
                                                        
                                                                                $_lst = substr($_lst,0,strlen($_lst)-1);
                                                                                $_ser_lst = substr($_ser_lst,0,strlen($_ser_lst)-1);
                                                                                $_pri_lst = substr($_pri_lst,0,strlen($_pri_lst)-1);
                                                                                
                                                                                $_pri = 0; $_pri1 = 0; $_pri2 = 0; $_pri3 = 0;
                                                                                $_prih = 0; $_pri1h = 0; $_pri2h = 0; $_pri3h = 0; 
                                                                                $_prid = 0; $_pri1d = 0; $_pri2d = 0; $_pri3d = 0;
                                                        
                                                                                $explode_lstx = explode(";",$_lst);
                                                                                $count_lstx = count($explode_lstx);
                                                        
                                                                                for ($i=0; $i < $count_lstx; $i++) {
                                                                                    $_item = $explode_lstx[$i];
                                                                                    $_item_add = trim(strpos($_item,"@"));
                                                                                    $_item_dot = trim(strpos($_item,"..."));
                                                        
                                                                                    if($_item_add === ""){
                                                                                        $_item_search = array_search($_item,$explode_labx_item);
                                                                                        if($explode_labx_item[$_item_search] !== ""){
                                                                                            $__pri = $array_lab_pri[$_item_search];
                                                                                            $__pri1 = $array_lab_pri1[$_item_search];
                                                                                            $__pri2 = $array_lab_pri2[$_item_search];
                                                                                            $__pri3 = $array_lab_pri3[$_item_search];
                                                                                            $_items = $array_lab_item[$_item_search];
                                                                                            $_item_next = $array_lab_item[$_item_search+1];
                                                                                            $_item_add_next = trim(strpos($_item_next,"@"));
                                                                                            $_item_dot_next = trim(strpos($_item_next,"..."));
                                                                                            // print_r($__pri);
                                                                                            if($_item_dot != "" && $__pri>0 && $_prih==0){
                                                                                                $_prih = $__pri; 
                                                                                                $_pri1h = $__pri1; 
                                                                                                $_pri2h = $__pri2; 
                                                                                                $_pri3h = $__pri3;
                                                                                                // print_r("1");
                                                                                            }else{
                                                                                                if($_prih>0){
                                                                                                    $_prid = $_prid+$__pri; 
                                                                                                    $_pri1d = $_pri1d+$__pri1; 
                                                                                                    $_pri2d = $_pri2d+$__pri2; 
                                                                                                    $_pri3d = $_pri3d+$__pri3;
                                                                                                    // print_r("2");
                                                                                                    if($_item_dot != "" || $_item_add_next != "" || $_item_dot_next != ""){
                                                                                                        // print_r("5");
                                                                                                        if($_prid>$_prih){
                                                                                                            $_pri = $_pri+$_prih; 
                                                                                                        }else{
                                                                                                            $_pri = $_pri+$_prid;
                                                                                                        }
                                                                                                        
                                                                                                        if($_pri1d>$_pri1h){
                                                                                                            $_pri1 = $_pri1+$_pri1h; 
                                                                                                        }else{
                                                                                                            $_pri1 = $_pri1+$_pri1d;
                                                                                                        }
                                                        
                                                                                                        if($_pri2d>$_pri2h){
                                                                                                            $_pri2 = $_pri2+$_pri2h; 
                                                                                                        }else{
                                                                                                            $_pri2 = $_pri2+$_pri2d;
                                                                                                        }
                                                        
                                                                                                        if($_pri3d>$_pri3h){
                                                                                                            $_pri3 = $_pri3+$_pri3h; 
                                                                                                        }else{
                                                                                                            $_pri3 = $_pri3+$_pri3d;
                                                                                                        }
                                                                                                        
                                                                                                        $_prih = 0; $_pri1h = 0; $_pri2h = 0; $_pri3h = 0; 
                                                                                                        $_prid = 0; $_pri1d = 0; $_pri2d = 0; $_pri3d = 0;
                                                        
                                                                                                    }
                                                                                                }else{
                                                                                                    // print_r("3");
                                                                                                    $_pri = $_pri+$__pri; 
                                                                                                    $_pri1 = $_pri1+$__pri1; 
                                                                                                    $_pri2 = $_pri2+$__pri2; 
                                                                                                    $_pri3 = $_pri3+$__pri3;

                                                                                                    $_item_next = $_items;
                                                                                                    $_item_add_next = trim(strpos($_item_next,"@"));
                                                                                                    $_item_dot_next = trim(strpos($_item_next,"..."));
                                                        
                                                                                                }
                                                                                            }
                                                                                            
                                                                                            if($_item_dot_next != "" && $_prih>0 && $_prid==0){
                                                                                                // print_r("4");
                                                                                                $_prih = 0; $_pri1h = 0; $_pri2h = 0; $_pri3h = 0; 
                                                                                                $_prid = 0; $_pri1d = 0; $_pri2d = 0; $_pri3d = 0;
                                                                                            }
                                                                                        }
                                                                                    }
                                                                                }
                                                        
                                                                                if((($_pri>$_mpri) || $_pri==0) && $_mpri>0){
                                                                                    $_pri = $_mpri; 
                                                                                }else{
                                                                                    $_pri = $_pri;
                                                                                }
                                                            
                                                                                if(($_pri1>$_mpri1 || $_pri1==0) && $_mpri1>0){
                                                                                    $_pri1 = $_mpri1; 
                                                                                }else{
                                                                                    $_pri1 = $_pri1;
                                                                                }
                                                            
                                                                                if(($_pri2>$_mpri2 || $_pri2==0) && $_mpri2>0){
                                                                                    $_pri2 = $_mpri2; 
                                                                                }else{
                                                                                    $_pri2 = $_pri2;
                                                                                }
                                                            
                                                                                if(($_pri3>$_mpri3 || $_pri3==0) && $_mpri3>0){
                                                                                    $_pri3 = $_mpri3; 
                                                                                }else{
                                                                                    $_pri3 = $_pri3;
                                                                                }
                                                        
                                                                                // print_r($_pri);
                                                                                $_ser_lst = $_labcode.'|'.$_ser_lst;

                                                                                $ins_lab = " INSERT INTO lab SET txn='".$txn."',code='".$ord_fu_code."',name='".$_labx_name."',amt='".$ord_fu_amt."'
                                                                                            ,date=curdate(),time=curtime(),pri='".$_pri."',pri1='".$_pri1."',pri2='".$_pri2."',pri3='".$_pri3."'
                                                                                            ,grp='".$_labx_grp."',ex='".$_labx_ex."',typ='".$_labx_typ."',lst='".$_lst."',doc='".$ord_fu_user."',room='".$room."'
                                                                                            ,ser_lst='".$_ser_lst."',pri_lst='".$_pri_lst."',df_doc='".$doc."'
                                                                                            ";
                                                                                $lab_res = dbQuery($ins_lab);

                                                                                $_L = TRUE;
                                                                            }
                                                                                    
                                                                            break;

                                                                        case "R":
                                                                            $radx_sql = " SELECT * FROM radx WHERE code='".$ord_fu_code."' ";
                                                                            $radx_res = dbQuery($radx_sql);
                                                                            if(dbNumRows($radx_res) > 0) {
                                                                                $radx_row = dbFetchAssoc($radx_res);
                                                                                $_radx_grp = $radx_row['grp'];
                                                                                $_radx_ex = $radx_row['ex'];
                                                                                $_radx_typ = $radx_row['typ'];
                                                            
                                                                                $rad_pri = 0; $rad_pri1 = 0; $rad_pri2 = 0; $rad_pri3 = 0; $rad_amt = 0;
                                                                                $rad_pri_lst = "";
                                                                                $rad_str = "";
                                                                                $radxi_sql = " SELECT * FROM radxi WHERE code='".$ord_fu_code."' ";
                                                                                $radxi_res = dbQuery($radxi_sql);
                                                                                if(dbNumRows($radxi_res) > 0) {
                                                                                    while($radxi_row = dbFetchAssoc($radxi_res)) {
                                                                                        $rad_name = trim($radxi_row['name']);
                                                                                        $rad_search = array_search($rad_name,$array_ord_fu_lst);
                                                                                        if($rad_name === $array_ord_fu_lst[$rad_search]){
                                                                                            $rad_str = $rad_str.$rad_name.";";
                                                                                            $rad_pri = $rad_pri+$radxi_row['pri']; 
                                                                                            $rad_pri1 = $rad_pri1+$radxi_row['pri1']; 
                                                                                            $rad_pri2 = $rad_pri2+$radxi_row['pri2']; 
                                                                                            $rad_pri3 = $rad_pri3+$radxi_row['pri3'];  
                                                                                            $rad_amt = $rad_amt+$radxi_row['film_use'];
                                                                                            $rad_pri_lst = $rad_pri_lst.$radxi_row['pri'].";";
                                                                                            // print_r($array_ord_fu_lst[$rad_search]);
                                                                                        }  
                                                                                    }
                                                                                    $rad_str = substr($rad_str,0,strlen($rad_str)-1);
                                                                                    $rad_pri_lst = substr($rad_pri_lst,0,strlen($rad_pri_lst)-1);

                                                                                    $ins_rad = " INSERT INTO rad SET txn='".$txn."',code='".$ord_fu_code."',name='".$ord_fu_name."',amt='".$rad_amt."'
                                                                                                ,date=curdate(),time=curtime(),pri='".$rad_pri."',pri1='".$rad_pri1."',pri2='".$rad_pri2."',pri3='".$rad_pri3."'
                                                                                                ,grp='".$_radx_grp."',ex='".$_radx_ex."',typ='".$_radx_typ."',lst='".$rad_str."',doc='".$ord_fu_user."',room='".$room."'
                                                                                                ,pri_lst='".$rad_pri_lst."'
                                                                                                ";
                                                                                    $lab_rad = dbQuery($ins_rad);
                                                                                    
                                                                                    $_R = TRUE;
                                                                                }
                                                                            }
                                                                            break;
                                                                        case "E":

                                                                            $etcx_sql = " SELECT * FROM etcx WHERE code='".$ord_fu_code."'";
                                                                            $etcx_res = dbQuery($etcx_sql);
                                                                            if(dbNumRows($etcx_res) > 0) {
                                                                                $etcx_row = dbFetchAssoc($etcx_res);
                                                                                $etcx_name = $etcx_row['name'];
                                                                                $etcx_pri = pudkn($etcx_row['pri'] * $ord_fu_amt);
                                                                                $etcx_pri1 = pudkn($etcx_row['pri1'] * $ord_fu_amt);
                                                                                $etcx_pri2 = pudkn($etcx_row['pri2'] * $ord_fu_amt);
                                                                                $etcx_pri3 = pudkn($etcx_row['pri3'] * $ord_fu_amt);
                                                                                $etcx_grp = $etcx_row['grp'];
                                                                                $etcx_ex = $etcx_row['ex'];
                                                                                $etcx_typ = $etcx_row['typ'];

                                                                                $ins_etc = " INSERT INTO etc SET txn='".$txn."',code='".$ord_fu_code."',name='".$etcx_name."',amt='".$ord_fu_amt."'
                                                                                            ,date=curdate(),time=curtime(),pri='".$etcx_pri."',pri1='".$etcx_pri1."',pri2='".$etcx_pri2."',pri3='".$etcx_pri3."'
                                                                                            ,grp='".$etcx_grp."',ex='".$etcx_ex."',typ='".$etcx_typ."',doc='',room='".$room."'
                                                                                            ";
                                                                                $etc_res = dbQuery($ins_etc);

                                                                                $_E = TRUE;
                                                                            }
                                                                            break;

                                                                        default:
                                                                            break;
                                                                    }
                                                                }
                                                                    /*Start Lab */
                                                                        $_Lprint = 1;
                                                                        // $_Ln = 0;
                                                                        $_Lpri = 0;
                                                                        $_Lpri1 = 0;
                                                                        $_Lpri2 = 0;
                                                                        $_Ltot = 0;
                                                                        $Ltem_ser = array();
                                                                        $Ltem_code = array();
                                                                        $Ltem_name = array();
                                                                        $Ltem_ex = array();
                                                                        $Ltem_mcode = array();
                                                                        $Ltem_lst = array();
                                                                        $Ltem_ser_lst = array();
                                                                        $Ltem_spc_code = array();
                                                                        $Ltem_amt = array();
                                                                        $Ltem_sql = " SELECT * FROM lab WHERE txn='".$txn."' and print=0 ORDER BY ex,mcode "; 
                                                                        $Ltem_res = dbQuery($Ltem_sql);
                                                                        if(dbNumRows($Ltem_res) > 0) {
                                                            
                                                                            while($Ltem_row = dbFetchAssoc($Ltem_res)) {
                                                                                $_Lpri = $_Lpri+$Ltem_row['PRI'];
                                                                                $_Lpri1 = $_Lpri1+$Ltem_row['PRI1'];
                                                                                $_Lpri2 = $_Lpri2+$Ltem_row['PRI2'];
                                                                                $Ltem_ser[] = $Ltem_row['ser'];
                                                                                $Ltem_code[] = $Ltem_row['CODE'];
                                                                                $Ltem_name[] = $Ltem_row['NAME'];
                                                                                $Ltem_mcode[] = $Ltem_row['mcode'];
                                                                                $Ltem_lst[] = $Ltem_row['LST'];
                                                                                $Ltem_ser_lst[] = $Ltem_row['ser_lst'];
                                                                                $Ltem_spc_code[] = $Ltem_row['spc_code'];
                                                                                $Ltem_amt[] = $Ltem_row['AMT'];
                                                                                $Ltem_ex[] = trim((string)cname('separate','labx','code',$Ltem_row['CODE']));
                                                                            }
                                                                            
                                                                            // print_r($Ltem_ex);
                                                            
                                                                            switch ($ty) {
                                                                                case 1:
                                                                                    $_Ltot = $_Lpri1;
                                                                                break;
                                                                                case 2:
                                                                                    $_Ltot = $_Lpri2;
                                                                                break;
                                                                                default:
                                                                                    $_Ltot = $_Lpri;
                                                                            }
                                                                            //   print_r($_Ltot);
                                                            
                                                                            $upd_sql = " UPDATE lab SET print='".$_Lprint."' WHERE txn='".$txn."' and print=0 "; 
                                                                            $upd_res = dbQuery($upd_sql);
                                                            
                                                                            $ins_bill = " INSERT INTO bill SET txn='".$txn."',date=curdate(),time=curtime(),desc_='LAB',print='".$_Lprint."',price='".$_Lpri."',type='".$type."',tot='".$_Ltot."' "; 
                                                                            $res_bill = dbQuery($ins_bill);
                                                                            $_depart="O";	
                                                                            $_labname = "";
                                                                            $nn = 1;
                                                                            $count_code = count($Ltem_code);
                                                                            for ($i=0; $i < $count_code; $i++) {
                                                            
                                                                                $_labname = trim($Ltem_name[$i]);
                                                                                $_lst = trim($Ltem_lst[$i]);
                                                                                $_ser = $Ltem_ser[$i];
                                                                                $_labser = '1'.str_repeat("0", (9 - strlen($_ser))).$_ser;
                                                                                $_labitem = $nn.'/'.$count_code;
                                                                                $_ser_lst = $Ltem_ser_lst[$i];
                                                                                $_spc_code = $Ltem_spc_code[$i];
                                                                                $_mcode = $Ltem_mcode[$i];
                                                                                $_room_x = trim($_mcode);
                                                                                $_roomsend = lcname('typ','lab_mt_code','code',$_room_x);
                                                            
                                                                                $temp_lab_insert_sql = " SELECT '',if(
                                                                                                        concat(concat((year(now())+543)-2500),right(concat('00000000',max(right(laborderno,8))+1),8)) is null,
                                                                                                        concat(((year(now())+543)-2500),'00000001'),
                                                                                                        concat(concat((year(now())+543)-2500),right(concat('00000000',max(right(laborderno,8))+1),8))
                                                                                                        ) as laborderno
                                                                                                        FROM lab_req 
                                                                                                        WHERE
                                                                                                        date>=concat(year(now()),'0101') and date<=concat(year(now()),'1231')
                                                                                                    "; 
                                                                                $temp_lab_insert_res = dbQuery($temp_lab_insert_sql);
                                                                                $row_temp_lab_insert = dbFetchAssoc($temp_lab_insert_res);
                                                                                $_laborderno = $row_temp_lab_insert['laborderno'];
                                                            
                                                                                $ins_lab_req = " INSERT INTO lab_req SET labname='".$_labname."',laborderno='".$_laborderno."',dep='".$_depart."',print='".$_Lprint."',txn='".$txn."',date=curdate(),time=curtime() "; 
                                                                                $res_lab_req = dbQuery($ins_lab_req);
                                                                                if($res_lab_req){
                                                                                    $Last_sql = " SELECT last_insert_id() as last_ "; 
                                                                                    $Last_res = dbQuery($Last_sql);
                                                                                    $Last_row = dbFetchAssoc($Last_res);
                                                                                    $_ser_labreq=$Last_row['last_'];
                                                                                }
                                                                            
                                                                                
                                                                                
                                                                                $_code_lst = "";
                                                                                $count_ser_lst = count($_ser_lst);
                                                                                $explode_ser_lst = explode('|',$_ser_lst);
                                                                                for ($j=0; $j < $count_code; $j++) {
                                                                                    $_ser_labx = $_ser_lst[$j];
                                                                                    $sql_ser_labx = " SELECT labcode FROM labx WHERE ser='".$_ser_labx."' "; 
                                                                                    $res_ser_labx = dbQuery($sql_ser_labx);
                                                                                    if(dbNumRows($res_ser_labx) > 0) {
                                                                                        $row_ser_labx = dbFetchAssoc($res_ser_labx);
                                                                                        $_code_lst = $_code_lst.trim($row_ser_labx['labcode']).";";
                                                                                    }
                                                                                }
                                                                                $_code_lst = substr($_code_lst,0,strlen($_code_lst)-1);
                                                            
                                                                                $upd_lab_sql = " UPDATE lab SET item='".$_labitem."',laborderno='".$_laborderno."' WHERE ser='".$_ser."' "; 
                                                                                $upd_lab_res = dbQuery($upd_lab_sql);
                                                            
                                                                                $ins_lablbl_sql = " INSERT INTO lablbl SET txn='".$txn."',print='".$_Lprint."',item='".$_labitem."',ttl='".$ttl."',fname='".$name."'
                                                                                                    ,lname='".$lname."',hn='".$hn."',sex='".$sex."',date=curdate(),time=curtime(),born='".$born."',code='".$Ltem_code[$i]."',name='".$Ltem_name[$i]."'
                                                                                                    ,amt='".$Ltem_amt[$i]."',room='".$room."',type='".$type."',doc='".$ord_fu_user."',lst='".$_lst."',labser='".$_labser."',laborderno='".$_laborderno."'
                                                                                                    ,roomsend='".$_roomsend."',spc_code='".$_spc_code."',code_lst='".$_code_lst."',ser_labreq='".$_ser_labreq."',serlab='".$_ser."'
                                                                                                "; 
                                                                                $lablbl_res = dbQuery($ins_lablbl_sql);
                                                            
                                                                                // print_r($_code_lst);
                                                                                $nn = $nn+1;
                                                                            }
                                                                            // print_r($count_code);
                                                                        }
                                                                    /*End Lab */
                                                                        
                                                                    /*Start Rad */
                                                                        $_Rprint = 1;
                                                                        // $_Rn = 0;
                                                                        $_Rpri = 0;
                                                                        $_Rpri1 = 0;
                                                                        $_Rpri2 = 0;
                                                                        $_Rtot = 0;
                                                                        $Rtem_code = array();
                                                                        $Rtem_name = array();
                                                                        $Rtem_ser = array();
                                                                        $Rtem_df_doc = array();
                                                                        $Rtem_lst = array();
                                                                        $Rtem_amt = array();
                                                                        $Rtem_sql = " SELECT * FROM rad WHERE txn='".$txn."' and print=0 "; 
                                                                        $Rtem_res = dbQuery($Rtem_sql);
                                                                        if(dbNumRows($Rtem_res) > 0) {
                                                                            while($Rtem_row = dbFetchAssoc($Rtem_res)) {
                                                                                $_Rpri = $_Rpri+$Rtem_row['PRI'];
                                                                                $_Rpri1 = $_Rpri1+$Rtem_row['PRI1'];
                                                                                $_Rpri2 = $_Rpri2+$Rtem_row['PRI2'];
                                                                                $Rtem_ser[] = $Rtem_row['ser'];
                                                                                $Rtem_code[] = $Rtem_row['CODE'];
                                                                                $Rtem_name[] = $Rtem_row['NAME'];
                                                                                $Rtem_df_doc[] = $Rtem_row['df_doc'];
                                                                                $Rtem_lst[] = $Rtem_row['LST'];
                                                                                $Rtem_amt[] = $Rtem_row['AMT'];
                                                                            }

                                                                            switch ($ty) {
                                                                                case 1:
                                                                                    $_Rtot = $_Rpri1;
                                                                                break;
                                                                                case 2:
                                                                                    $_Rtot = $_Rpri2;
                                                                                break;
                                                                                default:
                                                                                    $_Rtot = $_Rpri;
                                                                            }

                                                                            $upd_rad_sql = " UPDATE rad SET print='".$_Rprint."' WHERE txn='".$txn."' and print=0 "; 
                                                                            $upd_rad_res = dbQuery($upd_rad_sql);

                                                                            $ins_rad_bill = " INSERT INTO bill SET txn='".$txn."',date=curdate(),time=curtime(),desc_='RAD',print='".$_Rprint."',price='".$_Rpri."',type='".$type."',tot='".$_Rtot."' "; 
                                                                            $res_rad_bill = dbQuery($ins_rad_bill);

                                                                            $nn = 1;
                                                                            $count_rad_code = count($Rtem_code);
                                                                            for ($i=0; $i < $count_rad_code; $i++) {
                                                                                $_rad_lst = trim($Rtem_lst[$i]);
                                                                                $_ser_rad = $Rtem_ser[$i];
                                                                                $_radser = '1'.str_repeat("0", (9 - strlen($_ser_rad))).$_ser_rad;
                                                                                $_rad_df_doc = $Rtem_df_doc[$i];
                                                                                $_raditem = $nn.'/'.$count_rad_code;
                                                                                
                                                                                $_upd_rad_sql = " UPDATE rad SET item='".$_raditem."' WHERE ser='".$_ser_rad."' "; 
                                                                                $_upd_rad_res = dbQuery($_upd_rad_sql);

                                                                                $ins_radlbl_sql = " INSERT INTO radlbl SET txn='".$txn."',print='".$_Rprint."',item='".$_raditem."',ttl='".$ttl."',fname='".$name."'
                                                                                                    ,lname='".$lname."',hn='".$hn."',sex='".$sex."',date=curdate(),time=curtime(),born='".$born."',code='".$Rtem_code[$i]."'
                                                                                                    ,name='".$Rtem_name[$i]."',amt='".$Rtem_amt[$i]."',room='".$room."',type='".$type."',doc='".$ord_fu_user."',lst='".$_rad_lst."'
                                                                                                    ,radser='".$_radser."',df_doc='".$_rad_df_doc."',ser_i='".$_ser_rad."',hn_old='".$hn_old."'
                                                                                                "; 
                                                                                $radlbl_res = dbQuery($ins_radlbl_sql);

                                                                                $nn = $nn+1;
                                                                            }
                                                                        }
                                                                    /*End Rad */

                                                            }

                                                            $upd_fu2 = " UPDATE fu SET ok=1,date3=curdate(),txn='".$txn."' WHERE ser='".$ser_fu."'";
                                                            $upd_fu_res2 = dbQuery($upd_fu2);

                                                            echo json_encode(array('message' => 'ลงทะเบียนสำเร็จ','status' => TRUE));
                                                    }else{
                                                        echo json_encode(array('message' => 'ลงทะเบียนไม่สำเร็จ','status' => FALSE));
                                                    }
                                                }
                                            }else{
                                                echo json_encode(array('message' => 'ไม่พบสิทธิในฐานข้อมูล','status' => FALSE));
                                            } 
                                        }else{
                                            echo json_encode(array('message' => 'ค้หา HN '.$hn.' ไม่พบ','status' => FALSE)); 
                                        }
                                    }
                                }
                            }
                    }else{
                        echo json_encode(array('message' => 'ค้หา HN '.$hn.' ไม่พบ','status' => FALSE));
                    }
                }
            }
        }else{
            echo json_encode(array('message' => 'ไม่พบ ser '.$ser_fu.' เป็นผู้ป่วยนัดวันนี้','status' => FALSE));
        }
    }

?>