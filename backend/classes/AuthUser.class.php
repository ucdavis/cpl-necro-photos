<?php

class AuthUser {
    
    public static function updateTimeStamp($login, $area = ''){
        $ipv4 = filter_input(INPUT_SERVER,'REMOTE_ADDR');
                
        $q_user = "SELECT count(*) AS total 
                   FROM cpl_billing.sessions
                   WHERE login = ?";
        $params = [$login];
        
        $rst_user = dbConn::query($q_user,$params);
        if(count($rst_user) && $rst_user[0]['total'] < 1){
            // Add new session user
            $q_add_user = "INSERT INTO cpl_billing.sessions (login, last_activity, ipv4_address, area) VALUES (?, now(), INET_ATON(?), ?)";
            $params = [$login,$ipv4,$area];
            $rst_add_user = dbConn::query($q_add_user,$params);
        } else {
            // Update timestamp for user
            $q_update_user = "UPDATE cpl_billing.sessions SET last_activity = now(), ipv4_address = INET_ATON(?), area = ? WHERE login = ?";
            $params = [$ipv4,$area,$login];
            $rst_update_user = dbConn::query($q_update_user,$params);
        }        
        
    }

    
    public static function sessionTime($login){
        // timeout interval for all CPL apps
        $q_timeout = "SELECT login, TIMESTAMPDIFF(SECOND,last_activity,now()) AS last_activity, INET_NTOA(ipv4_address) AS ipv4_address 
                      FROM cpl_billing.sessions
                      WHERE login = ?";
        $rst_timeout = dbConn::query($q_timeout,[$login]);

        /*
        if($rst_timeout[0]['last_activity'] > 900){ // 900 sec = 15 min
            //header('Location: https://cplstaff.ucdavis.edu');
        } else {
            //die($rst_timeout[0]['last_activity']);
        }
        */
        return $rst_timeout[0]['last_activity'];

    }
}

