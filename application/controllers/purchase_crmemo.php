<?php if(!defined('BASEPATH')) exit('No direct script access allowd');

class Purchase_CrMemo extends CI_Controller {
    private $limit=10;
    private $sql="select kodecrdb,tanggal,docnumber,amount,keterangan,c.account, c.account_description
     from crdb_memo cm left join chart_of_accounts c on c.id=cm.accountid where transtype='PO-CREDIT MEMO'";
    private $controller='purchase_crmemo';
    private $primary_key='kodecrdb';
    private $file_view='purchase/credit_memo';
    private $table_name='crdb_memo';
	function __construct()
	{
		parent::__construct();
		if(!$this->access->is_login())redirect(base_url());
 		$this->load->helper(array('url','form','browse_select','mylib_helper'));
        $this->load->library('sysvar');
        $this->load->library('javascript');
        $this->load->library('template');
		$this->load->library('form_validation');
		$this->load->model('crdb_model');
		$this->load->model('syslog_model');
	}
	function nomor_bukti($add=false)
	{
		$key="Purchase CrDB Numbering";
		if($add){
		  	$this->sysvar->autonumber_inc($key);
		} else {			
			$no=$this->sysvar->autonumber($key,0,'!CRDBP~$00001');
			for($i=0;$i<100;$i++){			
				$no=$this->sysvar->autonumber($key,0,'!CRDBP~$00001');
				$rst=$this->crdb_model->get_by_id($no)->row();
				if($rst){
				  	$this->sysvar->autonumber_inc($key);
				} else {
					break;					
				}
			}
			return $no;
		}
	}
	function index()
	{	
		if(!allow_mod2('_40090'))return false;   
        $this->browse();
	}
    function browse($offset=0,$limit=50,$order_column='',$order_type='asc'){
		$data['controller']=$this->controller;
		$data['fields_caption']=array('Nomor Bukti','Tanggal','Faktur','Jumlah','Keterangan','Kode Akun','Perkiraan');
		$data['fields']=array('kodecrdb','tanggal','docnumber','amount','keterangan','account','account_description');
		$data['field_key']='kodecrdb';
		$data['caption']='DAFTAR CREDI MEMO';

		$this->load->library('search_criteria');
		
		$faa[]=criteria("Dari","sid_date_from","easyui-datetimebox");
		$faa[]=criteria("S/d","sid_date_to","easyui-datetimebox");
		$faa[]=criteria("Nomor BUkti","sid_number");
		$data['criteria']=$faa;
        $this->template->display_browse2($data);            
    }
    function browse_data($offset=0,$limit=10,$nama=''){
    	if($this->input->get('sid_number')){
    		$sql=$this->sql." and kodecrdb='".$this->input->get('sid_number')."'";
		} else {
			$d1= date( 'Y-m-d H:i:s', strtotime($this->input->get('sid_date_from')));
			$d2= date( 'Y-m-d H:i:s', strtotime($this->input->get('sid_date_to')));
			$sql=$this->sql." and tanggal between '".$d1."' and '".$d2."'";
		}
        echo datasource($sql);
    }	 
	
	function add()
	{
		if(!allow_mod2('_40091'))return false;   
		$data['kodecrdb']=$this->nomor_bukti();
		$data['tanggal']=date('Y-m-d');
		$data['docnumber']='';
		$data['amount']=0;
		$data['keterangan']="";
		$data['mode']='add';
		$this->template->display_form_input('purchase/credit_memo',$data,'');			
		
	}
	function save()
	{
		 
		$invoice_number=$this->input->post('docnumber');
		if($invoice_number)
		{
			$data['kodecrdb']=$this->nomor_bukti();
			$data['tanggal']=$this->input->post('tanggal');
			$data['docnumber']=$invoice_number;
			$data['amount']=$this->input->post('amount');
			$data['keterangan']=$this->input->post('keterangan');
			$data['transtype']=$this->input->post('transtype');
			$this->crdb_model->save($data);
			$this->nomor_bukti(true);
			$this->syslog_model->add($data['kodecrdb'],"crdb","edit");

		} else {echo 'Save: Invalid Invoice Number';}
	
	}
	function view($id,$message=null){
		if(!allow_mod2('_40090'))return false;   
		$id=urldecode($id);
		 $data['id']=$id;
		 $model=$this->crdb_model->get_by_id($id)->result_array();
		 $data=$this->set_defaults($model[0]);
		 $data['mode']='view';
         $this->template->display('purchase/credit_memo',$data);                 
	}
   
	function set_defaults($record=NULL){
            $data=data_table($this->table_name,$record);
            $data['mode']='';
            $data['message']='';
			return $data;
	}
	
}