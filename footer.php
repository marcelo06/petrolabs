		<footer id="pageFooter">
			<div class="centerContent">
				<div id="FLocationPin"><strong><?= BUSCA_ESTACIONES ?></strong> <a href="puntos-de-venta.php"><?= ENCUENTRELAS_AQUI ?></a></div>
				<div id="fCalendar"><a href="noticias-y-eventos.php"><?= NOTICIAS_EVENTOS ?></a></div>
			</div>
		</footer>
		<div class="centerContent" id="credits">
			<div class="fLeft"><?= TXT_CREDITS; ?></div>				
			<div class="fRight"><a href="http://www.haggen-it.com" target="_blank"><? echo LNK_HAGGEN; ?></a></div>				
			<div class="clear"></div>
		</div>
		<div class="prodBadge"><?
			$bProd[0] = '<a href="http://aditivospetrolabs.com/productos-single.php?id=6"><img src="http://aditivospetrolabs.com/uploads/news/6/petrolabs-super-concentrado-4x1--170x170.jpg" alt="Super concentrado 4x1" style="width:90px;"></a>';
			$bProd[1] = '<a href="http://aditivospetrolabs.com/productos-single.php?id=10"><img src="http://aditivospetrolabs.com/uploads/news/10/petrolabs-octane-power-2--170x170.jpg" alt="Octane power 2" style="width:90px;"></a>';
			$bProd[2] = '<a href="http://aditivospetrolabs.com/productos-single.php?id=13"><img src="http://aditivospetrolabs.com/uploads/news/13/petrolabs-diesel-power-2--170x170.jpg" alt="Diesel Power 2" style="width:90px;"></a>';
			shuffle($bProd);
			echo $bProd[0]; ?>
			<span><?= TXT_USELO;?></span>
		</div>		
	</body>
</html><?
$db->disconnect(); ?>