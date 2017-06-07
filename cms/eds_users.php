<?
include("lib/database.php");
$db = new database();
$module = $db->fetch_array($db->select('nombre', 'modulo_noticias', "pk='$_GET[id]'"));
$db->disconnect();
$pageTitle = 'Gestión Isleros';
$menuActive = 3; ?>
<? include("header.php"); ?> <?

$titleLabel = $_GET['id']=='2' ? 'Título' : 'Nombre';

$module = $db->fetch_array($db->select('*', 'modulo_noticias', "pk='$_GET[id]'"));
$editor = $module['editor']=='1' ? " class=\"editorTextArea\"" : '';
$editorResumen = $_GET['id']=='1' ? " class=\"editorTextArea\"" : '';
$thumbnail = '120x90';
$dimensions = $thumbnail.','.str_replace(' ', '', $module['tamano_img']);

// Array de idiomas
$result = $db->select('idi_pk, idi_locale, idi_txt', 'idioma', "idi_estado='1' ORDER BY idi_pk");
$numLanguages = $db->num_rows($result);
while($row = $db->fetch_array($result))
{
	$id = $row['idi_pk'];
	$languages[$id] = array('name'=>$row['idi_txt'], 'abbreviation'=>$row['idi_locale']);
	$descText[$id] = $numLanguages>1 ? "Descripción ($row[idi_locale])" : 'Descripción';
}


if($_SERVER['REQUEST_METHOD']=='POST')
{
	if($_POST['form']=='insert')
	{
			$columns = 'nombre_completo, email, contrasena, eds_id, redencion_pendiente';

			$nombre_completo = $_POST['nombre_islero'];
			$email = $_POST['email'];
			$contrasena = $_POST['contrasena'];
			$eds = $_POST['eds'];

			$values = "'$nombre_completo', '$email','$contrasena', '$eds', 1";

		if($db->insert('eds_users', $columns, $values))
		{
			// $not_pk = $db->last_insert_id();
			// chdir('../');
			// $updir = getcwd().DIRECTORY_SEPARATOR.'uploads'.DIRECTORY_SEPARATOR.'news'.DIRECTORY_SEPARATOR.$not_pk.DIRECTORY_SEPARATOR;
			// mkdir($updir);
			// chdir('cms/');
			

			

			echo "<script type=\"text/javascript\" language=\"javascript\"> alert('El registro ha sido creado.') </script>";
			echo "<script type=\"text/javascript\" language=\"javascript\"> window.location='".$_SERVER['PHP_SELF']."?id=".$_GET['id']."' </script>";
			exit();
		}else
			echo "<script type=\"text/javascript\" language=\"javascript\"> alert('No fue posible crear el registro.') </script>";
	}
	elseif($_POST['form']=='update')
	{
		$nombre_completo = $_POST['nombre_islero'];
		$email = $_POST['email'];
		$contrasena = $_POST['contrasena'];
		$eds = $_POST['eds'];	

		$columns = "nombre_completo = '".$nombre_completo."' , email = '".$email."' , contrasena = '".$contrasena."' , eds_id = ". $eds ;
		
		$condi= "id_eds_user = $_GET[regId]";
			
		if($db->update('eds_users', $columns,$condi ))
		{
			// $not_pk = $db->last_insert_id();
			// chdir('../');
			// $updir = getcwd().DIRECTORY_SEPARATOR.'uploads'.DIRECTORY_SEPARATOR.'news'.DIRECTORY_SEPARATOR.$not_pk.DIRECTORY_SEPARATOR;
			// mkdir($updir);
			// chdir('cms/');
			

			

			echo "<script type=\"text/javascript\" language=\"javascript\"> alert('El registro ha sido creado.') </script>";
			echo "<script type=\"text/javascript\" language=\"javascript\"> window.location='".$_SERVER['PHP_SELF']."?id=".$_GET['id']."' </script>";
			exit();
		}
		else
			echo "<script type=\"text/javascript\" language=\"javascript\"> alert('No fue posible crear el registro.') </script>";
	}elseif ($_POST['form']=='insert_venta') {
		
				
		$id_eds_user = $_GET['regId'];

		$condi = "id_eds_user = $id_eds_user";

		$columnas = "`id_eds_user`, ";
		$date = date("Y-m-d");	
		$columnas.= "`fecha` ";

		$ventas= $db->select('*', 'ventas_adt');
		$numRows = $db->num_rows($db->select('*', 'productos'));
		$values = "'$id_eds_user', '$date'";

		for ($i=0; $i < $numRows; $i++) {
			$col = $db->getFieldName($ventas, $i+3);
			$p ="prod".$i;
			$p = $_POST[$col];
			$columnas.= ", `$col` ";
			$values.= ", '$p'";

						
		 }
		 
		
		if($db->insert('ventas_adt', $columnas, $values))
		 {
			
			echo "<script type=\"text/javascript\" language=\"javascript\"> alert('El registro ha sido creado.') </script>";
			echo "<script type=\"text/javascript\" language=\"javascript\"> window.location='".$_SERVER['PHP_SELF']."?id=".$_GET['id']."&regId=".$_GET['regId']."&interfaz=venta' </script>";
		 }else
			echo "<script type=\"text/javascript\" language=\"javascript\"> alert('No fue posible crear el registro.') </script>";
	}
}
else
{
	if(isset($_GET['delete']))
	{
		
		$condi = "id_eds_user =". $_GET['delete'];
	

		if($db->delete('eds_users', $condi))  // Eliminar registro principal
		{
					
			echo "<script type=\"text/javascript\" language=\"javascript\"> alert('El registro ha sido eliminado.') </script>";
		}
		 else
			echo "<script type=\"text/javascript\" language=\"javascript\"> alert('No fue posible eliminar el registro.') </script>";
	}
	if(isset($_GET['deleteRedencion']))
	{
		
		$condi = "id =". $_GET['deleteRedencion'];
		
		$u= $db->select('COUNT(estado)','redenciones', "eds_user_id=".$_GET['user']." and estado='Por entregar'");
		$usuario = $db->fetch_row($u);

		if($db->delete('redenciones', $condi))  
		{
			$condi ="id =".$_GET['ventas_adt'];

			if ($db->delete('ventas_adt', $condi))
			{	
				if($usuario[0]==1)
		 		{	
		 			$db->update('eds_users',"redencion_pendiente = '1'","id_eds_user=".$_GET['user']);
		 			echo "<script> alert('Ahora el usuario no tiene redenciones pendientes.') </script>";
		 		}
				
				echo "<script type=\"text/javascript\" language=\"javascript\"> alert('El registro ha sido eliminado.') </script>";
			}else
			{
				echo "<script type=\"text/javascript\" language=\"javascript\"> alert('No fue posible eliminar el registro.') </script>";
			}
					
			
		}
		 else
			echo "<script type=\"text/javascript\" language=\"javascript\"> alert('No fue posible eliminar el registro.') </script>";
	}

	if(isset($_GET['entregarProducto']))
	{
		
		$condi = "id =". $_GET['entregarProducto'];
		$date = date("Y-m-d");
		$columns = "fecha = '".$date."', estado = 'Entregado'";
		
		$u= $db->select('COUNT(estado)','redenciones', "eds_user_id=".$_GET['id_user']." and estado='Por entregar'");
		$usuario = $db->fetch_row($u);

			

		if($db->update('redenciones',$columns, $condi))  
		{
		 		if($usuario[0]==1)
		 		{	
		 			$db->update('eds_users',"redencion_pendiente = '1'","id_eds_user=".$_GET['id_user']);
		 			echo "<script> alert('Ahora el usuario no tiene redenciones pendientes.') </script>";
		 		}
		 }
		 else
		 	echo "<script type=\"text/javascript\" language=\"javascript\"> alert('No fue posible actualizar el registro.') </script>";
	}
}

if($_GET['interfaz']=='insert')
{ 
	$estaciones = array();
	$result = $db->select('id_eds, nombre', 'eds');	

	for($i=0; $row=$db->fetch_array($result); $i++){
		$estaciones[$i] = $row;
	}	

	?>
	<header>
	<div class="fLeft">
		<h1>Crear <?= $module['nombre']; ?></h1>
	</div>
	<div class="fRight"><a href="<?= $_SERVER['PHP_SELF']; ?>"><span class="icon">]</span> Regresar al listado</a></div>
	<div class="clear"></div>
	</header>
	<div class="contentPane">
		<form id="newsForm" name="newsForm" action="<?= $_SERVER['PHP_SELF'].'?interfaz=insert'; ?>" enctype="multipart/form-data" method="post" onsubmit="return validar()">
			<input type="hidden" name="form" value="insert">
			<table class="formTable w875"> 
				<!-- <?
				foreach($languages as $key=>$value)
				{ ?>
					<tr>
						<th><span class="required">*</span> <? echo $titleLabel; if($numLanguages>1) echo " ($value[abbreviation])"; ?>:</th>
						<td><input type="text" id="titulo[<?= $key; ?>]" name="titulo[<?= $key; ?>]" size="100" maxlength="255" class="validate[required]"></td>
					</tr> <?
				}
				?> -->
				<tr>
					<th><span class="required">*</span>Nombre Completo:</th>
					<td><input id="nombre_islero" type="text" name="nombre_islero" size="90" maxlength="255" class="validate[required]"></td>

				</tr>
				<tr>
					<th><span class="required">*</span>Email:</th>
					<td><input id="email" type="email" name="email" size="90" maxlength="255" class="validate[required]"></td>

				</tr>
				<tr>
					<th><span class="required">*</span>Contraseña:</th>
					<td><input id="contrasena" type="password" name="contrasena" size="90" maxlength="255" class="validate[required]"></td>

				</tr>

				<!-- <tr>
					<th><span class="required">Nombre: </span></th>
					<td><input type="text" name="nombre_eds" size="100" maxlength="255" class="validate[required]"></td>

				</tr>
 -->
				<tr>
					<th><span class="required">*</span> EDS:</th>
					<td>
						<select id="eds" name="eds" class="validate[required]">
							<?php foreach ($estaciones as $eds): ?>
								<option value="<?= $eds['id_eds'] ?>"><?= $eds['nombre'] ?></option>
								
							<?php endforeach ?>
												
						</select>
					</td>
				</tr>

			</table>
			
			<div class="clear10"></div>
			<div align="center"><input type="submit" value="Guardar"></div>
		</form>
	</div> 
<?
}
else
{
	if($_GET['interfaz']=='edit')
	{
		
		$estaciones = array();
		$user = array();
		$result = $db->select('id_eds, nombre', 'eds');	

		for($i=0; $row=$db->fetch_array($result); $i++){
			$estaciones[$i] = $row;
		}

		$id_eds_user = $_GET['regId'];
		
		$condi = "id_eds_user = '$id_eds_user'";
		$result = $db->select('id_eds_user, nombre_completo, email, contrasena', 'eds_users', $condi); 

		$user = $db->fetch_assoc($result);
		
		
		?>
		<header>
			<div class="fLeft">
				<h1>Editar <?= $module['nombre']; ?></h1>
			</div>
			<div class="fRight"><a href="<?= $_SERVER['PHP_SELF'].'?id='.$_GET['id']; ?>"><span class="icon">]</span> Regresar al listado</a></div>
			<div class="clear"></div>
		</header>
		<div class="contentPane">
			<form id="newsForm" name="newsForm" action="<?= $_SERVER['PHP_SELF'].'?id='.$_GET['id'].'&regId='.$_GET['regId'].'&interfaz=edit'; ?>" enctype="multipart/form-data" method="post" onsubmit="return validar()">
				<input type="hidden" name="form" value="update">
				
				<table class="formTable w875"> 
				<!-- <?
				foreach($languages as $key=>$value)
				{ ?>
					<tr>
						<th><span class="required">*</span> <? echo $titleLabel; if($numLanguages>1) echo " ($value[abbreviation])"; ?>:</th>
						<td><input type="text" id="titulo[<?= $key; ?>]" name="titulo[<?= $key; ?>]" size="100" maxlength="255" class="validate[required]"></td>
					</tr> <?
				}
				?> -->
				<tr>
					<th><span class="required">*</span>Nombre Completo:</th>
					<td><input id="nombre_islero" type="text" name="nombre_islero" size="90" maxlength="255" class="validate[required]" value="<?= $user["nombre_completo"]?>"></td>

				</tr>
				<tr>
					<th><span class="required">*</span>Email:</th>
					<td><input id="email" type="email" name="email" size="90" maxlength="255" class="validate[required]" value="<?= $user['email']?>"></td>

				</tr>
				<tr>
					<th><span class="required">*</span>Contraseña:</th>
					<td><input id="contrasena" type="password" name="contrasena" size="90" maxlength="255" class="validate[required]" value="<?= $user['contrasena']?>"></td>

				</tr>

				<!-- <tr>
					<th><span class="required">Nombre: </span></th>
					<td><input type="text" name="nombre_eds" size="100" maxlength="255" class="validate[required]"></td>

				</tr>
 -->
				<tr>
					<th><span class="required">*</span> EDS:</th>
					<td>
						<select id="eds" name="eds" class="validate[required]">
							<?php foreach ($estaciones as $eds): ?>
								<option value="<?= $eds['id_eds'] ?>"><?= $eds['nombre'] ?></option>
								
							<?php endforeach ?>
												
						</select>
					</td>
				</tr>

			</table>
				
				<div class="clear10"></div>
				<div align="center"><input type="submit" value="Guardar"></div>
			</form>
		</div> <?
	}

	elseif ($_GET['interfaz']=='venta') {

		$id_eds_user = $_GET['regId'];
		
		$condi = "id_eds_user = '$id_eds_user'";
		$result = $db->select('id_eds_user, nombre_completo, email, contrasena', 'eds_users', $condi); 
		$user = $db->fetch_assoc($result);

		$ventas_col= $db->select('*', 'ventas_adt');
		$numRows = $db->num_rows($db->select('*', 'productos'));

		$ventas = array();
		
		
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
		

				
		?>
		<header>
			<div class="fLeft">
				<h1>Registrar Venta <?= $module['nombre']; ?></h1>
			</div>
			<div class="fRight">
				<!-- <a id="insertProduct" href="<?= $_SERVER['PHP_SELF'].'?id='.$_GET['id'].'&interfaz=insert_venta' ?>" class="newItem" onclick="return insertProduct()"><span class="icon" style="margin-left: 5px">+</span> Agregar producto a la venta</a> -->
				<a href="<?= $_SERVER['PHP_SELF'].'?id='.$_GET['id']; ?>"><span class="icon">]</span> Regresar al listado</a>
			</div>
			<div class="clear"></div>
		</header>
		<div class="contentPane">
			<form id="newsForm" name="newsForm" action="<?= $_SERVER['PHP_SELF'].'?id='.$_GET['id'].'&regId='.$_GET['regId'].'&interfaz=insert_venta'; ?>" enctype="multipart/form-data" method="post" onsubmit="return validarVentas()">
				<input type="hidden" name="form" value="insert_venta">
			
				<table width="100% "cellspacing="0" cellpadding="0" class="dataTable">
					<tr>
						<th width="100">Fecha</th>
						<th width="100">Vendedor</th>
						<?php for ($i=0; $i < $numRows; $i++) :?>
							<th width="100"><?=$col = $db->getFieldName($ventas_col, $i+3);	?>
								
							</th>
						<?php endfor ?>	
					</tr>
					<!-- Fila para registrar la venta del dia -->
					<tr>
						<td><?= $date= date('Y-m-d'); ?></td>
						<td><?= $user['nombre_completo'] ?></td>
						
						<?php for ($i=0; $i < $numRows; $i++) :?>
							<? $col = $db->getFieldName($ventas_col, $i+3); $j=$i+1;?>
							<td><input id="prod<?=$j?>" class="validate[required]" type="number" name="<?= "$col" ?>" value="0" style="width: 80px" min="0"></td>
						<?php endfor ?>	

					</tr>
					
				</table> 
				
				<div class="clear10"></div>
				<div align="center"><input type="submit" value="Guardar"></div>
			</form>
			<br>
			<br>

			<header>
				<div class="fLeft">
					<h1>Balance Actual </h1>
				</div>
				<div class="clear"></div>
			</header>

			<table width="100% "cellspacing="0" cellpadding="0" class="dataTable">
					<tr>
						<th width="100">Fecha</th>
						<th width="100">Vendedor</th>
						<?php for ($i=0; $i < $numRows; $i++) :?>
							<th width="100"><?=$col = $db->getFieldName($ventas_col, $i+3) ?></th>
						<?php endfor ?>	
					</tr>
					
					<?php while($row=$db->fetch_array($ventas)): ?>				

					<tr>
						<td><?= date('Y-m-d')?></td>
						<td><?= $user['nombre_completo']?></td>
					<?php for ($i=0; $i < $numRows; $i++) :?>
						 	<?$col = $db->getFieldName($ventas_col, $i+3) ?> 	
						<?php $p = "SUM(`".$col."`)"; ?>
						 <td><?= $v =$row["$p"] ?></td>
					<?php endfor ?>
					</tr>

					<?php endwhile ?>
					
			</table>
			<div class="clear10"></div>
			<div class="clear10"></div>

	<? 

		$filas = $db->num_rows($ventas_col);
	?>
	<?php if($filas >0) :?>

		<? $offset = isset($_GET['offset']) ? intval($_GET['offset']) : 0; 

			$condicion = "ventas_adt.".$condi;
			$condicion.= " AND ventas_adt.id_eds_user = eds_users.id_eds_user ORDER BY ventas_adt.id DESC LIMIT ".$conf['settings']->pager." OFFSET $offset";
			$result= $db->select('*', 'ventas_adt, eds_users',$condicion);

			//$res = $db->fetch_array($result);
		?>
			<header>
				<div class="fLeft">
					<h1>Registros de ventas </h1>
				</div>
				<div class="clear"></div>
			</header>
			<?php $offset = isset($_GET['offset']) ? intval($_GET['offset']) : 0;  ?>
	
			<table width="100% "cellspacing="0" cellpadding="0" class="dataTable">
					<tr>
						<th width="100">Fecha</th>
						<th width="100">Vendedor</th>
						<?php for ($i=0; $i < $numRows; $i++) :?>
							<th width="100"><?=$col = $db->getFieldName($ventas_col, $i+3) ?></th>
						<?php endfor ?>	
					</tr>
					
					
					<?php while($row=$db->fetch_array($result)): ?>				

						<tr>
							<td><?= $row['fecha'] ?></td>
							<td><?= $row['nombre_completo']; ?></td>
							<?php for ($i=0; $i < $numRows; $i++) :?>
							 <td width="100"><?= $row[$db->getFieldName($ventas_col, $i+3)];?> </td>
						<?php endfor ?>	
						</tr>

					<?php endwhile ?>

					
				</table>
					<?	if($pag=pager($numRows, $conf['settings']->pager, $offset))  // Si es necesario paginar
				{
					$currentUrl = "$_SERVER[PHP_SELF]?id=$_GET[id]";
					$prev = is_null($pag['prevOffset']) ? 'Anterior' : "<a href=\"$currentUrl&offset=$pag[prevOffset]\">Anterior</a>";
					$next = is_null($pag['nextOffset']) ? 'Siguiente' : "<a href=\"$currentUrl&offset=$pag[nextOffset]\">Siguiente</a>"; ?>
					<div class="pager">
						<div class="fLeft"><?= "Viendo del $pag[from] al $pag[to] de $numRows"; ?></div>
						<div class="fRight">
							<?= $prev; ?>
							<select onchange="javascript:window.location='<?= "$currentUrl&offset="; ?>'+this.value"> <?
								foreach($pag['pages'] as $num=>$val)
								{
									$selected = $val['selected'] ? ' selected' : ''; ?>
									<option value="<?= $val['offset']; ?>"<?= $selected; ?>><?= $num; ?></option> <?
								} ?>
							</select>
							<?= $next; ?>
						</div>
						<div class="clear"></div>
					</div> <?
				}?> 

	<?php else: ?>
	
				<p>No se ha registrado ninguna venta. </p>

	<?php endif ?>
		</div><?
	}

	elseif ($_GET['interfaz']=='redenciones') {

		$id_eds_user = $_GET['regId'];
		
		$condi = "eds_user_id = '$id_eds_user'";
		$result = $db->select('*', 'redenciones', $condi); 
				
		$numRows = $db->num_rows($result);
		?>

		<?php if ($numRows>0): ?>
			<? $offset = isset($_GET['offset']) ? intval($_GET['offset']) : 0; 

				$condicion = "redenciones.".$condi;
				$condicion.= " AND redenciones.eds_user_id = eds_users.id_eds_user and redenciones.noticia_txt_notpk = noticia_txt.not_pk and noticia_txt.idi_pk=1 ORDER BY redenciones.fecha LIMIT ".$conf['settings']->pager." OFFSET $offset";

				$resultado= $db->select('redenciones.id, redenciones.fecha, noticia_txt.ntx_titulo, redenciones.estado, redenciones.ref_ventas_adt', 'redenciones, noticia_txt, eds_users',$condicion);

					//$res = $db->fetch_array($result);
			?>
				<header>
					<div class="fLeft">
						<h1>Historial de redenciones </h1>
					</div>
					<div class="fRight">
						<!-- <a id="insertProduct" href="<?= $_SERVER['PHP_SELF'].'?id='.$_GET['id'].'&interfaz=insert_venta' ?>" class="newItem" onclick="return insertProduct()"><span class="icon" style="margin-left: 5px">+</span> Agregar producto a la venta</a> -->
						<a href="<?= $_SERVER['PHP_SELF'].'?id='.$_GET['id']; ?>"><span class="icon">]</span> Regresar al listado</a>
					</div>
					<div class="clear"></div>
				</header>
				<div class="contentPane">
				<?php $offset = isset($_GET['offset']) ? intval($_GET['offset']) : 0; ?>
					<table width="100% "cellspacing="0" cellpadding="0" class="dataTable">
							<tr>
								<th width="100">Fecha</th>
								<th width="300">Nombre Producto</th>
								<th width="100">Estado</th>
								<th>Opciones</th>
									
							</tr>
								
								
						<?php while($row=$db->fetch_array($resultado)): ?>				

							<tr>
								<td><?= $row['fecha'] ?></td>
								<td><?= $row['ntx_titulo']; ?></td>
								<td><?= $row['estado']; ?></td>

								<?php if ($row['estado']=='Entregado'): ?>
								<td>&nbsp;</td>	
								<?php else: ?>
									<td>
									<a class="blueLink" onclick="javascript:entregarProducto('<?= $row['id']; ?>', '<?= str_replace(array("'", "\""), '`', $row['ntx_titulo']); ?>','<?= $id_eds_user; ?>')"><span class="icon">%</span> Entregar Producto</a> 

									<a class="redLink" onclick="javascript:removalConfirmRedeption('<?= $row['id']; ?>', '<?= str_replace(array("'", "\""), '`', $row['ntx_titulo']); ?>', '<?= $row['ref_ventas_adt']; ?>','<?= $id_eds_user; ?>')"><span class="icon">X</span> Cancelar solicitud</a> 
								</td>
								<?php endif ?>
								
							</tr>

						<?php endwhile ?>

								
					</table> 
					<?	if($pag=pager($numRows, $conf['settings']->pager, $offset))  // Si es necesario paginar
				{
					$currentUrl = "$_SERVER[PHP_SELF]?id=$_GET[id]";
					$prev = is_null($pag['prevOffset']) ? 'Anterior' : "<a href=\"$currentUrl&offset=$pag[prevOffset]\">Anterior</a>";
					$next = is_null($pag['nextOffset']) ? 'Siguiente' : "<a href=\"$currentUrl&offset=$pag[nextOffset]\">Siguiente</a>"; ?>
					<div class="pager">
						<div class="fLeft"><?= "Viendo del $pag[from] al $pag[to] de $numRows"; ?></div>
						<div class="fRight">
							<?= $prev; ?>
							<select onchange="javascript:window.location='<?= "$currentUrl&offset="; ?>'+this.value"> <?
								foreach($pag['pages'] as $num=>$val)
								{
									$selected = $val['selected'] ? ' selected' : ''; ?>
									<option value="<?= $val['offset']; ?>"<?= $selected; ?>><?= $num; ?></option> <?
								} ?>
							</select>
							<?= $next; ?>
						</div>
						<div class="clear"></div>
					</div> <?
				}?>
				</div>

			<?php else: ?>

				<header>
					<div class="fLeft">
						<h1>Historial de redenciones </h1>
					</div>
					<div class="fRight">
						<!-- <a id="insertProduct" href="<?= $_SERVER['PHP_SELF'].'?id='.$_GET['id'].'&interfaz=insert_venta' ?>" class="newItem" onclick="return insertProduct()"><span class="icon" style="margin-left: 5px">+</span> Agregar producto a la venta</a> -->
						<a href="<?= $_SERVER['PHP_SELF'].'?id='.$_GET['id']; ?>"><span class="icon">]</span> Regresar al listado</a>
					</div>
					<div class="clear"></div>
				</header>
				<div class="contentPane">

				<?php $offset = isset($_GET['offset']) ? intval($_GET['offset']) : 0; ?>
					<table width="100% "cellspacing="0" cellpadding="0" class="dataTable">
							<tr>
								<th width="100">Fecha</th>
								<th width="300">Nombre Producto</th>
								<th width="100">Estado</th>
								<th>Opciones</th>
									
							</tr>

							<tr>
								No se ha registrado ninguna redención.
							</tr>
					 </table>
			<?	if($pag=pager($numRows, $conf['settings']->pager, $offset))  // Si es necesario paginar
				{
					$currentUrl = "$_SERVER[PHP_SELF]?id=$_GET[id]";
					$prev = is_null($pag['prevOffset']) ? 'Anterior' : "<a href=\"$currentUrl&offset=$pag[prevOffset]\">Anterior</a>";
					$next = is_null($pag['nextOffset']) ? 'Siguiente' : "<a href=\"$currentUrl&offset=$pag[nextOffset]\">Siguiente</a>"; ?>
					<div class="pager">
						<div class="fLeft"><?= "Viendo del $pag[from] al $pag[to] de $numRows"; ?></div>
						<div class="fRight">
							<?= $prev; ?>
							<select onchange="javascript:window.location='<?= "$currentUrl&offset="; ?>'+this.value"> <?
								foreach($pag['pages'] as $num=>$val)
								{
									$selected = $val['selected'] ? ' selected' : ''; ?>
									<option value="<?= $val['offset']; ?>"<?= $selected; ?>><?= $num; ?></option> <?
								} ?>
							</select>
							<?= $next; ?>
						</div>
						<div class="clear"></div>
					</div> <?
				}?>


				</div>	 
			
						

			<?php endif ?>		

	<?
	}


	else
	{
		$numRows = $db->num_rows($db->select('*', 'eds_users')); 

	?>
	
		

		<header>
		<div class="fLeft"><h1><?= $pageTitle; ?></h1></div>
		<div class="fRight" ><a href="<? echo $_SERVER['PHP_SELF'].'?interfaz=insert'?>" class="newItem"><span class="icon" >+</span> Crear</a></div>
		<div class="clear"></div>
		</header> <?
		if($numRows>0)
		{
			$offset = isset($_GET['offset']) ? intval($_GET['offset']) : 0;
			reset($languages);
			
			
			$condi = "eds_users.eds_id = eds.id_eds ORDER BY `eds_users`.`redencion_pendiente`, eds_users.nombre_completo ASC LIMIT ".$conf['settings']->pager." OFFSET $offset";
			
			$result = $db->select('eds_users.id_eds_user, eds_users.nombre_completo, eds_users.email, eds.nombre, eds_users.redencion_pendiente', 'eds_users, eds', $condi);
			?>
			<div class="contentPane">
				<table width="100% "cellspacing="0" cellpadding="0" class="dataTable">
					<tr>
						<th width="200">Nombre Completo</th>
						<th width="200">Correo</th>
						<th width="200">EDS</th>
						<th width="200">Opciones</th>
					</tr><?

					while($row=$db->fetch_array($result))
					{
					 ?>
						<tr>
						<?php if ($row['redencion_pendiente']==0): ?>
							<td style="font-weight: bold"><span style="color: red">*</span><?= $row['nombre_completo']; ?></td>
						<?php else: ?>
							<td><?= $row['nombre_completo']; ?></td>
						<?php endif ?>
							<td><?= $row['email']; ?></td>
							<td><?= $row['nombre']; ?></td>
							<td>

								<a href="<?= $_SERVER['PHP_SELF'].'?id='.$_GET['id'].'&regId='.$row['id_eds_user'].'&interfaz=venta'?>" class="blueLink"><span class="icon">$</span> Registrar Venta</a>

								<a href="<?= $_SERVER['PHP_SELF'].'?id='.$_GET['id'].'&regId='.$row['id_eds_user'].'&interfaz=edit'?>" class="blueLink"><span class="icon">V</span> Editar</a>

								<a href="<?= $_SERVER['PHP_SELF'].'?id='.$_GET['id'].'&regId='.$row['id_eds_user'].'&interfaz=redenciones'?>" class="blueLink"><span class="icon">E</span> Ver redenciones</a> 
								
								<a class="redLink" onclick="javascript:removalConfirm('<?= $row['id_eds_user']; ?>', '<?= str_replace(array("'", "\""), '`', $row['nombre_completo']); ?>')"><span class="icon">X</span> Borrar</a> 
							</td>
						</tr> <?
					} ?>
				</table> <?
				if($pag=pager($numRows, $conf['settings']->pager, $offset))  // Si es necesario paginar
				{
					$currentUrl = "$_SERVER[PHP_SELF]?id=$_GET[id]";
					$prev = is_null($pag['prevOffset']) ? 'Anterior' : "<a href=\"$currentUrl&offset=$pag[prevOffset]\">Anterior</a>";
					$next = is_null($pag['nextOffset']) ? 'Siguiente' : "<a href=\"$currentUrl&offset=$pag[nextOffset]\">Siguiente</a>"; ?>
					<div class="pager">
						<div class="fLeft"><?= "Viendo del $pag[from] al $pag[to] de $numRows"; ?></div>
						<div class="fRight">
							<?= $prev; ?>
							<select onchange="javascript:window.location='<?= "$currentUrl&offset="; ?>'+this.value"> <?
								foreach($pag['pages'] as $num=>$val)
								{
									$selected = $val['selected'] ? ' selected' : ''; ?>
									<option value="<?= $val['offset']; ?>"<?= $selected; ?>><?= $num; ?></option> <?
								} ?>
							</select>
							<?= $next; ?>
						</div>
						<div class="clear"></div>
					</div> <?
				} ?>
			</div><?
		}else{
		?>
			<p>No se ha registrado ningún Islero. Para crearlo presione el boton Crear</p>
		<?

		}
	}
} ?>
<script type="text/javascript" src="js/jquery.validationEngine-es.js"></script>
<script type="text/javascript" src="js/jquery.validationEngine.js"></script>
<script type="text/javascript">
<!--
	$(document).ready(function(){
		$("#newsForm").validationEngine();

		// Datepicker
		$('.dateSelector').datepicker({
			showOn: "both",
			buttonImage: "images/calendar.png",
			buttonImageOnly: true,
			buttonText: "Calendario",
			dateFormat: "dd/mm/yy",
			firstDay: 1,
		}); <?
		if($_GET['interfaz']=='insert')
		{ ?>
			$('.dateSelector').datepicker('setDate', 'today'); <?
		} ?>


		// Acordeon
		$('#accordion').accordion({
			active:false,
			collapsible:true,
			heightStyle: "content",
		});

		
		
		// Limpiar el input de la descripcion (Imágenes)
		$('.previewPicture input[type=text]').focus(function(){ <?
			foreach($languages as $key=>$value)
			{ ?>
				if($(this).val()=="<?= $descText[$key]; ?>")
				{
					$(this).val('');
				} <?
			} ?>
		});

		$('.previewPicture input[type=text]').blur(function(){
			if($(this).val()=='')
			{ <?
				foreach($languages as $key=>$value)
				{
					$class = 'desc'.ucfirst(mb_strtolower($value['abbreviation'])); ?>
					if($(this).attr('class')=='<?= $class; ?>')
						defaultText = "<?= $descText[$key]; ?>"; <?
				} ?>
				$(this).val(defaultText);
			}
		});

		
		document.getElementById()		



	});

	function validar(){

		nombre_islero = document.getElementById("nombre_eds").value;
		email = document.getElementById("email").value;
		contrasena = document.getElementById("contrasena");


	
		if( nombre_islero == null || nombre_islero.length == 0 || /^\s+$/.test(nombre_islero) ) {
		 return false;
		}
		if( !(/\w+([-+.']\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)/.test(email)) ) {
		 return false;
		}
		if( contrasena == null || valor.length < 4 || /^\s+$/.test(contrasena) ) {
		 return false;
		}
			}
	function validarVentas(){

		
		p1 = document.getElementById("prod1").value;
		p2 = document.getElementById("prod2").value;
		p3 = document.getElementById("prod3").value;
		p4 = document.getElementById("prod4").value;

		
		if( p1==0 && p2==0 && p3==0 && p4==0 ) {
			alert("Por favor ingrese valores validos");
		  return false;
		}
	}

	function removalConfirm(regId, regTitle)
	{
		if(confirm("¿Confirma la eliminación de '"+regTitle+"'?"))
			window.location = '<?= "$_SERVER[PHP_SELF]?delete="; ?>'+regId;
	}

	function removalConfirmRedeption(regId, regTitle, regVentas, regUserID)
		{
		if(confirm("¿Confirma la eliminación de la solicitud de '"+regTitle+"'?")){
			
			window.location = '<?= "$_SERVER[PHP_SELF]?deleteRedencion="; ?>'+regId+"&ventas_adt="+regVentas+"&user="+regUserID;
		}
	}

	function entregarProducto(regId, regTitle, regUserID)
	{
		alert("El producto "+regTitle+" ha sido entregado");
		window.location = '<?= "$_SERVER[PHP_SELF]?entregarProducto="; ?>'+regId+"&id_user="+regUserID;
	}

	function enviar()
	{
		$('.demoField').each(function(key, element){
			if($.trim($(element).val())!=''){
				$(element).val(' '+$.trim($(element).val()));
			}
		});
		return true;
	}

	
-->
</script>
<? include("footer.php"); ?>