﻿<?php
	// basically just a null coelesce
	function get($s,$dv){
		return isset($_GET[$s]) ? $_GET[$s] : $dv;
	}

	$game = get("game", "star picker-upper");
	// if the filename includes the .js extension, remove it (it's already part of the script tag)
	if(substr($game,-3) == ".js"){
		$game = substr($game,0,-3);
	}
	$colors = array(
		"text"=>get("color-text", "#FFF"),
		"bg"=>get("color-bg", "#000"),
		"btnBg"=>get("color-btnBg", "#666"),
		"btnBgHover"=>get("color-btnBgHover", "#BBB")
	);

	$width = 128*get("scale", "4");
	$height = 128*get("scale", "4");

	$includeButtons = get("includeButtons", false);

	$includeFocus = get("includeFocus", false);
	$includeGamepad = get("includeGamepad", false);
	if($includeGamepad){
		$includeGamepadMultiplayer = get("includeGamepadMultiplayer", false);
	}
	$hideCursor = get("hideCursor", false);

	$totalHeight = $height;

	if($includeButtons){
		$includeBtnRestart = get("includeBtnRestart", false);
		$includeBtnPause = get("includeBtnPause", false);
		$includeBtnFullscreen = get("includeBtnFullscreen", false);
		$includeBtnSound = get("includeBtnSound", false);
		$includeBtnLink = get("includeBtnLink", false);

		$btnCount = $includeBtnRestart+$includeBtnPause+$includeBtnFullscreen+$includeBtnSound+$includeBtnLink;

		$btnHeight = get("btnHeight", "24") - 8;
		$btnWidth = get("btnWidth", "100") - 8;
		$btnLinkLbl = get("btnLinkLbl", "Carts");
		$btnLinkTgt = get("btnLinkTgt", "http://www.lexaloffle.com/bbs/?cat=7&sub=2");

		$totalHeight += ($btnHeight+9)*ceil($btnCount/max(1,floor($width/$btnWidth)));
	}
?>

<html>
	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<title><?php echo $game; ?> PICO-8 Cartridge</title>
		<meta name="description" content="">

		<!--
		HTML page created using PICO-8 Styler
		URL: https://seansleblanc.itch.io/pico-8-styler
		version: 1.6
		-->

		<style type="text/css">
		<!--

		html,body{
			padding:0;
			margin:0;
			width:100%;
			height:100%;
			background:transparent;
		}
		center{
			text-align:center;
			width:<?php echo $width; ?>px;
			height:<?php echo $totalHeight; ?>px;
			position:absolute;
			display:block;
			top:0;
			bottom:0;
			right:0;
			left:0;
			margin:auto;
		}
		canvas#canvas{
			width: <?php echo $width; ?>px;
			height: <?php echo $height; ?>px;

            image-rendering: optimizeSpeed;
			image-rendering: -moz-crisp-edges;
			image-rendering:   -o-crisp-edges;
			image-rendering: -webkit-optimize-contrast;
			image-rendering: crisp-edges;
			-ms-interpolation-mode: nearest-neighbor;
			image-rendering: pixelated;
			<?php if($hideCursor){ ?>
			cursor: none;
			<?php } ?>
		}

		<?php if($includeButtons){ ?>
		.pico8_el{
			width:<?php echo $btnWidth; ?>px;
			height:<?php echo $btnHeight; ?>px;
			display:inline-block; 
			margin: 1px;
			margin-bottom:0;
			padding: 4px;
			text-align: center;
			color:<?php echo $colors["text"]; ?>;
			background-color:<?php echo $colors["btnBg"]; ?>;
			font-family : verdana, sans-serif;
			font-size: 9pt;
			cursor: pointer;

			user-select: none;
			-moz-user-select: none;
			-webkit-user-select: none;
			-ms-user-select: none;
		}
		.pico8_el img{
			image-rendering: -moz-crisp-edges;
			image-rendering:   -o-crisp-edges;
			image-rendering: -webkit-optimize-contrast;
			image-rendering: crisp-edges;
			-ms-interpolation-mode: nearest-neighbor;
			image-rendering: pixelated;
		}

		.pico8_el a{
			text-decoration: none;
			color:<?php echo $colors["text"]; ?>;
		}

		.pico8_el:hover,
		.pico8_el:link{
			background-color:<?php echo $colors["btnBgHover"]; ?>;
		}

		<?php } ?>
		-->
		</style>
	</head>

	<body style="background:<?php echo $colors["bg"]; ?>">
		<center>
			<div <?php if(!$includeButtons){ echo 'class="pico8_el"'; } ?> style="width:<?php echo $width; ?>px;">
				<!-- CANVAS -->
				<canvas class="emscripten" id="canvas" oncontextmenu="event.preventDefault()"></canvas>

				<!-- SCRIPTS -->

				<?php if($includeGamepad){ ?>
				<!-- GAMEPAD API WRAPPER -->
				<script>
					<?php echo file_get_contents("input-gamepads.js"); ?>
				</script>

				<!-- GAMEPADS -> PICO-8 BUTTONS -->
				<script>
					<?php echo file_get_contents("gamepads-to-pico8" . ($includeGamepadMultiplayer ? "-multiplayer" : "-singleplayer") . ".js"); ?>
				</script>
				<?php } ?>

				<script async type="text/javascript" src="<?php echo $game; ?>.js"></script>
				<script type="text/javascript">
					var require = null; // fix for node.js issue (prevents game from running when opened in something like NW.js)

					// if using NW.js, let ESC exit fullscreen (no way to get out of it otherwise)
					if (typeof nw !== 'undefined' && nw !== null) {
						nw.App.registerGlobalHotKey(new nw.Shortcut({
							key: "Escape",
							active: function() {
								nw.Window.get().leaveFullscreen();
							}
						}));
					}

					var canvas = document.getElementById("canvas");
					canvas.width = window.innerWidth;
					canvas.height = window.innerHeight;

					// show Emscripten environment where the canvas is
					// arguments are passed to PICO-8
					
					var Module = {
						arguments: ["-width","<?php echo $width; ?>","-height","<?php echo $height; ?>"],
					};
					Module.canvas = canvas;

					// key blocker. prevent cursor keys from scrolling page while playing cart.
					function onKeyDown_blocker(event) {
						event = event || window.event;
						var o = document.activeElement;
						if(!o || o == document.body || o.tagName == "canvas"){
							if ([32, 37, 38, 39, 40].indexOf(event.keyCode) > -1){
								if(event.preventDefault){
									event.preventDefault();
								}
							}
						}
					}

					document.addEventListener('keydown', onKeyDown_blocker, false);

					<?php if($includeFocus){ ?>
					// sometimes iframes have issues getting focus, so
					// attempt to auto-focus right now and also when we get clicked
					window.focus();
					document.addEventListener('mousedown',function(event){
						window.focus();
					});
					<?php } ?>
				</script>

				<?php if($includeButtons){
					echo "<!-- BUTTONS -->\n";
					if($includeBtnRestart){
						echo '<div class="pico8_el" onclick="Module.pico8Reset();"><img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABgAAAAYCAYAAADgdz34AAAAaklEQVR4Ae2dOwoAMQhE15A+rfc/3bZ7AlMnQfywCkKsfcgMM9ZP+QHtIn0vLeBAFduiFdQ/0DmvtR5LXJ6CPSXe2ZXcFNlTxFbemKrbZPs35XogeS9xeQr+anT6LzoOwEDwZJ7jwhXUnwkTTiDQ2Ja34AAAABB0RVh0TG9kZVBORwAyMDExMDIyMeNZtsEAAAAASUVORK5CYII=" alt="Reset" width="12" height="12"/> Reset</div>';
					}
					if($includeBtnPause){
						echo '<div class="pico8_el" onclick="Module.pico8TogglePaused();"><img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABgAAAAYCAYAAADgdz34AAAAPUlEQVR4Ae3doQ0AIAxEUWABLPtPh2WCq26DwFSU/JPNT166QSu/Hg86W9dwLte+diP7AwAAAAAAgD+A+jM2ZAgo84I0PgAAABB0RVh0TG9kZVBORwAyMDExMDIyMeNZtsEAAAAASUVORK5CYII=" alt="Pause" width="12" height="12"/> Pause</div>';
					}
					if($includeBtnFullscreen){
						echo '<div class="pico8_el" onclick="Module.requestFullScreen(true, false);"><img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABgAAAAYCAYAAADgdz34AAAAaklEQVR4Ae2dsQ1AIQhExfze1v2ns3UCrfgFhmgUUAoGgHscp21wX9BqaZoDojbB96OkDJKNcTN2BHTyYNYmoT2BlPL7BKgcPfHjAVXKKadkHOn9K1r16N0czN6a95N8mnA7Aq2fTZ3Af3UKmCSMazL8HwAAABB0RVh0TG9kZVBORwAyMDExMDIyMeNZtsEAAAAASUVORK5CYII=" alt="Fullscreen" width="12" height="12"/> Fullscreen</div>';
					}
					if($includeBtnSound){
						echo '<div class="pico8_el" onclick="Module.pico8ToggleSound();"><img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABgAAAAYCAYAAADgdz34AAAAXklEQVR4Ae2doQ4AIQxD4YLH8v9fh+ULhjpxxSwLg2uyapr1JRu1iV5Z+1BGl4+xNpX38SYo2uRvYiT5LwEmt+ocgXVLrhPEgBiw8Q5w7/kueSkK+D2tJO4E/I3GrwkqQCBabEj/4QAAABB0RVh0TG9kZVBORwAyMDExMDIyMeNZtsEAAAAASUVORK5CYII=" alt="Toggle Sound" width="12" height="12"/> Sound</div>';
					}
					if($includeBtnLink){
						echo '<div class="pico8_el" ><a target="_new" href="'.$btnLinkTgt.'" style="width:100%; display:inline-block;"><img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABgAAAAYCAYAAADgdz34AAAAlElEQVR4Ae2dMQ5FQBCGh6jcwAkkateg3DiAa+iQUGqVKi95FQfAJRQOoHeBUf8JyQqKjZ1uMzuz2e/LTE3KhyF7kSlgLOykas23f6D+A9Yp84aAOYU15pcJnfji0Il2ID8HzC4y38ZrnfIBGxeRoR3c3EWrACdsV5BOsx7OSRnrOXh4F5HzA6bevwUn8wlz7eCDsQM99B3ks0s/4QAAABB0RVh0TG9kZVBORwAyMDExMDIyMeNZtsEAAAAASUVORK5CYII=" alt="'.$btnLinkLbl.'" width="12" height="12"/> '.$btnLinkLbl.'</a></div>';
					}
				} ?>
			</div>
		</center>
	</body>
</html>