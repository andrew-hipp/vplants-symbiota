<?php
//error_reporting(E_ALL);
include_once('../config/symbini.php');
header("Content-Type: text/html; charset=".$charset);
?>
<html>
<head>
	<title><?php echo $defaultTitle; ?> About Us - Chicago Region - County Map</title>
	<link href="../css/base.css" type="text/css" rel="stylesheet" />
	<link href="../css/main.css" type="text/css" rel="stylesheet" />
	<meta name='keywords' content='' />
	<script type="text/javascript">
		<?php include_once($serverRoot.'/config/googleanalytics.php'); ?>
	</script>
</head>
<body>
	<?php
	$displayLeftMenu = "true";
	include($serverRoot."/header.php");
	?> 
        <!-- This is inner text! -->
		<!-- start of inner text and right side content -->
		<div  id="innervplantstext">
			<div id="bodywrap">
				<div id="wrapper1"><!-- for navigation and content -->

					<!-- PAGE CONTENT STARTS -->

					<div id="content1wrap"><!--  for content1 only -->

					<div id="content1"><!-- start of primary content --><a id="pagecontent" name="pagecontent"></a>
						<h1>County Map for Chicago Region</h1>

						<div style="margin:20px;">
							 <p>Map of the 24 counties included in the Chicago Region for vPlants. Please be aware when searching records that there is a Lake County in Illinois as well as a Lake County in Indiana.</p>
			 
							 <img class="floatleft" src="<?php echo $clientRoot; ?>/images/vplants/img/map_vplants.gif" width="490" height="484" alt="Map of the vPlants Chicago Region showing counties included">


							<div class="floatleft">
								  <h3>Illinois</h3>
								  <ul>
								   <li>Boone</li>
								   <li>Cook</li>
								   <li>DeKalb</li>
								   <li>DuPage</li>
								   <li>Grundy</li>
								   <li>Kane</li>
								   <li>Kankakee</li>
								   <li>Kendall</li>
								   <li>Lake</li>
								   <li>McHenry</li>
								   <li>Will</li>
								  </ul>
								  <h3>Indiana</h3>
								  <ul>
								   <li>Jasper</li>
								   <li>Lake</li>
								   <li>LaPorte</li>
								   <li>Newton</li>
								   <li>Porter</li>
								   <li>Starke</li>
								   <li>St. Joseph</li>
								  </ul>
								  <h3>Michigan</h3>
								  <ul>
								   <li>Berrien</li>
								  </ul>
								  <h3>Wisconsin</h3>
								  <ul>
								   <li>Kenosha</li>
								   <li>Milwaukee</li>
								   <li>Racine</li>
								   <li>Walworth</li>
								   <li>Waukesha</li>
								  </ul>
							</div>
						</div>
					</div><!-- end of #content1 -->
					</div><!-- end of #content1wrap -->
					
					<div id="content2">
								
						<div class="box">
							<h3>Chicago Region Maps</h3>
							<ul>
								<li><a href="chicago.php">Chicago Region Main</a></li>
								<li><a href="map.php">State Map</a></li>
								<li><strong>County Map</strong></li>
							</ul>
						</div>
									
					</div><!-- end of #content2 -->
				</div><!-- end of #wrapper1 -->
			</div><!-- end of #bodywrap -->
		</div><!-- end of #innervplantstext -->

	<?php
	include($serverRoot."/footer.php");
	?> 

</body>
</html>