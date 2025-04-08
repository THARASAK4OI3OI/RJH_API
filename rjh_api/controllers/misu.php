<?php
    function pudkn($num){
        $ret = 0;
        if($num > 0){
            if(($num - (int)$num) <> 0){
                $ret = (int)$num+1;
            }else{
                $ret = $num;
            }
        }else{
            if(($num - (int)$num) <> 0){
                $ret = (int)$num-1;
            }else{
                $ret = $num;
            }
        }
        return $ret;
    }

    function cut_stock($ord_fu_code,$stock,$ord_fu_amt){

        /* ตัด stock */
        $upd_sup_s01 = "UPDATE sup_s01 SET amt=amt-$ord_fu_amt WHERE code='".$ord_fu_code."' and stock='".$stock."' ";
        $res_sup_s01 = dbQuery($upd_sup_s01);
        $lotno = '';
        $amt2remain = (int)$ord_fu_amt;

        $sql_accconfig = "SELECT cut_lot FROM accconfig ";
        $res_accconfig = dbQuery($sql_accconfig);
        if(dbNumRows($res_accconfig) > 0) {
            $cut_lot_row = dbFetchAssoc($res_accconfig);
            $cut_lot = $cut_lot_row['cut_lot'];
            if($cut_lot == 1) {

                if($amt2remain > 0){
                    $sql_s01lot = " SELECT * FROM sup_s01lot 
                                    WHERE code='".$ord_fu_code."' and stock='".$stock."' and remain>0
                                    ORDER BY ser,expire
                                ";
                    $res_s01lot = dbQuery($sql_s01lot);
                    $res_sum_remain = dbQuery($sql_s01lot);
                }else{
                    $sql_s01lot = " SELECT * FROM sup_s01lot 
                                    WHERE code='".$ord_fu_code."' and stock='".$stock."' and remain<>amt
                                    ORDER BY ser DESC
                                ";
                    $res_s01lot = dbQuery($sql_s01lot);
                    $res_sum_remain = dbQuery($sql_s01lot);
                }

                if(dbNumRows($res_s01lot) < 1) {
                    $sql_s01lot = " SELECT * FROM sup_s01lot 
                                    WHERE code='".$ord_fu_code."' and stock='".$stock."' 
                                    ORDER BY ser DESC
                                ";
                    $res_s01lot = dbQuery($sql_s01lot);
                    $res_sum_remain = dbQuery($sql_s01lot);
                }
                
                if(dbNumRows($res_s01lot) > 0) {
                    $sum_remain = 0;
                    while($sum_remain_row = dbFetchAssoc($res_sum_remain)) {
                        $sum_remain = $sum_remain + (int)$sum_remain_row['remain'];
                    }

                    while(($s01lot_row = dbFetchAssoc($res_s01lot)) && $amt2remain <> 0) {
                        $serlot = $s01lot_row['ser'];
                        $remain = (int)$s01lot_row['remain'];
                        $lotno = $lotno.trim($s01lot_row['lotno']).';';
            
                        if($remain > $amt2remain){
                            $remain = $remain - $amt2remain;
                            $amt2remain = 0;
                        }else{
                            $amt2remain = $amt2remain - $remain;
                            $sum_remain = $sum_remain - $remain;
                            $remain = 0;
                        }
                        
                        if(($sum_remain == 0) && ($dru_can_zero = 1) && ($amt2remain > 0) ){
                            $remain = 0 - $amt2remain;
                            $amt2remain = 0;
                            $upd_sup_s01lot_can_zero = "UPDATE sup_s01lot SET remain='".$remain."' WHERE ser='".$serlot."'";
                            $res_sup_s01lot_can_zero = dbQuery($upd_sup_s01lot_can_zero);
                        }else{
                            $upd_sup_s01lot = "UPDATE sup_s01lot SET remain='".$remain."' WHERE ser='".$serlot."'";
                            $res_sup_s01lot = dbQuery($upd_sup_s01lot);
                        } 
                       
                    }

                    $lotno = substr($lotno,0,strlen($lotno)-1);   
                }
            } 
        }
 
        return $lotno;
     
    }

    function cname($tbl_field,$tbl_name,$tbl_key,$tbl_value){

        $rows_tbl = "";
        $sql_tbl = " SELECT $tbl_field FROM $tbl_name WHERE $tbl_key = '".$tbl_value."' ";
        $result_tbl = dbQuery($sql_tbl);
        $dbFet = dbFetchAssoc($result_tbl);

        if(dbNumRows($result_tbl) > 0) {
            $rows_tbl = trim($dbFet[$tbl_field]);
            return $rows_tbl;

        }else{
            return $rows_tbl;
        }
    }

    function lcname($tbl_field,$tbl_name,$tbl_key,$tbl_value){

        $rows_tbl = "";
        $sql_tbl = " SELECT $tbl_field FROM tbllu WHERE title='".$tbl_name."' and $tbl_key = '".$tbl_value."' ";
        $result_tbl = dbQuery($sql_tbl);
        $dbFet = dbFetchAssoc($result_tbl);

        if(dbNumRows($result_tbl) > 0) {
            $rows_tbl = trim($dbFet[$tbl_field]);
            return $rows_tbl;

        }else{
            return $rows_tbl;
        }
    }

    function str_like($text,$text_like,$len){
        
        $text_strpos = strpos($text,$text_like);
        $text_value = substr($text,$text_strpos,$len);
        return $text_value;
    }

?>