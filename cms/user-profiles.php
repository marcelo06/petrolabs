<?
$pageTitle = "Perfiles de usuario";
$menuActive = 4; ?>
<? include("header.php"); ?> <?

$result = $db->select('*', 'accion ORDER BY acc_pk');
while($row=$db->fetch_array($result))
{
	$pk = $row['acc_pk'];
	$perm[$pk] = $row['acc_nombre'];
}
if($_SERVER['REQUEST_METHOD']=='POST')
{
	if($_POST['form']=='insert')
	{
		if($db->num_rows($db->select('per_pk', 'perfil', "per_nombre='$_POST[nombre]' LIMIT 1"))>0)
		{
			echo "<script type=\"text/javascript\" language=\"javascript\"> alert('El nombre de perfil de usuario que ingresó ya se está usando.'); window.history.back(); </script>";
			exit();
		}
		elseif($db->insert('perfil', 'per_nombre', "'$_POST[nombre]'"))
		{
			$pk = $db->last_insert_id();
			foreach($perm as $prmPk=>$name)
			{
				if(isset($_POST['prm'][$prmPk]))
					$db->insert('permiso', 'per_pk, acc_pk', "'$pk', '$prmPk'");
			}
			echo "<script type=\"text/javascript\" language=\"javascript\"> alert('El perfil de usuario ha sido creado.'); window.location='$_SERVER[PHP_SELF]'; </script>";
			exit();
		}
		else
			echo "<script type=\"text/javascript\" language=\"javascript\"> alert('No fue posible crear el perfil de usuario.') </script>";
	}
	elseif($_POST['form']=='update')
	{
		if($db->num_rows($db->select('per_pk', 'perfil', "per_pk!='$_GET[pk]' AND per_nombre='$_POST[nombre]' LIMIT 1"))>0)
			echo "<script type=\"text/javascript\" language=\"javascript\"> alert('El nombre de perfil de usuario que ingresó ya se está usando.') </script>";
		elseif($db->update('perfil', "per_nombre='$_POST[nombre]'", "per_pk='$_GET[pk]'"))
		{
			if($db->delete('permiso', "per_pk='$_GET[pk]'"))
			{
				foreach($perm as $prmPk=>$name)
				{
					if(isset($_POST['prm'][$prmPk]))
						$db->insert('permiso', 'per_pk, acc_pk', "'$_GET[pk]', '$prmPk'");
				}
				echo "<script type=\"text/javascript\" language=\"javascript\"> alert('El perfil de usuario ha sido actualizado.') </script>";
			}
			else
				echo "<script type=\"text/javascript\" language=\"javascript\"> alert('Ocurrió una falla y no fue posible modificar los permisos para el perfil de usuario.') </script>";
		}
		else
			echo "<script type=\"text/javascript\" language=\"javascript\"> alert('No fue posible actualizar el perfil de usuario.') </script>";
	}
}
elseif(isset($_GET['changeStatus']))
{
	$row = $db->fetch_array($db->select('per_estado', 'perfil', "per_pk='$_GET[changeStatus]'"));
	if($row['per_estado']=='1')
	{
		$estado = '0';
		$str = "inactivo";
	}
	else
	{
		$estado = '1';
		$str = "activo";
	}
	if($db->update('perfil', "per_estado='$estado'", "per_pk='$_GET[changeStatus]'"))
	{
		echo "<script type=\"text/javascript\" language=\"javascript\"> alert('El perfil de usuario ahora está $str.'); window.location='$_SERVER[PHP_SELF]'; </script>";
		exit();
	}
	else
		echo "<script type=\"text/javascript\" language=\"javascript\"> alert('No fue posible cambiar el estado del perfil de usuario.') </script>";
}

if(!isset($_GET['interfaz']))
{
	$_GET['interfaz'] = '';
}
if($_GET['interfaz']=='insert')
{ ?>
	<header>
		<div class="fLeft">
			<h1>Nuevo perfil de usuario</h1>
		</div>
		<div class="fRight"><a href="<? echo $_SERVER['PHP_SELF']?>"><span class="icon">]</span> Regresar al listado de perfiles</a></div>
		<div class="clear"></div>
	</header>
	<div class="contentPane">
		<form id="profilesForm" name="profilesForm" action="<?= $_SERVER['PHP_SELF'].'?interfaz=insert'; ?>" method="post">
			<input type="hidden" name="form" value="insert">
			<table class="formTable">
				<tr>
					<th>Nombre:</th>
					<td><input type="text" id="nombre" name="nombre" size="45" maxlength="100" class="validate[required]"></td>
				</tr>
				<tr>
					<th valign="top">Permisos:</th>
					<td class="profilesList"> <?
						foreach($perm as $pk=>$name)
						{ ?>
							<label><input type="checkbox" name="<?= "prm[$pk]"; ?>"><?= $name; ?></label><div class="clear5"></div> <?
						} ?>
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
	$row = $db->fetch_array($db->select('per_nombre', 'perfil', "per_pk='$_GET[pk]'")); ?>
	<header>
		<div class="fLeft">
			<h1>Editar perfil de usuario</h1>
		</div>
		<div class="fRight"><a href="<? echo $_SERVER['PHP_SELF']?>"><span class="icon">]</span> Regresar al listado de perfiles</a></div>
		<div class="clear"></div>
	</header>
	<div class="contentPane">
		<form id="profilesForm" name="profilesForm" action="<?= $_SERVER['PHP_SELF'].'?pk='.$_GET['pk'].'&interfaz=update'; ?>" method="post">
			<input type="hidden" name="form" value="update">
			<table class="formTable">
				<tr>
					<th>Nombre:</th>
					<td><input type="text" id="nombre" name="nombre" size="45" maxlength="100" class="validate[required]" value="<?= $row['per_nombre']; ?>"></td>
				</tr>
				<tr>
					<th valign="top">Permisos:</th>
					<td class="profilesList"> <?
						foreach($perm as $pk=>$name)
						{
							$checked = $db->num_rows($db->select('prm_pk', 'permiso', "per_pk='$_GET[pk]' AND acc_pk='$pk'"))>0 ? ' checked' : ''; ?>
							<label><input type="checkbox" name="<?= "prm[$pk]"; ?>"<?= $checked; ?>><?= $name; ?></label><div class="clear5"></div> <?
						} ?>
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
else
{ ?>
	<header>
	<div class="fLeft"><h1>Perfiles de usuario</h1></div>
	<div class="fRight"><a href="<?= $_SERVER['PHP_SELF'].'?interfaz=insert'?>" class="newItem"><span class="icon">+</span> Nuevo perfil de usuario</a></div>
	<div class="clear"></div>
	</header>
	<div class="contentPane">
		<table width="100% "cellspacing="0" cellpadding="0" class="dataTable">
			<tr>
				<th>Perfil</th>
				<th>Estado</th>
				<th>Opciones</th>
			</tr><?
			$offset = isset($_GET['offset']) ? intval($_GET['offset']) : 0;
			$numRows = $db->num_rows($db->select('per_pk', 'perfil', 'per_pk>1'));
			if($numRows>0)
			{
				$result = $db->select('*', 'perfil', "per_pk>1 ORDER BY per_nombre LIMIT ".$conf['settings']->pager." OFFSET $offset");
				while($row=$db->fetch_array($result))
				{ ?>
					<tr>
						<td><?= $row['per_nombre']; ?></td>
						<td><? echo $row['per_estado']=='1' ? 'Activo' : 'Inactivo'; ?></td>
						<td>
							<a href="<?= $_SERVER['PHP_SELF'].'?pk='.$row['per_pk'].'&interfaz=update'; ?>" class="blueLink"><span class="icon">V</span> Editar</a> <?
							if($row['per_estado']=='0' || $row['per_estado']=='1' && $db->num_rows($db->select('usu_pk', 'usuario', "per_pk='$row[per_pk]'"))==0)
							{ ?>
								<a class="blueLink" onclick="changeStatus('<?= $row['per_pk']; ?>', '<?= $row['per_nombre']; ?>')"><span class="icon">V</span> Cambiar estado</a> <?
							} ?>
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
<script type="text/javascript">
<!--
	$(document).ready(function(){
		$("#profilesForm").validationEngine();

		// Intercalar colores de las filas del listado
		$('.dataTable tr:odd td').addClass('whiteTd');
	});

	function changeStatus(pk, name)
	{
		if(confirm("¿Confirma el cambio de estado del perfil de usuario '"+name+"'?"))
			window.location = '<?= "$_SERVER[PHP_SELF]?changeStatus="; ?>'+pk;
	}
-->
</script>
<? include("footer.php"); ?>