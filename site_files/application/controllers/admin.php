<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Admin extends MY_Controller{

	public function index(){
            
            if($this->user->Data()){
                $page = new Page('admin');
                $page->content('admin/admin-v');
                $page->scripts('admin');
                $page->scripts('fileUploader');
            }
            else{
                $page = new Page('login');
                $page->content('admin/login-v');
            }
            $page->show('admin_template');
	}
        
        public function save_item(){
//            $this->smrke->debug($_POST);
            $this->load->library('file');
            $image = new File($_POST['image_item']);
            $dest = $image->saveFile();
            echo $dest;
        }
        
        public function upload_file(){
            if(isset($_POST["uploadImage"])){
                $this->load->library('file');
                $this->file->grabFile($_FILES['image_item']);
                
                // return JSON
                print '<script type="text/javascript">';
                print 'parent.file_obj='.$this->file->getJSON().';';
                print '</script>';              
            }
        }
        
        public function login(){
            if(isset($_POST['pass']))
                $this->user->login($_POST['pass']);
            header('Location: '.base_url('admin'));
            exit();
        } // end login()
        
        public function logout(){
            $this->user->logout();            
        } // end logout();
	
}
