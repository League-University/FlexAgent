<?php
include 'modules/header.php';
include 'modules/space-background.php';
?>
<script type="application/javascript" src="/js/avatar.js"></script>
<link rel="stylesheet" href="/css/flexy.css" type="text/css" media="all">
<div class="container">
<div class="avatar terminal">
	<div class="screen face" data-token="ee">
		<div class="eyes">
			<div class="eye left">
				<div class="iris">
					<div class="pupil">
						<div class="light"></div>
					</div>
				</div>
			</div>
			<div class="eye right">
				<div class="iris">
					<div class="pupil">
						<div class="light"></div>
					</div>
				</div>
			</div>
		</div>
		<div class="mouth">
			<div class="tongue left"></div>
			<div class="tongue right"></div>
			<div class="teeth upper"></div>
			<div class="teeth lower"></div>
		</div>
		<div class="screen-effects"></div>
	</div>
	<hr class="sep">
	<div class="wordmark">FlexAgent<small><br>&copy; 2025 League University</small></div>
</div>
<div class="output"></div>
<textarea id="input" style="width:100%;grid-column:span 2;"></textarea>
</div>
<script type="application/javascript">
// Initialize when DOM is ready
document.addEventListener("DOMContentLoaded", function () {
  console.log('DOM loaded, creating avatar...');
  const avatar = new Avatar();
  console.log('Avatar created:', avatar);
  console.log('Avatar face element:', avatar.face);
  const testPhrase = "hello human! 😍\n\nI am Flexy the FlexAgent avatar. 😊\n\nI am here to let you know that the quick brown fox jumped over the lazy dog... in case you weren't aware, now you know! 🤯\n\n(mind-blowing, right? 🙄)";
	setTimeout(() => {
    console.log('Starting to speak...');
    avatar.speak(testPhrase);
  }, 1000);
	avatar.avatar.addEventListener("click", () => {
    console.log('Avatar clicked, speaking again...');
    avatar.speak(testPhrase);
  });
});
</script><?php

include 'modules/footer.php';
