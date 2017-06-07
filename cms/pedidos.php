<?php
$pageTitle = "Pedidos";
$menuActive = 3; ?>
<? include("header.php"); ?> <?
$rowPed = $db->fetch_all($db->select('*', 'pedido ORDER BY ped_pk DESC LIMIT 0,50'));
?>
<header>
	<h1>Pedidos de productos</h1>
</header>
<div class="clear10"></div>
<div class="contentPane">
	<table width="100%" cellspacing="0" cellpadding="0" class="dataTable">
		<tr>
			<td><strong>#</strong></td>
			<th><strong>Asesor</strong></th>
			<td><strong>Ciudad / EDS</strong></td>
			<th><strong>Teléfono</strong></th>
			<td><strong>S. C. 4x1</strong></td>
			<th><strong>S. C. 4x1 Plus</strong></th>
			<td><strong>Octane Power 2</strong></td>
			<th><strong>D. P. 4 onzas</strong></th>
			<td><strong>D. P. 2</strong></td>
			<th><strong>Moto Power</strong></th>
			<td><strong>Observaciones</strong></td>
		</tr> <?
		if(count($rowPed)>0)
		{
			foreach($rowPed as $r)
			{ ?>
				<tr>
					<td><?= $r['ped_pk']; ?></td>
					<td><?= $r['asesor']; ?></td>
					<td><?= $r['nombre']; ?></td>
					<td><?= $r['telefono']; ?></td>
					<td style="text-align:center;"><?= $r['sc4x1']; ?></td>
					<td style="text-align:center;"><?= $r['sc4x1p']; ?></td>
					<td style="text-align:center;"><?= $r['op2']; ?></td>
					<td style="text-align:center;"><?= $r['dp4o']; ?></td>
					<td style="text-align:center;"><?= $r['dp2']; ?></td>
					<td style="text-align:center;"><?= $r['mp']; ?></td>
					<td><?= $r['observaciones']; ?></td>
				</tr> <?
			}
		}
		else
		{ ?>
			<tr><td colspan="11"><div align="center">No se han registrado solicitudes de pedido.</div></td></tr> <?
		} ?>
	</table>
</div>
<? include("footer.php"); ?>