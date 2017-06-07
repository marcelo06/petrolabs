<?
$pageTitle = "Cuentas de usuario";
$menuActive = 4; ?>
<? include("header.php"); ?> <?

if($_SERVER['REQUEST_METHOD']=='POST')
{
	if($_POST['form']=='insert')
	{
		if($db->num_rows($db->select('usu_pk', 'usuario', "usu_login='$_POST[usuario]' LIMIT 1"))>0)
		{
			echo "<script type=\"text/javascript\" language=\"javascript\"> alert('Ya existe el usuario \'$_POST[usuario]\'.'); window.history.back(); </script>";
			exit();
		}
		else
		{
			$userPass = md5($_POST['userPass']);
			$values = "'$_POST[perfil]', '$_POST[usuario]', '$userPass', '$_POST[nombre]', '$_POST[email]'";
			if($db->insert('usuario', 'per_pk, usu_login, usu_clave, usu_nombre, usu_email', $values))
			{
				echo "<script type=\"text/javascript\" language=\"javascript\"> alert('La cuenta de usuario ha sido creada.'); window.location='$_SERVER[PHP_SELF]'; </script>";
				exit();
			}
			else
				echo "<script type=\"text/javascript\" language=\"javascript\"> alert('No fue posible crear la cuenta de usuario.') </script>";
		}
	}
	elseif($_POST['form']=='update')
	{
		$set = "per_pk='$_POST[perfil]', usu_nombre='$_POST[nombre]', usu_email='$_POST[email]'";
		if(isset($_POST['userPass']) && !empty($_POST['userPass']))
		{
			$userPass = md5($_POST['userPass']);
			$set .= ", usu_clave='$userPass'";
		}
		if($db->update('usuario', $set, "usu_pk='$_GET[pk]'"))
			echo "<script type=\"text/javascript\" language=\"javascript\"> alert('La cuenta de usuario ha sido actualizada.') </script>";
		else
			echo "<script type=\"text/javascript\" language=\"javascript\"> alert('No fue posible actualizar la cuenta de usuario.') </script>";
	}
}
elseif(isset($_GET['changeStatus']))
{
	$row = $db->fetch_array($db->select('usu_estado', 'usuario', "usu_pk='$_GET[changeStatus]'"));
	if($row['usu_estado']=='1')
	{
		$estado = '0';
		$str = "inactiva";
	}
	else
	{
		$estado = '1';
		$str = "activa";
	}
	if($db->update('usuario', "usu_estado='$estado'", "usu_pk='$_GET[changeStatus]'"))
		echo "<script type=\"text/javascript\" language=\"javascript\"> alert('La cuenta de usuario ahora está $str.') </script>";
	else
		echo "<script type=\"text/javascript\" language=\"javascript\"> alert('No fue posible cambiar el estado de la cuenta de usuario.') </script>";
}

if(!isset($_GET['interfaz']))
{
	$_GET['interfaz'] = '';
}
if($_GET['interfaz']=='insert')
{ ?>
	<header>
	<div class="fLeft">
		<h1>Nueva cuenta de usuario</h1>
	</div>
	<div class="fRight"><a href="<?= $_SERVER['PHP_SELF']; ?>"><span class="icon">]</span> Regresar al listado de cuentas</a></div>
	<div class="clear"></div>
	</header>
	<div class="contentPane">
		<form id="userForm" name="userForm" action="<?= $_SERVER['PHP_SELF'].'?interfaz=insert'; ?>" method="post">
			<input type="hidden" name="form" value="insert">
			<table class="formTable">
				<tr>
					<th><span class="required">*</span> Usuario:</th>
					<td><input type="text" id="usuario" name="usuario" size="45" maxlength="100" class="validate[required]"></td>
				</tr>
				<tr>
					<th><span class="required">*</span>  Perfil:</th>
					<td>
						<select id="perfil" name="perfil" class="validate[required]">
							<option value=""></option> <?
							$result = $db->select('per_pk, per_nombre', 'perfil', "per_pk>1 AND per_estado='1' ORDER BY per_nombre");
							while($row = $db->fetch_array($result))
							{ ?>
								<option value="<?= $row['per_pk']; ?>"><?= $row['per_nombre']; ?></option> <?
							}	?>
						</select>
					</td>
				</tr>
				<tr>
					<th><span class="required">*</span> Nombre:</th>
					<td><input type="text" id="nombre" name="nombre" size="45" maxlength="100" class="validate[required]"></td>
				</tr>
				<tr>
					<th><span class="required">*</span> E-mail:</th>
					<td><input type="text" id="email" name="email" size="45" maxlength="100" class="validate[required,custom[email]]"></td>
				</tr>
				<tr>
					<th valign="top" style="padding-top:10px"><span class="required">*</span> Contraseña:</th>
					<td>
						<div class="pwdwidgetdiv" id="userPassDiv"></div>
						<noscript><input type="password" id="userPass" name="userPass" size="45" maxlength="100" class="validate[required]"></noscript>
					</td>
				</tr>
				<tr>
					<th>&nbsp;</th>
					<td>
						<input type="submit" value="Guardar">
					</td>
				</tr>
			</table>
		</form>
	</div> <?
}
elseif($_GET['interfaz']=='update')
{
	$row = $db->fetch_array($db->select('*', 'usuario', "usu_pk='$_GET[pk]'")); ?>
	<header>
	<div class="fLeft">
		<h1>Editar cuenta de usuario: <?= $row['usu_login']; ?></h1>
	</div>
	<div class="fRight"><a href="<?= $_SERVER['PHP_SELF']; ?>"><span class="icon">]</span> Regresar al listado de cuentas</a></div>
	<div class="clear"></div>
	</header>
	<div class="contentPane">
		<form id="userForm" name="userForm" action="<?= $_SERVER['PHP_SELF'].'?pk='.$_GET['pk'].'&interfaz=update'; ?>" method="post">
			<input type="hidden" name="form" value="update">
			<table class="formTable">
				<tr>
					<th><span class="required">*</span> Usuario:</th>
					<td><strong> &nbsp;<?= $row['usu_login']; ?></strong></td>
				</tr>
				<tr>
					<th><span class="required">*</span>  Perfil:</th>
					<td>
						<select id="perfil" name="perfil" class="validate[required]">
							<option value=""></option> <?
							$result = $db->select('per_pk, per_nombre', 'perfil', "per_pk>1 AND per_estado='1' ORDER BY per_nombre");
							while($pRow = $db->fetch_array($result))
							{
								$selected = $pRow['per_pk']==$row['per_pk'] ? ' selected' : ''; ?>
								<option value="<?= $pRow['per_pk']; ?>"<?= $selected; ?>><?= $pRow['per_nombre']; ?></option> <?
							}	?>
						</select>
					</td>
				</tr>
				<tr>
					<th><span class="required">*</span> Nombre:</th>
					<td><input type="text" id="nombre" name="nombre" size="45" maxlength="100" class="validate[required]" value="<?= $row['usu_nombre']; ?>"></td>
				</tr>
				<tr>
					<th><span class="required">*</span> E-mail:</th>
					<td><input type="text" id="email" name="email" size="45" maxlength="100" class="validate[required,custom[email]]" value="<?= $row['usu_email']; ?>"></td>
				</tr> <?
				/* Únicamente permitir resetear contraseñas al usuario haggen */
				if($_SESSION['per_pk']==1)
				{ ?>
					<tr>
						<th>&nbsp;</th>
						<td><a id="aNewPass">Generar una nueva contraseña</a></td>
					</tr>
					<tr id="newPass" style="display:none">
						<th valign="top" style="padding-top:10px"><span class="required">*</span> Contraseña:</th>
						<td>
							<div class="pwdwidgetdiv" id="userPassDiv"></div>
							<noscript><input type="password" id="userPass" name="userPass" size="45" maxlength="100"></noscript>
						</td>
					</tr> <?
				} ?>
				<tr>
					<th>&nbsp;</th>
					<td>
						<input type="submit" value="Guardar">
					</td>
				</tr>
			</table>
		</form>
	</div> <?
}
else
{ ?>
	<header>
	<div class="fLeft"><h1>Cuentas de usuario</h1></div>
	<div class="fRight"><a href="<? echo $_SERVER['PHP_SELF'].'?interfaz=insert'?>" class="newItem"><span class="icon">+</span> Nueva cuenta de usuario</a></div>
	<div class="clear"></div>
	</header>
	<div class="contentPane">
		<table width="100% "cellspacing="0" cellpadding="0" class="dataTable">
			<tr>
				<th>Usuario</th>
				<th>Perfil</th>
				<th>Cuenta</th>
				<th>E-mail</th>
				<th>Estado</th>
				<th>Opciones</th>
			</tr><?
			$offset = isset($_GET['offset']) ? intval($_GET['offset']) : 0;
			$numRows = $db->num_rows($db->select('usu_pk', 'usuario', 'per_pk>1'));
			if($numRows>0)
			{
				$result = $db->select('*', 'usuario, perfil', "usuario.per_pk=perfil.per_pk AND usu_pk>1 ORDER BY usu_nombre LIMIT ".$conf['settings']->pager." OFFSET $offset");
				while($row=$db->fetch_array($result))
				{ ?>
					<tr>
						<td><?= $row['usu_nombre']; ?></td>
						<td><?= $row['per_nombre']; ?></td>
						<td><?= $row['usu_login']; ?></td>
						<td><?= $row['usu_email']; ?></td>
						<td><? echo $row['usu_estado']=='1' ? 'Activo' : 'Inactivo'; ?></td>
						<td>
							<a href="<?= $_SERVER['PHP_SELF'].'?pk='.$row['usu_pk'].'&interfaz=update'; ?>" class="blueLink"><span class="icon">V</span> Editar</a>
							<a class="blueLink" onclick="changeStatus('<?= $row['usu_pk']; ?>', '<?= $row['usu_login']; ?>')"><span class="icon">V</span> Cambiar estado</a>
						</td>
					</tr> <?
				}
			} ?>
		</table> <?
		if($pag=pager($numRows, $conf['settings']->pager, $offset))  // Si es necesario paginar
		{
			$currentUrl = $_SERVER['PHP_SELF'];
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
} ?>
<script type="text/javascript" src="js/jquery.validationEngine-es.js"></script>
<script type="text/javascript" src="js/jquery.validationEngine.js"></script>
<script type="text/javascript" src="js/pwdwidget.js"></script>
<script type="text/javascript">
<!--
	$(document).ready(function(){
		// Intercalar colores de las filas del listado
		$('.dataTable tr:odd td').addClass('whiteTd');
		var pwdwidget = new PasswordWidget('userPassDiv','userPass');
		pwdwidget.MakePWDWidget();

		$('#userPass_id').addClass('validate[required]');  // Agregar clase de validationEngine al campo de contraseña recién generado para que ésta sea requerida.
		$("#userForm").validationEngine();

		$('#aNewPass').click(function(){
			$(this).parents('tr').hide();
			$('#newPass').show();
		});
	});

	function changeStatus(pk, name)
	{
		if(confirm("¿Confirma el cambio de estado de la cuenta de usuario '"+name+"'?"))
			window.location = '<?= "$_SERVER[PHP_SELF]?changeStatus="; ?>'+pk;
	}
-->
</script>
<? include("footer.php"); ?>