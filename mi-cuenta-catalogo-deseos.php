<?php 

			include("lib/functions.php");
			session_start();
			if(isset($_GET['lang']))
				$_SESSION['lang'] = $_GET['lang'];
			if(!isset($_SESSION['lang']))
				$_SESSION['lang'] = '1';
			
			$db = database();
			include("lang/".language($db).".php");
			$db->disconnect();
			$pageTitle = 'Mi cuenta'; 
			include("header.php"); 
?>

			<script type="text/javascript">
					$(document).ready(function(e) {
						$('.whiteBg').addClass('bgCatalogo');
					});
				</script>

<?php 
			$sesion = FALSE;
			if ($_GET['login']=='out') {
				unset($_POST['form']);
				unset($_SESSION['nombre']);
				unset($_SESSION['email']);
				unset($_SESSION['id']);
				unset($_SESSION['eds']);

				

?>
					<script>
						document.location.href="login-catalogo-deseos.php";				
					</script>		
<?php 
			}
			
				
			if ($_POST['form']=='login') {
			
			
			$email = $_POST['username'];
			$password = $_POST['password'];

			$condi = "email = '$email' AND contrasena = '$password'";
			
			$numRows = $db->num_rows($db->select('*','eds_users', $condi));	
			
				if ($numRows > 0) {
					unset($_POST['form']);
					$user= array();
					$user = $db->fetch_assoc($db->select('*','eds_users', $condi));
					$_SESSION['nombre']= $user['nombre_completo'];
					$_SESSION['email'] = $user['email'];
					$_SESSION['id']= $user['id_eds_user'];
					$_SESSION['eds'] = $user['eds_id'];



					$ventas = array();
					$condi= "id_eds_user = ".$_SESSION['id'];

					$ventas_col= $db->select('*', 'ventas_adt');
					$numRows = $db->num_rows($db->select('*', 'productos'));

					$p1=$db->getFieldName($ventas_col, 3);
					$p2=$db->getFieldName($ventas_col, 4);
					$p3=$db->getFieldName($ventas_col, 5);
					$p4=$db->getFieldName($ventas_col, 6);
					
					$col= "SUM(`".$p1."`) ,".
						"SUM(`".$p2."`) ,".
						"SUM(`".$p3."`) ,".
						"SUM(`".$p4."`) ";
					 
					$ventas= array();
					$ventas= $db->query($col,'ventas_adt', $condi);
					unset($_POST['form']);
				
					
				}
				else{

?>

					<script>

						alert("No se ha podido iniciar sesión. Por favor intente de nuevo revisando su correo y contraseña");
						document.location.href="login-catalogo-deseos.php";				
					</script>		
<?php 
					}
			
		}elseif (!empty($_SESSION['nombre'])) {
			$nombre = $_SESSION['nombre'];
			

			$condi = "nombre_completo = '$nombre'";
			
			$numRows = $db->num_rows($db->select('*','eds_users', $condi));	
			
				if ($numRows > 0) {
					unset($_POST['form']);
					$user= array();
					$user = $db->fetch_assoc($db->select('*','eds_users', $condi));
					$_SESSION['nombre']= $user['nombre_completo'];
					$_SESSION['email'] = $user['email'];
					$_SESSION['id']= $user['id_eds_user'];
					$_SESSION['eds'] = $user['eds_id'];

					$ventas = array();
					$condi= "id_eds_user = ".$_SESSION['id'];

					$ventas_col= $db->select('*', 'ventas_adt');
					$numRows = $db->num_rows($db->select('*', 'productos'));

					$p1=$db->getFieldName($ventas_col, 3);
					$p2=$db->getFieldName($ventas_col, 4);
					$p3=$db->getFieldName($ventas_col, 5);
					$p4=$db->getFieldName($ventas_col, 6);
					
					$col= "SUM(`".$p1."`) ,".
						"SUM(`".$p2."`) ,".
						"SUM(`".$p3."`) ,".
						"SUM(`".$p4."`) ";
					 
					$ventas= array();
					$ventas= $db->query($col,'ventas_adt', $condi);
				}


		
				
				$result = $db->select('*', 'redenciones, noticia_txt, eds_users', $condi); 
						
				$filas = $db->num_rows($result);
				$condicion = "redenciones.eds_user_id = ".$_SESSION['id'];
				$condicion.= " AND redenciones.eds_user_id = eds_users.id_eds_user and redenciones.noticia_txt_notpk = noticia_txt.not_pk and noticia_txt.idi_pk=1 ORDER BY redenciones.fecha ";
				
				$resultado = array();
				$resultado= $db->select('redenciones.id, redenciones.fecha, noticia_txt.ntx_titulo, redenciones.estado', 'redenciones, noticia_txt, eds_users',$condicion);

		}

		
 ?>

				<?php if (isset($_SESSION['nombre'])): ?>

					<div id="titleArea" class="centerContent">
					<h1><?= $pageTitle; ?></h1>
				</div>
				<div class=" whiteBg catDeseosPage">
					<section class="centerContent">
						<div id="breadCrumbs">
							<div id="breadNav">Hola, <?= $_SESSION['nombre'] ?> . <a href="<?= $_SERVER['PHP_SELF']?>" class="cAcLink">Mi cuenta</a></div> 
							<a href="<?= $_SERVER['PHP_SELF'].'?login=out'?>" id="breadContact">Cerrar sesión</a>
						</div>		
						<div class="shadow"></div>
						<div class="clear"></div>
						<div id="productList">			
							<div class="clear20"></div>
							<div class="testiTitle">
								<i class="fa fa-star"></i>
								<h2>Balance Actual</h2>			
							</div>
							<div class="clear20"></div>
							<table class="tablaBalance" cellpadding="0" cellspacing="0">

								<?php while($row=$db->fetch_array($ventas)): ?>				

									<tr>
									<?php for ($i=0; $i < $numRows; $i++) :?>
										 
										 <tr>
										 	<th><?=$col = $db->getFieldName($ventas_col, $i+3) ?></th>
												<?php $p = "SUM(`".$col."`)"; ?>
												 <?php $v =$row["$p"] ?>
												 <?php if ($v==NULL): ?>
												 	<td>0</td>
												 <?php else: ?>
												 	<td><?= $v ?></td>
												 <?php endif ?>
										 	<?$_SESSION[$col]=$v;?>
										 </tr>
									<?php endfor ?>
									</tr>

								<?php endwhile ?>

							</table>
							<div class="clear50"></div>
							<div class="testiTitle">
								<i class="fa fa-list-alt"></i>
								<h2>Historial de redenciones</h2>			
							</div>
							<div class="clear20"></div>
							<table class="tablaBalance" cellpadding="0" cellspacing="0">
								

							<?php if ($filas>0): ?>

								<?php while($row=$db->fetch_array($resultado)): ?>				

									<tr>
										<td><?= $row['fecha'] ?></td>
										<td><?= $row['ntx_titulo']; ?></td>
										<td><?= $row['estado']; ?></td>

									</tr>				

								<?php endwhile ?>

							<?php else: ?>
								<tr>No se ha registrado ninguna solicitud de redención</tr>			
							<?php endif ?>	
							
							</table>
							<div class="clear20"></div>		
						</div>
					</section>
					<div class="clear50"></div>		
				</div>
				<? include("footer.php"); ?>

				<?php else: ?>
					<script>

						alert("No se ha podido iniciar sesión. Por favor intente de nuevo revisando su correo y contraseña");
						document.location.href="login-catalogo-deseos.php";				
					</script>	
				<?php endif ?>
				
				







