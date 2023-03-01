<?php include('db_connect.php');?>
<?php
	$voting = $conn->query("SELECT * FROM voting_list where  is_default = 1 ");
	foreach ($voting->fetch_array() as $key => $value) {
		$$key = $value;
	}

	$vchk = $conn->query("SELECT distinct(voting_id) from votes where user_id = ".$_SESSION['login_id']."")->num_rows;
	if($vchk > 0){
		// header('Location:voting.php?page=view_vote');
	}

	$vote = $conn->query("SELECT * FROM voting_list where id=".$id);
	foreach ($vote->fetch_array() as $key => $value) {
		$$key= $value;
	}
	$opts = $conn->query("SELECT * FROM voting_opt where voting_id=".$id);
	$opt_arr = array();
	$set_arr = array();

	while($row=$opts->fetch_assoc()){
		$opt_arr[$row['category_id']][] = $row;
		$set_arr[$row['category_id']] = array('id'=>'','max_selection'=>1);

	}

	$settings = $conn->query("SELECT * FROM voting_cat_settings where voting_id=".$id);
	while($row=$settings->fetch_assoc()){
		$set_arr[$row['category_id']] = $row;
	}

?>
<style>
	.candidate {
	    margin: auto;
	    width: 16vw;
	    padding: 10px;
	    cursor: pointer;
	    border-radius: 3px;
	    margin-bottom: 1em
	}
	.candidate:hover {
	    background-color: #80808030;
	    box-shadow: 2.5px 3px #00000063;
	}
	.candidate img {
	    height: 14vh;
	    width: 8vw;
	    margin: auto;
	}
	span.rem_btn {
	    position: absolute;
	    right: 0;
	    top: -1em;
	    z-index: 10;
	    display: none
	}
	span.rem_btn.active{
		display: block
	}
	.timer{
		position: fixed;
		top: 70px;
		left: 1030px;	
		z-index: 10;
		padding: 10px;
		font-size: 16px;
	}
	
	
</style>
<script>

    var count = 150;
    var total_h = parseInt(count/3600);
    var total_min = parseInt(count/600);
    var total_sec =parseInt(count%60);

  var interval = setInterval(function(){
    count--;
    total_h = parseInt(count/3600);
    total_min =  parseInt(count/60);
    total_sec = parseInt(count%60);
  document.getElementById('count').innerHTML= total_min + 'm ' + total_sec + 's';

if ( count <= 60) {
    document.querySelector('#count').innerHTML = total_sec + 's remaining'; 
} 
if (count === 0){
clearInterval(interval);
location.href = 'index.php';
}
},1000)
</script>
<div class="container-fluid">
	<div class="col-lg-12">
		<div class="card">
		<?php 
			$que = "SELECT * FROM  votes where user_id =".$_SESSION['login_id'];                                                                                                                                                                               
			$del_query = $conn -> query($que);
		
		if (mysqli_num_rows($del_query) ==0)  :?>
			<div class="card-body">
				<form action="" id="manage-vote">
					<input type="hidden" name="voting_id" value="<?php echo $id ?>">
				<div class="col-lg-12">
					<div class="text-center">
						<h3><b><?php echo $title ?></b></h3>
						<small><b><?php echo $description; ?></b></small>	
					<h4 id = "count" class = "bg-danger text-white rounded timer"></h4>	
					</div>
					
					<?php 
					$cats = $conn->query("SELECT * FROM category_list where id in (SELECT category_id from voting_opt where voting_id = '".$id."' )");
					while($row = $cats->fetch_assoc()):
					?>
						<hr>
						<div class="row mb-4">
							<div class="col-md-12">
									<div class="text-center">
										<h3><b><?php echo $row['category'] ?></b></h3>
									<small>Max Selection : <b><?php echo $set_arr[$row['id']]['max_selection']; ?></b></small>

									</div>
							</div>
						</div>
						<div class="row mt-3">
						<?php foreach ($opt_arr[$row['id']] as $candidate) {
						?>
							<div class="candidate" style="position: relative;" data-cid = '<?php echo $row['id'] ?>'  data-max="<?php echo $set_arr[$row['id']]['max_selection'] ?>" data-name="<?php echo $row['category'] ?>">
									<input type="checkbox" name="opt_id[<?php echo $row['id'] ?>][]" value="<?php echo $candidate['id'] ?>" style="display: none">
								<span class="rem_btn">
									<label for="" class="btn btn-primary"><span class="fa fa-check"></span></label>
								</span>
								<div class="item"  data-id="<?php echo $candidate['id'] ?>">
								<div style="display: flex">
									<img src="assets/img/<?php echo $candidate['image_path'] ?>" alt="">
								</div>
								<br>
								<div class="text-center">
									<large class="text-center"><b><?php echo ucwords($candidate['opt_txt']) ?></b></large>

								</div>
								</div>
							</div>
						<?php } ?>
						</div>
					<?php endwhile; ?>
				</div>

				<?php if ($_SESSION['login_type'] == 2)  :?>
				<hr>
				<button class="btn-block btn-primary">Sumbit</button>
				<?php endif; ?>
				<?php endif; ?>	

				<?php if ($_SESSION['login_type'] == 1)  :?>
				<h5 class = "text-center text-danger">You are an admin, you want to Rig, <b>GETAT!!!!!!</b></h5>
				<?php endif; ?>
				
				<?php 
				if (mysqli_num_rows($del_query) > 0):?>
				<hr>
				<h5 class = "text-center text-danger">You have voted already, <b>GETAT!!!!!!</b></h5>
				<?php endif; ?>	
			</form>
			</div>
		</div>
	</div>
</div>
<script>
	$('.candidate').click(function(){
		var chk = $(this).find('input[type="checkbox"]').prop("checked");
		
		if(chk == true){
			$(this).find('input[type="checkbox"]').prop("checked",false)
		}	else{
			var arr_chk = $("input[name='opt_id["+$(this).attr('data-cid')+"][]']:checked").length
			if($(this).attr('data-max') == 1){
			$("input[name='opt_id["+$(this).attr('data-cid')+"][]']").prop("checked",false)
			$(this).find('input[type="checkbox"]').prop("checked",true)
			}else{
			if(arr_chk >= $(this).attr('data-max')){
					alert_toast("Choose only "+$(this).attr('data-max')+" for "+$(this).attr('data-name')+" category","warning")
					return false;
				}
			}
			$(this).find('input[type="checkbox"]').prop("checked",true)
		}
		$('.candidate').each(function(){
			if($(this).find('input[type="checkbox"]').prop("checked") == true){
				$(this).find('.rem_btn').addClass('active')
			}else{
				$(this).find('.rem_btn').removeClass('active')
			}
		})
		
	})
	$('#manage-vote').submit(function(e){
		e.preventDefault()
		
	
		start_load();
		$.ajax({
			url:'ajax.php?action=submit_vote',
			method:'POST',
			data:$(this).serialize(),
			success:function(resp){
				if(resp == 1){
					alert_toast("Vote success fully submitted");
					setTimeout(function(){
						location.href = 'index.php'
					},1500)
				}
			}
		})

		if ($('.candidate').find('input[type="checkbox"]').prop("checked") == false){
				alert_toast("<a href = 'index.php?page=vote_sheet'> Please Select a candidate at each level</a>")
			}
	})
</script>