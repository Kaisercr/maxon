<?php if(!defined('BASEPATH')) exit('No direct script access allowd');

class Coa extends CI_Controller {
    private $sql="select account,account_description,db_or_cr,
    beginning_balance,account_type,group_type,id 
    from chart_of_accounts
    ";
    private $file_view='gl/coa';
	function __construct()
	{
		parent::__construct();
		if(!$this->access->is_login())redirect(base_url());
 		$this->load->helper(array('url','form','mylib_helper'));
		$this->load->library('template');
		$this->load->library('form_validation');
        $this->load->model('chart_of_accounts_model');
		$this->load->model('syslog_model');

	} 
	function index()
	{	
		if(!allow_mod2('_10010'))return false;   
        $this->browse();
	}
    function browse($offset=0,$limit=50,$order_column='account',$order_type='asc'){
		$data['controller']='coa';
		$data['fields_caption']=array('Kode Akun','Nama Akun Perkiraan',
			'Db/Cr','Saldo Awal','Type Akun','Kelompok');
		$data['fields']=array('account','account_description','db_or_cr'
			,'beginning_balance','account_type','group_type');
		$data['field_key']='account';
		$data['caption']='DAFTAR KODE AKUN / COA / PERKIRAAN';
		$data['list_info_visible']=true;

		$this->load->library('search_criteria');
		$faa[]=criteria("Kode Akun","sid_no");
		$faa[]=criteria("Nama Akun","sid_nama");
		$faa[]=criteria("Kelompok","sid_kel");
		$data['criteria']=$faa;
        $this->template->display_browse($data);            
    }
    function browse_data($offset=0,$limit=100,$nama=''){
		$no=$this->input->get('sid_no');
        $sql=$this->sql.' where 1=1';
		if($no!='')$sql.=" and account like '".$no."%'";
		if($this->input->get('sid_nama')!='')$sql.=" and account_description like '".$this->input->get('sid_nama')."%'";
		if($this->input->get('sid_kel')!='')$sql.=" and group_type like '".$this->input->get('sid_kel')."%'";
		$sql.=" order by account";
        echo datasource($sql);
    }	      
	function list_info($offset=0){
		if(isset($_GET['offset'])){
			$offset=$_GET['offset'];
		}
		$data['offset']=$offset;
		$this->load->library('search_criteria');

		$faa[]=criteria("Kode Akun","sid_no");
		$faa[]=criteria("Nama Akun","sid_nama");
		$faa[]=criteria("Kelompok","sid_kel");
	
		$data['criteria']=$faa;
		$data['criteria_text']=criteria_text($faa);
		$data['sid_nama']=$this->session->userdata('sid_nama');
		$data['sid_kel']=$this->session->userdata('sid_kel');
		$data['sid_no']=$this->session->userdata('sid_no');
		
		$this->template->display_form_input('gl/info_list_coa',$data);	
	}	    
	function add()
	{
		if(!allow_mod2('_10011'))return false;   
		 $data=$this->set_defaults();
		 $this->_set_rules();
		 if ($this->form_validation->run()=== TRUE){
			$data=$this->get_posts();
			$id=$this->chart_of_accounts_model->save($data);
            $data['message']='update success';
            $data['mode']='view';
			$this->syslog_model->add($id,"coa","add");

            $this->browse();
		} else {
			$data['mode']='add';
	        $this->template->display_form_input($this->file_view,$data,'');
		}
	}
    function save(){
    	$mode=$this->input->post('mode');
		if($mode=="add"){
			$this->add();
		} else {
			$this->update();
		}
    }    
	function set_defaults($record=NULL){
		$data['mode']='';
		$data['message']='';
    	$data['account_type_list']=$this->chart_of_accounts_model->account_type_list();
		$data['group_type_list']=$this->chart_of_accounts_model->group_type_list();
        $data['account_type']='';
        $data['group_type']='';
        $data['h_or_d']='0';
		if($record==NULL){
			$data['account']='';
			$data['account_description']='';
			$data['db_or_cr']='';
			$data['h_or_d']='';
			$data['beginning_balance']='0';
		} else {
			$data['account']=$record->account;
			$data['account_description']=$record->account_description;
			$data['db_or_cr']=$record->db_or_cr;
			$data['beginning_balance']=$record->beginning_balance;
            $data['account_type']=$record->account_type;
            $data['group_type']=$record->group_type;
		}
		return $data;
	}
	function get_posts(){
		$data['mode']=$this->input->post('mode');
		$data['account_type']=$this->input->post('account_type');
		$data['group_type']=$this->input->post('group_type');
		$data['account']=$this->input->post('account');
		$data['account_description']=$this->input->post('account_description');
		$data['db_or_cr']=$this->input->post('db_or_cr');
		$data['h_or_d']=$this->input->post('h_or_d');
		$data['beginning_balance']=$this->input->post('beginning_balance');
        return $data;
	}        
    function _set_rules(){	
		 $this->form_validation->set_rules('account_type','Account Type', 'required|trim');
		 $this->form_validation->set_rules('group_type','Group Type', 'required');
		 $this->form_validation->set_rules('account','Account', 'required|trim');
	}
    function delete($id){
		if(!allow_mod2('_10013'))return false;   
		$id=urldecode($id);
	 	$this->chart_of_accounts_model->delete($id);
		$this->syslog_model->add($id,"coa","delete");
	 	$this->browse();
	}
	function view($id,$message=null){
		if(!allow_mod2('_10010'))return false;   
		$id=urldecode($id);
		$message=urldecode($message);
		 $data['id']=$id;
		 $rst=$this->chart_of_accounts_model->get_by_id($id)->row();
		 if(count($rst)){
            $data=$this->set_defaults($rst);
            $data['db_or_cr']=$rst->db_or_cr;
            $data['h_or_d']='1';
         }
		 $data['mode']='view';
         $data['message']=$message;
         $data['account_type_list']=$this->chart_of_accounts_model->account_type_list();
		 $data['group_type_list']=$this->chart_of_accounts_model->group_type_list();
         $this->template->display_form_input($this->file_view,$data,'');
	}        
	function update()
	{
		 $data=$this->set_defaults();
		 $this->_set_rules();
 		 $id=$this->input->post('account');
		 if ($this->form_validation->run()=== TRUE){
			$data=$this->get_posts(); 
			unset($data['h_or_d']);                     
			$this->chart_of_accounts_model->update($id,$data);
            $message='Update Success';
			$this->syslog_model->add($id,"coa","edit");

            $this->browse();
		} else {
			$message='Error Update';
     		$this->view($id,$message);		
		}	  	
	}        
	function select($account=''){
		$account=urldecode($account);
		$sql="select account,account_description,id from chart_of_accounts where 1=1";
		if($account!="")$sql.=" and (account like '$account%' or account_description like '%$account%')";
		$sql.=" order by account";
		echo datasource($sql);	
	}
	function card($account_id) {
	{
		$account_id=urldecode($account_id);
		$account_id=$this->chart_of_accounts_model->get_by_id($account_id)->row()->id;
		$date_from= $this->input->get('d1');
		$date_from=  date('Y-m-d H:i:s', strtotime($date_from));
		$date_to= $this->input->get('d2');
		$date_to = date('Y-m-d H:i:s', strtotime($date_to));
		
		$sql="select sum(debit)-sum(credit) as saldo  
			from gl_transactions 
			where account_id='$account_id' 
			and date<'$date_from'";

        $query=$this->db->query($sql);
		$awal=$query->row()->saldo;
		$rows[0]=array("gl_id"=>"SALDO","date"=>"SALDO","source"=>"SALDO","debit"=>0,"credit"=>0,
			"operation"=>'SALDO',"saldo"=>number_format($awal));

		$sql="select gl_id,date,source,debit,credit,operation 
			from gl_transactions 
			where account_id='$account_id' 
			and date between '$date_from' and '$date_to' order by date";
		
	 
		
        $query=$this->db->query($sql);
		 
        $i=1;
		if($query)foreach($query->result_array() as $row) {
			$awal=$awal+$row['debit']-$row['credit'];
			$row['debit']=number_format($row['debit']);
			$row['credit']=number_format($row['credit']);
			$row["saldo"]=number_format($awal);
			$rows[]=$row;
		};	
        $data['total']=count($rows);
        $data['rows']=$rows;
                    
        echo json_encode($data);

	}

	
	
	}
}
