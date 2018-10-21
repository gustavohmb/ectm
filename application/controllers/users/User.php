<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class User extends CI_Controller {

	public function __construct(){
        parent::__construct();
        check_login_user();
        $this->load->model('common_model');
       $this->load->model('login_model');
    }


    public function index()
    {
        $data = array();
        $data['page_title'] = 'Usuario';
        $data['country'] = $this->common_model->select('country');
        $data['power'] = $this->common_model->get_all_power('user_power');
        $data['main_content'] = $this->load->view('users/user/add', $data, TRUE);
        
        $this->load->view('admin/index', $data);
    }

    //-- add new user by admin
    public function add()
    {   
    	
        if ($_POST) {
        	
            $data = array(
                'first_name' => $_POST['first_name'],
                'last_name' => $_POST['last_name'],
                'email' => $_POST['email'],
                'password' => md5($_POST['password']),
                'mobile' => $_POST['mobile'],
                'country' => $_POST['country'],
                'status' => $_POST['status'],
                'role' => $_POST['role'],
            	'idempresa'	=> $_POST['idempresa'],
                'created_at' => current_datetime()
            );

            $data = $this->security->xss_clean($data);
            
            //-- check duplicate email
            $email = $this->common_model->check_email($_POST['email']);

            if (empty($email)) {
                $user_id = $this->common_model->insert($data, 'user');
            
                if ($this->input->post('role') == "user") {
                    $actions = $this->input->post('role_action');
                    foreach ($actions as $value) {
                        $role_data = array(
                            'user_id' => $user_id,
                            'action' => $value
                        ); 
                       $role_data = $this->security->xss_clean($role_data);
                       $this->common_model->insert($role_data, 'user_role');
                    }
                }
                $this->session->set_flashdata('msg', ' Usu&aacute;rio cadastrado com sucesso');
                redirect(base_url('users/user/all_user_list'));
            } else {
                $this->session->set_flashdata('error_msg', ' Email j&aacute existe, tente outro!');
                redirect(base_url('users/user'));
            }

        }
    }

    public function all_user_list()
    {
    	//$arrListaUsuario = $this->replogin->getAll(array('idempresa' => $this->session->userdata("logado")));
    	
    	$data['users'] = $this->common_model->get_all_user($this->session->userdata('idempresa'));
        $data['country'] = $this->common_model->select('country');
        $data['count'] = $this->common_model->get_user_total();
        $data['main_content'] = $this->load->view('users/user/users', $data, TRUE);
        $this->load->view('admin/index', $data);
    }

    //-- update users info
    public function update($id)
    {
        if ($_POST) {

            $data = array(
                'idempresa'=> $_POST['idempresa'],
                'first_name' => $_POST['first_name'],
                'last_name' => $_POST['last_name'],
                'mobile' => $_POST['mobile'],
                'country' => $_POST['country'],
                'role' => $_POST['role']
            );
            $data = $this->security->xss_clean($data);

            $powers = $this->input->post('role_action');
            if (!empty($powers)) {
                $this->common_model->delete_user_role($id, 'user_role');
                foreach ($powers as $value) {
                   $role_data = array(
                        'user_id' => $id,
                        'action' => $value
                    ); 
                   $role_data = $this->security->xss_clean($role_data);
                   $this->common_model->insert($role_data, 'user_role');
                }
            }

            $this->common_model->edit_option($data, $id, 'user');
            $this->session->set_flashdata('msg', ' Informa&ccedil;&atilde;o alterada com sucesso');
            redirect(base_url('users/user/all_user_list'));

        }

        $data['user'] = $this->common_model->get_single_user_info($id);
        $data['user_role'] = $this->common_model->get_user_role($id);
        $data['power'] = $this->common_model->select('user_power');
        $data['country'] = $this->common_model->select('country');
        $data['main_content'] = $this->load->view('users/user/edit_user', $data, TRUE);
        $this->load->view('admin/index', $data);
        
    }

    
    //-- active user
    public function active($id) 
    {
        $data = array(
            'status' => 1
        );
        $data = $this->security->xss_clean($data);
        $this->common_model->update($data, $id,'user');
        $this->session->set_flashdata('msg', 'Usu&aacute;rio ativado com sucesso!');
        redirect(base_url('users/user/all_user_list'));
    }

    //-- deactive user
    public function deactive($id) 
    {
        $data = array(
            'status' => 0
        );
        $data = $this->security->xss_clean($data);
        $this->common_model->update($data, $id,'user');
        $this->session->set_flashdata('msg', 'Usu&aacute;rio desativado com sucesso!');
        redirect(base_url('users/user/all_user_list'));
    }

    //-- delete user
    public function delete($id)
    {
        $this->common_model->delete($id,'user'); 
        $this->session->set_flashdata('msg', 'Usu&aacute;rio exclu&iacute;do com sucesso!'); 
        redirect(base_url('users/user/all_user_list'));
    }


    public function power()
    {   
        $data['powers'] = $this->common_model->get_all_power('user_power');
        $data['main_content'] = $this->load->view('users/user/user_power', $data, TRUE);
        $this->load->view('admin/index', $data);
    }

    //-- add user power
    public function add_power()
    {   
        if (isset($_POST)) {
            $data = array(
                'name' => $_POST['name'],
                'power_id' => $_POST['power_id']
            );
            $data = $this->security->xss_clean($data);
            
            //-- check duplicate power id
            $power = $this->common_model->check_exist_power($_POST['power_id']);
            if (empty($power)) {
                $user_id = $this->common_model->insert($data, 'user_power');
                $this->session->set_flashdata('msg', 'Permiss&atilde;o cadastrada com sucesso');
            } else {
                $this->session->set_flashdata('error_msg', 'Permiss&atilde;o j&aacute; cadastrada, Tente outra!');
            }
            redirect(base_url('users/user/power'));
        }
        
    }

    //--update user power
    public function update_power()
    {   
        if (isset($_POST)) {
            $data = array(
                'name' => $_POST['name']
            );
            $data = $this->security->xss_clean($data);
            
            $this->session->set_flashdata('msg', 'Permiss&atilde;o alterada com sucesso!');
            $user_id = $this->common_model->edit_option($data, $_POST['id'], 'user_power');
            redirect(base_url('users/user/power'));
        }
        
    }

    public function delete_power($id)
    {
        $this->common_model->delete($id,'user_power'); 
        $this->session->set_flashdata('msg', 'Permiss&atilde;o exclu&iacute;da com sucesso');
        redirect(base_url('users/user/power'));
    }


}