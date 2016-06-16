<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?><!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<title>查看日志</title>

	<style type="text/css">

	::selection { background-color: #E13300; color: white; }
	::-moz-selection { background-color: #E13300; color: white; }

	body {
		background-color: #fff;
		margin: 40px;
		font: 13px/20px normal Helvetica, Arial, sans-serif;
		color: #4F5155;
	}

	a {
		color: #003399;
		background-color: transparent;
		font-weight: normal;
	}

	h1 {
		color: #444;
		background-color: transparent;
		border-bottom: 1px solid #D0D0D0;
		font-size: 19px;
		font-weight: normal;
		margin: 0 0 14px 0;
		padding: 14px 15px 10px 15px;
	}

	code {
		font-family: Consolas, Monaco, Courier New, Courier, monospace;
		font-size: 12px;
		background-color: #f9f9f9;
		border: 1px solid #D0D0D0;
		color: #002166;
		display: block;
		margin: 14px 0 14px 0;
		padding: 12px 10px 12px 10px;
	}

	#body {
		margin: 0 15px 0 15px;
	}

	p.footer {
		text-align: right;
		font-size: 11px;
		border-top: 1px solid #D0D0D0;
		line-height: 32px;
		padding: 0 10px 0 10px;
		margin: 20px 0 0 0;
	}

	#container {
		margin: 10px;
		border: 1px solid #D0D0D0;
		box-shadow: 0 0 8px #D0D0D0;
	}
	#body{ height: 500px; overflow-y: auto;}
	</style>
</head>
<body>

<div id="container">
	<h1><?php if ($log_file) { echo $log_file; } else { echo '可用的日志列表'; }?></h1>

	<div id="body">
		<?php 
		if ($file) {
			foreach ($file as $key => $value) {
				echo '<p><a href="/system?log_file='.$value.'">'.$value.'</a></p>';
			}
		}
		?>
	</div>

	<p class="footer">Page rendered in <strong>{elapsed_time}</strong> seconds. <?php echo  (ENVIRONMENT === 'development') ?  'CodeIgniter Version <strong>' . CI_VERSION . '</strong>' : '' ?></p>
</div>

</body>
<?php if ($log_file) {?>
<script type="text/javascript" src="//cdn.bootcss.com/jquery/2.2.3/jquery.min.js"></script>
<script>
jQuery(document).ready(function(){
	//获取提测成功率
  setInterval(function () {
    //获取我受理的任务量统计
    $.ajax({
      type: "GET",
      url: "/system/log?log_file=<?php echo $log_file; ?>",
      dataType: "text",
      success: function(data){
        if (data) {
          $("#body").html(data);
        }
      }
    });
  }, 1000);
});
</script>
<?php } ?>
</html>