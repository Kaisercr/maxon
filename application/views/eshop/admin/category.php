<div class="row-fluid" >
	<?
	if($cmd=="list") {
		$this->load->library("browser");
		$browse=new browser();
		$config['tablename']='inventory_categories';
		//$config['sql']="select item_number,description,unit_of_measure,retail,cost_from_mfg from inventory";
		$config['sql']='';
		$config['primary_key']="kode";
		$config['order_by']="kode";
		$config['where']="";
		$config['use_bootstrap']=true;
		$config['id']="tbl";
		$config['limit']=$limit;
		$config['caption']='Manage Category';
		$config['fields']=array(
			'kode'=>array("caption"=>"Kode",'size'=>10),
			'category'=>array('caption'=>'Kategori','size'=>50),
			'parent_id'=>array('caption'=>'Parent','size'=>50),
			'item_picture'=>array('caption'=>'Banner','size'=>100),
			'icon_picture'=>array('caption'=>'Icon')
		);
		$config['controller']=base_url()."index.php/eshop_admin/categories";

		if(!isset($page))$page=0;
		$config['page']=$page;

		$browse->init($config);
		$browse->render();		
		
	} else {
	?>
		<div class="col-md-8">
			<div class="">
				<form  enctype="multipart/form-data" class="form-horizontal" id='frmMain' method='post' >
					<input type='hidden' name='mode' id='mode' value='<?=$mode?>'>
					<?=my_input_2("Kode Kategori","kode",$kode)?>
					<?=my_input_2("Nama Kategori","category",$category)?>
					<?=my_textarea("Keterangan dan deskripsi","description",$description)?>
					<?=my_input_2("Kategori induk","parent_id",$parent_id)?>
					<?=my_input_2("Gambar banner (width=600,height=300)","item_picture",$item_picture)?>				
					<?=my_input_file("","item_picture")?>				
					<?=my_input_2("Gambar icon (width:50,height=50)","icon_picture",$icon_picture)?>				
					<?=my_input_file("","icon_picture")?>				
				</form>	  
			</div>
			<div  class='well'>
					<button type="button" class="btn btn-primary" onclick='save_item();return false'>Save changes</button>
			</div>
		</div>
		<script language='javascript'>
		function save_item(){
			var kode=$("#kode").val();
			if(kode==""){alert("Isi kode !");return false}
			var url="<?=base_url()?>index.php/eshop_admin/categories/save";
			var next_url='<?=base_url()?>index.php/eshop_admin/categories/browse';
			$('#frmMain').ajax_post(url,'undefined',next_url); 
		};

		</script>
	
	<? } ?>
</div>
<link rel="stylesheet" type="text/css" href="<?=base_url()?>assets/eshop/eshop.css">

