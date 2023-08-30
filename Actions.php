<?php 
session_start();
require_once('DBConnection.php');

Class Actions extends DBConnection{
    function __construct(){
        parent::__construct();
    }
    function __destruct(){
        parent::__destruct();
    }
    function login(){
        extract($_POST);
        $sql = "SELECT * FROM user_list where username = '{$username}' and `password` = '".md5($password)."' ";
        @$qry = $this->db->query($sql)->fetch_array();
        if(!$qry){
            $resp['status'] = "failed";
            $resp['msg'] = "Usuário ou senha inválido!";
        }else{
            $resp['status'] = "success";
            $resp['msg'] = "Login realizado com sucesso!";
            foreach($qry as $k => $v){
                if(!is_numeric($k))
                $_SESSION[$k] = $v;
            }
        }
        return json_encode($resp);
    }
    function logout(){
        session_destroy();
        header("location:./");
    }
    function save_user(){
        extract($_POST);
        $data = "";
        foreach($_POST as $k => $v){
        if(!in_array($k,array('id'))){
            if(!empty($id)){
                if(!empty($data)) $data .= ",";
                $data .= " `{$k}` = '{$v}' ";
                }else{
                    $cols[] = $k;
                    $values[] = "'{$v}'";
                }
            }
        }
        if(empty($id)){
            $cols[] = 'password';
            $values[] = "'".md5($username)."'";
        }
        if(isset($cols) && isset($values)){
            $data = "(".implode(',',$cols).") VALUES (".implode(',',$values).")";
        }
        

       
        @$check= $this->db->query("SELECT count(user_id) as `count` FROM user_list where `username` = '{$username}' ".($id > 0 ? " and user_id != '{$id}' " : ""))->fetch_array()['count'];
        if(@$check> 0){
            $resp['status'] = 'failed';
            $resp['msg'] = "O Usuário já existe.";
        }else{
            if(empty($id)){
                $sql = "INSERT INTO `user_list` {$data}";
            }else{
                $sql = "UPDATE `user_list` set {$data} where user_id = '{$id}'";
            }
            @$save = $this->db->query($sql);
            if($save){
                $resp['status'] = 'success';
                if(empty($id))
                $resp['msg'] = 'Novo usuário salvo com sucesso.';
                else
                $resp['msg'] = 'Dados do Usuário atualizados com sucesso.';
            }else{
                $resp['status'] = 'failed';
                $resp['msg'] = 'Falha ao salvar detalhes do usuário. Erro: '.$this->db->error;
                $resp['sql'] =$sql;
            }
        }
        return json_encode($resp);
    }
    function delete_user(){
        extract($_POST);

        @$delete = $this->db->query("DELETE FROM `user_list` where user_id = '{$id}'");
        if($delete){
            $resp['status']='success';
            $_SESSION['flashdata']['type'] = 'success';
            $_SESSION['flashdata']['msg'] = 'Usuário excluído com sucesso.';
        }else{
            $resp['status']='failed';
            $resp['error']=$this->db->error;
        }
        return json_encode($resp);
    }
    function update_credentials(){
        extract($_POST);
        $data = "";
        foreach($_POST as $k => $v){
            if(!in_array($k,array('id','old_password')) && !empty($v)){
                if(!empty($data)) $data .= ",";
                if($k == 'password') $v = md5($v);
                $data .= " `{$k}` = '{$v}' ";
            }
        }
        if(!empty($password) && md5($old_password) != $_SESSION['password']){
            $resp['status'] = 'failed';
            $resp['msg'] = "Old password is incorrect.";
        }else{
            $sql = "UPDATE `user_list` set {$data} where user_id = '{$_SESSION['user_id']}'";
            @$save = $this->db->query($sql);
            if($save){
                $resp['status'] = 'success';
                $_SESSION['flashdata']['type'] = 'success';
                $_SESSION['flashdata']['msg'] = 'Credencial atualizada com sucesso.';
                foreach($_POST as $k => $v){
                    if(!in_array($k,array('id','old_password')) && !empty($v)){
                        if(!empty($data)) $data .= ",";
                        if($k == 'password') $v = md5($v);
                        $_SESSION[$k] = $v;
                    }
                }
            }else{
                $resp['status'] = 'failed';
                $resp['msg'] = 'Falha na atualização de credenciais. Erro: '.$this->db->error;
                $resp['sql'] =$sql;
            }
        }
        return json_encode($resp);
    }
    function save_category(){
        extract($_POST);
        $data = "";
        foreach($_POST as $k => $v){
            if(!in_array($k,array('id'))){
                $v = addslashes(trim($v));
            if(empty($id)){
                $cols[] = "`{$k}`";
                $vals[] = "'{$v}'";
            }else{
                if(!empty($data)) $data .= ", ";
                $data .= " `{$k}` = '{$v}' ";
            }
            }
        }
        if(isset($cols) && isset($vals)){
            $cols_join = implode(",",$cols);
            $vals_join = implode(",",$vals);
        }
        if(empty($id)){
            $sql = "INSERT INTO `category_list` ({$cols_join}) VALUES ($vals_join)";
        }else{
            $sql = "UPDATE `category_list` set {$data} where category_id = '{$id}'";
        }
        @$check= $this->db->query("SELECT COUNT(category_id) as count from `category_list` where `name` = '{$name}' ".($id > 0 ? " and category_id != '{$id}'" : ""))->fetch_array()['count'];
        if(@$check> 0){
            $resp['status'] ='failed';
            $resp['msg'] = 'Category already exists.';
        }else{
            @$save = $this->db->query($sql);
            if($save){
                $resp['status']="success";
                if(empty($id))
                    $resp['msg'] = "Categoria salva com sucesso.";
                else
                    $resp['msg'] = "Categoria atualizada com sucesso.";
            }else{
                $resp['status']="failed";
                if(empty($id))
                    $resp['msg'] = "Falha ao salvar a nova categoria.";
                else
                    $resp['msg'] = "Falha na atualização da categoria.";
                $resp['error']=$this->db->error;
            }
        }
        return json_encode($resp);
    }
    function delete_category(){
        extract($_POST);

        @$update = $this->db->query("UPDATE `category_list` set `delete_flag` = 1 where category_id = '{$id}'");
        if($update){
            $resp['status']='success';
            $_SESSION['flashdata']['type'] = 'success';
            $_SESSION['flashdata']['msg'] = 'Categoria excluída com sucesso.';
        }else{
            $resp['status']='failed';
            $resp['error']=$this->db->error;
        }
        return json_encode($resp);
    }
    function save_product(){
        extract($_POST);
        $data = "";
        foreach($_POST as $k => $v){
            if(!in_array($k,array('id'))){
                $v = addslashes(trim($v));
            if(empty($id)){
                $cols[] = "`{$k}`";
                $vals[] = "'{$v}'";
            }else{
                if(!empty($data)) $data .= ", ";
                $data .= " `{$k}` = '{$v}' ";
            }
            }
        }
        if(isset($cols) && isset($vals)){
            $cols_join = implode(",",$cols);
            $vals_join = implode(",",$vals);
        }
        if(empty($id)){
            $sql = "INSERT INTO `product_list` ({$cols_join}) VALUES ($vals_join)";
        }else{
            $sql = "UPDATE `product_list` set {$data} where product_id = '{$id}'";
        }
        @$check= $this->db->query("SELECT COUNT(product_id) as count from `product_list` where `product_code` = '{$product_code}' and delete_flag = 0 ".($id > 0 ? " and product_id != '{$id}'" : ""))->fetch_array()['count'];
        @$check2= $this->db->query("SELECT COUNT(product_id) as count from `product_list` where `name` = '{$name}' and delete_flag = 0 ".($id > 0 ? " and product_id != '{$id}'" : ""))->fetch_array()['count'];
        if(@$check> 0){
            $resp['status'] ='failed';
            $resp['msg'] = 'O código do produto já existe.';
        }elseif(@$check2 > 0){
            $resp['status'] ='failed';
            $resp['msg'] = 'O nome do produto já existe.';
        }else{
            @$save = $this->db->query($sql);
            if($save){
                $resp['status']="success";
                if(empty($id))
                    $resp['msg'] = "Produto salvo com sucesso.";
                else
                    $resp['msg'] = "Produto atualizado com sucesso.";
            }else{
                $resp['status']="failed";
                if(empty($id))
                    $resp['msg'] = "Falha ao salvar o novo produto.";
                else
                    $resp['msg'] = "Falha na atualização do produto.";
                $resp['error']=$this->db->error;
            }
        }
        return json_encode($resp);
    }
    function delete_product(){
        extract($_POST);

        @$update = $this->db->query("UPDATE `product_list` set delete_flag = 1 where product_id = '{$id}'");
        if($update){
            $resp['status']='success';
            $_SESSION['flashdata']['type'] = 'success';
            $_SESSION['flashdata']['msg'] = 'Produto excluído com sucesso.';
        }else{
            $resp['status']='failed';
            $resp['error']=$this->db->error;
        }
        return json_encode($resp);
    }
    function save_stock(){
        extract($_POST);
        $data = "";
        foreach($_POST as $k => $v){
            if(!in_array($k,array('id'))){
                $v = addslashes(trim($v));
            if(empty($id)){
                $cols[] = "`{$k}`";
                $vals[] = "'{$v}'";
            }else{
                if(!empty($data)) $data .= ", ";
                $data .= " `{$k}` = '{$v}' ";
            }
            }
        }
        if(isset($cols) && isset($vals)){
            $cols_join = implode(",",$cols);
            $vals_join = implode(",",$vals);
        }
        if(empty($id)){
            $sql = "INSERT INTO `stock_list` ({$cols_join}) VALUES ($vals_join)";
        }else{
            $sql = "UPDATE `stock_list` set {$data} where stock_id = '{$id}'";
        }
        
        @$save = $this->db->query($sql);
        if($save){
            $resp['status']="success";
            if(empty($id))
                $resp['msg'] = "Estoque salvo com sucesso.";
            else
                $resp['msg'] = "Estoque atualizado com sucesso.";
        }else{
            $resp['status']="failed";
            if(empty($id))
                $resp['msg'] = "Falha ao salvar novo estoque.";
            else
                $resp['msg'] = "Falha na atualização do estoque.";
            $resp['error']=$this->db->error;
        }
        return json_encode($resp);
    }
    function delete_stock(){
        extract($_POST);

        @$delete = $this->db->query("DELETE FROM `stock_list` where stock_id = '{$id}'");
        if($delete){
            $resp['status']='success';
            $_SESSION['flashdata']['type'] = 'success';
            $_SESSION['flashdata']['msg'] = 'Estoque excluído com sucesso.';
        }else{
            $resp['status']='failed';
            $resp['error']=$this->db->error;
        }
        return json_encode($resp);
    }
    function save_transaction(){
        extract($_POST);
        $data = "";
        $receipt_no = time();
        $i = 0;
        while(true){
            $i++;
            $chk = $this->db->query("SELECT count(transaction_id) `count` FROM `transaction_list` where receipt_no = '{$receipt_no}' ")->fetch_array()['count'];
            if($chk > 0){
                $receipt_no = time().$i;
            }else{
                break;
            }
        }
        $_POST['receipt_no'] = $receipt_no;
        $_POST['user_id'] = $_SESSION['user_id'];
        foreach($_POST as $k => $v){
            if(!in_array($k,array('id')) && !is_array($_POST[$k])){
                $v = addslashes(trim($v));
            if(empty($id)){
                $cols[] = "`{$k}`";
                $vals[] = "'{$v}'";
            }else{
                if(!empty($data)) $data .= ", ";
                $data .= " `{$k}` = '{$v}' ";
            }
            }
        }
        if(isset($cols) && isset($vals)){
            $cols_join = implode(",",$cols);
            $vals_join = implode(",",$vals);
        }
        if(empty($id)){
            $sql = "INSERT INTO `transaction_list` ({$cols_join}) VALUES ($vals_join)";
        }else{
            $sql = "UPDATE `transaction_list` set {$data} where stock_id = '{$id}'";
        }
        
        @$save = $this->db->query($sql);
        if($save){
            $resp['status']="success";
            $_SESSION['flashdata']['type']="success";
            if(empty($id))
                $_SESSION['flashdata']['msg'] = "Transação salva com sucesso.";
            else
                $_SESSION['flashdata']['msg'] = "Transação atualizada com sucesso.";
            if(empty($id))
            $last_id = $this->db->insert_id;
                $tid = empty($id) ? $last_id : $id;
            $data ="";
            foreach($product_id as $k => $v){
                if(!empty($data)) $data .=",";
                $data .= "('{$tid}','{$v}','{$quantity[$k]}','{$price[$k]}')";
            }
            if(!empty($data))
            $this->db->query("DELETE FROM transaction_items where transaction_id = '{$tid}'");
            $sql = "INSERT INTO transaction_items (`transaction_id`,`product_id`,`quantity`,`price`) VALUES {$data}";
            $save = $this->db->query($sql);
            $resp['transaction_id'] = $tid;
        }else{
            $resp['status']="failed";
            if(empty($id))
                $resp['msg'] = "Falha ao salvar nova transação.";
            else
                $resp['msg'] = "Falha na atualização da transação.";
            $resp['error']=$this->db->error;
        }
        return json_encode($resp);
    }
    function delete_transaction(){
        extract($_POST);

        @$delete = $this->db->query("DELETE FROM `transaction_list` where transaction_id = '{$id}'");
        if($delete){
            $resp['status']='success';
            $_SESSION['flashdata']['type'] = 'success';
            $_SESSION['flashdata']['msg'] = 'Transação excluída com sucesso.';
        }else{
            $resp['status']='failed';
            $resp['error']=$this->db->error;
        }
        return json_encode($resp);
    }
}
$a = isset($_GET['a']) ?$_GET['a'] : '';
$action = new Actions();
switch($a){
    case 'login':
        echo $action->login();
    break;
    case 'customer_login':
        echo $action->customer_login();
    break;
    case 'logout':
        echo $action->logout();
    break;
    case 'customer_logout':
        echo $action->customer_logout();
    break;
    case 'save_user':
        echo $action->save_user();
    break;
    case 'delete_user':
        echo $action->delete_user();
    break;
    case 'update_credentials':
        echo $action->update_credentials();
    break;
    case 'save_category':
        echo $action->save_category();
    break;
    case 'delete_category':
        echo $action->delete_category();
    break;
    case 'save_product':
        echo $action->save_product();
    break;
    case 'delete_product':
        echo $action->delete_product();
    break;
    case 'save_stock':
        echo $action->save_stock();
    break;
    case 'delete_stock':
        echo $action->delete_stock();
    break;
    case 'save_transaction':
        echo $action->save_transaction();
    break;
    case 'delete_transaction':
        echo $action->delete_transaction();
    break;
    default:
    // default action here
    break;
}